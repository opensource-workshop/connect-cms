<?php

namespace Tests\Browser\User;

use Illuminate\Foundation\Testing\DatabaseMigrations;
use Laravel\Dusk\Browser;
use Tests\DuskTestCase;

use App\Enums\PluginName;
use App\Models\Common\Frame;
use App\Models\Common\Uploads;
use App\Models\Core\Dusks;
use App\Models\User\Contents\Contents;

class ContentsPluginTest extends DuskTestCase
{
    /**
     * フレーム
     */
    private $frame = null;

    /**
     * 固定記事
     */
    private $content = null;

    /**
     * テストする関数の制御
     *
     * @group manage
     * @see https://readouble.com/laravel/6.x/ja/dusk.html#running-tests
     */
    public function testInvoke()
    {
        $this->index();
        $this->login(1); // user id = 1(admin)でログイン
        $this->edit();
        $this->show();
        $this->listBuckets();
        $this->editFrame();
    }

    /**
     * インデックス
     */
    private function index()
    {
        // マニュアル用にデータ作成
        // ロゴがなければ、uploads を1行作成する。
        $upload_check = Uploads::where("client_original_name", "blobid0000000000001.png")->first();

        $upload = Uploads::firstOrCreate(
            ["client_original_name" => "blobid0000000000001.png"],
            [
                "client_original_name" => "blobid0000000000001.png",
                "mimetype" => "image/jpeg",
                "extension" => "jpg",
                "size" => 34008,
                "plugin_name" => "contents",
                "download_count" => 0,
                "page_id" => 1,
                "private" => 0,
                "temporary_flag" => 0
            ]
        );

        // 実ファイルコピー（$upload_check がnull だった場合に、uploads レコードをcreate しているので、ファイルもコピー。）
        if (empty($upload_check)) {
            \Storage::put($this->getDirectory($upload->id) . '/' . $upload->id . ".jpg", \Storage::disk('manual')->get('copy_data/image/blobid0000000000001.png'));
        }

        // ヘッダーに作ってあった固定記事を取得
        $this->frame = Frame::where('area_id', 0)->where('plugin_name', 'contents')->first();
        $this->content = Contents::where('bucket_id', $this->frame->bucket_id)->where('status', 0)->first();
        $this->content->content_text =<<< EOF
<div class="d-flex flex-row">
    <div class="p-2"><a href="/"><img src="/file/{$upload->id}" width="300" height="68" class="img-fluid" /></a></div>
    <div class="p-2">テストサイトです。</div>
</div>
EOF;
        $this->content->save();

        // 実行
        $this->browse(function (Browser $browser) {
            $browser->visit('/')
                    ->assertPathBeginsWith('/')
                    ->screenshot('user/contents/index/images/index');
        });

        // マニュアル用データ出力
        $dusk = Dusks::putManualData(
            ['html_path' => 'user/contents/index/index.html'],
            ['category' => 'user',
             'sort' => 2,
             'plugin_name' => 'contents',
             'plugin_title' => '固定記事',
             'plugin_desc' => 'サイト上に文字や画像を配置できるプラグインです。',
             'method_name' => 'index',
             'method_title' => '表示',
             'method_desc' => 'サイト上に記載した文字や画像を表示できる基本となるプラグインです。',
             'method_detail' => '',
             'html_path' => 'user/contents/index/index.html',
             'img_args' => '[
                 {"path": "user/contents/index/images/index",
                  "name": "固定記事",
                  "comment": "<ul class=\"mb-0\"><li>WYSIWYG の各機能は共通機能で説明します。</li></ul>"
                 }]',
             'test_result' => 'OK']
        );
    }

    /**
     * 編集
     */
    private function edit()
    {
        $this->browse(function (Browser $browser) {
            $browser->visit('/plugin/contents/edit/' . $this->frame->page_id . '/' . $this->frame->id . '/' . $this->content->id)
                    ->assertPathBeginsWith('/')
                    ->pause(500)
                    ->screenshot('user/contents/edit/images/edit');
        });

        // マニュアル用データ出力
        $this->putManualData('user/contents/edit/images/edit');
    }

    /**
     * 削除
     */
    private function show()
    {
        $this->browse(function (Browser $browser) {
            $browser->visit('/plugin/contents/show/' . $this->frame->page_id . '/' . $this->frame->id)
                    ->assertPathBeginsWith('/')
                    ->screenshot('user/contents/show/images/show');
        });

        // マニュアル用データ出力
        $this->putManualData('user/contents/show/images/show');
    }

    /**
     * 表示コンテンツ選択
     */
    private function listBuckets()
    {
        $this->browse(function (Browser $browser) {
            $browser->visit('/plugin/contents/listBuckets/' . $this->frame->page_id . '/' . $this->frame->id)
                    ->assertPathBeginsWith('/')
                    ->screenshot('user/contents/listBuckets/images/listBuckets');
        });

        // マニュアル用データ出力
        $this->putManualData('user/contents/listBuckets/images/listBuckets');
    }

    /**
     * フレームを編集する
     */
    private function editFrame()
    {
        // この後のマニュアルデータで、ヘッダーの固定記事のフレーム枠などが邪魔なので、none にするなど。
        $this->frame->frame_title = null;
        $this->frame->frame_design = 'none';
        $this->frame->save();
    }
}
