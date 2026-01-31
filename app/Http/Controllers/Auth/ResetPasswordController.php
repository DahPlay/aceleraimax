<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\Customer;
use App\Models\User;
use App\Providers\RouteServiceProvider;
use App\Services\YouCast\Customer\CustomerUpdate;
use Illuminate\Foundation\Auth\ResetsPasswords;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Password;
use Illuminate\Validation\Rules\Password as PasswordRules;

class ResetPasswordController extends Controller
{
    /*
    |--------------------------------------------------------------------------
    | Password Reset Controller
    |--------------------------------------------------------------------------
    |
    | This controller is responsible for handling password reset requests
    | and uses a simple trait to include this behavior. You're free to
    | explore this trait and override any methods you wish to tweak.
    |
    */

    use ResetsPasswords;

    protected $redirectTo = RouteServiceProvider::HOME;

    protected function rules()
    {
        return [
            'token' => 'required',
            'email' => 'required|email',
            'password' => [
                'required',
                'string',
                'confirmed',
                PasswordRules::min(6)
                    ->mixedCase()
                    ->numbers()
                    ->symbols(),
            ],
        ];
    }

    protected function validationErrorMessages()
    {
        return [
            'email.required' => 'O campo email é obrigatório.',
            'email.email' => 'O campo email deve conter um endereço de email válido.',
            'password.required' => 'O campo senha é obrigatório.',
            'password.confirmed' => 'As senhas não conferem.',
            'token.required' => 'Token de redefinição é obrigatório.',
        ];
    }

    public function reset(Request $request)
    {
        // Valida a solicitação antes de continuar
        $request->validate($this->rules(), $this->validationErrorMessages());

        $credentials = $this->credentials($request);

        $user = $this->broker()->getUser($credentials);

        if (!$user) {
            return $this->sendResetFailedResponse($request, Password::INVALID_USER);
        }

        $response = $this->broker()->reset(
            $credentials,
            function ($user) use ($request) {
                $this->resetPassword($user, $request->input('password'));
            }
        );

        if ($response === Password::PASSWORD_RESET && $user->customer) {
            $this->updateCustomerInYouCast($user->customer);
        }

        return $response === Password::PASSWORD_RESET
            ? redirect()->route('login')->with('status', trans($response))->with([
                'info' => 'Senha alterada. Faça o login.',
            ])
            : $this->sendResetFailedResponse($request, $response);
    }

    protected function resetPassword(User $user, $password)
    {
        $user->password = bcrypt($password);
        $user->save();
    }

    private function updateCustomerInYouCast(Customer $customer): void
    {
        $response = (new CustomerUpdate())->handle($customer);

        if ($response['status'] !== 1) {
            Log::error('Erro ao atualizar o customer no YouCast', [
                'customer_id' => $customer->id,
                'response' => $response,
            ]);
        }
    }
}
