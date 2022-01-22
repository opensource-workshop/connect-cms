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
        \Storage::disk('manual')->put("html/index.html", $html);
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
            \Storage::disk('manual')->put('html/' . $method->category . "/index.html", $html);
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
            \Storage::disk('manual')->put('html/' . $method->category . '/' . $method->plugin_name . "/index.html", $html);
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
            \Storage::disk('manual')->put('html/' . $method->category . '/' . $method->plugin_name . '/' . $method->method_name . "/index.html", $html);

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
        $json_paths = json_decode($method->img_args);
        foreach ($json_paths as $json_path) {

            $src_image = imagecreatefrompng($this->screenshots_root . $json_path->path . '.png');
            $new_image = null;

            if (property_exists($json_path, 'methods')) {
                foreach ($json_path->methods as $method) {

                    if (!\Storage::disk('manual')->exists('html/' . dirname($json_path->path))) {
                        \Storage::disk('manual')->makeDirectory('html/' . dirname($json_path->path));
                    }

                    if ($method->method == 'trim_h') {
                        $new_image = imagecreatetruecolor(imagesx($src_image), intval($method->args[1]));
                        imagecopyresampled($new_image, $src_image, 0, 0, 0, 0, imagesx($src_image), imagesy($src_image), imagesx($src_image), imagesy($src_image));
                    }

                    if ($method->method == 'arc') {
                        if ($new_image == null) {
                            $new_image = imagecreatetruecolor(imagesx($src_image), imagesy($src_image));
                        }
                        $elipse_w = $method->args[2];
                        $elipse_h = $method->args[3];
                        for ($line = 0; $line < $method->args[4]; $line++) {
                             $elipse_w--;
                             imageellipse($new_image,
                                          $method->args[0],
                                          $method->args[1],
                                          $elipse_w,
                                          $elipse_h,
                                          imagecolorallocate($new_image, 255, 0, 0)
                             );
                            $elipse_h--;
                        }
                    }
                    imagepng($new_image, \Storage::disk('manual')->path('html/' . $json_path->path . '.png'));
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
        if ($method->img_args) {
            // json か文字列かで処理を分岐
            if (json_decode($method->img_args)) {
                $this->trimingImage($method);
            } else {
                // 画像をコピー
                foreach (explode(',', $method->img_args) as $img_path) {

                    if (!\Storage::disk('manual')->exists('html/' . dirname($img_path))) {
                        \Storage::disk('manual')->makeDirectory('html/' . dirname($img_path));
                    }

                    \File::copy(\Storage::disk('screenshot')->path($img_path . '.png'),
                                \Storage::disk('manual')->path('html/' . $img_path . '.png'));
                }
            }
        }
    }

    /**
     * CSS やJavaScript などの生成したマニュアルで必要なHTML 部品のコピー
     *
     * @return void
     */
    private function htmlSrcCopy()
    {
        $files = \Storage::disk('manual')->allFiles('html_src');
        foreach ($files as $file) {
            \Storage::disk('manual')->put(str_replace('html_src/', 'html/', $file), \Storage::disk('manual')->get($file));
        }
    }

    /**
     * マニュアル出力用クラス
     *
     * @return void
     */
    public function testInvoke()
    {
        // 共通ファイルのコピー
        $this->htmlSrcCopy();

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
