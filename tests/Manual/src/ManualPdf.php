<?php

namespace Tests\Manual\src;

use Laravel\Dusk\Browser;
use Tests\DuskTestCase;

use App\Enums\ManualCategory;
use App\Models\Core\Dusks;
use App\Plugins\Manage\SiteManage\CCPDF;

class ManualPdf extends DuskTestCase
{
    /**
     * スクリーンショット保存ルートパス
     */
    private $screenshots_root;

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
     * 裏表紙出力
     *
     * @return void
     */
    private function outputBackfront($pdf)
    {
        $pdf->addPage();
        $pdf->Bookmark("最後に", 0, 0, '', '', array(0, 0, 0));
        $pdf->writeHTML(
            view(
                'manual.pdf.backfront'
            ),
            false
        );
        return $pdf;
    }

    /**
     * カテゴリ出力
     *
     * @return void
     */
    private function outputCategory($pdf, $dusks, $category)
    {
        $pdf->addPage();
        $pdf->Bookmark(ManualCategory::getDescription($category->category), 0, 0, '', '', array(0, 0, 0));
        $pdf->writeHTML(
            view(
                'manual.pdf.category',
                [
                    'category' => $category,
                    'plugins' => $dusks->where('category', $category->category)->where('method_name', 'index')
                ]
            ),
            false
        );
        return $pdf;
    }

    /**
     * プラグイン出力
     *
     * @return void
     */
    private function outputPlugin($pdf, $dusks, $category, $plugin)
    {
        $pdf->addPage();
        $pdf->Bookmark($plugin->plugin_title, 1, 0, '', '', array(0, 0, 0));
        $pdf->writeHTML(
            view(
                'manual.pdf.plugin',
                [
                    'plugin' => $plugin,
                    'methods' => $dusks->where('category', $category->category)->where('plugin_name', $plugin->plugin_name)
                ]
            ),
            false
        );
        return $pdf;
    }

    /**
     * メソッド出力
     *
     * @return void
     */
    private function outputMethod($pdf, $method)
    {
        $pdf->addPage();
        $pdf->Bookmark($method->method_title, 2, 0, '', '', array(0, 0, 0));
        $pdf->writeHTML(
            view(
                'manual.pdf.method',
                ['method' => $method]
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
        // Laravel がコンストラクタでbase_path など使えないので、ここで。
        $this->screenshots_root = base_path('tests/Browser/screenshots/');

        // マニュアル出力のために、dusk データベースなど利用するので、アサーションは無条件にOKとしたい。
        $this->browse(function (Browser $browser) {
            $browser->visit('/')
                    ->assertTitleContains('Connect-CMS');
        });

        // 全データ取得
        $dusks = Dusks::where('category', '!=', 'top')->orderBy("id", "asc")->get();

        // 出力するPDF の準備
        $pdf = new CCPDF(PDF_PAGE_ORIENTATION, PDF_UNIT, PDF_PAGE_FORMAT, true, 'UTF-8', false);

        // PDF プロパティ設定
        $pdf->SetTitle('Connect-CMS マニュアル');

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
        $pdf->setHeaderData('', 0, 'Connect-CMS マニュアル - https://connect-cms.jp', '');

        // フッター
        $pdf->setPrintFooter(true);

        // フォント設定
        $pdf->setFont('ipaexg', '', 12);

        // --- 表紙

        // 初期ページを追加
        $pdf->addPage();
        $pdf->Bookmark("表紙", 0, 0, '', '', array(0, 0, 0));

        // マニュアル表紙
        $pdf->writeHTML(view('manual.pdf.cover')->render(), false);

        // 概要
        $this->outputDescription($pdf);

        // マニュアル用データをループ
        // マニュアルHTML と違い、カテゴリ、プラグイン、メソッドの3重ループで処理する。
        // マニュアルHTML は、カテゴリ、プラグイン、メソッドをそれぞれ独立でループした。（メニューの生成のため）

        // カテゴリのループ
        // echo "\n";
        //foreach ($dusks->where('plugin_name', 'index')->where('method_name', 'index') as $category) {
        foreach ($dusks->groupBy('category') as $category) {
            $pdf = $this->outputCategory($pdf, $dusks, $category[0]);

            // プラグインのループ
            foreach ($dusks->where('category', $category[0]->category)->where('method_name', 'index') as $plugin) {
                $pdf = $this->outputPlugin($pdf, $dusks, $category[0], $plugin);

                // メソッドのループ
                foreach ($dusks->where('category', $category[0]->category)->where('plugin_name', $plugin->plugin_name) as $method) {
                    $pdf = $this->outputMethod($pdf, $method);
                }
            }
        }

        // 裏表紙
        $this->outputBackfront($pdf);

        // 目次ページの追加
        $pdf->addTOCPage();

        // write the TOC title
        $pdf->SetFont('ipaexg', 'B', 28);
        $pdf->MultiCell(0, 0, 'Connect-CMS マニュアル目次', 0, 'C', 0, 1, '', 30, true, 0);
        $pdf->Ln();

        $pdf->SetFont('ipaexg', '', 12);

        // add a simple Table Of Content at first page
        // (check the example n. 59 for the HTML version)
        $pdf->addTOC(2, 'ipaexg', '.', 'INDEX', 'B', array(0, 0, 0));

        // end of TOC page
        $pdf->endTOCPage();

        // 目次 --------------------/

        // 出力 ( D：Download, I：Inline )
        // env でパスが指定されていなかった場合は、manual ディスクの html フォルダに保存。
        if (empty(config('connect.manual_put_base'))) {
            if (!\File::exists(\Storage::disk('manual')->path('html/pdf'))) {
                \File::makeDirectory(\Storage::disk('manual')->path('html/pdf'), 0755, true);
            }
            $pdf->output(\Storage::disk('manual')->path('html/pdf/manual.pdf'), 'F');
        } else {
            if (!\File::exists(config('connect.manual_put_base') . 'pdf')) {
                \File::makeDirectory(config('connect.manual_put_base') . 'pdf', 0755, true);
            }
            $pdf->output(config('connect.manual_put_base') . 'pdf/manual.pdf', 'F');
        }
    }
}
