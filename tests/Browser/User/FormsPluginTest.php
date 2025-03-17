<?php

namespace Tests\Browser\User;

use Illuminate\Foundation\Testing\DatabaseMigrations;
use Laravel\Dusk\Browser;
use Tests\DuskTestCase;

use App\Enums\PluginName;
use App\Models\Common\Buckets;
use App\Models\Common\Frame;
use App\Models\Common\Page;
use App\Models\Common\Uploads;
use App\Models\Core\Dusks;
use App\Models\User\Forms\Forms;
use App\Models\User\Forms\FormsColumns;
use App\Models\User\Forms\FormsColumnsSelects;
use App\Models\User\Forms\FormsInputCols;
use App\Models\User\Forms\FormsInputs;

/**
 * フォームテスト
 *
 * @see https://github.com/opensource-workshop/connect-cms/wiki/Dusk#テスト実行 [How to test]
 */
class FormsPluginTest extends DuskTestCase
{
    /**
     * フォームテスト
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
        $this->listInputs();
        $this->listBuckets();

        $this->logout();
        $this->index();
        $this->template();

        $this->questionnaires();
    }

    /**
     * 初期処理
     */
    private function init()
    {
        // データクリア
        Forms::truncate();
        FormsColumns::truncate();
        FormsColumnsSelects::truncate();
        FormsInputCols::truncate();
        FormsInputs::truncate();
        $this->initPlugin('forms', '/test/form');

        // 最初にマニュアルの順番確定用にメソッドを指定する。
        $this->reserveManual('index', 'editColumn', 'editColumnDetail', 'createBuckets', 'listInputs', 'listBuckets', 'template');
    }

    /**
     * インデックス
     */
    private function index()
    {
        // プラグインがなければ追加(テストするFrameとページのインスタンス変数への保持も)
        $this->addPluginFirst('forms', '/test/form', 2);

        // 実行
        $this->browse(function (Browser $browser) {
            $browser->visit('/test/form')
                    ->assertPathBeginsWith('/')
                    ->screenshot('user/forms/index/images/index1');

            $browser->scrollIntoView('footer')
                    ->screenshot('user/forms/index/images/index3');
        });

        // マニュアル用データ出力
        $this->putManualData('[
            {"path": "user/forms/index/images/index1",
             "name": "入力画面１"
            },
            {"path": "user/forms/index/images/index2",
             "name": "入力画面２",
             "comment": "<ul class=\"mb-0\"><li>設定した項目の入力フォームが完成です。</li></ul>"
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
            $browser->visit('/plugin/forms/createBuckets/' . $this->test_frame->page_id . '/' . $this->test_frame->id . '#frame-' . $this->test_frame->id)
                    ->type('forms_name', 'テストのフォーム')
                    ->screenshot('user/forms/createBuckets/images/createBuckets1');

            $browser->scrollIntoView('#div_entry_limit')
                    ->screenshot('user/forms/index/images/createBuckets2');

            $browser->scrollIntoView('footer')
                    ->screenshot('user/forms/createBuckets/images/createBuckets3')
                    ->press('登録確定');

            // 画面表示がおいつかない場合があるので、ちょっと待つ
            $browser->pause(500);

            // 一度、選択確定させる。
            $bucket = Buckets::where('plugin_name', 'forms')->first();
            $browser->visit('/plugin/forms/listBuckets/' . $this->test_frame->page_id . '/' . $this->test_frame->id . '#frame-' . $this->test_frame->id)
                    ->radio('select_bucket', $bucket->id)
                    ->screenshot('user/forms/listBuckets/images/listBuckets')
                    ->press("表示フォーム変更");

            // 変更
            $browser->visit("/plugin/forms/editBuckets/" . $this->test_frame->page_id . '/' . $this->test_frame->id . '#frame-' . $this->test_frame->id)
                    ->screenshot('user/forms/createBuckets/images/editBuckets1');

            $browser->scrollIntoView('footer')
                    ->screenshot('user/forms/createBuckets/images/editBuckets2');
        });

        // マニュアル用データ出力
        $this->putManualData('[
            {"path": "user/forms/createBuckets/images/createBuckets1",
             "name": "作成１",
             "comment": "<ul class=\"mb-0\"><li>フォームモードを選ぶことで、フォームとしての使用とアンケートとしての使用を選択できます。</li><li>データ保存は初期値でチェックが入っています。<br />組織内の個人情報保護規定などでサーバにデータを残せない場合はこのチェックを外すことでサーバにデータが保存されません。ただし、その場合はメール送信先を設定して、メールで登録データを受け取れるようにしておかないと、登録された内容がどこにも保存されないため、注意してください。</li><li>フォームの閲覧制限を設定できます。「制限しない」はページが表示されれば、フォームも表示されます。<br />「パスワードで閲覧制限する」は閲覧パスワードを設定でき、閲覧パスワードを正しく入力してからフォームを表示できます。学校や組織内で事前にプリントなどでパスワードを配布できる場合に便利です。<br />「画像認証で閲覧制限する」はフォームが表示される前に画像認証を設定できます。機械などで宣伝を投稿してくる迷惑投稿の対策になります。</li></ul>"
            },
            {"path": "user/forms/createBuckets/images/createBuckets2",
             "name": "作成２"
            },
            {"path": "user/forms/createBuckets/images/createBuckets3",
             "name": "作成３",
             "comment": "<ul class=\"mb-0\"><li>新しいフォームを作成できます。</li></ul>"
            },
            {"path": "user/forms/createBuckets/images/editBuckets1",
             "name": "変更・削除１"
            },
            {"path": "user/forms/createBuckets/images/editBuckets2",
             "name": "変更・削除２",
             "comment": "<ul class=\"mb-0\"><li>フォームを変更・削除できます。</li></ul>"
            }
        ]', null, 4, 'basic');
    }

    /**
     * 項目の作成
     */
    private function editColumnOne(&$browser, $columns)
    {
        foreach ($columns as $column_name => $column_attr) {
            $browser->visit('/plugin/forms/editColumn/' . $this->test_frame->page_id . '/' . $this->test_frame->id . '#frame-' . $this->test_frame->id)
                    ->type('column_name', $column_name)
                    ->select("column_type", $column_attr["type"]);
            if ($column_attr["required"]) {
                $browser->check("required");
            }
            $browser->press('#button_submit_add_column');
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
                $browser->visit('/plugin/forms/editColumn/' . $this->test_frame->page_id . '/' . $this->test_frame->id . '#frame-' . $this->test_frame->id)
                        ->type('column_name', '氏名')
                        ->select("column_type", 'group')
                        ->pause(500)
                        ->screenshot('user/forms/editColumn/images/editColumn1')
                        ->press('#button_submit_add_column');

                $this->editColumnOne(
                    $browser, [
                        "姓" => ["type" => "text", "required" => true, "id" => 2],
                        "名" => ["type" => "text", "required" => true, "id" => 3],
                        "所属" => ["type" => "text", "required" => false, "id" => 4],
                        "メールアドレス" => ["type" => "mail", "required" => false, "id" => 5],
                        "お問合せ種類" => ["type" => "radio", "required" => false, "id" => 6],
                        "お問合せ内容" => ["type" => "textarea", "required" => false, "id" => 7],
                        "個人情報保護方針への同意" => ["type" => "checkbox", "required" => true, "id" => 8]
                    ]
                );
            }

            $browser->visit('/plugin/forms/editColumn/' . $this->test_frame->page_id . '/' . $this->test_frame->id . '#frame-' . $this->test_frame->id)
                    ->screenshot('user/forms/editColumn/images/editColumn2');
        });

        // マニュアル用データ出力
        $this->putManualData('[
            {"path": "user/forms/editColumn/images/editColumn1",
             "name": "項目の追加",
             "comment": "<ul class=\"mb-0\"><li>項目の追加行で新しい項目を追加します。</li></ul>"
            },
            {"path": "user/forms/editColumn/images/editColumn2",
             "name": "複数の項目を設定した状態",
             "comment": "<ul class=\"mb-0\"><li>お問い合わせフォームを想定した項目を作りました。</li></ul>"
            }
        ]', null, 4, 'basic');
    }

    /**
     * 項目詳細の作成
     */
    private function editColumnDetail()
    {
        // 実行
        $this->browse(function (Browser $browser) {
            // 氏名
            $browser->visit('/plugin/forms/editColumnDetail/' . $this->test_frame->page_id . '/' . $this->test_frame->id . '/1#frame-' . $this->test_frame->id)
                    ->select('frame_col', '2')
                    ->screenshot('user/forms/editColumnDetail/images/editColumnDetailGroup1')
                    ->press('#button_col_original');

            $browser->scrollIntoView('footer')
                    ->screenshot('user/forms/editColumnDetail/images/editColumnDetailGroup2');

            // 姓
            $browser->visit('/plugin/forms/editColumnDetail/' . $this->test_frame->page_id . '/' . $this->test_frame->id . '/2#frame-' . $this->test_frame->id)
                    ->screenshot('user/forms/editColumnDetail/images/editColumnDetailFirstName1');

            $browser->scrollIntoView('#div_rule_min')
                    ->screenshot('user/forms/editColumnDetail/images/editColumnDetailFirstName2');

            $browser->scrollIntoView('footer')
                    ->screenshot('user/forms/editColumnDetail/images/editColumnDetailFirstName3');

            // メールアドレス
            $browser->visit('/plugin/forms/editColumnDetail/' . $this->test_frame->page_id . '/' . $this->test_frame->id . '/5#frame-' . $this->test_frame->id)
                    ->screenshot('user/forms/editColumnDetail/images/editColumnDetailMail1');

            $browser->scrollIntoView('footer')
                    ->screenshot('user/forms/editColumnDetail/images/editColumnDetailMail2');

            // お問合せ種類
            $browser->visit('/plugin/forms/editColumnDetail/' . $this->test_frame->page_id . '/' . $this->test_frame->id . '/6#frame-' . $this->test_frame->id)
                    ->type('select_name', 'お見積依頼')
                    ->press('#button_add_select')
                    ->pause(500);
            $browser->visit('/plugin/forms/editColumnDetail/' . $this->test_frame->page_id . '/' . $this->test_frame->id . '/6#frame-' . $this->test_frame->id)
                    ->type('select_name', '導入相談')
                    ->press('#button_add_select')
                    ->pause(500);
            $browser->visit('/plugin/forms/editColumnDetail/' . $this->test_frame->page_id . '/' . $this->test_frame->id . '/6#frame-' . $this->test_frame->id)
                    ->type('select_name', 'その他')
                    ->press('#button_add_select')
                    ->pause(500)
                    ->screenshot('user/forms/editColumnDetail/images/editColumnDetailRadio1');

            $browser->scrollIntoView('footer')
                    ->screenshot('user/forms/editColumnDetail/images/editColumnDetailRadio2');

            // 個人情報保護方針への同意
            $browser->visit('/plugin/forms/editColumnDetail/' . $this->test_frame->page_id . '/' . $this->test_frame->id . '/8#frame-' . $this->test_frame->id)
                    ->type('select_name', '以下の内容に同意する。')
                    ->press('#button_add_select');

            $browser->visit('/plugin/forms/editColumnDetail/' . $this->test_frame->page_id . '/' . $this->test_frame->id . '/8#frame-' . $this->test_frame->id)
                    ->type('caption', '<div style="height: 120px; overflow-y: scroll; border: solid 1px #c0c0c0;"><ol>
<li>事業者の氏名又は名称<br />株式会社オープンソース・ワークショップ</li>
<li>個人情報保護管理者（若しくはその代理人）の氏名又は職名、所属及び連絡先<br />管理者職名：永原　篤<br />連絡先：メールアドレス：info@opensource-workshop.jp　TEL：03-5534-8088</li>
<li>個人情報の利用目的<ul style="list-style-type: disc;"><li>見積書の作成及び送付のため</li><li>お問い合わせ対応（本人への連絡を含む）のため</li></ul></li>
<li>個人情報取扱いの委託<br />当社は事業運営上、前項利用目的の範囲に限って個人情報を外部に委託することがあります。この場合、個人情報保護水準の高い委託先を選定し、個人情報の適正管理・機密保持についての契約を交わし、適切な管理を実施させます。</li>
<li>個人情報の開示等の請求<br />ご本人様は、当社に対してご自身の個人情報の開示等（利用目的の通知、開示、内容の訂正・追加・削除、利用の停止または消去、第三者への提供の停止）に関して、下記の当社問合わせ窓口に申し出ることができます。その際、当社はお客様ご本人を確認させていただいたうえで、合理的な期間内に対応いたします。<br />【お問合せ窓口】<br />〒104-0053 東京都中央区晴海三丁目13番 1-4807号<br />メールアドレス：info@opensource-workshop.jp　TEL：03-5534-8088<br />（受付時間　9:00～18:00　※土・日曜日、祝日、年末年始、ゴールデンウィークを除く)</li>
<li>個人情報を提供されることの任意性について<br />ご本人様が当社に個人情報を提供されるかどうかは任意によるものです。 ただし、必要な項目をいただけない場合、適切な対応ができない場合があります。</li></ol></div>')
                    ->press('#button_caption')
                    ->pause(500)
                    ->screenshot('user/forms/editColumnDetail/images/editColumnDetailCheck1');

            $browser->scrollIntoView('footer')
                    ->screenshot('user/forms/editColumnDetail/images/editColumnDetailCheck2');
        });

        // マニュアル用データ出力
        $this->putManualData('[
            {"path": "user/forms/editColumnDetail/images/editColumnDetailGroup1",
             "name": "項目詳細設定（1まとめ行）１"
            },
            {"path": "user/forms/editColumnDetail/images/editColumnDetailGroup2",
             "name": "項目詳細設定（1まとめ行）２",
             "comment": "<ul class=\"mb-0\"><li>まとめ行型の詳細設定です。</li></ul>"
            },
            {"path": "user/forms/editColumnDetail/images/editColumnDetailFirstName1",
             "name": "項目詳細設定（1行文字列）１"
            },
            {"path": "user/forms/editColumnDetail/images/editColumnDetailFirstName2",
             "name": "項目詳細設定（1行文字列）２"
            },
            {"path": "user/forms/editColumnDetail/images/editColumnDetailFirstName3",
             "name": "項目詳細設定（1行文字列）３",
             "comment": "<ul class=\"mb-0\"><li>1行文字列型の詳細設定です。</li></ul>"
            },
            {"path": "user/forms/editColumnDetail/images/editColumnDetailMail1",
             "name": "項目詳細設定（メールアドレス）１"
            },
            {"path": "user/forms/editColumnDetail/images/editColumnDetailMail2",
             "name": "項目詳細設定（メールアドレス）２",
             "comment": "<ul class=\"mb-0\"><li>メールアドレス型の詳細設定です。</li></ul>"
            },
            {"path": "user/forms/editColumnDetail/images/editColumnDetailRadio1",
             "name": "項目詳細設定（複数選択型）１"
            },
            {"path": "user/forms/editColumnDetail/images/editColumnDetailRadio2",
             "name": "項目詳細設定（複数選択型）２",
             "comment": "<ul class=\"mb-0\"><li>複数選択型の詳細設定です。</li></ul>"
            },
            {"path": "user/forms/editColumnDetail/images/editColumnDetailCheck1",
             "name": "項目詳細設定（単一選択型）１"
            },
            {"path": "user/forms/editColumnDetail/images/editColumnDetailCheck2",
             "name": "項目詳細設定（単一選択型）２",
             "comment": "<ul class=\"mb-0\"><li>単一選択型の詳細設定です。</li></ul>"
            }
        ]', null, 4, 'basic');
    }

    /**
     * 登録一覧
     */
    private function listInputs()
    {
        // 実行
        $this->browse(function (Browser $browser) {
            $browser->visit('/plugin/forms/listInputs/' . $this->test_frame->page_id . '/' . $this->test_frame->id . '#frame-' . $this->test_frame->id)
                    ->screenshot('user/forms/listInputs/images/listInputs');
        });

        // マニュアル用データ出力
        $this->putManualData('[
            {"path": "user/forms/listInputs/images/listInputs",
             "name": "フォーム一覧",
             "comment": "<ul class=\"mb-0\"><li>登録されたデータが一覧表示されます。</li></ul>"
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
            $browser->visit('/plugin/forms/listBuckets/' . $this->test_frame->page_id . '/' . $this->test_frame->id . '#frame-' . $this->test_frame->id)
                    ->screenshot('user/forms/listBuckets/images/listBuckets1');
        });

        // マニュアル用データ出力
        $this->putManualData('[
            {"path": "user/forms/listBuckets/images/listBuckets1",
             "name": "フォーム一覧",
             "comment": "<ul class=\"mb-0\"><li>表示するフォームを変更できます。</li><li>各フォームの登録データの「ダウンロード」ができます。</li></ul>"
            }
        ]', null, 4, 'basic');
    }

    /**
     * テンプレート
     */
    private function template()
    {
        // 画面の上からキャプチャするために、一度、開きなおす。
        $this->browse(function (Browser $browser) {
            $browser->visit('/test');
        });
        $this->browse(function (Browser $browser) {
            $browser->visit('/test/form');
        });

        $this->putManualTemplateData(
            $this->test_frame,
            'user',
            '/test/form',
            ['forms', 'フォーム'],
            ['default' => 'default', 'label-sm-4' => 'ラベル長め（幅１／３使用）', 'label-sm-6' => 'ラベル長め（幅１／２使用）']
        );
    }

    /**
     * アンケート
     */
    private function questionnaires()
    {
        $this->login(1);

        // アンケート - 初期化
        $this->initQ();

        // マニュアル用データ準備（Duskレコードの準備と親Duskの取得）
        $this->createDusks([
            'category' => 'user',
            'sort' => 4,
            'plugin_name' => 'questionnaires',
            'plugin_title' => 'アンケート',
            'plugin_desc' => 'アンケートを取ることができます。アンケートはフォームプラグインのアンケートモードで実現します。',
            'level' => 'basic',
            'test_result' => 'OK',
        ], ['index', 'createBuckets', 'editColumn', 'editColumnDetail']);

        // アンケート - バケツ作成
        $this->createBucketsQ();

        // アンケート - 項目の作成
        $this->editColumnQ();

        $this->logout();

        // アンケート - 回答入力
        $this->indexQ();
    }

    /**
     * アンケート - 表示
     */
    private function indexQ()
    {
        // 実行
        $this->browse(function (Browser $browser) {
            $browser->visit('/test/questionnaire')
                    ->screenshot('user/questionnaires/index/images/index');
        });

        // マニュアルデータ
        $this->updateDusk('questionnaires', 'index', [
            'method_title' => 'アンケート回答画面',
            'method_desc' => '設定した項目でアンケートを取ることができます。',
            'method_detail' => '',
            'html_path' => 'user/questionnaires/index/index.html',
            'img_args' => '[
                {"path": "user/questionnaires/index/images/index",
                 "name": "アンケート回答画面",
                 "comment": "<ul class=\"mb-0\"><li>設定した項目でアンケートを取ることができます。</li><li>項目は1行テキスト形式、複数行テキスト形式、ラジオボタンで択一選択、チェックボックスで複数選択が使用できます。</li></ul>"
                }
             ]',
        ]);
    }

    /**
     * アンケート - 初期化
     */
    private function initQ()
    {
        // アンケートのデータの削除
        $forms = Forms::where('form_mode', 'questionnaire')->get();
        foreach ($forms as $form) {
            FormsColumns::where('forms_id', $form->id)->delete();
        }

        // アンケートのマニュアルデータの削除
        Dusks::where('plugin_name', 'questionnaires')->delete();

        // アンケートのフレームの削除
        $page = Page::firstOrNew(['permanent_link' => '/test/questionnaire']);
        Frame::where('page_id', $page->id)->delete();

        // プラグインがなければ追加(テストするFrameとページのインスタンス変数への保持も)
        $this->addPluginFirst('forms', '/test/questionnaire', 2);
    }

    /**
     * アンケート - バケツ作成
     */
    private function createBucketsQ()
    {
        // 実行
        $this->browse(function (Browser $browser) {
            $browser->visit('/plugin/forms/createBuckets/' . $this->test_frame->page_id . '/' . $this->test_frame->id . '#frame-' . $this->test_frame->id)
                    ->type('forms_name', 'テストのアンケート')
                    ->click('#label_form_mode_questionnaire')
                    ->pause(500)
                    ->screenshot('user/questionnaires/createBuckets/images/createBuckets1');

            $browser->scrollIntoView('footer')
                    ->screenshot('user/questionnaires/createBuckets/images/createBuckets2')
                    ->press('登録確定');

            // 画面表示がおいつかない場合があるので、ちょっと待つ
            $browser->pause(500);

            // 一度、選択確定させる。
            $bucket = Buckets::where('plugin_name', 'forms')->orderBy('id', 'desc')->first();
            $browser->visit('/plugin/forms/listBuckets/' . $this->test_frame->page_id . '/' . $this->test_frame->id . '#frame-' . $this->test_frame->id)
                    ->radio('select_bucket', $bucket->id)
                    ->screenshot('user/questionnaires/listBuckets/images/listBuckets')
                    ->press("表示フォーム変更");
        });

        // マニュアルデータ
        $this->updateDusk('questionnaires', 'createBuckets', [
            'method_title' => 'アンケート作成',
            'method_desc' => 'アンケートの回答画面です。設定した項目でアンケートを取ることができます。',
            'method_detail' => 'アンケートはフォームプラグインのアンケートモードで実現します。<br />詳しくはフォームプラグインのページを参照してください。',
            'html_path' => 'user/questionnaires/createBuckets/index.html',
            'img_args' => '[
                {"path": "user/questionnaires/createBuckets/images/createBuckets1",
                 "name": "アンケート作成画面１",
                 "comment": "<ul class=\"mb-0\"><li>フォームを作成する際、フォームモードをアンケートとして使用する。を選んでください。</li></ul>"
                },
                {"path": "user/questionnaires/createBuckets/images/createBuckets2",
                 "name": "アンケート作成画面２",
                 "comment": "<ul class=\"mb-0\"><li>項目を設定して、新しいアンケートを作成できます。</li></ul>"
                }
            ]',
        ]);
    }

    /**
     * アンケート - 項目の作成
     */
    private function editColumnQ($view_only = false)
    {
        // 実行
        $this->browse(function (Browser $browser) use ($view_only) {
            if (!$view_only) {
                $browser->visit('/plugin/forms/editColumn/' . $this->test_frame->page_id . '/' . $this->test_frame->id . '#frame-' . $this->test_frame->id)
                        ->driver->executeScript('tinyMCE.get(0).setContent(\'氏名\')');

                // executeScriptした後は一度、メソッドチェーンを切らないと、次のメソッドでエラーが出る（ワーニングとかが出ている？？）
                $browser->pause(500)
                        ->select("column_type", 'text')
                        ->check("required")
                        //->pause(500)
                        //->screenshot('user/questionnaires/editColumn/images/editColumn1')
                        ->press('#button_submit_add_column');

                $this->editColumnOneQ(
                    $browser, [
                        "ヤンバルクイナは好きですか？" => ["type" => "radio", "required" => true, "id" => 2],
                        "Connect-CMSは好きですか？" => ["type" => "checkbox", "required" => true, "id" => 3],
                        "ヤンバルクイナとConnect-CMSへの愛をどうぞ" => ["type" => "textarea", "required" => false, "id" => 4]
                    ]
                );
            }

            // 項目の詳細を設定
            $this->editColumnDetailQ();
            // スクリーンショット
            $browser->visit('/plugin/forms/editColumn/' . $this->test_frame->page_id . '/' . $this->test_frame->id . '#frame-' . $this->test_frame->id)
                    ->screenshot('user/questionnaires/editColumn/images/editColumn1')
                    ->scrollIntoView('#forms_edit_row2_add')
                    ->screenshot('user/questionnaires/editColumn/images/editColumn2');
        });

        // マニュアルデータ
        $this->updateDusk('questionnaires', 'editColumn', [
            'method_title' => 'カラム編集',
            'method_desc' => 'カラムの設定を行います。',
            'method_detail' => 'カラム名と型を指定してカラムを作成します。',
            'html_path' => 'user/questionnaires/editColumn/index.html',
            'img_args' => '[
                {"path": "user/questionnaires/editColumn/images/editColumn1",
                 "name": "項目の追加",
                 "comment": "<ul class=\"mb-0\"><li>項目の追加行で新しい項目を追加します。</li></ul>"
                },
                {"path": "user/questionnaires/editColumn/images/editColumn2",
                 "name": "複数の項目を設定した状態",
                 "comment": "<ul class=\"mb-0\"><li>アンケートを想定した項目を作りました。</li></ul>"
                }
            ]',
        ]);
    }

    /**
     * 項目の作成
     */
    private function editColumnOneQ(&$browser, $columns)
    {
        foreach ($columns as $column_name => $column_attr) {
            $browser->visit('/plugin/forms/editColumn/' . $this->test_frame->page_id . '/' . $this->test_frame->id . '#frame-' . $this->test_frame->id)
                    ->driver->executeScript('tinyMCE.get(0).setContent(\'' . $column_name . '\')');

            // executeScriptした後は一度、メソッドチェーンを切らないと、次のメソッドでエラーが出る（ワーニングとかが出ている？？）
            $browser->pause(500)
                    ->select("column_type", $column_attr["type"]);
            if ($column_attr["required"]) {
                $browser->check("required");
            }
            $browser->press('#button_submit_add_column');
        }
    }

    /**
     * 項目詳細の作成
     */
    private function editColumnDetailQ()
    {
        // 実行
        $this->browse(function (Browser $browser) {
            // ヤンバルクイナは好きですか？
            $col = FormsColumns::where('column_name', 'ヤンバルクイナは好きですか？')->first();
            $browser->visit('/')
                    ->visit('/plugin/forms/editColumnDetail/' . $this->test_frame->page_id . '/' . $this->test_frame->id . '/' . $col->id . '#div_column_selects')
                    ->type('select_name', '超好き')
                    ->press('#button_add_select')
                    ->pause(500);
            $browser->visit('/plugin/forms/editColumnDetail/' . $this->test_frame->page_id . '/' . $this->test_frame->id . '/' . $col->id . '#div_column_selects')
                    ->type('select_name', '超絶Love！')
                    ->press('#button_add_select')
                    ->pause(500);
            $browser->visit('/plugin/forms/editColumnDetail/' . $this->test_frame->page_id . '/' . $this->test_frame->id . '/' . $col->id . '#div_column_selects')
                    ->type('select_name', 'めっちゃ好きやねん')
                    ->press('#button_add_select')
                    ->pause(500);
            $browser->visit('/plugin/forms/editColumnDetail/' . $this->test_frame->page_id . '/' . $this->test_frame->id . '/' . $col->id . '#div_column_selects')
                    ->screenshot('user/questionnaires/editColumnDetail/images/editColumnDetailRadio1');

            // Connect-CMSは好きですか？
            $col = FormsColumns::where('column_name', 'Connect-CMSは好きですか？')->first();
            $browser->visit('/')
                    ->visit('/plugin/forms/editColumnDetail/' . $this->test_frame->page_id . '/' . $this->test_frame->id . '/' . $col->id . '#div_column_selects')
                    ->type('select_name', 'Like Like Like')
                    ->press('#button_add_select')
                    ->pause(500);
            $browser->visit('/plugin/forms/editColumnDetail/' . $this->test_frame->page_id . '/' . $this->test_frame->id . '/' . $col->id . '#div_column_selects')
                    ->type('select_name', 'もうずっと使っていく！')
                    ->press('#button_add_select')
                    ->pause(500);
            $browser->visit('/plugin/forms/editColumnDetail/' . $this->test_frame->page_id . '/' . $this->test_frame->id . '/' . $col->id . '#div_column_selects')
                    ->type('select_name', '開発にも参画するくらい好き')
                    ->press('#button_add_select')
                    ->pause(500);
            $browser->visit('/plugin/forms/editColumnDetail/' . $this->test_frame->page_id . '/' . $this->test_frame->id . '/' . $col->id . '#div_column_selects')
                    ->screenshot('user/questionnaires/editColumnDetail/images/editColumnDetailCheckbox1');
        });

        // マニュアルデータ
        $this->updateDusk('questionnaires', 'editColumnDetail', [
            'method_title' => 'カラムの詳細編集',
            'method_desc' => 'カラムの詳細設定を行います。',
            'method_detail' => '入力チェック、キャプションやプレースホルダなどを設定できます。<br />できることはフォームプラグインと共通ですので、フォームプラグインのページも参照してください。',
            'html_path' => 'user/questionnaires/editColumnDetail/index.html',
            'img_args' => '[
                {"path": "user/questionnaires/editColumnDetail/images/editColumnDetailRadio1",
                 "name": "作成１",
                 "comment": "<ul class=\"mb-0\"><li>ラジオボタンでの項目設定例です。</li></ul>"
                },
                {"path": "user/questionnaires/editColumnDetail/images/editColumnDetailCheckbox1",
                 "name": "作成１",
                 "comment": "<ul class=\"mb-0\"><li>チェックボックスでの項目設定例です。</li></ul>"
                }
            ]',
        ]);
    }
}
