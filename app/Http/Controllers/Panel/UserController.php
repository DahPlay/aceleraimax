<?php

namespace App\Http\Controllers\Panel;

use App\Http\Controllers\Controller;
use App\Http\Requests\UserStoreRequest;
use App\Http\Requests\UserUpdateRequest;
use App\Models\Access;
use App\Models\Customer;
use App\Models\User;
use App\Services\Alloyal\User\UserCreate;
use App\Services\Alloyal\User\UserCreateSmartLink;
use App\Services\Alloyal\User\UserDetails;
use App\Services\Alloyal\User\UserDisable;
use App\Services\Alloyal\User\UserUpdate;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;
use Yajra\DataTables\Facades\DataTables;

class UserController extends Controller
{
    protected $model;
    protected $request;

    public function __construct(User $user, Request $request)
    {
        $this->model = $user;
        $this->request = $request;
    }

    public function index(): View
    {
        return view($this->request->route()->getName());
    }

    public function loadDatatable(): JsonResponse
    {
        $users = $this->model->with(['access'])
            ->select([
                'users.id',
                'users.name',
                'users.login',
                'users.email',
                'users.created_at',
                'users.access_id',
            ])
            ->when(auth()->user()->id !== 3, function ($query) {
                $query->where('access_id', '<>', 3);
            });

        return DataTables::of($users)
            ->addColumn('checkbox', function ($user) {
                return view('panel.users.local.index.datatable.checkbox', compact('user'));
            })
            ->editColumn('id', function ($user) {
                return view('panel.users.local.index.datatable.id', compact('user'));
            })
            ->editColumn('created_at', function ($user) {
                return $user->created_at ? date('d/m/Y H:i:s', strtotime($user->created_at)) : 'Sem data';
            })
            ->editColumn('access.name', function ($user) {
                return view('panel.users.local.index.datatable.access', compact('user'));
            })
            ->filterColumn('created_at', function ($query, $value) {
                $query->whereRaw("DATE_FORMAT(created_at,'%d/%m/%Y %H:%i:%s') like ?", ["%$value%"]);
            })
            ->addColumn('action', function ($user) {
                $loggedId = auth()->user()->id;

                return view('panel.users.local.index.datatable.action', compact('user', 'loggedId'));
            })
            ->make();
    }

    public function create(): View
    {
        $user = $this->model;

        $accesses = Access::select(['id', 'name'])->get();

        return view('panel.users.local.index.modals.create', compact('user', 'accesses'));
    }

    public function store(UserStoreRequest $request): JsonResponse
    {
        $data = $request->validated();

        $photoPath = null;

        try {
            $user = DB::transaction(function () use ($request, $data, $photoPath) {
                if ($request->hasFile('photo')) {
                    $photoPath = $request->file('photo')->store('avatars', 'public');
                    $data['photo'] = $photoPath;
                }

                $user = $this->model->create($data);

                $customerData = $this->prepareCustomerData($data);

                $user->customer()->create($customerData);

                return $user;
            });

            return response()->json([
                'status' => 200,
                'message' => 'Usuário criado com sucesso!',
                'user_id' => $user->id,
            ]);
        } catch (\Exception $e) {
            if ($photoPath && Storage::disk('public')->exists($photoPath)) {
                Storage::disk('public')->delete($photoPath);
            }

            Log::error('Falha na criação de usuário', [
                'exception' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            return response()->json([
                'status' => 400,
                'errors' => ['message' => ['Erro interno. Tente novamente.']],
            ]);
        }
    }

    public function createSmartLink($id): View
    {
        $user = $this->model->find($id);

        return view('panel.users.local.index.modals.create-smart-link', compact("user"));
    }

    public function storeSmartLink(Request $request, $id): JsonResponse
    {
        $user = $this->model->with('customer')->findOrFail($id);

        if ($user) {
            $cpf = $user->customer->document;

            $alloyalResponse = (new UserCreateSmartLink())->handle($cpf);

            if (isset($alloyalResponse['errors'])) {
                return response()->json([
                    'status' => 400,
                    'errors' => [
                        'message' => [$alloyalResponse['errors'] ?? 'Falha ao criar o Smart Link na Alloyal'],
                    ],
                ]);
            }

            $user->customer->update([
                'web_smart_link' => $alloyalResponse["web_smart_link"]
            ]);

            Log::channel('alloyal')->info("SmartLink adicionado ao customer com sucesso", [
                'user' => $user->name,
                'customer' => $user->customer->name,
                'web_smart_link' => $alloyalResponse["web_smart_link"],
                'timestamp' => now()->toDateTimeString(),
            ]);

            return response()->json([
                'status' => 200,
                'message' => 'Smart Link gerado com sucesso!',
            ]);
        }

        return response()->json([
            'status' => 400,
            'errors' => ['message' => ['Erro interno. Tente novamente.']],
        ]);
    }

    public function edit($id): View
    {
        $user = $this->model->find($id);

        $accesses = Access::select(['id', 'name'])->get();

        return view('panel.users.local.index.modals.edit', compact("user", "accesses"));
    }

    public function update(UserUpdateRequest $request, $id): JsonResponse
    {
        $user = $this->model->with('customer')->findOrFail($id);

        $originalUser = $user->replicate();
        $originalCustomer = $user->customer ? $user->customer->replicate() : null;

        $newPhotoPath = null;
        $oldPhotoPath = $user->photo;

        try {
            if ($request->hasFile('photo')) {
                $newPhotoPath = $request->file('photo')->store('avatars', 'public');
                $data['photo'] = $newPhotoPath;

                if ($oldPhotoPath && $oldPhotoPath !== 'avatars/default.png') {
                    Storage::disk('public')->delete($oldPhotoPath);
                }
            }

            $data = $request->validated();

            if ($request->filled('password')) {
                $data['password'] = Hash::make($request->input('password'));
            } else {
                unset($data['password'], $data['password_confirmation']);
            }

            $user->update($data);

            if ($user->customer) {
                $user->customer->update([
                    'document' => $data['document'],
                    'mobile' => $data['mobile'],
                ]);
            }

            $alloyalUser = (new UserDetails())->handle($user->customer->document);

            if (!is_null($alloyalUser)) {
                $alloyalPayload = [
                    'name' => $data['name'],
                    'email' => $data['email'],
                    'cpf' => $data['document'],
                    'cellphone' => $data['mobile'],
                ];

                if ($request->filled('password')) {
                    $alloyalPayload['password'] = $request->input('password');
                }

                $alloyalResponse = (new UserUpdate())->handle($alloyalPayload);

                if (isset($alloyalResponse['errors'])) {
                    $this->rollbackUserUpdate($user, $originalUser, $originalCustomer, $newPhotoPath, $oldPhotoPath);

                    return response()->json([
                        'status' => 400,
                        'errors' => [
                            'message' => [$alloyalResponse['errors'] ?? 'Falha ao atualizar na Alloyal'],
                        ],
                    ]);
                }
            }

            return response()->json([
                'status' => 200,
                'message' => 'Usuário atualizado com sucesso!',
            ]);
        } catch (\Exception $e) {
            $this->rollbackUserUpdate($user, $originalUser, $originalCustomer, $newPhotoPath, $oldPhotoPath);

            Log::error('Exceção em update de usuário', [
                'user_id' => $user->id,
                'exception' => $e->getMessage(),
            ]);

            return response()->json([
                'status' => 400,
                'errors' => ['message' => ['Erro interno. Tente novamente.']],
            ]);
        }
    }

    private function rollbackUserUpdate($user, $originalUser, $originalCustomer, $newPhotoPath, $oldPhotoPath): void
    {
        DB::transaction(function () use ($user, $originalUser, $originalCustomer, $newPhotoPath, $oldPhotoPath) {
            $user->fill([
                'name' => $originalUser->name,
                'email' => $originalUser->email,
                'login' => $originalUser->login,
                'photo' => $originalUser->photo,
                'password' => $originalUser->password,
            ])->save();

            if ($originalCustomer) {
                $user->customer->fill([
                    'document' => $originalCustomer->document,
                    'mobile' => $originalCustomer->mobile,
                ])->save();
            }

            if ($newPhotoPath) {
                Storage::disk('public')->delete($newPhotoPath);
            }
        });
    }

    public function delete($id): View
    {
        $user = $this->model->find($this->request->id);

        return view('panel.users.local.index.modals.delete', compact("user"));
    }

    public function destroy(): JsonResponse
    {
        $user = $this->model->with('customer')->findOrFail($this->request->id);

        $photoPath = $user->photo;
        $originalUserData = $user->replicate();
        $originalCustomerData = $user->customer ? $user->customer->replicate() : null;

        try {
            $alloyalUser = (new UserDetails())->handle($user->customer->document);

            if (!is_null($alloyalUser)) {
                $cpf = $user->customer?->document ?? $user->document ?? '';

                $alloyalResponse = (new UserDisable())->handle($cpf);
            }

            if (isset($alloyalResponse['errors'])) {
                return response()->json([
                    'status' => 400,
                    'errors' => [
                        'message' => [$alloyalResponse['errors'] ?? 'Falha ao inativar usuário na Alloyal'],
                    ],
                ]);
            }

            DB::transaction(function () use ($user, $photoPath) {
                if ($photoPath && $photoPath !== 'avatars/default.png') {
                    Storage::disk('public')->delete($photoPath);
                }

                $user->delete();

                $user->customer->delete();
            });

            return response()->json([
                'status' => 200,
                'message' => 'Usuário excluído com sucesso!',
            ]);
        } catch (\Exception $e) {
            $this->rollbackUserDeletion($originalUserData, $originalCustomerData, $photoPath);

            Log::error('Falha na exclusão de usuário', [
                'user_id' => $user->id,
                'exception' => $e->getMessage(),
            ]);

            return response()->json([
                'status' => 400,
                'errors' => ['message' => ['Erro interno. Tente novamente.']],
            ]);
        }
    }

    private function rollbackUserDeletion($originalUser, $originalCustomer, $photoPath): void
    {
        try {
            DB::transaction(function () use ($originalUser, $originalCustomer, $photoPath) {
                $restoredUser = User::create($originalUser->toArray());

                if ($originalCustomer) {
                    $restoredCustomer = Customer::create($originalCustomer->toArray());
                    $restoredUser->customer()->create($restoredCustomer);
                }
            });
        } catch (\Exception $rollbackException) {
            Log::critical('Falha ao reverter exclusão de usuário', [
                'original_user_id' => $originalUser->id ?? 'unknown',
                'rollback_error' => $rollbackException->getMessage(),
            ]);
        }
    }

    public function deleteAll(): View
    {
        $itens = $this->request->checkeds;

        session()->put('ids', $itens);

        return view('panel.users.local.index.modals.remove-all', compact("itens"));
    }

    public function destroyAll(): JsonResponse
    {
        foreach (session()->get('ids') as $item) {
            $item = $this->model->find($item["id"]);

            if ($item) {
                if ($item->photo != "avatars/default.png") {
                    $file_path_photo = public_path('storage/') . $item->photo;

                    if (file_exists($file_path_photo)) {
                        unlink($file_path_photo);
                    }
                }

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

    public function removeImage(): JsonResponse
    {
        $user = $this->model->find($this->request->id);

        if ($user) {
            if ($user->photo) {
                $file_path_photo = public_path('storage/') . $user->photo;

                if (file_exists($file_path_photo) && $user->photo != "avatars/default.png") {
                    unlink($file_path_photo);
                }

                $user->photo = "avatars/default.png";
                $user->save();

                return response()->json([
                    'status' => '200',
                    'message' => 'Imagem removida com sucesso!'
                ]);
            } else {
                return response()->json([
                    'status' => '400',
                    'errors' => [
                        'message' => ['Erro ao tentar remover a imagem!']
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

    private function prepareCustomerData(array $data): array
    {
        return [
            'login' => $data['login'],
            'name' => $data['name'],
            'document' => $data['document'],
            'mobile' => $data['mobile'],
            'email' => $data['email'],
            'payment_asaas_id' => $data['payment_asaas_id'] ?? null,
            'cpf_dependente_1' => $data['cpf_dependente_1'] ?? null,
            'cpf_dependente_2' => $data['cpf_dependente_2'] ?? null,
            'cpf_dependente_3' => $data['cpf_dependente_3'] ?? null,
            'coupon_id' => $data['coupon_id'] ?? null,
        ];
    }
}
