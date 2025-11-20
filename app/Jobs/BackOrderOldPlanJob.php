<?php

namespace App\Jobs;

use App\Enums\PaymentStatusOrderAsaasEnum;
use App\Models\Customer;
use App\Models\Order;
use App\Models\OrderHistory;
use App\Models\Package;
use App\Models\PackagePlan;
use App\Services\AppIntegration\PlanCancelService;
use App\Services\AppIntegration\PlanCreateService;
use App\Services\PaymentGateway\Connectors\AsaasConnector;
use App\Services\PaymentGateway\Gateway;
use App\Services\YouCast\Plan\PlanHistory;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class BackOrderOldPlanJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public function __construct(private readonly Order $order) {}

    /*
     * Este job volta a assinatura para o plano anterior, caso o cliente não pague o upgrade
     */
    public function handle(): void
    {
        if (!$this->order->changed_plan) {
            return; // Já foi revertido ou não é upgrade
        }

        Log::channel('payment')->info('BackOrderOldPlan acionado');
        $oldOrder = OrderHistory::where('order_id', $this->order->id)
            ->orderByDesc('created_at')
            ->first();

        if ($oldOrder) {
            $adapter = app(AsaasConnector::class);
            $gateway = new Gateway($adapter);

            $paymentsFromSubscription = $gateway->subscription()->getPayments($this->order->subscription_asaas_id);

            foreach ($paymentsFromSubscription['data'] as $payment) {
                if ($payment['status'] == 'PENDING' || $payment['status'] == 'OVERDUE') {
                    $paymentDeleted = $gateway->payment()->delete($payment['id']);

                    logger(
                        $paymentDeleted['deleted']
                            ? "Pagamento removido no Asaas para atualização de plano. Pedido: {$this->order->id}"
                            : "Erro ao remover pagamento no Asaas para atualização de plano. Pedido: {$this->order->id}"
                    );
                }
            }

            $nextDueDate = $this->getNewDate($this->order->cycle);

            $data = [
                'id' => $this->order->subscription_asaas_id,
                'value' => $oldOrder->data['value'],
                'nextDueDate' => now()->addDays($nextDueDate),
                'description' => "Assinatura do plano  {$this->order->plan->name}",
                'externalReference' => 'Pedido: ' . $this->order->id,
                'updatePendingPayments' => true
            ];

            Log::channel('payment')->info('updateSubscriptionAfterProportionalPayJob acionado');

            $response = $gateway->subscription()->update($this->order->subscription_asaas_id, $data);

            if ($response['object'] === 'subscription') {
                $customer = Customer::where('id', $oldOrder->data['customer_id'])->first();
                if (!$customer) {
                    Log::channel('payment')->error('Customer não encontrado em BackOrderOldPlanJob');
                    return;
                }

                $youcast = (new PlanHistory())->handle($customer->viewers_id);
                if ($youcast["status"] == 1) {
                    $packagesToCancel = [];
                    $packagePlans = PackagePlan::where('plan_id', $this->order->plan_id)->get();
                    foreach ($packagePlans as $packagePlan) {
                        $pack = Package::find($packagePlan->package_id);
                        $packagesToCancel[] = $pack->cod;
                    };
                    foreach ($youcast['response'] as $item) {
                        //verifica se o plano de suspensão está ativo e remove ele
                        if ($item['viewers_bouquets_cancelled'] === 136 && $item['viewers_bouquets_cancelled'] === 0) {
                            $packagesToCancel[] = 136;
                        }
                    }

                    (new PlanCancelService($packagesToCancel, $customer->viewers_id))->cancelPlan();

                    //preciso voltar aos pacotes anteriores
                    $packagesToCreate = [];
                    $packagesOld = PackagePlan::where('plan_id', $oldOrder->data['plan_id'])->get();
                    foreach ($packagesOld as $packagePlan) {
                        $pack = Package::find($packagePlan->package_id);
                        $packagesToCreate[] = $pack->cod;
                    };
                    (new PlanCreateService($packagesToCreate, $customer->viewers_id))->createPlan();
                }

                $this->order->update([
                    "cycle" => $response['cycle'],
                    "value" => $response['value'],
                    "status" => $response['status'],
                    "plan_id" => $oldOrder['data']['plan_id'],
                    "end_date" => $oldOrder['data']['end_date'],
                    "description" => $response['description'],
                    "billing_type" => $response['billingType'],
                    "changed_plan" => false,
                    "payment_date" => null,
                    "next_due_date" => $response['nextDueDate'],
                    "payment_status" => PaymentStatusOrderAsaasEnum::RECEIVED,
                    "original_plan_value" => null,
                ]);
            }
        }
    }

    private function getNewDate($cycle): int
    {
        return match ($cycle) {
            'WEEKLY' => 7,
            'BIWEEKLY' => 14,
            'MONTHLY' => 30,
            'BIMONTHLY' => 60,
            'QUARTERLY' => 90,
            'SEMIANNUALLY' => 180,
            'YEARLY' => 365,
            default => 30,
        };
    }
}
