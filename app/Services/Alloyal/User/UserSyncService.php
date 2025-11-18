<?php

namespace App\Services\Alloyal\User;

use App\Models\User;
use App\Services\Alloyal\User\UserCreate;
use App\Services\Alloyal\User\UserDetails;
use Illuminate\Support\Facades\Log;

class UserSyncService
{
    public function __construct(
        private UserDetails $userDetails,
        private UserCreate $userCreate
    ) {}

    public function ensureUserExistsInAlloyal(User $localUser, ?string $plainPassword = null): void
    {
        $cpf = $localUser->customer->document;
        $startTime = microtime(true);

        Log::channel('alloyal')->debug('Início de sincronização de usuário com Alloyal', [
            'user_id' => $localUser->id,
            'cpf' => $cpf,
            'timestamp' => now()->toDateTimeString(),
        ]);

        try {
            $alloyalUser = $this->userDetails->handle($cpf);

            if ($alloyalUser === null) {
                $payload = [
                    'name' => $localUser->name,
                    'email' => $localUser->email,
                    'cpf' => $cpf,
                    'cellphone' => $localUser->customer->mobile,
                    'password' => $plainPassword,
                ];

                $this->userCreate->handle($payload);
            } else {
                $durationMs = (microtime(true) - $startTime) * 1000;
                Log::channel('alloyal')->debug('Usuário já existente no Alloyal (sincronização não necessária)', [
                    'user_id' => $localUser->id,
                    'cpf' => $cpf,
                    'alloyal_user_id' => $alloyalUser['id'] ?? null,
                    'response_time_ms' => round($durationMs, 2),
                ]);
            }
        } catch (\Exception $e) {
            $durationMs = (microtime(true) - $startTime) * 1000;

            Log::channel('alloyal')->error('Falha ao sincronizar usuário com Alloyal', [
                'user_id' => $localUser->id,
                'cpf' => $cpf,
                'exception_class' => get_class($e),
                'exception_message' => $e->getMessage(),
                'response_time_ms' => round($durationMs, 2),
            ]);
        }
    }
}
