<?php

namespace Tests\Browser\User;

use Illuminate\Foundation\Testing\DatabaseMigrations;
use Laravel\Dusk\Browser;
use Tests\DuskTestCase;

use App\Enums\PluginName;
use App\Models\Common\Buckets;
use App\Models\Common\Frame;
use App\Models\Common\Page;
use App\Models\Core\Dusks;

class MenusPluginTest extends DuskTestCase
{
    /**
     * テストする関数の制御
     *
     * @group manage
     * @see https://readouble.com/laravel/6.x/ja/dusk.html#running-tests
     */
    public function testInvoke()
    {
        $this->index();
        $this->login(1);
        $this->select();
        $this->logout();
        $this->index();
        $this->template(); // テンプレート
    }

    /**
     * インデックス
     */
    private function index()
    {
        // 実行
        $this->browse(function (Browser $browser) {
            $browser->visit('/')
                    ->assertPathBeginsWith('/')
                    ->screenshot('user/menus/index/images/index');
        });

        // マニュアル用データ出力
        $this->putManualData('user/menus/index/images/index');
    }

    /**
     * ページ選択
     */
    private function select()
    {
        // プラグインがなければ追加(テストするFrameとページのインスタンス変数への保持も)
        $this->addPluginFirst('menus', '/', 1);
        $this->test_frame->frame_title = null;
        $this->test_frame->frame_design = 'none';
        $this->test_frame->save();

        $this->browse(function (Browser $browser) {
            $browser->visit('/plugin/menus/select/' . $this->test_frame->page_id . '/' . $this->test_frame->id)
                    ->assertPathBeginsWith('/')
                    ->screenshot('user/menus/select/images/select');

            $browser->scrollIntoView('footer')
                    ->screenshot('user/menus/select/images/select2');
        });

        // マニュアル用データ出力
        $this->putManualData('user/menus/select/images/select,user/menus/select/images/select2');
    }

    /**
     * テンプレート
     */
    private function template()
    {
        $this->login(1);
        $this->addPluginFirst('menus', '/test/menu', 2);
        $this->logout();

        $this->putManualTemplateData($this->test_frame, 'user', '/test/menu', ['menus', 'メニュー'], ['opencurrenttree' => 'ディレクトリ展開式', 'tab' => 'タブ']);
    }
}
