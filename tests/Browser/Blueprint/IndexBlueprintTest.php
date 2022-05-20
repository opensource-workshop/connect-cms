<?php

namespace Tests\Browser\Blueprint;

use Illuminate\Foundation\Testing\DatabaseMigrations;
use Laravel\Dusk\Browser;
use Tests\DuskTestCase;

use App\Models\Common\Frame;
use App\Models\Core\Dusks;

use App\Enums\PluginName;

/**
 * 設計の初期クラス
 *
 */
class IndexBlueprintTest extends DuskTestCase
{
    /**
     * テストする関数の制御
     *
     * @group user
     * @see https://readouble.com/laravel/6.x/ja/dusk.html#running-tests
     */
    public function testInvoke()
    {
        // マニュアルデータの削除
        Dusks::where('category', 'blueprint')->where('plugin_name', 'index')->delete();

        // マニュアル出力のために、dusk データベースなど利用するので、アサーションは無条件にOKとしたい。
        $this->browse(function (Browser $browser) {
            $browser->visit('/')
                    ->assertPathBeginsWith('/');
        });

        // マニュアル用データ出力
        $dusk = Dusks::create([
            'category' => 'blueprint',
            'sort' => 1,
            'plugin_name' => 'index',
            'plugin_title' => '構造',
            'plugin_desc' => 'Connect-CMSの構造について説明します。',
            'method_name' => 'index',
            'method_title' => 'バケツ',
            'method_desc' => 'Connect-CMSでは、記事はバケツに入っていると考えます。',
            'method_detail' => '以下にバケツとは何かを説明します。',
            'html_path' => 'blueprint/index/index/index.html',
            'level' => null,
            'test_result' => 'OK',
        ]);
    }
}
