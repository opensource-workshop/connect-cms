<?php

namespace App\Services;

use App\Models\Core\Configs;
use League\OAuth2\Client\Provider\GenericProvider;
use Illuminate\Support\Facades\Log;

/**
 * Microsoft 365メールOAuth2サービスクラス
 *
 * Microsoft 365のOAuth2認証を管理する（Client Credentials Grant方式）
 *
 * @author OpenSource-WorkShop Co.,Ltd.
 * @copyright OpenSource-WorkShop Co.,Ltd. All Rights Reserved
 * @category サービス
 * @package Services
 */
class Ms365MailOauth2Service
{
    /**
     * Microsoft 365 OAuth2エンドポイント
     */
    private const MS365_LOGIN_BASE_URL = 'https://login.microsoftonline.com';
    private const MS365_OAUTH2_VERSION = 'v2.0';

    /**
     * Microsoft Graph API スコープ
     */
    private const GRAPH_API_DEFAULT_SCOPE = 'https://graph.microsoft.com/.default';

    /**
     * OAuth2設定のキャッシュ
     * @var \Illuminate\Support\Collection|null
     */
    private $oauth2_configs_cache = null;

    /**
     * OAuth2設定を取得（キャッシュ付き）
     *
     * @return \Illuminate\Support\Collection
     */
    private function getOauth2Configs()
    {
        if ($this->oauth2_configs_cache === null) {
            $this->oauth2_configs_cache = Configs::where('category', 'mail_oauth2_ms365_app')->get();
        }
        return $this->oauth2_configs_cache;
    }

    /**
     * OAuth2 Provider インスタンスを取得
     */
    public function getProvider(): GenericProvider
    {
        $oauth2_configs = $this->getOauth2Configs();

        $tenant_id = Configs::getConfigsValue($oauth2_configs, 'tenant_id');
        $client_id = Configs::getConfigsValue($oauth2_configs, 'client_id');
        $client_secret = Configs::getConfigsValue($oauth2_configs, 'client_secret');

        // クライアントシークレットの復号化
        if ($client_secret) {
            try {
                $client_secret = decrypt($client_secret);
            } catch (\Exception $e) {
                Log::error('Failed to decrypt client secret: ' . $e->getMessage());
                $client_secret = null;
            }
        }

        return new GenericProvider([
            'clientId'                => $client_id,
            'clientSecret'            => $client_secret,
            'redirectUri'             => '', // Client Credentials Grantでは不要
            'urlAuthorize'            => self::MS365_LOGIN_BASE_URL . "/{$tenant_id}/oauth2/" . self::MS365_OAUTH2_VERSION . "/authorize",
            'urlAccessToken'          => self::MS365_LOGIN_BASE_URL . "/{$tenant_id}/oauth2/" . self::MS365_OAUTH2_VERSION . "/token",
            'urlResourceOwnerDetails' => '',
            'scopes'                  => self::GRAPH_API_DEFAULT_SCOPE,
        ]);
    }

    /**
     * アクセストークンを取得（Client Credentials Grant）
     */
    public function getAccessToken(): \League\OAuth2\Client\Token\AccessToken
    {
        $provider = $this->getProvider();

        try {
            // Client Credentials Grantでトークンを取得
            $access_token = $provider->getAccessToken('client_credentials', [
                'scope' => self::GRAPH_API_DEFAULT_SCOPE
            ]);

            return $access_token;
        } catch (\Exception $e) {
            Log::error('Failed to get access token: ' . $e->getMessage());
            throw $e;
        }
    }

    /**
     * OAuth2設定保存時にトークンを取得
     */
    public function obtainTokens(): bool
    {
        $access_token = $this->getAccessToken();
        $this->saveTokens($access_token);
        return true;
    }

    /**
     * トークンが有効期限切れかチェック
     */
    public function isTokenExpired(): bool
    {
        $oauth2_configs = $this->getOauth2Configs();
        $expires_at = Configs::getConfigsValue($oauth2_configs, 'token_expires_at');

        if (!$expires_at) {
            return true;
        }

        // 有効期限の5分前に期限切れとみなす（安全マージン）
        return now()->addMinutes(5)->greaterThan($expires_at);
    }

    /**
     * 有効なアクセストークンを取得（期限切れなら自動再取得）
     */
    public function getValidAccessToken(): string
    {
        // 連携解除されている場合はエラー
        if (!$this->isConnected()) {
            throw new \Exception('Microsoft 365 OAuth2 is not connected. Please reconnect.');
        }

        if ($this->isTokenExpired()) {
            // トークンを再取得
            // 理由: Microsoft 365のアクセストークンは通常1時間で期限切れになるため、
            //       期限切れ前（5分前）に自動的に新しいトークンを取得する。
            //       Client Credentials Grant方式ではリフレッシュトークンがないため、
            //       新規にアクセストークンを取得し直す必要がある。
            $access_token = $this->getAccessToken();
            $this->saveTokens($access_token);
            return $access_token->getToken();
        }

        $oauth2_configs = $this->getOauth2Configs();
        $token = Configs::getConfigsValue($oauth2_configs, 'access_token');

        // トークンの復号化
        if ($token) {
            try {
                return decrypt($token);
            } catch (\Exception $e) {
                Log::error('Failed to decrypt access token: ' . $e->getMessage());
                throw $e;
            }
        }

        throw new \Exception('Access token not found');
    }

    /**
     * トークン情報を保存
     */
    public function saveTokens($access_token): void
    {
        // アクセストークンを暗号化して保存
        Configs::updateOrCreate(
            ['category' => 'mail_oauth2_ms365_app', 'name' => 'access_token'],
            ['value' => encrypt($access_token->getToken())]
        );

        // 有効期限を保存
        $expires_at = now()->addSeconds($access_token->getExpires() - time());
        Configs::updateOrCreate(
            ['category' => 'mail_oauth2_ms365_app', 'name' => 'token_expires_at'],
            ['value' => $expires_at->toDateTimeString()]
        );

        // 最終取得日時を保存
        Configs::updateOrCreate(
            ['category' => 'mail_oauth2_ms365_app', 'name' => 'token_obtained_at'],
            ['value' => now()->toDateTimeString()]
        );

        // 連携状態を「連携済み」に更新
        Configs::updateOrCreate(
            ['category' => 'mail_oauth2_ms365_app', 'name' => 'is_connected'],
            ['value' => '1']
        );
    }

    /**
     * 連携状態をチェック
     */
    public function isConnected(): bool
    {
        $oauth2_configs = $this->getOauth2Configs();
        $is_connected = Configs::getConfigsValue($oauth2_configs, 'is_connected', '0');

        return $is_connected === '1';
    }

    /**
     * 最終トークン取得日時を取得
     */
    public function getTokenObtainedAt(): ?string
    {
        $oauth2_configs = $this->getOauth2Configs();
        return Configs::getConfigsValue($oauth2_configs, 'token_obtained_at');
    }

    /**
     * 連携を解除（トークン情報を削除）
     */
    public function disconnect(): void
    {
        // トークン情報を削除
        Configs::where('category', 'mail_oauth2_ms365_app')
            ->whereIn('name', ['access_token', 'token_expires_at', 'token_obtained_at'])
            ->delete();

        // 連携状態を「未連携」に更新
        Configs::updateOrCreate(
            ['category' => 'mail_oauth2_ms365_app', 'name' => 'is_connected'],
            ['value' => '0']
        );
    }
}
