<?php

namespace App\Services\YouCast\User;

use App\Services\YouCast\Concerns\YouCastClient;
use Illuminate\Support\Facades\Http;

class UserAuthenticate
{
    use YouCastClient;

    public function handle(string $userLogin, string $userPass): bool
    {
        $data = [
            'data' => [
                'vendors_id' => 1,
                'login' => $userLogin,
                'password' => $userPass,
            ]
        ];

        try {
            $response = Http::withHeaders([
                'accept' => 'application/json',
                'authorization' => $this->token,
            ])->post("{$this->url}/api/devices/motv/apiLoginV2", $data);

            $body = $response->json();

            return $body['status'] == 1;
        } catch (\Exception $e) {
            return false;
        }
    }
}
