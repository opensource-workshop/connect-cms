<?php

namespace Tests\Browser\Manage;

use Illuminate\Foundation\Testing\DatabaseMigrations;
use Laravel\Dusk\Browser;
use Tests\DuskTestCase;

class CodeManageTest extends DuskTestCase
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
        $this->index();  // 初めにindex データを作っておく必要がある。
        $this->import();
        $this->download();
        $this->regist();
        $this->edit();
        $this->display();
        $this->searchRegist();
        $this->searches();
        $this->helpMessageRegist();
        $this->helpMessages();
        $this->index();  // データが登録された後の状態のスクリーンショットが欲しいので、最後に実行
    }

    /**
     * コード一覧
     */
    private function index()
    {
        // 実行
        $this->browse(function (Browser $browser) {
            $browser->visit('/manage/code')
                    ->assertTitle('Connect-CMS')
                    ->screenshot('manage/code/index/images/index');
        });

        // コード一覧
        $this->putManualData('[
            {"path": "manage/code/index/images/index",
             "name": "コード一覧",
             "comment": "<ul class=\"mb-0\"><li>コード一覧に表示する項目は、「表示設定」から設定できます。</li><li>「ｘ」ボタンを押下すると、検索条件をクリアして再検索します。</li><li>「？」ボタンを押下すると、このオンラインマニュアルで下記の[ 検索条件の詳細 ]が確認できます。</li><li>「虫眼鏡 学校」ボタンは「検索条件登録」から登録した検索条件です。ボタンを押すと、指定した検索条件でコード一覧を表示します。</li></ul>"
            }
        ]');
    }

    /**
     * コード登録
     */
    private function regist()
    {
        // 実行
        $this->browse(function (Browser $browser) {
            $browser->visit('/manage/code/regist')
                    ->assertTitle('Connect-CMS')
                    ->screenshot('manage/code/regist/images/regist');

            $browser->scrollIntoView('footer')
                    ->screenshot('manage/code/regist/images/regist2');
        });

        // コード登録
        $this->putManualData('[
            {"path": "manage/code/regist/images/regist",
             "name": "コード登録"
            },
            {"path": "manage/code/regist/images/regist2",
             "name": "コード登録２",
             "comment": "<ul class=\"mb-0\"><li>注釈名を選択すると、表示している各項目の注釈が切り替わります。注釈は「注釈登録」から登録できます。</li><li>各項目の注釈は設定なしの場合、なにも表示しません。</li></ul>"
            }
        ]');
    }

    /**
     * コード変更
     */
    private function edit()
    {
        // 実行
        $this->browse(function (Browser $browser) {
            $browser->visit('/manage/code/edit/1')
                    ->assertTitle('Connect-CMS')
                    ->screenshot('manage/code/edit/images/edit');

            $browser->scrollIntoView('footer')
                    ->screenshot('manage/code/edit/images/edit2');
        });

        // コード登録
        $this->putManualData('[
            {"path": "manage/code/edit/images/edit",
             "name": "コード変更"
            },
            {"path": "manage/code/edit/images/edit2",
             "name": "コード変更２",
             "comment": "<ul class=\"mb-0\"><li>登録内容をコピーして、再登録できます。</li><li>注釈名を選択すると、表示している各項目の注釈が切り替わります。注釈は「注釈登録」から登録できます。</li><li>各項目の注釈は設定なしの場合、なにも表示しません。</li></ul>"
            }
        ]');
    }

    /**
     * 表示設定
     */
    private function display()
    {
        // 実行
        $this->browse(function (Browser $browser) {
            $browser->visit('/manage/code/display')
                    ->check('code_list_display_colums[id]')
                    ->check('code_list_display_colums[type_name]')
                    ->check('code_list_display_colums[type_code1]')
                    ->check('code_list_display_colums[code]')
                    ->check('code_list_display_colums[value]')
                    ->assertTitleContains('Connect-CMS')
                    ->screenshot('manage/code/display/images/display');

            $browser->scrollIntoView('footer')
                    ->screenshot('manage/code/display/images/display2');

            $browser->press('#display_update_button')
                    ->assertTitleContains('Connect-CMS')
                    ->screenshot('manage/code/display/images/display3');
        });

        // 表示設定
        $this->putManualData("manage/code/display/images/display,manage/code/display/images/display2");
    }

    /**
     * 検索条件登録
     */
    private function searchRegist()
    {
        // 実行
        $this->browse(function (Browser $browser) {
            $browser->visit('/manage/code/searchRegist')
                    ->type('name', '学校')
                    ->type('search_words', 'type_code1=school')
                    ->type('display_sequence', '1')
                    ->assertTitleContains('Connect-CMS')
                    ->screenshot('manage/code/searchRegist/images/searchRegist');

            $browser->press('登録')
                    ->assertTitleContains('Connect-CMS')
                    ->screenshot('manage/code/searchRegist/images/searchRegist2');
        });

        // 検索条件登録
        $this->putManualData("manage/code/searchRegist/images/searchRegist");
    }

    /**
     * 検索条件一覧
     */
    private function searches()
    {
        // 実行
        $this->browse(function (Browser $browser) {
            $browser->visit('/manage/code/searches')
                    ->assertTitleContains('Connect-CMS')
                    ->screenshot('manage/code/searches/images/searches');
        });

        // 検索条件一覧
        $this->putManualData("manage/code/searches/images/searches");
    }

    /**
     * 注釈登録
     */
    private function helpMessageRegist()
    {
        // 実行
        $this->browse(function (Browser $browser) {
            $browser->visit('/manage/code/helpMessageRegist')
                    ->type('name', '学校')
                    ->type('alias_key', 'school')
                    ->assertTitleContains('Connect-CMS')
                    ->screenshot('manage/code/helpMessageRegist/images/helpMessageRegist');

            $browser->scrollIntoView('footer')
                    ->screenshot('manage/code/helpMessageRegist/images/helpMessageRegist2');

            $browser->press('登録')
                    ->assertTitleContains('Connect-CMS')
                    ->screenshot('manage/code/helpMessageRegist/images/helpMessageRegist3');
        });

        // 注釈登録
        $this->putManualData("manage/code/helpMessageRegist/images/helpMessageRegist,manage/code/helpMessageRegist/images/helpMessageRegist2");
    }

    /**
     * 注釈一覧
     */
    private function helpMessages()
    {
        // 実行
        $this->browse(function (Browser $browser) {
            $browser->visit('/manage/code/helpMessages')
                    ->assertTitleContains('Connect-CMS')
                    ->screenshot('manage/code/helpMessages/images/helpMessages');
        });

        // 注釈一覧
        $this->putManualData("manage/code/helpMessages/images/helpMessages");
    }

    /**
     * インポート
     */
    private function import()
    {
        $this->browse(function (Browser $browser) {
            $browser->visit('/manage/code/import')
                    ->attach('codes_csv', __DIR__.'/codes.csv')
                    ->assertTitleContains('Connect-CMS')
                    ->screenshot('manage/code/import/images/import');

            $browser->press('インポート')
                    ->acceptDialog()
                    ->assertDontSee('500')        // "500" 文字がない事
                    ->screenshot('manage/code/import/images/import2');
        });

        // インポート
        $this->putManualData('[
            {"path": "manage/code/import/images/import",
             "name": "CSVインポート",
             "comment": "<ul class=\"mb-0\"><li>CSV ファイルのフォーマットはコード管理マニュアルのトップで確認してください。</li></ul>"
            }
        ]');
    }

    /**
     * ダウンロード
     */
    private function download()
    {
        $this->browse(function (Browser $browser) {
            $browser->visit('/manage/code/download')
                    ->assertTitleContains('Connect-CMS')
                    ->screenshot('manage/code/download/images/download');
        });

        // ダウンロード
        $this->putManualData('[
            {"path": "manage/code/download/images/download",
             "name": "CSVダウンロード",
             "comment": "<ul class=\"mb-0\"><li>登録されているコード一覧をCSVでダウンロードできます。</li><li>ダウンロードする文字コードはShift-JISとUTF-8形式から選択できます。</li></ul>"
            }
        ]');
    }
}
