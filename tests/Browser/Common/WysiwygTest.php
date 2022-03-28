<?php

namespace Tests\Browser\Common;

use Illuminate\Foundation\Testing\DatabaseMigrations;
use Laravel\Dusk\Browser;
use Tests\DuskTestCase;

use App\Models\Common\Buckets;
use App\Models\Common\Page;
use App\Models\Common\Frame;
use App\Models\Core\Dusks;
use App\Models\User\Contents\Contents;

/**
 * WYSIWYGテスト
 *
 * @see https://github.com/opensource-workshop/connect-cms/wiki/Dusk#テスト実行 [How to test]
 */
class WysiwygTest extends DuskTestCase
{
    private $content = null;
    private $frame = null;
    private $main_frame = null;

    /**
     * テストする関数の制御
     *
     * @group user
     * @see https://readouble.com/laravel/6.x/ja/dusk.html#running-tests
     */
    public function testInvoke()
    {
        $this->login(1);
        $this->init();

        $this->index();
        $this->decoration();
        $this->paragraph();
        $this->color();
        $this->table();
        $this->hr();
        $this->list();
        $this->indent();
        $this->link();
        $this->image();
        $this->file();
        $this->media();
        $this->preview();
        $this->source();
        $this->translate();
        $this->pdf();
        $this->face();

        $this->logout();
    }

    /**
     * 初期処理
     */
    private function init()
    {
        // データクリア
        $page = $this->firstOrCreatePage('/test/content');

        $frames = Frame::where('page_id', $page->id)->where('plugin_name', 'contents')->get();
        foreach ($frames as $frame) {
            $bucket = Buckets::find($frame->bucket_id);
            if (!empty($bucket)) {
                Contents::where('bucket_id', $bucket->id)->forceDelete();
                Buckets::find($bucket->id)->forceDelete();
            }
            $frame->forceDelete();
        }

        // 固定記事を作成
        $this->addPluginModal('contents', '/test/content', 2, false);
        $bucket = Buckets::create(['bucket_name' => 'WYSIWYGエディタ', 'plugin_name' => 'contents']);

        // 初めは記事は文字のみ。
        $this->content = Contents::create(['bucket_id' => $bucket->id, 'content_text' => '<p>WYSIWYGのテストです。</p>', 'status' => 0]);

        $this->frame = Frame::orderBy('id', 'desc')->first();
        $this->frame->update(['bucket_id' => $bucket->id, 'frame_title' => 'WYSIWYGエディタ']);
        $this->main_frame = $this->frame;  // 後で入れ替えて使うために一時保存

        // 外部サービス有効化
        $this->browse(function (Browser $browser) {
            $browser->visit('/manage/service')
                    ->assertTitleContains('Connect-CMS')
                    ->click('#lavel_use_translate_1')
                    ->click('#label_use_pdf_thumbnail_1')
                    ->click('#label_use_face_ai_1')
                    ->press('更新');
        });

        // 最初にマニュアルの順番確定用にメソッドを指定する。
        $this->reserveManual('index', 'decoration', 'paragraph', 'color', 'table', 'hr', 'list', 'indent', 'link', 'image', 'file', 'media', 'preview', 'source');
    }

    /**
     * マニュアルレコードの取得
     */
    private function getDuskBody($method_name, $method_title, $method_desc, $method_detail, $html_path, $img_args)
    {
        return [
            'category' => 'common',
            'sort' => 2,
            'plugin_name' => 'wysiwyg',
            'plugin_title' => 'WYSIWYG',
            'plugin_desc' => 'WYSIWYG機能で記事を編集できます。',
            'method_name' => $method_name,
            'method_title' => $method_title,
            'method_desc' => $method_desc,
            'method_detail' => $method_detail,
            'html_path' => $html_path,
            'img_args' => $img_args,
            'test_result' => 'OK'
        ];
    }

    /**
     * WYSIWYG
     */
    private function index()
    {
        // ログイン画面
        $this->browse(function (Browser $browser) {
            $frame = Frame::orderBy('id', 'desc')->first();

            // 固定記事のページを開く
            $browser->visit('/plugin/contents/edit/' . $frame->page_id . '/' . $frame->id . '/' . $this->content->id . '#frame-' . $frame->id)
                    ->pause(500)
                    ->assertPathBeginsWith('/')
                    ->screenshot('common/wysiwyg/index/images/index');
        });

        // マニュアル用データ出力
        $dusk = Dusks::putManualData(
            ['html_path' => 'common/wysiwyg/index/index.html'],
            $this->getDuskBody(
                'index',
                'WYSIWYGエディタ',
                'WYSIWYGエディタによる記事の編集が可能です。',
                '文字の装飾や表の作成、画像・ファイルの挿入など、記事を編集できるWYSIWYGエディタを説明します。',
                'common/wysiwyg/index/index.html',
                '[
                     {"path": "common/wysiwyg/index/images/index",
                      "name": "WYSIWYGエディタ",
                      "comment": "<ul class=\"mb-0\"><li>各ボタンの機能は、それぞれのページで確認してください。</li></ul>"
                     }
                 ]'
            )
        );
    }

    /**
     * 文字の装飾
     */
    private function decoration()
    {
        // 画面
        $this->browse(function (Browser $browser) {
            // index と同じ画像を使用する。挿入HTML でボタンを説明する。
        });

        // マニュアル用データ出力
        $dusk = Dusks::putManualData(
            ['html_path' => 'common/wysiwyg/decoration/index.html'],
            $this->getDuskBody(
                'decoration',
                '文字の装飾',
                '太字や下線などの文字の装飾ができます。編集内容を戻すアンドゥも使えます。',
                '',
                'common/wysiwyg/decoration/index.html',
                '[
                     {"path": "common/wysiwyg/index/images/index",
                      "name": "文字の装飾",
                      "comment": "<ul class=\"mb-0\"><li>文字の装飾に関係する機能を以下で説明します。</li></ul>"
                     }
                 ]'
            )
        );
    }

    /**
     * 書式（段落）
     */
    private function paragraph()
    {
        // 画面
        $this->browse(function (Browser $browser) {
            // 段落
            $browser->visit('/plugin/contents/edit/' . $this->frame->page_id . '/' . $this->frame->id . '/' . $this->content->id . '#frame-' . $this->frame->id)
                    ->click('#ccMainArea .tox-tinymce .tox-toolbar__group:nth-child(3) button')
                    ->pause(500)
                    ->screenshot('common/wysiwyg/paragraph/images/paragraph');
        });

        // マニュアル用データ出力
        $dusk = Dusks::putManualData(
            ['html_path' => 'common/wysiwyg/paragraph/index.html'],
            $this->getDuskBody(
                'paragraph',
                '書式（段落）',
                '文字の書式やリンクのPDFアイコンなどの設定ができます。',
                '',
                'common/wysiwyg/paragraph/index.html',
                '[
                     {"path": "common/wysiwyg/paragraph/images/paragraph",
                      "name": "書式（段落）",
                      "comment": "<ul class=\"mb-0\"><li>文字の書式やリンクのPDFアイコンなどの設定ができます。</li></ul>"
                     }
                 ]'
            )
        );
    }

    /**
     * 色
     */
    private function color()
    {
        // 画面
        $this->browse(function (Browser $browser) {
            // 文字色のカラーパネル
            $browser->visit('/plugin/contents/edit/' . $this->frame->page_id . '/' . $this->frame->id . '/' . $this->content->id . '#frame-' . $this->frame->id)
                    ->click('#ccMainArea .tox-tinymce .tox-toolbar__group:nth-child(4) div:nth-child(1) span:nth-child(2)')
                    ->pause(500)
                    ->screenshot('common/wysiwyg/color/images/colorPickerFont');

            // 背景色のカラーパネル
            $browser->click('#ccMainArea .tox-tinymce .tox-toolbar__group:nth-child(4) div:nth-child(2) span:nth-child(2)')
                    ->pause(500)
                    ->screenshot('common/wysiwyg/color/images/colorPickerBackground');
        });

        // マニュアル用データ出力
        $dusk = Dusks::putManualData(
            ['html_path' => 'common/wysiwyg/color/index.html'],
            $this->getDuskBody(
                'color',
                '文字色・背景色',
                '文字の書式やリンクのPDFアイコンなどの設定ができます。',
                '',
                'common/wysiwyg/color/index.html',
                '[
                     {"path": "common/wysiwyg/color/images/colorPickerFont",
                      "name": "文字色",
                      "comment": "<ul class=\"mb-0\"><li>文字に色を付けることができます。</li></ul>"
                     },
                     {"path": "common/wysiwyg/color/images/colorPickerBackground",
                      "name": "背景色",
                      "comment": "<ul class=\"mb-0\"><li>文字の背景に色を付けることができます。</li></ul>"
                     }
                 ]'
            )
        );
    }

    /**
     * 表
     */
    private function table()
    {
        // 固定記事の配置
        $this->frame = $this->addContents('/test/content', '', ['title' => '表']);

        // 画面
        $this->browse(function (Browser $browser) {
            // 表で使うContents を取得
            $content = Contents::where('bucket_id', $this->frame->bucket_id)->first();

            // 表に合わせた記事に変更
            $content_text = '<table border="1" class="table"><tbody>';
            for ($i = 0; $i < 2; $i++) {
                $content_text .= '<tr><td>A</td><td>B</td><td>C</td></tr>';
            }
            $content_text .= '</tbody></table>';
            $content->content_text = $content_text;
            $content->save();

            // 記事をデータベースから変更したので、一度開きなおす。
            $browser->visit('/plugin/contents/edit/' . $this->frame->page_id . '/' . $this->frame->id . '/' . $content->id . '#frame-' . $this->frame->id);

            // 表のドロップダウン
            $browser->click('#ccMainArea .tox-tinymce .tox-toolbar__group:nth-child(6) button:nth-child(1) div')
                    ->pause(500)
                    ->screenshot('common/wysiwyg/table/images/table');

            // 表のドロップダウン ＞ 表
            //$browser->click('.tox-collection__group:nth-child(1)')
            $browser->mouseover('.tox-collection__group:nth-child(1)')
                    ->pause(500)
                    ->screenshot('common/wysiwyg/table/images/table_insert');

            // 表のドロップダウン ＞ セル
            $browser->mouseover('.tox-collection__group:nth-child(2) .tox-collection__item:nth-child(1)')
                    ->pause(500)
                    ->screenshot('common/wysiwyg/table/images/table_cell');

            // 表のドロップダウン ＞ セル ＞ セルの詳細設定
            $browser->click('.tox-collection--list:nth-child(2) .tox-collection__item:nth-child(1)')
                    ->pause(500)
                    ->screenshot('common/wysiwyg/table/images/table_cell_detail');

            // 表のドロップダウン ＞ セル ＞ セルの詳細設定 ＞ 詳細設定
            $browser->click('.tox-tab:nth-child(2)')
                    ->pause(500)
                    ->screenshot('common/wysiwyg/table/images/table_cell_detail_detail');

            // 一度開きなおす。
            $browser->visit('/plugin/contents/edit/' . $this->frame->page_id . '/' . $this->frame->id . '/' . $content->id . '#frame-' . $this->frame->id);

            // 表のドロップダウン
            $browser->click('#ccMainArea .tox-tinymce .tox-toolbar__group:nth-child(6) button:nth-child(1) div')
                    ->pause(500);

            // 表のドロップダウン ＞ 行
            $browser->mouseover('.tox-collection__group:nth-child(2) .tox-collection__item:nth-child(2)')
                    ->pause(500)
                    ->screenshot('common/wysiwyg/table/images/table_row');

            // 表のドロップダウン ＞ 行 ＞ 行の詳細設定
            $browser->click('.tox-collection--list:nth-child(2) .tox-collection__item:nth-child(4)')
                    ->pause(500)
                    ->screenshot('common/wysiwyg/table/images/table_row_detail');

            // 表のドロップダウン ＞ 行 ＞ 行の詳細設定 ＞ 詳細設定
            $browser->click('.tox-tab:nth-child(2)')
                    ->pause(500)
                    ->screenshot('common/wysiwyg/table/images/table_row_detail_detail');

            // 一度開きなおす。
            $browser->visit('/plugin/contents/edit/' . $this->frame->page_id . '/' . $this->frame->id . '/' . $content->id . '#frame-' . $this->frame->id);

            // 表のドロップダウン
            $browser->click('#ccMainArea .tox-tinymce .tox-toolbar__group:nth-child(6) button:nth-child(1) div')
                    ->pause(500);

            // 表のドロップダウン ＞ 列
            $browser->mouseover('.tox-collection__group:nth-child(2) .tox-collection__item:nth-child(3)')
                    ->pause(500)
                    ->screenshot('common/wysiwyg/table/images/table_col');

            // 表のドロップダウン ＞ 表の詳細設定
            $browser->mouseover('.tox-collection__group:nth-child(3) .tox-collection__item:nth-child(1)')
                    ->pause(500)
                    ->screenshot('common/wysiwyg/table/images/table_detail_mouse');

            $browser->click('.tox-collection__group:nth-child(3) .tox-collection__item:nth-child(1)')
                    ->pause(500)
                    ->screenshot('common/wysiwyg/table/images/table_detail_click');

            // 表のドロップダウン ＞ 表の詳細設定 ＞ 詳細設定
            $browser->click('.tox-tab:nth-child(2)')
                    ->pause(500)
                    ->screenshot('common/wysiwyg/table/images/table_detail_detail');
        });

        // マニュアル用データ出力
        $dusk = Dusks::putManualData(
            ['html_path' => 'common/wysiwyg/table/index.html'],
            $this->getDuskBody(
                'table',
                '表の挿入・編集',
                '表の挿入や設定ができます。',
                '※ セルの幅指定など一部機能について、スマホでの自動幅調整を優先しているために機能しないものがあります。',
                'common/wysiwyg/table/index.html',
                '[
                     {"path": "common/wysiwyg/table/images/table",
                      "name": "表メニュー",
                      "comment": "<ul class=\"mb-0\"><li>表に関するいくつかのメニューがあります。</li></ul>"
                     },
                     {"path": "common/wysiwyg/table/images/table_insert",
                      "name": "表作成",
                      "comment": "<ul class=\"mb-0\"><li>表メニューから表を作成できます。</li></ul>"
                     },
                     {"path": "common/wysiwyg/table/images/table_cell",
                      "name": "セルメニュー",
                      "comment": "<ul class=\"mb-0\"><li>セルに関するメニューです。</li></ul>"
                     },
                     {"path": "common/wysiwyg/table/images/table_cell_detail",
                      "name": "セルの詳細設定（一般）",
                      "comment": "<ul class=\"mb-0\"><li>セルタイプなどを設定できます。</li></ul>"
                     },
                     {"path": "common/wysiwyg/table/images/table_cell_detail_detail",
                      "name": "セルの詳細設定（詳細設定）",
                      "comment": "<ul class=\"mb-0\"><li>セルの枠線などを設定できます。</li></ul>"
                     },
                     {"path": "common/wysiwyg/table/images/table_row",
                      "name": "行メニュー",
                      "comment": "<ul class=\"mb-0\"><li>行に関するメニューです。</li></ul>"
                     },
                     {"path": "common/wysiwyg/table/images/table_row_detail",
                      "name": "行の詳細設定（一般）",
                      "comment": "<ul class=\"mb-0\"><li>行タイプなどを設定できます。</li></ul>"
                     },
                     {"path": "common/wysiwyg/table/images/table_row_detail_detail",
                      "name": "行の詳細設定（詳細設定）",
                      "comment": "<ul class=\"mb-0\"><li>行の枠線などを設定できます。</li></ul>"
                     },
                     {"path": "common/wysiwyg/table/images/table_col",
                      "name": "列メニュー",
                      "comment": "<ul class=\"mb-0\"><li>列に関するメニューです。</li></ul>"
                     },
                     {"path": "common/wysiwyg/table/images/table_detail_mouse",
                      "name": "表の詳細設定を開きます。",
                      "comment": "<ul class=\"mb-0\"><li>ここから表全体の詳細設定画面を開きます。</li></ul>"
                     },
                     {"path": "common/wysiwyg/table/images/table_detail_click",
                      "name": "表の詳細設定（一般）",
                      "comment": "<ul class=\"mb-0\"><li>配置やクラスを設定できます。</li></ul>"
                     },
                     {"path": "common/wysiwyg/table/images/table_detail_detail",
                      "name": "表の詳細設定（詳細設定）",
                      "comment": "<ul class=\"mb-0\"><li>表の枠線などを設定できます。</li></ul>"
                     }
                 ]'
            )
        );
    }

    /**
     * 罫線
     */
    private function hr()
    {
        // 使用する固定記事の入れ替え
        $this->frame = $this->main_frame;

        // 画面
        $this->browse(function (Browser $browser) {
            // WYSIWYG 記事のクリア
            $this->content->content_text = "";
            $this->content->save();

            // 段落
            $browser->visit('/plugin/contents/edit/' . $this->frame->page_id . '/' . $this->frame->id . '/' . $this->content->id . '#frame-' . $this->frame->id)
                    ->click('#ccMainArea .tox-tinymce .tox-toolbar__group:nth-child(6) button:nth-child(2)')
                    ->pause(500)
                    ->screenshot('common/wysiwyg/hr/images/hr');
        });

        // マニュアル用データ出力
        $dusk = Dusks::putManualData(
            ['html_path' => 'common/wysiwyg/hr/index.html'],
            $this->getDuskBody(
                'hr',
                '罫線',
                '文章にHTMLの罫線を追加できます。',
                '',
                'common/wysiwyg/hr/index.html',
                '[
                     {"path": "common/wysiwyg/hr/images/hr",
                      "name": "罫線",
                      "comment": "<ul class=\"mb-0\"><li>文章にHTMLの罫線を追加できます。</li></ul>"
                     }
                 ]'
            )
        );
    }

    /**
     * リスト
     */
    private function list()
    {
        // 画面
        $this->browse(function (Browser $browser) {
            // リスト（箇条書き UL）
            $browser->visit('/plugin/contents/edit/' . $this->frame->page_id . '/' . $this->frame->id . '/' . $this->content->id . '#frame-' . $this->frame->id)
                    ->click('#ccMainArea .tox-tinymce .tox-toolbar__group:nth-child(7) div:nth-child(1) span:nth-child(2)')
                    ->pause(500)
                    ->screenshot('common/wysiwyg/list/images/list_ul');

            // リスト（番号付き箇条書き OL）
            $browser->visit('/plugin/contents/edit/' . $this->frame->page_id . '/' . $this->frame->id . '/' . $this->content->id . '#frame-' . $this->frame->id)
                    ->click('#ccMainArea .tox-tinymce .tox-toolbar__group:nth-child(7) div:nth-child(2) span:nth-child(2)')
                    ->pause(500)
                    ->screenshot('common/wysiwyg/list/images/list_ol');
        });

        // マニュアル用データ出力
        $dusk = Dusks::putManualData(
            ['html_path' => 'common/wysiwyg/list/index.html'],
            $this->getDuskBody(
                'list',
                'リスト',
                '文章にHTMLのリスト（箇条書き）を追加できます。',
                '',
                'common/wysiwyg/list/index.html',
                '[
                     {"path": "common/wysiwyg/list/images/list_ul",
                      "name": "箇条書き（UL）",
                      "comment": "<ul class=\"mb-0\"><li>文章にHTMLの箇条書きを追加できます。</li></ul>"
                     },
                     {"path": "common/wysiwyg/list/images/list_ol",
                      "name": "番号付き箇条書き（OL）",
                      "comment": "<ul class=\"mb-0\"><li>文章にHTMLの番号付き箇条書きを追加できます。</li></ul>"
                     }
                 ]'
            )
        );
    }

    /**
     * インデント
     */
    private function indent()
    {
        // 画面
        $this->browse(function (Browser $browser) {
            // WYSIWYG 記事のクリア
            $this->content->content_text = '<p>WYSIWYGのテストです。</p>';
            $this->content->save();

            // ブロッククオート
            $browser->visit('/plugin/contents/edit/' . $this->frame->page_id . '/' . $this->frame->id . '/' . $this->content->id . '#frame-' . $this->frame->id)
                    ->click('#ccMainArea .tox-tinymce .tox-toolbar__group:nth-child(8) button')
                    ->pause(500)
                    ->screenshot('common/wysiwyg/indent/images/blockquote');

            // 真ん中寄せ
            $browser->visit('/plugin/contents/edit/' . $this->frame->page_id . '/' . $this->frame->id . '/' . $this->content->id . '#frame-' . $this->frame->id)
                    ->click('#ccMainArea .tox-tinymce .tox-toolbar__group:nth-child(9) button:nth-child(2)')
                    ->pause(500)
                    ->screenshot('common/wysiwyg/indent/images/text_center');
        });

        // マニュアル用データ出力
        $dusk = Dusks::putManualData(
            ['html_path' => 'common/wysiwyg/indent/index.html'],
            $this->getDuskBody(
                'indent',
                'インデント関係',
                '文章を右寄せやインデントできます。',
                'このグループの一通りのアイコンの説明は下に示します。',
                'common/wysiwyg/indent/index.html',
                '[
                     {"path": "common/wysiwyg/indent/images/blockquote",
                      "name": "ブロッククオート",
                      "comment": "<ul class=\"mb-0\"><li>標準のデザインでは、通常の文字より大きめになります。</li></ul>"
                     },
                     {"path": "common/wysiwyg/indent/images/text_center",
                      "name": "中央寄せ",
                      "comment": "<ul class=\"mb-0\"><li>文字を中央寄せにします。</li></ul>"
                     }
                 ]'
            )
        );
    }

    /**
     * リンク
     */
    private function link()
    {
        // 画面
        $this->browse(function (Browser $browser) {
            $browser->visit('/plugin/contents/edit/' . $this->frame->page_id . '/' . $this->frame->id . '/' . $this->content->id . '#frame-' . $this->frame->id)
                    ->click('#ccMainArea .tox-tinymce .tox-toolbar__group:nth-child(11) button')
                    ->pause(500)
                    ->screenshot('common/wysiwyg/link/images/link');
        });

        // マニュアル用データ出力
        $dusk = Dusks::putManualData(
            ['html_path' => 'common/wysiwyg/link/index.html'],
            $this->getDuskBody(
                'link',
                'リンク',
                '文字や画像にリンクを設定できます。',
                '',
                'common/wysiwyg/link/index.html',
                '[
                     {"path": "common/wysiwyg/link/images/link",
                      "name": "リンク",
                      "comment": "<ul class=\"mb-0\"><li>リンク先URLやリンクの開き方を設定できます。</li></ul>"
                     }
                 ]'
            )
        );
    }

    /**
     * 画像の挿入
     */
    private function image()
    {
        // 画面
        $this->browse(function (Browser $browser) {
            $browser->visit('/plugin/contents/edit/' . $this->frame->page_id . '/' . $this->frame->id . '/' . $this->content->id . '#frame-' . $this->frame->id)
                    ->click('#ccMainArea .tox-tinymce .tox-toolbar__group:nth-child(12) button:nth-child(1)')
                    ->pause(500)
                    ->screenshot('common/wysiwyg/image/images/image');
        });

        // マニュアル用データ出力
        $dusk = Dusks::putManualData(
            ['html_path' => 'common/wysiwyg/image/index.html'],
            $this->getDuskBody(
                'image',
                '画像の挿入・編集',
                '画像をアップロードできます。アップロード時に自動サイズ変更が可能です。',
                '',
                'common/wysiwyg/image/index.html',
                '[
                     {"path": "common/wysiwyg/image/images/image",
                      "name": "画像の挿入・編集",
                      "comment": "<ul class=\"mb-0\"><li>画像をアップロードできます。アップロード時に自動サイズ変更が可能です。</li></ul>"
                     }
                 ]'
            )
        );
    }

    /**
     * ファイル
     */
    private function file()
    {
        // 画面
        $this->browse(function (Browser $browser) {
            $browser->visit('/plugin/contents/edit/' . $this->frame->page_id . '/' . $this->frame->id . '/' . $this->content->id . '#frame-' . $this->frame->id)
                    ->click('#ccMainArea .tox-tinymce .tox-toolbar__group:nth-child(12) button:nth-child(2)')
                    ->pause(500)
                    ->screenshot('common/wysiwyg/file/images/file');
        });

        // マニュアル用データ出力
        $dusk = Dusks::putManualData(
            ['html_path' => 'common/wysiwyg/file/index.html'],
            $this->getDuskBody(
                'file',
                'ファイルアップロード',
                'ファイルをアップロードできます。',
                'アップロードしたファイルには自動的にリンクが設定されます。',
                'common/wysiwyg/file/index.html',
                '[
                     {"path": "common/wysiwyg/file/images/file",
                      "name": "ファイルアップロード",
                      "comment": "<ul class=\"mb-0\"><li>5ファイルまで一気にアップロードできます。</li></ul>"
                     }
                 ]'
            )
        );
    }

    /**
     * メディア
     */
    private function media()
    {
        // 画面
        $this->browse(function (Browser $browser) {
            $browser->visit('/plugin/contents/edit/' . $this->frame->page_id . '/' . $this->frame->id . '/' . $this->content->id . '#frame-' . $this->frame->id)
                    ->click('#ccMainArea .tox-tinymce .tox-toolbar__group:nth-child(12) button:nth-child(3)')
                    ->pause(500)
                    ->screenshot('common/wysiwyg/media/images/media');

            // 埋め込み
            $browser->click('.tox-tab:nth-child(2)')
                    ->pause(500)
                    ->screenshot('common/wysiwyg/media/images/embed');

            // ポスター
            $browser->click('.tox-tab:nth-child(3)')
                    ->pause(500)
                    ->screenshot('common/wysiwyg/media/images/poster');
        });

        // マニュアル用データ出力
        $dusk = Dusks::putManualData(
            ['html_path' => 'common/wysiwyg/media/index.html'],
            $this->getDuskBody(
                'media',
                'メディア',
                '動画や音声ファイルをアップロードできます。',
                'アップロードしたファイルには自動的にプレーヤーが設定されます。',
                'common/wysiwyg/media/index.html',
                '[
                     {"path": "common/wysiwyg/media/images/media",
                      "name": "メディアアップロード",
                      "comment": "<ul class=\"mb-0\"><li>動画や音声を指定してアップロードできます。</li></ul>"
                     },
                     {"path": "common/wysiwyg/media/images/embed",
                      "name": "埋め込み",
                      "comment": "<ul class=\"mb-0\"><li>Youtubeなどの埋め込みコードを登録する画面です。</li></ul>"
                     },
                     {"path": "common/wysiwyg/media/images/poster",
                      "name": "詳細設定",
                      "comment": "<ul class=\"mb-0\"><li>動画の場合のポスター画像を登録する画面です。</li></ul>"
                     }
                 ]'
            )
        );
    }

    /**
     * プレビュー
     */
    private function preview()
    {
        // 画面
        $this->browse(function (Browser $browser) {
            $browser->visit('/plugin/contents/edit/' . $this->frame->page_id . '/' . $this->frame->id . '/' . $this->content->id . '#frame-' . $this->frame->id)
                    ->click('#ccMainArea .tox-tinymce .tox-toolbar__group:nth-child(13) button')
                    ->pause(500)
                    ->screenshot('common/wysiwyg/preview/images/preview');
        });

        // マニュアル用データ出力
        $dusk = Dusks::putManualData(
            ['html_path' => 'common/wysiwyg/preview/index.html'],
            $this->getDuskBody(
                'preview',
                'プレビュー',
                'WYSIWYGで記述中の内容をプレビュー表示できます。',
                '',
                'common/wysiwyg/preview/index.html',
                '[{"path": "common/wysiwyg/preview/images/preview", "name": "プレビュー"}]'
            )
        );
    }

    /**
     * HTMLソース
     */
    private function source()
    {
        // 画面
        $this->browse(function (Browser $browser) {
            $browser->visit('/plugin/contents/edit/' . $this->frame->page_id . '/' . $this->frame->id . '/' . $this->content->id . '#frame-' . $this->frame->id)
                    ->click('#ccMainArea .tox-tinymce .tox-toolbar__group:nth-child(14) button')
                    ->pause(500)
                    ->screenshot('common/wysiwyg/source/images/source');
        });

        // マニュアル用データ出力
        $dusk = Dusks::putManualData(
            ['html_path' => 'common/wysiwyg/source/index.html'],
            $this->getDuskBody(
                'source',
                'HTMLソース',
                'WYSIWYGで記述中の内容のHTMLを表示したり、HTMLで編集できます。',
                '',
                'common/wysiwyg/source/index.html',
                '[{"path": "common/wysiwyg/source/images/source", "name": "HTMLソース"}]'
            )
        );
    }

    /**
     * 翻訳
     */
    private function translate()
    {
        if (!config('connect.TRANSLATE_API_URL')) {
            $this->fail('.env.dusk.localのTRANSLATE_API_URLが空');
        }

        // 画面
        $this->browse(function (Browser $browser) {
            // 編集画面を開き、WYSIWYGエディタのセレクタをターゲットにCTRL＋Aキーを押して、全選択させる。その後に翻訳プラグインを起動することで、翻訳するテキストが選択されている状態。
            $browser->visit('/plugin/contents/edit/' . $this->frame->page_id . '/' . $this->frame->id . '/' . $this->content->id . '#frame-' . $this->frame->id)
                    ->keys('#mce_0_ifr', ['{control}', 'a'])
                    ->click('#ccMainArea .tox-tinymce .tox-toolbar__group:nth-child(15) button:nth-child(1)')
                    ->pause(500)
                    ->screenshot('common/wysiwyg/translate/images/translate');

            // 2022-03-20 翻訳API の許可がIP アドレス指定になっているので、マニュアル用にデータ編集方式で進める。
            //$browser->press('翻訳')
            //        ->pause(500)
            //        ->screenshot('common/wysiwyg/translate/images/translate2');

            // 表に合わせた記事に変更
            $content_text = '<p>WYSIWYGのテストです。<br />This is a test for WYSIWYG.</p>';
            $this->content->content_text = $content_text;
            $this->content->save();

            $browser->visit('/plugin/contents/edit/' . $this->frame->page_id . '/' . $this->frame->id . '/' . $this->content->id . '#frame-' . $this->frame->id)
                    ->screenshot('common/wysiwyg/translate/images/translate2');
        });

        // マニュアル用データ出力
        $dusk = Dusks::putManualData(
            ['html_path' => 'common/wysiwyg/translate/index.html'],
            $this->getDuskBody(
                'translate',
                '翻訳',
                '文章を他の言語に翻訳できます。',
                '翻訳を使用するには、外部サービス設定が必要です。<br />記載した文章を選択してからボタンをクリックすると、選択したものが初期値で入力されます。',
                'common/wysiwyg/translate/index.html',
                '[
                     {"path": "common/wysiwyg/translate/images/translate",
                      "name": "翻訳",
                      "comment": "<ul class=\"mb-0\"><li>2022-02-23時点では、英語、スペイン語、フランス語、ドイツ語、ポルトガル語、中国語（簡体字）、中国語（繁体字）、韓国語、タガログ語、ベトナム語があります。</li><li>翻訳言語は必要に応じて追加します。</li></ul>"
                     },
                     {"path": "common/wysiwyg/translate/images/translate2",
                      "name": "翻訳結果",
                      "comment": "<ul class=\"mb-0\"><li>選択した内容が翻訳されて、元のテキストの下に追記されます。</li></ul>"
                     }
                 ]'
            )
        );
    }

    /**
     * PDF
     */
    private function pdf()
    {
        if (!config('connect.PDF_THUMBNAIL_API_URL')) {
            $this->fail('.env.dusk.localのPDF_THUMBNAIL_API_URLが空');
        }

        // 画面
        $this->browse(function (Browser $browser) {
            $browser->visit('/plugin/contents/edit/' . $this->frame->page_id . '/' . $this->frame->id . '/' . $this->content->id . '#frame-' . $this->frame->id)
                    ->click('#ccMainArea .tox-tinymce .tox-toolbar__group:nth-child(15) button:nth-child(2)')
                    ->pause(500)
                    ->screenshot('common/wysiwyg/pdf/images/pdf1');

            // PDFアップロード後に合わせた記事に変更
            list($upload, $thumbnail1) = $this->fileUpload(__DIR__.'/wysiwyg/Upload_PDF.pdf', 'Upload_PDF.pdf', 'application/pdf', 'pdf', 'contents', $this->frame->page_id, __DIR__.'/wysiwyg/Upload_PDF_thumbnail1.png');
            $thumbnail2 = $this->fileUpload(__DIR__.'/wysiwyg/Upload_PDF_thumbnail2.png', 'Upload_PDF_thumbnail2.png', 'image/png', 'png', 'contents', $this->frame->page_id);

            $content_text  = '<p>WYSIWYGのテストです。<br />This is a test for WYSIWYG.</p>';
            $content_text .= '<p><a href="/file/' . $upload->id . '" target="_blank" rel="noopener">Upload_PDF.pdf</a><br /><a href="/file/' . $upload->id . '" target="_blank" rel="noopener"><img src="/file/' . $thumbnail1->id . '" width="150" class="img-fluid img-thumbnail" alt="Upload_PDF.pdfの1ページ目のサムネイル" /></a> <a href="/file/' . $upload->id . '" target="_blank" rel="noopener"><img src="/file/' . $thumbnail2->id . '" width="150" class="img-fluid img-thumbnail" alt="Upload_PDF.pdfの2ページ目のサムネイル" /></a></p>';
            $this->content->content_text = $content_text;
            $this->content->save();

            // 編集画面開きなおし
            $browser->visit('/plugin/contents/edit/' . $this->frame->page_id . '/' . $this->frame->id . '/' . $this->content->id . '#frame-' . $this->frame->id)
                    ->pause(500)
                    ->screenshot('common/wysiwyg/pdf/images/pdf2');

            // 表示画面
            $this->logout();
            $browser->visit('/test/content'. '#frame-' . $this->frame->id)
                    ->screenshot('common/wysiwyg/pdf/images/pdf3');
            $this->login(1);

            // TINYMCE のファイルアップロードにうまくファイル指定できない。
            // 以下で見た目は設定できてそうだが、うまく動かなかった。
            //$browser->screenshot('common/wysiwyg/pdf/images/pdf2')
            //        ->type('.tox-textfield:nth-child(1)', 'Upload_PDF.pdf')
            //        ->attach('#cc-pdf-upload-' . $this->frame->id , __DIR__.'/wysiwyg/Upload_PDF.pdf')
            //        ->pause(500)
            //        ->press('サムネイル作成')
            //        ->pause(500)
            //        ->screenshot('common/wysiwyg/pdf/images/pdf3');

            //$browser->press('サムネイル作成')
            //        ->pause(500)
            //        ->screenshot('common/wysiwyg/pdf/images/pdf2');

            // press('サムネイル作成') はうまく行かなかったが、'.tox-dialog__footer button:nth-child(2)' はclink できた。
            // attach すれば、ボタンも動かなくなる。
            //$browser->attach('#cc-pdf-upload-' . $this->frame->id , __DIR__.'/wysiwyg/Upload_PDF.pdf')
            //        ->click('.tox-dialog__footer button:nth-child(2)')
            //        ->pause(500)
            //        ->screenshot('common/wysiwyg/pdf/images/pdf2');

            //$browser->script('your js');
            //$browser->value('#cc-pdf-upload-' . $this->frame->id , __DIR__.'/wysiwyg/Upload_PDF.pdf')
            //        ->click('.tox-dialog__footer button:nth-child(2)')
            //        ->pause(500)
            //        ->screenshot('common/wysiwyg/pdf/images/pdf2');
        });

        // マニュアル用データ出力
        $dusk = Dusks::putManualData(
            ['html_path' => 'common/wysiwyg/pdf/index.html'],
            $this->getDuskBody(
                'pdf',
                'PDFアップロード',
                'PDFをアップロードして、自動的にサムネイルを作成します。',
                'PDFアップロードを使用するには、外部サービス設定が必要です。<br />アップロードしたPDFから、サムネイルを自動で生成し、サムネイルからもリンクします。サムネイルの大きさやサムネイルを生成するページ数も指定できます。',
                'common/wysiwyg/pdf/index.html',
                '[
                     {"path": "common/wysiwyg/pdf/images/pdf1",
                      "name": "PDFアップロード",
                      "comment": "<ul class=\"mb-0\"><li>パスワード付PDFの場合は、パスワードも入力してください。</li></ul>"
                     },
                     {"path": "common/wysiwyg/pdf/images/pdf2",
                      "name": "PDFアップロード後",
                      "comment": "<ul class=\"mb-0\"><li>サムネイルを作成するページ数も指定できます。ここでは、全2ページで全て作成した例です。</li></ul>"
                     },
                     {"path": "common/wysiwyg/pdf/images/pdf3",
                      "name": "表示画面",
                      "comment": "<ul class=\"mb-0\"><li>PDFファイルへのファイル名でのリンク、サムネイルからのリンクが表示されます。</li></ul>"
                     }
                 ]'
            )
        );
    }

    /**
     * AI顔認識
     */
    private function face()
    {
        if (!config('connect.FACE_AI_API_URL')) {
            $this->fail('.env.dusk.localのFACE_AI_API_URLが空');
        }

        // 固定記事の配置
        $this->frame = $this->addContents('/test/content', '', ['title' => 'AI顔認識']);
        $content = Contents::where('bucket_id', $this->frame->bucket_id)->first();

        // Dusk で操作できないので、マニュアル用にデータ生成
        $upload = $this->fileUpload(__DIR__.'/wysiwyg/face_and_dog.jpg', 'face_and_dog.jpg', 'image/jpeg', 'jpg', 'contents', $this->frame->page_id);
        $content->content_text = '<p><img src="/file/' . $upload->id . '" class="img-fluid" alt="" /></p>';
        $content->save();

        // 画面
        $this->browse(function (Browser $browser) use ($content) {
            // 変換前の画像（正面：人＆犬）
            $this->logout();
            $browser->visit('/test/content'. '#frame-' . $this->frame->id)
                    ->screenshot('common/wysiwyg/face/images/face1');
            $this->login(1);

            // AI顔認識のダイアログ起動
            $browser->visit('/plugin/contents/edit/' . $this->frame->page_id . '/' . $this->frame->id . '/' . $content->id . '#frame-' . $this->frame->id)
                    ->click('#ccMainArea .tox-tinymce .tox-toolbar__group:nth-child(15) button:nth-child(3)')
                    ->pause(500)
                    ->screenshot('common/wysiwyg/face/images/face2');

            // Dusk で操作できないので、マニュアル用にデータ生成
            $upload2 = $this->fileUpload(__DIR__.'/wysiwyg/face_and_dog_mosaic.jpg', 'face_and_dog_mosaic.jpg', 'image/jpeg', 'jpg', 'contents', $this->frame->page_id);
            $content->content_text = '<p><img src="/file/' . $upload2->id . '" class="img-fluid" alt="" /></p>' . "\n" . $content->content_text;
            $content->save();

            // 変換後の画像（正面：人＆犬）
            $this->logout();
            $browser->visit('/test/content'. '#frame-' . $this->frame->id)
                    ->screenshot('common/wysiwyg/face/images/face3');
            $this->login(1);

            // Dusk で操作できないので、マニュアル用にデータ生成
            $upload3 = $this->fileUpload(__DIR__.'/wysiwyg/face_profile.jpg', 'face_profile.jpg', 'image/jpeg', 'jpg', 'contents', $this->frame->page_id);
            $content->content_text = '<p><img src="/file/' . $upload3->id . '" class="img-fluid" alt="" /></p>' . "\n" . $content->content_text;
            $content->save();

            // 変換前の画像（横：人）
            $this->logout();
            $browser->visit('/test/content'. '#frame-' . $this->frame->id)
                    ->screenshot('common/wysiwyg/face/images/face4');
            $this->login(1);

            // Dusk で操作できないので、マニュアル用にデータ生成
            $upload4 = $this->fileUpload(__DIR__.'/wysiwyg/face_profile_mosaic.jpg', 'face_profile_mosaic.jpg', 'image/jpeg', 'jpg', 'contents', $this->frame->page_id);
            $content->content_text = '<p><img src="/file/' . $upload4->id . '" class="img-fluid" alt="" /></p>' . "\n" . $content->content_text;
            $content->save();

            // 変換後の画像（横：人）
            $this->logout();
            $browser->visit('/test/content'. '#frame-' . $this->frame->id)
                    ->screenshot('common/wysiwyg/face/images/face5');
            $this->login(1);
        });

        // マニュアル用データ出力
        $dusk = Dusks::putManualData(
            ['html_path' => 'common/wysiwyg/face/index.html'],
            $this->getDuskBody(
                'face',
                'AI顔認識',
                'アップロードした写真から顔を判定して、自動的にモザイク処理を施します。',
                'AI顔認識を使用するには、外部サービス設定が必要です。<br />モザイクの粗さも指定できます。',
                'common/wysiwyg/face/index.html',
                '[
                     {"path": "common/wysiwyg/face/images/face1",
                      "name": "AI顔認識（AI顔認識処理前の画像）",
                      "comment": "<ul class=\"mb-0\"><li>人と犬の正面からの画像です。</li></ul>"
                     },
                     {"path": "common/wysiwyg/face/images/face2",
                      "name": "AI顔認識のダイアログ",
                      "comment": "<ul class=\"mb-0\"><li>AI顔認識用のダイアログ画面です。画像のサイズ変更もこの時、同時に実施できます。</li></ul>"
                     },
                     {"path": "common/wysiwyg/face/images/face3",
                      "name": "AI顔認識（AI顔認識処理後の画像）",
                      "comment": "<ul class=\"mb-0\"><li>人の顔のみ判定して、顔をモザイク処理しています。</li></ul>"
                     },
                     {"path": "common/wysiwyg/face/images/face4",
                      "name": "AI顔認識（横顔のAI顔認識処理前の画像）",
                      "comment": "<ul class=\"mb-0\"><li>横顔も判定できます。</li></ul>"
                     },
                     {"path": "common/wysiwyg/face/images/face5",
                      "name": "AI顔認識（横顔のAI顔認識処理後の画像）",
                      "comment": "<ul class=\"mb-0\"><li>横顔の判定、処理結果です。</li></ul>"
                     }
                 ]'
            )
        );
    }
}
