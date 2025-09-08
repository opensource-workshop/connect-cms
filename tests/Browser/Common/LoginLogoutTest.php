<?php

namespace Tests\Browser\Common;

use Illuminate\Foundation\Testing\DatabaseMigrations;
use Laravel\Dusk\Browser;
use Tests\DuskTestCase;

use App\Models\Common\Frame;
use App\Models\Core\Dusks;

use App\Enums\PluginName;

/**
 * ログイン・ログアウトテスト
 *
 * @see https://github.com/opensource-workshop/connect-cms/wiki/Dusk#テスト実行 [How to test]
 */
class LoginLogoutTest extends DuskTestCase
{
    /**
     * テストする関数の制御
     *
     * @group user
     * @see https://readouble.com/laravel/6.x/ja/dusk.html#running-tests
     */
    public function testInvoke()
    {
        $this->loginTest();
        $this->logoutTest();
        $this->resetTest();
    }

    /**
     * ログイン
     */
    private function loginTest()
    {
        // ログイン画面
        $this->browse(function (Browser $browser) {
            $browser->visit('/')
                    ->assertTitleContains('Connect-CMS')
                    ->screenshot('common/index/index/images/login1');

            $browser->clickLink('ログイン')
                    ->assertTitleContains('Connect-CMS')
                    ->screenshot('common/index/index/images/login2');

            $browser->type('#userid', 'admin')
                    ->type('#password', 'C-admin')
                    ->assertTitleContains('Connect-CMS')
                    ->screenshot('common/index/index/images/login3');

            $browser->click('@login-button')
                    ->assertTitleContains('Connect-CMS')
                    ->screenshot('common/index/index/images/login4');
        });

        // マニュアル用データ出力
        $dusk = Dusks::putManualData(
            ['html_path' => 'common/index/index/index.html'],
            ['category' => 'common',
             'sort' => 2,
             'plugin_name' => 'index',
             'plugin_title' => 'ログイン関係',
             'plugin_desc' => 'ログイン・ログアウトなどログインに関係する操作方法について説明します。',
             'method_name' => 'index',
             'method_title' => 'ログイン',
             'method_desc' => 'ログインの方法を紹介します。',
             'method_detail' => '',
             'html_path' => 'common/index/index/index.html',
             'img_args' => '[
                 {"path": "common/index/index/images/login1",
                  "name": "ログイン前",
                  "methods": [
                     {"method": "trim_h", "args": [0,200]},
                     {"method": "arc", "args": [1225,30,100,50,6]}],
                  "comment": "<ul class=\"mb-0\"><li>画面の右上にログインリンクがあります。</li><li>設定でログインリンクを消している場合があります。その場合は管理者にログイン用のURLを確認してください。</li></ul>"
                 },
                 {"path": "common/index/index/images/login2",
                  "name": "ログインID、パスワードの入力",
                  "methods": [
                     {"method": "trim_h", "args": [0,300]},
                     {"method": "arc", "args": [700,160,300,50,6]},
                     {"method": "arc", "args": [700,220,300,50,6]}],
                  "comment": "<ul class=\"mb-0\"><li>ログインID、パスワードを入力してログインします。</li><li>「ログイン状態を維持する」にチェックを入れてログインすることで、ブラウザを閉じてもログイン状態を維持することができます。</li></ul>"
                 },
                 {"path": "common/index/index/images/login3",
                  "name": "ログインボタンのクリック",
                  "methods": [
                     {"method": "trim_h", "args": [0,400]},
                     {"method": "arc", "args": [585,318,150,50,10]}
                 ]},
                 {"path": "common/index/index/images/login4",
                  "name": "ログイン後",
                  "methods": [
                     {"method": "trim_h", "args": [0,300]},
                     {"method": "arc", "args": [1190,30,160,50,10]}],
                  "comment": "<ul class=\"mb-0\"><li>ログインできると、画面右上にログインユーザ名が表示されます。</li></ul>"
                 }
             ]',
             'level' => 'basic',
             'test_result' => 'OK']
        );
    }

    /**
     * ログアウト
     */
    private function logoutTest()
    {
        $this->login(1);

        // ログアウト
        $this->browse(function (Browser $browser) {
            $browser->visit('/')
                    ->assertTitleContains('Connect-CMS')
                    ->screenshot('common/index/logout/images/logout1');

            $browser->waitFor('#dropdown_auth')->click('#dropdown_auth')
                    ->assertTitleContains('Connect-CMS')
                    ->screenshot('common/index/logout/images/logout2');

            $browser->clickLink('ログアウト')
                    ->assertTitleContains('Connect-CMS')
                    ->screenshot('common/index/logout/images/logout3');
        });

        // マニュアル用データ出力
        $dusk = Dusks::putManualData(
            ['html_path' => 'common/index/logout/index.html'],
            ['category' => 'common',
             'sort' => 2,
             'plugin_name' => 'index',
             'plugin_title' => 'ログイン関係',
             'plugin_desc' => '',
             'method_name' => 'logout',
             'method_title' => 'ログアウト',
             'method_desc' => 'ログアウトの方法を紹介します。',
             'method_detail' => '',
             'html_path' => 'common/index/logout/index.html',
             'img_args' => '[
                 {"path": "common/index/logout/images/logout1",
                  "name": "ログイン状態",
                  "methods": [
                     {"method": "trim_h", "args": [0,250]},
                     {"method": "arc", "args": [1190,30,160,50,10]}],
                  "comment": "<ul class=\"mb-0\"><li>ログイン中のユーザ名をクリックします。</li></ul>"
                 },
                 {"path": "common/index/logout/images/logout2",
                  "name": "ログアウトをクリック",
                  "methods": [
                     {"method": "trim_h", "args": [0,300]},
                     {"method": "arc", "args": [1190,128,160,50,10]}
                 ]},
                 {"path": "common/index/logout/images/logout3",
                  "name": "ログアウト済み",
                  "methods": [
                     {"method": "trim_h", "args": [0,250]},
                     {"method": "arc", "args": [1225,30,100,50,6]}
                 ]}
             ]',
             'level' => null,
             'test_result' => 'OK']
        );
    }

    /**
     * パスワードリセット
     */
    private function resetTest()
    {
        // ログイン画面
        $this->browse(function (Browser $browser) {
            $browser->visit('/')
                    ->clickLink('ログイン')
                    ->assertTitleContains('Connect-CMS')
                    ->screenshot('common/index/reset/images/reset1');

            $browser->click('@login_password_reset')
                    ->assertTitleContains('Connect-CMS')
                    ->screenshot('common/index/reset/images/reset2');

            $browser->visit('/password/reset/' . $browser->value('@token'))
                    ->assertTitleContains('Connect-CMS')
                    ->screenshot('common/index/reset/images/reset3');
        });

        // パスワードリセット・メールはあらかじめ用意しておいたメールサンプル画像を使用
        \Storage::disk('screenshot')->put('common/index/reset/images/password_reset_mail.png', \Storage::disk('manual')->get('copy_data/image/password_reset_mail.png'));

        // マニュアル用データ出力
        $dusk = Dusks::putManualData(
            ['html_path' => 'common/index/reset/index.html'],
            ['category' => 'common',
             'sort' => 2,
             'plugin_name' => 'index',
             'plugin_title' => 'ログイン関係',
             'plugin_desc' => 'ログイン・ログアウトなどログインに関係する操作方法について説明します。',
             'method_name' => 'reset',
             'method_title' => 'パスワード リセット',
             'method_desc' => 'パスワード リセットの方法を紹介します。',
             'method_detail' => '',
             'html_path' => 'common/index/reset/index.html',
             'img_args' => '[
                 {"path": "common/index/reset/images/reset1",
                  "name": "ログイン画面",
                  "methods": [
                     {"method": "trim_h", "args": [0,400]},
                     {"method": "arc", "args": [750,316,226,52,10]}],
                  "comment": "<ul class=\"mb-0\"><li>パスワードを忘れた場合はこのリンクをクリックします。</li><li>「パスワードを忘れた場合」のリンクがない場合は管理者が設定で消している可能性があります。<br />その場合は管理者にパスワードを忘れた場合の対応方法について確認してください。</li></ul>"
                 },
                 {"path": "common/index/reset/images/reset2",
                  "name": "パスワードリセット",
                  "methods": [
                     {"method": "trim_h", "args": [0,300]}],
                  "comment": "<ul class=\"mb-0\"><li>登録しているユーザのメールアドレスを入力して、「パスワードのリセットリンクを送信する。」をクリックします。</li></ul>"
                 },
                 {"path": "common/index/reset/images/password_reset_mail",
                  "name": "パスワードリセット・確認メール",
                  "comment": "<ul class=\"mb-0\"><li>パスワードを忘れた場合はパスワードリセット機能を使って、登録してあるメールアドレス宛にリセットリンクを送ることができます。</li><li>メールアドレスを設定していない場合は、管理者に連絡してパスワードを変更してもらってください。</li><li>パスワードリセットでは、メールをクリックし、画面を表示した後、対象のメールアドレスと新パスワードを入力してパスワード変更が完了します。<br />ウイルスチェックソフトなどがURLを疑似クリックする動作をした場合でも、意図しない承認が行われることがないため、安心です。</ul>",
                  "style": "width: 100%; max-width: 800px;"
                 },
                 {"path": "common/index/reset/images/reset3",
                  "name": "リセット",
                  "methods": [
                     {"method": "trim_h", "args": [0,400]}],
                  "comment": "<ul class=\"mb-0\"><li>パスワード変更するユーザの eメールと新しいパスワードを入力してパスワードリセットをクリックします。</li></ul>"
                 }
             ]',
             'level' => 'basic',
             'test_result' => 'OK']
        );
    }
}
