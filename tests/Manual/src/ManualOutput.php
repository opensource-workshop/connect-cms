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
     * HTML 相対パスを生成
     *
     * @return void
     */
    private function getLinkPath($dusk, $category_top = false)
    {
        // html_path を見て、自分の深さの分だけ、ルートに戻れる相対パスを生成する。
        if ($category_top) {
            $dir_count = count(explode('/', dirname($dusk->html_path))) - 2;
        } else {
            $dir_count = count(explode('/', dirname($dusk->html_path)));
        }

        if ($dir_count < 1) {
            return "./";
        }
        return str_repeat("../", $dir_count);
    }

    /**
     * ページ出力
     *
     * @return void
     */
//    private function outputHtml($view_path, $methods, $current_method, $category_top = false, $plugin_top = false)
    private function outputHtml($view_path, $methods = null)
    {
        // ページ生成
        $html = view($view_path, ['methods' => $methods]);
        \Storage::disk('manual')->put($current_method->category . "/index.html", $html);
return;
        // ページ生成
        $html = view($view_path, ['base_path' => $this->getLinkPath($current_method, $category_top), 'methods' => $methods, 'current_method' => $current_method]);

        // ページ出力
        if ($category_top) {
            \Storage::disk('manual')->put($current_method->category . "/index.html", $html);
        } elseif ($plugin_top) {
            \Storage::disk('manual')->put(dirname(dirname($current_method->html_path)) . "/index.html", $html);
//\Log::debug($current_method->category . "/index.html\n");
        } else {
            \Storage::disk('manual')->put($current_method->html_path, $html);
//\Log::debug($current_method->html_path . "\n");
        }
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

return;

        // Laravel がコンストラクタでbase_path など使えないので、ここで。
        $this->screenshots_root = base_path('tests/Browser/screenshots/');

        // マニュアル出力のために、dusk データベースなど利用するので、アサーションは無条件にOKとしたい。
        $this->browse(function (Browser $browser) {
            $browser->visit('/')
                    ->assertTitleContains('Connect-CMS');
        });

        // トップページ(トップページは Dusk レコードがないので、空の Dusks を使用する)
        $this->outputHtml('manual/index', collect(new Dusks()), new Dusks(), true);

        // カテゴリ（plugin_name = index）のトップを処理
        $categories = Dusks::where('plugin_name', 'index')->get();

        foreach ($categories as $category) {

            // カテゴリトップ：ページ生成
            $this->outputHtml('manual/category/index', $categories, $category, true);

            // カテゴリ内のプラグインを取得
            $plugins = Dusks::where('category', $category->category)->where('method_name', 'index')->get();

            // プラグインをループ
            foreach($plugins as $method) {

                // プラグイントップ：ページ生成
//                $this->outputHtml('manual/category/index', $plugins, $method, false, true);
            }
        }

/*

        // メソッドをループして、ページの生成
        foreach($plugins as $method) {

            // 画像をコピーするディレクトリのパス確認
            if ($method->img_paths) {
                if (!\Storage::disk('manual')->exists(dirname($method->html_path) . '/images')) {
                    \Storage::disk('manual')->makeDirectory(dirname($method->html_path) . '/images');
                }
                // 画像をコピー
                foreach (explode(',', $method->img_paths) as $img_path) {
                    \File::copy($this->screenshots_root . $img_path . '.png',
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
*/
    }
}
