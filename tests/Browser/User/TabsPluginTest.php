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
use App\Models\User\Contents\Contents;
use App\Models\User\Tabs\Tabs;

/**
 * タブテスト
 *
 * @see https://github.com/opensource-workshop/connect-cms/wiki/Dusk#テスト実行 [How to test]
 */
class TabsPluginTest extends DuskTestCase
{
    // 固定記事のフレーム
    private $page_frames = array();

    /**
     * タブテスト
     *
     * @group user
     * @see https://readouble.com/laravel/6.x/ja/dusk.html#running-tests
     */
    public function test()
    {
        $this->login(1);
        $this->init();
        $this->logout();

        $this->login(1);
        $this->select();
        $this->indexLogin();

        $this->logout();
        $this->index();
    }

    /**
     * 初期処理
     */
    private function init()
    {
        // データクリア
        Tabs::truncate();

        // テストの固定記事の削除
        $page = Page::where('permanent_link', '/test/tab')->first();
        $frames = Frame::where('page_id', $page->id)->where('plugin_name', 'contents')->get();
        foreach ($frames as $frame) {
            if (!empty($frame->bucket_id)) {
                Contents::where('bucket_id', $frame->bucket_id)->delete();
            }
            $frame->delete();
        }

        // 固定記事×2、タブ×1を配置
        $this->addPluginModal('contents', '/test/tab', 2, false);
        $bucket = Buckets::create(['bucket_name' => 'タブテスト②', 'plugin_name' => 'contents']);
        $content = Contents::create(['bucket_id' => $bucket->id, 'content_text' => '<p>タブテストのための固定記事<span style="color: red; font-size: 150%; font-weight: bold;"> ② </span>です。</p>', 'status' => 0]);
        $this->page_frames[0] = Frame::orderBy('id', 'desc')->first();
        $this->page_frames[0]->update(['bucket_id' => $bucket->id, 'frame_title' => '固定記事②', 'default_hidden' => 1]);

        $this->addPluginModal('contents', '/test/tab', 2, false);
        $bucket = Buckets::create(['bucket_name' => 'タブテスト①', 'plugin_name' => 'contents']);
        $content = Contents::create(['bucket_id' => $bucket->id, 'content_text' => '<p>タブテストのための固定記事<span style="color: red; font-size: 150%; font-weight: bold;"> ① </span>です。</p>', 'status' => 0]);
        $this->page_frames[1] = Frame::orderBy('id', 'desc')->first();
        $this->page_frames[1]->update(['bucket_id' => $bucket->id, 'frame_title' => '固定記事①', ]);

        $this->initPlugin('tabs', '/test/tab');

        // 最初にマニュアルの順番確定用にメソッドを指定する。
        $this->reserveManual('index', 'select');
    }

    /**
     * インデックス
     */
    private function index()
    {
        $this->browse(function (Browser $browser) {
            // キャプチャ
            $browser->visit('/test/tab')
                    ->assertPathBeginsWith('/')
                    ->screenshot('user/tabs/index/images/index');
        });

        // マニュアル用データ出力
        $this->putManualData('[
            {"path": "user/tabs/index/images/index",
             "name": "タブ表示",
             "comment": "<ul class=\"mb-0\"><li>タブで、表示するフレームを切り替えられます。</li></ul>"
            },
            {"path": "user/tabs/index/images/index_admin",
             "name": "タブ表示（管理者でログインしている場合）",
             "comment": "<ul class=\"mb-0\"><li>管理者でログインしている場合は、すべてのフレームが見えます。</li><li>最初に表示するフレーム以外は、フレームの編集で「初期状態を非表示とする。」をチェックしておきます。</li></ul>"
            }
        ]');
    }

    /**
     * インデックス（管理者でログイン中）
     */
    private function indexLogin()
    {
        $this->browse(function (Browser $browser) {
            // キャプチャ
            $browser->visit('/test/tab')
                    ->assertPathBeginsWith('/')
                    ->screenshot('user/tabs/index/images/index_admin');
        });
    }

    /**
     * フレーム選択
     */
    private function select()
    {
        // 実行
        $this->browse(function (Browser $browser) {
            // 新規作成
            $browser->visit("/plugin/tabs/select/" . $this->test_frame->page_id . '/' . $this->test_frame->id . '#frame-' . $this->test_frame->id)
                    ->screenshot('user/tabs/select/images/select_')
                    ->click('#label_frame_select' . $this->page_frames[0]->id)
                    ->click('#label_frame_select' . $this->page_frames[1]->id)
                    ->pause(500)
                    ->screenshot('user/tabs/select/images/select')
                    ->press("更新");
        });

        // マニュアル用データ出力
        $this->putManualData('[
            {"path": "user/tabs/select/images/select",
             "name": "フレーム選択",
             "comment": "<ul class=\"mb-0\"><li>タブで制御するフレームを選択します。</li></ul>"
            }
        ]');
    }
}
