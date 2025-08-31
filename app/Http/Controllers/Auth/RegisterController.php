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

    public function validateCoupon(Request $request)
    {
        $couponName = $request->input('coupon');
        $planId = $request->input('plan_id');

        $plan = Plan::find($planId);
        $coupon = $this->getCoupon($couponName);

        if (!$coupon->is_active || !$plan) {
            return response()->json(['valid' => false, 'message' => 'Cupom inválido.']);
        }

        $discountedValue = $this->getDiscount($plan, $coupon);

        return response()->json([
            'valid' => true,
            'discounted_value' => number_format($discountedValue, 2, ',', '.'),
            'raw_value' => $discountedValue,
            'message' => 'Cupom aplicado com sucesso! Você pagará R$ ' . number_format($discountedValue, 2, ',', '.'),
        ]);
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



    public function register(Request $request, CustomerService $customerService)
    {
        $this->validator($request->all())->validate();

        $data = $request->only(['login', 'name', 'document', 'mobile', 'birthdate', 'email', 'payment_asaas_id', 'cpf_dependente_1', 'cpf_dependente_2', 'cpf_dependente_3']);

        if (!session()->has('customerData')) {
            $externalCustomer = $this->verifyCustomerInYouCast($customerService);

            if ($externalCustomer instanceof RedirectResponse) {
                return $externalCustomer;
            }
        }

        $data['coupon_id'] = $this->getCoupon($request->coupon)->id ?? null;

        try {
            DB::beginTransaction();
            $customer = Customer::updateOrCreate(
                ['login' => $data['login']],
                $data
            );
            DB::commit();
        } catch (\Illuminate\Database\QueryException $e) {
            if ($e->getCode() === '23000') {
                $errorMessage = $e->getMessage();

                if (strpos($errorMessage, 'customers_document_unique') !== false) {
                    return back()->withInput()->withErrors(['document' => 'O CPF/CNPJ informado já está cadastrado.']);
                }

                if (strpos($errorMessage, 'customers_login_unique') !== false) {
                    return back()->withInput()->withErrors(['login' => 'O login informado já está em uso.']);
                }
            }

            Log::error("RegisterController - linha - 138: {$e->getMessage()}");

            return back()->withInput()->withErrors(['error' => 'Ocorreu um erro ao processar o registro. Tente novamente mais tarde.']);
        } catch (\Exception $e) {
            Log::error("RegisterController - linha - 179: {$e->getMessage()}");
            DB::rollBack();
            return back()->withInput()->withErrors(['error' => $e->getMessage()]);
        }

        toastr()->success('Criado com sucesso, Acesse seu email ou faça o login para visualizar sua Assinatura!');

        session()->forget('customerData');

        $login = $data['login'];
        return redirect('/login?login=' . $login);

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
