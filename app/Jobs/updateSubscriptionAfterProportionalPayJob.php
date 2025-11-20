<?php

namespace App\Jobs;

use App\Models\Order;
use App\Services\PaymentGateway\Connectors\AsaasConnector;
use App\Services\PaymentGateway\Gateway;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class updateSubscriptionAfterProportionalPayJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public function __construct(private readonly Order $order) {}

    public function handle(): void
    {
        $adapter = app(AsaasConnector::class);
        $gateway = new Gateway($adapter);

        $nextDueDate = $this->getNewDate($this->order->cycle);
        $data = [
            'id' => $this->order->subscription_asaas_id,
            'value' => $this->order->original_plan_value,
            'nextDueDate' => now()->addDays($nextDueDate),
            'description' => "Assinatura do plano  {$this->order->plan->name}",
            'externalReference' => 'Pedido: ' . $this->order->id,
        ];

        Log::channel('payment')->info('updateSubscriptionAfterProportionalPayJob acionado');

        $response = $gateway->subscription()->update($this->order->subscription_asaas_id, $data);

        if ($response['object'] === 'subscription') {
            $this->order->update([
                'value' => $this->order->original_plan_value,
                'changed_plan' => false
            ]);
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
