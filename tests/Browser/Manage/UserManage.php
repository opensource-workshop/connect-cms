<?php

namespace Tests\Browser\Manage;

use Illuminate\Foundation\Testing\DatabaseMigrations;
use Laravel\Dusk\Browser;
use Tests\DuskTestCase;

use App\Models\Core\Configs;

/**
 * > tests\bin\connect-cms-test.bat
 */
class UserManage extends DuskTestCase
{
    /**
     * テストする関数の制御
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
        $this->index();
    }

    /**
     * index の表示
     */
    private function index()
    {
        $this->browse(function (Browser $browser) {
            $browser->visit('/manage/user')
                    ->assertTitleContains('Laravel');
            $this->screenshot($browser);
        });
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
                    ->assertPathIs('/manage/user/originalRole');
            $this->screenshot($browser);
        });
    }

    /**
     * 役割設定追加処理
     */
    private function saveOriginalRoles()
    {
        $this->browse(function (Browser $browser) {
            $browser->press('変更')
                    ->assertPathIs('/manage/user/saveOriginalRoles');
            $this->screenshot($browser);
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
                    ->assertTitleContains('Laravel');
            $this->screenshot($browser);
        });
    }

    /**
     * ユーザ登録処理
     */
    private function register()
    {
        $this->browse(function (Browser $browser) {
            $browser->press('ユーザ登録')
                    ->assertTitleContains('Laravel');
            $this->screenshot($browser);
        });
    }
}
