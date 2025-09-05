<?php

namespace Tests\Browser\User;

use App\Models\Common\Buckets;
use App\Models\User\Photoalbums\Photoalbum;
use App\Models\User\Photoalbums\PhotoalbumContent;
use Laravel\Dusk\Browser;
use Tests\DuskTestCase;

/**
 * フォトアルバムテスト
 *
 * @see https://github.com/opensource-workshop/connect-cms/wiki/Dusk#テスト実行 [How to test]
 */
class PhotoalbumsPluginTest extends DuskTestCase
{
    /** @var int|null 写真用アルバムのフォルダID */
    private $photoFolderId = null;
    /** @var int|null 動画用アルバムのフォルダID */
    private $movieFolderId = null;
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
        $this->template(); // テンプレート
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
        $this->reserveManual('index', 'makeFolder', 'upload', 'deleteContents', 'template', 'createBuckets', 'editView', 'listBuckets');
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

            $targetFolder = $this->photoFolderId ?? 2;
            $browser->visit('/plugin/photoalbums/changeDirectory/' . $this->test_frame->page_id . '/' . $this->test_frame->id . '/' . $targetFolder . '#frame-' . $this->test_frame->id)
                    ->waitFor('[id^="photo_"]')
                    ->screenshot('user/photoalbums/index/images/index2');

            $browser->click('[id^="photo_"]')
                    ->pause(500)
                    ->screenshot('user/photoalbums/index/images/index3');

            $targetMovieFolder = $this->movieFolderId ?? 3;
            $browser->visit('/plugin/photoalbums/changeDirectory/' . $this->test_frame->page_id . '/' . $this->test_frame->id . '/' . $targetMovieFolder . '#frame-' . $this->test_frame->id)
                    ->waitFor('#a_embed_code_check10')
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
        ]', null, 4, 'basic');
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
                    ->screenshot('user/photoalbums/makeFolder/images/makeFolder1')
                    ->press('#button_make_folder' . $this->test_frame->id);

            $browser->visit('/test/photoalbum')
                    ->press('アルバム作成')
                    ->pause(500)
                    ->type('folder_name[' . $this->test_frame->id . ']', '動画用アルバム')
                    ->press('#button_make_folder' . $this->test_frame->id)
                    ->screenshot('user/photoalbums/makeFolder/images/makeFolder2');
        });

        // 作成したフォルダのIDを取得
        $bucket = Buckets::where('plugin_name', 'photoalbums')->orderBy('id', 'desc')->first();
        if ($bucket) {
            $album = Photoalbum::where('bucket_id', $bucket->id)->first();
            if ($album) {
                $photoFolder = PhotoalbumContent::where('photoalbum_id', $album->id)
                    ->where('is_folder', PhotoalbumContent::is_folder_on)
                    ->where('name', '写真用アルバム')
                    ->orderBy('id', 'desc')->first();
                $movieFolder = PhotoalbumContent::where('photoalbum_id', $album->id)
                    ->where('is_folder', PhotoalbumContent::is_folder_on)
                    ->where('name', '動画用アルバム')
                    ->orderBy('id', 'desc')->first();
                $this->photoFolderId = optional($photoFolder)->id;
                $this->movieFolderId = optional($movieFolder)->id;
            }
        }

        // マニュアル用データ出力
        $this->putManualData('[
            {"path": "user/photoalbums/makeFolder/images/makeFolder1",
             "name": "アルバムの作成画面"
            },
            {"path": "user/photoalbums/makeFolder/images/makeFolder2",
             "name": "アルバムの作成結果",
             "comment": "<ul class=\"mb-0\"><li>ここでは、写真用アルバムと動画用アルバムとして作成しましたが、写真と動画を混在することもできます。</li></ul>"
            }
        ]', null, 4, 'basic');
    }

    /**
     * 画像アップロード
     */
    private function uploadOne(&$browser, ...$filenames)
    {
        foreach ($filenames as $filename) {
            $targetFolder = $this->photoFolderId ?? 2;
            $browser->visit('/plugin/photoalbums/changeDirectory/' . $this->test_frame->page_id . '/' . $this->test_frame->id . '/' . $targetFolder . '#frame-' . $this->test_frame->id)
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
            $targetMovieFolder = $this->movieFolderId ?? 3;
            $browser->visit('/plugin/photoalbums/changeDirectory/' . $this->test_frame->page_id . '/' . $this->test_frame->id . '/' . $targetMovieFolder . '#frame-' . $this->test_frame->id)
                    ->press('動画ファイル追加')
                    ->pause(500)
                    ->attach('upload_video[' . $this->test_frame->id . ']', __DIR__.'/photoalbum/' . $base_filename . '.mp4')
                    ->attach('upload_poster[' . $this->test_frame->id . ']', __DIR__.'/photoalbum/' . $base_filename . '.png')
                    ->screenshot("user/photoalbums/upload/images/upload_video_{$base_filename}_1")
                    ->press('#button_upload_video' . $this->test_frame->id)
                    ->screenshot("user/photoalbums/upload/images/upload_video_{$base_filename}_2");
        }
    }

    /**
     * アップロード
     */
    private function upload()
    {
        // 実行
        $this->browse(function (Browser $browser) {
            $targetFolder = $this->photoFolderId ?? 2;
            $browser->visit('/plugin/photoalbums/changeDirectory/' . $this->test_frame->page_id . '/' . $this->test_frame->id . '/' . $targetFolder . '#frame-' . $this->test_frame->id)
                    ->press('画像ファイル追加')
                    ->pause(500)
                    ->attach('upload_file[' . $this->test_frame->id . ']', __DIR__.'/photoalbum/Ariake_Arena.jpg')
                    ->click('#label_is_cover' . $this->test_frame->id)
                    ->pause(500)
                    ->screenshot('user/photoalbums/upload/images/upload1')
                    ->press('#button_upload' . $this->test_frame->id)
                    ->screenshot('user/photoalbums/upload/images/upload2');

            $this->uploadOne($browser, "Bonito_and_Myoga.jpg", "Bonito_and_Olive.jpg", "Bonito_and_Onion.jpg", "じと目あんず.jpg");

            $targetMovieFolder = $this->movieFolderId ?? 3;
            $browser->visit('/plugin/photoalbums/changeDirectory/' . $this->test_frame->page_id . '/' . $this->test_frame->id . '/' . $targetMovieFolder . '#frame-' . $this->test_frame->id)
                    ->press('動画ファイル追加')
                    ->pause(500)
                    ->attach('upload_video[' . $this->test_frame->id . ']', __DIR__.'/photoalbum/あんず伏せ.mp4')
                    ->attach('upload_poster[' . $this->test_frame->id . ']', __DIR__.'/photoalbum/あんず伏せ.jpg')
                    ->click('#label_poster_is_cover' . $this->test_frame->id)
                    ->pause(500)
                    ->screenshot('user/photoalbums/upload/images/upload_video1_1')
                    ->press('#button_upload_video' . $this->test_frame->id)
                    ->screenshot('user/photoalbums/upload/images/upload_video1_2');

            $this->uploadMovieOne($browser, "たまごふわふわ1", "たまごふわふわ2");
        });

        // マニュアル用データ出力
        $this->putManualData('[
            {"path": "user/photoalbums/upload/images/upload1",
             "name": "写真のアップロード画面",
             "comment": "<ul class=\"mb-0\"><li>写真をアップロードできます。</li><li>アップロードできるファイルの拡張子はjpg, png, gif, zipです。</li><li>zipの場合は、zipファイル内のフォルダ構成が、アルバム、サブアルバムとして再現されます。すでに存在するアルバム名がある場合は存在するアルバム内に写真が格納されます。</li></ul>"
            },
            {"path": "user/photoalbums/upload/images/upload2",
             "name": "写真のアップロード後"
            },
            {"path": "user/photoalbums/upload/images/upload_video1_1",
             "name": "動画のアップロード後"
            },
            {"path": "user/photoalbums/upload/images/upload_video1_2",
             "name": "動画のアップロード後",
             "comment": "<ul class=\"mb-0\"><li>動画をアップロードできます。</li></ul>"
            }
        ]', null, 4, 'basic');
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
        ]', null, 4, 'basic');
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
                    ->select('video_upload_max_size', '51200')
                    ->screenshot('user/photoalbums/createBuckets/images/createBuckets')
                    ->press('登録確定');

            // 画面表示がおいつかない場合があるので、ちょっと待つ
            $browser->pause(500);

            // 一度、選択確定させる。
            $bucket = Buckets::where('plugin_name', 'photoalbums')->first();
            $browser->visit('/plugin/photoalbums/listBuckets/' . $this->test_frame->page_id . '/' . $this->test_frame->id . '#frame-' . $this->test_frame->id)
                    ->radio('select_bucket', $bucket->id)
                    ->screenshot('user/photoalbums/createBuckets/images/listBuckets')
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
        ]', null, 4, 'basic');
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
        ]', null, 4);
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
        ]', null, 4);
    }

    /**
     * テンプレート
     */
    private function template()
    {
        $this->putManualTemplateData($this->test_frame, 'user', '/test/photoalbum', ['photoalbums', 'フォトアルバム'], ['default' => 'アルバムはリスト表示', 'card' => 'アルバムもカード表示']);
    }
}
