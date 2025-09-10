<?php

namespace Tests\Browser\Manage;

use Illuminate\Foundation\Testing\DatabaseMigrations;
use Laravel\Dusk\Browser;
use Tests\DuskTestCase;

/**
 * > tests\bin\connect-cms-test.bat
 */
class SiteManageTest extends DuskTestCase
{
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
        $this->update();
        $this->meta();
        $this->saveMeta();
        $this->layout();
        $this->saveLayout();
        $this->categories();
        $this->saveCategories();
        $this->languages('日本語', '/');
        $this->saveLanguages();
        $this->languages('英語', '/en');
        $this->saveLanguages();
        $this->pageError();
        $this->savePageError();
        $this->analytics();
        $this->saveAnalytics();
        $this->favicon();
        $this->wysiwyg();
        $this->document();
        //$this->saveFavicon();
    }

    /**
     * index の表示
     */
    private function index()
    {
        // サイト管理画面
        $this->browse(function (Browser $browser) {
            $browser->visit('/manage/site')
                    ->assertTitleContains('Connect-CMS')
                    ->screenshot('manage/site/index/images/index');
        });

        // ページスクロール
        $this->browse(function (Browser $browser) {
            $browser->scrollIntoView('#base_header_color')
                    ->screenshot('manage/site/index/images/index2');
        });
        $this->browse(function (Browser $browser) {
            $browser->scrollIntoView('#footer_area_optional_class')
                    ->screenshot('manage/site/index/images/index3');
        });
        $this->browse(function (Browser $browser) {
            $browser->scrollIntoView('footer')
                    ->screenshot('manage/site/index/images/index4');
        });

        // マニュアル用データ出力
        $this->putManualData('[
            {"path": "manage/site/index/images/index",
             "name": "画面1",
             "comment": "<ul class=\"mb-0\"><li>サイト名や適用するテーマなど、サイトに関する基本的なことを設定します。</li><li>追加テーマを指定した際は基本テーマの後で追加テーマのCSSが反映されます。</li></ul>"
            },
            {"path": "manage/site/index/images/index2",
             "name": "画面1",
             "comment": "<ul class=\"mb-0\"><li>ヘッダーバーの文字色や各エリアに付加するクラス名を指定できます。</li><li>クラス名はテーマでオリジナルのCSSを作成する場合に参照するセレクタとなります。</li></ul>"
            },
            {"path": "manage/site/index/images/index3",
             "name": "画面1",
             "comment": "<ul class=\"mb-0\"><li>ヘッダーバーの表示、固定、ログインリンクの表示などが設定できます。</li></ul>"
            },
            {"path": "manage/site/index/images/index4",
             "name": "画面1",
             "comment": "<ul class=\"mb-0\"><li>パスワードリセットの使用可否、ログイン後に移動するページ、マイページの使用可否が設定できます。</li><li>画像の保存機能を無効化する設定もできます。</li><li>スマートフォン表示の際のハンバーガーメニューの表示は、全ページを表示するデフォルトか今いるフォルダの仮想のみ展開するオープンカレントツリーを選ぶことができます。</li></ul>"
            }
        ]', null, 3, 'basic');
    }

    /**
     * サイト基本設定画面
     */
    private function edit()
    {
        // パスワードリセットの使用を「許可しない」にする。
/*
        $this->browse(function (Browser $browser) {
            $browser->visit('/manage/site')
                    ->click('label[for="base_login_password_reset_off"]')
                    ->assertTitleContains('Connect-CMS')
                    ->screenshot('manage/site/edit/images/edit');
        });
*/
    }

    /**
     * サイト基本設定変更処理
     */
    private function update()
    {
        $this->browse(function (Browser $browser) {
            $browser->press('更新')
                    ->assertTitleContains('Connect-CMS')
                    ->screenshot('manage/site/update/images/update');
        });
    }

    /**
     * meta情報
     */
    private function meta()
    {
        $this->browse(function (Browser $browser) {
            $browser->visit('/manage/site/meta')
                    ->type('description', 'Connect-CMSのテストサイトです。')
                    ->type('og_site_name', 'Connect-CMS')
                    ->type('og_title', 'Connect-CMS テストサイト')
                    ->type('og_description', 'Webサイトを簡単に作成できるコンテンツ管理システム')
                    ->type('og_type', 'website')
                    ->assertTitleContains('Connect-CMS')
                    ->screenshot('manage/site/meta/images/meta');
        });

        // OGP設定セクションのスクロール表示
        $this->browse(function (Browser $browser) {
            $browser->scrollIntoView('#ogp_settings')
                    ->screenshot('manage/site/meta/images/meta_ogp');
        });

        // マニュアル用データ出力
        $this->putManualData('[
            {"path": "manage/site/meta/images/meta",
             "name": "meta情報",
             "comment": "<ul class=\"mb-0\"><li>HEADのdescriptionに出力される値を設定できます。</li><li>OGP設定により、SNSでシェアされた際の表示内容を制御できます。</li></ul>"
            },
            {"path": "manage/site/meta/images/meta_ogp",
             "name": "OGP設定",
             "comment": "<ul class=\"mb-0\"><li>FacebookやTwitterなどのSNSでWebページがシェアされた際に表示される情報を設定できます。</li><li>OG画像は推奨サイズ1200x630pxで、jpg、jpeg、png形式に対応しています。</li></ul>"
            }
        ]', null, 3, 'basic');
    }

    /**
     * meta情報処理
     */
    private function saveMeta()
    {
        $this->browse(function (Browser $browser) {
            $browser->press('更新')
                    ->waitForText('メタ情報を更新しました。')
                    ->assertTitleContains('Connect-CMS')
                    ->assertSee('メタ情報を更新しました。')
                    ->screenshot('manage/site/meta/images/saveMeta');
        });

        // OGPタグの出力確認
        $this->browse(function (Browser $browser) {
            $browser->visit('/')
                    ->assertSourceHas('<meta property="og:site_name" content="Connect-CMS">')
                    ->assertSourceHas('<meta property="og:title" content="Connect-CMS テストサイト">')
                    ->assertSourceHas('<meta property="og:description" content="Webサイトを簡単に作成できるコンテンツ管理システム">')
                    ->assertSourceHas('<meta property="og:type" content="website">')
                    ->assertSourceHas('<meta property="og:url"');
        });
    }

    /**
     * レイアウト設定
     */
    private function layout()
    {
        $this->browse(function (Browser $browser) {
            $browser->visit('/manage/site/layout')
                    ->click('#label_browser_width_footer')
                    ->assertTitleContains('Connect-CMS')
                    ->pause(500)
                    ->screenshot('manage/site/layout/images/layout');
        });

        // マニュアル用データ出力
        $this->putManualData('[
            {"path": "manage/site/layout/images/layout",
             "name": "レイアウト設定",
             "comment": "<ul class=\"mb-0\"><li>ブラウザ幅100％で表示するエリアをヘッダー、センター、フッターからそれぞれ選ぶことができます。</li></ul>"
            }
        ]', null, 3, 'basic');
    }

    /**
     * レイアウト設定更新処理
     */
    private function saveLayout()
    {
        $this->browse(function (Browser $browser) {
            $browser->press('変更')
                    ->pause(300)
                    ->assertTitleContains('Connect-CMS')
                    ->screenshot('manage/site/layout/images/saveLayout');
        });
    }

    /**
     * カテゴリ設定
     */
    private function categories()
    {
        $this->browse(function (Browser $browser) {
            $browser->visit('/manage/site/categories')
                    ->type('add_display_sequence', '1')
                    ->type('add_classname', 'news')
                    ->type('add_category', 'ニュース')
                    ->type('add_color', '#ffffff')
                    ->type('add_background_color', '#0000c0')
                    ->assertTitleContains('Connect-CMS')
                    ->screenshot('manage/site/categories/images/categories');
        });

        // マニュアル用データ出力
        $this->putManualData('[
            {"path": "manage/site/categories/images/categories",
             "name": "カテゴリ設定",
             "comment": "<ul class=\"mb-0\"><li>ここでは、対象がALLの共通カテゴリ―を設定できます。</li></ul>"
            }
        ]', null, 3, 'basic');
    }

    /**
     * カテゴリ設定更新処理
     */
    private function saveCategories()
    {
        $this->browse(function (Browser $browser) {
            $browser->press('変更')
                    ->pause(300)
                    ->assertTitleContains('Connect-CMS')
                    ->screenshot('manage/site/categories/images/saveCategories');
        });
    }

    /**
     * 他言語設定
     */
    private function languages($add_language, $add_url)
    {
        $this->browse(function (Browser $browser) use ($add_language, $add_url) {
            $browser->visit('/manage/site/languages')
                    ->click('#label_language_multi_on_on')
                    ->type('add_language', $add_language)
                    ->type('add_url', $add_url)
                    ->assertTitleContains('Connect-CMS')
                    ->screenshot('manage/site/languages/images/languages');
        });

        // マニュアル用データ出力
        $this->putManualData('[
            {"path": "manage/site/languages/images/languages",
             "name": "他言語設定",
             "comment": "<ul class=\"mb-0\"><li>URLは各言語のページの最初に付く値を設定します。</li></ul>"
            }
        ]', null, 3, 'basic');
    }

    /**
     * 他言語設定更新処理
     */
    private function saveLanguages()
    {
        $this->browse(function (Browser $browser) {
            $browser->press('変更')
                    ->pause(300)
                    ->assertTitleContains('Connect-CMS')
                    ->screenshot('manage/site/languages/images/saveLanguages');
        });
    }

    /**
     * エラーページ設定
     */
    private function pageError()
    {
        $this->browse(function (Browser $browser) {
            $browser->visit('/manage/site/pageError')
                    ->type('page_permanent_link_403', "/403")
                    ->type('page_permanent_link_404', "/404")
                    ->assertTitleContains('Connect-CMS')
                    ->screenshot('manage/site/pageError/images/pageError');
        });

        // マニュアル用データ出力
        $this->putManualData('[
            {"path": "manage/site/pageError/images/pageError",
             "name": "エラーページ設定",
             "comment": "<ul class=\"mb-0\"><li>ここで指定した表示ページはこの指定内容の固定リンクをページ管理で作成しておく必要があります。</li></ul>"
            }
        ]', null, 3, 'basic');
    }

    /**
     * エラー画面設定更新処理
     */
    private function savePageError()
    {
        $this->browse(function (Browser $browser) {
            $browser->press('更新')
                    ->pause(300)
                    ->assertTitleContains('Connect-CMS')
                    ->screenshot('manage/site/pageError/images/savePageError');
        });
    }

    /**
     * アクセス解析設定
     */
    private function analytics()
    {
        $this->browse(function (Browser $browser) {
            $browser->visit('/manage/site/analytics')
                    ->type('tracking_code', "<!-- Global site tag (gtag.js) - Google Analytics -->")
                    ->assertTitleContains('Connect-CMS')
                    ->screenshot('manage/site/analytics/images/analytics');
        });

        // マニュアル用データ出力
        $this->putManualData('[
            {"path": "manage/site/analytics/images/analytics",
             "name": "アクセス解析設定",
             "comment": "<ul class=\"mb-0\"><li>複数のトラッキングコードの指定なども可能です。</li></ul>"
            }
        ]', null, 3, 'basic');
    }

    /**
     * アクセス解析更新処理
     */
    private function saveAnalytics()
    {
        $this->browse(function (Browser $browser) {
            $browser->press('更新')
                    ->pause(500)
                    ->assertTitleContains('Connect-CMS')
                    ->screenshot('manage/site/analytics/images/saveAnalytics');
        });
    }

    /**
     * ファビコン
     */
    private function favicon()
    {
        $this->browse(function (Browser $browser) {
            $browser->visit('/manage/site/favicon')
                    ->attach('favicon', __DIR__.'/favicon.ico')
                    ->assertTitleContains('Connect-CMS')
                    ->screenshot('manage/site/favicon/images/favicon');
        });

        // マニュアル用データ出力
        $this->putManualData('[
            {"path": "manage/site/favicon/images/favicon",
             "name": "ファビコン設定",
             "comment": "<ul class=\"mb-0\"><li>.ico形式のファイルをアップロードしてください。</li></ul>"
            }
        ]', null, 3, 'basic');
    }

    /**
     * ファビコン更新処理
     */
    private function saveFavicon()
    {
        $this->browse(function (Browser $browser) {
            // $browser->click("button[type='submit']")
            $browser->press('ファビコン追加')
                    ->assertTitleContains('Connect-CMS')
                    ->screenshot('manage/site/saveFavicon/images/saveFavicon');
        });
    }

    /**
     * WYSIWYG
     */
    private function wysiwyg()
    {
        $this->browse(function (Browser $browser) {
            $browser->visit('/manage/site/wysiwyg')
                    ->assertTitleContains('Connect-CMS')
                    ->screenshot('manage/site/wysiwyg/images/wysiwyg');
        });

        // マニュアル用データ出力
        $this->putManualData('[
            {"path": "manage/site/wysiwyg/images/wysiwyg",
             "name": "WYSIWYG設定",
             "comment": "<ul class=\"mb-0\"><li>初期に選択させる画像サイズは1200px、800px、400px、200pxから選ぶことができます。</li></ul>"
            }
        ]', null, 3, 'basic');
    }

    /**
     * サイト設計書
     */
    private function document()
    {
        $this->browse(function (Browser $browser) {
            $browser->visit('/manage/site/document')
                    ->assertTitleContains('Connect-CMS')
                    ->screenshot('manage/site/document/images/document');
        });

        // マニュアル用データ出力
        $this->putManualData('[
            {"path": "manage/site/document/images/document",
             "name": "サイト設計書",
             "comment": "<ul class=\"mb-0\"><li>追加の出力内容で、サイト設計書に反映させる項目を増やせます。</li><li>最終ページには問合せ先や連絡先を提示するページがあり、最終ページの内容の各項目に登録してあるものが反映されます。</li></ul>"
            }
        ]', null, 3, 'basic');
    }
}
