<?php

namespace App\Services\YouCast\Customer;

use App\Services\YouCast\Concerns\YouCastClient;
use Illuminate\Support\Facades\Http;

class CustomerAuthenticate
{
    use YouCastClient;

    public function handle(string $login, string $password): array
    {
        $data = [
            'data' => [
                'vendors_id' => 1,
                'login' => $login,
                'password' => $password,
            ]
        ];

        try {
            $response = Http::withHeaders([
                'accept' => 'application/json',
                'authorization' => $this->token,
            ])->post("{$this->url}/api/devices/motv/apiLoginV2", $data);

            return $response->json()['response'];
        } catch (\Exception $e) {
            return [];
        }
    }
}
