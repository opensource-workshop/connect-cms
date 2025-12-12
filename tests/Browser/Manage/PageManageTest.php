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
        $this->init();
        $this->login(1);
        $this->index();
        $this->edit();
        $this->store();
        $this->upload();
        $this->movePage();
        $this->roleList();
        $this->index();  // マニュアル用に再度スクリーンショット
    }

    /**
     * 初期処理
     */
    private function init()
    {
        // データクリア
        Page::where('permanent_link', '<>', '/')->delete();

        // 最初にマニュアルの順番確定用にメソッドを指定する。
        $this->reserveManual('index', 'edit', 'movePage', 'roleList', 'upload');
    }

    /**
     * index の表示
     */
    private function index()
    {
        // ブラウザ操作と画面キャプチャ
        $this->browse(function (Browser $browser) {
            $browser->visit('/manage/page')
                    ->assertTitleContains('Connect-CMS')
                    ->screenshot('manage/page/index/images/index1')
                    ->scrollIntoView('footer')
                    ->screenshot('manage/page/index/images/index2');
        });

        // マニュアル用データ出力
        $this->putManualData('[
            {"path": "manage/page/index/images/index1",
             "name": "ページ一覧",
             "comment": "<ul class=\"mb-0\"><li>現在のページの一覧を確認できます。</li><li>ページ内容の編集は<a href=\"../edit/index.html\">「ページ編集」</a>を参照してください。</li><li>上矢印と下矢印で同じ階層内のページ移動ができます。</li><li>階層の移動は<a href=\"../movePage/index.html\">「ページ階層移動」</a>を参照してください。</li><li>ページ名が表示されます。大なり記号は階層を表しています。</li><li>目のアイコンはメニューの初期設定で表示するかどうかを表します。<br />クリックすることで、状態を変更できます。</li><li>固定リンクは設定した内容が表示されます。ページにリンクされています。</li><li>シリンダー鍵マークはパスワード付きページを表します。</li><li>南京錠マークはメンバーシップページとログインユーザ全員参加ページを表します。</li><li>ページ権限設定はメンバーシップページにたいする権限が設定さえているかどうかを表します。</li><li>その他、ページ編集で設定したいくつかの内容の状態がアイコンで表示されます。</li></ul>"
            }
        ]', null, 3, 'basic');
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
                    ->waitFor("input[name='page_name']")
                    ->type('page_name', 'プラグイン・テスト')
                    ->type('permanent_link', '/test')
                    ->click('#label_base_display_flag')
                    ->assertTitleContains('Connect-CMS')
                    ->screenshot('manage/page/edit/images/edit');
        });

        $this->browse(function (Browser $browser) {
            $browser->scrollIntoView('footer');
            $browser->screenshot('manage/page/edit/images/edit2');
        });

        // マニュアル用データ出力
        $this->putManualData('[
            {"path": "manage/page/edit/images/edit",
             "name": "ページ編集1",
             "comment": "<ul class=\"mb-0\"><li>ページ名を指定してください。</li><li>URLを表す固定リンクを指定してください。</li><li>限定公開設定を使うことで、ページを非公開にすることができます。メンバーシップページは指定したユーザのみ、ログインユーザ全員参加はログインしていれば見ることができます。</li><li>パスワードを指定すると、ページの閲覧の際にパスワードが必要になります。</li></ul>"
            },
            {"path": "manage/page/edit/images/edit2",
             "name": "ページ編集2",
             "comment": "<ul class=\"mb-0\"><li>テーマとレイアウトはページ毎に設定することもできます。</li><li>メニュー表示の設定は下記に反映されます。<ul><li>スマホのメニュー表示</li><li>メニュープラグインのページ表示条件で「ページ管理の条件」を選択した場合のメニュー表示</li></ul></li><li>IPアドレス制限は、このページを閲覧する際の閲覧元のIPアドレスを制限します。その他、様々な設定が可能です。</li></ul>"
            }
        ]', null, 3, 'basic');
    }

    /**
     * ページ登録処理
     */
    private function store()
    {
        $this->browse(function (Browser $browser) {
            $browser->press('ページ追加')
                    ->assertTitleContains('Connect-CMS')
                    ->screenshot('manage/page/store/images/store');
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
                    ->assertPathIs('/manage/page/import')
                    ->screenshot('manage/page/upload/images/upload');
        });

        // マニュアル用データ出力
        $this->putManualData('[
            {"path": "manage/page/upload/images/upload",
             "name": "ページ一覧",
             "comment": "<ul class=\"mb-0\"><li>初期配置にチェックすると、インポートした各ページに固定記事プラグインがひとつ配置されます。</li></ul>"
            }
        ]', null, 3, 'basic');
    }

    /**
     * ページの移動
     */
    private function movePage()
    {
        $this->browse(function (Browser $browser) {

            // 固定記事 を テスト の下に移動
            $test_page = Page::where('page_name', 'プラグイン・テスト')->first();
            $sub_page = Page::where('page_name', '固定記事')->first();

            $browser->visit('/manage/page')
                    ->screenshot('manage/page/movePage/images/movePage1')
                    ->click("#move_level_" . $sub_page->id)
                    ->waitFor("#level_move_page_" . $test_page->id)
                    ->screenshot('manage/page/movePage/images/movePage2')
                    ->click("#level_move_page_" . $test_page->id)
                    ->waitForText('決定')
                    ->screenshot('manage/page/movePage/images/movePage3')
                    ->press("決定");
        });

        // 他のページも移動
        $this->movePageNoScreenshot();

        // マニュアル用データ出力
        //$this->putManualData('manage/page/movePage/images/movePage1', null, 3, 'basic');
        // マニュアル用データ出力
        $this->putManualData('[
            {"path": "manage/page/movePage/images/movePage1",
             "name": "ページ一覧",
             "comment": "<ul class=\"mb-0\"><li>現在のページの一覧を確認できます。</li></ul>"
            },
            {"path": "manage/page/movePage/images/movePage3",
             "name": "ページ移動",
             "comment": "<ul class=\"mb-0\"><li>移動したいページを選択して決定をクリックすることで、ページの階層を移動できます。</li></ul>"
            }
        ]', null, 3);
    }

    /**
     * ページの移動
     */
    private function movePageNoScreenshot()
    {
        $children_names = ['ブログ','カレンダー','スライドショー','開館カレンダー','FAQ','リンクリスト','キャビネット','フォトアルバム','データベース','RSS','OPAC','フォーム','アンケート','課題管理','カウンター','サイト内検索','データベース検索','掲示板','施設予約','メニュー','タブ','ログイン'];
        $this->movePageChildren('プラグイン・テスト', $children_names);

        $children_names = ['フレーム'];
        $this->movePageChildren('共通機能テスト', $children_names);
    }

    /**
     * テストする関数の制御
     *
     * @group manage
     */
/*
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
*/

    /**
     * グループ登録画面
     */
    private function groupEdit($name)
    {
        $this->browse(function (Browser $browser) use ($name) {
            $browser->visit('/manage/group/edit')
                    ->type('name', $name)
                    ->assertTitleContains('Connect-CMS')
                    ->screenshot('manage/page/groupEdit/images/groupEdit');
        });
    }

    /**
     * グループ登録処理
     */
    private function groupUpdate()
    {
        $this->browse(function (Browser $browser) {
            $browser->press('グループ変更')
                    ->assertTitleContains('Connect-CMS')
                    ->screenshot('manage/page/groupUpdate/images/groupUpdate');
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

            // collapseの展開待機
            $browser->waitFor("label[for='role_reporter1']");

            //$this->screenshot($browser);
            $browser->screenshot('manage/page/pageRole/images/pageRole');

            $browser->click("label[for='role_reporter1']")
                    ->screenshot('manage/page/pageRole/images/pageRole2');
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

            // 反映待機（最低限、要素の存在を担保）
            $browser->waitFor("label[for='role_reporter1']");

            //$this->screenshot($browser);
            $browser->screenshot('manage/page/pageRoleUpdate/images/pageRoleUpdate');

            // [TODO] チェックボックスONにしてるはずなんだけど、なんでかチェック外れて更新できない。残念ギブアップ。
            $browser->press('権限更新')
                    ->assertTitleContains('Connect-CMS')
                    ->screenshot('manage/page/pageRoleUpdate/images/pageRoleUpdate2');
        });
    }

    /**
     * ページ権限一覧
     */
    private function roleList()
    {
        $this->browse(function (Browser $browser) {
            $browser->visit('/manage/page/roleList')
                    ->screenshot('manage/page/roleList/images/roleList1')
                    ->scrollIntoView('footer')
                    ->screenshot('manage/page/roleList/images/roleList2');
        });

        // マニュアル用データ出力
        $this->putManualData('[
            {"path": "manage/page/roleList/images/roleList1",
             "name": "ページ権限一覧１"
            },
            {"path": "manage/page/roleList/images/roleList2",
             "name": "ページ権限一覧２",
             "comment": "<ul class=\"mb-0\"><li>ページ毎の権限設定の状態が確認できます。</li></ul>"
            }
        ]', null, 3);
    }
}
