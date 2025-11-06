<?php

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;

class CouponRequest extends FormRequest
{
    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:255', 'min:3'],
            'percent' => ['required', 'numeric', 'min:0', 'max:100'],
            'cod' => ['required','numeric'],
            'observation' => ['nullable'],
            'is_active' => ['filled', 'boolean'],
        ];
    }

    protected function failedValidation(Validator $validator)
    {
        throw new HttpResponseException(response()->json([
            'status' => 400,
            'errors' => $validator->errors()
        ], 400));
    }

    public function authorize(): bool
    {
        return true;
    }
}
