<?php

namespace Tests\Browser\User;

use App\Models\Common\Buckets;
use App\Models\Common\Categories;
use App\Models\Common\PluginCategory;
use App\Models\User\Faqs\Faqs;
use App\Models\User\Faqs\FaqsPosts;
use App\Models\User\Faqs\FaqsPostsTags;
use Laravel\Dusk\Browser;
use Tests\DuskTestCase;

/**
 * FAQテスト
 *
 * @see https://github.com/opensource-workshop/connect-cms/wiki/Dusk#テスト実行 [How to test]
 * @see \Tests\Browser\Manage\SiteManageTest 実行後に実行すること（共通カテゴリが作成される）
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
        $this->template();
    }

    /**
     * 初期処理
     */
    private function init()
    {
        // データクリア
        Faqs::truncate();
        FaqsPosts::truncate();
        FaqsPostsTags::truncate();
        Categories::where('target', 'faqs')->forceDelete();
        PluginCategory::where('target', 'faqs')->forceDelete();
        $this->initPlugin('faqs', '/test/faq');

        // 最初にマニュアルの順番確定用にメソッドを指定する。
        $this->reserveManual('index', 'show', 'create', 'template', 'createBuckets', 'listCategories', 'listBuckets');
    }

    /**
     * インデックス
     */
    private function index()
    {
        // 実行
        $this->browse(function (Browser $browser) {
            $category = PluginCategory::where('target', 'faqs')->first();
            $browser->visit('/test/faq')
                    ->assertPathBeginsWith('/')
                    ->screenshot('user/faqs/index/images/index1')
                    ->click('#a_category_button_' . $category->categories_id)
                    ->screenshot('user/faqs/index/images/index2');
        });

        // ドロップダウン形式の絞り込みの例
        // ※ $this->browse() 入れ子対応。$this->login(), $this->logout()はなるべく$this->browse()内で使わない
        $this->login(1);
        $this->browse(function (Browser $browser) {
            $browser->visit("/plugin/faqs/editBuckets/" . $this->test_frame->page_id . '/' . $this->test_frame->id . '#frame-' . $this->test_frame->id)
                    ->assertPathBeginsWith('/')
                    ->click('#label_narrowing_down_type_dropdown')
                    ->pause(500)    // github actionsの安定性のためにclick後に少し待つ
                    ->screenshot('user/faqs/createBuckets/images/editBuckets2')
                    ->press("変更確定")
                    ->pause(500);    // github actionsの安定性のためにpress後に少し待つ
        });
        $this->logout();

        $this->browse(function (Browser $browser) {
            $browser->visit('/test/faq')
                    ->assertPathBeginsWith('/')
                    ->screenshot('user/faqs/index/images/index3-0')
                    ->click('#categories_id_' . $this->test_frame->id)  // ドロップダウンを開く
                    ->screenshot('user/faqs/index/images/index3');

            $post = FaqsPosts::first();

            // 本文表示
            $browser->click('#button_collapse_faq' . $post->id)
                    ->pause(500)
                    ->assertPathBeginsWith('/')
                    ->screenshot('user/faqs/index/images/index4');
        });

        // マニュアル用データ出力
        $this->putManualData('[
            {"path": "user/faqs/index/images/index1",
             "name": "FAQ一覧（絞り込み機能をボタン形式）",
             "comment": "<ul class=\"mb-0\"><li>FAQの一覧です。</li><li>絞り込み機能をボタン形式に設定している例です。</li></ul>"
            },
            {"path": "user/faqs/index/images/index2",
             "name": "FAQ一覧（ボタン形式で絞り込んだ状態）",
             "comment": "<ul class=\"mb-0\"><li>絞り込みたいカテゴリのボタンをクリックした状態です。</li><li>クリックしたカテゴリのFAQのみに絞り込まれます。</li></ul>"
            },
            {"path": "user/faqs/index/images/index3",
             "name": "FAQ一覧（絞り込み機能をドロップダウン形式）",
             "comment": "<ul class=\"mb-0\"><li>FAQの一覧です。</li><li>絞り込み機能をドロップダウン形式に設定している例です。</li></ul>"
            },
            {"path": "user/faqs/index/images/index2",
             "name": "FAQ本文の表示",
             "comment": "<ul class=\"mb-0\"><li>質問の詳細をアコーディオン方式で表示します。</li></ul>"
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
            // 新規作成
            $browser->visit("/plugin/faqs/createBuckets/" . $this->test_frame->page_id . '/' . $this->test_frame->id . '#frame-' . $this->test_frame->id)
                    ->assertPathBeginsWith('/')
                    ->type('faq_name', 'テストのFAQ')
                    ->type('view_count', '10')
                    ->click('#label_rss_on')
                    ->type('rss_count', '10')
                    ->click('#label_narrowing_down_type_button')
                    ->screenshot('user/faqs/createBuckets/images/createBuckets')
                    ->press("登録確定");

            // 画面表示がおいつかない場合があるので、ちょっと待つ
            $browser->pause(500);

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
        ]', null, 4);
    }

    /**
     * カテゴリー
     */
    private function listCategories()
    {
        // 実行
        $this->browse(function (Browser $browser) {
            // ループの連続実行で画面表示がおいついてないので、ちょっと待つ
            $browser->pause(500);

            $browser->visit('/plugin/faqs/listCategories/' . $this->test_frame->page_id . '/' . $this->test_frame->id . '#frame-' . $this->test_frame->id)
                    ->assertPathBeginsWith('/')
                    ->waitFor('input[name="add_category"]');

            // 共通カテゴリ行が存在する場合のみクリック（SiteManageTestの結果に依存しないように）
            $exists = $browser->script("return document.querySelector('#div_general_view_flag_1') !== null;")[0];
            if ($exists) {
                $browser->click('#div_general_view_flag_1');
            }

            $browser->press('変更')
                    ->screenshot('user/faqs/listCategories/images/listCategories');
        });

        // マニュアル用データ出力
        $this->putManualData('[
            {"path": "user/faqs/listCategories/images/listCategories",
             "name": "カテゴリ設定画面",
             "comment": "<ul class=\"mb-0\"><li>カテゴリ設定は共通カテゴリとこのブログ独自のカテゴリ設定があります。</li><li>上の表が共通カテゴリで、表示のON/OFFと順番が指定できます。</li><li>下の表でこのブログ独自のカテゴリを設定できます。</li></ul>"
            }
        ]', null, 4);

        // 個別カテゴリの作成
        $this->browse(function (Browser $browser) {
            $browser->visit('/plugin/faqs/listCategories/' . $this->test_frame->page_id . '/' . $this->test_frame->id . '#frame-' . $this->test_frame->id)
                    ->click('#div_add_view_flag')
                    ->type('add_display_sequence', '2')
                    ->type('add_classname', 'faq_dev')
                    ->type('add_category', '開発関係')
                    ->type('add_color', '#ffffff')
                    ->type('add_background_color', '#009000')
                    ->assertPathBeginsWith('/')
                    ->press('変更');
        });

        // 個別カテゴリの作成
        $this->browse(function (Browser $browser) {
            // 画面表示がおいつかない場合があるので、ちょっと待つ
            $browser->pause(500);

            $browser->visit('/plugin/faqs/listCategories/' . $this->test_frame->page_id . '/' . $this->test_frame->id . '#frame-' . $this->test_frame->id)
                    ->click('#div_add_view_flag')
                    ->type('add_display_sequence', '3')
                    ->type('add_classname', 'faq_use')
                    ->type('add_category', '使用方法')
                    ->type('add_color', '#ffffff')
                    ->type('add_background_color', '#c00000')
                    ->assertPathBeginsWith('/')
                    ->press('変更')
                    ->screenshot('user/faqs/listCategories/images/listCategories2');
        });

        // マニュアル用データ出力
        $this->putManualData('[
            {"path": "user/faqs/listCategories/images/listCategories2",
             "name": "個別カテゴリ登録後",
             "comment": "<ul class=\"mb-0\"><li>個別カテゴリも登録した後の状態です。</li></ul>"
            }
        ]', null, 4);
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
        ]', null, 4);
    }

    /**
     * 1件分の記事登録
     */
    private function postOne($browser, $title, $body, $category_id, $img_no1, $img_no2)
    {
        // 画面表示がおいつかない場合があるので、ちょっと待つ
        $browser->pause(500);

        $browser->visit('/plugin/faqs/create/' . $this->test_frame->page_id . '/' . $this->test_frame->id . '#frame-' . $this->test_frame->id)
                ->type('post_title', $title)
                ->driver->executeScript('tinyMCE.get(0).setContent(\'' . $body . '\')');

        $browser->pause(500)
                ->screenshot('user/faqs/create/images/create' . $img_no1)
                ->scrollIntoView('footer')
                ->select("categories_id", $category_id)
                ->screenshot('user/faqs/create/images/create' . $img_no2)
                ->press('登録確定');
    }

    /**
     * 記事登録
     */
    private function create()
    {
        // 実行
        $this->browse(function (Browser $browser) {
            // 記事で使うカテゴリの取得
            $categories = PluginCategory::where('target', 'faqs')->orderBy('id')->get();

            // 記事で使う画像の取得
            $upload = $this->firstOrCreateFileUpload('manual', 'copy_data/image/blobid0000000000001.png', 'blobid0000000000001.png', 'image/png', 'png', 'faqs', $this->test_frame->page_id);

            //$body = '<p>FAQの本文です。</p>';
            $body = "";
            if ($upload) {
                $body .= '<br /><img src="/file/' . $upload->id . '" />';
            }

            // カテゴリが3件取得できているはずなので、記事も3件作成する。
            $idx = 0;
            foreach ($categories as $category) {
                $this->postOne($browser, "テストのFAQ記事" . ($idx + 1), "<p>FAQの本文です。" . ($idx + 1) . "</p>" . $body, $category->categories_id, $idx * 2 + 1, $idx * 2 + 2);
                $idx++;
            }

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
        ]', null, 4, 'basic');
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
        $this->putManualData('user/faqs/show/images/show', null, 4);
    }

    /**
     * テンプレート
     */
    private function template()
    {
        $this->putManualTemplateData($this->test_frame, 'user', '/test/faq', ['faqs', 'FAQ'], ['category' => 'カテゴリー別表示']);
    }
}
