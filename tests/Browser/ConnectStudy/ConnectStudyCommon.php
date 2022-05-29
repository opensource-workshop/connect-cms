<?php

namespace Tests\Browser\ConnectStudy;

use Illuminate\Foundation\Testing\DatabaseMigrations;
use Laravel\Dusk\Browser;
use Tests\DuskTestCase;

use App\Models\Common\Frame;
use App\Models\Common\Page;
use App\Models\Core\Dusks;

use App\Enums\PluginName;

/**
 * Connect-Study 各プラグインのテストの準備クラス
 *
 */
class ConnectStudyCommon extends DuskTestCase
{
    /**
     * テストする関数の制御
     *
     * @group user
     * @see https://readouble.com/laravel/6.x/ja/dusk.html#running-tests
     */
    public function testInvoke()
    {
        // マニュアル出力のために、dusk データベースなど利用するので、アサーションは無条件にOKとしたい。
        $this->assertTrue(true);

        // Connect-Study 用のオプションページの削除と再生成
        Page::where('permanent_link', 'LIKE', '/study%')->delete();

        $this->login(1);

        $this->browse(function (Browser $browser) {
            $browser->visit('/manage/page/import')
                    ->attach('page_csv', __DIR__.'/study_page.csv')
                    ->press('インポート')
                    ->acceptDialog()
                    ->pause(500);  // 少し待たないと、次のページ移動でデータができていない。
        });

        $children_names = ['ドローン','AI顔認識','音声合成'];
        $this->movePageChildren('Connect-Study', $children_names);

        $this->logout();
    }
}
