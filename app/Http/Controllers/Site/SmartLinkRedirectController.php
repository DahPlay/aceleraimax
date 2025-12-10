<?php

namespace App\Http\Controllers\Site;

use App\Http\Controllers\Controller;
use App\Models\Customer;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;
use Motv\Connector\Mw\AdminConnector;

class SmartLinkRedirectController extends Controller
{
    public function redirectToSmartLink(string $customerId, Request $request): RedirectResponse
    {
        try {
            Validator::make(['customerId' => $customerId], [
                'customerId' => 'required|numeric|min:1',
            ])->validate();

            $customerId = (int) $customerId;

            $referer = $request->header('Referer');
            $userAgent = $request->userAgent() ?? '';

            $isFromPortal = $referer && str_starts_with(trim($referer), 'https://portal.aceleraimax.com/');

            $isFromApp =
                $referer === null
                && (
                    str_contains($userAgent, 'wv')
                    || str_contains($userAgent, 'iPhone') && !str_contains($userAgent, 'Safari/')
                    || str_contains($userAgent, 'CriOS')
                );

            if (!$isFromPortal && !$isFromApp) {
                Log::warning('Acesso suspeito ao smart-link', [
                    'ip' => $request->ip(),
                    'referer' => $referer,
                    'user_agent' => $userAgent,
                    'customer_id' => $customerId,
                ]);

                session()->flash('error', 'Não foi possível acessar o clube de benefício, por favor falar com o suporte.');

                return redirect()->to(env('APP_URL') . '/login');
            } else {
                Log::info('Smart Link access autorizado', [
                    'source' => $isFromApp ? 'app' : 'portal',
                    'customer_id' => $customerId,
                ]);
            }

            $mwAdminConnector = new AdminConnector(
                config('youcast.dahplay_mw.url'),
                config('youcast.dahplay_mw.email'),
                config('youcast.dahplay_mw.secret')
            );

            $customerData = $mwAdminConnector->Customer()->getData($customerId);

            if (empty($customerData) || !isset($customerData->customers_login)) {
                Log::warning('Cliente MW não encontrado ou desaparecido viewers_id', [
                    'mw_customer_id' => $customerId,
                    'response' => $customerData,
                ]);

                session()->flash('error', 'Não foi possível acessar o clube de benefício, por favor falar com o suporte.');

                return redirect()->to(env('APP_URL') . '/login');
            }

            $login = $customerData->customers_login;

            $customer = Customer::where('login', $login)->first();

            if (!$customer) {
                Log::warning('Cliente local não encontrado para viewers_id', [
                    'login' => $login,
                    'mw_customer_id' => $customerId,
                ]);

                session()->flash('error', 'Não foi possível acessar o clube de benefício, por favor falar com o suporte.');

                return redirect()->to(env('APP_URL') . '/login');
            }

            $this->attemptCreateSmartLink($customer->user_id);

            $customer->refresh();

            $smartLink = $customer->web_smart_link;

            if (empty($smartLink)) {
                Log::error('Smart Link ainda vazio após tentativa de atualização', [
                    'mw_customer_id' => $customerId,
                    'login' => $login,
                ]);

                session()->flash('error', 'Não foi possível acessar o clube de benefício, por favor falar com o suporte.');

                return redirect()->to(env('APP_URL') . '/login');
            }

            return redirect()->away($smartLink);
        } catch (\Throwable $e) {
            Log::error('Erro no redirecionamento de Smart Link', [
                'customerId' => $customerId,
                'exception' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            return redirect()->away(env('APP_URL') . '/login')->withErrors('Não foi possível acessar o clube de benefício, por favor falar com o suporte.');
        }
    }

    protected function attemptCreateSmartLink(int $userId): bool
    {
        try {
            $service = app(\App\Services\SmartLinkService::class);
            $result = $service->createForUser($userId);

            return !isset($result['errors']);
        } catch (\Throwable $e) {
            Log::error('Falha ao criar Smart Link em tempo real', [
                'user_id' => $userId,
                'exception' => $e->getMessage(),
            ]);
            return false;
        }
    }
}
