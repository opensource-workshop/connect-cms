<?php

namespace App\Providers;

use Illuminate\Foundation\Support\Providers\AuthServiceProvider as ServiceProvider;
use Illuminate\Support\Facades\Auth;

use App\Providers\ConnectEloquentUserProvider;

class AuthServiceProvider extends ServiceProvider
{
    /**
     * The policy mappings for the application.
     *
     * @var array
     */
    protected $policies = [
        //
    ];

    /**
     * Register any authentication / authorization services.
     */
    public function boot(): void
    {
        // ユーザー認証はカスタマイズしたもので行う。
        Auth::provider('connect_eloquent', function ($app, array $config) {
            return new ConnectEloquentUserProvider($app['hash'], $config['model']);
        });
    }
}
