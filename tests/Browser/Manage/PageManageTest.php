<?php

namespace Tests\Browser\Manage;

use Illuminate\Foundation\Testing\DatabaseMigrations;
use Laravel\Dusk\Browser;
use Tests\DuskTestCase;

use App\Models\Common\Page;

/**
 * > tests\bin\connect-cms-test.bat
 *
 * @see https://github.com/opensource-workshop/connect-cms/wiki/Dusk#テスト実行 [How to test]
 */
class PageManageTest extends DuskTestCase
{
    /**
     * テスト前共通処理
     *
     * @return void
     */
    protected function setUp(): void
    {
        parent::setUp();

        // bugfix: APP_DEBUG=trueだと,phpdebugbar-header とボタンが被って、ボタンが押せずにテストエラーになるため、phpdebugbarを閉じる
        $this->closePhpdebugar();
    }

    /**
     * テストする関数の制御
     *
     * @group manage
     * @see https://readouble.com/laravel/6.x/ja/dusk.html#running-tests
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
                    ->assertTitleContains('Connect-CMS');
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
                    ->assertTitleContains('Connect-CMS');
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
                    ->assertTitleContains('Connect-CMS');
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

    /**
     * テストする関数の制御
     *
     * @group manage
     */
    public function testInvoke2()
    {
        $this->login(1);

        // グループ登録
        $this->groupEdit('管理者グループ');
        $this->groupUpdate();

        // ページ管理
        $this->upload();
        $this->movePage();
        $this->pageRole();
        $this->pageRoleUpdate();
    }

    /**
     * グループ登録画面
     */
    private function groupEdit($name)
    {
        $this->browse(function (Browser $browser) use ($name) {
            $browser->visit('/manage/group/edit')
                    ->type('name', $name)
                    ->assertTitleContains('Connect-CMS');
            $this->screenshot($browser);
        });
    }

    /**
     * グループ登録処理
     */
    private function groupUpdate()
    {
        $this->browse(function (Browser $browser) {
            $browser->press('グループ変更')
                    ->assertTitleContains('Connect-CMS');
            $this->screenshot($browser);
        });
    }

    /**
     * ページ権限表示
     */
    private function pageRole()
    {
        $this->browse(function (Browser $browser) {
            $upload  = Page::where('page_name', 'アップロード')->first();

            $browser->visit('/manage/page/role/' . $upload->id)
                ->clickLink('管理者グループ')
                ->assertSourceHas('ページ権限設定');

            // collapseが表示されるまで、ちょっと待つ
            $browser->pause(500);

            $this->screenshot($browser);

            $browser->click("label[for='role_reporter1']");
            $this->screenshot($browser);
        });
    }

    /**
     * ページ権限更新
     */
    private function pageRoleUpdate()
    {
        $this->browse(function (Browser $browser) {
            $browser->click("label[for='role_reporter1']")
                ->assertTitleContains('Connect-CMS');

            // チェックボックスのクリックが反映されるまで、ちょっと待つ
            $browser->pause(500);

            $this->screenshot($browser);

            // [TODO] チェックボックスONにしてるはずなんだけど、なんでかチェック外れて更新できない。残念ギブアップ。
            $browser->press('権限更新')
                    ->assertTitleContains('Connect-CMS');
            $this->screenshot($browser);
        });
    }
}
