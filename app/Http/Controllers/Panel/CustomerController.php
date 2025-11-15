<?php

namespace App\Http\Controllers\Panel;

use App\Http\Controllers\Controller;
use App\Models\Customer;
use App\Models\Coupon;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Validator;
use Yajra\DataTables\Facades\DataTables;
use Illuminate\View\View;

class CustomerController extends Controller
{
    protected $model;
    protected $request;

    public function __construct(Customer $customer, Request $request)
    {
        $this->middleware('can:user');

        $this->model = $customer;
        $this->request = $request;
    }

    public function index(): View
    {
        return view($this->request->route()->getName());
    }

    public function loadDatatable(): JsonResponse
    {
        $customers = $this->model
            ->leftJoin('coupons', 'coupons.id', '=', 'customers.coupon_id')
            ->select([
                'customers.id',
                'customers.name',
                'customers.customer_id',
                'customers.viewers_id',
                'customers.login',
                'customers.email',
                'customers.document',
                'customers.cpf_dependente_1',
                'customers.cpf_dependente_2',
                'customers.cpf_dependente_3',
                'customers.created_at',
                'customers.coupon_id',
                'coupons.name as coupon_name',
            ]);

        return DataTables::of($customers)
            ->addColumn('checkbox', function ($customer) {
                return view('panel.customers.local.index.datatable.checkbox', compact('customer'));
            })
            ->editColumn('coupon_id', function ($customer) {
                return $customer->coupon_name ?? 'N/A';
            })
            ->filterColumn('coupon_id', function ($query, $keyword) {
                $query->whereRaw("coupons.name like ?", ["%{$keyword}%"]);
            })
            ->editColumn('id', function ($customer) {
                return view('panel.customers.local.index.datatable.id', compact('customer'));
            })
            ->editColumn('created_at', function ($customer) {
                return $customer->created_at ? date('d/m/Y H:i:s', strtotime($customer->created_at)) : 'Sem data';
            })
            ->filterColumn('created_at', function ($query, $value) {
                $query->whereRaw("DATE_FORMAT(created_at,'%d/%m/%Y %H:%i:%s') like ?", ["%$value%"]);
            })
            ->addColumn('action', function ($customer) {
                $loggedId = auth()->user()->id;

                return view('panel.customers.local.index.datatable.action', compact('customer', 'loggedId'));
            })
            ->toJson();
    }

    public function create(): View
    {
        $customer = $this->model;

        $users = Customer::select(['id', 'name'])->get();

        return view('panel.customers.local.index.modals.create', compact('customer', 'users'));
    }

    public function store(): JsonResponse
    {
        $data = $this->request->only([
            'name',
            'id',
            'document',
            'mobile',
            'birthdate',
            'email',
            'cpf_dependente_1',
            'cpf_dependente_2',
            'cpf_dependente_3',
        ]);

        $validator = Validator::make($data, [
            'id' => ['integer'],
            'name' => [
                'required',
                'string',
                'max:255',
                'regex:/^[a-zA-ZÀ-ÿ\s]+ [a-zA-ZÀ-ÿ\s]+$/',
                function ($attribute, $value, $fail) {
                    $parts = array_filter(array_map('trim', explode(' ', $value)));
                    if (count($parts) < 2) {
                        $fail('O campo Nome deve conter nome e sobrenome.');
                    }
                },
            ],
            'document' => ['required', 'string', 'unique_document:customers,document'],
            'mobile' => ['required', 'string'],
            'birthdate' => ['nullable', 'date'],
            'email' => ['required', 'string', 'unique:customers'],
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

        $customer = $this->model->create($data);

        if ($customer) {
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
        $customer = $this->model->find($id);

        $users = User::select(['id', 'name'])
            ->whereNotIn('id', [2, 3])
            ->get();

        return view('panel.customers.local.index.modals.edit', compact('users', 'customer'));
    }

    public function update($id): JsonResponse
    {
        $customer = $this->model->find($id);

        if ($customer) {
            $data = $this->request->only([
                'name',
                'id',
                'document',
                'mobile',
                'birthdate',
                'email',
                'cpf_dependente_1',
                'cpf_dependente_2',
                'cpf_dependente_3',
            ]);

            $validator = Validator::make($data, [
                'id' => ['integer'],
                'name' => [
                    'required',
                    'string',
                    'max:255',
                    'regex:/^[a-zA-ZÀ-ÿ\s]+ [a-zA-ZÀ-ÿ\s]+$/',
                    function ($attribute, $value, $fail) {
                        $parts = array_filter(array_map('trim', explode(' ', $value)));
                        if (count($parts) < 2) {
                            $fail('O campo Nome deve conter nome e sobrenome.');
                        }
                    },
                ],
                'document' => ['required', 'string'],
                'mobile' => ['required', 'string'],
                'birthdate' => ['nullable', 'date'],
                'email' => ['required', 'string'],
            ]);

            if (count($validator->errors()) > 0) {
                return response()->json([
                    'status' => 400,
                    'errors' => $validator->errors(),
                ]);
            }

            $customer->update($data);

            if ($customer) {
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
        $customer = $this->model->find($this->request->id);

        return view('panel.customers.local.index.modals.delete', compact("customer"));
    }

    public function destroy(): JsonResponse
    {
        $customer = $this->model->find($this->request->id);

        if ($customer) {
            $delete = $customer->delete();

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

        return view('panel.customers.local.index.modals.remove-all', compact("itens"));
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
}
