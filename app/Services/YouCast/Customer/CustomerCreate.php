<?php

namespace App\Services\YouCast\Customer;

use App\Models\Customer;
use App\Services\YouCast\Concerns\YouCastClient;
use Illuminate\Support\Facades\Http;

class CustomerCreate
{
    use YouCastClient;

    public function handle(Customer $customer): array
    {
        $nameParts = $this->fullName($customer->name);

        $data = [
            'data' => [
                'login' => $customer->login,
                'password' => request()->password,
                'profileName' => $customer->name,
                'email' => $customer->email,
                "firstname" => $nameParts[0],
                "lastname" => count($nameParts) > 1
                    ? implode(' ', array_slice($nameParts, 1))
                    : '',
            ]
        ];

        try {
            $response = Http::withHeaders([
                'accept' => 'application/json',
                'authorization' => $this->token,
            ])->post("{$this->url}/api/integration/createMotvCustomer", $data);

            return $response->json();
        } catch (\Exception $e) {
            return ['error' => $e->getMessage()];
        }
    }

    private function fullName($name): array
    {
        return explode(' ', $name);
    }
}
