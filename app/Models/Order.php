<?php

namespace App\Models;

use App\Enums\BillingTypeAsaasEnum;
use App\Enums\CycleAsaasEnum;
use App\Enums\PaymentStatusOrderAsaasEnum;
use App\Enums\StatusOrderAsaasEnum;
use App\Observers\OrderObserver;
use App\Traits\BelongsUserScope;
use Illuminate\Database\Eloquent\Attributes\ObservedBy;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

#[ObservedBy(OrderObserver::class)]
class Order extends Model
{
    use BelongsUserScope;

    protected $casts = [
        'next_due_date' => 'datetime',
        'changed_plan' => 'boolean',
    ];

    protected $fillable = [
        'customer_id',
        'plan_id',
        'customer_asaas_id',
        'subscription_asaas_id',
        'payment_asaas_id',
        'value',
        'cycle',
        'billing_type',
        'next_due_date',
        'end_date',
        'status',
        'payment_status',
        'description',
        'payment_date',
        'changed_plan',
        'deleted_date',
        'original_plan_value',
        'boleto_url',
        'consent_id',
    ];

    protected function cycle(): Attribute
    {
        return Attribute::make(
            get: fn($value) => CycleAsaasEnum::from($value)->getName(),
        );
    }

    protected function status(): Attribute
    {
        return Attribute::make(
            get: fn($value) => StatusOrderAsaasEnum::from($value)->getName(),
        );
    }

    protected function paymentStatus(): Attribute
    {
        return Attribute::make(
            get: fn($value) => PaymentStatusOrderAsaasEnum::from($value)->getName(),
        );
    }

    protected function billingType(): Attribute
    {
        return Attribute::make(
            get: fn($value) => BillingTypeAsaasEnum::from($value)->getName()
        );
    }

    public function orderHistories(): HasMany
    {
        return $this->hasMany(OrderHistory::class);
    }

    public function customer(): BelongsTo
    {
        return $this->belongsTo(Customer::class);
    }

    public function plan(): BelongsTo
    {
        return $this->belongsTo(Plan::class);
    }

    public function hasPlan(int $planId, int $customer_id): bool
    {
        $order = Order::where('customer_id', $customer_id)
            ->where('plan_id', $planId)
            ->get();

        if ($order->count() > 0) {
            return true;
        }
        return false;
    }

    public function consent()
    {
        return $this->belongsTo(UserConsent::class);
    }
}
