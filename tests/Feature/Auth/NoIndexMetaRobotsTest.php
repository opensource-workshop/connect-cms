<?php

namespace Tests\Feature\Auth;

use App\Models\Core\Configs;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * ログイン・ユーザー登録・パスワード再設定の各画面が、検索エンジン向けに
 * noindexを出力し続けることを守る。Route::currentRouteName()による判定は
 * ルート名変更時に静かに機能しなくなるため、回帰検知の目的で用意する。
 */
class NoIndexMetaRobotsTest extends TestCase
{
    use RefreshDatabase;

    private const NOINDEX_TAG = '<meta name="robots" content="noindex, nofollow">';

    public function testLoginPageHasNoIndexMetaRobots()
    {
        $response = $this->get('/login');

        $response->assertOk();
        $response->assertSee(self::NOINDEX_TAG, false);
    }

    public function testRegisterPageHasNoIndexMetaRobots()
    {
        Configs::factory()->create([
            'category' => 'user_register',
            'name' => 'user_register_enable',
            'value' => '1',
            'additional1' => 1,
        ]);

        $response = $this->get('/register');

        $response->assertOk();
        $response->assertSee(self::NOINDEX_TAG, false);
    }

    public function testPasswordResetPageHasNoIndexMetaRobots()
    {
        Configs::factory()->create([
            'category' => 'base',
            'name' => 'base_login_password_reset',
            'value' => '1',
        ]);

        $response = $this->get('/password/reset');

        $response->assertOk();
        $response->assertSee(self::NOINDEX_TAG, false);
    }
}
