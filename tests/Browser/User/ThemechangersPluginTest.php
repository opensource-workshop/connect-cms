<?php

namespace Tests\Browser\User;

use Illuminate\Foundation\Testing\DatabaseMigrations;
use Laravel\Dusk\Browser;
use Tests\DuskTestCase;

use App\Enums\PluginName;
use App\Models\Common\Buckets;
use App\Models\Common\Frame;
use App\Models\Common\Page;
use App\Models\Core\Dusks;

class ThemechangersPluginTest extends DuskTestCase
{
    /**
     * テストする関数の制御
     *
     * @group manage
     * @see https://readouble.com/laravel/6.x/ja/dusk.html#running-tests
     */
    public function testInvoke()
    {
        $this->login(1);
        $this->index();
        $this->logout();
        $this->select();
    }

    /**
     * インデックス
     */
    private function index()
    {
        // マニュアル出力のために、dusk データベースなど利用するので、アサーションは無条件にOKとしたい。
        $this->assertTrue(true);

        // プラグインがなければ追加(テストするFrameとページのインスタンス変数への保持も)
        $this->addPluginFirst('themechangers', '/', 1);

        // ブラウザ操作
        $this->browse(function (Browser $browser) {
            $browser->visit('/')
                    ->screenshot('user/themechangers/index/images/index');
        });

        // マニュアル用データ出力
        $this->putManualData('[
            {"path": "user/themechangers/index/images/index",
             "name": "テーマチェンジャーを配置したところ。",
             "comment": "<ul class=\"mb-0\"><li>現在インストールされているテーマを選択することができるようになります。</li></ul>"
            }
        ]', null, 4);
    }

    /**
     * ページ選択
     */
    private function select()
    {
        // 実行
        $this->browse(function (Browser $browser) {
            // 新規作成
            $browser->visit("/")
                    ->screenshot('user/themechangers/select/images/select1')
                    ->select("session_theme", 'Defaults/DarkRed')
                    ->click("#themechanger_button" . $this->test_frame->id)
                    ->pause(500)
                    ->visit("/test")
                    ->visit("/")
                    ->screenshot('user/themechangers/select/images/select2');
        });

        $this->login(1);
        $this->browse(function (Browser $browser) {
            // フレームを下移動
            $browser->visit("/")
                    ->click('#frame_down_' . $this->test_frame->id)
                    ->pause(500)
                    ->click('#frame_down_' . $this->test_frame->id);
        });
        $this->logout();

        // マニュアル用データ出力
        $this->putManualData('[
            {"path": "user/themechangers/select/images/select2",
             "name": "DefaultグループのDarkRedを選択したところ。",
             "comment": "<ul class=\"mb-0\"><li>テーマDarkRedの設定どおりにフレームなどのデザインが変更されています。</li></ul>"
            }
        ]', null, 4);
    }
}
