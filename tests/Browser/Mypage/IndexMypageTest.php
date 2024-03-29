<?php

namespace Tests\Browser\Mypage;

use Illuminate\Foundation\Testing\DatabaseMigrations;
use Laravel\Dusk\Browser;
use Tests\DuskTestCase;

use App\Models\Core\Dusks;

use App\Plugins\Mypage\IndexMypage\IndexMypage;

class IndexMypageTest extends DuskTestCase
{
    /**
     * テストする関数の制御
     *
     * @group manage
     * @see https://readouble.com/laravel/6.x/ja/dusk.html#running-tests
     */
    public function test()
    {
        $this->login(1); // user id = 1(admin)でログイン
        $this->index();
    }

    /**
     * 管理画面のindex の表示
     */
    private function index()
    {
        // 実行
        $this->browse(function (Browser $browser) {
            $browser->visit('/mypage')
                    ->assertPathBeginsWith('/')
                    ->screenshot('mypage/index/index/images/index');
        });

        // マニュアル用データ出力
        $this->putManualData('mypage/index/index/images/index', null, 5);
    }
}
