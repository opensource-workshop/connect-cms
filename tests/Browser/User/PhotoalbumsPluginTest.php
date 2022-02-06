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
        $this->createBuckets();

        $this->index();  // マニュアルデータ用
    }

    /**
     * インデックス
     */
    private function index()
    {
        // プラグインがなければ追加(テストするFrameとページのインスタンス変数への保持も)
        $this->addPluginFirst('photoalbums', '/test/photoalbum', 2);

        // 実行
        $this->browse(function (Browser $browser) {
            $browser->visit('/test/photoalbum')
                    ->assertPathBeginsWith('/')
                    ->screenshot('user/photoalbums/index/images/index');
        });

        // マニュアルデータ
        $this->putManualData('user/photoalbums/index/images/index');
    }

    /**
     * バケツ作成
     */
    private function createBuckets()
    {
        // 実行
        $this->browse(function (Browser $browser) {
            $browser->visit('/plugin/photoalbums/createBuckets/' . $this->getTestPageId() . '/' . $this->getTestFrameId())
                    ->assertPathBeginsWith('/')
                    ->screenshot('user/photoalbums/createBuckets/images/createBuckets');
        });

        // マニュアルデータ
        $this->putManualData('user/photoalbums/createBuckets/images/createBuckets');
    }
}
