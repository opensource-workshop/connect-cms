<?php

namespace Tests\Manual\src;

use Laravel\Dusk\Browser;
use Tests\DuskTestCase;

use App\Models\Core\Dusks;

class ManualOutput extends DuskTestCase
{
    /**
     * スクリーンショット保存ルートパス
     */
    private $screenshots_root;

    /**
     * コンストラクタ
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * トップページ出力
     *
     * @return void
     */
    private function outputHome($view_path)
    {
        // ページ生成
        $html = view($view_path, ['level' => 'home', 'base_path' => './']);
        \Storage::disk('manual')->put("index.html", $html);
    }

    /**
     * カテゴリトップ出力
     *
     * @return void
     */
    private function outputCategory($view_path, $methods)
    {
        // カテゴリをループ
        foreach($methods->where('plugin_name', 'index')->where('method_name', 'index') as $method) {
            // ページ生成
            $html = view($view_path, ['level' => 'category', 'base_path' => '../', 'methods' => $methods, 'current_method' => $method]);
            \Storage::disk('manual')->put($method->category . "/index.html", $html);
        }
    }

    /**
     * プラグイントップ出力
     *
     * @return void
     */
    private function outputPlugin($view_path, $methods)
    {
        // プラグインをループ
        foreach($methods->where('method_name', 'index') as $method) {
            // ページ生成
            $html = view($view_path, ['level' => 'plugin', 'base_path' => '../../', 'methods' => $methods, 'current_method' => $method]);
            \Storage::disk('manual')->put($method->category . '/' . $method->plugin_name . "/index.html", $html);
        }
    }

    /**
     * メソッド出力
     *
     * @return void
     */
    private function outputMethod($view_path, $methods)
    {
        // メソッドをループ
        foreach($methods as $method) {
            // ページ生成
            $html = view($view_path, ['level' => 'method', 'base_path' => '../../../', 'methods' => $methods, 'current_method' => $method]);
            \Storage::disk('manual')->put($method->category . '/' . $method->plugin_name . '/' . $method->method_name . "/index.html", $html);

            // 画像の出力
            $this->outputImage($method);
        }
    }

    /**
     * 画像トリミング
     *
     * @return void
     */
    private function trimingImage($method)
    {
        // 元画像, 切り取りする開始点の X, Y, 切り取りする W, H/*
        $json_paths = json_decode($method->img_paths);
        foreach ($json_paths as $json_path) {
            foreach ($json_path->img_methods as $img_method) {
                if ($img_method == 'trim_h') {
                    $src_image = imagecreatefrompng($this->screenshots_root . $json_path->name . '.png');
                    $new_image = imagecreatetruecolor(imagesx($src_image), intval($img_method->args[1]));
                    imagecopyresampled($new_image, $src_image, 0, 0, 0, 0, imagesx($src_image), imagesy($src_image), imagesx($src_image), imagesy($src_image));
                    imagepng($new_image, dirname(config('filesystems.disks.manual.root') . '/' .  $method->html_path) . '/images/' . basename($img_method->name) . '.png');
                }
            }
        }
    }

    /**
     * 画像出力
     *
     * @return void
     */
    private function outputImage($method)
    {
        // 画像をコピーするディレクトリのパス確認
        if ($method->img_paths) {
            if (!\Storage::disk('manual')->exists(dirname($method->html_path) . '/images')) {
                \Storage::disk('manual')->makeDirectory(dirname($method->html_path) . '/images');
            }
            // json か文字列かで処理を分岐
            if (json_decode($method->img_paths)) {
                $this->trimingImage($method);
            } else {
                // 画像をコピー
                foreach (explode(',', $method->img_paths) as $img_path) {
                    \File::copy($this->screenshots_root . $img_path . '.png',
                                dirname(config('filesystems.disks.manual.root') . '/' .  $method->img_paths) . '/images/' . basename($img_path) . '.png');
                }
            }
        }
    }

    /**
     * マニュアル出力用クラス
     *
     * @return void
     */
    public function testInvoke()
    {
        // Laravel がコンストラクタでbase_path など使えないので、ここで。
        $this->screenshots_root = base_path('tests/Browser/screenshots/');

        // マニュアル出力のために、dusk データベースなど利用するので、アサーションは無条件にOKとしたい。
        $this->browse(function (Browser $browser) {
            $browser->visit('/')
                    ->assertTitleContains('Connect-CMS');
        });

        // 全データ取得
        $methods = Dusks::get();

        // トップページ(トップページは Dusk レコードがないので、空の Dusks を使用する)
        $this->outputHome('manual/index');

        // カテゴリトップ出力
        $this->outputCategory('manual/category', $methods);

        // プラグイントップ出力
        $this->outputPlugin('manual/plugin', $methods);

        // メソッド出力
        $this->outputMethod('manual/method', $methods);
    }
}
