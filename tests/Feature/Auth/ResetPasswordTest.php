<?php

namespace Tests\Feature\Auth;

use App\Enums\UserStatus;
use App\User;
use App\Models\Core\Configs;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Password;
use Tests\TestCase;

class ResetPasswordTest extends TestCase
{
    use RefreshDatabase;

    /**
     * テスト前にパスワードリセット機能を有効化する設定を投入
     */
    protected function setUp(): void
    {
        parent::setUp();

        Configs::factory()->create([
            'name' => 'base_login_password_reset',
            'value' => '1',
            'category' => 'base',
        ]);
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
}
