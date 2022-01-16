<?php

namespace Tests\Browser\Manage;

use Illuminate\Foundation\Testing\DatabaseMigrations;
use Laravel\Dusk\Browser;
use Tests\DuskTestCase;

use App\Models\Core\Dusks;

use App\Plugins\Manage\IndexManage\IndexManage;

class IndexManageTest extends DuskTestCase
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
     * 管理画面のindex の表示
     */
    private function index()
    {
        // 実行
        $this->browse(function (Browser $browser) {
            $browser->visit('/manage')
                    ->assertTitle('Connect-CMS')
                    ->screenshot('manage/index/index');
            //parent::screenshot($browser); // この記述の場合、ディレクトリは自動で判別＆日時付きでファイルが保存される。
        });

        // マニュアル用データ出力
        $this->putManualData('manage/index/index');
    }
}
