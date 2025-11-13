<?php

namespace App\Services\Alloyal\User;

use App\Services\Alloyal\Concerns\AlloyalClient;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class UserCreate
{
    use AlloyalClient;

    public function handle(array $data): array
    {
        $cpf = $data['cpf'] ?? '';
        $cpf = $this->cleanDocument($cpf);

        $phone = $data['cellphone'] ?? '';

        Log::channel('alloyal')->debug('Início da criação de usuário no Alloyal', [
            'name' => $data['name'] ?? 'N/A',
            'email' => $data['email'] ?? 'N/A',
            'cpf_clean' => $cpf,
            'cellphone_clean' => $phone,
            'timestamp' => now()->toDateTimeString(),
        ]);

        $payload = [
            'name' => $data['name'] ?? '',
            'email' => $data['email'] ?? '',
            'cpf' => $cpf,
            'phone' => $phone,
        ];

        if (isset($data['password'])) {
            $payload['password'] = $data['password'];
        }

        try {
            $startTime = microtime(true);

            $response = Http::withHeaders([
                'accept' => 'application/json',
                'Content-Type' => 'application/json',
                'X-ClientEmployee-Email' => $this->email,
                'X-ClientEmployee-Token' => $this->token,
            ])->timeout(30)->post(
                "{$this->base_url}/client/v2/businesses/{$this->business_id}/authorized_users",
                $payload
            );

            $durationMs = (microtime(true) - $startTime) * 1000;

            $responseData = $response->json() ?? [];

            if ($response->successful()) {
                Log::channel('alloyal')->info('Usuário criado com sucesso no Alloyal', [
                    'alloyal_user_id' => $responseData['id'] ?? null,
                    'cpf_clean' => $cpf,
                    'status_code' => $response->status(),
                    'response_time_ms' => round($durationMs, 2),
                ]);

                return $responseData;
            } else {
                $errorJson = $response->json() ?? [];

                Log::channel('alloyal')->error('Falha na criação no Alloyal', [
                    'cpf_clean' => $cpf,
                    'status_code' => $response->status(),
                    'error_summary' => $errorJson['message'] ?? $errorJson['error'] ?? 'Sem corpo na resposta',
                    'response_time_ms' => round($durationMs, 2),
                ]);

                return [
                    'status' => 400,
                    'errors' => 'Alloyal: ' . $errorJson['error'],
                ];
            }
        } catch (\Exception $e) {
            Log::channel('alloyal')->critical('Exceção ao criar usuário no Alloyal', [
                'cpf_clean' => $cpf,
                'exception_class' => get_class($e),
                'exception_message' => $e->getMessage(),
            ]);

            return [
                'status' => 400,
                'errors' => $e->getMessage(),
            ];
        }
    }

    private function cleanDocument(string $document): string
    {
        return preg_replace('/[^0-9]/', '', $document);
    }
}
