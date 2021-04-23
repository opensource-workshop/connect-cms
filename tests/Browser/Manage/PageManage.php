<?php

namespace Tests\Browser\Manage;

use Illuminate\Foundation\Testing\DatabaseMigrations;
use Laravel\Dusk\Browser;
use Tests\DuskTestCase;

use App\Models\Common\Page;

/**
 * > tests\bin\connect-cms-test.bat
 */
class PageManage extends DuskTestCase
{
    /**
     * テストする関数の制御
     */
    public function testInvoke()
    {
        $this->login(1);
        $this->index();
        $this->edit();
        $this->store();
        $this->upload();
        $this->movePage();
    }

    /**
     * index の表示
     */
    private function index()
    {
        $this->browse(function (Browser $browser) {
            $browser->visit('/manage/page')
                    ->assertTitleContains('Laravel');
            $this->screenshot($browser);
        });
    }

    /**
     * ページ登録画面
     */
    private function edit()
    {
        // ラジオボタンとチェックボックスには、bootstrap の custom-control を使用している。
        // そのため、Dusk のcheck() メソッドやradio() メソッドは効かない。（クリックできないエレメントのため）
        // クリックできるのは、label タグになるため、label タグにセレクタを追加して、ckick() メソッドで値を設定する。
        $this->browse(function (Browser $browser) {
            $browser->visit('/manage/page/edit')
                    ->type('page_name', 'テスト')
                    ->type('permanent_link', '/test')
                    ->click('#label_base_display_flag')
                    ->assertTitleContains('Laravel');
            $this->screenshot($browser);
        });
    }

    /**
     * ページ登録処理
     */
    private function store()
    {
        $this->browse(function (Browser $browser) {
            $browser->press('ページ追加')
                    ->assertTitleContains('Laravel');
            $this->screenshot($browser);
        });
    }

    /**
     * CSV インポート処理
     */
    private function upload()
    {
        $this->browse(function (Browser $browser) {
            $browser->visit('/manage/page/import')
                    ->attach('page_csv', __DIR__.'/page.csv')
                    ->press('インポート')
                    ->acceptDialog()
                    ->assertPathIs('/manage/page/import');
            $this->screenshot($browser);
        });
    }

    /**
     * ページの移動
     */
    private function movePage()
    {
        $this->browse(function (Browser $browser) {

            // アップロード2 を アップロード の下に移動
            $upload  = Page::where('page_name', 'アップロード')->first();
            $upload2 = Page::where('page_name', 'アップロード2')->first();

            $browser->visit('/manage/page')
                    ->select('#form_select_page' . $upload2->id . ' .manage-page-selectpage', $upload->id);
            $this->screenshot($browser);
        });
    }
}
