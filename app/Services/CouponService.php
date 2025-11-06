<?php

namespace App\Services;

use App\Models\Coupon;
use App\Models\Plan;

class CouponService
{
    public function validate(string $couponName, int $planId): array
    {
        $plan = Plan::find($planId);
        $coupon = $this->getCoupon($couponName);

        if (!$coupon?->is_active || !$plan) {
            return [
                'valid' => false,
                'message' => 'Cupom inválido.',
            ];
        }

        $discountedValue = $this->getDiscount($plan, $coupon);
        $formattedValue = number_format($discountedValue, 2, ',', '.');

        if ($discountedValue > 0 && $discountedValue < 5) {
            return [
                'valid' => false,
                'message' => "O valor final de R$ {$formattedValue} após o cupom ser aplicado não pode ser menor que R$5,00.",
            ];
        }

        return [
            'valid' => true,
            'message' => "Cupom aplicado com sucesso! Você pagará R$ {$formattedValue}.",
            'discounted_value' => $formattedValue,
            'raw_value' => $discountedValue,
        ];
    }

    private function getCoupon(string $couponName): ?Coupon
    {
        return Coupon::where('name', $couponName)->first();
    }

    private function getDiscount(Plan $plan, Coupon $coupon): float
    {
        $planValue = (float) $plan->value;
        $percent = (float) $coupon->percent;

        $discountAmount = $planValue * ($percent / 100);
        $finalValue = $planValue - $discountAmount;

        return max(0, $finalValue);
    }
}
