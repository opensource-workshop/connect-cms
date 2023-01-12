<?php

namespace Tests\Browser\Manage;

use Illuminate\Foundation\Testing\DatabaseMigrations;
use Laravel\Dusk\Browser;
use Tests\DuskTestCase;

/**
 * > tests\bin\connect-cms-test.bat
 */
class ServiceManageTest extends DuskTestCase
{
    /**
     * テストする関数の制御
     *
     * @group manage
     * @see https://readouble.com/laravel/6.x/ja/dusk.html#running-tests
     */
    public function testInvoke()
    {
        $this->login(1);
        $this->index();
        $this->pdf();
        $this->face();
    }

    /**
     * WYSIWYG設定の表示
     */
    private function index()
    {
        $this->browse(function (Browser $browser) {
            $browser->visit('/manage/service')
                    ->assertTitleContains('Connect-CMS')
                    ->click('#lavel_use_translate_1')
                    ->click('#label_use_pdf_thumbnail_1')
                    ->click('#label_use_face_ai_1')
                    ->press('更新');

            $browser->visit('/manage/service')
                    ->screenshot('manage/service/index/images/index');
        });

        $this->browse(function (Browser $browser) {
            $browser->scrollIntoView('footer');
            $browser->screenshot('manage/service/index/images/index2');
        });

        // マニュアル用データ出力
        $this->putManualData('[
            {"path": "manage/service/index/images/index",
             "name": "WYSIWYG設定1",
             "comment": ""
            },
            {"path": "manage/service/index/images/index2",
             "name": "WYSIWYG設定2",
             "comment": "<ul class=\"mb-0\"><li>翻訳、PDFアップロード、AI顔認識でそれぞれ、外部サービスを使用するか否かを設定できます。</li><li>外部サービスの使用には、環境設定ファイル.envでの外部サービス設定が必要です。</li></ul>"
            }
        ]', null, 3);
    }

    /**
     * PDFアップロードの表示
     */
    private function pdf()
    {
        $this->browse(function (Browser $browser) {
            $browser->visit('/manage/service/pdf')
                    ->assertTitleContains('Connect-CMS')
                    ->screenshot('manage/service/pdf/images/pdf');
        });

        // マニュアル用データ出力
        $this->putManualData('[
            {"path": "manage/service/pdf/images/pdf",
             "name": "PDFアップロード",
             "comment": "<ul class=\"mb-0\"><li>サムネイル付きでPDFアップロードする画面で初期選択させるサムネイルの大きさを設定できます。<br />生成されたサムネイル画像からもPDFにリンクが張られます。</li><li>初期に選択させるサムネイルの数を指定できます。1、2、3、4、全てから選択できます。</li><li>サムネイルに張られるリンクは、通常はPDFにリンクされますが、サムネイルを大きいサイズにして、そのまま画像を開いて見たいという要望の場合はサムネイルのリンクを画像を開く。を設定しておきます。</li></ul>"
            }
        ]', null, 3);
    }

    /**
     * AI顔認識アップロードの表示
     */
    private function face()
    {
        $this->browse(function (Browser $browser) {
            $browser->visit('/manage/service/face')
                    ->assertTitleContains('Connect-CMS')
                    ->screenshot('manage/service/face/images/face');
        });

        // マニュアル用データ出力
        $this->putManualData('[
            {"path": "manage/service/face/images/face",
             "name": "AI顔認識",
             "comment": "<ul class=\"mb-0\"><li>アップロード後に変換する画像サイズの初期選択肢を設定できます。</li><li>モザイクの粗さの初期選択肢を設定できます。</li></ul>"
            }
        ]', null, 3);
    }
}
