<?php

namespace Tests\Browser\User;

use Illuminate\Foundation\Testing\DatabaseMigrations;
use Laravel\Dusk\Browser;
use Tests\DuskTestCase;

use App\Models\Common\Frame;

use App\Enums\PluginName;

/**
 * ブログテスト
 *
 * @see https://github.com/opensource-workshop/connect-cms/wiki/Dusk#テスト実行 [How to test]
 */
class BlogTest extends DuskTestCase
{
    /**
     * テストする関数の制御
     *
     * @group user
     * @see https://readouble.com/laravel/6.x/ja/dusk.html#running-tests
     */
    public function testInvoke()
    {
        $this->login(1);

        // 固定記事をプラグイン追加
        $this->addPluginModal(PluginName::getPluginName(PluginName::blogs));

        $this->blogSetting();
    }

    /**
     * ブログ設定
     */
    private function blogSetting()
    {
        // $header_first_content_frame = Frame::where('area_id', 0)->orderBy('display_sequence', 'asc')->first();
        // if (empty($header_first_content_frame)) {
        //     $this->assertFalse(false);
        // }

        // $this->browse(function (Browser $browser) use ($header_first_content_frame) {
        $this->browse(function (Browser $browser) {

            // プラグインの（右上）歯車マーク押下
            // <a href="http://localhost/plugin/blogs/editBuckets/1/1#frame-1" title="ブログ設定"><small><i class="fas fa-cog bg-default cc-font-color"></i></small></a>
            $browser->visit('/')
                ->click('[title="ブログ設定"]')
                ->assertSee('新規作成してください。');
            $this->screenshot($browser);

            // プラグインの（右上）歯車マーク押下
            // <a href="http://localhost/plugin/blogs/createBuckets/1/1#frame-1" class="nav-link">新規作成</a>
            // [TODO] 下記エラーでうまくテスト作れなかった。とりあえずここまで。
            //   1) Tests\Browser\User\BlogTest::testInvoke
            //   Facebook\WebDriver\Exception\NoSuchElementException: no such element: Unable to locate element: {"method":"css selector","selector":"body [href*="plugin/blogs/createBuckets"]"}
            //   (Session info: headless chrome=91.0.4472.114)
            //
            // @see http://semooh.jp/jquery/api/selectors/ Selectors - jQuery 日本語リファレンス
            //
            // $browser->visit('/')
            //     // ->press('新規作成')
            //     // ->clickLink('新規作成')
            //     // ->click("a[href*='plugin/blogs/createBuckets']")
            //     // ->click("[href='http://localhost/plugin/blogs/createBuckets/1/1#frame-1']")
            //     // ->click('[href*="plugin/blogs/createBuckets"]')
            //     ->assertSee('登録確定');
            // $this->screenshot($browser);
        });
    }
}
