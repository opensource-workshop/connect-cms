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
        $dusk = Dusks::updateOrCreate(['html_path' => 'common/index/index/index.html'],[
            'category' => 'common',
            'sort' => 2,
            'plugin_name' => 'index',
            'plugin_title' => 'ログイン・ログアウト',
            'plugin_desc' => 'Connect-CMSで共通的に使用する機能について説明します。',
            'method_name' => 'index',
            'method_title' => 'ログイン・ログアウト',
            'method_desc' => 'ログイン・ログアウトの方法を紹介します。',
            'method_detail' => '',
            'html_path' => 'common/index/index/index.html',
            'img_paths' => '[
                {"name": "common/index/index/images/login1", "img_methods": [
                    {"img_method": "trim_h", "args": [0,250]},
                    {"img_method": "arc", "args": [1670,75,200,50,10]}
                ]},
                {"name": "common/index/index/images/login2", "img_methods": [
                    {"img_method": "trim_h", "args": [0,400]},
                    {"img_method": "arc", "args": [960,130,200,50,10]}
                ]},
                {"name": "common/index/index/images/login3", "img_methods": [
                    {"img_method": "trim_h", "args": [0,600]},
                    {"img_method": "arc", "args": [960,215,200,40,10]}
                ]},
                {"name": "common/index/index/images/login4", "img_methods": [{"img_method": "trim_h", "args": [0,300]}]}
            ]',
            'test_result' => 'OK',
        ]);
    }
}
