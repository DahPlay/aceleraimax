<?php

namespace App\Models;

use App\Enums\CycleAsaasEnum;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Plan extends Model
{
    use HasFactory;
    protected $fillable = [
        'id',
        'name',
        'value',
        'description',
        'is_active',
        'is_best_seller',
        'cycle',
        'billing_type',
        'free_for_days',
        'priority',
        'hidden',
        'is_active_telemedicine',
        'is_active_alloyal_in_free'
    ];

    protected function value(): Attribute
    {
        return Attribute::make(
            set: fn(string $value) => str_replace(['.', ','], ['', '.'], $value),
        );
    }

    public function orders(): HasMany
    {
        return $this->hasMany(Order::class);
    }

    public function benefits(): HasMany
    {
        return $this->hasMany(PlanBenefit::class);
    }

    public function packagePlans(): HasMany
    {
        return $this->hasMany(PackagePlan::class);
    }

    public static function getPlansData(?int $exceptId = null): array
    {
        $plans = Plan::query()
            ->where('is_active', 1)
            ->when($exceptId, function ($query, $exceptId) {
                return $query->where('id', '!=', $exceptId);
            })
            ->orderBy('value')
            ->get();

        $plansByCycle = $plans->groupBy('cycle');
        $cycles = $plansByCycle->keys()->mapWithKeys(fn($cycle) => [
            $cycle => CycleAsaasEnum::from($cycle)->getName()
        ]);

        $activeCycle = $plans->firstWhere('is_best_seller', true)?->cycle ?? $plansByCycle->keys()->first();

        return [
            'cycles' => $cycles,
            'plansByCycle' => $plansByCycle,
            'activeCycle' => $activeCycle,
        ];
    }
}
