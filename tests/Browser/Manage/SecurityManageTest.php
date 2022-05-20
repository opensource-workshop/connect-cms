<?php

namespace Tests\Browser\Manage;

use Illuminate\Foundation\Testing\DatabaseMigrations;
use Laravel\Dusk\Browser;
use Tests\DuskTestCase;

/**
 * > tests\bin\connect-cms-test.bat
 */
class SecurityManageTest extends DuskTestCase
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
        $this->index();
        $this->security();
        $this->saveLoginPermit();
        $this->purifier();
        $this->savePurifier();

        // マニュアル用
        $this->index();
        $this->purifier();
    }

    /**
     * index の表示
     */
    private function index()
    {
        $this->browse(function (Browser $browser) {
            $browser->visit('/manage/security')
                    ->assertTitleContains('Connect-CMS')
                    ->screenshot('manage/security/index/images/index');
        });

        // マニュアル用データ出力
        $this->putManualData('[
            {"path": "manage/security/index/images/index",
             "name": "ログイン制限",
             "comment": "<ul class=\"mb-0\"><li>ログインできるIPアドレスを設定することができます。</li><li>IPアドレスはCIDR形式の設定も可能です。</li><li>IPアドレスと権限の組み合わせで許可、拒否を設定できます。</li><li>複数の設定を行った場合は、適用順にすべての条件を評価します。</li><li>この設定を行うことで、管理者権限は特定のIPアドレスからのみ許可する。すべてのログインは特定のIPアドレスからのみ許可する。など、複雑な条件も設定可能です。</li><li>運用引き継ぎ等を想定してメモも追記することができます。</li></ul>"
            }
        ]', null, 3);
    }

    /**
     * ログイン制限画面
     */
    private function security()
    {
        $this->browse(function (Browser $browser) {
            $browser->visit('/manage/security')
                    ->type('add_apply_sequence', '1')
                    ->type('add_ip_address', '*')
                    ->select('add_role', 'role_reporter')
                    ->click('#label_add_reject_on')
                    ->assertTitleContains('Connect-CMS')
                    ->screenshot('manage/security/security/images/security');
        });
    }

    /**
     * ログイン制限登録処理
     */
    private function saveLoginPermit()
    {
        $this->browse(function (Browser $browser) {
            $browser->press('変更')
                    ->assertTitleContains('Connect-CMS')
                    ->screenshot('manage/security/index/images/index');
        });
    }

    /**
     * HTML記述制限画面
     */
    private function purifier()
    {
        $this->browse(function (Browser $browser) {
            $browser->visit('/manage/security/purifier')
                    ->click('#label_role_approval_0')
                    ->check('confirm_purifier')
                    ->assertTitleContains('Connect-CMS')
                    ->screenshot('manage/security/purifier/images/purifier');
        });

        // マニュアル用データ出力
        $this->putManualData('[
            {"path": "manage/security/purifier/images/savePurifier",
             "name": "HTML記述制限",
             "comment": "<ul class=\"mb-0\"><li>権限毎にJavaScript等の制限を行うかどうかを設定できます。</li><li>「制限する」に設定している場合は、JavaScript等が記述されても、自動で消去されます。</li><li>編集者などにJavaScript等の記述を許可する場合は、運用面での危険性を十分ご理解いただき、規約や契約などでも対応するなど、ご注意ください。</li></ul>"
            }
        ]', null, 3);
    }

    /**
     * HTML記述制限登録処理
     */
    private function savePurifier()
    {
        $this->browse(function (Browser $browser) {
            $browser->press('登録')
                    ->assertTitleContains('Connect-CMS')
                    ->screenshot('manage/security/purifier/images/savePurifier');
        });
    }
}
