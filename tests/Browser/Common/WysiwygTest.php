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
        $page = Page::where('permanent_link', '/test/content')->first();
        $frame = Frame::where('page_id', $page->id)->where('plugin_name', 'contents')->first();
        if (!empty($frame)) {
            $bucket = Buckets::find($frame->bucket_id);
            if (!empty($bucket)) {
                Contents::where('bucket_id', $bucket->id)->forceDelete();
                Buckets::find($bucket->id)->forceDelete();
            }
            $frame->forceDelete();
        }

        // 固定記事を作成
        $this->addPluginModal('contents', '/test/content', 2, false);
        $bucket = Buckets::create(['bucket_name' => 'WYSIWYGテスト', 'plugin_name' => 'contents']);

        // 初めは記事は文字のみ。
        $this->content = Contents::create(['bucket_id' => $bucket->id, 'content_text' => '<p>WYSIWYGのテストです。</p>', 'status' => 0]);

        $this->frame = Frame::orderBy('id', 'desc')->first();
        $this->frame->update(['bucket_id' => $bucket->id]);

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
        // 画面
        $this->browse(function (Browser $browser) {
            // 表に合わせた記事に変更
            $content_text = '<table border="1" class="table"><tbody>';
            for ($i = 0; $i < 2; $i++) {
                $content_text .= '<tr><td>A</td><td>B</td><td>C</td></tr>';
            }
            $content_text .= '</tbody></table>';
            $this->content->content_text = $content_text;
            $this->content->save();

            // 記事をデータベースから変更したので、一度開きなおす。
            $browser->visit('/plugin/contents/edit/' . $this->frame->page_id . '/' . $this->frame->id . '/' . $this->content->id . '#frame-' . $this->frame->id);

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
            $browser->visit('/plugin/contents/edit/' . $this->frame->page_id . '/' . $this->frame->id . '/' . $this->content->id . '#frame-' . $this->frame->id);

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
            $browser->visit('/plugin/contents/edit/' . $this->frame->page_id . '/' . $this->frame->id . '/' . $this->content->id . '#frame-' . $this->frame->id);

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
        // 画面
        $this->browse(function (Browser $browser) {
            $browser->visit('/plugin/contents/edit/' . $this->frame->page_id . '/' . $this->frame->id . '/' . $this->content->id . '#frame-' . $this->frame->id)
                    ->click('#ccMainArea .tox-tinymce .tox-toolbar__group:nth-child(15) button:nth-child(1)')
                    ->pause(500)
                    ->screenshot('common/wysiwyg/translate/images/translate');
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
        // 画面
        $this->browse(function (Browser $browser) {
            $browser->visit('/plugin/contents/edit/' . $this->frame->page_id . '/' . $this->frame->id . '/' . $this->content->id . '#frame-' . $this->frame->id)
                    ->click('#ccMainArea .tox-tinymce .tox-toolbar__group:nth-child(15) button:nth-child(2)')
                    ->pause(500)
                    ->screenshot('common/wysiwyg/pdf/images/pdf');
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
                     {"path": "common/wysiwyg/pdf/images/pdf",
                      "name": "PDFアップロード",
                      "comment": "<ul class=\"mb-0\"><li>パスワード付PDFの場合は、パスワードも入力してください。</li></ul>"
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
        // 画面
        $this->browse(function (Browser $browser) {
            $browser->visit('/plugin/contents/edit/' . $this->frame->page_id . '/' . $this->frame->id . '/' . $this->content->id . '#frame-' . $this->frame->id)
                    ->click('#ccMainArea .tox-tinymce .tox-toolbar__group:nth-child(15) button:nth-child(3)')
                    ->pause(500)
                    ->screenshot('common/wysiwyg/face/images/face');
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
                     {"path": "common/wysiwyg/face/images/face",
                      "name": "AI顔認識",
                      "comment": "<ul class=\"mb-0\"><li>画像のサイズ変更もこの時、同時に実施できます。</li></ul>"
                     }
                 ]'
            )
        );
    }
}