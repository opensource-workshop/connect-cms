<?php

namespace Tests;

use Facebook\WebDriver\Chrome\ChromeOptions;
use Facebook\WebDriver\Remote\DesiredCapabilities;
use Facebook\WebDriver\Remote\RemoteWebDriver;

use Laravel\Dusk\TestCase as BaseTestCase;
use Laravel\Dusk\Browser;

use App\Models\Common\Frame;
use App\Models\Common\Page;
use App\Models\Core\Dusks;
use App\User;

use TruncateAllTables;

abstract class DuskTestCase extends BaseTestCase
{
    use CreatesApplication;

    /**
     * テスト実行のタイミングで一度だけフラグ
     *
     * @var boolean
     */
    private static $migrated = false;

    /**
     * マニュアルの生成可否
     */
    protected $no_manual = false;

    /**
     * テストするフレーム
     */
    protected $test_frame = null;

    /**
     * テストするページ
     */
    protected $test_page = null;

    /**
     * Prepare for Dusk test execution.
     *
     * @beforeClass
     * @return void
     */
    public static function prepare()
    {
        if (! static::runningInSail()) {
            static::startChromeDriver();
        }
    }

    /**
     * Create the RemoteWebDriver instance.
     *
     * @return \Facebook\WebDriver\Remote\RemoteWebDriver
     */
    protected function driver()
    {
        $options = (new ChromeOptions)->addArguments([
            '--disable-gpu',
            '--headless',
            '--window-size=1920,1080',
        ]);

        return RemoteWebDriver::create(
            $_ENV['DUSK_DRIVER_URL'] ?? 'http://localhost:9515',
            DesiredCapabilities::chrome()->setCapability(
                ChromeOptions::CAPABILITY, $options
            )
        );
    }

    /**
     * テスト前共通処理
     * テストケースクラスのテストメソッドごとに (そして最初にインスタンスを作成したときに) 一度実行
     *
     * @return void
     */
    protected function setUp(): void
    {
        parent::setUp();

        // テスト実行のタイミングで一度だけ実行する
        if (! self::$migrated) {
            $this->browse(function (Browser $browser) {
                //$browser->resize(1920, 1080);
                $browser->resize(1280, 800);
            });
        }

        // コマンドライン引数 第5（配列インデックス4）に no_manual が指定されていた場合は、マニュアル作成しない。
        if ($_SERVER && count($_SERVER['argv']) > 4) {
            if ($_SERVER['argv'][4] == 'no_manual') {
                $this->no_manual = true;
            }
        }

/* 一旦コメントアウト。データのクリアは、意識して行いたいかもしれないので。

        // テスト実行のタイミングで一度だけ実行する
        if (! self::$migrated) {
            // config キャッシュクリア
            $this->artisan('config:clear');

            // マイグレーション実行
            $this->artisan('migrate');

            // Seederを実行
            // ・Seederを１つだけ指定して実行（全テーブルを空にするSeeder）
            // ・通常のDatabaseSeederを実行
            $this->seed(TruncateAllTables::class);
            $this->seed();

            self::$migrated = true;
        }
*/
    }

    /**
     * 指定したユーザでログインする。
     */
    protected function login($user_id)
    {
        $this->browse(function (Browser $browser) use ($user_id) {
            $browser->loginAs(User::find($user_id));
            $this->assertTrue(true);
        });
    }

    /**
     * ログアウトする。
     */
    public function logout()
    {
        // ログアウト
        $this->browse(function (Browser $browser) {
            $browser->logout();
        });
    }

    /**
     * 連続スクリーンショット
     * @param Browser $browser
     * @return Browser
     */
    public function screenshot($browser)
    {
        // ウィンドウ高さ
        $height = $browser->script('return window.innerWidth')[0];
        // ウィンドウ幅
        $width = $browser->script('return window.innerHeight')[0];
        // ウィンドウスクロール量取得
        $allHeight = $browser->script('return document.documentElement.scrollHeight')[0];

        // タイトル用にURL取得
        $title = $browser->driver->getCurrentURL();

        // ファイル禁止文字とAPP_URL削除
        $title = str_replace(config('app.url'), '', $title);
        $error_strs = ['"', '*', '/', ':', '<', '>', '?', '|'];
        foreach ($error_strs as $value) {
            $title = str_replace($value, '', $title);
        }

        // タイトルにウィンドウサイズを追加
        $title .= '_'.$width.'x'.$height;

        // クラス名のフォルダに保存する
        $class_name_path = get_class($this);
        $class_name = explode('\\', $class_name_path);

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
            $browser->screenshot($class_name[count($class_name) - 2] . "/" . $class_name[count($class_name) - 1] . "/" . date('Ymd_His_') . $title . '_' . str_pad(++ $index, 3, 0, STR_PAD_LEFT));
            // 0.8秒スリープ
            // usleep(800000);
        }
        return $browser;
    }

    /**
     * phpdebugbarを閉じる
     * DuskTestCase::setup() でテスト前に全実行したところ、全テスト実施時になぜか既にphpdebugbarを閉じてるケースがあり、テストエラーになったため、必要なテストで個別に呼び出す。
     */
    public function closePhpdebugar()
    {
        // APP_DEBUG=trueの場合、画面最下部のボタンが被って押下できずテストエラーになるため、phpdebugbarを閉じる
        if (env('APP_DEBUG')) {
            // phpdebugbarを閉じる
            $this->browse(function (Browser $browser) {
                $browser->visit('/')->click('.phpdebugbar-close-btn');
            });
        }
    }

    /**
     * テストするページID
     */
    public function getTestPageId()
    {
        return $this->test_page->id;
    }

    /**
     * テストするフレームID
     */
    public function getTestFrameId()
    {
        return $this->test_frame->id;
    }

    /**
     * プラグイン追加
     */
    public function addPlugin($add_plugin, $permanent_link = '/', $area = 0, $screenshot = true)
    {
        $this->addPluginModal($add_plugin, $permanent_link, $area, $screenshot);

        $this->test_frame = Frame::where('plugin_name', $add_plugin)->orderBy('id', 'desc')->first();
        $this->test_page = Page::where('permanent_link', $permanent_link)->first();
    }

    /**
     * プラグイン追加（なければ）
     */
    public function addPluginFirst($add_plugin, $permanent_link = '/', $area = 0, $screenshot = true)
    {
        if (!Frame::where('plugin_name', $add_plugin)->first()) {
            $this->addPluginModal($add_plugin, $permanent_link, $area, $screenshot);
        }

        $this->test_frame = Frame::where('plugin_name', $add_plugin)->orderBy('id', 'desc')->first();
        $this->test_page = Page::where('permanent_link', $permanent_link)->first();
    }

    /**
     * プラグイン追加
     */
    public function addPluginModal($add_plugin, $permanent_link = '/', $area = 0, $screenshot = true)
    {
        $this->browse(function (Browser $browser) use ($add_plugin, $permanent_link, $area, $screenshot) {
            // 管理機能からプラグイン追加で指定されたプラグインを追加する。
            $browser->visit($permanent_link)
                    ->clickLink('管理機能')
                    ->assertPathBeginsWith('/');
            if ($screenshot) {
                $browser->screenshot('common/admin_link/plugin/images/add_plugin1');
            }

            // 指定されたエリアにプラグイン追加
            // 早すぎると、プラグイン追加ダイアログが表示しきれないので、1秒待つ。
            $browser->clickLink('プラグイン追加')
                    ->assertPathBeginsWith('/')
                    ->pause(1000);
            if ($screenshot) {
                $browser->screenshot('common/admin_link/plugin/images/add_plugin2');
            }

            $browser->click('#form_add_plugin' . $area);
            if ($screenshot) {
                $browser->screenshot('common/admin_link/plugin/images/add_plugin3');
            }

            $browser->select('#form_add_plugin' . $area, $add_plugin)
                    ->assertPathBeginsWith('/');
            if ($screenshot) {
                $browser->screenshot('common/admin_link/plugin/images/add_plugin4');
            }
        });
    }

    /**
     *  newするクラス名の取得
     */
    private function getClassName($plugin_name)
    {
        // 管理プラグインとして存在するか確認
        $class_name = "App\Plugins\Manage\\" . ucfirst($plugin_name) . "Manage\\" . ucfirst($plugin_name) . "Manage";
        if (class_exists($class_name)) {
            return $class_name;
        }

        // 標準プラグインとして存在するか確認
        $class_name = "App\Plugins\User\\" . ucfirst($plugin_name) . "\\" . ucfirst($plugin_name) . "Plugin";
        if (class_exists($class_name)) {
            return $class_name;
        }

        // オプションプラグインとして存在するか確認
        $class_name = "App\PluginsOption\User\\" . ucfirst($plugin_name) . "\\" . ucfirst($plugin_name) . "Plugin";
        if (class_exists($class_name)) {
            return $class_name;
        }
        return false;
    }

    /**
     *  ドキュメントコメントの解析
     */
    private function getAnnotation($document, $annotation_name)
    {
        if (strpos($document, " * @") === false) {
            return "";
        }
        // 初めの * @ の前までを省き、アノテーションコメントを分割抽出、指定の内容を返却
        $tmp = substr($document, strpos($document, " * @"));
        $tmp = str_replace('*/', '', $tmp);
        $annotation_list = explode(' * @', $tmp);
        foreach ($annotation_list as $annotation_str) {
            if (strpos($annotation_str, " ") === false) {
                continue;
            }
            if (substr($annotation_str, 0, strpos($annotation_str, " ")) == $annotation_name) {
                return substr($annotation_str, strpos($annotation_str, " "));
            }
        }
        return "";
    }

    /**
     *  ソースをリフレクションしてドキュメントを抽出する。
     */
    private function getDocument($annotation_name, $class_name, $method_name = null)
    {
        if ($method_name == null) {
            $class = new \ReflectionClass($class_name);
        } elseif (method_exists($class_name, $method_name)) {
            $class = new \ReflectionMethod($class_name, $method_name);
        } else {
            return "";
        }
        $class_document = $class->getDocComment();
        return trim($this->getAnnotation($class_document, $annotation_name));
    }

    /**
     * マニュアルデータの初期値出力
     */
    public function reserveManual(...$methods)
    {
        foreach ($methods as $method) {
            $this->putManualData(null, $method);
        }
    }

    /**
     * マニュアルデータ出力
     */
    public function putManualData($img_args = null, $method = null)
    {
        // マニュアル用データ出力がOFF の場合は、出力せずに戻る。
        if ($this->no_manual) {
            return;
        }

        // 実行しているサブクラスの名前を取得して、マニュアル用に編集する。
        $sub_class_name = \Str::snake(get_class($this));
        $sub_class_array = explode('\\', $sub_class_name);

        // 呼び出し元メソッド名
        if (empty($method)) {
            $dbg = debug_backtrace();
            $source_method = $dbg[1]['function'];
        } else {
            $source_method = $method;
        }

        // クラス名の本体部分の取得
        $class_name_3 = trim($sub_class_array[3], '_');
        $class_name_3_array = explode('_', $class_name_3);
        $plugin_name = $class_name_3_array[0];

        // html パスの生成
        $html_path = trim($sub_class_array[2], '_') . '/' . $plugin_name . '/' . $source_method . '/index.html';

        // 結果の保存
        $dusk = Dusks::firstOrNew(['html_path' => $html_path]);
        $dusk->category = trim($sub_class_array[2], '_');
        $dusk->sort = 2;
        $dusk->plugin_name = $plugin_name;
        $dusk->method_name = $source_method;
        $dusk->test_result = 'OK';
        $dusk->html_path   = $html_path;

        // 対象クラスの生成とマニュアル用文章の取得
        $class_name = $this->getClassName($plugin_name);
        $dusk->plugin_title = $this->getDocument('plugin_title', $class_name);
        $dusk->plugin_desc = $this->getDocument('plugin_desc', $class_name);
        $dusk->method_title = $this->getDocument('method_title', $class_name, $dusk->method_name);
        $dusk->method_desc = $this->getDocument('method_desc', $class_name, $dusk->method_name);
        $dusk->method_detail = $this->getDocument('method_detail', $class_name, $dusk->method_name);
        $dusk->img_args = $img_args;
        $dusk->save();

        // 結果の親子関係の紐づけ
        if ($source_method != 'index') {
            // 親を取得して、子のparent をセットして保存する。（_lft, _rgt は自動的に変更される）
            $parent = Dusks::where('category', $dusk->category)->where('plugin_name', $dusk->plugin_name)->where('method_name', 'index')->first();
            $dusk->parent_id = $parent->id;
            $dusk->save();
        }
    }

    /**
     * マニュアルデータ（テンプレート変更）出力
     */
    public function putManualTemplateData($frame, $category, $test_path, $plugin, $templates)
    {
        // 画像関係パス
        $img_args = "";

        // テンプレートの数だけループしながら、スクリーンショット取得
        foreach ($templates as $template_name => $template_desc) {
            // フレームのテンプレート名を修正する。
            $frame->template = $template_name;
            $frame->save();

            // 指定のURLを表示し、スクリーンショットを取る。
            $this->browse(function (Browser $browser) use ($category, $test_path, $plugin, $template_name) {
                $browser->visit($test_path)
                        ->assertPathBeginsWith('/')
                        ->screenshot($category . '/' . $plugin[0] . '/template/images/' . $template_name);
            });

            $img_args .=<<< EOF
{"path": "{$category}/{$plugin[0]}/template/images/{$template_name}",
 "name": "{$template_desc}"
}
EOF;
            if (array_key_last($templates) != $template_name) {
                $img_args .= ",";
            }
        }

        // テンプレートを標準に戻す。
        $frame->template = "default";
        $frame->save();

        // マニュアル用データ出力
        $dusk = Dusks::putManualData(
            ['html_path' => $category . '/' . $plugin[0] . '/template/index.html'],
            ['category' => $category,
             'sort' => 2,
             'plugin_name' => $plugin[0],
             'plugin_title' => $plugin[1],
             'plugin_desc' => '',
             'method_name' => 'template',
             'method_title' => 'テンプレート',
             'method_desc' => 'テンプレート変更時の表示結果を紹介します。',
             'method_detail' => '',
             'html_path' => $category . '/' . $plugin[0] . '/template/index.html',
             'img_args' => '[' . $img_args . ']',
             'test_result' => 'OK']
        );
    }
}
