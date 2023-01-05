<?php

namespace Tests\Manual\src;

use Laravel\Dusk\Browser;
use Tests\DuskTestCase;

use App\Enums\ManualCategory;
use App\Models\Core\Dusks;
use App\Plugins\Manage\SiteManage\CCPDF;

/**
 * 仕様の出力クラス
 * 出力形式は2つ（class：クラスの仕様を出力。method(初期値)：メソッドの仕様を出力）
 *
 * @author 永原　篤 <nagahara@opensource-workshop.jp>
 * @copyright OpenSource-WorkShop Co.,Ltd. All Rights Reserved
 */
class SpecOutput extends DuskTestCase
{
    /**
     * 出力レベル
     */
    private $level = null;

    /**
     * 出力するクラス単位の仕様ファイル
     */
    private static $output_class_file = "spec/spec_summary.txt";

    /**
     * 出力するメソッド単位の仕様ファイル
     */
    private static $output_method_file = "spec/spec_detail.txt";

    /**
     * TEXTフォーマットBladeファイル
     */
    private static $output_blade = "manual.spec.txt";

    /**
     * PDFフォーマットBladeファイル
     */
//    private static $output_blade_pdf = "manual.spec.pdf_txt";

    /**
     * 最初の説明文
     */
    private static $about_title = "この資料について";
    private static $about_body = [
        "この仕様書は、株式会社オープンソース・ワークショップが小学校、中学校、高等学校、大学等の学校や研究所、保育園、学会、NPO、社団法人、財団法人、行政関連機関、企業等の情報公開Webサイト（いわゆるホームページ）及び組織内の情報共有用Webサイトに関わる中で必要と考えて策定した内容です。",
        "当社で開発し、オープンソース・ソフトウェアとして公開しているConnect-CMSは、この仕様をもとに実装されたものです。",
        "また、この仕様はConnect-CMSにのみ当てはまるものではなく、Webサイトを構築する際の一般的な仕様として検討、策定したものであるため、様々なWebサイトの構築において流用できるように、ここに公開いたします。",
        "当社はオープンソース・ソフトウェアを通して社会貢献を行うことを自らの役割としているため、この仕様に関しても公開し、自由に流用していただくことで社会への一つの貢献とさせていただきます。"
    ];

    /**
     * 問い合わせ先
     */
    private static $contact_title = "お問い合わせ";
    private static $contact_body = [
        "株式会社オープンソース・ワークショップ",
        "〒104-0053 東京都中央区晴海三丁目13番 1-4807号",
        "TEL：03-5534-8088　FAX：03-5534-8188",
        "email：info@opensource-workshop.jp",
        "web：https://opensource-workshop.jp/",
    ];

    /**
     * 大分類の説明
     */
    private static $category_desc = [
        'manage' => ['管理者向け機能', '管理者向けの機能として以下の内容を実現すること。'],
        'user' => ['一般ユーザ向け機能', '一般権限ユーザ及びゲスト向けの機能として以下の内容を実現すること。']
    ];

    /**
     * PDFインスタンス
     */
    private $pdf = null;

    /**
     * 出力形式
     */
    private $format = 'method';

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
     * 初期処理
     *
     * @return void
     */
    private function init($file_path, $level)
    {
        // 引数の受け取り用
        global $argv;
        if (count($argv) > 4 && $argv[4] == 'summary') {
            $this->format = 'summary';
        }

        // ファイルの削除
        \File::delete(config('connect.manual_put_base') . $file_path);

        // 仕様番号
        $number = ['category' => 0, 'plugin' => 0, 'spec' => 0];

        // 出力する仕様レベルの判定
        if ($level == 'class') {
            // クラスレベル仕様の取得
            $dusks = Dusks::whereNotNull('spec_class')->where('spec_class', '<>', '')->orderBy("id", "asc")->get();
        } else {
            // メソッドレベル仕様の取得
            $dusks = Dusks::whereNotNull('spec_method')->where('spec_method', '<>', '')->orderBy("id", "asc")->get();
        }

        // テキストの準備
        $this->initText($file_path, $level);

        // PDFの準備
        $this->initPdf($file_path, $level);

        // 番号配列と仕様データを戻す。
        return [$number, $dusks];
    }

    /**
     * テキストの初期処理
     *
     * @return void
     */
    private function initText($file_path, $level)
    {
        // 初期メッセージ
        $this->insertLf($file_path);

        // この資料について
        $this->appendFile($file_path, '', null, "【" . self::$about_title . "】", true);
        $this->insertLf($file_path);
        foreach (self::$about_body as $about_body) {
            $this->appendFile($file_path, '', null, $about_body, true);
        }

        // 問い合わせ先
        $this->appendLfFile($file_path, '', null, "【" . self::$contact_title . "】", true);
        foreach (self::$contact_body as $contact_body) {
            $this->appendLfFile($file_path, '', null, $contact_body);
        }
        $this->insertLf($file_path);

        // 機能仕様
        $this->appendLfFile($file_path, '', null, "【機能仕様】");
    }

    /**
     * PDFの初期処理
     *
     * @return void
     */
    private function initPdf($file_path, $level)
    {
        // 出力するPDF の準備
        $this->pdf = new CCPDF(PDF_PAGE_ORIENTATION, PDF_UNIT, PDF_PAGE_FORMAT, true, 'UTF-8', false);

        // PDF プロパティ設定
        $this->pdf->SetTitle('CMS仕様書');

        // 余白
        $this->pdf->SetMargins(15, 20, 15);

        // フォントを登録：追加フォントをtcpdf用フォントファイルに変換してvendor\tecnickcom\tcpdf\fontsに登録
        $font = new \TCPDF_FONTS();

        // ttfフォントファイルからtcpdf用フォントファイルを生成（tcpdf用フォントファイルがある場合は再生成しない）
        $fontX = $font->addTTFfont(resource_path('fonts/ipaexg.ttf'));

        // ヘッダーのフォントの設定（フォント情報を配列で渡す必要があるので、要注意）
        $this->pdf->setHeaderMargin(5);
        $this->pdf->setHeaderFont(array('ipaexg', '', 10));
        $this->pdf->setHeaderData('', 0, 'CMS仕様書', '');

        // フッター
        $this->pdf->setPrintFooter(true);

        // フォント設定
        $this->pdf->setFont('ipaexg', '', 12);

        // --- 表紙

        // 初期ページを追加
        $this->pdf->addPage();
        $this->pdf->Bookmark("表紙", 0, 0, '', '', array(0, 0, 0));

        // マニュアル表紙
        $this->pdf->writeHTML(
            view(
                'manual.spec.pdf_cover',
                [
                    'about_title' => self::$about_title,
                    'about_body_lines' => self::$about_body,
                    'contact_title' => self::$contact_title,
                    'contact_body_lines' => self::$contact_body,
                ]
            )->render(), false
        );

        // 機能仕様用に改ページしておく。
        $this->pdf->addPage();

        // マニュアル表紙
        $this->pdf->writeHTML(
            '<h1 style="text-align: center; font-size: 32px;">機能仕様</h1>', false
        );
    }

    /**
     * 仕様番号カウントアップ
     *
     * @return void
     */
    private function numberUp(&$number, $key)
    {
        // 指定されたキーの仕様番号のカウントアップ
        $number[$key]++;

        // 指定されたキーの下層の仕様番号のクリア
        if ($key == 'category') {
            $number['plugin'] = 0;
            $number['spec'] = 0;
        } elseif ($key == 'plugin') {
            $number['spec'] = 0;
        }
    }

    /**
     * ファイル出力
     *
     * @return void
     */
    private function appendFile($file_path, $level, $number, $txt, $lf = false, $u = false)
    {
        // テキストをフォーマットする。
        $format_txt = view(self::$output_blade, [
                              'level' => $level,
                              'number' => $number,
                              'txt' => $txt
                          ]);

        // viewでできるインデントなどを削除
        $format_txt = trim($format_txt);

        // 改行
        if ($lf) {
            $format_txt .= "\n";
        }

        // env でパスが指定されていなかった場合は、保存しない。
        if (empty(config('connect.manual_put_base'))) {
        } else {
            if (!\File::exists(dirname(config('connect.manual_put_base') . $file_path))) {
                \File::makeDirectory(dirname(config('connect.manual_put_base') . $file_path), 0755, true);
            }
            \File::append(config('connect.manual_put_base') . $file_path, $format_txt);
        }

        // アンダーライン
        if ($u) {
            $format_txt = '<u>' . $format_txt . '</u>';
        }

        // 改行
        if ($lf) {
            $format_txt .= "<br>";
        }

        // PDF出力
        if ($level == 'category' || $level == 'plugin' || $level == 'method') {
            $this->pdf->writeHTML($format_txt . "<br>", false);
        }
    }

    /**
     * 改行してからファイル出力
     *
     * @return void
     */
    private function appendLfFile($file_path, $level, $number, $txt, $lf = false, $u = false)
    {
        // 改行
        $this->insertLf($file_path);

        // ファイル出力
        $this->appendFile($file_path, $level, $number, $txt, $lf, $u);
    }

    /**
     * ファイルに下位行を挿入
     *
     * @return void
     */
    private function insertLf($file_path)
    {
        \File::append(config('connect.manual_put_base') . $file_path, "\n");
    }

    /**
     * ファイルに下位行を挿入
     *
     * @return void
     */
    private function insertLfPdf()
    {
        $this->pdf->writeHTML("<br><br>", false);
    }

    /**
     * クラス単位の使用の出力
     *
     * @return void
     */
    private function outputSpecClass()
    {
        // 初期処理（クリアされた仕様番号の配列と仕様の全データを受け取る）
        list($number, $dusks) = $this->init(self::$output_class_file, 'class');

        // 仕様データをカテゴリでグルーピングしてループ
        foreach ($dusks->groupBy('category') as $category_key => $category) {
            // 項番（カテゴリ）アップ
            $this->numberUp($number, 'category');

            // カテゴリ出力
            $this->insertLf(self::$output_class_file);
            $this->insertLfPdf();
            $this->appendLfFile(self::$output_class_file, 'category', $number, self::$category_desc[$category_key][0], true, true); // カテゴリ名
            $this->appendFile(self::$output_class_file, 'category', null, self::$category_desc[$category_key][1]);    // カテゴリ説明

            // 仕様データをプラグインでグルーピングしてループ
            foreach ($category->groupBy('plugin_title') as $plugin_title => $plugin) {
                // 項番（プラグイン）アップ
                $this->numberUp($number, 'plugin');

                // プラグイン出力
                $this->insertLf(self::$output_class_file);
                $this->insertLfPdf();
                $this->appendLfFile(self::$output_class_file, 'plugin', $number, $plugin_title, false, true);
                $this->appendLfFile(self::$output_class_file, 'plugin', null, $plugin[0]->spec_class);
            }
        }
        $this->insertLf(self::$output_class_file);

        // PDF出力
        $this->pdf->output(config('connect.manual_put_base') . "spec/spec_summary.pdf", 'F');
    }

    /**
     * メソッド単位の使用の出力
     *
     * @return void
     */
    private function outputSpecMethod()
    {
        // 初期処理（クリアされた仕様番号の配列と仕様の全データを受け取る）
        list($number, $dusks) = $this->init(self::$output_method_file, 'method');

        // 仕様データをカテゴリでグルーピングしてループ
        foreach ($dusks->groupBy('category') as $category_key => $category) {
            $this->insertLf(self::$output_method_file);

            // 項番（カテゴリ）アップ
            $this->numberUp($number, 'category');

            // カテゴリ出力
            $this->insertLfPdf();
            $this->appendLfFile(self::$output_method_file, 'category', $number, implode('：', self::$category_desc[$category_key]), false, true);

            // 仕様データをプラグインでグルーピングしてループ
            foreach ($category->groupBy('plugin_title') as $plugin_title => $plugin) {
                // 項番（プラグイン）アップ
                $this->numberUp($number, 'plugin');

                // プラグイン出力
                $this->insertLf(self::$output_method_file);
                $this->insertLfPdf();
                $this->appendLfFile(self::$output_method_file, 'plugin', $number, $plugin_title, false, true);

                // 仕様データをメソッドでグルーピングしてループ
                foreach ($plugin->groupBy('method_title') as $method_title => $method) {
                    // メソッドで実装している仕様を行にバラしてループ
                    $spec_lines = explode("\n", $method[0]->spec_method);

                    foreach ($spec_lines as $spec_line) {
                        if (empty($spec_line)) {
                            // 中身のない行は読み飛ばす。
                            continue;
                        }
                        // 項番（仕様詳細）アップ
                        $this->numberUp($number, 'spec');

                        // メソッド出力
                        $this->appendLfFile(self::$output_method_file, 'method', $number, $spec_line);
                    }
                }
            }
        }
        $this->insertLf(self::$output_method_file);

        // PDF出力
        $this->pdf->output(config('connect.manual_put_base') . "spec/spec_detail.pdf", 'F');
    }

    /**
     * 仕様出力用メインクラス
     *
     * @return void
     */
    public function testInvoke()
    {
        // エラー判定はOKとする。
        $this->assertTrue(true);

        // クラス単位の仕様の出力
        $this->outputSpecClass();

        // メソッド単位の仕様の出力
        $this->outputSpecMethod();
    }
}
