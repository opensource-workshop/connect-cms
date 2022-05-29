<?php

namespace Tests\Browser\ConnectStudy;

use Illuminate\Foundation\Testing\DatabaseMigrations;
use Laravel\Dusk\Browser;
use Tests\DuskTestCase;

use App\User;

use App\Enums\PluginName;
use App\Models\Common\Buckets;
use App\Models\Common\Frame;
use App\Models\Core\Dusks;

use App\ModelsOption\User\Dronestudies\Dronestudy;
use App\ModelsOption\User\Dronestudies\DronestudyPost;

/**
 * DroneStudyテスト
 */
class DroneStudyTest extends DuskTestCase
{
    /**
     * DroneStudyテスト
     */
    public function test()
    {
        $this->category = 'study';

        $this->init();
        $this->login(1);

        $this->createBuckets();
        $this->editView();
        $this->listBuckets();
        $this->editBucketsRoles();

        $this->logout();

        $this->index();
    }

    /**
     * 初期処理
     */
    private function init()
    {
        // データクリア
        Dronestudy::truncate();
        DronestudyPost::truncate();
        $this->initPlugin('dronestudies', '/study/drone');

        // 最初にマニュアルの順番確定用にメソッドを指定する。
        $this->reserveManualMin('study', 'dronestudies', ['index', 'createBuckets', 'editView', 'editBucketsRoles', 'listBuckets']);

        // プラグインがなければ追加(テストするFrameとページのインスタンス変数への保持も)
        $this->addPluginFirst('dronestudies', '/study/drone', 2);
    }

    /**
     * バケツ作成
     */
    private function createBuckets()
    {
        // 実行
        $this->browse(function (Browser $browser) {
            // バケツ各内容の入力
            $browser->visit('/plugin/dronestudies/createBuckets/' . $this->test_frame->page_id . '/' . $this->test_frame->id . '#frame-' . $this->test_frame->id)
                    ->type('name', 'テストのDroneStudy')
                    ->type('command_interval', '7')
                    ->click('#label_use_stream_1')
                    ->type('max_block_count', '10')
                    ->type('remote_url', 'http://localhost/remote-drone')
                    ->type('remote_id', '12')
                    ->type('secret_code', 'Secret-T187HjN')
                    ->screenshot('study/dronestudies/createBuckets/images/createBuckets')
                    ->scrollIntoView('footer')
                    ->screenshot('study/dronestudies/createBuckets/images/createBuckets2')
                    ->press('登録確定');

            // 一度、選択確定させる。
            $bucket = Buckets::where('plugin_name', 'dronestudies')->first();
            $browser->visit('/plugin/dronestudies/listBuckets/' . $this->test_frame->page_id . '/' . $this->test_frame->id . '#frame-' . $this->test_frame->id)
                    ->radio('select_bucket', $bucket->id)
                    ->press("表示DroneStudy変更");

            // 変更
            $browser->visit("/plugin/dronestudies/editBuckets/" . $this->test_frame->page_id . '/' . $this->test_frame->id . '#frame-' . $this->test_frame->id)
                    ->screenshot('study/dronestudies/createBuckets/images/editBuckets')
                    ->scrollIntoView('footer')
                    ->screenshot('study/dronestudies/createBuckets/images/editBuckets2');
        });

        // マニュアル用データ出力
        $dusk = Dusks::updateOrCreate([
            'plugin_name' => 'dronestudies',
            'method_name' => 'createBuckets'
        ], [
            'category' => 'study',
            'sort' => 6,
            'plugin_name' => 'dronestudies',
            'plugin_title' => 'DroneStudy',
            'plugin_desc' => 'ドローンでプログラミングを学べます。',
            'method_name' => 'createBuckets',
            'method_title' => 'バケツ',
            'method_desc' => '複数のDroneStudyの設定を作成できます。',
            'method_detail' => '',
            'html_path' => 'study/dronestudies/createBuckets/index.html',
            'img_args' => '[
                {"path": "study/dronestudies/createBuckets/images/createBuckets",
                 "name": "作成",
                 "comment": "<ul class=\"mb-0\"><li>リモートサイト設定をすることで、リモートサイトのプログラムを呼び出せます。</li></ul>"
                },
                {"path": "study/dronestudies/createBuckets/images/createBuckets2",
                 "name": "作成2",
                 "comment": "<ul class=\"mb-0\"><li>新しいDroneStudyを作成できます。</li></ul>"
                },
                {"path": "study/dronestudies/createBuckets/images/editBuckets2",
                 "name": "変更・削除",
                 "comment": "<ul class=\"mb-0\"><li>DroneStudyを変更・削除できます。</li></ul>"
                }
            ]',
            'level' => null,
            'test_result' => 'OK',
        ]);
    }

    /**
     * フレーム表示設定
     */
    private function editView()
    {
        // 実行
        $this->browse(function (Browser $browser) {
            $browser->visit('/plugin/dronestudies/editView/' . $this->test_frame->page_id . '/' . $this->test_frame->id . '#frame-' . $this->test_frame->id)
                    ->screenshot('study/dronestudies/editView/images/editView')
                    ->scrollIntoView('footer')
                    ->screenshot('study/dronestudies/editView/images/editView2');
        });

        // マニュアル用データ出力
        $dusk = Dusks::updateOrCreate([
            'plugin_name' => 'dronestudies',
            'method_name' => 'editView'
        ], [
            'category' => 'study',
            'sort' => 6,
            'plugin_name' => 'dronestudies',
            'plugin_title' => 'DroneStudy',
            'plugin_desc' => 'ドローンでプログラミングを学べます。',
            'method_name' => 'editView',
            'method_title' => '表示設定',
            'method_desc' => 'DroneStudyの表示内容を設定できます。',
            'method_detail' => 'ひらがなモードや実行ボタンの表示/非表示を設定できます。',
            'html_path' => 'study/dronestudies/editView/index.html',
            'img_args' => '[
                {"path": "study/dronestudies/editView/images/editView",
                 "name": "表示設定",
                 "comment": "<ul class=\"mb-0\"><li>ブロックの言語を漢字、ひらがなから選ぶことができます。まだ、習っていない漢字がある学年では、ひらがなで使うことができます。</li></ul>"
                }
            ]',
            'level' => null,
            'test_result' => 'OK',
        ]);
    }

    /**
     * 選択
     */
    private function listBuckets()
    {
        // 実行
        $this->browse(function (Browser $browser) {
            $browser->visit('/plugin/dronestudies/listBuckets/' . $this->test_frame->page_id . '/' . $this->test_frame->id . '#frame-' . $this->test_frame->id)
                    ->screenshot('study/dronestudies/listBuckets/images/listBuckets');
        });

        // マニュアル用データ出力
        $dusk = Dusks::updateOrCreate([
            'plugin_name' => 'dronestudies',
            'method_name' => 'listBuckets'
        ], [
            'category' => 'study',
            'sort' => 6,
            'plugin_name' => 'dronestudies',
            'plugin_title' => 'DroneStudy',
            'plugin_desc' => 'ドローンでプログラミングを学べます。',
            'method_name' => 'listBuckets',
            'method_title' => 'バケツ選択',
            'method_desc' => 'このフレームに表示するDroneStudyを変更できます。',
            'method_detail' => '',
            'html_path' => 'study/dronestudies/listBuckets/index.html',
            'img_args' => '[
                {"path": "study/dronestudies/listBuckets/images/listBuckets",
                 "name": "バケツ選択",
                 "comment": "<ul class=\"mb-0\"><li>クラスごとにDroneStudyを用意しておくなどの使い方ができます。</li></ul>"
                }
            ]',
            'level' => null,
            'test_result' => 'OK',
        ]);
    }

    /**
     * 権限設定
     */
    private function editBucketsRoles()
    {
        // 実行
        $this->browse(function (Browser $browser) {
            $browser->visit('/plugin/dronestudies/editBucketsRoles/' . $this->test_frame->page_id . '/' . $this->test_frame->id . '#frame-' . $this->test_frame->id)
                    ->click('#label_role_reporter_post')
                    ->pause(500)
                    ->screenshot('study/dronestudies/editBucketsRoles/images/editBucketsRoles')
                    ->press('更新');
        });

        // マニュアル用データ出力
        $dusk = Dusks::updateOrCreate([
            'plugin_name' => 'dronestudies',
            'method_name' => 'editBucketsRoles'
        ], [
            'category' => 'study',
            'sort' => 6,
            'plugin_name' => 'dronestudies',
            'plugin_title' => 'DroneStudy',
            'plugin_desc' => 'ドローンでプログラミングを学べます。',
            'method_name' => 'editBucketsRoles',
            'method_title' => '権限設定',
            'method_desc' => 'DroneStudyでプログラミングを保存できる権限を設定します。',
            'method_detail' => '権限が設定されていないと保存できないので注意しましょう。',
            'html_path' => 'study/dronestudies/editBucketsRoles/index.html',
            'img_args' => '[
                {"path": "study/dronestudies/editBucketsRoles/images/editBucketsRoles",
                 "name": "権限設定",
                 "comment": "<ul class=\"mb-0\"><li>プログラミングするユーザに割り当てた権限を設定します。</li></ul>"
                }
            ]',
            'level' => null,
            'test_result' => 'OK',
        ]);
    }

    /**
     * インデックス
     */
    private function index()
    {
        // ユーザ「test-user」で作成したとする。
        $test_user = User::where('userid', 'test-user')->first();

        // 「test-user」でログイン
        $this->login($test_user->id);

        // データの追加（正三角形）
        $post = DronestudyPost::create([
            'dronestudy_id' => 1,
            'title' => '正三角形',
            'xml_text' => '<xml xmlns="https://developers.google.com/blockly/xml"><block type="drone_takeoff" id="xcHdH3ki3$K*sFY3EDZ~" x="-204" y="-203"><next><block type="drone_loop" id="ksos0_pyJ*YDW/*m%$Ie"><field name="arg_drone_loop">3</field><statement name="loop_statement"><block type="drone_forward" id="gN[}s6_1h#[-q}^=_]N^"><field name="arg_drone_forward">100</field><next><block type="drone_cw" id="dn[*Tl|_!c6R4CbqWcDY"><field name="arg_drone_cw">120</field></block></next></block></statement><next><block type="drone_land" id="s@}1/2XZK/]^k*,beV#O"></block></next></block></next></block></xml>',
            'status' => 0,
            'created_id' => $test_user->id,
            'created_name' => $test_user->name,
        ]);

        // スクリーンショット取得
        $this->browse(function (Browser $browser) use ($post) {
            $browser->visit('/plugin/dronestudies/index/' . $this->test_frame->page_id . '/' . $this->test_frame->id . '/' . $post->id . '#frame-' . $this->test_frame->id)
                    ->screenshot('study/dronestudies/index/images/index')
                    ->scrollIntoView('footer')
                    ->screenshot('study/dronestudies/index/images/index2');
        });

        // データの追加（映像のON/OFF）
        $post = DronestudyPost::create([
            'dronestudy_id' => 1,
            'title' => '映像プログラミング',
            'xml_text' => '<xml xmlns="https://developers.google.com/blockly/xml"><block type="drone_takeoff" id="3/]%3ShmF]Ybi1r]=~iP" x="-177" y="-189"><next><block type="drone_streamon" id="f0d*s$gWG~2itu;B#lex"><next><block type="drone_up" id="q#NKfiG8s[T.4=$QyHkp"><field name="arg_drone_up">50</field><next><block type="drone_cw" id="-htI.CK#@rLiB3+%I~%."><field name="arg_drone_cw">180</field><next><block type="drone_forward" id="XBY~$2ACPo%[z}F9GNZ]"><field name="arg_drone_forward">100</field><next><block type="drone_streamoff" id="iBtkyY.ru1G;hU)cM,jU"><next><block type="drone_land" id="l5i.cJ]ymx-kjiLVIycg"></block></next></block></next></block></next></block></next></block></next></block></next></block></xml>',
            'status' => 0,
            'created_id' => $test_user->id,
            'created_name' => $test_user->name,
        ]);

        // スクリーンショット取得
        $this->browse(function (Browser $browser) use ($post) {
            $browser->visit('/plugin/dronestudies/index/' . $this->test_frame->page_id . '/' . $this->test_frame->id . '/' . $post->id . '#frame-' . $this->test_frame->id)
                    ->screenshot('study/dronestudies/index/images/indexVideo');
        });

        $this->logout();

        // マニュアル用データ出力
        $dusk = Dusks::updateOrCreate([
            'plugin_name' => 'dronestudies',
            'method_name' => 'index'
        ], [
            'category' => 'study',
            'sort' => 6,
            'plugin_name' => 'dronestudies',
            'plugin_title' => 'DroneStudy',
            'plugin_desc' => 'ドローンでプログラミングを学べます。<br />生徒のChromeBookやiPadで作成して、教卓のパソコンで実行することもできます。<br />DroneStudyはConnect-CMSのオプションプラグインです。',
            'method_name' => 'index',
            'method_title' => '初期表示',
            'method_desc' => 'ドローンプログラミングを作る画面です。',
            'method_detail' => '離陸や着陸、前進、後進、回転、宙返りなど、ドローンを制御するための命令がブロックで並んでいます。マウスでプログラミングするだけで、ドローンを飛ばすことができ、楽しくプログラミングを学ぶことができます。',
            'html_path' => 'study/dronestudies/index/index.html',
            'img_args' => '[
                {"path": "study/dronestudies/index/images/index",
                 "name": "正三角形の例",
                 "comment": "<ul class=\"mb-0\"><li>文部科学省の小学校プログラミング教育の手引にも例示がある、正三角形を教材にして学ぶことができます。</li></ul>"
                },
                {"path": "study/dronestudies/index/images/index2",
                 "name": "保存、実行ボタン",
                 "comment": "<ul class=\"mb-0\"><li>作成したプログラムや作成途中のプログラムを保存できます。パソコンとドローンを接続しておくと、実行ボタンをクリックすれば、ドローンがプログラム通りに動きます。</li></ul>"
                },
                {"path": "study/dronestudies/index/images/indexVideo",
                 "name": "映像ON/OFFボタンを使用した例",
                 "comment": "<ul class=\"mb-0\"><li>ffmpeg を使用することで、ドローンのカメラから取得した映像をパソコンに表示することができます。</li></ul>"
                }
            ]',
            'level' => null,
            'test_result' => 'OK',
        ]);
    }
}
