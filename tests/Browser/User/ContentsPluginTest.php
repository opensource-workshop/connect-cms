<?php

namespace Tests\Browser\User;

use Illuminate\Foundation\Testing\DatabaseMigrations;
use Laravel\Dusk\Browser;
use Tests\DuskTestCase;

use App\Enums\PluginName;
use App\Models\Core\Dusks;

class ContentsPluginTest extends DuskTestCase
{
    /**
     * テストする関数の制御
     *
     * @group manage
     * @see https://readouble.com/laravel/6.x/ja/dusk.html#running-tests
     */
    public function testInvoke()
    {
        $this->index();
        $this->login(1); // user id = 1(admin)でログイン
    }

    /**
     * インデックス
     */
    private function index()
    {
        // 実行
        $this->browse(function (Browser $browser) {
            $browser->visit('/')
                    ->assertPathBeginsWith('/')
                    ->screenshot('user/contents/index/images/index');
        });

        // マニュアルデータ
//        $this->putManualData('user/contents/index/images/index');

        // マニュアル用データ出力
        $dusk = Dusks::putManualData(
            ['html_path' => 'user/index/index/index.html'],
            ['category' => 'user',
             'sort' => 2,
             'plugin_name' => 'index',
             'plugin_title' => '固定記事',
             'plugin_desc' => 'サイト上に文字や画像を配置できるプラグインです。',
             'method_name' => 'index',
             'method_title' => '固定記事',
             'method_desc' => '',
             'method_detail' => '',
             'html_path' => 'user/index/index/index.html',
            'test_result' => 'OK']
        );
    }
}
