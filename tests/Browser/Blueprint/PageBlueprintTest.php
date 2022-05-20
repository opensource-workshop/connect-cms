<?php

namespace Tests\Browser\Blueprint;

use Illuminate\Foundation\Testing\DatabaseMigrations;
use Laravel\Dusk\Browser;
use Tests\DuskTestCase;

use App\Models\Common\Frame;
use App\Models\Core\Dusks;

use App\Enums\PluginName;

/**
 * ページ・クラス
 *
 */
class PageBlueprintTest extends DuskTestCase
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
        Dusks::where('category', 'blueprint')->where('plugin_name', 'page')->delete();

        // マニュアル出力のために、dusk データベースなど利用するので、アサーションは無条件にOKとしたい。
        $this->browse(function (Browser $browser) {
            $browser->visit('/')
                    ->assertPathBeginsWith('/');
        });

        // マニュアル用データ出力（ページとフレーム）
        $dusk_index = Dusks::create([
            'category' => 'blueprint',
            'sort' => 1,
            'plugin_name' => 'page',
            'plugin_title' => 'ページ',
            'plugin_desc' => 'Connect-CMSのページについて説明します。',
            'method_name' => 'index',
            'method_title' => 'ページとフレーム',
            'method_desc' => 'Connect-CMS のページについて説明します。',
            'method_detail' => '以下にページとフレームの例を示します。',
            'html_path' => 'blueprint/page/index/index.html',
            'level' => 'basic',
            'test_result' => 'OK',
        ]);

        // マニュアル用データ出力（フレームとバケツ）
        $dusk = Dusks::create([
            'category' => 'blueprint',
            'sort' => 1,
            'plugin_name' => 'page',
            'plugin_title' => 'ページ',
            'plugin_desc' => 'Connect-CMSのページについて説明します。',
            'method_name' => 'backet',
            'method_title' => 'フレームとバケツ',
            'method_desc' => 'Connect-CMS のフレームとバケツの関係について説明します。',
            'method_detail' => '以下にフレームとバケツの例を示します。',
            'html_path' => 'blueprint/page/backet/index.html',
            'level' => 'basic',
            'test_result' => 'OK',
            'parent_id' => $dusk_index->id,
        ]);

        // マニュアル用データ出力（フレームとプラグイン）
        $dusk = Dusks::create([
            'category' => 'blueprint',
            'sort' => 1,
            'plugin_name' => 'page',
            'plugin_title' => 'ページ',
            'plugin_desc' => 'Connect-CMSのページについて説明します。',
            'method_name' => 'plugin',
            'method_title' => 'フレームとプラグイン',
            'method_desc' => 'Connect-CMS のフレームとプラグインの関係について説明します。',
            'method_detail' => '以下にフレームとプラグインの例を示します。',
            'html_path' => 'blueprint/page/plugin/index.html',
            'level' => 'basic',
            'test_result' => 'OK',
            'parent_id' => $dusk_index->id,
        ]);
    }
}
