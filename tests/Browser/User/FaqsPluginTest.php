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
use App\Models\User\Faqs\Faqs;
use App\Models\User\Faqs\FaqsPosts;
use App\Models\User\Faqs\FaqsPostsTags;

/**
 * FAQテスト
 *
 * @see https://github.com/opensource-workshop/connect-cms/wiki/Dusk#テスト実行 [How to test]
 */
class FaqsPluginTest extends DuskTestCase
{
    /**
     * FAQテスト
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
        $this->listCategories();
        $this->create();

        $this->logout();
        $this->index();    // 記事一覧
        $this->show();
    }

    /**
     * 初期処理
     */
    private function init()
    {
        // 最初にマニュアルの順番確定用にメソッドを指定する。
        $this->reserveManual('index', 'show', 'create', 'createBuckets', 'listCategories', 'listBuckets');

        // データクリア
        Faqs::truncate();
        FaqsPosts::truncate();
        FaqsPostsTags::truncate();
        $this->initPlugin('faqs', '/test/faq');
    }

    /**
     * インデックス
     */
    private function index()
    {
        // 実行
        $this->browse(function (Browser $browser) {
            $post = FaqsPosts::first();
            $browser->visit('/test/faq')
                    ->assertPathBeginsWith('/')
                    ->screenshot('user/faqs/index/images/index1');

            $browser->click('#button_collapse_faq' . $post->id)
                    ->pause(500)
                    ->assertPathBeginsWith('/')
                    ->screenshot('user/faqs/index/images/index2');
        });

        // マニュアル用データ出力
        $this->putManualData('[
            {"path": "user/faqs/index/images/index1",
             "comment": "<ul class=\"mb-0\"><li>FAQの一覧です。</li></ul>"
            },
            {"path": "user/faqs/index/images/index2",
             "comment": "<ul class=\"mb-0\"><li>質問の詳細をアコーディオン方式で表示します。</li></ul>"
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
            // 新規作成
            $browser->visit("/plugin/faqs/createBuckets/" . $this->test_frame->page_id . '/' . $this->test_frame->id . '#frame-' . $this->test_frame->id)
                    ->assertPathBeginsWith('/')
                    ->type('faq_name', 'テストのFAQ')
                    ->type('view_count', '10')
                    ->click('#label_rss_on')
                    ->type('rss_count', '10')
                    ->screenshot('user/faqs/createBuckets/images/createBuckets')
                    ->press("登録確定");

            // 一度、選択確定させる。
            $bucket = Buckets::where('plugin_name', 'faqs')->first();
            $browser->visit('/plugin/faqs/listBuckets/' . $this->test_frame->page_id . '/' . $this->test_frame->id . '#frame-' . $this->test_frame->id)
                    ->radio('select_bucket', $bucket->id)
                    ->assertPathBeginsWith('/')
                    ->press("表示FAQ変更");

            // 変更
            $browser->visit("/plugin/faqs/editBuckets/" . $this->test_frame->page_id . '/' . $this->test_frame->id . '#frame-' . $this->test_frame->id)
                    ->assertPathBeginsWith('/')
                    ->screenshot('user/faqs/createBuckets/images/editBuckets');
        });

        // マニュアル用データ出力
        $this->putManualData('[
            {"path": "user/faqs/createBuckets/images/createBuckets",
             "name": "作成",
             "comment": "<ul class=\"mb-0\"><li>新しいFAQを作成できます。</li></ul>"
            },
            {"path": "user/faqs/createBuckets/images/editBuckets",
             "name": "変更・削除",
             "comment": "<ul class=\"mb-0\"><li>FAQを変更・削除できます。</li></ul>"
            }
        ]');
    }

    /**
     * カテゴリー
     */
    private function listCategories()
    {
        // 実行
        $this->browse(function (Browser $browser) {
            $browser->visit('/plugin/faqs/listCategories/' . $this->test_frame->page_id . '/' . $this->test_frame->id . '#frame-' . $this->test_frame->id)
                    ->assertPathBeginsWith('/')
                    ->screenshot('user/faqs/listCategories/images/listCategories');
        });

        // マニュアル用データ出力
        $this->putManualData('[
            {"path": "user/faqs/listCategories/images/listCategories",
             "comment": "<ul class=\"mb-0\"><li>カテゴリ設定は共通カテゴリとこのブログ独自のカテゴリ設定があります。</li><li>上の表が共通カテゴリで、表示のON/OFFと順番が指定できます。</li><li>下の表でこのブログ独自のカテゴリを設定できます。</li></ul>"
            }
        ]');
    }

    /**
     * バケツ選択
     */
    private function listBuckets()
    {
        // 実行
        $this->browse(function (Browser $browser) {
            $browser->visit('/plugin/faqs/listBuckets/' . $this->test_frame->page_id . '/' . $this->test_frame->id . '#frame-' . $this->test_frame->id)
                    ->assertPathBeginsWith('/')
                    ->screenshot('user/faqs/listBuckets/images/listBuckets')
                    ->press("表示FAQ変更");
        });

        // マニュアル用データ出力
        $this->putManualData('[
            {"path": "user/faqs/listBuckets/images/listBuckets",
             "comment": "<ul class=\"mb-0\"><li>表示するFAQを変更できます。</li></ul>"
            }
        ]');
    }

    /**
     * 記事登録
     */
    private function create()
    {
        // 実行
        $this->browse(function (Browser $browser) {
            // 一度クリア
            FaqsPosts::truncate();

            // 記事で使う画像の取得
            $upload = Uploads::where('client_original_name', 'blobid0000000000001.jpg')->first();
            $body = '<p>FAQの本文です。</p>';
            if ($upload) {
                $body .= '<br /><img src="/file/' . $upload->id . '" />';
            }

            $browser->visit('/plugin/faqs/create/' . $this->test_frame->page_id . '/' . $this->test_frame->id . '#frame-' . $this->test_frame->id)
                    ->type('post_title', 'テストのFAQ記事')
                    ->driver->executeScript('tinyMCE.get(0).setContent(\'' . $body . '\')');

            $browser->pause(500)
                    ->screenshot('user/faqs/create/images/create1')
                    ->scrollIntoView('footer')
                    ->screenshot('user/faqs/create/images/create2')
                    ->press('登録確定');

            // 編集リンクを表示
            $post = FaqsPosts::first();
            $browser->visit('/test/faq')
                    ->click('#button_collapse_faq' . $post->id)
                    ->pause(500)
                    ->assertPathBeginsWith('/')
                    ->screenshot('user/faqs/create/images/edit1');

            $browser->visit('/plugin/faqs/edit/' . $this->test_frame->page_id . '/' . $this->test_frame->id . '/' . $post->id . '#frame-' . $this->test_frame->id)
                    ->pause(500)
                    ->screenshot('user/faqs/create/images/edit2');
        });

        // マニュアル用データ出力
        $this->putManualData('[
            {"path": "user/faqs/create/images/create1",
             "name": "FAQ記事の登録１"
            },
            {"path": "user/faqs/create/images/create2",
             "name": "FAQ記事の登録２",
             "comment": "<ul class=\"mb-0\"><li>FAQの記事を登録できます。</li></ul>"
            },
            {"path": "user/faqs/create/images/edit1",
             "name": "変更画面へのボタン",
             "comment": "<ul class=\"mb-0\"><li>FAQ詳細を表示すると編集ボタンが表示されます。</li></ul>"
            },
            {"path": "user/faqs/create/images/edit2",
             "name": "FAQ記事の変更・削除",
             "comment": "<ul class=\"mb-0\"><li>FAQ内容を変更・削除できます。</li></ul>"
            }
        ]');
    }

    /**
     * 記事詳細
     */
    private function show()
    {
        // 実行
        $this->browse(function (Browser $browser) {
            $post = FaqsPosts::first();

            $browser->visit('/plugin/faqs/show/' . $this->test_frame->page_id . '/' . $this->test_frame->id . '/' . $post->id . '#frame-' . $this->test_frame->id)
                    ->screenshot('user/faqs/show/images/show');
        });

        // マニュアル用データ出力
        $this->putManualData('user/faqs/show/images/show');
    }
}
