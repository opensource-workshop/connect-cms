<?php

namespace Tests\Browser\Manage;

use Illuminate\Foundation\Testing\DatabaseMigrations;
use Laravel\Dusk\Browser;
use Tests\DuskTestCase;

use App\Models\Core\Configs;

/**
 * > tests\bin\connect-cms-test.bat
 */
class UserManageTest extends DuskTestCase
{
    /**
     * テストする関数の制御
     *
     * @group manage
     * @see https://readouble.com/laravel/6.x/ja/dusk.html#running-tests
     */
    public function testInvoke()
    {
        $this->login(1);
        $this->originalRole('1', 'student', '学生');
        $this->saveOriginalRoles();
        $this->originalRole('2', 'teacher', '教員');
        $this->saveOriginalRoles();
        $this->regist();
        $this->register();
        $this->import();
        $this->index();
    }

    /**
     * index の表示
     */
    private function index()
    {
        // ユーザ一覧の表示
        $this->browse(function (Browser $browser) {
            $browser->visit('/manage/user')
                    ->assertTitleContains('Connect-CMS')
                    ->screenshot('manage/user/index/images/index');
        });

        // 絞り込み画面
        $this->browse(function (Browser $browser) {
            $browser->click('#user_search_condition')
                    ->assertTitleContains('Connect-CMS')
                    ->pause(500)
                    ->screenshot('manage/user/index/images/user_search_condition');
        });
        $this->browse(function (Browser $browser) {
            $browser->scrollIntoView('#user_search_condition_status');
            $browser->screenshot('manage/user/index/images/user_search_condition2');
        });

        // マニュアル用データ出力
        $this->putManualData('[
            {"path": "manage/user/index/images/index",
             "name": "ユーザ一覧",
             "comment": "<ul class=\"mb-0\"><li>ユーザの一覧が表示されます。</li><li>「グループ」列の編集ボタンから、「グループ参加」画面に飛べます。</li><li>「最終ログイン日時」列の履歴リンクから、「ログイン履歴」画面に飛べます</li></ul>"
            },
            {"path": "manage/user/index/images/user_search_condition",
             "name": "絞り込み画面"
            },
            {"path": "manage/user/index/images/user_search_condition2",
             "name": "絞り込み画面２",
             "comment": "<ul class=\"mb-0\"><li>様々な条件でユーザを絞り込むことができます。</li><li>絞り込んだ内容でダウンロードすることができます。</li></ul>"
            }
        ]');
    }

    /**
     * 役割設定画面
     */
    private function originalRole($add_additional1, $add_name, $add_value)
    {
        $this->browse(function (Browser $browser) use ($add_additional1, $add_name, $add_value) {
            $browser->visit('/manage/user/originalRole')
                    ->type('add_additional1', $add_additional1)
                    ->type('add_name', $add_name)
                    ->type('add_value', $add_value)
                    ->assertPathIs('/manage/user/originalRole')
                    ->screenshot('manage/user/originalRole/images/originalRole');
        });
    }

    /**
     * 役割設定追加処理
     */
    private function saveOriginalRoles()
    {
        $this->browse(function (Browser $browser) {
            $browser->press('変更')
                    ->assertPathIs('/manage/user/originalRole')
                    ->screenshot('manage/user/saveOriginalRoles/images/saveOriginalRoles');
        });
    }

    /**
     * ユーザ登録画面
     */
    private function regist()
    {
        $this->browse(function (Browser $browser) {

            // 役割設定を取得して、学生にする。
            $original_role_student = Configs::where('category', 'original_role')->where('name', 'student')->first();

            $browser->visit('/manage/user/regist')
                    ->type('name', 'テストユーザ')
                    ->type('userid', 'test-user')
                    ->type('email', 'test@osws.jp')
                    ->type('password', 'test-user')
                    ->type('password_confirmation', 'test-user')
                    ->click('#label_role_reporter')
                    ->click('#label_original_role' . $original_role_student->id)
                    ->assertTitleContains('Connect-CMS')
                    ->screenshot('manage/user/regist/images/regist');
        });
    }

    /**
     * ユーザ登録処理
     */
    private function register()
    {
        $this->browse(function (Browser $browser) {
            $browser->press('ユーザ登録')
                    ->assertTitleContains('Connect-CMS')
                    ->screenshot('manage/user/register/images/register');
        });
    }

    /**
     * インポート＆ページ送りテスト
     *
     * @group manage
     */
    private function testPaginate()
    {
        $this->login(1);
        $this->import();
        $this->index();
        $this->indexPage2();
    }

    /**
     * CSVインポート処理
     */
    private function import()
    {
        $this->browse(function (Browser $browser) {
            $browser->visit('/manage/user/import')
                    ->attach('users_csv', __DIR__.'/users.csv')
                    ->press('インポート')
                    ->acceptDialog()
                    ->assertSee('インポートしました')
                    ->screenshot('manage/user/import/images/import');
        });
    }

    /**
     * index の2ページ目表示
     */
    private function indexPage2()
    {
        $this->browse(function (Browser $browser) {
            $browser->visit('/manage/user?page=2')
                ->assertSee('ユーザ一覧')
                ->assertDontSee('500')        // "500" 文字がない事
                ->screenshot('manage/user/import/images/import2');
        });
    }
}
