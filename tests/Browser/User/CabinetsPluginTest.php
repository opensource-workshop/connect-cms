<?php

namespace Tests\Browser\User;

use Illuminate\Foundation\Testing\DatabaseMigrations;
use Laravel\Dusk\Browser;
use Tests\DuskTestCase;

use App\Enums\PluginName;
use App\Models\Common\Buckets;
use App\Models\Common\Frame;
use App\Models\Common\Uploads;
use App\Models\Core\Dusks;
use App\Models\User\Cabinets\Cabinet;
use App\Models\User\Cabinets\CabinetContent;

/**
 * キャビネットテスト
 *
 * @see https://github.com/opensource-workshop/connect-cms/wiki/Dusk#テスト実行 [How to test]
 */
class CabinetsPluginTest extends DuskTestCase
{
    /**
     * キャビネットテスト
     *
     * @group user
     * @see https://readouble.com/laravel/6.x/ja/dusk.html#running-tests
     */
    public function test()
    {
        $this->init();
        $this->login(1);

        $this->createBuckets();
        $this->editView();
        $this->listBuckets();

        $this->makeFolder();
        $this->upload();
        $this->deleteContents();

        $this->logout();
        $this->index();
    }

    /**
     * 初期処理
     */
    private function init()
    {
        // 最初にマニュアルの順番確定用にメソッドを指定する。
        $this->reserveManual('index', 'makeFolder', 'upload', 'deleteContents', 'createBuckets', 'editView', 'listBuckets');

        // データクリア
        Cabinet::truncate();
        CabinetContent::truncate();
        $this->initPlugin('cabinets', '/test/cabinet');
    }

    /**
     * インデックス
     */
    private function index()
    {
        // 実行
        $this->browse(function (Browser $browser) {
            $browser->visit('/test/cabinet')
                    ->screenshot('user/cabinets/index/images/index');
        });

        // マニュアル用データ出力
        $this->putManualData('[
            {"path": "user/cabinets/index/images/index",
             "name": "フォルダ、ファイルの一覧",
             "comment": "<ul class=\"mb-0\"><li>ファイルをクリックすることで、ファイルをダウンロードすることができます。</li><li>ファイルやフォルダのチェックボックスをクリックすると、圧縮して一括でダウンロードできます。</li></ul>"
            }
        ]');
    }

    /**
     * フォルダ作成
     */
    private function makeFolder()
    {
        // 実行
        $this->browse(function (Browser $browser) {
            $browser->visit('/test/cabinet')
                    ->press('フォルダ作成')
                    ->pause(500)
                    ->type('folder_name[' . $this->test_frame->id . ']', 'テストのフォルダ')
                    ->screenshot('user/cabinets/upload/images/makeFolder1')
                    ->press('#button_make_folder' . $this->test_frame->id)
                    ->screenshot('user/cabinets/upload/images/makeFolder2');
        });

        // マニュアル用データ出力
        $this->putManualData('[
            {"path": "user/cabinets/upload/images/makeFolder1",
             "name": "フォルダの作成画面"
            },
            {"path": "user/cabinets/upload/images/makeFolder1",
             "name": "フォルダの作成結果",
             "comment": "<ul class=\"mb-0\"><li>フォルダを作成できます。</li></ul>"
            }
        ]');
    }

    /**
     * アップロード
     */
    private function upload()
    {
        // 実行
        $this->browse(function (Browser $browser) {
            $browser->visit('/test/cabinet')
                    ->press('ファイル追加')
                    ->pause(500)
                    ->attach('upload_file[' . $this->test_frame->id . ']', __DIR__.'/cabinet/ドキュメント.pdf')
                    ->screenshot('user/cabinets/upload/images/upload1')
                    ->press('#button_upload_file' . $this->test_frame->id)
                    ->screenshot('user/cabinets/upload/images/upload2');
        });

        // マニュアル用データ出力
        $this->putManualData('[
            {"path": "user/cabinets/upload/images/upload1",
             "name": "ファイルのアップロード画面"
            },
            {"path": "user/cabinets/upload/images/upload2",
             "name": "ファイルのアップロード後",
             "comment": "<ul class=\"mb-0\"><li>ファイルをアップロードできます。</li></ul>"
            }
        ]');
    }

    /**
     * 削除
     */
    private function deleteContents()
    {
        // 実行
        $this->browse(function (Browser $browser) {
            $browser->visit('/test/cabinet')
                    ->screenshot('user/cabinets/delete/images/delete');
        });

        // マニュアル用データ出力
        $this->putManualData('[
            {"path": "user/cabinets/delete/images/delete",
             "name": "ファイル削除",
             "comment": "<ul class=\"mb-0\"><li>ファイルやフォルダの横のチェックボックスをチェックして、削除することができます。</li></ul>"
            }
        ]');
    }

    /**
     * バケツ作成
     */
    private function createBuckets()
    {
        // 実行
        $this->browse(function (Browser $browser) {
            $browser->visit('/plugin/cabinets/createBuckets/' . $this->test_frame->page_id . '/' . $this->test_frame->id . '#frame-' . $this->test_frame->id)
                    ->type('name', 'テストのキャビネット')
                    ->screenshot('user/cabinets/createBuckets/images/createBuckets')
                    ->press('登録確定');

            // 一度、選択確定させる。
            $bucket = Buckets::where('plugin_name', 'cabinets')->first();
            $browser->visit('/plugin/cabinets/listBuckets/' . $this->test_frame->page_id . '/' . $this->test_frame->id . '#frame-' . $this->test_frame->id)
                    ->radio('select_bucket', $bucket->id)
                    ->screenshot('user/cabinets/listBuckets/images/listBuckets')
                    ->press("表示キャビネット変更");

            // 変更
            $browser->visit("/plugin/cabinets/editBuckets/" . $this->test_frame->page_id . '/' . $this->test_frame->id . '#frame-' . $this->test_frame->id)
                    ->screenshot('user/cabinets/createBuckets/images/editBuckets');
        });

        // マニュアル用データ出力
        $this->putManualData('[
            {"path": "user/cabinets/createBuckets/images/createBuckets",
             "name": "作成",
             "comment": "<ul class=\"mb-0\"><li>新しいキャビネットを作成できます。</li></ul>"
            },
            {"path": "user/cabinets/createBuckets/images/editBuckets",
             "name": "変更・削除",
             "comment": "<ul class=\"mb-0\"><li>キャビネットを変更・削除できます。</li></ul>"
            }
        ]');
    }

    /**
     * ブログ選択
     */
    private function listBuckets()
    {
        // 実行
        $this->browse(function (Browser $browser) {
            $browser->visit('/plugin/cabinets/listBuckets/' . $this->test_frame->page_id . '/' . $this->test_frame->id . '#frame-' . $this->test_frame->id)
                    ->screenshot('user/cabinets/listBuckets/images/listBuckets');
        });

        // マニュアル用データ出力
        $this->putManualData('[
            {"path": "user/cabinets/listBuckets/images/listBuckets",
             "comment": "<ul class=\"mb-0\"><li>表示するキャビネットを変更できます。</li></ul>"
            }
        ]');
    }

    /**
     * フレーム表示設定
     */
    private function editView()
    {
        // 実行
        $this->browse(function (Browser $browser) {
            $browser->visit('/plugin/cabinets/editView/' . $this->test_frame->page_id . '/' . $this->test_frame->id . '#frame-' . $this->test_frame->id)
                    ->screenshot('user/cabinets/editView/images/editView');
        });

        // マニュアル用データ出力
        $this->putManualData('[
            {"path": "user/cabinets/editView/images/editView",
             "comment": "<ul class=\"mb-0\"><li>キャビネットのファイルの並び順を設定できます。</li></ul>"
            }
        ]');
    }
}
