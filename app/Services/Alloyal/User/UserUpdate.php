<?php

namespace App\Services\Alloyal\User;

use App\Services\Alloyal\Concerns\AlloyalClient;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class UserUpdate
{
    use AlloyalClient;

    public function handle(array $data): array
    {
        $cpf = $data['cpf'] ?? '';
        $cpf = $this->cleanCPF($cpf);

        $phone = $data['cellphone'] ?? '';

        Log::channel('alloyal')->debug('Início da atualização de usuário no Alloyal', [
            'name' => $data['name'] ?? 'N/A',
            'email' => $data['email'] ?? 'N/A',
            'cpf' => $cpf,
            'cellphone_clean' => $phone,
            'timestamp' => now()->toDateTimeString(),
        ]);

        $payload = [
            'name' => $data['name'] ?? '',
            'email' => $data['email'] ?? '',
            'cpf' => $cpf,
            'cellphone' => $phone,
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
            ])->timeout(30)->patch(
                "{$this->base_url}/client/v2/businesses/{$this->business_id}/users/{$cpf}",
                $payload
            );

            $durationMs = (microtime(true) - $startTime) * 1000;

            $responseData = $response->json() ?? [];

            if ($response->successful()) {
                Log::channel('alloyal')->info('Usuário atualizado com sucesso no Alloyal', [
                    'cpf' => $cpf,
                    'status_code' => $response->status(),
                    'response_time_ms' => round($durationMs, 2),
                    'alloyal_user_id' => $responseData['id'] ?? null,
                ]);

                return $responseData;
            } else {
                $errorJson = $response->json() ?? [];

                Log::channel('alloyal')->warning('Falha na atualização no Alloyal', [
                    'cpf' => $cpf,
                    'status_code' => $response->status(),
                    'error_summary' => $errorJson['error'] ?? 'Sem corpo na resposta',
                    'response_time_ms' => round($durationMs, 2),
                ]);

                return [
                    'status' => 400,
                    'errors' => 'Alloyal: ' . $errorJson['error'],
                ];
            }
        } catch (\Exception $e) {
            Log::channel('alloyal')->error('Exceção ao atualizar usuário no Alloyal', [
                'cpf' => $cpf,
                'exception_class' => get_class($e),
                'exception_message' => $e->getMessage(),
            ]);

            return [
                'status' => 400,
                'errors' => 'Alloyal: ' . $e->getMessage(),
            ];
        }
    }

    private function cleanCPF(string $document): string
    {
        return preg_replace('/[^0-9]/', '', $document);
    }
}
