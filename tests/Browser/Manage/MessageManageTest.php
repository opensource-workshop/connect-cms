<?php

namespace Tests\Browser\Manage;

use Illuminate\Foundation\Testing\DatabaseMigrations;
use Laravel\Dusk\Browser;
use Tests\DuskTestCase;

/**
 * > tests\bin\connect-cms-test.bat
 */
class MessageManageTest extends DuskTestCase
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
        $this->message();
        $this->update();
    }

    /**
     * 初期画面
     */
    private function index()
    {
        $this->browse(function (Browser $browser) {
            $browser->visit('/manage/message')
                    ->assertTitleContains('Connect-CMS')
                    ->screenshot('manage/message/index/images/index');
        });

        $this->browse(function (Browser $browser) {
            $browser->scrollIntoView('footer');
            $browser->screenshot('manage/message/index/images/index2');
        });

        // マニュアル用データ出力
        $this->putManualData('[
            {"path": "manage/message/index/images/index",
             "name": "初回確認メッセージ"
            },
            {"path": "manage/message/index/images/index2",
             "name": "初回確認メッセージ２",
             "comment": "<ul class=\"mb-0\"><li>サイトの利用確認など、初回にアクセスした際に確認したいメッセージを表示することができる設定です。</li></ul>"
            }
        ]');
    }

    /**
     * 初回確認メッセージの入力
     */
    private function message()
    {
        $this->browse(function (Browser $browser) {
            $browser->click('#label_message_first_show_type_1')
                    ->type('message_first_content', 'テストのメッセージです。')
                    ->type('message_first_button_name', '確認')
                    ->assertTitleContains('Connect-CMS')
                    ->screenshot('manage/message/index/images/message');
        });
    }

    /**
     * 初回確認メッセージの登録
     */
    private function update()
    {
        $this->browse(function (Browser $browser) {
            // 設定の保存
            $browser->press('更新')
                    ->assertTitleContains('Connect-CMS');
            $this->screenshot($browser);

            // 一般画面の表示
            $browser->visit('/')
                    ->assertSee('テストのメッセージです。');
            $this->screenshot($browser);

            // メッセージを表示しないに戻す
            $browser->visit('/manage/message')
                    ->click('#label_message_first_show_type_0')
                    ->press('更新')
                    ->assertTitleContains('Connect-CMS')
                    ->screenshot('manage/message/index/images/update');
        });
    }
}
