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
        $screenshots_root = base_path('tests/Browser/screenshots/');

        // トップページ：ページ生成
        $html = view('manual/index', ['base_path' => './']);

        // トップページ：出力
        \Storage::disk('manual')->put('index.html', $html);

        // index メソッドのプラグイン名を取得
        $plugins = Dusks::where('category', 'manage')->get();

        // 管理画面トップ：ページ生成
        $html = view('manual/manage/index', ['base_path' => '../', 'plugins' => $plugins]);

        // 管理画面トップ：出力
        if (!\Storage::disk('manual')->exists('manage')) {
            \Storage::disk('manual')->makeDirectory('manage');
        }
        \Storage::disk('manual')->put('manage/index.html', $html);

        // メソッドをループして、ページの生成
        foreach($plugins as $method) {

            // 画像をコピーするディレクトリのパス確認
            if ($method->img_paths) {
                if (!\Storage::disk('manual')->exists(dirname($method->html_path) . '/images')) {
                    \Storage::disk('manual')->makeDirectory(dirname($method->html_path) . '/images');
                }
                // 画像をコピー
                foreach (explode(',', $method->img_paths) as $img_path) {
                    \File::copy($screenshots_root . $img_path . '.png',
                                dirname(config('filesystems.disks.manual.root') . '/' .  $method->html_path) . '/images/' . basename($img_path) . '.png');
                }
            }

            // HTML出力
            if (!\Storage::disk('manual')->exists(dirname($method->html_path))) {
                \Storage::disk('manual')->makeDirectory(dirname($method->html_path));
            }

            // ページ生成
            $html = view('manual/page/page', ['base_path' => '../../', 'plugins' => $plugins, 'method' => $method]);
            \Storage::disk('manual')->put($method->html_path, $html);
        }
    }
}
