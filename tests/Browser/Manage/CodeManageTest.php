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
        $this->regist();
        $this->display();
        $this->searchRegist();
        $this->searches();
        $this->helpMessageRegist();
        $this->helpMessages();
        $this->import();
        $this->download();
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
             "comment": "<ul class=\"mb-0\"><li>プラグインで使うコードを一覧表示できます。</li></ul>"
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
        });

        // コード登録
        $this->putManualData("manage/code/regist/images/regist");
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

            $browser->press('登録')
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
        $this->putManualData("manage/code/helpMessageRegist/images/helpMessageRegist,manage/code/helpMessageRegist/images/helpMessageRegist2,manage/code/helpMessageRegist/images/helpMessageRegist3");
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
        $this->putManualData("manage/code/download/images/download");
    }
}
