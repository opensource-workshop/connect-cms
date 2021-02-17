<?php

namespace Tests;

use Facebook\WebDriver\Chrome\ChromeOptions;
use Facebook\WebDriver\Remote\DesiredCapabilities;
use Facebook\WebDriver\Remote\RemoteWebDriver;

use Laravel\Dusk\TestCase as BaseTestCase;
use Laravel\Dusk\Browser;

use App\User;

abstract class DuskTestCase extends BaseTestCase
{
    use CreatesApplication;

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
            // 画面スクロール
            $browser->script("window.scrollTo(0, {$i});");
            // スクリーンショット撮影
            $browser->screenshot($class_name[count($class_name) - 2] . "/" . $class_name[count($class_name) - 1] . "/" . date('Ymd_His_') . $title . '_' . str_pad(++ $index, 3, 0, STR_PAD_LEFT));
            // 0.8秒スリープ
            usleep(800000);
        }
        return $browser;
    }
}
