<?php

namespace Tests\Browser\Blueprint;

use Illuminate\Foundation\Testing\DatabaseMigrations;
use Laravel\Dusk\Browser;
use Tests\DuskTestCase;

use App\Models\Common\Frame;
use App\Models\Core\Dusks;

use App\Enums\PluginName;

/**
 * 外部サービス・テストクラス
 *
 */
class ServiceBlueprintTest extends DuskTestCase
{
    /**
     * テストする関数の制御
     *
     * @group user
     * @see https://readouble.com/laravel/6.x/ja/dusk.html#running-tests
     */
    public function testInvoke()
    {
        // マニュアルデータの削除
        Dusks::where('category', 'blueprint')->where('plugin_name', 'service')->delete();

        // マニュアル出力のために、dusk データベースなど利用するので、アサーションは無条件にOKとしたい。
        $this->browse(function (Browser $browser) {
            $browser->visit('/')
                    ->assertPathBeginsWith('/');
        });

        // マニュアル用データ出力（ページとフレーム）
        $dusk_index = Dusks::create([
            'category' => 'blueprint',
            'sort' => 2,
            'plugin_name' => 'service',
            'plugin_title' => '外部サービス',
            'plugin_desc' => 'Connect-CMSの外部サービスについて説明します。',
            'method_name' => 'index',
            'method_title' => '外部サービス',
            'method_desc' => '外部サービスとは、Connect-CMSから呼び出すAPIサービスです。',
            'method_detail' => '翻訳やPDFサムネイル自動生成、AI顔認識などは、Connect-CMSとしては、呼び出しを行うユーザインタフェースのみを提供し、実際の処理は別のサービスを使用していただくものです。<br />APIの仕様は公開しているため、個人が自作することも、会社等が提供しているサービスを使用することも可能です。',
            'html_path' => 'blueprint/service/index/index.html',
            'test_result' => 'OK',
        ]);
    }
}
