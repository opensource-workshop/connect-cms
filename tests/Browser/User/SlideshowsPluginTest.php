<?php

namespace Tests\Browser\User;

use Illuminate\Foundation\Testing\DatabaseMigrations;
use Laravel\Dusk\Browser;
use Tests\DuskTestCase;

use App\Enums\PluginName;
use App\Models\Common\Buckets;
use App\Models\Common\Frame;
use App\Models\Common\Uploads;
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
    public function testSlideshow()
    {
        // 最初にマニュアルの順番確定用にメソッドを指定する。
        $this->reserveManual('index', 'editItem', 'createBuckets', 'listBuckets');

        $this->login(1);

        // プラグインが配置されていなければ追加(テストするFrameとページのインスタンス変数への保持も)
        $this->addPluginFirst('slideshows', '/test/slideshow', 2);

        $this->createBuckets();
        $this->listBuckets();

        $this->editItem();

        $this->logout();
        $this->index();   // 記事一覧
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
                    ->screenshot('user/slideshows/index/images/index');
        });

        // マニュアル用データ出力
        $this->putManualData('[
            {"path": "user/slideshows/index/images/index",
             "name": "スライドショー",
             "comment": "<ul class=\"mb-0\"><li>画像は画面の幅に合わせて自動で大きさが変化します。（レスポンシブします。）</li></ul>"
            }
        ]');
    }

    /**
     * 予定登録
     */
    private function editItem($title = null)
    {
        // データがあれば削除してから作成
        $slideshows_items = SlideshowsItems::get();
        foreach ($slideshows_items as $slideshows_item) {
            Uploads::destroy($slideshows_item->uploads_id);
            \Storage::delete(config('connect.directory_base') . $slideshows_item->image_path);
        }
        SlideshowsItems::query()->delete();

        // ブログ（バケツ）がある場合に記事作成
        if (Frame::where('plugin_name', 'slideshows')->first()) {
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
        }

        // マニュアル用データ出力(記事の登録はしていなくても、画像データはできているはず。reserveManual() で一旦、内容がクリアされているので、画像の登録は行う)
        $this->putManualData('[
            {"path": "user/slideshows/editItem/images/editItem1",
             "comment": "<ul class=\"mb-0\"><li>画像選択ボタンから画像のアップロードを行い、追加ボタンを押すことで登録されます。</li><li>登録された画像群は画面下部に一覧表示されます。</li><li>スライドショー項目を削除してもアップロードした画像は削除しません。アップロードファイルの削除は「管理者メニュー＞アップロードファイル」より行えるようにする予定です。</li></ul>"
            },
            {"path": "user/slideshows/editItem/images/editItem2"},
            {"path": "user/slideshows/editItem/images/editItem3"}
        ]');
    }

    /**
     * バケツ作成
     */
    private function createBuckets()
    {
        // 実行
        $this->browse(function (Browser $browser) {
            Slideshows::truncate();
            Buckets::where('plugin_name', 'slideshows')->delete();

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
        ]');
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
        ]');
    }
}
