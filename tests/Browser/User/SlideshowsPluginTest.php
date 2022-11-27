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
use App\Models\User\Slideshows\Slideshows;
use App\Models\User\Slideshows\SlideshowsItems;

/**
 * スライドショーテスト
 *
 * @see https://github.com/opensource-workshop/connect-cms/wiki/Dusk#テスト実行 [How to test]
 */
class SlideshowsPluginTest extends DuskTestCase
{
    /**
     * スライドショーテスト
     *
     * @group user
     * @see https://readouble.com/laravel/6.x/ja/dusk.html#running-tests
     */
    public function test()
    {
        $this->init();
        $this->login(1);

        $this->createBuckets();
        $this->listBuckets();

        $this->editItem();
        if (! $this->no_api_test) {
            $this->editItemPdf();
        }

        $this->logout();
        $this->index();   // 記事一覧
    }

    /**
     * 初期処理
     */
    private function init()
    {
        // データクリア
        Slideshows::truncate();
        SlideshowsItems::truncate();
        $this->initPlugin('slideshows', '/test/slideshow');

        // 最初にマニュアルの順番確定用にメソッドを指定する。
        $this->reserveManual('index', 'editItem', 'editItemPdf', 'createBuckets', 'listBuckets');
    }

    /**
     * インデックス
     */
    private function index()
    {
        // 実行
        $this->browse(function (Browser $browser) {
            $browser->visit('/test/slideshow')
                    ->assertPathBeginsWith('/')
                    ->pause(500)
                    ->screenshot('user/slideshows/index/images/index2');

            // フレームの順番を入れ替える。
            $this->frame1->display_sequence = 1;
            $this->frame1->save();
            $this->frame2->display_sequence = 2;
            $this->frame2->save();

            $browser->visit('/');
            $browser->visit('/test/slideshow')
                    ->assertPathBeginsWith('/')
                    ->pause(500)
                    ->screenshot('user/slideshows/index/images/index');
        });

        // マニュアル用データ出力
        $this->putManualData('[
            {"path": "user/slideshows/index/images/index",
             "name": "スライドショー",
             "comment": "<ul class=\"mb-0\"><li>画像は画面の幅に合わせて自動で大きさが変化します。（レスポンシブします。）</li></ul>"
            },
            {"path": "user/slideshows/index/images/index2",
             "name": "スライドショー（縦長画像）",
             "comment": "<ul class=\"mb-0\"><li>縦長の画像でもスライドショーが作成できます。</li></ul>"
            }
        ]', null, 4, 'basic');
    }

    /**
     * 画像登録
     */
    private function editItem($title = null)
    {
        // フレームの一時保存
        $this->frame1 = $this->test_frame;

        // 実行
        $this->browse(function (Browser $browser) {
            $browser->visit('plugin/slideshows/editItem/' . $this->test_frame->page_id . '/' . $this->test_frame->id . '#frame-' . $this->test_frame->id)
                    ->attach('image_file', __DIR__.'/slideshow/Connect-CMS.png')
                    ->type('link_url', 'https://connect-cms.jp/')
                    ->type('caption', 'Connect-CMS公式サイト')
                    ->type('link_target', '_blank')
                    ->assertPathBeginsWith('/')
                    ->screenshot('user/slideshows/editItem/images/editItem1');

            $browser->press('追加')
                    ->assertPathBeginsWith('/')
                    ->screenshot('user/slideshows/editItem/images/editItem2');

            $browser->visit('plugin/slideshows/editItem/' . $this->test_frame->page_id . '/' . $this->test_frame->id . '#frame-' . $this->test_frame->id)
                    ->attach('image_file', __DIR__.'/slideshow/NC2toConnect-CMS.png')
                    ->press('追加')
                    ->assertPathBeginsWith('/');

            $browser->visit('plugin/slideshows/editItem/' . $this->test_frame->page_id . '/' . $this->test_frame->id . '#frame-' . $this->test_frame->id)
                    ->attach('image_file', __DIR__.'/slideshow/researchmap.png')
                    ->press('追加')
                    ->assertPathBeginsWith('/')
                    ->screenshot('user/slideshows/editItem/images/editItem3');
        });

        // マニュアル用データ出力(記事の登録はしていなくても、画像データはできているはず。reserveManual() で一旦、内容がクリアされているので、画像の登録は行う)
        $this->putManualData('[
            {"path": "user/slideshows/editItem/images/editItem1",
             "name": "画像選択",
             "comment": "<ul class=\"mb-0\"><li>画像選択ボタンから画像のアップロードを行い、追加ボタンを押すことで登録されます。</li><li>登録された画像群は画面下部に一覧表示されます。</li><li>スライドショー項目を削除してもアップロードした画像は削除しません。アップロードファイルの削除は「管理者メニュー＞アップロードファイル」より行えるようにする予定です。</li></ul>"
            },
            {"path": "user/slideshows/editItem/images/editItem2",
             "name": "画像追加後",
             "comment": "<ul class=\"mb-0\"><li>画像を追加すると、「既存の設定行」に追加されます。</li></ul>"
            },
            {"path": "user/slideshows/editItem/images/editItem3",
             "name": "複数の画像追加",
             "comment": "<ul class=\"mb-0\"><li>複数の画像を追加できます。順番の変更やチェックを外すことで、一時的に非表示にすることもできます。</li></ul>"
            }
        ]', null, 4, 'basic');
    }

    /**
     * PDFから画像登録
     */
    private function editItemPdf($title = null)
    {
        // 2つ目のスライドショーを追加
        $this->addPlugin('slideshows', '/test/slideshow', 2, false);

        // フレームの一時保存
        $this->frame2 = $this->test_frame;

        // 実行
        $this->browse(function (Browser $browser) {
            $browser->visit('/plugin/slideshows/createBuckets/' . $this->test_frame->page_id . '/' . $this->test_frame->id . '#frame-' . $this->test_frame->id)
                    ->assertPathBeginsWith('/')
                    ->type('slideshows_name', 'テストのスライドショー2')
                    ->click('#label_control_display_flag_1')
                    ->click('#label_indicators_display_flag_1')
                    ->click('#label_fade_use_flag_1')
                    ->pause(500)
                    ->press('登録確定');

            // 一度、選択確定させる。
            $bucket = Buckets::where('plugin_name', 'slideshows')->orderBy('id', 'desc')->first();
            $browser->visit('/plugin/slideshows/listBuckets/' . $this->test_frame->page_id . '/' . $this->test_frame->id . '#frame-' . $this->test_frame->id)
                    ->radio('select_bucket', $bucket->id)
                    ->assertPathBeginsWith('/')
                    ->press("変更確定");

            $browser->visit('plugin/slideshows/editItem/' . $this->test_frame->page_id . '/' . $this->test_frame->id . '#frame-' . $this->test_frame->id)
                    ->attach('pdf_file', __DIR__.'/slideshow/PDF2Image.pdf')
                    ->assertPathBeginsWith('/')
                    ->screenshot('user/slideshows/editItemPdf/images/editItemPdf1');

            $browser->press('@submit_add_pdf')
                    ->assertPathBeginsWith('/')
                    ->screenshot('user/slideshows/editItemPdf/images/editItemPdf2')
                    ->visit('test/slideshow')
                    ->pause(1000)
                    ->screenshot('user/slideshows/editItemPdf/images/editItemPdf3');

            // フレームを半分に。
            $this->test_frame->frame_col = 6;
            $this->test_frame->save();

            $browser->visit('test/slideshow')
                    ->pause(500)
                    ->screenshot('user/slideshows/editItemPdf/images/editItemPdf4')
                    ->click('.frame-' . $this->test_frame->id .  ' .carousel-control-next')
                    ->pause(500)
                    ->screenshot('user/slideshows/editItemPdf/images/editItemPdf5');
        });

        // マニュアル用データ出力（フレームとバケツ）
        // PDFから画像登録専用のメソッドはないが、機能を分けて説明したいため、仮想のメソッドでマニュアルを作成する。
        $dusk_index = Dusks::where('plugin_name', 'slideshows')->where('method_name', 'index')->first();
        $dusk = Dusks::updateOrCreate(
            ['plugin_name' => 'slideshows', 'method_name' => 'editItemPdf'],
            ['category' => 'user',
             'sort' => 4,
             'plugin_name' => 'slideshows',
             'plugin_title' => 'スライドショー',
             'plugin_desc' => '画像をアップロードして自動スライド形式で表示することができます。',
             'method_name' => 'editItemPdf',
             'method_title' => 'PDF画像変換登録',
             'method_desc' => 'PDFをアップロードして、各ページを1つの画像に自動変換して登録します。<br />この機能は、外部サービスの「PDFアップロード」が設定されている場合のみ使用できます。',
             'method_detail' => '学校だよりをそのままスライドショーにしたい場合や、複数枚のパンフレットをデジタルサイネージとして表示したい場合に便利です。',
             'html_path' => 'user/slideshows/editItemPdf/index.html',
             'img_args' => '[
                 {"path": "user/slideshows/editItemPdf/images/editItemPdf1", "name": "PDFファイルの選択", "comment": "<ul class=\"mb-0\"><li>PDFファイルを選択します。</li></ul>"},
                 {"path": "user/slideshows/editItemPdf/images/editItemPdf2", "name": "PDFファイルの追加", "comment": "<ul class=\"mb-0\"><li>PDFファイルを追加することで、各ページが画像に変換され、登録されます。</li></ul>"},
                 {"path": "user/slideshows/editItemPdf/images/editItemPdf4", "name": "スライドショーの表示", "comment": "<ul class=\"mb-0\"><li>PDFの1枚目が表示されています。</li></ul>"},
                 {"path": "user/slideshows/editItemPdf/images/editItemPdf5", "name": "スライドショーの表示", "comment": "<ul class=\"mb-0\"><li>PDFの2枚目が表示されています。</li></ul>"}
             ]',
             'level' => null,
             'test_result' => 'OK',
             'parent_id' => $dusk_index->id]
        );

        // マニュアル用データ出力
/*
        $this->putManualData('[
            {"path": "user/slideshows/editItemPdf/images/editItemPdf1",
             "name": "PDFから画像登録",
             "comment": "<ul class=\"mb-0\"><li>あああああああああああ</li></ul>"
            },
            {"path": "user/slideshows/editItemPdf/images/editItemPdf2"},
            {"path": "user/slideshows/editItemPdf/images/editItemPdf3"}
        ]', null, 4, 'basic');
*/
    }

    /**
     * バケツ作成
     */
    private function createBuckets()
    {
        // 実行
        $this->browse(function (Browser $browser) {
            $browser->visit('/plugin/slideshows/createBuckets/' . $this->test_frame->page_id . '/' . $this->test_frame->id . '#frame-' . $this->test_frame->id)
                    ->assertPathBeginsWith('/')
                    ->type('slideshows_name', 'テストのスライドショー')
                    ->click('#label_control_display_flag_1')
                    ->click('#label_indicators_display_flag_1')
                    ->click('#label_fade_use_flag_1')
                    ->pause(500)
                    ->screenshot('user/slideshows/createBuckets/images/createBuckets')
                    ->press('登録確定');

            // 一度、選択確定させる。
            $bucket = Buckets::where('plugin_name', 'slideshows')->first();
            $browser->visit('/plugin/slideshows/listBuckets/' . $this->test_frame->page_id . '/' . $this->test_frame->id . '#frame-' . $this->test_frame->id)
                    ->radio('select_bucket', $bucket->id)
                    ->assertPathBeginsWith('/')
                    ->screenshot('user/slideshows/listBuckets/images/listBuckets')
                    ->press("変更確定");

            // 変更
            $browser->visit("/plugin/slideshows/editBuckets/" . $this->test_frame->page_id . '/' . $this->test_frame->id . '#frame-' . $this->test_frame->id)
                    ->assertPathBeginsWith('/')
                    ->screenshot('user/slideshows/createBuckets/images/editBuckets');
        });

        // マニュアル用データ出力
        $this->putManualData('[
            {"path": "user/slideshows/createBuckets/images/createBuckets",
             "name": "作成",
             "comment": "<ul class=\"mb-0\"><li>新しいスライドショーを作成できます。</li></ul>"
            },
            {"path": "user/slideshows/createBuckets/images/editBuckets",
             "name": "変更・削除",
             "comment": "<ul class=\"mb-0\"><li>スライドショーを変更・削除できます。</li></ul>"
            }
        ]', null, 4, 'basic');
    }

    /**
     * ブログ選択
     */
    private function listBuckets()
    {
        // 実行
        $this->browse(function (Browser $browser) {
            $browser->visit('/plugin/slideshows/listBuckets/' . $this->test_frame->page_id . '/' . $this->test_frame->id . '#frame-' . $this->test_frame->id)
                    ->assertPathBeginsWith('/')
                    ->screenshot('user/slideshows/listBuckets/images/listBuckets');
        });

        // マニュアル用データ出力
        $this->putManualData('[
            {"path": "user/slideshows/listBuckets/images/listBuckets",
             "comment": "<ul class=\"mb-0\"><li>表示するスライドショーを変更できます。</li></ul>"
            }
        ]', null, 4);
    }
}
