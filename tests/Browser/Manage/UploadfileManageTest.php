<?php

namespace Tests\Browser\Manage;

use Illuminate\Foundation\Testing\DatabaseMigrations;
use Laravel\Dusk\Browser;
use Tests\DuskTestCase;

use App\Models\Common\Uploads;
use App\Traits\ConnectCommonTrait;

class UploadfileManageTest extends DuskTestCase
{
    use ConnectCommonTrait;

    /**
     * テストする関数の制御
     *
     * @group manage
     * @see https://readouble.com/laravel/6.x/ja/dusk.html#running-tests
     */
    public function testInvoke()
    {
        $this->login(1); // user id = 1(admin)でログイン
        $this->index();
        $this->edit();
        $this->userdir();
    }

    /**
     * アップロードファイル一覧
     */
    private function index()
    {
        // uploads を1行作成する。
        $upload = $this->firstOrCreateFileUpload('manual', 'copy_data/image/blobid0000000000001.png', 'blobid0000000000001.png', 'image/png', 'png', 'contents', 1);

        // 実行
        $this->browse(function (Browser $browser) {
            $browser->visit('/manage/uploadfile')
                    ->assertTitle('Connect-CMS')
                    ->screenshot('manage/uploadfile/index/images/index');
        });

        // マニュアル用データ出力
        $this->putManualData('[
            {"path": "manage/uploadfile/index/images/index",
             "name": "アップロードファイル一覧",
             "comment": "<ul class=\"mb-0\"><li>アップロードファイルを一覧表示できます。</li></ul>"
            }
        ]');
    }

    /**
     * アップロードファイル編集
     */
    private function edit()
    {
        // 実行
        $this->browse(function (Browser $browser) {
            $browser->visit('/manage/uploadfile')
                    ->click('#edit_1')
                    ->assertTitleContains('Connect-CMS')
                    ->screenshot('manage/uploadfile/edit/images/edit');
        });

        // マニュアル用データ出力
        $this->putManualData('[
            {"path": "manage/uploadfile/edit/images/edit",
             "name": "アップロードファイル編集",
             "comment": "<ul class=\"mb-0\"><li>ファイル名の変更が可能です。</li></ul>"
            }
        ]');
    }

    /**
     * ユーザファイル
     */
    private function userdir()
    {
        // 実行
        $this->browse(function (Browser $browser) {
            $browser->visit('/manage/uploadfile/userdir')
                    ->assertTitle('Connect-CMS')
                    ->screenshot('manage/uploadfile/userdir/images/userdir');
        });

        // マニュアル用データ出力
        $this->putManualData('[
            {"path": "manage/uploadfile/userdir/images/userdir",
             "name": "ユーザディレクトリ一覧",
             "comment": "<ul class=\"mb-0\"><li>サーバ上のファイルを操作できる環境用の設定です。</li><li>storage/user 配下のディレクトリをConnect-CMS で閲覧制御できます。</li><li>ファイル参照時は /file/user/ディレクトリ名/ファイル名 となります。</li></ul>"
            }
        ]');
    }
}
