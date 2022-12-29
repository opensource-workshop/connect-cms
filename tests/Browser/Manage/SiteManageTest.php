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
        $this->putManualData('manage/site/index/images/index,manage/site/index/images/index2,manage/site/index/images/index3,manage/site/index/images/index4', null, 3, 'basic');
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
                    ->assertTitleContains('Connect-CMS')
                    ->screenshot('manage/site/meta/images/meta');
        });

        // マニュアル用データ出力
        $this->putManualData('manage/site/meta/images/meta', null, 3);
    }

    /**
     * meta情報処理
     */
    private function saveMeta()
    {
        $this->browse(function (Browser $browser) {
            $browser->press('更新')
                    ->assertTitleContains('Connect-CMS')
                    ->screenshot('manage/site/meta/images/saveMeta');
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
                    ->screenshot('manage/site/layout/images/layout');
        });

        // マニュアル用データ出力
        $this->putManualData('manage/site/layout/images/layout', null, 3);
    }

    /**
     * レイアウト設定更新処理
     */
    private function saveLayout()
    {
        $this->browse(function (Browser $browser) {
            $browser->press('変更')
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
        $this->putManualData('manage/site/categories/images/categories', null, 3, 'basic');
    }

    /**
     * カテゴリ設定更新処理
     */
    private function saveCategories()
    {
        $this->browse(function (Browser $browser) {
            $browser->press('変更')
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
        $this->putManualData('manage/site/languages/images/languages', null, 3);
    }

    /**
     * 他言語設定更新処理
     */
    private function saveLanguages()
    {
        $this->browse(function (Browser $browser) {
            $browser->press('変更')
                    ->assertTitleContains('Connect-CMS')
                    ->screenshot('manage/site/languages/images/saveLanguages');
        });
    }

    /**
     * エラー画面設定
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
        $this->putManualData('manage/site/pageError/images/pageError', null, 3);
    }

    /**
     * エラー画面設定更新処理
     */
    private function savePageError()
    {
        $this->browse(function (Browser $browser) {
            $browser->press('更新')
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
        $this->putManualData('manage/site/analytics/images/analytics', null, 3);
    }

    /**
     * アクセス解析更新処理
     */
    private function saveAnalytics()
    {
        $this->browse(function (Browser $browser) {
            $browser->press('更新')
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
        $this->putManualData('manage/site/favicon/images/favicon', null, 3);
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
        $this->putManualData('manage/site/wysiwyg/images/wysiwyg', null, 3, 'basic');
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
        $this->putManualData('manage/site/document/images/document', null, 3);
    }
}
