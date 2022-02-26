<?php

namespace Tests\Browser\Blueprint;

use Illuminate\Foundation\Testing\DatabaseMigrations;
use Laravel\Dusk\Browser;
use Tests\DuskTestCase;

use App\Models\Common\Frame;
use App\Models\Core\Dusks;

use App\Enums\PluginName;

/**
 * 権限・テストクラス
 *
 */
class RoleBlueprintTest extends DuskTestCase
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
        Dusks::where('category', 'blueprint')->where('plugin_name', 'role')->delete();

        // マニュアル出力のために、dusk データベースなど利用するので、アサーションは無条件にOKとしたい。
        $this->browse(function (Browser $browser) {
            $browser->visit('/')
                    ->assertPathBeginsWith('/');
        });

        // マニュアル用データ出力（権限）
        $dusk_index = Dusks::create([
            'category' => 'blueprint',
            'sort' => 2,
            'plugin_name' => 'role',
            'plugin_title' => '権限',
            'plugin_desc' => 'Connect-CMSの権限について説明します。',
            'method_name' => 'index',
            'method_title' => '権限',
            'method_desc' => '権限とは、管理機能やプラグインの配置ができる権限や記事の投稿ができる権限などのように、各操作を許可するものを指します。',
            'method_detail' => '',
            'html_path' => 'blueprint/role/index/index.html',
            'test_result' => 'OK',
        ]);
    }
}
