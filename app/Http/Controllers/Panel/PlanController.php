<?php

namespace App\Http\Controllers\Panel;

use App\Enums\BillingTypeAsaasEnum;
use App\Enums\CycleAsaasEnum;
use App\Http\Controllers\Controller;
use App\Http\Requests\PlanRequest;
use App\Models\Package;
use App\Models\Plan;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\View\View;
use Yajra\DataTables\Facades\DataTables;
use Illuminate\Validation\Rule;

class PlanController extends Controller
{
    protected $model;
    protected $request;

    public function __construct(Plan $plan, Request $request)
    {
        $this->middleware('can:admin');

        $this->model = $plan;
        $this->request = $request;
    }

    public function index(): View
    {
        return view($this->request->route()->getName());
    }

    public function loadDatatable(): JsonResponse
    {
        $plans = $this->model
            ->select([
                'plans.id',
                'plans.name',
                'plans.value',
                'plans.cycle',
                'plans.created_at',
                'plans.is_active',
                'plans.billing_type',
                'plans.priority',
                'plans.is_active_telemedicine',
            ])
            ->where('hidden', '!=', 'Sim');

        return DataTables::of($plans)
            ->addColumn('checkbox', function ($plan) {
                return view('panel.plans.local.index.datatable.checkbox', compact('plan'));
            })
            ->editColumn('id', function ($plan) {
                return view('panel.plans.local.index.datatable.id', compact('plan'));
            })
            ->editColumn('cycle', function ($plan) {
                return CycleAsaasEnum::from($plan->cycle)->getName();
            })
            ->filterColumn('cycle', function ($query, $keyword) {
                $matchingCycles = collect(CycleAsaasEnum::cases())
                    ->filter(fn($enum) => str_contains($enum->getName(), $keyword))
                    ->pluck('value')
                    ->toArray();

                $query->whereIn('cycle', $matchingCycles);
            })
            ->editColumn('billing_type', function ($plan) {
                return BillingTypeAsaasEnum::from($plan->billing_type)->getName();
            })
            ->filterColumn('billing_type', function ($query, $keyword) {
                $matchingBillingTypes = collect(BillingTypeAsaasEnum::cases())
                    ->filter(fn($enum) => str_contains($enum->getName(), $keyword))
                    ->pluck('value')
                    ->toArray();

                $query->whereIn('billing_type', $matchingBillingTypes);
            })
            ->editColumn('created_at', function ($plan) {
                return $plan->created_at ? date('d/m/Y H:i:s', strtotime($plan->created_at)) : 'Sem data';
            })
            ->filterColumn('created_at', function ($query, $value) {
                $query->whereRaw("DATE_FORMAT(created_at,'%d/%m/%Y %H:%i:%s') like ?", ["%$value%"]);
            })
            ->editColumn('is_active', function ($item) {
                return view('panel.plans.local.index.datatable.is_active', compact('item'));
            })
            ->editColumn('is_active_telemedicine', function ($item) {
                return view('panel.plans.local.index.datatable.is_active_telemedicine', compact('item'));
            })
            ->addColumn('action', function ($plan) {
                $loggedId = auth()->user()->id;

                return view('panel.plans.local.index.datatable.action', compact('plan', 'loggedId'));
            })
            ->toJson();
    }


    public function create(): View
    {
        $plan = $this->model;
        $packages = Package::where('name', '!=', 'Dahplay desativado')
            ->where('is_active', true)
            ->get();

        return view('panel.plans.local.index.modals.create', compact('plan', 'packages'));
    }

    public function store(PlanRequest $request): JsonResponse
    {
        // 1) Obter os dados validados (exceto priority, que trataremos logo abaixo)
        $data = $request->validated();

        // 2) Capturar priority diretamente do request
        $priority = $request->input('priority');

        // 3) Verificar se veio preenchido
        if ($priority === null || $priority === '') {
            return response()->json([
                'status' => 400,
                'errors' => [
                    'priority' => ['O campo Prioridade é obrigatório.'],
                ],
            ]);
        }

        // 4) Verificar duplicidade
        if ($this->model->where('priority', $priority)->exists()) {
            return response()->json([
                'status' => 400,
                'errors' => [
                    'priority' => ['Já existe uma prioridade cadastrada com esse valor.'],
                ],
            ]);
        }

        // 5) Tratar booleanos
        $data['is_active'] = $request->has('is_active') ? 1 : 0;
        $data['is_active_telemedicine'] = $request->has('is_active_telemedicine') ? 1 : 0;
        $data['is_best_seller'] = $request->has('is_best_seller') ? 1 : 0;

        // 6) Injetar priority no array de dados
        $data['priority'] = (int) $priority;

        // 7) Criar o plano
        $plan = $this->model->create($data);

        if ($plan) {
            // 8) Relacionar benefícios
            if ($request->filled('benefits') && $request->benefits[0] !== null) {
                foreach ($request->benefits as $benefit) {
                    $plan->benefits()->create(['description' => $benefit]);
                }
            }

            // 9) Relacionar pacotes
            if ($request->filled('packages')) {
                foreach ($request->packages as $packageId) {
                    $plan->packagePlans()->create(['package_id' => $packageId]);
                }
            }

            return response()->json([
                'status' => 200,
                'message' => 'Ação executada com sucesso!',
            ]);
        }

        // 10) Erro inesperado
        return response()->json([
            'status' => 400,
            'errors' => [
                'message' => ['Erro ao executar a ação, tente novamente!'],
            ],
        ]);
    }



    // public function store(PlanRequest $request): JsonResponse
    // {
    //     $data = $request->validated();

    //     $data['is_active'] = isset($data['is_active']) ? 1 : 0;
    //     $data['is_best_seller'] = isset($data['is_best_seller']) ? 1 : 0;

    //     $plan = $this->model->create($data);

    //     if ($plan) {
    //         if ($this->request->filled('benefits') && $this->request->benefits[0] !== null) {
    //             foreach ($this->request->benefits as $benefit) {
    //                 $plan->benefits()->create(['description' => $benefit]);
    //             }
    //         }

    //         if ($this->request->filled('packages')) {
    //             foreach ($this->request->packages as $package) {
    //                 $plan->packagePlans()->create([
    //                     'package_id' => $package,
    //                 ]);
    //             }
    //         }
    //         return response()->json([
    //             'status' => 200,
    //             'message' => 'Ação executada com sucesso!'
    //         ]);
    //     }
    //     return response()->json([
    //         'status' => 400,
    //         'errors' => [
    //             'message' => ['Erro executar a ação, tente novamente!']
    //         ]
    //     ]);
    // }

    public function edit($id): View
    {
        $plan = $this->model->find($id);
        $packages = Package::where('name', '!=', 'Dahplay desativado')
            ->where('is_active', true)
            ->get();

        return view('panel.plans.local.index.modals.edit', compact("plan", 'packages'));
    }

    public function duplicate(): View
    {
        $plan = $this->model->find($this->request->id);
        $packages = Package::where('name', '!=', 'Dahplay desativado')
            ->where('is_active', true)
            ->get();
        return view('panel.plans.local.index.modals.duplicate', compact('plan', 'packages'));
    }


    public function update($id): JsonResponse
    {
        $plan = $this->model->find($id);

        if (!$plan) {
            return response()->json([
                'status' => 400,
                'errors' => [
                    'message' => ['Os dados não foram encontrados!']
                ]
            ]);
        }

        $data = $this->request->only([
            'name',
            'value',
            'description',
            'cycle',
            'is_active',
            'is_best_seller',
            'billing_type',
            'free_for_days',
            'priority',
            'is_active_telemedicine',
        ]);

        $validator = Validator::make($data, [
            'name' => ['required', 'string', 'max:255'],
            'value' => ['required', 'string', 'min:0'],
            'description' => ['nullable', 'string'],
            'is_active' => ['nullable', 'string'],
            'is_active_telemedicine' => ['nullable', 'string'],
            'is_best_seller' => ['nullable', 'string'],
            'billing_type' => ['string'],
            'free_for_days' => ['integer'],
            'priority' => [
                'required',
                'integer',
                Rule::unique('plans', 'priority')->ignore($id),
            ],
        ], [
            'priority.unique' => 'Prioridade já cadastrada.',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => 400,
                'errors' => $validator->errors(),
            ]);
        }

        $data['is_active'] = isset($data['is_active']) ? 1 : 0;
        $data['is_active_telemedicine'] = isset($data['is_active_telemedicine']) ? 1 : 0;
        $data['is_best_seller'] = isset($data['is_best_seller']) ? 1 : 0;

        $plan->update($data);

        $plan->benefits()->delete();
        $plan->packagePlans()->delete();

        if ($this->request->filled('benefits') && count($this->request->input('benefits')) > 0 && !is_null($this->request->input('benefits')[0])) {
            foreach ($this->request->benefits as $benefit) {
                $plan->benefits()->create(['description' => $benefit]);
            }
        }

        if ($this->request->filled('packages')) {
            foreach ($this->request->packages as $package) {
                $plan->packagePlans()->create([
                    'package_id' => $package,
                ]);
            }
        }

        return response()->json([
            'status' => 200,
            'message' => 'Ação executada com sucesso!'
        ]);
    }

    // public function update($id): JsonResponse
    // {
    //     $plan = $this->model->find($id);

    //     if ($plan) {
    //         $data = $this->request->only([
    //             'name',
    //             'value',
    //             'description',
    //             'cycle',
    //             'is_active',
    //             'is_best_seller',
    //             'billing_type',
    //             'free_for_days',
    //             'priority',
    //         ]);

    //         $validator = Validator::make($data, [
    //             'name' => ['required', 'string', 'max:255'],
    //             'value' => ['required', 'string', 'min:0'],
    //             'description' => ['nullable', 'string'],
    //             'name' => ['required', 'string'],
    //             'is_active' => ['nullable', 'string'],
    //             'is_best_seller' => ['nullable', 'string'],
    //             'billing_type' => ['string'],
    //             'free_for_days' => ['integer'],
    //             'priority' => ['required', 'integer'],
    //         ]);

    //         if (count($validator->errors()) > 0) {
    //             return response()->json([
    //                 'status' => 400,
    //                 'errors' => $validator->errors(),
    //             ]);
    //         }

    //         $data['is_active'] = isset($data['is_active']) ? 1 : 0;
    //         $data['is_best_seller'] = isset($data['is_best_seller']) ? 1 : 0;

    //         $plan->update($data);

    //         $plan->benefits()->delete();

    //         $plan->packagePlans()->delete();

    //         if ($this->request->filled('benefits') && count($this->request->input('benefits')) > 0 && !is_null(
    //                 $this->request->input('benefits')[0]
    //             )) {
    //             foreach ($this->request->benefits as $benefit) {
    //                 $plan->benefits()->create(['description' => $benefit]);
    //             }
    //         }

    //         if ($this->request->filled('packages')) {
    //             foreach ($this->request->packages as $package) {
    //                 $plan->packagePlans()->create([
    //                     'package_id' => $package,
    //                 ]);
    //             }
    //         }

    //         if ($plan) {
    //             return response()->json([
    //                 'status' => '200',
    //                 'message' => 'Ação executada com sucesso!'
    //             ]);
    //         } else {
    //             return response()->json([
    //                 'status' => '400',
    //                 'errors' => [
    //                     'message' => ['Erro executar a ação, tente novamente!']
    //                 ]
    //             ]);
    //         }
    //     } else {
    //         return response()->json([
    //             'status' => '400',
    //             'errors' => [
    //                 'message' => ['Os dados não foram encontrados!']
    //             ]
    //         ]);
    //     }
    // }

    public function delete($id): View
    {
        $plan = $this->model->find($this->request->id);

        return view('panel.plans.local.index.modals.delete', compact("plan"));
    }

    public function destroy(): JsonResponse
    {
        $plan = $this->model->find($this->request->id);

        if ($plan) {
            $plan->hidden = 'Sim';
            $plan->is_active = 0;
            $plan->priority = 0;
            $plan->save();

            return response()->json([
                'status' => '200',
                'message' => 'O plano foi ocultado com sucesso.'
            ]);
        }

        return response()->json([
            'status' => '400',
            'errors' => [
                'message' => ['Os dados não foram encontrados!']
            ],
        ]);
    }



    public function deleteAll(): View
    {
        $itens = $this->request->checkeds;

        session()->put('ids', $itens);

        return view('panel.plans.local.index.modals.remove-all', compact("itens"));
    }

    public function destroyAll(): JsonResponse
    {
        foreach (session()->get('ids') as $itemData) {
            $item = $this->model->find($itemData["id"]);

            if ($item) {
                $item->hidden = 'Sim';
                $item->is_active = 0;
                $item->priority = 0;
                $item->save();
            } else {
                return response()->json([
                    'status' => '400',
                    'errors' => [
                        'message' => ['Um ou mais dados não foram encontrados!']
                    ],
                ]);
            }
        }

        return response()->json([
            'status' => '200',
            'message' => 'Planos ocultados com sucesso.'
        ]);
    }
}
