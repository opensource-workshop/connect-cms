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
        $this->frameEdit();
        $this->frameSetting();
        $this->frameDesign();
        $this->frameCol();
        $this->frameDelete();
        $this->frameMail();
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
             'plugin_desc' => 'フレームに関する操作ができます。<br />ここでは、各プラグインに共通的なフレーム操作を説明します。<br />プラグイン毎に固有の設定はプラグインの説明に記載します。',
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
     * フレーム設定系画面全般の説明
     */
    private function frameEdit()
    {
        // ブラウザ操作
        $this->browse(function (Browser $browser) {
            $page = Page::where('permanent_link', '/test')->first();
            $frame = Frame::where('page_id', $page->id)->where('plugin_name', 'whatsnews')->first();

            $browser->visit('/plugin/whatsnews/frame_setting/' . $page->id . '/' . $frame->id . '#frame-' . $frame->id)
                    ->screenshot('common/frame/frameEdit/images/frameEdit1');

            $browser->scrollIntoView('#default_hidden')
                    ->screenshot('common/frame/frameEdit/images/frameEdit2');

            // スマホ画面で開きなおす。（同じURLだと、リロードしないので、一度トップへ戻っている）
            $browser->resize(400, 800);
            $browser->visit('/');
            $browser->visit('/plugin/whatsnews/frame_setting/' . $page->id . '/' . $frame->id . '#frame-' . $frame->id)
                    ->click('#button_collapsing_navbar_lg')
                    ->pause(500)
                    ->screenshot('common/frame/frameEdit/images/frameEdit3');

            // PC画面に戻す。
            $browser->resize(1280, 800);
        });

        // マニュアル用データ出力
        $dusk = Dusks::putManualData(
            ['html_path' => 'common/frame/frameEdit/index.html'],
            ['category' => 'common',
             'sort' => 2,
             'plugin_name' => 'frame',
             'plugin_title' => 'フレーム',
             'plugin_desc' => 'フレームに関する操作ができます。',
             'method_name' => 'frameEdit',
             'method_title' => 'フレーム設定系メニュー',
             'method_desc' => 'フレームの設定メニュー全般を説明します。',
             'method_detail' => '設定系のメニューは、プラグイン、表示エリアや設定幅、ブラウザ幅によって表示方法が異なります。<br />ここでは設定系メニューの表示方法を説明します。',
             'html_path' => 'common/frame/frameEdit/index.html',
             'img_args' => '[
                 {"path": "common/frame/frameEdit/images/frameEdit1",
                  "name": "フレーム設定系メニュー（PC及び幅の広いフレームの場合）-1"
                 },
                 {"path": "common/frame/frameEdit/images/frameEdit2",
                  "name": "フレーム設定系メニュー（PC及び幅の広いフレームの場合）-2",
                  "comment": "<ul class=\"mb-0\"><li>ここでは、例として新着情報プラグインの設定系メニューを示します。</li><li>設定系メニューはフレームヘッダーの下にプラグインで設定可能なメニューが並びます。</li><li>設定できる内容は、プラグインによって異なります。</li></ul>"
                 },
                 {"path": "common/frame/frameEdit/images/frameEdit3",
                  "name": "フレーム設定系メニュー（スマートフォン及び左・右エリアや幅を狭く表示している場合のフレームの場合）",
                  "comment": "<ul class=\"mb-0\"><li>フレームヘッダーの下にハンバーガーアイコンが表示され、それをクリックすると、設定系のメニューが表示されます。</li></ul>"
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
        $this->frame = $this->addContents('/common/frame', '幅2', ['frame_col' => 2]);
        $this->frame = $this->addContents('/common/frame', '幅4', ['frame_col' => 4]);
        $this->frame = $this->addContents('/common/frame', '幅6', ['frame_col' => 6]);

        $this->logout();

        // ブラウザ操作
        $this->browse(function (Browser $browser) {
            $browser->visit('/common/frame')
                    ->screenshot('common/frame/frameCol/images/frameCol1');

            $browser->resize(400, 800);

            $browser->screenshot('common/frame/frameCol/images/frameCol2');

            $browser->resize(1280, 800);
        });

        $this->login(1);

        // マニュアル用データ出力
        $dusk = Dusks::putManualData(
            ['html_path' => 'common/frame/frameCol/index.html'],
            ['category' => 'common',
             'sort' => 2,
             'plugin_name' => 'frame',
             'plugin_title' => 'フレーム',
             'plugin_desc' => 'フレームに関する操作ができます。',
             'method_name' => 'frameCol',
             'method_title' => 'フレーム幅',
             'method_desc' => 'フレーム幅を変更できます。',
             'method_detail' => 'Connect-CMSでは、画面の各エリアを12等分し、フレーム毎に使用する数を指定できます。<br />例えば、3つのフレームの幅をそれぞれ、6、4、2で指定すると、合計12となり、PCで閲覧している場合にフレームが横並びになります。<br />横並びになったフレームは、スマートフォンで閲覧する際は、自動的に縦並びになります。',
             'html_path' => 'common/frame/frameCol/index.html',
             'img_args' => '[
                 {"path": "common/frame/frameCol/images/frameCol1",
                  "name": "フレーム幅の使用例",
                  "comment": "<ul class=\"mb-0\"><li>3つのフレームの幅をそれぞれ、6、4、2で指定した例です。</li></ul>"
                 },
                 {"path": "common/frame/frameCol/images/frameCol2",
                  "name": "スマートフォンでのフレーム幅の使用例",
                  "comment": "<ul class=\"mb-0\"><li>3つのフレームの幅をそれぞれ、6、4、2で指定した場合でも、スマートフォンでは縦に並ぶ例です。</li></ul>"
                 }
             ]',
            'test_result' => 'OK']
        );
    }

    /**
     * フレーム削除
     */
    private function frameDelete()
    {
        // ブラウザ操作
        $this->browse(function (Browser $browser) {
            $page = Page::where('permanent_link', '/test')->first();
            $frame = Frame::where('page_id', $page->id)->where('plugin_name', 'whatsnews')->first();

            $browser->visit('/plugin/whatsnews/frame_delete/' . $page->id . '/' . $frame->id . '#frame-' . $frame->id)
                    ->screenshot('common/frame/frameDelete/images/frameDelete');
        });

        // マニュアル用データ出力
        $dusk = Dusks::putManualData(
            ['html_path' => 'common/frame/frameDelete/index.html'],
            ['category' => 'common',
             'sort' => 2,
             'plugin_name' => 'frame',
             'plugin_title' => 'フレーム',
             'plugin_desc' => 'フレームに関する操作ができます。',
             'method_name' => 'frameDelete',
             'method_title' => 'フレーム削除',
             'method_desc' => 'フレームを削除できます。',
             'method_detail' => 'ここでは、画面上のフレームを削除できます。フレームが削除されても、バケツの中のデータは削除されません。<br />フレームとバケツの関係は、マニュアルの「設計」から、「構造」および「ページ」の各説明を参照してください。',
             'html_path' => 'common/frame/frameDelete/index.html',
             'img_args' => '[
                 {"path": "common/frame/frameDelete/images/frameDelete",
                  "name": "フレーム削除",
                  "comment": "<ul class=\"mb-0\"><li>フレームを削除できます。</li></ul>"
                 }
             ]',
            'test_result' => 'OK']
        );
    }

    /**
     * メール設定
     */
    private function frameMail()
    {
        // 実行
        $this->browse(function (Browser $browser) {
            // 掲示板のメール設定を例にする。
            $page = Page::where('permanent_link', '/test/bbs')->first();
            $frame = Frame::where('page_id', $page->id)->where('plugin_name', 'bbses')->first();
            $browser->visit('/plugin/bbses/editBucketsMails/' . $frame->page_id . '/' . $frame->id . '#frame-' . $frame->id)
                    ->screenshot('common/frame/frameMail/images/editBucketsMails')
                    ->click('#label_notice_on')
                    ->pause(500)
                    ->scrollIntoView('#label_notice_on')
                    ->screenshot('common/frame/frameMail/images/editBucketsMailsNotice')
                    ->click('#label_relate_on')
                    ->pause(500)
                    ->scrollIntoView('#label_relate_on')
                    ->screenshot('common/frame/frameMail/images/editBucketsMailsRelate')
                    ->click('#label_approval_on')
                    ->pause(500)
                    ->scrollIntoView('#label_approval_on')
                    ->screenshot('common/frame/frameMail/images/editBucketsMailsApproval')
                    ->click('#label_approved_on')
                    ->pause(500)
                    ->scrollIntoView('#label_approved_on')
                    ->screenshot('common/frame/frameMail/images/editBucketsMailsApproved');
        });

        // マニュアル用データ出力
        $dusk = Dusks::putManualData(
            ['html_path' => 'common/frame/frameMail/index.html'],
            ['category' => 'common',
             'sort' => 2,
             'plugin_name' => 'frame',
             'plugin_title' => 'フレーム',
             'plugin_desc' => 'フレームに関する操作ができます。',
             'method_name' => 'frameMail',
             'method_title' => 'メール設定',
             'method_desc' => 'プラグインのメール送信条件を設定します。。',
             'method_detail' => '送信タイミングや送信先、件名、本文などを設定します。<br />設定できる送信通知はプラグインによって異なります。<br />ここでは、掲示板プラグインを例にして説明します。',
             'html_path' => 'common/frame/frameMail/index.html',
             'img_args' => '[
                {"path": "common/frame/frameMail/images/editBucketsMails",
                 "name": "送信タイミング設定",
                 "comment": "<ul class=\"mb-0\"><li>タイミング毎にメールの送信を設定できます。</li></ul>"
                },
                {"path": "common/frame/frameMail/images/editBucketsMailsNotice",
                 "name": "投稿通知",
                 "comment": "<ul class=\"mb-0\"><li>投稿通知の設定です。</li></ul>"
                },
                {"path": "common/frame/frameMail/images/editBucketsMailsRelate",
                 "name": "関連記事通知",
                 "comment": "<ul class=\"mb-0\"><li>関連記事の投稿通知の設定です。</li></ul>"
                },
                {"path": "common/frame/frameMail/images/editBucketsMailsApproval",
                 "name": "承認通知",
                 "comment": "<ul class=\"mb-0\"><li>承認通知の設定です。</li></ul>"
                },
                {"path": "common/frame/frameMail/images/editBucketsMailsApproved",
                 "name": "承認済み通知",
                 "comment": "<ul class=\"mb-0\"><li>承認済み通知の設定です。</li></ul>"
                }
             ]',
            'test_result' => 'OK']
        );
    }
}
