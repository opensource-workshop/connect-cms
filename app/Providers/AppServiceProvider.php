<?php

namespace App\Providers;

use Illuminate\Support\Facades\Log;

use Gate;

//use Illuminate\Support\ServiceProvider;
use Illuminate\Foundation\Support\Providers\AuthServiceProvider;

//class AppServiceProvider extends ServiceProvider
class AppServiceProvider extends AuthServiceProvider
{
    /**
     * Bootstrap any application services.
     *
     * @return void
     */
    public function boot()
    {
        // 認可サービス(Gate)利用の準備
        $this->registerPolicies();

        // システム管理者権限の有無確認
        Gate::define(config('cc_role.ROLE_SYSTEM_MANAGER'), function ($user) {
            if ($user->role == config('cc_role.ROLE_SYSTEM_MANAGER')) {
                return true;
            }
            return false;
        });

        // サイト管理者権限の有無確認
        Gate::define(config('cc_role.ROLE_SITE_MANAGER'), function ($user) {
            if ($user->role == config('cc_role.ROLE_SITE_MANAGER')) {
                return true;
            }
            return false;
        });

        // ユーザ管理者権限の有無確認
        Gate::define(config('cc_role.ROLE_USER_MANAGER'), function ($user) {
            if ($user->role == config('cc_role.ROLE_USER_MANAGER')) {
                return true;
            }
            return false;
        });

        // ページ管理者権限の有無確認
        Gate::define(config('cc_role.ROLE_PAGE_MANAGER'), function ($user) {
            if ($user->role == config('cc_role.ROLE_PAGE_MANAGER')) {
                return true;
            }
            return false;
        });

        // 運用管理者権限の有無確認
        Gate::define(config('cc_role.ROLE_OPERATION_MANAGER'), function ($user) {
            if ($user->role == config('cc_role.ROLE_OPERATION_MANAGER')) {
                return true;
            }
            return false;
        });

        return false;
    }

    /**
     * Register any application services.
     *
     * @return void
     */
    public function register()
    {
        //
    }
}
