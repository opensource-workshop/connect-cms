<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Mail\MailManager;
use App\Mail\Transport\MicrosoftGraphTransport;
use App\Services\Ms365MailOauth2Service;
use App\Models\Core\Configs;

/**
 * メールサービスプロバイダー
 *
 * カスタムメールトランスポートの登録
 *
 * @author OpenSource-WorkShop Co.,Ltd.
 * @copyright OpenSource-WorkShop Co.,Ltd. All Rights Reserved
 * @category サービスプロバイダー
 * @package Providers
 */
class MailServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     *
     * @return void
     */
    public function register()
    {
        //
    }

    /**
     * Bootstrap services.
     *
     * @return void
     */
    public function boot()
    {
        // Microsoft Graph トランスポートをMailManagerに登録
        $this->app->resolving(MailManager::class, function (MailManager $manager) {
            $manager->extend('microsoft-graph', function ($config) {
                // OAuth2設定の取得
                $oauth2_configs = Configs::where('category', 'mail_oauth2_ms365_app')->get();
                $from_address = Configs::getConfigsValue($oauth2_configs, 'mail_from_address');

                $oauth2_service = new Ms365MailOauth2Service();

                return new MicrosoftGraphTransport($oauth2_service, $from_address);
            });
        });
    }
}
