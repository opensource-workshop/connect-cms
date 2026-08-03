<?php

namespace Tests\Feature\Auth;

use App\Enums\UserStatus;
use App\User;
use App\Models\Core\Configs;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Facades\URL;
use Symfony\Component\HttpFoundation\Request as SymfonyRequest;
use Tests\TestCase;

/**
 * パスワードリセット機能について、利用者から見た申請可否と更新可否をFeatureテストで守る。
 * セキュリティ上重要なHost検証は、公開ルート経由でメール本文まで確認する。
 */
class ResetPasswordTest extends TestCase
{
    use RefreshDatabase;

    /**
     * テスト前にパスワードリセット機能を有効化する設定を投入
     */
    protected function setUp(): void
    {
        parent::setUp();

        config([
            'app.env' => 'testing',
            'app.url' => 'http://localhost',
        ]);
        URL::forceRootUrl(null);
        SymfonyRequest::setTrustedHosts([]);
        $this->flushSentMessages();

        Configs::factory()->create([
            'name' => 'base_login_password_reset',
            'value' => '1',
            'category' => 'base',
        ]);
    }

    /**
     * Host検証の静的状態を次のテストへ持ち越さないよう初期化する
     */
    protected function tearDown(): void
    {
        URL::forceRootUrl(null);
        SymfonyRequest::setTrustedHosts([]);

        parent::tearDown();
    }

    /**
     * 非アクティブユーザーにはリセットメールが送信されないことを確認
     */
    public function testInactiveUserCannotRequestResetLink()
    {
        $user = User::factory()->create([
            'status' => UserStatus::not_active,
        ]);

        $response = $this->from('/password/reset')->post('/password/email', [
            'email' => $user->email,
        ]);

        $response->assertRedirect('/password/reset');
        $response->assertSessionHas('status', trans('passwords.sent'));
        $this->assertGuest();
        $this->assertDatabaseMissing('password_resets', [
            'email' => $user->email,
        ]);
    }

    /**
     * 許可していないHostヘッダでは、リセットメール送信処理へ進ませないことを確認
     */
    public function testUntrustedHostCannotRequestResetLink()
    {
        config([
            'app.env' => 'production',
            'app.url' => 'https://connect.example.test',
        ]);

        $user = User::factory()->create([
            'status' => UserStatus::active,
        ]);

        $response = $this->from('https://evil.example.test/password/reset')
            ->post('https://evil.example.test/password/email', [
                'email' => $user->email,
            ]);

        $response->assertNotFound();
        $this->assertNoMailSent();
    }

    /**
     * APP_URLのHostではリセット申請を許可し、メールのリンクも正規Hostになることを確認
     */
    public function testAppUrlHostCanRequestResetLink()
    {
        config([
            'app.env' => 'production',
            'app.url' => 'https://connect.example.test',
        ]);

        $user = User::factory()->create([
            'status' => UserStatus::active,
        ]);

        $response = $this->from('https://connect.example.test/password/reset')
            ->post('https://connect.example.test/password/email', [
                'email' => $user->email,
            ]);

        $response->assertRedirect('https://connect.example.test/password/reset');
        $response->assertSessionHas('status', trans('passwords.sent'));
        $this->assertDatabaseHas('password_resets', [
            'email' => $user->email,
        ]);
        $this->assertResetMailContainsHost('connect.example.test');
    }

    /**
     * APP_URL配下のサブドメインでも、正規Hostでなければリセットメール送信処理へ進ませないことを確認
     */
    public function testSubdomainOfAppUrlCannotRequestResetLink()
    {
        config([
            'app.env' => 'production',
            'app.url' => 'https://example.test',
        ]);

        $user = User::factory()->create([
            'status' => UserStatus::active,
        ]);

        $response = $this->from('https://www.example.test/password/reset')
            ->post('https://www.example.test/password/email', [
                'email' => $user->email,
            ]);

        $response->assertNotFound();
        $this->assertNoMailSent();
    }

    /**
     * 非アクティブユーザーはトークンを用いてもパスワードを更新できないことを確認
     */
    public function testInactiveUserCannotResetPasswordEvenWithToken()
    {
        $user = User::factory()->create([
            'status' => UserStatus::not_active,
            'password' => Hash::make('old-password'),
        ]);

        $token = Password::broker()->createToken($user);

        $response = $this->from('/password/reset/'.$token)->post('/password/reset', [
            'token' => $token,
            'email' => $user->email,
            'password' => 'new-password',
            'password_confirmation' => 'new-password',
        ]);

        $response->assertRedirect('/password/reset/'.$token);
        $response->assertSessionHasErrors('email');

        $this->assertGuest();

        $user->refresh();
        $this->assertTrue(Hash::check('old-password', $user->password));
    }

    /**
     * アクティブユーザーはパスワード更新後に自動ログインされることを確認
     */
    public function testActiveUserCanResetPasswordAndIsLoggedIn()
    {
        $user = User::factory()->create([
            'status' => UserStatus::active,
            'password' => Hash::make('old-password'),
        ]);

        $token = Password::broker()->createToken($user);

        $response = $this->post('/password/reset', [
            'token' => $token,
            'email' => $user->email,
            'password' => 'new-password',
            'password_confirmation' => 'new-password',
        ]);

        $response->assertRedirect('/');
        $this->assertAuthenticatedAs($user->fresh());

        $user->refresh();
        $this->assertTrue(Hash::check('new-password', $user->password));
    }

    /**
     * メールが送信されていないことを確認する。
     *
     * @return void
     */
    private function assertNoMailSent(): void
    {
        $this->assertCount(0, $this->sentMessages());
    }

    /**
     * 送信されたパスワードリセットメールが期待Hostのリンクを含むことを補助する。
     *
     * @param  string  $host
     * @return void
     */
    private function assertResetMailContainsHost(string $host): void
    {
        $messages = $this->sentMessages();

        $this->assertCount(1, $messages);
        $this->assertStringContainsString('://' . $host . '/password/reset/', $messages->first()->getBody());
    }

    /**
     * arrayメールドライバに蓄積された送信済みメールを取得する。
     *
     * PasswordResetNotification は Mailable を返すが、MailChannel 経由では Mail::fake() が
     * ConnectMail の送信として捕捉できないため、実際の送信内容を array ドライバから確認する。
     *
     * @return \Illuminate\Support\Collection
     */
    private function sentMessages()
    {
        return app('mailer')->getSwiftMailer()->getTransport()->messages();
    }

    /**
     * arrayメールドライバに蓄積された送信済みメールを初期化する。
     *
     * @return void
     */
    private function flushSentMessages(): void
    {
        $transport = app('mailer')->getSwiftMailer()->getTransport();

        if (method_exists($transport, 'flush')) {
            $transport->flush();
        }
    }
}
