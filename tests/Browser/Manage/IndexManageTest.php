<?php

namespace Tests\Browser\Manage;

use Illuminate\Foundation\Testing\DatabaseMigrations;
use Laravel\Dusk\Browser;
use Tests\DuskTestCase;

use app\Plugins\Manage\IndexManage\IndexManage;

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
//print_r($_SERVER['argv']);
    }

    /**
     * 管理画面のindex の表示
     */
    private function index()
    {
        $this->browse(function (Browser $browser) {
            $browser->visit('/manage')
                    ->assertTitle('Connect-CMS')
                    ->screenshot('Manage/IndexManageTest/IndexManage');
            //parent::screenshot($browser); // この記述の場合、ディレクトリは自動で判別＆日時付きでファイルが保存される。
        });

require_once __DIR__.'/../../../app/Plugins/Manage/IndexManage/IndexManage.php';
$index_manage = new IndexManage();
print_r($index_manage->declareManual());



    }
}
