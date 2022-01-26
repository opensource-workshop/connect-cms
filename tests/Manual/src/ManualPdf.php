<?php

namespace Tests\Manual\src;

use Laravel\Dusk\Browser;
use Tests\DuskTestCase;

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
     * トップページ出力
     *
     * @return void
     */
    private function outputHome($view_path)
    {
        // ページ生成
        $html = view($view_path, ['level' => 'home', 'base_path' => './']);
        //\Storage::disk('manual')->put("html/index.html", $html);
    }

    /**
     * カテゴリトップ出力
     *
     * @return void
     */
    private function outputCategory($view_path, $methods)
    {
        // カテゴリをループ
        foreach($methods->where('plugin_name', 'index')->where('method_name', 'index') as $method) {
            // ページ生成
            $html = view($view_path, ['level' => 'category', 'base_path' => '../', 'methods' => $methods, 'current_method' => $method]);
            \Storage::disk('manual')->put('html/' . $method->category . "/index.html", $html);
        }
    }

    /**
     * プラグイントップ出力
     *
     * @return void
     */
    private function outputPlugin($view_path, $methods)
    {
        // プラグインをループ
        foreach($methods->where('method_name', 'index') as $method) {
            // ページ生成
            $html = view($view_path, ['level' => 'plugin', 'base_path' => '../../', 'methods' => $methods, 'current_method' => $method]);
            \Storage::disk('manual')->put('html/' . $method->category . '/' . $method->plugin_name . "/index.html", $html);
        }
    }

    /**
     * メソッド出力
     *
     * @return void
     */
    private function outputMethod($view_path, $methods)
    {
        // メソッドをループ
        foreach($methods as $method) {
            // ページ生成
            $html = view($view_path, ['level' => 'method', 'base_path' => '../../../', 'methods' => $methods, 'current_method' => $method]);
            \Storage::disk('manual')->put('html/' . $method->category . '/' . $method->plugin_name . '/' . $method->method_name . "/index.html", $html);

            // 画像の出力
            $this->outputImage($method);
        }
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
        $dusks = Dusks::get();

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

        // マニュアル表紙
        $pdf->writeHTML(view('manual.pdf.cover')->render(), false);

        // マニュアル用データをループ
        // マニュアルHTML と違い、カテゴリ、プラグイン、メソッドの3重ループで処理する。
        // マニュアルHTML は、カテゴリ、プラグイン、メソッドをそれぞれ独立でループした。（メニューの生成のため）

        // カテゴリのループ
        // echo "\n";
        foreach($dusks->where('plugin_name', 'index')->where('method_name', 'index') as $category) {
            // echo "【" . $category->category . "】\n";
            $pdf->addPage();
            $pdf->Bookmark($category->category, 0, 0, '', '', array(0, 0, 0));
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

            // プラグインのループ
            foreach($dusks->where('category', $category->category)->where('method_name', 'index') as $plugin) {
                // echo "- " . $plugin->plugin_title . "\n";
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

                foreach($dusks->where('category', $category->category)->where('plugin_name', $plugin->plugin_name) as $method) {
                    // echo $method->method_title . "\n";
                    $pdf->Bookmark($method->method_title, 2, 0, '', '', array(0, 0, 0));
                    $pdf->writeHTML(
                        view(
                            'manual.pdf.method',
                            ['method' => $method]
                        ),
                        false
                    );
                }
            }
        }


/*
        // 
        $pdf->addPage();
        $pdf->Bookmark('サイト基本設定', 0, 0, '', '', array(0, 0, 0));

$tmp_method = $dusks->where('plugin_name', 'admin_link');
$current_method = $dusks->where('id', 11)->first();
        $pdf->writeHTML(
            view(
                'manual.pdf.method_test',
                ['methods' => $tmp_method, 'current_method' => $current_method, 'base_path' => '', 'level' => 'method']
            ),
            false
        );

$tmp = view('manual.method',['methods' => $tmp_method, 'current_method' => $current_method, 'base_path' => '', 'level' => 'method']);
\Log::debug($tmp);
*/

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
        $pdf->output(\Storage::disk('manual')->path('pdf/manual.pdf'), 'F');



        // トップページ(トップページは Dusk レコードがないので、空の Dusks を使用する)
//        $this->outputHome('manual/index');

        // カテゴリトップ出力
//        $this->outputCategory('manual/category', $methods);

        // プラグイントップ出力
//        $this->outputPlugin('manual/plugin', $methods);

        // メソッド出力
//        $this->outputMethod('manual/method', $methods);
    }
}
