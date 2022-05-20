<?php

namespace Tests\Browser\Common;

use Illuminate\Foundation\Testing\DatabaseMigrations;
use Laravel\Dusk\Browser;
use Tests\DuskTestCase;

use App\Models\Common\Frame;
use App\Models\Core\Dusks;

use App\Enums\PluginName;

/**
 * 共通エリアテストの初期クラス
 *
 */
class IndexCommonTest extends DuskTestCase
{
    /**
     * テストする関数の制御
     *
     * @group user
     * @see https://readouble.com/laravel/6.x/ja/dusk.html#running-tests
     */
    public function testInvoke()
    {
        // Laravel がコンストラクタでbase_path など使えないので、ここで。
        $this->screenshots_root = base_path('tests/Browser/screenshots/');

        // マニュアル出力のために、dusk データベースなど利用するので、アサーションは無条件にOKとしたい。
        $this->browse(function (Browser $browser) {
            $browser->visit('/')
                    ->assertTitleContains('Connect-CMS');
        });

        // マニュアル用データ出力
        $dusk = Dusks::create([
            'category' => 'common',
            'sort' => 2,
            'plugin_name' => 'index',
            'plugin_title' => '共通機能',
            'plugin_desc' => 'Connect-CMSで共通的に使用する機能について説明します。',
            'method_name' => 'index',
            'method_title' => '',
            'method_desc' => 'プラグイン配置の方法を紹介します。',
            'method_detail' => 'プラグインの配置は、各プラグインで共通です。',
            'html_path' => 'common/plugin/addPlugin/index.html',
            // 'img_args' => 'common/plugin/add_plugin1,common/plugin/add_plugin2,common/plugin/add_plugin3',
            'img_args' => 'common/plugin/edit_content1,common/plugin/edit_content2,common/plugin/edit_content3',
            'level' => 'basic',
            'test_result' => 'OK',
        ]);
    }
}
