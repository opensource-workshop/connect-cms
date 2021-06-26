<?php

namespace Tests\Browser\Core;

use Illuminate\Foundation\Testing\DatabaseMigrations;
use Laravel\Dusk\Browser;
use Tests\DuskTestCase;

use App\Models\Core\Configs;

/**
 * 初回確認メッセージ動作テスト 項目フル入力
 */
class MessageFirstShowFullTest extends DuskTestCase
{
    /**
     * 初回確認メッセージ動作テスト 項目フル入力
     *
     * @return void
     *
     * @group core
     */
    public function testMessageFirstShowFull()
    {
        // クッキーをリセットするため、複数回の初回確認メッセージテストは別ける
        // setcookie('connect_cookie_message_first');
        // Cookie::queue('connect_cookie_message_first', null, 525600);

        // --- 更新
        // 初回確認メッセージ（利用有無）ON
        $this->messageFirstShowTypeOn();

        $configs = Configs::updateOrCreate(
            ['name' => 'message_first_content'],
            [
                'category' => 'message',
                'value'    => 'テストの初回確認メッセージです。２',
            ]
        );

        $configs = Configs::updateOrCreate(
            ['name' => 'message_first_button_name'],
            [
                'category' => 'message',
                'value'    => '確認２',
            ]
        );

        // 初回確認メッセージ（枠外クリック等の離脱許可）
        $configs = Configs::updateOrCreate(
            ['name'     => 'message_first_permission_type'],
            ['category' => 'message',
             'value'    => 1]       // 1:許可する
        );

        // 初回確認メッセージ（除外URL）
        $configs = Configs::updateOrCreate(
            ['name'     => 'message_first_exclued_url'],
            ['category' => 'message',
             'value'    => '/about,/policy']
        );

        // 初回確認メッセージ（メッセージウィンドウ任意クラス）
        $configs = Configs::updateOrCreate(
            ['name'     => 'message_first_optional_class'],
            ['category' => 'message',
             'value'    => 'message_first_optional_class']
        );

        $this->browse(function (Browser $browser) {
            $browser->visit('/')
                ->press('確認２')
                ->assertTitleContains('Connect-CMS');
            parent::screenshot($browser);
        });

        // 設定OFF
        $this->messageFirstShowTypeOff();
    }

    /**
     * 初回確認メッセージ（利用有無）ON
     */
    private function messageFirstShowTypeOn()
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
    }

    /**
     * 初回確認メッセージ（利用有無）OFF
     */
    private function messageFirstShowTypeOff()
    {
        // 設定OFF
        $configs = Configs::updateOrCreate(
            ['name' => 'message_first_show_type'],
            [
                'category' => 'message',
                'value'    => 0     // 0:表示しない
            ]
        );
    }
}
