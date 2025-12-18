<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class PlanRequest extends FormRequest
{
    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:255'],
            'value' => ['required', 'string', 'min:0'],
            'description' => ['required', 'string'],
            'is_active' => ['nullable', 'string'],
            'cycle' => ['nullable', 'string'],
            'is_best_seller' => ['nullable', 'string'],
            'billing_type' => ['string'],
            'free_for_days' => ['integer'],
            'packages' => ['required', 'array'],
            'is_active_alloyal_in_free' => ['nullable', 'string'],
        ];
    }

    public function authorize(): bool
    {
        return true;
    }
}
