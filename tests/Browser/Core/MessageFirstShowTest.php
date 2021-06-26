<?php

namespace Tests\Browser\Core;

use Illuminate\Foundation\Testing\DatabaseMigrations;
use Laravel\Dusk\Browser;
use Tests\DuskTestCase;

use App\Models\Core\Configs;

class MessageFirstShowTest extends DuskTestCase
{
    /**
     * 初回確認メッセージ動作テスト
     *
     * @return void
     *
     * @group core
     * @see https://readouble.com/laravel/6.x/ja/dusk.html#running-tests
     */
    public function testMessageFirstShow()
    {
        // --- 更新
        // 初回確認メッセージ（利用有無）
        $configs = Configs::updateOrCreate(
            ['name' => 'message_first_show_type'],
            [
                'category' => 'message',
                'value'    => 1     // 1:表示する
            ]
        );

        $this->browse(function (Browser $browser) {
            $browser->visit('/')
                ->press('確認')
                ->assertTitleContains('Connect-CMS');
            parent::screenshot($browser);
        });

        // 設定を戻す
        $configs = Configs::updateOrCreate(
            ['name' => 'message_first_show_type'],
            [
                'category' => 'message',
                'value'    => 0     // 0:表示しない
            ]
        );
    }
}
