<?php

namespace App\Providers;

use Illuminate\Foundation\Support\Providers\AuthServiceProvider as ServiceProvider;
use Illuminate\Support\Facades\Gate;

use App\Providers\ConnectEloquentUserProvider;
use Auth;

class AuthServiceProvider extends ServiceProvider
{
    /**
     * The policy mappings for the application.
     *
     * @var array
     */
    protected $policies = [
        // 'App\Model' => 'App\Policies\ModelPolicy',
    ];

    /**
     * Register any authentication / authorization services.
     *
     * @return void
     */
    public function boot()
    {
        $this->registerPolicies();

        // システム管理者のみ許可
        Gate::define('system', function ($user) {
            return ($user->role == 1);
        });
        // サイト管理者のみ許可
        Gate::define('site-admin', function ($user) {
            return ($user->role == 2);
        });
        // ユーザ管理者のみ許可
        Gate::define('user-admin', function ($user) {
            return ($user->role == 3);
        });
        // 運用管理者のみ許可
        Gate::define('manager', function ($user) {
            return ($user->role == 10);
        });
        // 承認者のみ許可
        Gate::define('approver', function ($user) {
            return ($user->role == 11);
        });
        // 編集者のみ許可
        Gate::define('editor', function ($user) {
            return ($user->role == 12);
        });

        // システム管理者＆ユーザ管理者のみ許可
        Gate::define('system_user-admin', function ($user) {
            return ($user->role == 1 || $user->role == 3);
        });

        // ユーザー認証はカスタマイズしたもので行う。
        Auth::provider('connect_eloquent', function ($app, array $config) {
            return new ConnectEloquentUserProvider($app['hash'], $config['model']);
        });
    }
}
