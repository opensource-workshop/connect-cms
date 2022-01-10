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
//print_r($_SERVER['argv']);
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
                    ->screenshot('Manage/IndexManageTest/IndexManage');
            //parent::screenshot($browser); // この記述の場合、ディレクトリは自動で判別＆日時付きでファイルが保存される。
        });

        $manual = IndexManage::declareManual();

        // 結果の保存
        Dusks::create([
            'category' => 'Manage',
            'sort' => '2',
            'method' => 'index',
            'test_result' => 'OK',
            'html_path' => 'Manage/IndexManageTest/IndexManage',
            'function_title' => $manual['function_title'],
            'method_desc' => $manual['method_desc']['index'],
            'function_desc' => $manual['function_desc'],
        ]);




    }
}
