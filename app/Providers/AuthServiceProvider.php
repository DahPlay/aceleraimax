<?php

namespace App\Providers;

// use Illuminate\Support\Facades\Gate;
use Illuminate\Foundation\Support\Providers\AuthServiceProvider as ServiceProvider;
use Illuminate\Support\Facades\Gate;
use Opcodes\LogViewer\Facades\LogViewer;

class AuthServiceProvider extends ServiceProvider
{
    /**
     * The model to policy mappings for the application.
     *
     * @var array<class-string, class-string>
     */
    protected $policies = [
        //
    ];

    /**
     * Register any authentication / authorization services.
     */
    public function boot(): void
    {
        LogViewer::auth(function(){
            return auth()->check() && auth()->user()?->access_id === 3;
        });

        Gate::define('user', function ($user) {
            return $user->access_id == 1 || $user->access_id == 2 || $user->access_id == 3;
        });

        Gate::define('admin', function ($user) {
            return $user->access_id == 2 || $user->access_id == 3;
        });

        Gate::define('developer', function ($user) {
            return $user->access_id == 3;
        });
    }
}
