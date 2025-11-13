<?php

namespace App\Services\Alloyal\User;

use App\Services\Alloyal\Concerns\AlloyalClient;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class UserDetails
{
    use AlloyalClient;

    public function handle(string $cpf): ?array
    {
        $cpf = $this->cleanCPF($cpf);

        Log::channel('alloyal')->debug('Buscando usuário no Alloyal por CPF', [
            'cpf' => $cpf,
        ]);

        try {
            $startTime = microtime(true);

            $response = Http::withHeaders([
                'accept' => 'application/json',
                'Content-Type' => 'application/json',
                'X-ClientEmployee-Email' => $this->email,
                'X-ClientEmployee-Token' => $this->token,
            ])->timeout(30)->get(
                "{$this->base_url}/client/v2/businesses/{$this->business_id}/users/{$cpf}"
            );

            $durationMs = (microtime(true) - $startTime) * 1000;

            if ($response->successful()) {
                $data = $response->json();

                Log::channel('alloyal')->info('Usuário encontrado no Alloyal', [
                    'cpf' => $cpf,
                    'alloyal_id' => $data['id'] ?? null,
                    'response_time_ms' => round($durationMs, 2),
                ]);

                return $data;
            }

            if ($response->status() === 404) {
                Log::channel('alloyal')->debug('Usuário NÃO encontrado no Alloyal', [
                    'cpf' => $cpf,
                    'response_time_ms' => round($durationMs, 2),
                ]);
                return null;
            }

            $error = $response->json()['error'] ?? 'Erro desconhecido';
            throw new \RuntimeException("Alloyal API error ({$response->status()}): $error");
        } catch (\Exception $e) {
            Log::channel('alloyal')->error('Falha na consulta ao Alloyal', [
                'cpf' => $cpf,
                'exception' => $e->getMessage(),
            ]);
            throw $e;
        }
    }

    private function cleanCPF(string $document): string
    {
        return preg_replace('/[^0-9]/', '', $document);
    }
}
