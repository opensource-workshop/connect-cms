<?php

namespace Tests\Browser\Test;

use Illuminate\Foundation\Testing\DatabaseMigrations;
use Laravel\Dusk\Browser;
use Tests\DuskTestCase;

use App\Models\Common\Buckets;
use App\Models\Common\Frame;
use App\Models\Common\Page;
use App\Models\Core\Configs;
use App\Models\Core\Dusks;

use App\Enums\PluginName;

/**
 * 画面キャプチャ・テスト
 *
 * php artisan dusk tests\Browser\Test\CapturepageTest.php
 */
class CapturepageTest extends DuskTestCase
{
    private $frame = null;

    /**
     * テストする関数の制御
     *
     * @group user
     * @see https://readouble.com/laravel/6.x/ja/dusk.html#running-tests
     */
    public function test()
    {
        $this->init();
    }

    /**
     * 連続スクリーンショット
     * @param Browser $browser
     * @return Browser
     */
    public function scrollScreenshot($browser, $title)
    {
        // ウィンドウ高さ
        $height = $browser->script('return window.innerWidth')[0];
        // ウィンドウ幅
        $width = $browser->script('return window.innerHeight')[0];
        // ウィンドウスクロール量取得
        $allHeight = $browser->script('return document.documentElement.scrollHeight')[0];

        // タイトルにウィンドウサイズを追加
        $title .= '_'.$width.'x'.$height;

        // スクリーンショットの連続保存
        $index = 0;
        for ($i = 0; ($i + $height) <= ($allHeight + $height); $i += $height) {
            // 0回以外は、連続スクリーンショットのため、少し待つ
            if ($i > 0) {
                // 0.8秒スリープ
                usleep(800000);
            }

            // 画面スクロール
            $browser->script("window.scrollTo(0, {$i});");
            // スクリーンショット撮影
            $browser->screenshot($title . '_' . str_pad(++ $index, 3, 0, STR_PAD_LEFT));
            // 0.8秒スリープ
            // usleep(800000);
        }
        return $browser;
    }

    /**
     * 初期処理
     */
    private function init()
    {
        // ブラウザ操作
        $this->browse(function (Browser $browser) {

            $browser->resize(400, 800);

            // Pageテーブルをループしてキャプチャ取得
            $pages = Page::where('membership_flag', 0)->defaultOrder()->get();

            foreach ($pages as $page) {
                $permanent_link_text = $page->permanent_link == '/' ? 'home' : str_replace('/', '_', trim($page->permanent_link, '/'));
                $browser->visit($page->permanent_link)
                        ->assertPathBeginsWith('/')
                        ->screenshot('test/' . $page->id . "-" . $permanent_link_text);

                //$browser->visit($page->permanent_link)
                //        ->assertPathBeginsWith('/');
                //$this->scrollScreenshot($browser, 'test/' . $page->id . "-" . str_replace('/', '_', $page->permanent_link));

            }
        });
    }
}
