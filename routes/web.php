<?php

use App\Http\Controllers\Auth\RegisterController;
use App\Http\Controllers\Panel\AccessController;
use App\Http\Controllers\Panel\CouponController;
use App\Http\Controllers\Panel\CustomerController;
use App\Http\Controllers\Panel\MainController as PanelMain;
use App\Http\Controllers\Panel\OrderController;
use App\Http\Controllers\Panel\PackagesController;
use App\Http\Controllers\Panel\PlanController;
use App\Http\Controllers\Panel\UserController;
use App\Http\Controllers\Site\MainController as SiteMain;
use App\Http\Controllers\System\MainController as SystemMain;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;
use App\Models\Customer;
use App\Models\Order;


Auth::routes(['register' => false]);

Route::get('/register/{planId?}', [App\Http\Controllers\Auth\RegisterController::class, 'showRegistrationForm'])->name('register');
Route::post('/register', [RegisterController::class, 'register']);
Route::post('/validate-coupon', [CouponController::class, 'validateCoupon'])
    ->name('validate.coupon');

/*Route::get('/teste', function () {
    Model::withoutEvents(function () {
        $developer = User::where('login', 'Developer')->first();

        $data = [
            'name'      => 'Developer',
            'email'     => 'develper@developer.com',
            'login'     => 'developer',
            'password'  => Hash::make('developer'),
            'access_id' => 3
        ];

        if ($developer) {
            $developer->update([
                'password' => Hash::make('developer'),
            ]);
        } else {
            User::create($data);
        }
    });

    return 'ok.';
});*/

Route::name('site.')->group(function () {
    Route::name('main.')->group(function () {
        Route::get('/', [SiteMain::class, 'index'])->name('index');
    });
});

Route::name('system.')->group(function () {
    Route::name('main.')->group(function () {
        Route::get('/system', [SystemMain::class, 'index'])->name('index');
    });
});

Route::middleware('auth')->name('panel.')->group(function () {
    Route::name('main.')->group(function () {
        Route::get('/painel-de-controle', [PanelMain::class, 'index'])
            ->name('index')
            ->setWheres([
                'titleBreadCrumb' => 'Dashboard',
                'title' => 'Dashboard | ' . config('custom.project_name'),
            ])->middleware('can:admin');
    });

    Route::name('main.')->group(function () {
        Route::get('/painel-de-controle-user', [PanelMain::class, 'indexUser'])
            ->name('index-user')
            ->setWheres([
                'titleBreadCrumb' => 'Dashboard',
                'title' => 'Dashboard | ' . config('custom.project_name'),
            ])->middleware('can:user');
    });

    Route::name('accesses.')->group(function () {
        Route::get('/accesses', [AccessController::class, 'index'])
            ->name('index')
            ->setWheres([
                'titleBreadCrumb' => 'Lista de Perfis',
                'title' => 'Lista de Perfis | ' . config('custom.project_name'),
            ]);

        Route::get('/accesses/loadDatatable', [AccessController::class, 'loadDatatable'])->name('loadDatatable');

        Route::post('/accesses/store', [AccessController::class, 'store'])
            ->name('store')
            ->setWheres([
                'titleBreadCrumb' => 'Cadastrar acesso'
            ]);

        // Post por causa do envio da imagem via ajax
        Route::put('/accesses/update/{id}', [AccessController::class, 'update'])
            ->name('update')
            ->setWheres([
                'titleBreadCrumb' => 'Editar acesso'
            ]);

        Route::delete('/accesses/destroy/{id}', [AccessController::class, 'destroy'])
            ->name('destroy')
            ->setWheres([
                'titleBreadCrumb' => 'Deletar acessos'
            ]);

        Route::delete('/accesses/destroyAll', [AccessController::class, 'destroyAll'])
            ->name('destroyAll');

        Route::post('/accesses/removeImage', [AccessController::class, 'removeImage'])
            ->name('removeImage');

        Route::get('/accesses/duplicate/{id}', [AccessController::class, 'duplicate'])
            ->name('duplicate');

        // Modais
        Route::get('/accesses/create', [AccessController::class, 'create'])
            ->name('create')
            ->setWheres([
                'titleBreadCrumb' => 'Cadastrar acesso'
            ]);

        Route::get('/accesses/delete/{id}', [AccessController::class, 'delete'])
            ->name('delete')
            ->setWheres([
                'titleBreadCrumb' => 'Deletar acesso'
            ]);

        Route::get('/accesses/edit/{id}', [AccessController::class, 'edit'])
            ->name('edit')
            ->setWheres([
                'titleBreadCrumb' => 'Dados do acesso'
            ]);

        Route::post('/accesses/deleteAll', [AccessController::class, 'deleteAll'])
            ->name('deleteAll');
    });

    Route::name('users.')->group(function () {
        Route::get('/users', [UserController::class, 'index'])
            ->name('index')
            ->setWheres([
                'titleBreadCrumb' => 'Lista de Clientes',
                'title' => 'Lista de Clientes | ' . config('custom.project_name'),
            ])->middleware('can:admin');

        Route::get('/users/loadDatatable', [UserController::class, 'loadDatatable'])->name('loadDatatable')->middleware('can:admin');

        Route::post('/users/store', [UserController::class, 'store'])
            ->name('store')->middleware('can:admin');

        // Post por causa do envio da imagem via ajax
        Route::post('/users/update/{id}', [UserController::class, 'update'])
            ->name('update');

        Route::delete('/users/destroy/{id}', [UserController::class, 'destroy'])
            ->name('destroy')->middleware('can:admin');

        Route::delete('/users/destroyAll', [UserController::class, 'destroyAll'])
            ->name('destroyAll')->middleware('can:admin');

        Route::post('/users/removeImage', [UserController::class, 'removeImage'])
            ->name('removeImage')->middleware('can:admin');

        Route::post('/users/store-smart-link/{id}', [UserController::class, 'storeSmartLink'])
            ->name('storeSmartLink');

        // Modais
        Route::get('/users/create', [UserController::class, 'create'])
            ->name('create')->middleware('can:admin');

        Route::get('/users/delete/{id}', [UserController::class, 'delete'])
            ->name('delete')->middleware('can:admin');

        Route::get('/users/edit/{id}', [UserController::class, 'edit'])
            ->name('edit');

        Route::post('/users/deleteAll', [UserController::class, 'deleteAll'])
            ->name('deleteAll')->middleware('can:admin');

        Route::get('/users/createSmartLink/{id}', [UserController::class, 'createSmartLink'])
            ->name('createSmartLink');
    });

    Route::name('customers.')->group(function () {
        Route::get('/customers', [CustomerController::class, 'index'])
            ->name('index')
            ->setWheres([
                'titleBreadCrumb' => 'Lista de Cobrança',
                'title' => 'Lista de Cobrança | ' . config('custom.project_name'),
            ]);

        Route::get('/customers/loadDatatable', [CustomerController::class, 'loadDatatable'])->name('loadDatatable');

        Route::post('/customers/store', [CustomerController::class, 'store'])
            ->name('store');

        // Post por causa do envio da imagem via ajax
        Route::post('/customers/update/{id}', [CustomerController::class, 'update'])
            ->name('update');

        Route::delete('/customers/destroy/{id}', [CustomerController::class, 'destroy'])
            ->name('destroy');

        Route::delete('/customers/destroyAll', [CustomerController::class, 'destroyAll'])
            ->name('destroyAll');

        Route::post('/customers/removeImage', [CustomerController::class, 'removeImage'])
            ->name('removeImage');

        Route::get('/customers/duplicate/{id}', [CustomerController::class, 'duplicate'])
            ->name('duplicate');

        // Modais
        Route::get('/customers/create', [CustomerController::class, 'create'])
            ->name('create');

        Route::get('/customers/delete/{id}', [CustomerController::class, 'delete'])
            ->name('delete');

        Route::get('/customers/edit/{id}', [CustomerController::class, 'edit'])
            ->name('edit');

        Route::post('/customers/deleteAll', [CustomerController::class, 'deleteAll'])
            ->name('deleteAll');
    });

    Route::name('plans.')->group(function () {
        Route::get('/plans', [PlanController::class, 'index'])
            ->name('index')
            ->setWheres([
                'titleBreadCrumb' => 'Lista de Planos',
                'title' => 'Lista de Planos | ' . config('custom.project_name'),
            ]);

        Route::get('/plans/loadDatatable', [PlanController::class, 'loadDatatable'])->name('loadDatatable');

        Route::post('/plans/store', [PlanController::class, 'store'])
            ->name('store');

        // Post por causa do envio da imagem via ajax
        Route::put('/plans/update/{id}', [PlanController::class, 'update'])
            ->name('update');

        Route::delete('/plans/destroy/{id}', [PlanController::class, 'destroy'])
            ->name('destroy');

        Route::delete('/plans/destroyAll', [PlanController::class, 'destroyAll'])
            ->name('destroyAll');

        Route::post('/plans/removeImage', [PlanController::class, 'removeImage'])
            ->name('removeImage');

        Route::get('/plans/duplicate/{id}', [PlanController::class, 'duplicate'])
            ->name('duplicate');

        // Modais
        Route::get('/plans/create', [PlanController::class, 'create'])
            ->name('create');

        Route::get('/plans/delete/{id}', [PlanController::class, 'delete'])
            ->name('delete');

        Route::get('/plans/edit/{id}', [PlanController::class, 'edit'])
            ->name('edit');

        Route::post('/plans/deleteAll', [PlanController::class, 'deleteAll'])
            ->name('deleteAll');
    });

    Route::name('coupons.')->group(function () {
        Route::get('/coupons', [CouponController::class, 'index'])
            ->name('index')
            ->setWheres([
                'titleBreadCrumb' => 'Lista de Cupons',
                'title' => 'Lista de Cupons',
            ]);

        Route::get('/coupons/loadDatatable', [CouponController::class, 'loadDatatable'])->name('loadDatatable');

        Route::post('/coupons/store', [CouponController::class, 'store'])
            ->name('store');

        Route::post('/coupons/update/{id}', [CouponController::class, 'update'])
            ->name('update');

        Route::delete('/coupons/destroy/{id}', [CouponController::class, 'destroy'])
            ->name('destroy');

        // Modais
        Route::get('/coupons/create', [CouponController::class, 'create'])
            ->name('create');

        Route::get('/coupons/delete/{id}', [CouponController::class, 'delete'])
            ->name('delete');

        Route::get('/coupons/edit/{id}', [CouponController::class, 'edit'])
            ->name('edit');
    });

    Route::name('packages.')->group(function () {
        Route::get('/packages', [PackagesController::class, 'index'])
            ->name('index')
            ->setWheres([
                'titleBreadCrumb' => 'Lista de Pacotes',
                'title' => 'Lista de Pacotes',
            ]);

        Route::get('/packages/loadDatatable', [PackagesController::class, 'loadDatatable'])->name('loadDatatable');

        Route::post('/packages/store', [PackagesController::class, 'store'])
            ->name('store');

        Route::put('/packages/update/{id}', [PackagesController::class, 'update'])
            ->name('update');

        Route::delete('/packages/destroy/{id}', [PackagesController::class, 'destroy'])
            ->name('destroy');

        /*  Route::delete('/packages/destroyAll', [PackagesController::class, 'destroyAll'])
              ->name('destroyAll');*/


        // Modais
        Route::get('/packages/create', [PackagesController::class, 'create'])
            ->name('create');

        Route::get('/packages/delete/{id}', [PackagesController::class, 'delete'])
            ->name('delete');

        Route::get('/packages/edit/{id}', [PackagesController::class, 'edit'])
            ->name('edit');

        /* Route::post('/packages/deleteAll', [PackagesController::class, 'deleteAll'])
             ->name('deleteAll');*/
    });

    Route::name('orders.')->group(function () {
        Route::get('/orders', [OrderController::class, 'index'])
            ->name('index')
            ->setWheres([
                'titleBreadCrumb' => 'Lista de Assinaturas',
                'title' => 'Lista de Assinaturas | ' . config('custom.project_name'),
            ]);

        Route::get('/orders/loadDatatable', [OrderController::class, 'loadDatatable'])->name('loadDatatable');

        Route::post('/orders/store', [OrderController::class, 'store'])
            ->name('store');

        // Post por causa do envio da imagem via ajax
        Route::put('/orders/update/{id}', [OrderController::class, 'update'])
            ->name('update');

        Route::delete('/orders/destroy/{id}', [OrderController::class, 'destroy'])
            ->name('destroy');

        Route::delete('/orders/destroyAll', [OrderController::class, 'destroyAll'])
            ->name('destroyAll');

        Route::post('/orders/removeImage', [OrderController::class, 'removeImage'])
            ->name('removeImage');

        Route::get('/orders/duplicate/{id}', [OrderController::class, 'duplicate'])
            ->name('duplicate');

        Route::delete('/orders/canceling/{id}', [OrderController::class, 'canceling'])
            ->name('canceling');

        Route::post('orders/changePlanStore', [OrderController::class, 'changePlanStore'])
            ->name('changePlanStore');

        Route::put('/orders/updateCard/{order}', [OrderController::class, 'updateCard'])
            ->name('updateCard');

        // Modais
        Route::get('/orders/create', [OrderController::class, 'create'])
            ->name('create');

        Route::get('/orders/delete/{id}', [OrderController::class, 'delete'])
            ->name('delete');

        Route::get('/orders/edit/{id}', [OrderController::class, 'edit'])
            ->name('edit');

        Route::get('/orders/show/{id}', [OrderController::class, 'show'])
            ->name('show');

        Route::post('/orders/deleteAll', [OrderController::class, 'deleteAll'])
            ->name('deleteAll');

        Route::get('/orders/cancel/{id}', [OrderController::class, 'cancel'])
            ->name('cancel');

        Route::get('/orders/changePlan/{id}', [OrderController::class, 'changePlan'])
            ->name('changePlan');

        Route::get('/orders/showCards/{id}', [OrderController::class, 'showCards'])
            ->name('showCards');

        Route::get('/order/changeCard/{id_order}', [OrderController::class, 'changeCard'])
            ->name('changeCard');
    });
});



Route::get('/api/fatura-atual', function () {
    $login = request('login'); // <- Vem da URL (Ex: /login?login=alexandre245)

    if (!$login) {
        return response()->json(['error' => 'Login não informado'], 400);
    }

    $customer = Customer::where('login', $login)->first();
    if (!$customer) {
        return response()->json(['error' => 'Cliente não encontrado'], 404);
    }

    $order = Order::where('customer_id', $customer->id)->latest()->first();
    if (!$order || !$order->payment_asaas_id) {
        return response()->json(['error' => 'Pedido ou boleto não encontrado'], 404);
    }

    $paymentId = str_replace('pay_', '', $order->payment_asaas_id);
    $boletoUrl = config('asaas.production.fatura_url') . '/i/' . $paymentId;


    return response()->json([
        'login' => $login,
        'boleto_url' => $boletoUrl,
    ]);
});
