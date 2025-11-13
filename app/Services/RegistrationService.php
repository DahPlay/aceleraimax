<?php

namespace App\Services;

use App\Models\Customer;
use App\Models\User;
use App\Models\Order;
use App\Models\Plan;
use App\Models\Coupon;
use App\Models\Package;
use App\Models\UserConsent;
use App\Services\Alloyal\User\UserCreate;
use App\Services\PaymentGateway\Connectors\AsaasConnector;
use App\Services\PaymentGateway\Gateway;
use App\Services\YouCast\Customer\CustomerCreate;
use App\Services\YouCast\Customer\CustomerSearch;
use App\Services\AppIntegration\PlanCreateService;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;

class RegistrationService
{
    public function handle(array $data): Customer
    {
        Log::channel('registration')->debug('Validando unicidade dos dados', [
            'login' => $data['login'],
            'document' => $data['document'],
        ]);

        $this->validateUniqueness($data);

        $customerData = $this->prepareCustomerData($data);

        $asaasCustomerId = null;
        $creditCardInfo = null;

        if (!empty($customerData['document'])) {
            Log::channel('registration')->info('Criando customer no Asaas', [
                'name' => $customerData['name'],
                'email' => $customerData['email'],
                'document' => $customerData['document'],
            ]);

            $asaasCustomerId = $this->createAsaasCustomer($customerData);

            $alloyalPayload = [
                'name' => $customerData['name'],
                'email' => $customerData['email'],
                'cpf' => $customerData['document'],
                'cellphone' => $customerData['mobile'],
                'password' => $data['password'],
            ];

            $alloyalResponse = (new UserCreate())->handle($alloyalPayload);

            Log::channel('registration')->info('Usuário criado com sucesso no Alloyal', [
                'cpf' => $data['document'],
            ]);

            try {
                $creditCardData = $this->extractCreditCardData($data);
                Log::channel('registration')->info('Tentando tokenizar cartão', [
                    'asaas_customer_id' => $asaasCustomerId,
                    'card_last4' => substr($creditCardData['number'], -4),
                    'holder' => $creditCardData['holderName'],
                ]);

                $creditCardInfo = $this->tokenizeCreditCard($asaasCustomerId, $creditCardData);

                Log::channel('registration')->info('Cartão tokenizado com sucesso', [
                    'asaas_customer_id' => $asaasCustomerId,
                    'token' => $creditCardInfo['token'],
                    'brand' => $creditCardInfo['brand'],
                ]);
            } catch (\Exception $e) {
                Log::channel('registration')->error('Falha na tokenização do cartão. Deletando customer no Asaas.', [
                    'asaas_customer_id' => $asaasCustomerId,
                    'error' => $e->getMessage(),
                ]);
                $this->deleteAsaasCustomer($asaasCustomerId);
                throw $e;
            }
        }

        return DB::transaction(function () use ($data, $customerData, $asaasCustomerId, $creditCardInfo) {
            Log::channel('registration')->debug('Iniciando transação de banco para criação local');

            $customer = Customer::create($customerData);
            Log::channel('registration')->debug('Customer local criado', ['id' => $customer->id]);

            $user = $this->createUser($customer, $data['password']);
            Log::channel('registration')->debug('User local criado', ['email' => $user->id]);

            $userConsent = $this->createUserConsent((int) $user->id);
            Log::channel('registration')->debug('UserConsent local criado', ['email' => $userConsent->id]);

            if ($asaasCustomerId) {
                $customer->update([
                    'customer_id' => $asaasCustomerId,
                    'credit_card_token' => $creditCardInfo['token'],
                    'credit_card_brand' => $creditCardInfo['brand'],
                    'credit_card_number' => $creditCardInfo['number'],
                ]);

                Log::channel('registration')->info('Criando customer na YouCast', ['login' => $customer->login]);
                $viewersId = $this->createYouCastCustomer($customer);
                $customer->update(['viewers_id' => $viewersId]);
                Log::channel('registration')->info('Customer YouCast criado', ['viewers_id' => $viewersId]);

                Log::channel('registration')->debug('Criando pedido');
                $order = $this->createOrder($customer, (int) $data['plan_id'], $data['coupon_id'] ?? null, (int) $userConsent->id);
                Log::channel('registration')->debug('Pedido criado', ['order_id' => $order->id]);

                Log::channel('registration')->debug('Criando assinatura no Asaas');
                $this->createAsaasSubscription($customer, $order, (int) $data['plan_id']);
                Log::channel('registration')->info('Assinatura no Asaas criada com sucesso');
            }

            Log::channel('registration')->info('Registro finalizado com sucesso na transação', [
                'customer_id' => $customer->id,
                'email' => $customer->email,
            ]);

            return $customer;
        });
    }

    private function deleteAsaasCustomer(string $customerId): void
    {
        try {
            $adapter = new AsaasConnector();
            $gateway = new Gateway($adapter);
            $gateway->customer()->delete($customerId);
            Log::channel('registration')->info("Customer Asaas deletado com sucesso", ['asaas_id' => $customerId]);
        } catch (\Exception $e) {
            Log::channel('registration')->warning("Falha ao deletar customer Asaas", [
                'asaas_id' => $customerId,
                'error' => $e->getMessage(),
            ]);
        }
    }

    private function createAsaasCustomer(array $customerData): string
    {
        $adapter = new AsaasConnector();
        $gateway = new Gateway($adapter);

        $payload = [
            'name' => $customerData['name'],
            'cpfCnpj' => sanitize($customerData['document']),
            'email' => $customerData['email'],
            'mobilePhone' => sanitize($customerData['mobile']),
        ];

        $listResponse = $gateway->customer()->list($payload);
        if (!empty($listResponse['data'])) {
            $existingId = $listResponse['data'][0]['id'];
            Log::channel('registration')->info('Customer Asaas reutilizado (já existia)', [
                'asaas_id' => $existingId,
                'email' => $customerData['email'],
            ]);
            return $existingId;
        }

        $response = $gateway->customer()->create($payload);

        if (isset($response['error'])) {
            $error = $response['error']['errors'][0]['description'] ?? 'Erro ao criar cliente no Asaas';
            Log::channel('registration')->error("Asaas - erro ao criar cliente", [
                'name' => $customerData['name'],
                'email' => $customerData['email'],
                'error_detail' => $error,
            ]);
            throw new \Exception($error);
        }

        if (!isset($response['id'])) {
            Log::channel('registration')->error("Asaas - resposta inválida ao criar cliente", [
                'response' => $response,
                'email' => $customerData['email'],
            ]);
            throw new \Exception('Resposta inválida do Asaas ao criar cliente.');
        }

        Log::channel('registration')->info('Customer Asaas criado com sucesso', [
            'asaas_id' => $response['id'],
            'email' => $customerData['email'],
        ]);

        return $response['id'];
    }

    private function validateUniqueness(array $data): void
    {
        if (Customer::where('login', $data['login'])->exists()) {
            throw new \InvalidArgumentException('O login informado já está em uso.');
        }

        if (User::where('login', $data['login'])->exists()) {
            throw new \InvalidArgumentException('O login informado já está em uso.');
        }

        if (User::where('email', $data['email'])->exists()) {
            throw new \InvalidArgumentException('O e-mail informado já está em uso.');
        }

        if (Customer::where('document', $data['document'])->exists()) {
            throw new \InvalidArgumentException('O CPF/CNPJ informado já está cadastrado.');
        }
    }

    private function prepareCustomerData(array $data): array
    {
        return [
            'login' => $data['login'],
            'name' => $data['name'],
            'document' => $data['document'],
            'mobile' => $data['mobile'],
            'email' => $data['email'],
            'payment_asaas_id' => $data['payment_asaas_id'] ?? null,
            'cpf_dependente_1' => $data['cpf_dependente_1'] ?? null,
            'cpf_dependente_2' => $data['cpf_dependente_2'] ?? null,
            'cpf_dependente_3' => $data['cpf_dependente_3'] ?? null,
            'coupon_id' => $data['coupon_id'] ?? null,
        ];
    }

    private function createUser(Customer $customer, string $password): User
    {
        $user = User::create([
            'name' => $customer->name,
            'email' => $customer->email,
            'login' => $customer->login,
            'password' => Hash::make($password),
            'access_id' => 1,
        ]);

        $customer->update(['user_id' => $user->id]);

        return $user;
    }

    private function createUserConsent(int $user_id): UserConsent
    {
        $consent = UserConsent::create([
            'user_id' => $user_id,
            'ip_address' => request()->ip(),
            'user_agent' => request()->userAgent(),
            'consented_at' => now(),
        ]);

        return $consent;
    }

    private function extractCreditCardData(array $data): array
    {
        return [
            'holderName' => $data['credit_card_name'],
            'number' => $data['credit_card_number'],
            'expiryMonth' => $data['credit_card_expiry_month'],
            'expiryYear' => $data['credit_card_expiry_year'],
            'ccv' => $data['credit_card_ccv'],
            'ip' => $data['ip'] ?? request()->ip(),
        ];
    }

    private function tokenizeCreditCard(string $asaasCustomerId, array $cardData): array
    {
        $adapter = new AsaasConnector();
        $gateway = new Gateway($adapter);

        $payload = [
            'customer' => $asaasCustomerId,
            'creditCard' => [
                'holderName' => $cardData['holderName'],
                'number' => $cardData['number'],
                'expiryMonth' => $cardData['expiryMonth'],
                'expiryYear' => $cardData['expiryYear'],
                'ccv' => $cardData['ccv'],
            ],
            'remoteIp' => $cardData['ip'],
        ];

        $response = $gateway->creditCard()->tokenize($payload);

        if (!isset($response['creditCardToken']) || isset($response['error'])) {
            $error = $response['error']['errors'][0]['description'] ?? 'Erro ao tokenizar cartão';
            Log::channel('registration')->info("Asaas - falha na tokenização: {$error}");
            throw new \Exception($error);
        }

        return [
            'token' => $response['creditCardToken'],
            'brand' => $response['creditCardBrand'],
            'number' => $response['creditCardNumber'],
        ];
    }

    private function createYouCastCustomer(Customer $customer): string
    {
        $response = (new CustomerSearch)->handle($customer->login);

        if ($response['response'] ?? false) {
            $customerData = $response['response'];
            $customerId = array_key_first($customerData);
            Log::channel('registration')->info('Customer YouCast reutilizado', [
                'viewers_id' => $customerId,
                'login' => $customer->login,
            ]);
            return $customerId ?: '';
        }

        $createResponse = (new CustomerCreate)->handle($customer);
        $viewersId = $createResponse['response'] ?? '';

        if (empty($viewersId) || $viewersId == '1') {
            Log::channel('registration')->error("YouCast - falha ao criar cliente: resposta inválida", $createResponse);

            throw new \Exception('Não foi possível criar o cliente na YouCast.');
        }

        return $viewersId;
    }

    private function createOrder(Customer $customer, int $planId, ?int $couponId, int $consent_id): Order
    {
        $plan = Plan::findOrFail($planId);
        $value = $plan->value;

        $coupon = null;
        $packagesToCreate = [];

        if ($couponId) {
            $coupon = Coupon::find($couponId);
            if ($coupon) {
                $value = $plan->value - ($plan->value * ($coupon->percent / 100));
                $packagesToCreate[] = $coupon->cod;
            }
        }

        foreach ($plan->packagePlans as $packagePlan) {
            $package = Package::find($packagePlan->package_id);
            if ($package) {
                $packagesToCreate[] = $package->cod;
            }
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
            'consent_id' => $consent_id,
        ]);

        (new PlanCreateService($packagesToCreate, $customer->viewers_id))->createPlan();

        return $order;
    }

    private function createAsaasSubscription(Customer $customer, Order $order, int $plan_id): void
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

        if ($order->value <= 0) {
            Log::channel('registration')->error("Assinatura não criada no Asaas (valor <= 0)", [
                'customer_id' => $customer->id,
                'order_id' => $order->id,
                'value' => $order->value,
            ]);
            return;
        }

        $adapter = new AsaasConnector();
        $gateway = new Gateway($adapter);

        $payload = [
            'customer' => $customer->customer_id,
            'billingType' => $plan->billing_type,
            'value' => $value,
            'nextDueDate' => now()->addDays($plan->free_for_days)->format('Y-m-d'),
            'cycle' => $plan->cycle,
            'description' => "Assinatura do plano $plan->name",
            'externalReference' => 'Pedido: ' . $order->id,
            'creditCardToken' => $customer->credit_card_token,
        ];

        $response = $gateway->subscription()->create($payload);

        if (isset($response['error'])) {
            $error = $response['error']['errors'][0]['description'] ?? 'Erro ao criar assinatura';
            Log::channel('registration')->error("Asaas - erro na assinatura para {$customer->name}: {$error}");
            throw new \Exception($error);
        }

        if (!isset($response['id'])) {
            Log::channel('registration')->error("Asaas - resposta inválida na criação de assinatura: " . json_encode($response));
            throw new \Exception('Assinatura não foi criada corretamente no Asaas.');
        }

        $order->update([
            'subscription_asaas_id' => $response['id'],
            'customer_asaas_id' => $response['customer'] ?? null,
            'status' => $response['status'] ?? null,
            'description' => $response['description'] ?? null,
        ]);
    }
}
