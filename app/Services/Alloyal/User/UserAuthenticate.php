<?php

namespace App\Services\Alloyal\User;

use App\Services\Alloyal\Concerns\AlloyalClient;
use Illuminate\Support\Facades\Http;

class UserAuthenticate
{
    use AlloyalClient;

    public function handle(): string
    {
        $data = [
            "email" => $this->email,
            "password" => $this->password
        ];

        try {
            $response = Http::withHeaders([
                'accept' => 'application/json',
                'Content-Type' => 'application/json',
            ])->post("{$this->base_url}/client/v2/sign_in", $data);

            $body = $response->json();

            return $body['auth_token'];
        } catch (\Exception $e) {
            return false;
        }
    }
}
