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
use App\Models\User\Whatsnews\Whatsnews;

/**
 * 新着情報テスト
 *
 * @see https://github.com/opensource-workshop/connect-cms/wiki/Dusk#テスト実行 [How to test]
 */
class WhatsnewsPluginTest extends DuskTestCase
{
    /**
     * 新着情報テスト
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
        $this->editView();

        $this->logout();
        $this->index();    // 記事一覧
        $this->template(); // テンプレート
    }

    /**
     * 初期処理
     */
    private function init()
    {
        // データクリア
        Whatsnews::truncate();
        $this->initPlugin('whatsnews', '/test');

        // 最初にマニュアルの順番確定用にメソッドを指定する。
        $this->reserveManual('index', 'template', 'createBuckets', 'editView', 'listBuckets');
    }

    /**
     * インデックス
     */
    private function index()
    {
        // 実行
        $this->browse(function (Browser $browser) {
            $browser->visit('/test')
                    ->assertPathBeginsWith('/')
                    ->screenshot('user/whatsnews/index/images/index');
        });

        // マニュアル用データ出力
        $this->putManualData('[
            {"path": "user/whatsnews/index/images/index",
             "comment": "<ul class=\"mb-0\"><li>サイト上の記事を新着表示できます。</li></ul>"
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
            $browser->visit("/plugin/whatsnews/createBuckets/" . $this->test_frame->page_id . '/' . $this->test_frame->id . '#frame-' . $this->test_frame->id)
                    ->type('whatsnew_name', 'テストの新着情報')
                    ->type('count', '10')
                    ->click('#label_view_posted_name_1')
                    ->click('#label_view_posted_at_1')
                    ->pause(500)
                    ->assertPathBeginsWith('/')
                    ->screenshot('user/whatsnews/createBuckets/images/createBuckets1');

            $browser->scrollIntoView('#title_important')
                    //->click('#label_read_more_use_flag_1')
                    ->click('#label_target_plugin_blogs')
                    ->click('#label_target_plugin_bbses')
                    ->click('#label_target_plugin_databases')
                    ->pause(500)
                    ->assertPathBeginsWith('/')
                    ->screenshot('user/whatsnews/createBuckets/images/createBuckets2');

            $browser->scrollIntoView('footer')
                    ->screenshot('user/whatsnews/createBuckets/images/createBuckets3')
                    ->assertPathBeginsWith('/')
                    ->press("登録");

            // 一度、選択確定させる。
            $bucket = Buckets::where('plugin_name', 'whatsnews')->first();
            $browser->visit('/plugin/whatsnews/listBuckets/' . $this->test_frame->page_id . '/' . $this->test_frame->id . '#frame-' . $this->test_frame->id)
                    ->radio('select_bucket', $bucket->id)
                    ->screenshot('user/whatsnews/createBuckets/images/listBuckets')
                    ->assertPathBeginsWith('/')
                    ->press("変更");

            // 変更
            $browser->visit("/plugin/whatsnews/editBuckets/" . $this->test_frame->page_id . '/' . $this->test_frame->id . '#frame-' . $this->test_frame->id)
                    ->assertPathBeginsWith('/')
                    ->screenshot('user/whatsnews/createBuckets/images/editBuckets1');

            $browser->scrollIntoView('footer')
                    ->assertPathBeginsWith('/')
                    ->screenshot('user/whatsnews/createBuckets/images/editBuckets2');
        });

        // マニュアル用データ出力
        $this->putManualData('[
            {"path": "user/whatsnews/createBuckets/images/createBuckets1", "name": "新規作成１"},
            {"path": "user/whatsnews/createBuckets/images/createBuckets2", "name": "新規作成２"},
            {"path": "user/whatsnews/createBuckets/images/createBuckets3", "name": "新規作成３",
             "comment": "<ul class=\"mb-0\"><li>新着の表示方法やRSS、登録者、登録日時の表示条件を設定できます。</li><li>もっと見る機能では、表示件数以上の追加表示やボタンデザインが設定できます。<br />※デフォルトは「ボタンを表示しない」の為、使用する場合は「ボタンを表示する」に設定してください。</li><li>選択したフレームのみ表示することができるので、日本語関係だけの新着、英語関係だけの新着と分けることや、重要記事のみの新着などと意味のある新着を複数作ることができます。</li><li>フレーム側の設定で「新着に表示しない」を設定して、表示するフレームを絞り込むこともできます。</li></ul>"
            },
            {"path": "user/whatsnews/createBuckets/images/editBuckets1", "name": "変更・削除１"},
            {"path": "user/whatsnews/createBuckets/images/editBuckets2", "name": "変更・削除２",
             "comment": "<ul class=\"mb-0\"><li>新着情報を変更・削除できます。</li></ul>"
            }
        ]', null, 4, 'basic');
    }

    /**
     * バケツ選択
     */
    private function listBuckets()
    {
        // 実行
        $this->browse(function (Browser $browser) {
            $browser->visit('/plugin/whatsnews/listBuckets/' . $this->test_frame->page_id . '/' . $this->test_frame->id . '#frame-' . $this->test_frame->id)
                    ->assertPathBeginsWith('/')
                    ->screenshot('user/whatsnews/listBuckets/images/listBuckets')
                    ->press("変更");
        });

        // マニュアル用データ出力
        $this->putManualData('[
            {"path": "user/whatsnews/listBuckets/images/listBuckets",
             "comment": "<ul class=\"mb-0\"><li>表示する新着情報を変更できます。</li></ul>"
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
            $browser->visit('/plugin/whatsnews/editView/' . $this->test_frame->page_id . '/' . $this->test_frame->id . '#frame-' . $this->test_frame->id)
                    ->screenshot('user/whatsnews/editView/images/editView');
        });

        // マニュアル用データ出力
        $this->putManualData('[
            {"path": "user/whatsnews/editView/images/editView",
             "comment": "<ul class=\"mb-0\"><li>本文・サムネイル・罫線の表示・非表示を設定できます。</li></ul>"
            }
        ]', null, 4, 'basic');
    }

    /**
     * テンプレート
     */
    private function template()
    {
        $this->putManualTemplateData($this->test_frame, 'user', '/test', ['whatsnews', '新着情報'], ['onerow' => 'onerow', 'card_04' => 'card_04']);
    }
}
