<?php

namespace Tests\Manual\src;

use Laravel\Dusk\Browser;
use Tests\DuskTestCase;

use App\Enums\ManualCategory;
use App\Models\Core\Dusks;
use App\Plugins\Manage\SiteManage\CCPDF;

class FunctionListPdf extends DuskTestCase
{
    /**
     * コンストラクタ
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * 概要出力
     *
     * @return void
     */
    private function outputDescription($pdf)
    {
        $pdf->addPage();
        $pdf->Bookmark("概要", 0, 0, '', '', array(0, 0, 0));
        $pdf->writeHTML(
            view(
                'manual.pdf.description'
            ),
            false
        );
        return $pdf;
    }

    /**
     * 問合せ先ページ出力
     *
     * @return void
     */
    private function outputContact($pdf)
    {
        $pdf->addPage();
        $pdf->Bookmark("お問い合わせ", 0, 0, '', '', array(0, 0, 0));
        $pdf->writeHTML(
            view(
                'manual.pdf.contact',
                [
                    'contact' => mb_convert_encoding(config('connect.manual_contact_page'), "UTF-8", "SJIS"),
                ]
            ),
            false
        );
        return $pdf;
    }

    /**
     * マニュアル出力用クラス
     *
     * @return void
     */
    public function testInvoke()
    {
        // マニュアル出力のために、dusk データベースなど利用するので、アサーションは無条件にOKとしたい。
        $this->assertTrue(true);

        // 全データ取得
        $dusks = Dusks::orderBy('sort')->orderBy('id')->get();

        // 出力するPDF の準備
        $pdf = new CCPDF(PDF_PAGE_ORIENTATION, PDF_UNIT, PDF_PAGE_FORMAT, true, 'UTF-8', false);

        // PDF プロパティ設定
        $pdf->SetTitle('Connect-CMS 機能一覧');

        // 余白
        $pdf->SetMargins(15, 20, 15);

        // フォントを登録
        // 追加フォントをtcpdf用フォントファイルに変換してvendor\tecnickcom\tcpdf\fontsに登録
        $font = new \TCPDF_FONTS();

        // ttfフォントファイルからtcpdf用フォントファイルを生成（tcpdf用フォントファイルがある場合は再生成しない）
        $fontX = $font->addTTFfont(resource_path('fonts/ipaexg.ttf'));

        // ヘッダーのフォントの設定（フォント情報を配列で渡す必要があるので、要注意）
        $pdf->setHeaderMargin(5);
        $pdf->setHeaderFont(array('ipaexg', '', 10));
        $pdf->setHeaderData('', 0, 'Connect-CMS 機能一覧 - https://connect-cms.jp', '');

        // フッター
        $pdf->setPrintFooter(true);

        // フォント設定
        $pdf->setFont('ipaexg', '', 12);

        // --- 表紙

        // 初期ページを追加
        $pdf->addPage();
        $pdf->Bookmark("表紙", 0, 0, '', '', array(0, 0, 0));

        // マニュアル表紙
        $pdf->writeHTML(
            view(
                'manual.function.cover',[]
            )->render(), false
        );

        // 機能の出力
        $pdf->addPage();
        $pdf->writeHTML(
            view(
                'manual.function.function',
                [
                    'dusks' => $dusks,
                ]
            ),
            false
        );

        // 問合せ先ページ
        $this->outputContact($pdf);

        // env でパスが指定されていなかった場合は、manual ディスクの html フォルダに保存。
        if (empty(config('connect.manual_put_base'))) {
            if (!\File::exists(\Storage::disk('manual')->path('html/pdf'))) {
                \File::makeDirectory(\Storage::disk('manual')->path('html/pdf'), 0755, true);
            }
            $pdf->output(\Storage::disk('manual')->path('html/pdf/function.pdf'), 'F');
        } else {
            if (!\File::exists(config('connect.manual_put_base') . 'pdf')) {
                \File::makeDirectory(config('connect.manual_put_base') . 'pdf', 0755, true);
            }
            $pdf->output(config('connect.manual_put_base') . 'pdf/function.pdf', 'F');
        }
    }
}
