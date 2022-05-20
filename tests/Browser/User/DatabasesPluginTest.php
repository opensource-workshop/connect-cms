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
use App\Models\User\Databases\Databases;
use App\Models\User\Databases\DatabasesColumns;
use App\Models\User\Databases\DatabasesColumnsRole;
use App\Models\User\Databases\DatabasesColumnsSelects;
use App\Models\User\Databases\DatabasesFrames;
use App\Models\User\Databases\DatabasesInputCols;
use App\Models\User\Databases\DatabasesInputs;
use App\Models\User\Databases\DatabasesRole;

/**
 * データベーステスト
 *
 * @see https://github.com/opensource-workshop/connect-cms/wiki/Dusk#テスト実行 [How to test]
 */
class DatabasesPluginTest extends DuskTestCase
{
    /**
     * データベーステスト
     *
     * @group user
     * @see https://readouble.com/laravel/6.x/ja/dusk.html#running-tests
     */
    public function test()
    {
        $this->init();
        $this->login(1);

        $this->createBuckets();
        $this->editColumn();
        $this->editColumnDetail();
        $this->editColumn(true);
        $this->editView();
        $this->listBuckets();
        $this->import();
        $this->input();

        $this->logout();
        $this->index();
        $this->detail();
        $this->template();
    }

    /**
     * 初期処理
     */
    private function init()
    {
        // データクリア
        Databases::truncate();
        DatabasesColumns::truncate();
        DatabasesColumnsRole::truncate();
        DatabasesColumnsSelects::truncate();
        DatabasesFrames::truncate();
        DatabasesInputCols::truncate();
        DatabasesInputs::truncate();
        DatabasesRole::truncate();
        $this->initPlugin('databases', '/test/database');

        // 最初にマニュアルの順番確定用にメソッドを指定する。
        $this->reserveManual('index', 'input', 'detail', 'template', 'editColumn', 'editColumnDetail', 'editView', 'createBuckets', 'listBuckets', 'import');
    }

    /**
     * インデックス
     */
    private function index()
    {
        // プラグインがなければ追加(テストするFrameとページのインスタンス変数への保持も)
        $this->addPluginFirst('databases', '/test/database', 2);

        // 実行
        $this->browse(function (Browser $browser) {
            $browser->visit('/test/database')
                    ->assertPathBeginsWith('/')
                    ->screenshot('user/databases/index/images/index1');

            $browser->scrollIntoView('footer')
                    ->screenshot('user/databases/index/images/index2');

            $browser->scrollIntoView('#ccMainArea')
                    ->click('#select_search_column0_' . $this->test_frame->id)
                    ->screenshot('user/databases/index/images/index3')
                    ->click('#select_sort_column' . $this->test_frame->id)
                    ->screenshot('user/databases/index/images/index4');
        });

        // マニュアル用データ出力
        $this->putManualData('[
            {"path": "user/databases/index/images/index1",
             "name": "コンテンツの一覧１"
            },
            {"path": "user/databases/index/images/index2",
             "name": "コンテンツの一覧２",
             "comment": "<ul class=\"mb-0\"><li>登録されているデータが一覧表示されます。</li><li>設定によって、絞り込みの選択肢を表示できます。</li><li>設定によって、並べ替えの選択肢を表示できます。</li></ul>"
            },
            {"path": "user/databases/index/images/index3",
             "name": "絞り込み",
             "comment": "<ul class=\"mb-0\"><li>データを絞り込むことができます。</li><li>絞り込める項目は選択肢の項目で且つ、項目設定で絞り込みの対象としたものになります。</li></ul>"
            },
            {"path": "user/databases/index/images/index4",
             "name": "絞り込み",
             "comment": "<ul class=\"mb-0\"><li>データの並べ替えができます。</li><li>並び替えできる項目は選択肢の項目で且つ、並び替えの対象としたものになります。</li></ul>"
            }
        ]', null, 4);
    }

    /**
     * 詳細
     */
    private function detail()
    {
        // 実行
        $this->browse(function (Browser $browser) {
            $browser->visit('/plugin/databases/detail/' . $this->test_frame->page_id . '/' . $this->test_frame->id . '/1#frame-' . $this->test_frame->id)
                    ->screenshot('user/databases/detail/images/detail');
        });

        // マニュアル用データ出力
        $this->putManualData('[
            {"path": "user/databases/detail/images/detail",
             "name": "詳細表示",
             "comment": "<ul class=\"mb-0\"><li>１件ずつのデータが詳細表示できます。</li><li>表再画面に表示する項目は選べます。</li></ul>"
            }
        ]', null, 4);
    }

    /**
     * 入力
     */
    private function inputOne(&$browser, ...$records)
    {
        foreach ($records as $record) {
            // uploads 追加
            $upload = Uploads::create([
                "client_original_name" => $record["photo"],
                "mimetype" => "image/png",
                "extension" => "png",
                "size" => "999",
                "plugin_name" => "databases",
                "page_id" => $this->test_frame->page_id
            ]);

            // ファイルコピー
            \Storage::put($this->getDirectory($upload->id) . '/' . $upload->id . ".png", \Storage::disk('manual')->get('copy_data/database/' . $record["photo"]));

            // databases_inputs 作成
            $max = DatabasesInputs::orderBy("display_sequence", "desc")->first();
            $input = DatabasesInputs::create([
                "databases_id" => 1,
                "status" => 0,
                "display_sequence" => empty($max) ? 1 : ($max->display_sequence + 1),
                "posted_at" => date("Y-m-d H:i:s"),
                "first_committed_at" => date("Y-m-d H:i:s")
            ]);

            DatabasesInputCols::create(["databases_inputs_id" => $input->id, "databases_columns_id" => 1, "value" => $upload->id]);
            DatabasesInputCols::create(["databases_inputs_id" => $input->id, "databases_columns_id" => 2, "value" => $record["pref_name"]]);
            DatabasesInputCols::create(["databases_inputs_id" => $input->id, "databases_columns_id" => 3, "value" => $record["city_name"]]);

           /* ブラウザが不安定にエラーを吐くので、上記のデータ挿入方式へ変更
            $browser->visit('/test/database')
                    ->assertPathBeginsWith('/')
                    ->screenshot('user/databases/input/images/input1')
                    ->press('新規登録')
                    ->pause(10000)
                    ->attach('databases_columns_value[1]', __DIR__.'/database/' . $record["photo"])
                    ->select("databases_columns_value[2]", $record["pref_name"])
                    ->type('databases_columns_value[3]', $record["city_name"])
                    ->press('確認画面へ')
                    ->pause(10000)
                    ->press('登録確定');
            */
        }
    }

    /**
     * 入力
     */
    private function input()
    {
        // 実行
        $this->browse(function (Browser $browser) {
            $browser->visit('/test/database')
                    ->assertPathBeginsWith('/')
                    ->screenshot('user/databases/input/images/input1')
                    ->press('新規登録')
                    ->pause(500)
                    ->attach('databases_columns_value[1]', __DIR__.'/database/01-北海道旗.png')
                    ->select("databases_columns_value[2]", '北海道')
                    ->type('databases_columns_value[3]', '札幌市')
                    ->screenshot('user/databases/input/images/input2');

                    /* 動作が不安定すぎるので、ここはデータベース挿入方式へ変更
                    ->press('確認画面へ')
                    ->pause(5000)
                    ->screenshot('user/databases/input/images/input3')
                    ->press('登録確定')
                    ->screenshot('user/databases/input/images/input4');
                    */

            $this->inputOne(
                $browser,
                ["photo" => "01-北海道旗.png", "pref_name" => "北海道", "city_name" => "札幌市"],
                ["photo" => "02-青森県旗.png", "pref_name" => "青森県", "city_name" => "青森市"],
                ["photo" => "03-岩手県旗.png", "pref_name" => "岩手県", "city_name" => "盛岡市"],
                ["photo" => "04-宮城県旗.png", "pref_name" => "宮城県", "city_name" => "仙台市"],
                ["photo" => "05-秋田県旗.png", "pref_name" => "秋田県", "city_name" => "秋田市"],
                ["photo" => "06-山形県旗.png", "pref_name" => "山形県", "city_name" => "山形市"],
                ["photo" => "07-福島県旗.png", "pref_name" => "福島県", "city_name" => "福島市"]
            );
        });

        // マニュアル用データ出力
        $this->putManualData('[
            {"path": "user/databases/input/images/input1",
             "name": "新規登録ボタン",
             "comment": "<ul class=\"mb-0\"><li>新規登録ボタンをクリックし、コンテンツを登録する画面へ遷移します。</li></ul>"
            },
            {"path": "user/databases/input/images/input2",
             "name": "新規登録画面",
             "comment": "<ul class=\"mb-0\"><li>項目設定した内容の登録画面が表示されます。</li></ul>"
            }
        ]', null, 4);
    }

    /**
     * バケツ作成
     */
    private function createBuckets()
    {
        // 実行
        $this->browse(function (Browser $browser) {
            $browser->visit('/plugin/databases/createBuckets/' . $this->test_frame->page_id . '/' . $this->test_frame->id . '#frame-' . $this->test_frame->id)
                    ->type('databases_name', 'テストのデータベース')
                    ->screenshot('user/databases/createBuckets/images/createBuckets')
                    ->press('登録確定');

            // 一度、選択確定させる。
            $bucket = Buckets::where('plugin_name', 'databases')->first();
            $browser->visit('/plugin/databases/listBuckets/' . $this->test_frame->page_id . '/' . $this->test_frame->id . '#frame-' . $this->test_frame->id)
                    ->radio('select_bucket', $bucket->id)
                    ->screenshot('user/databases/listBuckets/images/listBuckets')
                    ->press("表示データベース変更");

            // 変更
            $browser->visit("/plugin/databases/editBuckets/" . $this->test_frame->page_id . '/' . $this->test_frame->id . '#frame-' . $this->test_frame->id)
                    ->screenshot('user/databases/createBuckets/images/editBuckets');
        });

        // マニュアル用データ出力
        $this->putManualData('[
            {"path": "user/databases/createBuckets/images/createBuckets",
             "name": "作成",
             "comment": "<ul class=\"mb-0\"><li>新しいデータベースを作成できます。</li></ul>"
            },
            {"path": "user/databases/createBuckets/images/editBuckets",
             "name": "変更・削除",
             "comment": "<ul class=\"mb-0\"><li>データベースを変更・削除できます。</li></ul>"
            }
        ]', null, 4);
    }

    /**
     * 項目の作成
     */
    private function editColumnOne(&$browser, $columns)
    {
        foreach ($columns as $column_name => $column_type) {
            $browser->visit('/plugin/databases/editColumn/' . $this->test_frame->page_id . '/' . $this->test_frame->id . '#frame-' . $this->test_frame->id)
                    ->type('column_name', $column_name)
                    ->select("column_type", $column_type)
                    ->press('#button_submit_add_column');
        }
    }

    /**
     * 項目の作成
     */
    private function editColumn($view_only = false)
    {
        // 実行
        $this->browse(function (Browser $browser) use ($view_only) {
            if (!$view_only) {
                $browser->visit('/plugin/databases/editColumn/' . $this->test_frame->page_id . '/' . $this->test_frame->id . '#frame-' . $this->test_frame->id)
                        ->type('column_name', '写真')
                        ->select("column_type", 'image')
                        ->check('required')
                        ->pause(500)
                        ->screenshot('user/databases/editColumn/images/editColumn1')
                        ->press('#button_submit_add_column');

                $this->editColumnOne($browser, ["都道府県名" => "select", "都道府県庁所在地" => "text"]);
            }

            $browser->visit('/plugin/databases/editColumn/' . $this->test_frame->page_id . '/' . $this->test_frame->id . '#frame-' . $this->test_frame->id)
                    ->screenshot('user/databases/editColumn/images/editColumn2');
        });

        // マニュアル用データ出力
        $this->putManualData('[
            {"path": "user/databases/editColumn/images/editColumn1",
             "name": "項目の追加",
             "comment": "<ul class=\"mb-0\"><li>項目の追加行で新しい項目を追加します。</li></ul>"
            },
            {"path": "user/databases/editColumn/images/editColumn2",
             "name": "複数の項目を設定した状態",
             "comment": "<ul class=\"mb-0\"><li>都道府県一覧を想定した項目を作りました。</li></ul>"
            }
        ]', null, 4);
    }

    /**
     * 項目詳細の作成
     */
    private function editColumnDetail()
    {
        // 実行
        $this->browse(function (Browser $browser) {
            // 写真
            $browser->visit('/plugin/databases/editColumnDetail/' . $this->test_frame->page_id . '/' . $this->test_frame->id . '/1#frame-' . $this->test_frame->id)
                    ->scrollIntoView('#div_group')
                    ->type('row_group', '1')
                    ->type('column_group', '1')
                    ->press('#button_column');

            // 都道府県名
            $browser->visit('/plugin/databases/editColumnDetail/' . $this->test_frame->page_id . '/' . $this->test_frame->id . '/2#frame-' . $this->test_frame->id)
                    ->scrollIntoView('#div_select')
                    ->press('#button_add_pref')
                    ->acceptDialog()
                    ->pause(1000);

            $browser->visit('/plugin/databases/editColumnDetail/' . $this->test_frame->page_id . '/' . $this->test_frame->id . '/2#frame-' . $this->test_frame->id)
                    ->scrollIntoView('#div_group')
                    ->click('#label_select_flag_1')
                    ->type('row_group', '1')
                    ->type('column_group', '2')
                    ->press('#button_column');

            $browser->visit('/plugin/databases/editColumnDetail/' . $this->test_frame->page_id . '/' . $this->test_frame->id . '/2#frame-' . $this->test_frame->id)
                    ->click('#label_list_detail_hide_role_article')
                    ->press('#button_base');

            // 都道府県庁所在地
            $browser->visit('/plugin/databases/editColumnDetail/' . $this->test_frame->page_id . '/' . $this->test_frame->id . '/3#frame-' . $this->test_frame->id)
                    ->screenshot('user/databases/editColumnDetail/images/editColumnDetail1');

            $browser->scrollIntoView('#div_rule')
                    ->screenshot('user/databases/editColumnDetail/images/editColumnDetail2');

            $browser->scrollIntoView('#div_caption')
                    ->screenshot('user/databases/editColumnDetail/images/editColumnDetail3');

            $browser->scrollIntoView('#div_column')
                    ->screenshot('user/databases/editColumnDetail/images/editColumnDetail4');

            $browser->scrollIntoView('#div_design')
                    ->type('row_group', '1')
                    ->type('column_group', '2')
                    ->screenshot('user/databases/editColumnDetail/images/editColumnDetail5')
                    ->press('#button_column');
        });

        // マニュアル用データ出力
        $this->putManualData('[
            {"path": "user/databases/editColumnDetail/images/editColumnDetail1",
             "name": "項目詳細設定（新着情報等の設定）",
             "comment": "<ul class=\"mb-0\"><li>項目の詳細内容（新着情報等の設定）を設定します。項目詳細設定は型によって、設定できる項目が異なります。ここでは、1行文字列型を例にします。</li></ul>"
            },
            {"path": "user/databases/editColumnDetail/images/editColumnDetail2",
             "name": "項目詳細設定（チェック処理の設定）",
             "comment": "<ul class=\"mb-0\"><li>項目の詳細内容（チェック処理の設定）を設定します。</li></ul>"
            },
            {"path": "user/databases/editColumnDetail/images/editColumnDetail3",
             "name": "項目詳細設定（キャプションの設定）",
             "comment": "<ul class=\"mb-0\"><li>項目の詳細内容（キャプションの設定）を設定します。</li></ul>"
            },
            {"path": "user/databases/editColumnDetail/images/editColumnDetail4",
             "name": "項目詳細設定（DBカラム設定）",
             "comment": "<ul class=\"mb-0\"><li>項目の詳細内容（DBカラム設定）を設定します。</li></ul>"
            },
            {"path": "user/databases/editColumnDetail/images/editColumnDetail5",
             "name": "DBカラム設定の後半、デザインの設定",
             "comment": "<ul class=\"mb-0\"><li>項目の詳細内容（DBカラム設定の後半、及びデザインの設定）を設定します。</li></ul>"
            }
        ]', null, 4);
    }

    /**
     * 選択
     */
    private function listBuckets()
    {
        // 実行
        $this->browse(function (Browser $browser) {
            $browser->visit('/plugin/databases/listBuckets/' . $this->test_frame->page_id . '/' . $this->test_frame->id . '#frame-' . $this->test_frame->id)
                    ->screenshot('user/databases/listBuckets/images/listBuckets1')
                    ->click("#button_setting_dropdown")
                    ->screenshot('user/databases/listBuckets/images/listBuckets2');
        });

        // マニュアル用データ出力
        $this->putManualData('[
            {"path": "user/databases/listBuckets/images/listBuckets1",
             "name": "データベース一覧",
             "comment": "<ul class=\"mb-0\"><li>表示するデータベースを変更できます。</li><li>各データベースの「設定変更」登録データの「ダウンロード」「インポート」ができます。</li></ul>"
            },
            {"path": "user/databases/listBuckets/images/listBuckets2",
             "name": "データベースのコピー機能",
             "comment": "<ul class=\"mb-0\"><li>「設定変更」ボタンの▼から、「コピーしてDB作成へ」機能が利用できます。<br />「コピーしてDB作成へ」は、登録データはコピーせず設定のみコピーしてDB作成します。</li></ul>"
            }
        ]', null, 4);
    }

    /**
     * インポート
     */
    private function import()
    {
        // 実行
        $this->browse(function (Browser $browser) {
            $browser->visit('/plugin/databases/import/' . $this->test_frame->page_id . '/' . $this->test_frame->id . '/1#frame-' . $this->test_frame->id)
                    ->screenshot('user/databases/import/images/import');
        });

        // マニュアル用データ出力
        $this->putManualData('[
            {"path": "user/databases/import/images/import",
             "name": "インポート",
             "comment": "<ul class=\"mb-0\"><li>インポート機能は、管理者用の機能です。<br />CSVファイルを使って、データベースへ一括登録できます。</li><li>CSVファイルの文字コードは<span style=\"color:#e83e8c;\">Shift_JIS</span>, 又は<span style=\"color:#e83e8c;\">UTF-8</span>です。<br />文字コードの自動検出は、<span style=\"color:#e83e8c;\">Shift_JIS</span>, <span style=\"color:#e83e8c;\">UTF-8</span>のいずれかを自動検出します。</li><li>CSVインポートは登録・更新・添付ファイルに対応しています。<br />添付ファイルは、下記「添付ファイル一括インポートの場合」を参照してください。</li></ul>"
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
            $browser->visit('/plugin/databases/editView/' . $this->test_frame->page_id . '/' . $this->test_frame->id . '#frame-' . $this->test_frame->id)
                    ->pause(500)
                    ->type('view_count', '10')
                    ->click('#label_use_search_flag_1')
                    ->click('#label_use_select_flag_1')
                    ->click('#label_use_sort_flag_updated_asc')
                    ->click('#label_use_sort_flag_updated_desc')
                    ->click('#label_use_sort_flag_posted_asc')
                    ->click('#label_use_sort_flag_posted_desc')
                    ->click('#label_use_sort_flag_random_session')
                    ->click('#label_use_sort_flag_random_every')
                    ->select('default_sort_flag', 'posted_desc')
                    ->screenshot('user/databases/editView/images/editView1');

            $browser->scrollIntoView('footer')
                    ->screenshot('user/databases/editView/images/editView2')
                    ->press('登録確定');
        });

        // マニュアル用データ出力
        $this->putManualData('[
            {"path": "user/databases/editView/images/editView1"},
            {"path": "user/databases/editView/images/editView2",
             "comment": "<ul class=\"mb-0\"><li>データベースの表示設定を設定できます。</li></ul>"
            }
        ]', null, 4);
    }

    /**
     * テンプレート
     */
    private function template()
    {
        $this->putManualTemplateData(
            $this->test_frame,
            'user',
            '/test/database',
            ['databases', 'データベース'],
            ['table' => 'table', 'default-left-col-3' => 'default-left-col-3']
        );
    }
}
