<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\Coupon;
use App\Models\Customer;
use App\Models\Plan;
use App\Models\Order;
use App\Models\User;
use App\Providers\RouteServiceProvider;
use App\Services\AppIntegration\CustomerService;
use App\Services\RegistrationService;
use Illuminate\Contracts\Validation\Validator as ValidationValidator;
use Illuminate\Foundation\Auth\RegistersUsers;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;
use GuzzleHttp\Client;

class RegisterController extends Controller
{
    use RegistersUsers;

    protected $redirectTo = RouteServiceProvider::HOME;

    public function __construct()
    {
        $this->middleware('guest');
    }

    public function showRegistrationForm(int|string $planId = null)
    {
        $planId = $planId ?: '';

        $plans = Plan::select(['id', 'name', 'value', 'is_active_telemedicine'])
            ->where('is_active', 1)
            ->get();
        $data = Plan::getPlansData();

        return view('auth.register', [
            'planId' => $planId,
            'plans' => $plans,
            'cycles' => $data['cycles'],
            'plansByCycle' => $data['plansByCycle'],
            'activeCycle' => $data['activeCycle']
        ]);
    }

    protected function validator(array $data): ValidationValidator
    {
        return Validator::make($data, [
            'id' => ['integer'],
            'name' => ['required', 'string'],
            'document' => ['required', new \App\Rules\Cpf()],
            'mobile' => ['required', 'string'],
            'birthdate' => ['date'],
            'email' => [
                'required',
                'string',
                function ($attribute, $value, $fail) use ($data) {
                    if (($data['source'] ?? null) === 'temporarily') {
                        return;
                    }

                    if (!filter_var($value, FILTER_VALIDATE_EMAIL)) {
                        $fail('O campo email deve conter um endereço de email válido.');
                    }

                    if (\App\Models\Customer::where('email', $value)->exists()) {
                        $fail('O email já está em uso.');
                    }
                }
            ],
            'login' => [
                'required',
                'string',
                function ($attribute, $value, $fail) use ($data) {
                    if (($data['source'] ?? null) === 'temporarily') {
                        return;
                    }

                    if (\App\Models\Customer::where('login', $value)->exists()) {
                        $fail('O login já está em uso.');
                    }
                }
            ],
            'password' => [
                function ($attribute, $value, $fail) use ($data) {
                    if (($data['source'] ?? null) !== 'temporarily' && empty($value)) {
                        $fail('O campo senha é obrigatório.');
                    }
                },
                'string',
                'confirmed',
            ],
            'credit_card_number' => ['required', new \App\Rules\CreditCard()],
            'credit_card_expiry_month' => ['required', 'digits:2'],
            'credit_card_expiry_year' => ['required', 'digits:4'],
            'credit_card_ccv' => ['required'],
        ]);
    }

    protected function create(array $data): User
    {
        return User::create([
            'name' => $data['name'],
            'email' => $data['email'],
            'password' => Hash::make($data['password']),
        ]);
    }

    public function register(Request $request, CustomerService $customerService, RegistrationService $registrationService)
    {
        $this->validator($request->all())->validate();

        $data = $request->only([
            'plan_id',
            'login',
            'password',
            'name',
            'document',
            'mobile',
            'email',
            'payment_asaas_id',
            'cpf_dependente_1',
            'cpf_dependente_2',
            'cpf_dependente_3',
            'credit_card_name',
            'credit_card_number',
            'credit_card_expiry_month',
            'credit_card_expiry_year',
            'credit_card_ccv',
            'coupon',
        ]);

        $couponName = $request->input('coupon');
        $planId = $request->input('plan_id');

        try {
            if ($couponName) {
                $plan = Plan::find($planId);
                $coupon = $this->getCoupon($couponName);

                if (!$coupon?->is_active || !$plan) {
                    Log::channel('registration')->info('Tentativa de registro com cupom inválido', [
                        'coupon' => $couponName,
                        'plan_id' => $planId,
                        'email' => $data['email'] ?? 'n/a',
                    ]);
                    toastr()->info("Cupom inválido.");
                    return back()->withInput()->withErrors(['error' => 'Ocorreu uma falha ao processar seu cadastro. Tente novamente.']);
                }

                $discountedValue = $this->getDiscount($plan, $coupon);
                $discountedValueFormat = number_format($discountedValue, 2, ',', '.');

                if ($discountedValue > 0 && $discountedValue <= 5) {
                    Log::channel('registration')->info('Tentativa de registro com valor final abaixo do mínimo', [
                        'coupon' => $couponName,
                        'plan_id' => $planId,
                        'final_value' => $discountedValue,
                        'email' => $data['email'] ?? 'n/a',
                    ]);
                    toastr()->info("O valor final de R$$discountedValueFormat após o cupom ser aplicado não pode ser menor que R$5,00.");
                    return back()->withInput()->withErrors(['error' => 'Ocorreu uma falha ao processar seu cadastro. Tente novamente.']);
                }
            }

            if (!session()->has('customerData')) {
                $externalCustomer = $this->verifyCustomerInYouCast($customerService);
                if ($externalCustomer instanceof RedirectResponse) {
                    Log::channel('registration')->info('Redirecionado por verificação YouCast', [
                        'email' => $data['email'] ?? 'n/a',
                    ]);
                    return $externalCustomer;
                }
            }

            $data['coupon_id'] = $this->getCoupon($request->coupon)?->id;

            Log::channel('registration')->info('Iniciando processo de registro', [
                'email' => $data['email'],
                'login' => $data['login'],
                'plan_id' => $data['plan_id'],
                'has_coupon' => !empty($data['coupon_id']),
            ]);

            $customer = $registrationService->handle($data);

            Log::channel('registration')->info('Registro concluído com sucesso', [
                'customer_id' => $customer->id,
                'email' => $customer->email,
                'asaas_id' => $customer->customer_id,
                'youcast_id' => $customer->viewers_id,
            ]);

            toastr()->success('Criado com sucesso! Acesse seu e-mail ou faça login.');
            return redirect('/login');
        } catch (\InvalidArgumentException $e) {
            Log::channel('registration')->warning('Falha na validação do registro', [
                'error' => $e->getMessage(),
                'email' => $data['email'] ?? 'n/a',
                'login' => $data['login'] ?? 'n/a',
            ]);
            return back()->withInput()->withErrors(['error' => $e->getMessage()]);
        } catch (\Exception $e) {
            Log::channel('registration')->error('Erro crítico no registro', [
                'message' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
                'email' => $data['email'] ?? 'n/a',
                'login' => $data['login'] ?? 'n/a',
            ]);
            toastr()->info($e->getMessage());
            return back()->withInput()->withErrors(['error' => 'Ocorreu uma falha ao processar seu cadastro. Tente novamente.']);
        }
    }

    private function verifyCustomerInYouCast(CustomerService $customerService): mixed
    {
        $login = request()->login;
        $password = request()->password;
        $couponName = request()->coupon;
        $coupon = $this->getCoupon($couponName);

        $externalCustomer = $customerService->findExternalCustomerByLogin($login, $password);

        if ($externalCustomer) {
            $authenticateExternalCustomer = $customerService->authenticateExternalCustomer($login, $password);

            $customerData = $externalCustomer['customer'];

            if ($authenticateExternalCustomer) {
                session([
                    'authenticate' => true,
                    'customerData' => $customerData,
                ]);

                return redirect()->route('login')->with([
                    'info' => 'Usuário localizado na plataforma de Streaming. Efetue o login ou recupere a senha.',
                ]);
            }

            $data = request()->only(['login', 'name', 'document', 'mobile', 'birthdate', 'email', 'cpf_dependente_1', 'cpf_dependente_2', 'cpf_dependente_3']);

            Customer::create([
                'viewers_id' => $customerData['viewers_id'],
                'login' => $customerData['login'],
                'name' => $customerData['name'],
                'cpf_dependente_1' => $customerData['cpf_dependente_1'] ?? null,
                'cpf_dependente_2' => $customerData['cpf_dependente_2'] ?? null,
                'cpf_dependente_3' => $customerData['cpf_dependente_3'] ?? null,
                'email' => $data['email'],
                'coupon_id' => $coupon->id ?? null,
            ]);

            return redirect()->route('login')->with([
                'error' => 'Usuário localizado na plataforma de Streaming. Login ou senha incorretos. Tente novamente ou clique em recuperar senha informando o email cadastrado: ' . $customerData['email'],
            ]);
        }

        return null;
    }

    private function getCoupon(mixed $couponName): ?Coupon
    {
        return Coupon::where('name', $couponName)->first();
    }

    private function getDiscount(Plan $plan, Coupon $coupon): mixed
    {
        return $plan->value - ($plan->value * ($coupon->percent / 100));
    }
}
