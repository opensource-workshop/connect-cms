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
     * @see https://readouble.com/laravel/6.x/ja/dusk.html#running-tests `php artisan dusk --group=manage`
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
        $this->saveFavicon();
    }

    /**
     * index の表示
     */
    private function index()
    {
        $this->browse(function (Browser $browser) {
            $browser->visit('/manage/site')
                    ->assertTitleContains('Connect-CMS');
            $this->screenshot($browser);
        });
    }

    /**
     * サイト基本設定画面
     */
    private function edit()
    {
        // 自動ユーザ登録の使用を「許可する」にする。
        $this->browse(function (Browser $browser) {
            $browser->visit('/manage/site')
                    ->click('#label_user_register_enable_on')
                    ->assertTitleContains('Connect-CMS');
            $this->screenshot($browser);
        });
    }

    /**
     * サイト基本設定変更処理
     */
    private function update()
    {
        $this->browse(function (Browser $browser) {
            $browser->press('更新')
                    ->assertTitleContains('Connect-CMS');
            $this->screenshot($browser);
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
                    ->assertTitleContains('Laravel');
            $this->screenshot($browser);
        });
    }

    /**
     * meta情報処理
     */
    private function saveMeta()
    {
        $this->browse(function (Browser $browser) {
            $browser->press('更新')
                    ->assertTitleContains('Laravel');
            $this->screenshot($browser);
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
                    ->assertTitleContains('Laravel');
            $this->screenshot($browser);
        });
    }

    /**
     * レイアウト設定更新処理
     */
    private function saveLayout()
    {
        $this->browse(function (Browser $browser) {
            $browser->press('変更')
                    ->assertTitleContains('Laravel');
            $this->screenshot($browser);
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
                    ->assertTitleContains('Laravel');
            $this->screenshot($browser);
        });
    }

    /**
     * カテゴリ設定更新処理
     */
    private function saveCategories()
    {
        $this->browse(function (Browser $browser) {
            $browser->press('変更')
                    ->assertTitleContains('Laravel');
            $this->screenshot($browser);
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
                    ->assertTitleContains('Laravel');
            $this->screenshot($browser);
        });
    }

    /**
     * 他言語設定更新処理
     */
    private function saveLanguages()
    {
        $this->browse(function (Browser $browser) {
            $browser->press('変更')
                    ->assertTitleContains('Laravel');
            $this->screenshot($browser);
        });
    }

    /**
     * 他言語設定
     */
    private function pageError()
    {
        $this->browse(function (Browser $browser) {
            $browser->visit('/manage/site/pageError')
                    ->type('page_permanent_link_403', "/403")
                    ->type('page_permanent_link_404', "/404")
                    ->assertTitleContains('Laravel');
            $this->screenshot($browser);
        });
    }

    /**
     * 他言語設定更新処理
     */
    private function savePageError()
    {
        $this->browse(function (Browser $browser) {
            $browser->press('更新')
                    ->assertTitleContains('Laravel');
            $this->screenshot($browser);
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
                    ->assertTitleContains('Laravel');
            $this->screenshot($browser);
        });
    }

    /**
     * アクセス解析更新処理
     */
    private function saveAnalytics()
    {
        $this->browse(function (Browser $browser) {
            $browser->press('更新')
                    ->assertTitleContains('Laravel');
            $this->screenshot($browser);
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
                    ->assertTitleContains('Laravel');
            $this->screenshot($browser);
        });
    }

    /**
     * ファビコン更新処理
     */
    private function saveFavicon()
    {
        $this->browse(function (Browser $browser) {
            $browser->press('ファビコン追加')
                    ->assertTitleContains('Laravel');
            $this->screenshot($browser);
        });
    }
}
