<?php

namespace App\Http\Controllers\Panel;

use App\Http\Controllers\Controller;
use App\Http\Requests\CouponRequest;
use App\Models\Coupon;
use App\Models\Package;
use App\Services\CouponService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Yajra\DataTables\Facades\DataTables;

class CouponController extends Controller
{
    protected $model;
    protected $request;

    public function __construct(Coupon $coupon, Request $request)
    {
        $this->middleware('can:admin')->except('validateCoupon');

        $this->model = $coupon;
        $this->request = $request;
    }

    public function index(): View
    {
        return view($this->request->route()->getName());
    }

    public function loadDatatable(): JsonResponse
    {
        $coupons = $this->model
            ->get();

        return DataTables::of($coupons)
            ->addColumn('checkbox', function ($coupon) {
                return view('panel.coupons.local.index.datatable.checkbox', compact('coupon'));
            })
            ->editColumn('id', function ($coupon) {
                return view('panel.coupons.local.index.datatable.id', compact('coupon'));
            })
            ->editColumn('created_at', function ($coupon) {
                return $coupon->created_at
                    ? $coupon->created_at->format('d/m/Y H:i:s')
                    : 'Sem data';
            })
            ->editColumn('is_active', function ($item) {
                return view('panel.coupons.local.index.datatable.is_active', compact('item'));
            })
            ->addColumn('action', function ($coupon) {
                $loggedId = auth()->user()->id;
                return view('panel.coupons.local.index.datatable.action', compact('coupon', 'loggedId'));
            })
            ->toJson();
    }


    public function create(): View
    {
        $coupon = $this->model;

        return view('panel.coupons.local.index.modals.create', compact('coupon'));
    }

    public function store(CouponRequest $request): JsonResponse
    {
        $isActive = match ($request->validated()['is_active']) {
            '1' => true,
            '0' => false,
        };

        $data = $request->validated();

        $data['is_active'] = $isActive;

        $coupon = $this->model->create($data);

        if ($coupon) {
            return response()->json([
                'status' => 200,
                'message' => 'Ação executada com sucesso!'
            ]);
        }
        return response()->json([
            'status' => 400,
            'errors' => [
                'message' => ['Erro executar a ação, tente novamente!']
            ]
        ]);
    }

    public function edit($id): View
    {
        $coupon = $this->model->find($id);
        return view('panel.coupons.local.index.modals.edit', compact("coupon"));
    }

    public function duplicate(): View
    {
        $coupon = $this->model->find($this->request->id);
        $packages = Package::where('name', '!=', 'Dahplay desativado')
            ->where('is_active', true)
            ->get();
        return view('panel.plans.local.index.modals.duplicate', compact('plan', 'packages'));
    }

    public function update(CouponRequest $request): JsonResponse
    {
        $isActive = match ($request->validated()['is_active']) {
            '1' => true,
            '0' => false,
        };

        $data = $request->validated();

        $data['is_active'] = $isActive;

        $coupon = $this->model->find($this->request->id);
        $coupon = $coupon->update($data);
        // ds($coupon); 23/08/2025 Comentado esse trecho pois estava dando erro, verificar se precisa dessa função mais tarde
        if ($coupon) {
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

    public function delete($id): View
    {
        $coupon = $this->model->find($this->request->id);

        return view('panel.coupons.local.index.modals.delete', compact("coupon"));
    }

    public function destroy(): JsonResponse
    {

        $coupon = $this->model->find($this->request->id);

        if ($coupon) {
            $delete = $coupon->delete();

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

        return view('panel.plans.local.index.modals.remove-all', compact("itens"));
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

    public function validateCoupon(Request $request, CouponService $couponService)
    {
        $data = $couponService->validate(
            $request->input('coupon'),
            (int) $request->input('plan_id')
        );

        return response()->json($data);
    }
}
