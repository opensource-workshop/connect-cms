<?php

namespace Tests\Browser\User;

use Illuminate\Foundation\Testing\DatabaseMigrations;
use Laravel\Dusk\Browser;
use Tests\DuskTestCase;

use App\Enums\PluginName;
use App\Models\Common\Buckets;
use App\Models\Common\Frame;
use App\Models\Common\Page;
use App\Models\Common\Uploads;
use App\Models\Core\Dusks;
use App\Models\Core\FrameConfig;
use App\Models\User\Logins\Login;

/**
 * ログインテスト
 *
 * @see https://github.com/opensource-workshop/connect-cms/wiki/Dusk#テスト実行 [How to test]
 */
class LoginsPluginTest extends DuskTestCase
{
    // 固定記事のフレーム
    private $page_frames = array();

    /**
     * ログインテスト
     *
     * @group user
     * @see https://readouble.com/laravel/6.x/ja/dusk.html#running-tests
     */
    public function test()
    {
        $this->login(1);
        $this->init();
        $this->index();
        $this->createBuckets();
    }

    /**
     * 初期処理
     */
    private function init()
    {
        // データクリア
        Login::truncate();

        // ログイン関係のデータの削除
        // バケツ
        Buckets::where('plugin_name', 'logins')->delete();
        // フレームコンフィグ
        $frames = Frame::where('plugin_name', 'logins')->get();
        foreach ($frames as $frame) {
            FrameConfig::where('frame_id', $frame->id)->delete();
        }
        // フレーム
        Frame::where('plugin_name', 'logins')->delete();

        // 最初にマニュアルの順番確定用にメソッドを指定する。
        $this->reserveManual('index', 'createBuckets');

        // プラグイン配置
        $this->initPlugin('logins', '/test/login');
    }

    /**
     * インデックス
     */
    private function index()
    {
        $this->logout();

        $this->browse(function (Browser $browser) {
            // キャプチャ
            $browser->visit('/test/login')
                    ->assertPathBeginsWith('/')
                    ->screenshot('user/logins/index/images/index');
        });

        $this->login(1);

        $this->browse(function (Browser $browser) {
            // キャプチャ
            $browser->visit('/test/login')
                    ->assertPathBeginsWith('/')
                    ->screenshot('user/logins/index/images/index2');
        });


        // マニュアル用データ出力
        $this->putManualData('[
            {"path": "user/logins/index/images/index",
             "name": "ログイン画面",
             "comment": "<ul class=\"mb-0\"><li>標準のログインがヘッダーエリアに表示されますが、独自にログイン画面を作成したい時に使用します。</li><li>「ログイン後に移動する指定ページ」をログイン・プラグインの設定で変更することができます。</li></ul>"
            },
            {"path": "user/logins/index/images/index2",
             "name": "ログイン後の画面",
             "comment": "<ul class=\"mb-0\"><li>ログイン後はログインしているユーザ名が表示されます。</li></ul>"
            }
        ]', null, 4);
    }

    /**
     * バケツ作成画面
     */
    private function createBuckets()
    {
        $this->browse(function (Browser $browser) {
            // キャプチャ
            $browser->visit('/plugin/logins/createBuckets/' . $this->test_frame->page_id . '/' . $this->test_frame->id . '#frame-' . $this->test_frame->id)
                    ->assertPathBeginsWith('/')
                    ->screenshot('user/logins/createBuckets/images/createBuckets');
        });


        // マニュアル用データ出力
        $this->putManualData('[
            {"path": "user/logins/createBuckets/images/createBuckets",
             "name": "ログイン設定画面",
             "comment": "<ul class=\"mb-0\"><li>ログイン後に移動する指定ページを選択できます。</li><li>「ログイン後に移動する指定ページ」は配置したログイン・プラグイン毎に設定することができます。</li></ul>"
            }
        ]', null, 4);
    }
}
