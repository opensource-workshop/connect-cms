<?php

namespace Tests\Browser\User;

use Illuminate\Foundation\Testing\DatabaseMigrations;
use Laravel\Dusk\Browser;
use Tests\DuskTestCase;

use App\Enums\PluginName;

class PhotoalbumsPluginTest extends DuskTestCase
{
    /**
     * テストする関数の制御
     *
     * @group manage
     * @see https://readouble.com/laravel/6.x/ja/dusk.html#running-tests
     */
    public function testInvoke()
    {
        $this->login(1); // user id = 1(admin)でログイン
        $this->index();
    }

    /**
     * インデックス
     */
    private function index()
    {
        // プラグイン追加
        $this->addPluginModal(PluginName::getPluginName(PluginName::photoalbums), '/test/photoalbum', 2, false);

        // 実行
        $this->browse(function (Browser $browser) {
            $browser->visit('/test/photoalbum')
                    ->assertPathBeginsWith('/')
                    ->screenshot('user/photoalbums/index/images/index');
        });

        // マニュアルデータ
        $this->putManualData('user/photoalbums/index/images/index');
    }
}
