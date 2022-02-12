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
use App\Models\User\Photoalbums\Photoalbum;
use App\Models\User\Photoalbums\PhotoalbumContent;

/**
 * フォトアルバムテスト
 *
 * @see https://github.com/opensource-workshop/connect-cms/wiki/Dusk#テスト実行 [How to test]
 */
class PhotoalbumsPluginTest extends DuskTestCase
{
    /**
     * フォトアルバムテスト
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
        // データクリア
        Photoalbum::truncate();
        PhotoalbumContent::truncate();
        $this->initPlugin('photoalbums', '/test/photoalbum');

        // 最初にマニュアルの順番確定用にメソッドを指定する。
        $this->reserveManual('index', 'makeFolder', 'upload', 'deleteContents', 'createBuckets', 'editView', 'listBuckets');
    }

    /**
     * インデックス
     */
    private function index()
    {
        // プラグインがなければ追加(テストするFrameとページのインスタンス変数への保持も)
        $this->addPluginFirst('photoalbums', '/test/photoalbum', 2);

        // 実行
        $this->browse(function (Browser $browser) {
            $browser->visit('/test/photoalbum')
                    ->assertPathBeginsWith('/')
                    ->screenshot('user/photoalbums/index/images/index1');

            $browser->visit('/plugin/photoalbums/changeDirectory/' . $this->test_frame->page_id . '/' . $this->test_frame->id . '/2#frame-' . $this->test_frame->id)
                    ->screenshot('user/photoalbums/index/images/index2');

            $browser->click('#photo_1')
                    ->pause(500)
                    ->screenshot('user/photoalbums/index/images/index3');

            $browser->visit('/plugin/photoalbums/changeDirectory/' . $this->test_frame->page_id . '/' . $this->test_frame->id . '/3#frame-' . $this->test_frame->id)
                    ->click('#a_embed_code_check10')
                    ->pause(1000)
                    ->screenshot('user/photoalbums/index/images/index4');
        });

        // マニュアル用データ出力
        $this->putManualData('[
            {"path": "user/photoalbums/index/images/index1",
             "name": "写真、動画の一覧",
             "comment": "<ul class=\"mb-0\"><li>アルバムを作成し、写真や動画を整理できます。</li><li>表示設定することで、アルバムごとのダウンロード機能を表示することもできます。</li></ul>"
            },
            {"path": "user/photoalbums/index/images/index2",
             "name": "写真の一覧",
             "comment": "<ul class=\"mb-0\"><li>写真はこのように一覧で表示されます。</li></ul>"
            },
            {"path": "user/photoalbums/index/images/index3",
             "name": "写真の拡大表示",
             "comment": "<ul class=\"mb-0\"><li>写真をクリックすると、このように拡大表示されます。</li></ul>"
            },
            {"path": "user/photoalbums/index/images/index4",
             "name": "動画の表示",
             "comment": "<ul class=\"mb-0\"><li>動画は再生、最大化再生などが可能です。表示設定で埋め込みコードを表示するにしている場合は、埋め込みコードも表示されます。</li></ul>"
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
            $browser->visit('/test/photoalbum')
                    ->press('アルバム作成')
                    ->pause(500)
                    ->type('folder_name[' . $this->test_frame->id . ']', '写真用アルバム')
                    ->screenshot('user/photoalbums/upload/images/makeFolder1')
                    ->press('#button_make_folder' . $this->test_frame->id);

            $browser->visit('/test/photoalbum')
                    ->press('アルバム作成')
                    ->pause(500)
                    ->type('folder_name[' . $this->test_frame->id . ']', '動画用アルバム')
                    ->press('#button_make_folder' . $this->test_frame->id)
                    ->screenshot('user/photoalbums/upload/images/makeFolder2');
        });

        // マニュアル用データ出力
        $this->putManualData('[
            {"path": "user/photoalbums/upload/images/makeFolder1",
             "name": "アルバムの作成画面"
            },
            {"path": "user/photoalbums/upload/images/makeFolder2",
             "name": "アルバムの作成結果",
             "comment": "<ul class=\"mb-0\"><li>ここでは、写真用アルバムと動画用アルバムとして作成しましたが、写真と動画を混在することもできます。</li></ul>"
            }
        ]');
    }

    /**
     * 画像アップロード
     */
    private function uploadOne(&$browser, ...$filenames)
    {
        foreach ($filenames as $filename) {
            $browser->visit('/plugin/photoalbums/changeDirectory/' . $this->test_frame->page_id . '/' . $this->test_frame->id . '/2#frame-' . $this->test_frame->id)
                    ->press('画像ファイル追加')
                    ->pause(500)
                    ->attach('upload_file[' . $this->test_frame->id . ']', __DIR__.'/photoalbum/'.$filename)
                    ->press('#button_upload' . $this->test_frame->id);
        }
    }

    /**
     * 動画アップロード
     */
    private function uploadMovieOne(&$browser, ...$base_filenames)
    {
        foreach ($base_filenames as $base_filename) {

            $browser->visit('/plugin/photoalbums/changeDirectory/' . $this->test_frame->page_id . '/' . $this->test_frame->id . '/3#frame-' . $this->test_frame->id)
                    ->press('動画ファイル追加')
                    ->pause(500)
                    ->attach('upload_video[' . $this->test_frame->id . ']', __DIR__.'/photoalbum/' . $base_filename . '.mp4')
                    ->attach('upload_poster[' . $this->test_frame->id . ']', __DIR__.'/photoalbum/' . $base_filename . '.png')
                    ->press('#button_upload_video' . $this->test_frame->id);
        }
    }

    /**
     * アップロード
     */
    private function upload()
    {
        // 実行
        $this->browse(function (Browser $browser) {
            $browser->visit('/plugin/photoalbums/changeDirectory/' . $this->test_frame->page_id . '/' . $this->test_frame->id . '/2#frame-' . $this->test_frame->id)
                    ->press('画像ファイル追加')
                    ->pause(500)
                    ->attach('upload_file[' . $this->test_frame->id . ']', __DIR__.'/photoalbum/Ariake_Arena.jpg')
                    ->click('#label_is_cover' . $this->test_frame->id)
                    ->pause(500)
                    ->screenshot('user/photoalbums/upload/images/upload1')
                    ->press('#button_upload' . $this->test_frame->id)
                    ->screenshot('user/photoalbums/upload/images/upload2');

            $this->uploadOne($browser, "Bonito_and_Myoga.jpg", "Bonito_and_Olive.jpg", "Bonito_and_Onion.jpg", "じと目あんず.jpg");

            $browser->visit('/plugin/photoalbums/changeDirectory/' . $this->test_frame->page_id . '/' . $this->test_frame->id . '/3#frame-' . $this->test_frame->id)
                    ->press('動画ファイル追加')
                    ->pause(500)
                    ->attach('upload_video[' . $this->test_frame->id . ']', __DIR__.'/photoalbum/あんず伏せ.mp4')
                    ->attach('upload_poster[' . $this->test_frame->id . ']', __DIR__.'/photoalbum/あんず伏せ.jpg')
                    ->click('#label_poster_is_cover' . $this->test_frame->id)
                    ->pause(500)
                    ->screenshot('user/photoalbums/upload/images/upload_video1')
                    ->press('#button_upload_video' . $this->test_frame->id)
                    ->screenshot('user/photoalbums/upload/images/upload_video2');

            $this->uploadMovieOne($browser, "たまごふわふわ1", "たまごふわふわ2");
        });

        // マニュアル用データ出力
        $this->putManualData('[
            {"path": "user/photoalbums/upload/images/upload1",
             "name": "写真のアップロード画面"
            },
            {"path": "user/photoalbums/upload/images/upload2",
             "name": "写真のアップロード後",
             "comment": "<ul class=\"mb-0\"><li>写真をアップロードできます。</li></ul>"
            },
            {"path": "user/photoalbums/upload/images/upload_video1",
             "name": "動画のアップロード後"
            },
            {"path": "user/photoalbums/upload/images/upload_video2",
             "name": "動画のアップロード後",
             "comment": "<ul class=\"mb-0\"><li>動画をアップロードできます。</li></ul>"
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
            $browser->visit('/test/photoalbum')
                    ->screenshot('user/photoalbums/delete/images/delete');
        });

        // マニュアル用データ出力
        $this->putManualData('[
            {"path": "user/photoalbums/delete/images/delete",
             "name": "ファイル削除",
             "comment": "<ul class=\"mb-0\"><li>ファイルやアルバムの横のチェックボックスをチェックして、削除することができます。</li></ul>"
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
            $browser->visit('/plugin/photoalbums/createBuckets/' . $this->test_frame->page_id . '/' . $this->test_frame->id . '#frame-' . $this->test_frame->id)
                    ->type('name', 'テストのフォトアルバム')
                    ->select('video_upload_max_size', '20480')
                    ->screenshot('user/photoalbums/createBuckets/images/createBuckets')
                    ->press('登録確定');

            // 一度、選択確定させる。
            $bucket = Buckets::where('plugin_name', 'photoalbums')->first();
            $browser->visit('/plugin/photoalbums/listBuckets/' . $this->test_frame->page_id . '/' . $this->test_frame->id . '#frame-' . $this->test_frame->id)
                    ->radio('select_bucket', $bucket->id)
                    ->screenshot('user/photoalbums/listBuckets/images/listBuckets')
                    ->press("表示フォトアルバム変更");

            // 変更
            $browser->visit("/plugin/photoalbums/editBuckets/" . $this->test_frame->page_id . '/' . $this->test_frame->id . '#frame-' . $this->test_frame->id)
                    ->screenshot('user/photoalbums/createBuckets/images/editBuckets');
        });

        // マニュアル用データ出力
        $this->putManualData('[
            {"path": "user/photoalbums/createBuckets/images/createBuckets",
             "name": "作成",
             "comment": "<ul class=\"mb-0\"><li>新しいフォトアルバムを作成できます。</li></ul>"
            },
            {"path": "user/photoalbums/createBuckets/images/editBuckets",
             "name": "変更・削除",
             "comment": "<ul class=\"mb-0\"><li>フォトアルバムを変更・削除できます。</li></ul>"
            }
        ]');
    }

    /**
     * 選択
     */
    private function listBuckets()
    {
        // 実行
        $this->browse(function (Browser $browser) {
            $browser->visit('/plugin/photoalbums/listBuckets/' . $this->test_frame->page_id . '/' . $this->test_frame->id . '#frame-' . $this->test_frame->id)
                    ->screenshot('user/photoalbums/listBuckets/images/listBuckets');
        });

        // マニュアル用データ出力
        $this->putManualData('[
            {"path": "user/photoalbums/listBuckets/images/listBuckets",
             "comment": "<ul class=\"mb-0\"><li>表示するフォトアルバムを変更できます。</li></ul>"
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
            $browser->visit('/plugin/photoalbums/editView/' . $this->test_frame->page_id . '/' . $this->test_frame->id . '#frame-' . $this->test_frame->id)
                    ->click('#label_embed_code_1')
                    ->pause(500)
                    ->screenshot('user/photoalbums/editView/images/editView')
                    ->press("変更確定");
        });

        // マニュアル用データ出力
        $this->putManualData('[
            {"path": "user/photoalbums/editView/images/editView",
             "comment": "<ul class=\"mb-0\"><li>フォトアルバムの表示設定を設定できます。</li></ul>"
            }
        ]');
    }
}
