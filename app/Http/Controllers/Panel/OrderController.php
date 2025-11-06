<?php

namespace App\Http\Controllers\Panel;

use App\Enums\CycleAsaasEnum;
use App\Enums\PaymentStatusOrderAsaasEnum;
use App\Enums\StatusOrderAsaasEnum;
use App\Http\Controllers\Controller;
use App\Models\Coupon;
use App\Models\Customer;
use App\Models\CustomerCreditCard;
use App\Models\Order;
use App\Models\Package;
use App\Models\Plan;
use App\Services\AppIntegration\PlanCancelService;
use App\Services\AppIntegration\PlanCreateService;
use App\Services\PaymentGateway\Connectors\AsaasConnector;
use App\Services\PaymentGateway\Gateway;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;
use Illuminate\View\View;
use Yajra\DataTables\Facades\DataTables;
use App\Services\PaymentGateway\Connectors\Asaas\Subscription;
use App\Services\PaymentGateway\Contracts\AdapterInterface;
use Illuminate\Support\Facades\DB;

class OrderController extends Controller
{
    protected $model;
    protected $request;

    public function __construct(Order $order, Request $request)
    {
        $this->model = $order;
        $this->request = $request;
    }

    public function index(): View
    {
        return view($this->request->route()->getName());
    }

    public function loadDatatable(): JsonResponse
    {
        $orders = $this->model
            ->with(['customer:id,name', 'plan:id,name'])
            ->leftJoin('customers', 'customers.id', '=', 'orders.customer_id')
            ->leftJoin('coupons', 'coupons.id', '=', 'customers.coupon_id')
            ->select([
                'orders.id',
                'orders.customer_id',
                'orders.plan_id',
                'orders.value',
                'orders.subscription_asaas_id',
                'orders.customer_asaas_id',
                'orders.cycle',
                'orders.status',
                'orders.next_due_date',
                'orders.payment_status',
                'orders.created_at',
                'orders.payment_asaas_id',
                'coupons.name as coupon_name',
            ]);

        return DataTables::of($orders)
            ->addColumn('checkbox', function ($order) {
                return view('panel.orders.local.index.datatable.checkbox', compact('order'));
            })
            ->editColumn('id', function ($order) {
                return view('panel.orders.local.index.datatable.id', compact('order'));
            })
            ->editColumn('coupon_name', function ($order) {
                return $order->coupon_name ?? 'N/A';
            })
            ->filterColumn('coupon_name', function ($query, $keyword) {
                $query->whereRaw("coupons.name like ?", ["%{$keyword}%"]);
            })
            ->editColumn('customer_id', function ($order) {
                return $order->customer->name ?? '-';
            })
            ->filterColumn('customer_id', function ($query, $keyword) {
                $query->whereHas('customer', function ($q) use ($keyword) {
                    $q->where('name', 'like', "%{$keyword}%");
                });
            })
            ->editColumn('plan_id', function ($order) {
                return $order->plan->name ?? '-';
            })
            ->filterColumn('plan_id', function ($query, $keyword) {
                $query->whereHas('plan', function ($q) use ($keyword) {
                    $q->where('name', 'like', "%{$keyword}%");
                });
            })
            ->filterColumn('status', function ($query, $keyword) {
                $matchingStatuses = collect(StatusOrderAsaasEnum::cases())
                    ->filter(fn($enum) => str_contains($enum->getName(), $keyword))
                    ->pluck('value')
                    ->toArray();

                $query->whereIn('status', $matchingStatuses);
            })
            ->editColumn('status', function ($order) {
                return $order->value == 0
                    ? 'Free'
                    : StatusOrderAsaasEnum::tryFrom($order->status)?->getName() ?? $order->status;
            })
            ->editColumn('payment_status', function ($order) {
                if ($order->value == 0) {
                    return 'Free';
                }

                $currentDate = Carbon::now()->startOfDay();
                $nextDueDate = Carbon::parse($order->next_due_date)->startOfDay();

                if ($nextDueDate > $currentDate) {
                    return 'GRÁTIS';
                }

                return PaymentStatusOrderAsaasEnum::tryFrom($order->payment_status)?->getName() ?? $order->payment_status;
            })
            ->editColumn('payment_asaas_id', function ($item) {
                return view('panel.orders.local.index.datatable.payment_asaas_id', compact('item'));
            })
            ->filterColumn('payment_status', function ($query, $keyword) {
                $matchingStatuses = collect(PaymentStatusOrderAsaasEnum::cases())
                    ->filter(fn($enum) => str_contains($enum->getName(), $keyword))
                    ->pluck('value')
                    ->toArray();

                $query->whereIn('payment_status', $matchingStatuses);
            })
            ->editColumn('cycle', function ($order) {
                return $order->value == 0
                    ? 'Free'
                    : CycleAsaasEnum::tryFrom($order->cycle)?->getName() ?? $order->cycle;
            })
            ->filterColumn('cycle', function ($query, $keyword) {
                $matchingCycles = collect(CycleAsaasEnum::cases())
                    ->filter(fn($enum) => str_contains($enum->getName(), $keyword))
                    ->pluck('value')
                    ->toArray();

                $query->whereIn('cycle', $matchingCycles);
            })
            ->editColumn('next_due_date', function ($order) {
                if ($order->value == 0) {
                    return 'Sem data';
                }

                return $order->next_due_date ? date('d/m/Y', strtotime($order->next_due_date)) : 'Sem data';
            })
            ->filterColumn('next_due_date', function ($query, $value) {
                $query->whereRaw("DATE_FORMAT(next_due_date,'%d/%m/%Y') like ?", ["%$value%"]);
            })
            ->editColumn('created_at', function ($order) {
                return $order->created_at ? date('d/m/Y H:i:s', strtotime($order->created_at)) : 'Sem data';
            })
            ->filterColumn('created_at', function ($query, $value) {
                $query->whereRaw("DATE_FORMAT(orders.created_at,'%d/%m/%Y %H:%i:%s') like ?", ["%$value%"]);
            })
            ->addColumn('action', function ($order) {
                $loggedId = auth()->user()->id;

                return view('panel.orders.local.index.datatable.action', compact('order', 'loggedId'));
            })
            ->toJson();
    }



    public function create(): View
    {
        $order = $this->model;

        return view('panel.orders.local.index.modals.create', compact('order'));
    }

    public function store(): JsonResponse
    {
        $data = $this->request->only([
            'name',
        ]);

        $validator = Validator::make($data, [
            'name' => ['required', 'string', 'max:100'],
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => 400,
                'errors' => $validator->errors(),
            ]);
        }

        if ($this->request->hasFile('photo')) {
            $data["photo"] = $this->request->file('photo')->store('avatars');
        }

        $order = $this->model->create($data);
        $order->update([
            'boleto_url' => $payments['data'][0]['invoiceUrl'] ?? null,
        ]);

        if ($order) {
            return response()->json([
                'status' => '200',
                'message' => 'Ação executada com sucesso!'
            ]);
        } else {
            return response()->json([
                'status' => '400',
                'errors' => [
                    'message' => ['Erro executar a ação, tente novamente!']
                ]
            ]);
        }
    }


    public function edit($id): View
    {
        $order = $this->model->find($id);

        return view('panel.orders.local.index.modals.edit', compact("order"));
    }

    public function update($id): JsonResponse
    {
        $order = $this->model->find($id);

        if ($order) {
            $data = $this->request->only([
                'name',
            ]);

            $validator = Validator::make($data, [
                'name' => ['required', 'string', 'max:100'],
            ]);

            if (count($validator->errors()) > 0) {
                return response()->json([
                    'status' => 400,
                    'errors' => $validator->errors(),
                ]);
            }

            $order->update($data);

            if ($order) {
                return response()->json([
                    'status' => '200',
                    'message' => 'Ação executada com sucesso!'
                ]);
            } else {
                return response()->json([
                    'status' => '400',
                    'errors' => [
                        'message' => ['Erro executar a ação, tente novamente!']
                    ]
                ]);
            }
        } else {
            return response()->json([
                'status' => '400',
                'errors' => [
                    'message' => ['Os dados não foram encontrados!']
                ]
            ]);
        }
    }

    public function delete($id): View
    {
        $order = $this->model->find($this->request->id);

        return view('panel.orders.local.index.modals.delete', compact("order"));
    }

    public function destroy(): JsonResponse
    {
        $order = $this->model->find($this->request->id);

        if ($order) {
            $delete = $order->delete();

            if ($delete) {
                return response()->json([
                    'status' => '200',
                    'message' => 'Ação executada com sucesso!'
                ]);
            } else {
                return response()->json([
                    'status' => '400',
                    'errors' => [
                        'message' => ['Erro executar a ação, tente novamente!']
                    ],
                ]);
            }
        } else {
            return response()->json([
                'status' => '400',
                'errors' => [
                    'message' => ['Os dados não foram encontrados!']
                ],
            ]);
        }
    }

    public function deleteAll(): View
    {
        $itens = $this->request->checkeds;

        session()->put('ids', $itens);

        return view('panel.orders.local.index.modals.remove-all', compact("itens"));
    }

    public function destroyAll(): JsonResponse
    {
        foreach (session()->get('ids') as $item) {
            $item = $this->model->find($item["id"]);

            if ($item) {
                $item->delete();

                if (!$item) {
                    return response()->json([
                        'status' => '400',
                        'errors' => [
                            'message' => ['Erro executar a ação, tente novamente!']
                        ],
                    ]);
                }
            } else {
                return response()->json([
                    'status' => '400',
                    'errors' => [
                        'message' => ['Os dados não foram encontrados!']
                    ],
                ]);
            }
        }

        return response()->json([
            'status' => '200',
            'message' => 'Ação executada com sucesso!'
        ]);
    }

    public function show($id): View
    {
        $order = $this->model->find($id);

        return view('panel.orders.local.index.modals.show', compact("order"));
    }

    public function duplicate(): View
    {
        $order = $this->model->find($this->request->id);

        return view('panel.orders.local.index.modals.duplicate', compact('order'));
    }

    public function cancel($id): View
    {
        $order = $this->model->find($this->request->id);

        return view('panel.orders.local.index.modals.cancel', compact("order"));
    }

    public function changePlan($id): View
    {
        $order = $this->model->find($this->request->id);
        $data = Plan::getPlansData();

        return view('panel.orders.local.index.modals.change-plan', [
            'actualPlan' => $order->plan_id,
            'order' => $order,
            'cycles' => $data['cycles'],
            'plansByCycle' => $data['plansByCycle'],
            'activeCycle' => $data['activeCycle']
        ]);
    }

    public function changePlanStore(Request $request)
    {
        Log::channel('plan_change')->debug('Início da troca de plano', [
            'request' => $request->only(['planId', 'orderId', 'coupon']),
            'current_date' => now()->toDateString(),
        ]);

        $validator = Validator::make($request->all(), [
            'planId' => 'required',
            'orderId' => 'required',
            'coupon' => 'nullable'
        ]);

        $planId = $validator->validated()['planId'];
        $couponName = $request->input('coupon');

        $coupon = null;
        $discountedValue = 0;

        $plan = Plan::find($planId);

        if ($couponName) {
            $coupon = $this->getCoupon($couponName);

            if (!$coupon?->is_active || !$plan) {
                Log::channel('plan_change')->info('Tentativa de troca com cupom inválido', [
                    'coupon' => $couponName,
                    'plan_id' => $planId
                ]);

                toastr()->info("Cupom inválido.");
                return back()->withInput()->withErrors(['error' => 'Ocorreu uma falha ao processar seu cadastro. Tente novamente.']);
            }

            $discountedValue = $this->getDiscount($plan, $coupon);
            $discountedValueFormat = number_format($discountedValue, 2, ',', '.');

            if ($discountedValue > 0 && $discountedValue <= 5) {
                Log::channel('plan_change')->info('Valor final após cupom abaixo do mínimo', [
                    'coupon' => $couponName,
                    'plan_id' => $planId,
                    'final_value' => $discountedValue,
                ]);
                toastr()->info("O valor final de R$$discountedValueFormat após o cupom ser aplicado não pode ser menor que R$5,00.");
                return back()->withInput()->withErrors(['error' => 'Ocorreu uma falha ao processar seu cadastro. Tente novamente.']);
            }
        }

        $order = $this->model->find($validator->validated()['orderId']);

        if ($order->hasPlan($planId, $order->customer_id)) {
            toastr('Este é o seu plano atual, escolha outro plano.', 'warning');
            return redirect()->back();
        }

        if (
            $order->next_due_date < now()
            && (
                $order->payment_status !== PaymentStatusOrderAsaasEnum::RECEIVED->getName() &&
                $order->payment_status !== PaymentStatusOrderAsaasEnum::CONFIRMED->getName()
            )
        ) {
            toastr(
                'Se já fez o pagamento, por favor, aguarde a efetivação pelo sistema.',
                'info',
                'Seu plano está vencido. Realize o pagamento antes de continuar!',
            );
            return redirect()->back();
        }

        $rawCycle = $order->getRawOriginal('cycle');

        $cycleDays = match ($rawCycle) {
            'WEEKLY' => 7,
            'BIWEEKLY' => 14,
            'MONTHLY' => 30,
            'BIMONTHLY' => 60,
            'QUARTERLY' => 90,
            'SEMIANNUALLY' => 180,
            'YEARLY' => 365,
            default => 30,
        };

        Log::channel('plan_change')->debug('Dados do pedido e ciclo', [
            'order_id' => $order->id,
            'current_plan_value' => $order->value,
            'current_cycle' => $order->cycle,
            'cycle_days' => $cycleDays,
            'next_due_date_local' => $order->next_due_date,
        ]);

        $adapter = app(AsaasConnector::class);
        $gateway = new Gateway($adapter);

        $subscription = $gateway->subscription()->get($order->subscription_asaas_id);
        Log::channel('plan_change')->debug('Resposta da API do Asaas - assinatura', [
            'subscription_id' => $order->subscription_asaas_id,
            'nextDueDate_asaas' => $subscription['nextDueDate'] ?? null,
            'status' => $subscription['status'] ?? null,
        ]);

        $asaasPayments = $gateway->subscription()->getPayments($order->subscription_asaas_id);
        $pendingPayments = collect($asaasPayments['data'])
            ->whereIn('status', ['PENDING', 'OVERDUE'])
            ->sortBy('dueDate');

        if ($pendingPayments->isEmpty()) {
            $nextDueDate = $order->next_due_date->format('Y-m-d');
            Log::channel('plan_change')->warning('Nenhum pagamento pendente encontrado. Usando next_due_date do banco.', [
                'order_id' => $order->id,
                'fallback_date' => $nextDueDate,
            ]);
        } else {
            $nextDueDate = $pendingPayments->first()['dueDate'];
        }

        Log::channel('plan_change')->debug('Próxima data de vencimento real (do pagamento)', [
            'next_due_date_from_payment' => $nextDueDate,
            'order_next_due_date' => $order->next_due_date->toDateString(),
        ]);

        $today = now()->startOfDay();
        $dueDate = Carbon::parse($nextDueDate)->startOfDay();

        $daysRemaining = max(0, $dueDate->diffInDays($today));

        Log::channel('plan_change')->debug('Cálculo de dias corrigido', [
            'today' => $today->toDateString(),
            'due_date' => $dueDate->toDateString(),
            'days_remaining' => $daysRemaining,
        ]);

        $daysUsed = $cycleDays - $daysRemaining;

        Log::channel('plan_change')->debug('Cálculo de dias', [
            'current_date' => now()->toDateString(),
            'next_due_date_asaas' => $subscription['nextDueDate'],
            'days_remaining' => $daysRemaining,
            'days_used' => $daysUsed,
        ]);

        $actualPlanValue = $order->value;
        $newPlanValue = is_null($coupon) ? $plan->value : $discountedValue;
        $isUpgrade = $newPlanValue > $actualPlanValue;
        $isDowngrade = $newPlanValue < $actualPlanValue;
        $invoiceValue = $newPlanValue;

        Log::channel('plan_change')->debug('Valores dos planos', [
            'actual_plan_value' => $actualPlanValue,
            'new_plan_value' => $newPlanValue,
            'is_upgrade' => $isUpgrade,
            'is_downgrade' => $isDowngrade,
            'has_coupon' => !is_null($coupon),
            'coupon_name' => $couponName,
        ]);

        $forNextCycle = $isDowngrade;

        $days = 0;

        if ($isUpgrade) {
            Log::channel('plan_change')->info('Iniciando processo de upgrade');

            $asaasPaymentsFromActualSubscription = $gateway->subscription()->getPayments($order->subscription_asaas_id);

            // Verifica se já houve pagamento recebido (não está em trial)
            $hasReceivedPayment = collect($asaasPaymentsFromActualSubscription['data'])
                ->contains(fn($payment) => $payment['status'] === 'RECEIVED');

            // Remove pagamentos pendentes (sempre necessário para evitar cobranças antigas)
            foreach ($asaasPaymentsFromActualSubscription['data'] as $subscriptionPayment) {
                if ($subscriptionPayment['status'] === 'PENDING') {
                    Log::channel('plan_change')->debug('Removendo pagamento pendente', [
                        'payment_id' => $subscriptionPayment['id'],
                        'value' => $subscriptionPayment['value'],
                        'status' => $subscriptionPayment['status'],
                    ]);

                    $paymentDeleted = $gateway->payment()->delete($subscriptionPayment['id']);
                    logger(
                        $paymentDeleted['deleted']
                            ? "Pagamento removido no Asaas para atualização de plano. Pedido: $order->id"
                            : "Erro ao remover pagamento no Asaas para atualização de plano. Pedido: $order->id"
                    );
                }
            }

            if ($hasReceivedPayment) {
                // Cliente já pagou → aplica crédito do plano antigo
                $dailyRate = (float) $actualPlanValue / (float) $cycleDays;
                $dailyRate = floor($dailyRate * 100) / 100;
                $credit = $dailyRate * $daysRemaining;
                $invoiceValue = max(0, $newPlanValue - $credit);

                Log::channel('plan_change')->debug('Cliente já pagou. Aplicando crédito.', [
                    'daily_rate' => $dailyRate,
                    'days_remaining' => $daysRemaining,
                    'credit' => $credit,
                    'new_plan_value' => $newPlanValue,
                    'invoice_value' => $invoiceValue,
                ]);
            } else {
                // Está em trial → NÃO há crédito. Cobra valor integral do novo plano no próximo vencimento.
                $invoiceValue = $newPlanValue;

                Log::channel('plan_change')->debug('Cliente em trial. Sem crédito. Próxima cobrança será valor integral do novo plano.', [
                    'new_plan_value' => $newPlanValue,
                    'invoice_value' => $invoiceValue,
                ]);
            }

            if ($invoiceValue <= 5) {
                Log::channel('plan_change')->info('Upgrade bloqueado: valor final <= R$5,00', [
                    'invoice_value' => $invoiceValue,
                ]);

                toastr(
                    "Não é possível trocar o plano nesse momento pois o valor a ser cobrado (R$$invoiceValue) é menor que R$5,00.",
                    'info'
                );

                return redirect()->route('panel.orders.index');
            }

            // Define a data de vencimento como a data original (não hoje!)
            $dueDate = $nextDueDate;
        } else {
            $days = max(0, $cycleDays - $daysUsed);
            $dueDate = $forNextCycle
                ? $order->next_due_date->copy()->addDays($days)->format('Y-m-d')
                : now()->format('Y-m-d');
        }

        Log::channel('plan_change')->debug('Data de vencimento da nova fatura', [
            'for_next_cycle' => $forNextCycle,
            'days_to_add' => $days,
            'original_next_due_date' => $order->next_due_date->toDateString(),
            'new_due_date' => $dueDate,
        ]);

        // Atualiza assinatura e troca os pacotes
        $result = $this->updateSubscription($order, $invoiceValue, $plan, $gateway, $dueDate);

        Log::channel('plan_change')->debug('Resultado da atualização da assinatura', [
            'success' => $result,
            'order_id' => $order->id,
            'invoice_value' => $invoiceValue,
            'new_plan_id' => $plan->id,
        ]);

        if ($isDowngrade && $result) {
            toastr(
                'Seu plano será alterado no próximo ciclo. A cobrança atual permanecerá com o valor do plano anterior.',
                'info'
            );
        }

        if (!$isDowngrade && $result) {
            toastr('Assinatura atualizada com sucesso!', 'success');
        }

        if (!$result) {
            toastr('Erro ao atualizar assinatura!', 'error');
        }

        Log::channel('plan_change')->info('Fim do processo de troca de plano', [
            'order_id' => $order->id,
            'success' => $result,
        ]);

        return redirect()->route('panel.orders.index');
    }

    private function getCoupon(mixed $couponName): ?Coupon
    {
        return Coupon::where('name', $couponName)->first();
    }

    private function getDiscount(Plan $plan, Coupon $coupon): mixed
    {
        return $plan->value - ($plan->value * ($coupon->percent / 100));
    }

    public function changeCard(string $id_order)
    {
        return view('panel.orders.local.index.modals.edit-card', compact('id_order'));
    }

    public function showCards($id): View
    {
        $order = $this->model->find($this->request->id);

        return view('panel.orders.local.index.modals.change-card', compact('order'));
    }

    public function updateCard(Order $order)
    {
        $data = $this->request->only([
            "id_order",
            "credit_card_number",
            "credit_card_name",
            "credit_card_expiry_month",
            "credit_card_expiry_year",
            "credit_card_ccv"
        ]);

        $validator = Validator::make($data, [
            'credit_card_number' => ['required', 'string', 'max:19'],
            'credit_card_name' => ['required', 'string', 'max:255'],
            'credit_card_expiry_month' => ['required', 'string', 'max:2'],
            'credit_card_expiry_year' => ['required', 'string', 'max:4'],
            'credit_card_ccv' => ['required', 'string', 'max:4'],
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => 400,
                'errors' => $validator->errors(),
            ]);
        }

        $customer = $order->customer;

        $newLast4 = substr(preg_replace('/\D/', '', $data['credit_card_number']), -4);
        $existingLast4 = $customer->credit_card_number ? substr($customer->credit_card_number, -4) : null;

        if ($existingLast4 && $newLast4 === $existingLast4) {
            return response()->json([
                'status' => 400,
                'errors' => [
                    'message' => ['Este cartão já está em uso. Por favor, insira um cartão diferente.']
                ]
            ]);
        }

        $asaasCustomerId = $order->customer_asaas_id;

        try {
            $creditCardData = $this->extractCreditCardData($data);
            Log::channel('registration')->info('Tentando tokenizar cartão', [
                'asaas_customer_id' => $asaasCustomerId,
                'card_last4' => substr($creditCardData['number'], -4),
                'holder' => $creditCardData['holderName'],
            ]);

            $creditCardInfo = $this->tokenizeCreditCard($asaasCustomerId, $creditCardData);

            Log::channel('registration')->info('Cartão tokenizado com sucesso', [
                'asaas_customer_id' => $asaasCustomerId,
                'token' => $creditCardInfo['token'],
                'brand' => $creditCardInfo['brand'],
            ]);
        } catch (\Exception $e) {
            Log::channel('registration')->error('Falha na tokenização do cartão.', [
                'asaas_customer_id' => $asaasCustomerId,
                'error' => $e->getMessage(),
            ]);

            throw $e;
        }

        try {
            $result = DB::transaction(function () use ($customer, $creditCardInfo, $asaasCustomerId, $order) {
                if ($customer->credit_card_token) {
                    $customer->creditCards()->create([
                        'credit_card_token' => $customer->credit_card_token,
                        'credit_card_brand' => $customer->credit_card_brand,
                        'credit_card_number' => $customer->credit_card_number,
                    ]);
                }

                $customer->update([
                    'customer_id' => $asaasCustomerId,
                    'credit_card_token' => $creditCardInfo['token'],
                    'credit_card_brand' => $creditCardInfo['brand'],
                    'credit_card_number' => $creditCardInfo['number'],
                ]);

                if ($order->subscription_asaas_id) {
                    $adapter = new AsaasConnector();
                    $gateway = new Gateway($adapter);

                    $customerIp = $this->request->ip();
                    $gateway->subscription()->updateCreditCard(
                        $order->subscription_asaas_id,
                        $creditCardInfo['token'],
                        $customerIp
                    );
                } else {
                    throw new \Exception('Assinatura não encontrada para atualização do cartão.');
                }

                return true;
            });

            Log::channel('registration')->info('Cartão e assinatura atualizados com sucesso');
            return response()->json(['status' => 200, 'message' => 'Cartão atualizado com sucesso!']);
        } catch (\Exception $e) {
            Log::channel('registration')->error('Falha na atualização do cartão (transação revertida).', [
                'error' => $e->getMessage(),
            ]);

            return response()->json([
                'status' => 500,
                'message' => 'Não foi possível atualizar o cartão. Tente novamente.',
            ], 500);
        }
    }

    private function extractCreditCardData(array $data): array
    {
        return [
            'holderName' => $data['credit_card_name'],
            'number' => $data['credit_card_number'],
            'expiryMonth' => $data['credit_card_expiry_month'],
            'expiryYear' => $data['credit_card_expiry_year'],
            'ccv' => $data['credit_card_ccv'],
            'ip' => $data['ip'] ?? request()->ip(),
        ];
    }

    private function tokenizeCreditCard(string $asaasCustomerId, array $cardData): array
    {
        $adapter = new AsaasConnector();
        $gateway = new Gateway($adapter);

        $payload = [
            'customer' => $asaasCustomerId,
            'creditCard' => [
                'holderName' => $cardData['holderName'],
                'number' => $cardData['number'],
                'expiryMonth' => $cardData['expiryMonth'],
                'expiryYear' => $cardData['expiryYear'],
                'ccv' => $cardData['ccv'],
            ],
            'remoteIp' => $cardData['ip'],
        ];

        $response = $gateway->creditCard()->tokenize($payload);

        if (!isset($response['creditCardToken']) || isset($response['error'])) {
            $error = $response['error']['errors'][0]['description'] ?? 'Erro ao tokenizar cartão';
            Log::channel('registration')->info("Asaas - falha na tokenização: {$error}");
            throw new \Exception($error);
        }

        return [
            'token' => $response['creditCardToken'],
            'brand' => $response['creditCardBrand'],
            'number' => $response['creditCardNumber'],
        ];
    }
    protected function updateSubscription(
        $order,
        $invoiceValue,
        $plan,
        $gateway,
        $due_date
    ): bool {
        $data = [
            'id' => $order->subscription_asaas_id,
            'billingType' => $plan->billing_type,
            'value' => $invoiceValue,
            'nextDueDate' => $due_date,
            'description' => "Troca de plano para o plano: $plan->name",
            'externalReference' => 'Pedido: ' . $order->id,
        ];

        $response = $gateway->subscription()->update($order->subscription_asaas_id, $data);

        if (isset($response['object']) && $response['object'] === 'subscription') {
            // Cancela pacotes antigos na Youcast
            $packagesToCancel = [];
            $oldPlan = Plan::where('id', $order->plan_id)->first();
            foreach ($oldPlan->packagePlans as $packagePlan) {
                $pack = Package::find($packagePlan->package_id);
                $packagesToCancel[] = $pack->cod;
            }
            (new PlanCancelService($packagesToCancel, $order->customer->viewers_id))->cancelPlan();

            // Cria pacotes novos na Youcast
            $packagesToCreate = [];
            foreach ($plan->packagePlans as $packagePlan) {
                $pack = Package::find($packagePlan->package_id);
                $packagesToCreate[] = $pack->cod;
            }
            (new PlanCreateService($packagesToCreate, $order->customer->viewers_id))->createPlan();

            // Atualiza o pedido
            $order->update([
                'plan_id' => $plan->id,
                'description' => $plan->description,
                'changed_plan' => true,
                'value' => $invoiceValue,
                'original_plan_value' => $plan->value
            ]);
            return true;
        }

        if (isset($response['error'])) {
            $error = $response['error']['errors'][0]['description'] ?? 'Erro ao criar cliente no Asaas';
            Log::error("Asaas - Erro no retorno do Asaas ao atualizar assinatura.", [
                'response' => $error,
                'order_id' => $order->id,
            ]);

            toastr()->info($error);
        }

        return false;
    }


    public function canceling(): JsonResponse
    {
        $order = $this->model->find($this->request->id);

        if ($order) {
            $adapter = app(AsaasConnector::class);

            $gateway = new Gateway($adapter);

            $response = $gateway->subscription()->delete($order->subscription_asaas_id);

            if (!$response['deleted']) {
                Log::error(
                    "Erro ao cancelar assinatura - linha 306 - OrderController {$order->customer->name} - {$order->id}"
                );

                return response()->json([
                    'status' => '400',
                    'errors' => [
                        'message' => ['Erro executar a ação, tente novamente!']
                    ],
                ]);
            }

            $order->deleted_date = now();
            $order->status = 'INACTIVE';
            $order->save();

            if ($response['id']) {
                return response()->json([
                    'status' => '200',
                    'message' => 'Ação executada com sucesso!'
                ]);
            } else {
                return response()->json([
                    'status' => '400',
                    'errors' => [
                        'message' => ['Erro executar a ação, tente novamente!']
                    ],
                ]);
            }
        } else {
            return response()->json([
                'status' => '400',
                'errors' => [
                    'message' => ['Os dados não foram encontrados!']
                ],
            ]);
        }
    }
}
