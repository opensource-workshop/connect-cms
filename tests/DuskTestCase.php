<?php

namespace Tests;

use Facebook\WebDriver\Chrome\ChromeOptions;
use Facebook\WebDriver\Remote\DesiredCapabilities;
use Facebook\WebDriver\Remote\RemoteWebDriver;

use Laravel\Dusk\TestCase as BaseTestCase;
use Laravel\Dusk\Browser;

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
     * プラグイン追加
     */
    public function addPluginModal($add_plugin)
    {
        $this->browse(function (Browser $browser) use ($add_plugin) {
            // 管理機能からプラグイン追加で固定記事を追加する。
            $browser->visit('/')
                    ->clickLink('管理機能')
                    ->assertTitleContains('Connect-CMS');
            $this->screenshot($browser);

            // ヘッダーエリアにプラグイン追加
            $browser->clickLink('プラグイン追加')
                    ->assertTitleContains('Connect-CMS');

            // 早すぎると、プラグイン追加ダイアログが表示しきれないので、1秒待つ。
            $browser->pause(1000);
            $this->screenshot($browser);

            $browser->select('add_plugin', $add_plugin)
                    ->assertTitleContains('Connect-CMS');
            $this->screenshot($browser);
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
     * マニュアルデータ出力
     */
    public function putManualData()
    {
        // 実行しているサブクラスの名前を取得して、マニュアル用に編集する。
        $sub_class_name = \Str::snake(get_class($this));
        $sub_class_array = explode('\\', $sub_class_name);

        // 呼び出し元メソッド名
        $dbg = debug_backtrace();
        $source_method = $dbg[1]['function'];

        // クラス名の本体部分の取得
        $class_name_3 = trim($sub_class_array[3], '_');
        $class_name_3_array = explode('_', $class_name_3);
        $plugin_name = $class_name_3_array[0];

        // html パスの生成
        $html_path = trim($sub_class_array[2], '_') . '/' . $plugin_name . '/' . $source_method;

        // 対象クラスの生成とマニュアル用文章の取得
        $class_name = $this->getClassName($plugin_name);
        $manual = $class_name::declareManual();

        // 結果の保存
        Dusks::updateOrCreate(
        ['html_path' => $html_path],
        [
            'category' => trim($sub_class_array[2], '_'),
            'sort' => '2',
            'plugin' => $plugin_name,
            'method' => $source_method,
            'test_result' => 'OK',
            'html_path' => $html_path,
            'function_title' => array_key_exists('function_title', $manual) ? $manual['function_title'] : '',
            'method_desc' => array_key_exists('method_desc', $manual) && array_key_exists($source_method, $manual['method_desc'])? $manual['method_desc'][$source_method] : '',
            'function_desc' => array_key_exists('function_desc', $manual) ? $manual['function_desc'] : '',
        ]);
    }
}
