<?php

namespace Tests\Browser\Common;

use Illuminate\Foundation\Testing\DatabaseMigrations;
use Laravel\Dusk\Browser;
use Tests\DuskTestCase;

use App\Models\Common\Frame;
use App\Models\Common\Page;
use App\Models\Core\Dusks;

use App\Enums\PluginName;

/**
 * フレーム・テスト
 *
 * @see https://github.com/opensource-workshop/connect-cms/wiki/Dusk#テスト実行 [How to test]
 */
class FrameTest extends DuskTestCase
{
    private $frame = null;

    /**
     * テストする関数の制御
     *
     * @group user
     * @see https://readouble.com/laravel/6.x/ja/dusk.html#running-tests
     */
    public function test()
    {
        $this->init();
        $this->login(1);
        $this->frameButton();
        $this->frameSetting();
        $this->frameDesign();
        $this->frameCol();
    }

    /**
     * 初期処理
     */
    private function init()
    {
        $this->clearContents('/common/frame');
    }

    /**
     * フレーム操作ボタン
     */
    private function frameButton()
    {
        // 固定記事の配置
        $this->frame = $this->addContents('/common/frame', 'テスト用の固定記事');

        // ブラウザ操作
        $this->browse(function (Browser $browser) {
            $browser->visit('/common/frame')
                    ->assertPathBeginsWith('/')
                    ->screenshot('common/frame/index/images/frameButton');
        });

        // マニュアル用データ出力
        $dusk = Dusks::putManualData(
            ['html_path' => 'common/frame/index/index.html'],
            ['category' => 'common',
             'sort' => 2,
             'plugin_name' => 'frame',
             'plugin_title' => 'フレーム',
             'plugin_desc' => 'フレームに関する操作ができます。',
             'method_name' => 'index',
             'method_title' => 'フレーム操作ボタン',
             'method_desc' => 'フレームを操作するためにいくつかのボタンがあります。',
             'method_detail' => '順番変更やフレーム編集へのリンクがあります。',
             'html_path' => 'common/frame/index/index.html',
             'img_args' => '[
                 {"path": "common/frame/index/images/frameButton",
                  "name": "権限のある状態のフレーム",
                  "comment": "<ul class=\"mb-0\"><li>権限のあるユーザでログインしている場合、フレームヘッダーの右側に、フレームを操作するリンクが表示されます。</li><li>フレームの操作に関係するアイコンを以下で説明します。</li></ul>"
                 }
             ]',
            'test_result' => 'OK']
        );
    }

    /**
     * フレーム編集
     */
    private function frameSetting()
    {
        // ブラウザ操作
        $this->browse(function (Browser $browser) {
            $browser->resize(1280, 850);

            $page = Page::where('permanent_link', '/')->first();
            $frame = Frame::where('page_id', $page->id)->where('plugin_name', 'contents')->where('area_id', 0)->orderBy('display_sequence', 'asc')->first();

            $browser->visit('/plugin/contents/frame_setting/' . $page->id . '/' . $frame->id . '#frame-' . $frame->id)
                    ->screenshot('common/frame/frameSetting/images/frameSetting');

            $browser->resize(1280, 800);
        });

        // マニュアル用データ出力
        $dusk = Dusks::putManualData(
            ['html_path' => 'common/frame/frameSetting/index.html'],
            ['category' => 'common',
             'sort' => 2,
             'plugin_name' => 'frame',
             'plugin_title' => 'フレーム',
             'plugin_desc' => 'フレームに関する操作ができます。',
             'method_name' => 'frameSetting',
             'method_title' => 'フレーム編集',
             'method_desc' => 'フレームに関する設定を変更できます。',
             'method_detail' => '下に各項目の説明を記載します。',
             'html_path' => 'common/frame/frameSetting/index.html',
             'img_args' => '[
                 {"path": "common/frame/frameSetting/images/frameSetting",
                  "name": "フレーム編集",
                  "comment": "<ul class=\"mb-0\"><li>各項目の説明は以下を参照してください。</li></ul>"
                 }
             ]',
            'test_result' => 'OK']
        );
    }

    /**
     * フレームデザイン
     */
    private function frameDesign()
    {
        // ブラウザ操作
        $this->browse(function (Browser $browser) {
            $browser->visit('/plugin/contents/frame_setting/' . $this->frame->page_id . '/' . $this->frame->id . '#frame-' . $this->frame->id)
                    ->click('#frame_design')
                    ->screenshot('common/frame/frameDesign/images/frameDesign');
            $this->logout();

            $frame_designs = ["none", "primary", "secondary", "success", "info", "warning", "danger", "default"];
            foreach ($frame_designs as $frame_design) {
                $title = ($frame_design == 'none') ? '' : '[無題]';
                $this->login(1);
                $browser->visit('/plugin/contents/frame_setting/' . $this->frame->page_id . '/' . $this->frame->id . '#frame-' . $this->frame->id)
                        ->type('frame_title', $title)
                        ->select('frame_design', $frame_design)
                        ->press('更新');
                $this->logout();
                $browser->visit('/common/frame')
                        ->screenshot('common/frame/frameDesign/images/' . $frame_design);
            }
            $this->login(1);
        });

        // マニュアル用データ出力
        $dusk = Dusks::putManualData(
            ['html_path' => 'common/frame/frameDesign/index.html'],
            ['category' => 'common',
             'sort' => 2,
             'plugin_name' => 'frame',
             'plugin_title' => 'フレーム',
             'plugin_desc' => 'フレームに関する操作ができます。',
             'method_name' => 'frameDesign',
             'method_title' => 'フレームデザイン',
             'method_desc' => 'フレームデザインを変更できます。',
             'method_detail' => 'フレームデザインは意味を持つ8つのパターンから選択できます。<br />各デザインは、設定しているテーマによって、配色などが変わる可能性があります。',
             'html_path' => 'common/frame/frameDesign/index.html',
             'img_args' => '[
                 {"path": "common/frame/frameDesign/images/frameDesign",
                  "name": "フレームデザインの選択肢",
                  "comment": "<ul class=\"mb-0\"><li>以下に、初期テーマの場合の各フレームデザインを示します。</li></ul>"
                 },
                 {"path": "common/frame/frameDesign/images/none",
                  "name": "none",
                  "comment": "<ul class=\"mb-0\"><li>none の場合は通常、タイトルも空にして使用することが多くあります。</li></ul>"
                 },
                 {"path": "common/frame/frameDesign/images/default",
                  "name": "default",
                  "comment": "<ul class=\"mb-0\"><li>初期状態でのフレームデザインです。</li></ul>"
                 },
                 {"path": "common/frame/frameDesign/images/primary", "name": "primary"},
                 {"path": "common/frame/frameDesign/images/secondary", "name": "secondary"},
                 {"path": "common/frame/frameDesign/images/success", "name": "success"},
                 {"path": "common/frame/frameDesign/images/info", "name": "info"},
                 {"path": "common/frame/frameDesign/images/warning", "name": "warning"},
                 {"path": "common/frame/frameDesign/images/danger", "name": "danger"}
             ]',
            'test_result' => 'OK']
        );
    }

    /**
     * フレーム幅
     */
    private function frameCol()
    {
        // 固定記事の配置
        $this->frame = $this->addContents('/common/frame', '幅6', ['frame_col' => 6]);
        $this->frame = $this->addContents('/common/frame', '幅4', ['frame_col' => 4]);
        $this->frame = $this->addContents('/common/frame', '幅2', ['frame_col' => 2]);
    }
}
