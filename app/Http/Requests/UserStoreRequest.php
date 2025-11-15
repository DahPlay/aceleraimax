<?php

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;

class UserStoreRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'photo' => 'nullable|image|mimes:jpeg,jpg,png|max:500',
            'name' => [
                'required',
                'string',
                'max:255',
                'regex:/^[a-zA-ZÀ-ÿ\s]+ [a-zA-ZÀ-ÿ\s]+$/',
                function ($attribute, $value, $fail) {
                    $parts = array_filter(array_map('trim', explode(' ', $value)));
                    if (count($parts) < 2) {
                        $fail('O campo Nome deve conter nome e sobrenome.');
                    }
                },
            ],
            'email' => ['required', 'email', 'max:200', 'unique:users'],
            'login' => ['nullable', 'string', 'max:50'],
            'password' => ['required', 'string', 'min:4', 'confirmed'],
            'password_confirmation' => ['required', 'string'],
            'access_id' => ['required', 'integer'],
            'document' => ['required'],
            'mobile' => ['required'],
        ];
    }

    protected function failedValidation(Validator $validator)
    {
        throw new HttpResponseException(response()->json([
            'status' => 400,
            'errors' => $validator->errors()
        ]));
    }
}
