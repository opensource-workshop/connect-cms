<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Mail\MailManager;
use App\Mail\Transport\MicrosoftGraphTransport;
use App\Services\Ms365MailOauth2Service;
use App\Models\Core\Configs;
use App\Enums\MailAuthMethod;

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
        /**
         * MailManager解決時（メール送信時）に実行される
         * ※SMTP認証、OAuth2認証のどちらの場合でもこのコールバックは実行される
         */
        $this->app->resolving(MailManager::class, function (MailManager $manager) {
            /**
             * メール認証方式に応じて、メールドライバーとFROMアドレスを動的に設定
             * ・SMTP認証の場合: 何も設定を変更せず、.envの設定をそのまま使用
             * ・OAuth2認証の場合: ドライバーを'microsoft-graph'に変更し、FROMアドレスを設定
             */
            $this->configureMail();

            /**
             * Microsoft Graph カスタムトランスポートを登録
             * ※この登録は常に行われるが、実際に使用されるのはOAuth2認証時のみ
             *   SMTP認証時はconfig['mail.driver']が'smtp'のままなので、このトランスポートは使用されない
             */
            $manager->extend('microsoft-graph', function ($config) {
                // OAuth2設定の取得
                $oauth2_configs = Configs::where('category', 'mail_oauth2_ms365_app')->get();
                $from_address = Configs::getConfigsValue($oauth2_configs, 'mail_from_address');

                $oauth2_service = new Ms365MailOauth2Service();

                return new MicrosoftGraphTransport($oauth2_service, $from_address);
            });
        });
    }

    /**
     * メール設定を動的に設定
     *
     * @return void
     */
    private function configureMail()
    {
        // 認証方式を取得
        $mail_configs = Configs::where('category', 'mail')->get();
        $mail_auth_method = Configs::getConfigsValue(
            $mail_configs,
            'mail_auth_method',
            MailAuthMethod::smtp
        );

        if ($mail_auth_method == MailAuthMethod::oauth2_microsoft365_app) {
            // Microsoft 365連携（OAuth2）の場合
            $oauth2_configs = Configs::where('category', 'mail_oauth2_ms365_app')->get();
            $from_address = Configs::getConfigsValue($oauth2_configs, 'mail_from_address');

            // メールドライバーをMicrosoft Graphに設定
            config(['mail.driver' => 'microsoft-graph']);

            // FROMアドレスをOAuth2設定のものに変更
            if ($from_address) {
                config(['mail.from.address' => $from_address]);
            }
        }
        // SMTP認証の場合は、.envの設定をそのまま使用（デフォルト動作）
    }
}
