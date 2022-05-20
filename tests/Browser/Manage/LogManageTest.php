<?php

namespace Tests\Browser\Manage;

use Illuminate\Foundation\Testing\DatabaseMigrations;
use Laravel\Dusk\Browser;
use Tests\DuskTestCase;

class LogManageTest extends DuskTestCase
{
    /**
     * テストする関数の制御
     *
     * @group manage
     * @see https://readouble.com/laravel/6.x/ja/dusk.html#running-tests
     */
    public function testInvoke()
    {
        $this->login(1); // user id = 1(admin)でログイン
        $this->index();
        $this->edit();
    }

    /**
     * ログ一覧
     */
    private function index()
    {
        // 実行
        $this->browse(function (Browser $browser) {
            $browser->visit('/manage/log')
                    ->assertTitle('Connect-CMS')
                    ->screenshot('manage/log/index/images/index');

            $browser->scrollIntoView('footer')
                    ->screenshot('manage/log/index/images/index2');

            // ブラウザを一番上の位置でキャプチャするために、一度、別ページに移動して再度表示
            $browser->visit('/manage/edit')
                    ->visit('/manage/log')
                    ->click('#app_log_search_condition_button')
                    ->assertTitleContains('Connect-CMS');

            // collapseが表示されるまで、ちょっと待つ
            $browser->pause(500)
                    ->screenshot('manage/log/index/images/index3');
        });

        $this->putManualData('[
            {"path": "manage/log/index/images/index",
             "name": "ログ一覧"
            },
            {"path": "manage/log/index/images/index2",
             "name": "ログ一覧２",
             "comment": "<ul class=\"mb-0\"><li>ログ設定で設定した種類のログが保存され、一覧で表示されます。<br />ダウンロードもできます。</li></ul>"
            },
            {"path": "manage/log/index/images/index3",
             "name": "絞り込み条件",
             "comment": "<ul class=\"mb-0\"><li>保存されているログを絞り込むことができます。</li></ul>"
            }
        ]', null, 3);
    }

    /**
     * ログ設定
     */
    private function edit()
    {
        // 実行
        $this->browse(function (Browser $browser) {
            $browser->visit('/manage/log/edit')
                    ->click('#app_log_scope_select_label')
                    ->click('#save_log_type_login_label')
                    ->click('#save_log_type_manage_label')
                    ->click('#save_log_type_sendmail_label')
                    ->press('更新')
                    ->assertTitle('Connect-CMS')
                    ->screenshot('manage/log/edit/images/edit');
        });

        // マニュアル用データ出力
        $this->putManualData('[
            {"path": "manage/log/edit/images/edit",
             "name": "ユーザディレクトリ一覧",
             "comment": "<ul class=\"mb-0\"><li>記録するログ種別を設定できます。</li></ul>"
            }
        ]', null, 3);
    }
}
