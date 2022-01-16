<?php

namespace Tests\Manual\src;

use Laravel\Dusk\Browser;
use Tests\DuskTestCase;

use App\Models\Core\Dusks;

class ManualOutput extends DuskTestCase
{
    /**
     * マニュアル出力用クラス
     *
     * @return void
     */
    public function testInvoke()
    {
        // 無条件にOKとしたい。
        $this->browse(function (Browser $browser) {
            $browser->visit('/')
                    ->assertTitleContains('Connect-CMS');
        });

        // HTML 出力パス（トップページ）
        $html_root = base_path('tests/Manual/html/');
        $screenshots_root = base_path('tests/Browser/screenshots/');

        // トップページ：ページ生成
        $html = view('manual/index', ['base_path' => './']);

        // トップページ：出力
        \File::put($html_root . 'index.html', $html);

        // index メソッドのプラグイン名を取得
        $plugins = Dusks::where('category', 'manage')->get();

        // 管理画面トップ：ページ生成
        $html = view('manual/manage/index', ['base_path' => '../', 'plugins' => $plugins]);

        // 管理画面トップ：出力
        if (!\File::exists($html_root . 'manage')) {
            \File::makeDirectory($html_root . 'manage');
        }
        \File::put($html_root . 'manage/index.html', $html);

        // メソッドをループして、ページの生成
        foreach($plugins as $method) {

            // 画像をコピーするディレクトリのパス確認
            if ($method->img_paths) {
                if (!\File::exists(dirname($html_root . $method->html_path) . '/images')) {
                    \File::makeDirectory(dirname($html_root . $method->html_path) . '/images');
                }
                // 画像をコピー
                foreach (explode(',', $method->img_paths) as $img_path) {
                    \File::copy($screenshots_root . $img_path . '.png',
                                dirname($html_root . $method->html_path) . '/images/' . basename($img_path) . '.png');
                }
            }

            // HTML出力
            if (!\File::exists(dirname($html_root . $method->html_path))) {
                \File::makeDirectory(dirname($html_root . $method->html_path));
            }

            // 管理画面トップ：ページ生成
            $html = view('manual/page/page', ['base_path' => '../../', 'plugins' => $plugins, 'method' => $method]);

            \File::put($html_root . $method->html_path, $html);
        }
    }
}
