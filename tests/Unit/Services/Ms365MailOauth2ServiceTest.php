<?php

namespace Tests\Unit\Services;

use App\Services\Ms365MailOauth2Service;
use App\Models\Core\Configs;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Cache;
use League\OAuth2\Client\Provider\GenericProvider;
use League\OAuth2\Client\Token\AccessToken;
use Tests\TestCase;

class Ms365MailOauth2ServiceTest extends TestCase
{
    use RefreshDatabase;

    /**
     * @var Ms365MailOauth2Service
     */
    private $service;

    /**
     * 初期設定
     */
    protected function setUp(): void
    {
        parent::setUp();

        $this->service = new Ms365MailOauth2Service();

        // キャッシュをクリア
        Cache::flush();
    }

    /**
     * OAuth2設定のキャッシュテスト
     */
    public function testGetOauth2ConfigsCache(): void
    {
        // 設定を作成
        Configs::create([
            'category' => 'mail_oauth2_ms365_app',
            'name' => 'tenant_id',
            'value' => 'test-tenant-id',
        ]);
        Configs::create([
            'category' => 'mail_oauth2_ms365_app',
            'name' => 'client_id',
            'value' => 'test-client-id',
        ]);

        // リフレクションでprivateメソッドにアクセス
        $reflection = new \ReflectionClass($this->service);
        $method = $reflection->getMethod('getOauth2Configs');
        $method->setAccessible(true);

        // 1回目：DBから取得
        $configs1 = $method->invoke($this->service);
        $this->assertCount(2, $configs1);

        // 設定を変更（DBのみ）
        Configs::where('category', 'mail_oauth2_ms365_app')
            ->where('name', 'tenant_id')
            ->update(['value' => 'changed-tenant-id']);

        // 2回目：キャッシュから取得（変更前の値）
        $configs2 = $method->invoke($this->service);
        $tenant_id = Configs::getConfigsValue($configs2, 'tenant_id');

        // キャッシュされているので、変更前の値のまま
        $this->assertEquals('test-tenant-id', $tenant_id);
    }

    /**
     * 連携状態チェックテスト：未連携
     */
    public function testIsConnectedWhenNotConnected(): void
    {
        // is_connected設定なし、またはfalse
        Configs::create([
            'category' => 'mail_oauth2_ms365_app',
            'name' => 'is_connected',
            'value' => '0',
        ]);

        $this->assertFalse($this->service->isConnected());
    }

    /**
     * 連携状態チェックテスト：連携済み
     */
    public function testIsConnectedWhenConnected(): void
    {
        Configs::create([
            'category' => 'mail_oauth2_ms365_app',
            'name' => 'is_connected',
            'value' => '1',
        ]);

        $this->assertTrue($this->service->isConnected());
    }

    /**
     * トークン期限切れチェックテスト：期限切れ
     */
    public function testIsTokenExpiredWhenExpired(): void
    {
        // 過去の有効期限
        Configs::create([
            'category' => 'mail_oauth2_ms365_app',
            'name' => 'token_expires_at',
            'value' => now()->subHours(1)->toDateTimeString(),
        ]);

        $this->assertTrue($this->service->isTokenExpired());
    }

    /**
     * トークン期限切れチェックテスト：5分前（安全マージン）
     */
    public function testIsTokenExpiredWithSafetyMargin(): void
    {
        // 4分後に期限切れ（5分前マージンで期限切れ扱い）
        Configs::create([
            'category' => 'mail_oauth2_ms365_app',
            'name' => 'token_expires_at',
            'value' => now()->addMinutes(4)->toDateTimeString(),
        ]);

        $this->assertTrue($this->service->isTokenExpired());
    }

    /**
     * トークン期限切れチェックテスト：有効
     */
    public function testIsTokenExpiredWhenValid(): void
    {
        // 10分後に期限切れ（まだ有効）
        Configs::create([
            'category' => 'mail_oauth2_ms365_app',
            'name' => 'token_expires_at',
            'value' => now()->addMinutes(10)->toDateTimeString(),
        ]);

        $this->assertFalse($this->service->isTokenExpired());
    }

    /**
     * トークン期限切れチェックテスト：設定なし
     */
    public function testIsTokenExpiredWhenNoConfig(): void
    {
        // token_expires_at設定なし
        $this->assertTrue($this->service->isTokenExpired());
    }

    /**
     * 有効なアクセストークン取得テスト：未連携時のエラー
     */
    public function testGetValidAccessTokenWhenNotConnected(): void
    {
        // 未連携状態
        Configs::create([
            'category' => 'mail_oauth2_ms365_app',
            'name' => 'is_connected',
            'value' => '0',
        ]);

        $this->expectException(\Exception::class);
        $this->expectExceptionMessage('Microsoft 365 OAuth2が未連携です。再度連携を行ってください。');

        $this->service->getValidAccessToken();
    }

    /**
     * 有効なアクセストークン取得テスト：トークンなし
     */
    public function testGetValidAccessTokenWhenNoToken(): void
    {
        // 連携済みだがトークンなし
        Configs::create([
            'category' => 'mail_oauth2_ms365_app',
            'name' => 'is_connected',
            'value' => '1',
        ]);
        Configs::create([
            'category' => 'mail_oauth2_ms365_app',
            'name' => 'token_expires_at',
            'value' => now()->addMinutes(10)->toDateTimeString(),
        ]);

        $this->expectException(\Exception::class);
        $this->expectExceptionMessage('アクセストークンが見つかりません。');

        $this->service->getValidAccessToken();
    }

    /**
     * トークン情報保存テスト
     */
    public function testSaveTokens(): void
    {
        // モックのAccessToken作成
        $token_data = [
            'access_token' => 'test-access-token-12345',
            'expires_in' => 3600,
        ];
        $access_token = new AccessToken($token_data);

        // トークンを保存
        $this->service->saveTokens($access_token);

        // 保存された設定を確認
        $configs = Configs::where('category', 'mail_oauth2_ms365_app')->get();

        // アクセストークンが暗号化されて保存されているか
        $saved_token = Configs::getConfigsValue($configs, 'access_token');
        $this->assertNotNull($saved_token);
        $decrypted_token = decrypt($saved_token);
        $this->assertEquals('test-access-token-12345', $decrypted_token);

        // 有効期限が保存されているか
        $expires_at = Configs::getConfigsValue($configs, 'token_expires_at');
        $this->assertNotNull($expires_at);

        // 最終取得日時が保存されているか
        $obtained_at = Configs::getConfigsValue($configs, 'token_obtained_at');
        $this->assertNotNull($obtained_at);

        // 連携状態が「連携済み」になっているか
        $is_connected = Configs::getConfigsValue($configs, 'is_connected');
        $this->assertEquals('1', $is_connected);
    }

    /**
     * 連携解除テスト
     */
    public function testDisconnect(): void
    {
        // トークン情報を作成
        Configs::create([
            'category' => 'mail_oauth2_ms365_app',
            'name' => 'access_token',
            'value' => encrypt('test-token'),
        ]);
        Configs::create([
            'category' => 'mail_oauth2_ms365_app',
            'name' => 'token_expires_at',
            'value' => now()->addMinutes(10)->toDateTimeString(),
        ]);
        Configs::create([
            'category' => 'mail_oauth2_ms365_app',
            'name' => 'token_obtained_at',
            'value' => now()->toDateTimeString(),
        ]);
        Configs::create([
            'category' => 'mail_oauth2_ms365_app',
            'name' => 'is_connected',
            'value' => '1',
        ]);

        // 連携解除
        $this->service->disconnect();

        // トークン情報が削除されているか
        $configs = Configs::where('category', 'mail_oauth2_ms365_app')->get();
        $this->assertEmpty(Configs::getConfigsValue($configs, 'access_token'));
        $this->assertEmpty(Configs::getConfigsValue($configs, 'token_expires_at'));
        $this->assertEmpty(Configs::getConfigsValue($configs, 'token_obtained_at'));

        // 連携状態が「未連携」になっているか
        $is_connected = Configs::getConfigsValue($configs, 'is_connected');
        $this->assertEquals('0', $is_connected);
    }

    /**
     * 最終トークン取得日時取得テスト
     */
    public function testGetTokenObtainedAt(): void
    {
        $obtained_at = now()->toDateTimeString();

        Configs::create([
            'category' => 'mail_oauth2_ms365_app',
            'name' => 'token_obtained_at',
            'value' => $obtained_at,
        ]);

        $result = $this->service->getTokenObtainedAt();
        $this->assertEquals($obtained_at, $result);
    }

    /**
     * 最終トークン取得日時取得テスト：設定なし
     */
    public function testGetTokenObtainedAtWhenNoConfig(): void
    {
        $result = $this->service->getTokenObtainedAt();
        $this->assertEmpty($result);
    }

    /**
     * Providerインスタンス取得テスト
     */
    public function testGetProvider(): void
    {
        // OAuth2設定を作成
        Configs::create([
            'category' => 'mail_oauth2_ms365_app',
            'name' => 'tenant_id',
            'value' => 'test-tenant-id',
        ]);
        Configs::create([
            'category' => 'mail_oauth2_ms365_app',
            'name' => 'client_id',
            'value' => 'test-client-id',
        ]);
        Configs::create([
            'category' => 'mail_oauth2_ms365_app',
            'name' => 'client_secret',
            'value' => encrypt('test-client-secret'),
        ]);

        $provider = $this->service->getProvider();

        // GenericProviderのインスタンスか
        $this->assertInstanceOf(GenericProvider::class, $provider);
    }
}
