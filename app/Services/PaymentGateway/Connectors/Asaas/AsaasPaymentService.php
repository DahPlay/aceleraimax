<?php

namespace App\Services\PaymentGateway\Connectors\Asaas;

use App\Enums\StatusOrderAsaasEnum;
use App\Jobs\BackOrderOldPlanJob;
use App\Jobs\UpdateSubscriptionAfterProportionalPayJob;
use App\Models\Coupon;
use App\Models\Order;
use App\Models\Package;
use App\Services\Alloyal\User\UserDisable;
use App\Services\Alloyal\User\UserEnable;
use App\Services\AppIntegration\PlanCancelService;
use App\Services\AppIntegration\PlanCreateService;
use App\Services\YouCast\Plan\PlanHistory;
use App\Services\YouCast\Plan\PlanList;
use Illuminate\Support\Facades\Log;

class AsaasPaymentService
{
    protected $paymentId = null;
    protected $customerId = null;
    protected $subscriptionId = null;
    protected $paymentStatus = null;
    protected $dueDate = null;
    protected $paymentDate = null;
    protected $order = null;

    public function processEvent(string $event, array $data): bool
    {
        $this->paymentId = $data['payment']['id'];
        $this->customerId = $data['payment']['customer'];
        $this->subscriptionId = $data['payment']['subscription'];
        $this->paymentStatus = $data['payment']['status'];
        $this->dueDate = $data['payment']['dueDate'];
        $this->paymentDate = $data['payment']['paymentDate'];

        $order = Order::where('subscription_asaas_id', $this->subscriptionId)->first();

        if (!$order) {
            Log::channel('payment')->warning("Ordem não encontrada para a assinatura $this->subscriptionId no evento $event.");
            return false;
        }

        $this->order = $order;

        Log::channel('payment')->info('AsaasPaymentService acionado');

        if (!$this->order) {
            Log::channel('payment')->warning("Ordem não encontrada para a assinatura $this->subscriptionId no evento $event.");
            return false;
        }

        switch ($event) {
            case 'PAYMENT_RECEIVED':
                $this->updatePaymentSteps();

                Log::channel('payment')->info("PAYMENT_RECEIVED - Pagamento recebido para a ordem {$this->order->id}.");

                break;

            case 'PAYMENT_CREATED':
                $this->order->update([
                    'payment_asaas_id' => $this->paymentId,
                    'payment_status' => $this->paymentStatus,
                    'next_due_date' => $this->dueDate,
                ]);

                Log::channel('payment')->info("PAYMENT_CREATED - Pagamento criado para a ordem {$this->order->id}.");
                break;

            case 'PAYMENT_CONFIRMED':
                $this->updatePaymentSteps();

                Log::channel('payment')->info("PAYMENT_CONFIRMED - Pagamento confirmado para a ordem {$this->order->id}.");

                break;

            case 'PAYMENT_OVERDUE':

                if ($this->order->changed_plan) {
                    BackOrderOldPlanJob::dispatch($this->order);
                    break;
                }
                $this->order->update([
                    'status' => 'INACTIVE',
                    'payment_status' => $this->paymentStatus
                ]);

                $cpf = $this->order->customer?->document ?? '';

                (new UserDisable())->handle($cpf);

                Log::channel('payment')->info('Usuário inativado com sucesso no Alloyal');

                Log::channel('payment')->warning("Pagamento atrasado para a ordem {$this->order->id}.");

                $youcast = (new PlanList)->handle($this->order->customer->viewers_id);

                if ($youcast["status"] == 1) {
                    $packagesToCancel = [];
                    foreach ($this->order->plan->packagePlans as $packagePlan) {
                        $pack = Package::find($packagePlan->package_id);
                        $packagesToCancel[] = $pack->cod;
                    };
                    (new PlanCancelService($packagesToCancel, $this->order->customer->viewers_id))->cancelPlan();

                    //adiciona o pacote de suspensão
                    $suspension = $this->getSuspension();
                    if ($suspension) {
                        $packagesToCreate = [$suspension->cod];
                        (new PlanCreateService($packagesToCreate, $this->order->customer->viewers_id))->createPlan();
                    }
                }

                break;

            case 'PAYMENT_DELETED':
                $this->order->update(['payment_status' => $this->paymentStatus]);

                Log::channel('payment')->info("Pagamento cancelado para a ordem {$this->order->id}.");

                break;

            default:
                Log::channel('payment')->info("Evento de pagamento não tratado: $event");
                return false;
        }

        return true;
    }

    private function updatePaymentSteps()
    {
        //para aplicar o cupom de desconto somente na primeira mensalidade, descomente abaixo
        if ($this->order->changed_plan /*|| $this->order->value != $plan->value*/) {
            UpdateSubscriptionAfterProportionalPayJob::dispatch($this->order);
        }

        $this->order->update([
            'status' => StatusOrderAsaasEnum::ACTIVE,
            'payment_asaas_id' => $this->paymentId,
            'payment_status' => $this->paymentStatus,
            'next_due_date' => $this->dueDate,
            'payment_date' => $this->paymentDate,
        ]);

        Log::channel('payment')->info("Pagamento confirmado para a ordem {$this->order->id}.");
        //este if impede que seja enviado cupom de desconto durante a troca de plano
        // remova o if depois de implementar cupom na troca de plano
        if (!$this->order->changed_plan) {
            $customer = \App\Models\Customer::find($this->order->customer_id);
            $packagesToCreate = [];
            if ($customer->coupon_id != null) {
                $coupon = Coupon::find($customer->coupon_id);

                $packagesToCreate[] = $coupon->cod;
            }
        }
        foreach ($this->order->plan->packagePlans as $packagePlan) {
            $pack = Package::find($packagePlan->package_id);
            $packagesToCreate[] = $pack->cod;
        };

        $planInYoucast = (new PlanHistory())->handle($this->order->customer->viewers_id);

        if ($planInYoucast['response']) {
            foreach ($planInYoucast['response'] as $item) {
                $planExists = in_array($item['viewers_bouquets_products_id'], $packagesToCreate);

                //verifica se o plano de suspensão está ativo e remove ele
                $suspension = $this->getSuspension();

                if ($suspension && $item['viewers_bouquets_products_id'] == $suspension->cod && $item['viewers_bouquets_cancelled'] == 0) {
                    $planToCancel = [$suspension->cod];
                    (new PlanCancelService($planToCancel, $this->order->customer->viewers_id))->cancelPlan();
                }

                //ativa novamente os planos cancelados que pertencem ao pedido pago
                if (!$planExists || $item['viewers_bouquets_cancelled'] == 1) {
                    (new PlanCreateService($packagesToCreate, $this->order->customer->viewers_id))->createPlan();
                }
            }
        };

        (new UserEnable())->handle($this->order->customer->document);

        Log::channel('payment')->info('Usuário ativado com sucesso no Alloyal');
    }

    private function getSuspension(): ?Package
    {
        return (new Package())->getSuspensionPackage();
    }
}
