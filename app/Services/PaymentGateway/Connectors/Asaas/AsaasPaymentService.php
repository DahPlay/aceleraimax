<?php

namespace App\Services\PaymentGateway\Connectors\Asaas;

use App\Enums\StatusOrderAsaasEnum;
use App\Jobs\BackOrderOldPlanJob;
use App\Jobs\updateSubscriptionAfterProportionalPayJob;
use App\Models\Coupon;
use App\Models\Order;
use App\Models\Package;
use App\Services\Alloyal\User\UserDisable;
use App\Services\AppIntegration\PlanCancelService;
use App\Services\AppIntegration\PlanCreateService;
use App\Services\YouCast\Plan\PlanHistory;
use App\Services\YouCast\Plan\PlanList;
use Illuminate\Support\Facades\Log;

class AsaasPaymentService
{
    public function processEvent(string $event, array $data): bool
    {
        $paymentId = $data['payment']['id'];
        $customerId = $data['payment']['customer'];
        $subscriptionId = $data['payment']['subscription'];
        $paymentStatus = $data['payment']['status'];
        $dueDate = $data['payment']['dueDate'];
        $paymentDate = $data['payment']['paymentDate'];

        $order = Order::where('subscription_asaas_id', $subscriptionId)->first();

        Log::info('AsaasPaymentService acionado');
        if (!$order) {
            Log::warning("Ordem não encontrada para a assinatura $subscriptionId no evento $event.");
            return false;
        }

        switch ($event) {
            case 'PAYMENT_RECEIVED':
//para aplicar o cupom de desconto somente na primeira mensalidade, descomente abaixo
                if ($order->changed_plan /*|| $order->value != $plan->value*/) {
                    updateSubscriptionAfterProportionalPayJob::dispatch($order);
                }
                $order->update([
                    'status' => StatusOrderAsaasEnum::ACTIVE,
                    'payment_asaas_id' => $paymentId,
                    'payment_status' => $paymentStatus,
                    'next_due_date' => $dueDate,
                    'payment_date' => $paymentDate,
                ]);

                Log::info("Pagamento confirmado para a ordem {$order->id}.");
                //este if impede que seja enviado cupom de desconto durante a troca de plano
                // remova o if depois de implementar cupom na troca de plano
                if (!$order->changed_plan) {
                    $customer = \App\Models\Customer::find($order->customer_id);
                    $packagesToCreate = [];
                    if ($customer->coupon_id != null) {
                        $coupon = Coupon::find($customer->coupon_id);

                        $packagesToCreate[] = $coupon->cod;
                    }
                }
                foreach ($order->plan->packagePlans as $packagePlan) {
                    $pack = Package::find($packagePlan->package_id);
                    $packagesToCreate[] = $pack->cod;
                };

                $planInYoucast = (new PlanHistory())->handle($order->customer->viewers_id);

                if ($planInYoucast['response']) {
                    foreach ($planInYoucast['response'] as $item) {
                        $planExists = in_array($item['viewers_bouquets_products_id'], $packagesToCreate);

                        //verifica se o plano de suspensão está ativo e remove ele
                        $suspension = $this->getSuspension();

                        if ($suspension && $item['viewers_bouquets_products_id'] == $suspension->cod && $item['viewers_bouquets_cancelled'] == 0) {
                            $planToCancel = [$suspension->cod];
                            (new PlanCancelService($planToCancel, $order->customer->viewers_id))->cancelPlan();
                        }

                        //ativa novamente os planos cancelados que pertencem ao pedido pago
                        if (!$planExists || $item['viewers_bouquets_cancelled'] == 1) {
                            (new PlanCreateService($packagesToCreate, $order->customer->viewers_id))->createPlan();
                        }
                    }
                };
                break;

            case 'PAYMENT_CREATED':
                $order->update([
                    'payment_asaas_id' => $paymentId,
                    'payment_status' => $paymentStatus,
                    'next_due_date' => $dueDate,
                ]);

                Log::info("Pagamento criado para a ordem {$order->id}.");
                break;

            case 'PAYMENT_CONFIRMED':
                $order->update([
                    'payment_status' => $paymentStatus,
                ]);

                Log::info("Pagamento criado para a ordem {$order->id}.");

                break;

            case 'PAYMENT_OVERDUE':

                if ($order->changed_plan) {
                    BackOrderOldPlanJob::dispatch($order);
                    break;
                }
                $order->update(
                    ['status' => 'INACTIVE'],
                    ['payment_status' => $paymentStatus]
                );

                $cpf = $order->customer?->document ?? '';

                $alloyalResponse = (new UserDisable())->handle($cpf);

                Log::warning("Pagamento atrasado para a ordem {$order->id}.");

                $youcast = (new PlanList)->handle($order->customer->viewers_id);

                if ($youcast["status"] == 1) {
                    $packagesToCancel = [];
                    foreach ($order->plan->packagePlans as $packagePlan) {
                        $pack = Package::find($packagePlan->package_id);
                        $packagesToCancel[] = $pack->cod;
                    };
                    (new PlanCancelService($packagesToCancel, $order->customer->viewers_id))->cancelPlan();

                    //adiciona o pacote de suspensão
                    $suspension = $this->getSuspension();
                    if ($suspension) {
                        $packagesToCreate = [$suspension->cod];
                        (new PlanCreateService($packagesToCreate, $order->customer->viewers_id))->createPlan();
                    }
                }

                break;

            case 'PAYMENT_DELETED':
                $order->update(['payment_status' => $paymentStatus]);

                Log::info("AssasPaymentService - linha 91 - Pagamento cancelado para a ordem {$order->id}.");

                break;

            default:
                Log::info("Evento de pagamento não tratado: $event");
                return false;
        }

        return true;
    }

    private function getSuspension(): ?Package
    {
        return (new Package())->getSuspensionPackage();
    }
}
