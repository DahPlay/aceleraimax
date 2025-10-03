<?php

declare(strict_types=1);

namespace App\Observers;

use App\Models\Coupon;
use App\Models\Customer;
use App\Models\Order;
use App\Models\Package;
use App\Models\Plan;
use App\Models\User;
use App\Services\AppIntegration\PlanCreateService;
use App\Services\PaymentGateway\Connectors\AsaasConnector;
use App\Services\PaymentGateway\Gateway;
use App\Services\YouCast\Customer\CustomerCreate;
use App\Services\YouCast\Customer\CustomerSearch;
use App\Services\YouCast\Customer\CustomerUpdate;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class CustomerObserver
{
    public function created(Customer $customer): void
    {
        $this->createUser($customer);

        if ($customer->document) {
            $this->createCustomerInAsaas($customer);
            $this->generateCreditCardToken($customer);
            $this->createCustomerInYouCast($customer);

            $plan_id = (int) request()->input('plan_id');

            $order = $this->createOrder($customer, $plan_id);

            $this->createSubscriptionInAsaas($customer, $plan_id, $order);
        }
    }

    public function deleted(Customer $customer)
    {
    }

    private function createUser(Customer $customer): void
    {
        $user = User::create([
            'name' => $customer->name,
            'email' => $customer->email,
            'login' => $customer->login,
            'password' => !is_null($customer->document) ? Hash::make(request()->input('password')) : Str::random(12),
            'access_id' => 1,
        ]);

        $this->updateUserIdInCustomer($customer, $user);
    }

    private function updateUserIdInCustomer(Customer $customer, User $user): void
    {
        $customer->user_id = $user->id;
        $customer->saveQuietly();
    }

    private function createCustomerInAsaas(Customer $customer): mixed
    {
        $customer = Customer::query()->firstWhere('email', $customer->email);

        $adapter = new AsaasConnector();
        $gateway = new Gateway($adapter);

        $data = [
            'name' => $customer->name,
            'cpfCnpj' => sanitize($customer->document),
            'email' => $customer->email,
            'mobilePhone' => sanitize($customer->mobile),
        ];

        $response = $gateway->customer()->list($data);

        if (!empty($response['data'])) {
            $customer->updateQuietly([
                'customer_id' => $response['data'][0]['id']
            ]);

            return $response;
        }

        $response = $gateway->customer()->create($data);

        if (is_null($response)) {
            Log::error("Erro ao atualizar {$customer->name}: retorno nulo");
            toastr()->error("Erro ao atualizar {$customer->name}: retorno nulo");

            return null;
        }

        if (!isset($response['id']) && is_string($response['error'])) {
            Log::error("Erro ao atualizar {$customer->name}: {$response['error']}");
            toastr()->error("Erro ao atualizar {$customer->name}: {$response['error']}");

            return null;
        }

        if (!isset($response['id']) && is_array($response['error'])) {
            $error = $response['error']['errors'][0]['description'] ?? 'Erro de integração';
            toastr()->error($error);
            Log::error("Erro ao atualizar - linha 92 - CustomerObserver {$customer->name}: {$error}");

            return null;
        }

        Log::info("Customer criado no ASAAS - linha 97 - CustomerObserver:", $response);

        $customer->updateQuietly([
            'customer_id' => $response['id']
        ]);

        return $response;
    }

    public function generateCreditCardToken(Customer $customer): mixed
    {
        $customer = Customer::query()->firstWhere('email', $customer->email);

        $adapter = new AsaasConnector();
        $gateway = new Gateway($adapter);

        $data = [
            'customer'   => $customer->customer_id,
            'creditCard' => [
                'holderName'  => request()->input('credit_card_name'),
                'number'      => request()->input('credit_card_number'),
                'expiryMonth' => request()->input('credit_card_expiry_month'),
                'expiryYear'  => request()->input('credit_card_expiry_year'),
                'ccv'         => request()->input('credit_card_ccv'),
            ],
            'remoteIp'        => request()->ip(),
        ];

        $response = $gateway->creditCard()->tokenize($data);

        if (!isset($response['creditCardToken']) && isset($response['error'])) {
            $error = $response['error']['errors'][0]['description'] ?? 'Erro de integração';
            Log::error("Erro ao tokenizar cartão - linha 135 - CustomerObserver {$customer->name}: {$error}");
            toastr()->error("{$error}");
            //return null;
            throw new \Exception($error);
        }

        Log::info("Cartão de Crédito tokenizado - linha 137 - CustomerObserver:", $response);

        $customer->updateQuietly([
            'credit_card_number' => $response['creditCardNumber'],
            'credit_card_brand'  => $response['creditCardBrand'],
            'credit_card_token'  => $response['creditCardToken'],
        ]);

        return null;
    }

    private function createCustomerInYouCast(Customer $customer): void
    {
        $response = (new CustomerSearch)->handle($customer->login);

        Log::error($response);

        $viewers_id = "";
        if (!$response["response"]) {
            $viewers_id = (new CustomerCreate)->handle($customer);

            $viewers_id = $viewers_id["response"];

            if ($viewers_id != 1) {
                Log::error($viewers_id);
            }
        } else {
            $customerData = $response['response'] ?? null;

            if ($customerData) {
                $customerId = array_key_first($customerData);

                if ($customerId) {
                    $viewers_id = $customerId ?? null;
                }
            }
        }

        $customer->updateQuietly([
            'viewers_id' => $viewers_id,
        ]);
    }

    private function createOrder(Customer $customer, int $plan_id): mixed
    {
        $customer = Customer::query()->firstWhere('email', $customer->email);
        $plan = Plan::query()->firstWhere('id', $plan_id);
        $coupon = null;
        $packagesToCreate = [];
        $value = $plan->value;
        if ($customer->coupon_id !== null) {
            $coupon = Coupon::find($customer->coupon_id);
        }

        if ($coupon) {
            $value = $plan->value - ($plan->value * ($coupon->percent / 100));
            $packagesToCreate[] = $coupon->cod;
        }
        $order = Order::create([
            'customer_id' => $customer->id,
            'plan_id' => $plan->id,
            'customer_asaas_id' => $customer->customer_id,
            'value' => $value,
            'cycle' => $plan->cycle,
            'billing_type' => 'CREDIT_CARD',
            'next_due_date' => now()->addDays($plan->free_for_days)->format('Y-m-d'),
            'original_plan_value' => $plan->value,
        ]);

        /* if ($order->plan->free_for_days > 0) {
             (new PlanCreate())->handle($customer->viewers_id, 861);
         }*/
        foreach ($order->plan->packagePlans as $packagePlan) {
            $pack = Package::find($packagePlan->package_id);
            $packagesToCreate[] = $pack->cod;
        };
        (new PlanCreateService($packagesToCreate, $order->customer->viewers_id))->createPlan();

        return $order;
    }

    private function createSubscriptionInAsaas(Customer $customer, int $plan_id, Order $order): mixed
    {
        $customer = Customer::query()->firstWhere('email', $customer->email);
        $plan = Plan::query()->firstWhere('id', $plan_id);
        $coupon = null;
        $value = $plan->value;
        if ($customer->coupon_id !== null) {
            $coupon = Coupon::find($customer->coupon_id);
        }

        if ($coupon) {
            $value = $plan->value - ($plan->value * ($coupon->percent / 100));
        }

        // 🚫 Ignora criação de assinatura se valor <= 0
        if ($value <= 0) {
            Log::info("Assinatura não criada no Asaas para {$customer->name} pois o valor é zero ou negativo.");
            return null;
        }

        $adapter = new AsaasConnector();
        $gateway = new Gateway($adapter);

        $data = [
            'customer' => $customer->customer_id,
            'billingType' => $plan->billing_type,
            'value' => $value,
            'nextDueDate' => now()->addDays($plan->free_for_days)->format('Y-m-d'),
            'cycle' => $plan->cycle,
            'description' => "Assinatura do plano $plan->name",
            'externalReference' => 'Pedido: ' . $order->id,
            'creditCardToken' => $customer->credit_card_token,
        ];

        $response = $gateway->subscription()->create($data);

        if (!isset($response['id']) && isset($response['error']) && is_string($response['error'])) {
            $error = $response['error']['errors'][0]['description'] ?? 'Erro de integração';
            Log::error("Erro ao atualizar - linha 150 - CustomerObserver {$customer->name} - {$plan->name}: {$error}");
            toastr()->error("{$error}");
            return '';
        }

        if (!isset($response['id']) && isset($response['error']) && is_array($response['error'])) {
            $error = $response['error']['errors'][0]['description'] ?? 'Erro de integração';
            Log::error("Erro ao atualizar - linha 159 - CustomerObserver {$customer->name} - {$plan->name}: {$error}");
            toastr()->error($error);
            return '';
        }

        if (isset($response['id'])) {
            $order->subscription_asaas_id = $response['id'];
            $order->customer_asaas_id = $response['customer'] ?? null;
            $order->status = $response['status'] ?? null;
            $order->description = $response['description'] ?? null;
            $order->save();

            return $response['id'];
        } else {
            Log::error("Assinatura não foi criada corretamente para {$customer->name}. Resposta sem ID: " . json_encode($response));
            toastr()->error("Não foi possível criar a assinatura. Verifique o log.");
            return null;
        }

    }


    public function updated(Customer $customer): void
    {
        $customerSearch = (new CustomerSearch)->handle($customer->login);

        Log::info("customerSearch: ");
        Log::info($customerSearch);

        Log::info("status: " . $customerSearch["status"]);

        if ($customerSearch["status"] === 1) {
            (new CustomerUpdate)->handle($customer);

            // if (!is_null($customer->orders)) {
            //     $this->createCustomerInAsaas($customer);
            //     $this->createCustomerInYouCast($customer);

            //     $plan_id = (int) request()->input('plan_id');

            //     $order = $this->createOrder($customer, $plan_id);

            //     $this->createSubscriptionInAsaas($customer, $plan_id, $order);
            // }
        }

        Log::info('CustomerObserver - line 233 - Customer atualizado na YouCast', $customerSearch);
    }
}
