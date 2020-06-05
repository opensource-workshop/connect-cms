<?php

namespace App\Traits\Migration;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

use File;
use Session;
use Storage;

use App\Models\Common\Buckets;
use App\Models\Common\Frame;
use App\Models\Common\Page;
use App\Models\Common\Uploads;
use App\Models\Core\Configs;
use App\Models\User\Contents\Contents;

use App\Models\Migration\Nc2\Pages;

use App\Traits\ConnectCommonTrait;

trait MigrationTrait
{
//    var $directory_base = "uploads/";

    use ConnectCommonTrait;

    /**
     * ファイル取得時のHTTPヘッダーからcontent_disposition
     * CURL でクロージャで取得するため、値の移送用いインスタンス変数を用意
     * ここから日本語ファイル名を取得する。
     */
    private $content_disposition = "";

    /**
     * テストメソッド
     */
    private function getTestStr()
    {
        return "This is MigrationTrait test.";
    }

    /**
     * Connect-CMS 移行形式のHTML をインポート
     */
    private function importHtml($page_id)
    {
        /*
        HTML からインポート（ページ指定）

        "frame_*.ini" でFile::glob する。
        Buckets 登録
        Frames 登録
        [image_names] の画像を登録（連番ディレクトリ注意）
            upload_id でhtml の内容を編集
        Contents 登録
        */

        // フレーム単位のini ファイルの取得
        $ini_files = File::glob(storage_path() . '/app/migration/' . $page_id . '/*.ini');

        // フレームのループ
        $display_sequence = 0;
        foreach ($ini_files as $ini_file) {
            // echo $ini_file . "\n";

            $display_sequence++;

            // フレーム毎のini_file の解析
            $ini_array = parse_ini_file($ini_file, true);
            //print_r($ini_array);

            // HTML コンテンツの取得（画像処理をループしながら、タグを編集するので、ここで読みこんでおく）
            $html_file_path = str_replace('.ini', '.html', $ini_file);
            $content_html = File::get($html_file_path);

            // Buckets 登録
            // echo "Buckets 登録\n";
            $bucket = Buckets::create(['bucket_name' => '無題', 'plugin_name' => 'contents']);

            // Frames 登録
            // echo "Frames 登録\n";

            // Frame タイトル
            $frame_title = '[無題]';
            if (array_key_exists('frame_base', $ini_array) && array_key_exists('frame_title', $ini_array['frame_base'])) {
                $frame_title = $ini_array['frame_base']['frame_title'];
            }

            // Frame デザイン
            $frame_design = 'default';
            if (array_key_exists('frame_base', $ini_array) && array_key_exists('frame_design', $ini_array['frame_base'])) {
                $frame_design = $ini_array['frame_base']['frame_design'];
            }

            $frame = Frame::create(['page_id'          => $page_id,
                                    'area_id'          => 2,
                                    'frame_title'      => $frame_title,
                                    'frame_design'     => $frame_design,
                                    'plugin_name'      => 'contents',
                                    'frame_col'        => 0,
                                    'template'         => 'default',
                                    'bucket_id'        => $bucket->id,
                                    'display_sequence' => $display_sequence,
                                   ]);

            // [image_names] の画像を登録
            if (array_key_exists('image_names', $ini_array)) {
                foreach ($ini_array['image_names'] as $filename => $client_original_name) {
                    // ファイルサイズ
                    if (File::exists(storage_path() . '/app/migration/' . $page_id . "/" . $filename)) {
                        $file_size = File::size(storage_path() . '/app/migration/' . $page_id . "/" . $filename);
                    } else {
                        $file_size = 0;
                    }
                    //echo "ファイルサイズ = " . $file_size . "\n";

                    // Uploads テーブル
                    $upload = Uploads::create([
                                  'client_original_name' => $client_original_name,
                                  'mimetype'             => $this->getMimetypeFromFilename($filename),
                                  'extension'            => $this->getExtension($filename),
                                  'size'                 => $file_size,
                                  'plugin_name'          => 'contents',
                                  'page_id'              => $page_id,
                                  'temporary_flag'       => 0,
                              ]);

                    // ファイルのコピー
                    $source_file_path = 'migration/' . $page_id . "/" . $filename;
                    $destination_file_path = $this->getDirectory($upload->id) . '/' . $upload->id . '.' . $this->getExtension($filename);
                    Storage::copy($source_file_path, $destination_file_path);

                    // 画像のパスの修正
                    $content_html = str_replace($filename, '/file/' . $upload->id, $content_html);
                }
            }

            // [file_names] の画像を登録
            if (array_key_exists('file_names', $ini_array)) {
                foreach ($ini_array['file_names'] as $filename => $client_original_name) {
                    // ファイルサイズ
                    if (File::exists(storage_path() . '/app/migration/' . $page_id . "/" . $filename)) {
                        $file_size = File::size(storage_path() . '/app/migration/' . $page_id . "/" . $filename);
                    } else {
                        $file_size = 0;
                    }
                    //echo "ファイルサイズ = " . $file_size . "\n";

                    // Uploads テーブル
                    $upload = Uploads::create([
                                  'client_original_name' => $client_original_name,
                                  'mimetype'             => $this->getMimetypeFromFilename($filename),
                                  'extension'            => $this->getExtension($filename),
                                  'size'                 => $file_size,
                                  'plugin_name'          => 'contents',
                                  'page_id'              => $page_id,
                                  'temporary_flag'       => 0,
                              ]);

                    // ファイルのコピー
                    $source_file_path = 'migration/' . $page_id . "/" . $filename;
                    $destination_file_path = $this->getDirectory($upload->id) . '/' . $upload->id . '.' . $this->getExtension($filename);
                    Storage::copy($source_file_path, $destination_file_path);

                    // ファイルのパスの修正
                    $content_html = str_replace($filename, '/file/' . $upload->id, $content_html);
                }
            }

            //Log::debug($content_html);

            // Contents 登録
            // echo "Contents 登録\n";
            $content = Contents::create(['bucket_id' => $bucket->id,
                                         'content_text' => $content_html,
                                         'status' => 0]);
        }
        // echo $page_id . ' の移行が完了';
    }

    /**
     * 拡張子からMIMETYPE 取得
     */
    private function getMimetypeFromExtension($extension)
    {
        // jpeg の場合
        if ($extension == 'jpg') {
            return IMAGETYPE_JPEG;
        }
        // png の場合
        elseif ($extension == 'png') {
            return IMAGETYPE_PNG;
        }
        // gif の場合
        elseif ($extension == 'gif') {
            return IMAGETYPE_GIF;
        }
        return "";
    }

    /**
     * ファイル名から拡張子を取得
     */
    private function getExtension($filename)
    {
        $filepath = pathinfo($filename);
        return $filepath['extension'];
    }

    /**
     * ファイル名からMIMETYPE 取得
     */
    private function getMimetypeFromFilename($filename)
    {
        $extension = $this->getExtension($filename);

        // jpg
        if ($extension == 'jpg') {
            return IMAGETYPE_JPEG;
        }
        // png
        if ($extension == 'png') {
            return IMAGETYPE_PNG;
        }
        // gif
        if ($extension == 'gif') {
            return IMAGETYPE_GIF;
        }
        // pdf
        if ($extension == 'pdf') {
            return 'application/pdf';
        }
        // excel
        if ($extension == 'xls') {
            return 'application/vnd.ms-excel';
        }
        // excel
        if ($extension == 'xlsx') {
            return 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet';
        }
        // word
        if ($extension == 'doc') {
            return 'application/msword';
        }
        // word
        if ($extension == 'docx') {
            return 'application/vnd.openxmlformats-officedocument.wordprocessingml.document';
        }
        // power point
        if ($extension == 'ppt') {
            return 'application/vnd.ms-powerpoint';
        }
        // power point
        if ($extension == 'pptx') {
            return 'application/vnd.openxmlformats-officedocument.presentationml.presentation';
        }
        // mp3
        if ($extension == 'mp3') {
            return 'audio/mpeg';
        }
        // mp4
        if ($extension == 'mp4') {
            return 'video/mp4';
        }

        return "application/octet-stream";
    }

    /**
     * ページのHTML取得
     */
    private function migrationNC3Page($url, $page_id)
    {
        /*
        ページ移行関数呼び出し(URL, 移行先のページid)

        ページ移行関数
            HTML 取得
            タイトルバーの文字列の取得
            本文をHTML形式で取得
            本文から画像、添付ファイルのタグの抜き出し
            画像、添付ファイルの取得
            ---
            バケツ作成
            フレーム作成
            固定記事作成
            ファイル登録
        */

        // 画像ファイルや添付ファイルを取得する場合のテンポラリ・ディレクトリ
        //$uniq_tmp_dir = uniqid('migration_');
        Storage::makeDirectory('migration/' . $page_id);

        // 指定されたページのHTML を取得
        $html = $this->getHTMLPage($url);

        // HTMLドキュメントの解析準備
        $dom = new \DOMDocument;

        // DOMDocument が返ってくる。
        @$dom->loadHTML($html);
        $xpath = new \DOMXPath($dom);

        // NC3 のメイン部分を抜き出します。
        $expression = '//*[@id="container-main"]';

        // DOMElement が返ってくる。
        $container_main = $xpath->query($expression)->item(0);
        //var_dump($container_main);

        // NC3 のフレーム部分として section を抜き出します。
        $expression = './/section';
        $frame_index = 0; // フレームの連番
        foreach ($xpath->query($expression, $container_main) as $section) {
            $frame_index++;
            $frame_index_str = sprintf("%'.04d", $frame_index);

            // フレームタイトル(panel-heading)を抜き出します。
            $expression = './/div[contains(@class, "panel-heading")]/span';
            $frame_title = $xpath->query($expression, $section)->item(0);
            //var_dump($this->getInnerHtml($frame_title));
            //Log::debug($this->getInnerHtml($frame_title));

            // フレーム設定の保存用変数
            $frame_ini = "[frame_base]\n" . "frame_title = \"" . $this->getInnerHtml($frame_title) . "\"\n";

            // フレームデザイン
            $expression = './/@class';
            $frame_design = $xpath->query($expression, $section)->item(0);
            $frame_ini .= "frame_design = \"" . $this->getFrameDesign($frame_design->value) . "\"\n";

            // 本文を抜き出します。
            $expression = './/div[contains(@class, "panel-body")]/article';
            $content = $xpath->query($expression, $section)->item(0);
            //var_dump($this->getInnerHtml($content));
            //Log::debug($this->getInnerHtml($content));

            // HTML の保存用変数
            $content_html = $this->getInnerHtml($content);

            // 本文から画像(img src)を抜き出す
            $images = $this->get_content_image($content_html);
            //var_dump($images);

            // 画像の取得と保存
            // ・取得して連番で保存（拡張子ナシ）
            // ・mime_type から拡張子決定
            if ($images) {
                // HTML 中の画像ファイルをループで処理
                $frame_ini .= "\n[image_names]\n";
                $image_index = 0;
                foreach ($images as $image_url) {
                    // 保存する画像のパス
                    $image_index++;
                    $downloadPath = $image_url;

                    $file_name = "frame_" . $frame_index_str . '_' . $image_index;
                    $savePath = 'migration/' . $page_id . "/" . $file_name;
                    $saveStragePath = storage_path() . '/app/' . $savePath;

                    // CURL 設定、ファイル取得
                    $ch = curl_init($downloadPath);
                    $fp = fopen($saveStragePath, 'w');
                    curl_setopt($ch, CURLOPT_FILE, $fp);
                    curl_setopt($ch, CURLOPT_HEADER, false);
                    curl_setopt($ch, CURLOPT_HEADERFUNCTION, array(&$this,'header_callback'));
                    $result = curl_exec($ch);
                    curl_close($ch);
                    fclose($fp);

                    //echo $this->content_disposition;

                    //getimagesize関数で画像情報を取得する
                    list($img_width, $img_height, $mime_type, $attr) = getimagesize($saveStragePath);

                    //list関数の第3引数にはgetimagesize関数で取得した画像のMIMEタイプが格納されているので条件分岐で拡張子を決定する
                    switch ($mime_type) {
                        //jpegの場合
                        case IMAGETYPE_JPEG:
                            //拡張子の設定
                            $img_extension = "jpg";
                            break;
                        //pngの場合
                        case IMAGETYPE_PNG:
                        //拡張子の設定
                            $img_extension = "png";
                            break;
                        //gifの場合
                        case IMAGETYPE_GIF:
                            //拡張子の設定
                            $img_extension = "gif";
                            break;
                    }

                    // 拡張子の変更
                    Storage::delete($savePath . '.' . $img_extension);
                    Storage::move($savePath, $savePath . '.' . $img_extension);

                    // 画像の設定情報の記載
                    $frame_ini .= $file_name . '.' . $img_extension . ' = "' . $this->search_file_name($this->content_disposition) . "\"\n";

                    // content 内の保存した画像のパスを修正
                    $content_html = str_replace($image_url, $file_name . '.' . $img_extension, $content_html);

                    //拡張子の出力
                    //echo $img_extension;
                    //echo "\n";
                }
            }

            // 本文からアンカー(a href)を抜き出す
            $anchors = $this->get_content_anchor($content_html);
            //var_dump($anchors);

            // 添付ファイルの取得と保存
            // ・取得して連番で保存（拡張子ナシ）
            // ・mime_type から拡張子決定
            if ($anchors) {
                // HTML 中のアップロードファイルをループで処理
                $frame_ini .= "\n[file_names]\n";
                $file_index = 0;
                foreach ($anchors as $anchor_href) {
                    // アップロードファイルの場合
                    if (stripos($anchor_href, 'cabinet_files/download') !== false || stripos($anchor_href, 'wysiwyg/file/download') !== false) {
                        // 保存するファイルのパス
                        $file_index++;
                        $downloadPath = $anchor_href;

                        $file_name = "frame_" . $frame_index_str . '_file_' . $file_index;
                        $savePath = 'migration/' . $page_id . "/" . $file_name;
                        $saveStragePath = storage_path() . '/app/' . $savePath;

                        // CURL 設定、ファイル取得
                        $ch = curl_init($downloadPath);
                        $fp = fopen($saveStragePath, 'w');
                        curl_setopt($ch, CURLOPT_FILE, $fp);
                        curl_setopt($ch, CURLOPT_HEADER, false);
                        curl_setopt($ch, CURLOPT_HEADERFUNCTION, array(&$this,'header_callback'));
                        $result = curl_exec($ch);
                        curl_close($ch);
                        fclose($fp);

                        //echo $this->content_disposition;

                        // ファイルの拡張子の取得
                        $file_extension = $this->getExtension($this->search_file_name($this->content_disposition));

                        // 拡張子の変更
                        Storage::delete($savePath . '.' . $file_extension);
                        Storage::move($savePath, $savePath . '.' . $file_extension);

                        // ファイルの設定情報の記載
                        $frame_ini .= $file_name . '.' . $file_extension . ' = "' . $this->search_file_name($this->content_disposition) . "\"\n";

                        // content 内の保存したファイルのパスを修正
                        $content_html = str_replace($anchor_href, $file_name . '.' . $file_extension, $content_html);

                        //拡張子の出力
                        //echo $file_extension;
                        //echo "\n";
                    }
                }
            }

            // フレーム設定ファイルの出力
            Storage::put('migration/' . $page_id . "/frame_" . $frame_index_str . '.ini', $frame_ini);

            // Contents 変換
            $content_html = $this->migrationHtml($content_html);

            // HTML content の保存
            Storage::put('migration/' . $page_id . "/frame_" . $frame_index_str . '.html', trim($content_html));
        }
    }

    /**
     * NC3 からConnect-CMS へタグ変換
     */
    private function migrationHtml($content_html)
    {
        // 画像のレスポンスCSS
        $content_html = $this->replaceCss('img-responsive', 'img-fluid', $content_html);

        // NC3 用画像CSS（削除）
        $content_html = $this->replaceCss('nc3-img-block', '', $content_html);
        $content_html = $this->replaceCss('nc3-img', '', $content_html);
        $content_html = $this->replaceCss('thumbnail', 'img-thumbnail', $content_html);

        return $content_html;
    }

    /**
     * CSS 中のクラス名の変換
     */
    private function replaceCss($search, $replace, $subject)
    {
        $pattern = '/class=((?:\s|")?)(.*?)((?:\s|")+)' . $search . '((?:\s|")+)(.*?)((?:\s|")?)/';
        $replacement = 'class=$1$2$3' . $replace . '$4$5$6';
        $content_html = preg_replace($pattern, $replacement, $subject);
        return $content_html;
    }

    /**
     * content_disposition からファイル名の抜き出し
     */
    function search_file_name($content_disposition)
    {
        // attachment ＆ filename*=UTF-8 形式
        if (stripos($content_disposition, "Content-Disposition: attachment;filename*=UTF-8''") !== false) {
            return trim(str_replace("Content-Disposition: attachment;filename*=UTF-8''", '', $content_disposition));
        }

        // inline ＆ filename= ＆ filename*=UTF-8 併用形式
        if (stripos($content_disposition, "Content-Disposition: inline; filename=") !== false &&
            stripos($content_disposition, "filename*=UTF-8''") !== false) {
            return trim(mb_substr($content_disposition, stripos($content_disposition, "filename*=UTF-8''") + 17, mb_strlen($content_disposition) - 1));
        }

        return "";
    }

    /**
     * CURL のhttp ヘッダー処理コールバック関数
     */
    function header_callback($ch, $header_line)
    {
        // Content-Disposition の場合に処理する。
        // （この関数はhttp ヘッダーの行数分、呼び出される）
        if (strpos($header_line, "Content-Disposition") !== false) {
            //Log::debug($header_line);
            //echo urldecode($header_line);
            $this->content_disposition = urldecode($header_line);
        }

        return strlen($header_line);
    }

    /**
     * HTML からimg タグの src 属性を取得
     */
    private function get_content_image($content)
    {
        $pattern = '/<img.*?src\s*=\s*[\"|\'](.*?)[\"|\'].*?>/i';

        if (preg_match_all($pattern, $content, $images)) {
            if (is_array($images) && isset($images[1])) {
                return $images[1];
            } else {
                return false;
            }
        } else {
            return false;
        }
    }

    /**
     * HTML からa タグの href 属性を取得
     */
    private function get_content_anchor($content)
    {

        $pattern = "|<a href=\"(.*?)\".*?>(.*?)</a>|mis";
        if (preg_match_all($pattern, $content, $anchors)) {
            if (is_array($anchors) && isset($anchors[1])) {
                return $anchors[1];
            } else {
                return false;
            }
        } else {
            return false;
        }
    }

    /**
     * ページのHTML取得
     */
    private function getHTMLPage($url)
    {

        // curl Open
        $ch = curl_init();

        //オプション
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        $html =  curl_exec($ch);

        // curl Close
        curl_close($ch);

        return $html;
    }

    /**
     * nodeをHTMLとして取り出す
     */
    private function getInnerHtml($node)
    {

        // node が空の場合
        if (empty($node)) {
            return "";
        }

        $children = $node->childNodes;
        $html = '';
        foreach ($children as $child) {
            $html .= $node->ownerDocument->saveHTML($child);
        }
        return $html;
    }

    /**
     * フレームデザインの取得
     */
    private function getFrameDesign($classes)
    {

        // none
        if (stripos($classes, 'panel-none') !== false) {
            return 'none';
        }
        // primary
        if (stripos($classes, 'panel-primary') !== false) {
            return 'primary';
        }
        // info
        if (stripos($classes, 'panel-info') !== false) {
            return 'info';
        }
        // success
        if (stripos($classes, 'panel-success') !== false) {
            return 'success';
        }
        // warning
        if (stripos($classes, 'panel-warning') !== false) {
            return 'warning';
        }
        // danger
        if (stripos($classes, 'panel-danger') !== false) {
            return 'danger';
        }

        // default
        return 'default';
    }

    /**
     * ID のゼロ埋め
     */
    private function zeroSuppress($id)
    {
        // ページID がとりあえず、1万ページ未満で想定。
        // ここの桁数を上げれば、さらに大きなページ数でも処理可能
        return sprintf("%'.04d", $id);
    }

    /**
     * 経路探索キーの取得（作成）
     */
    private function getRouteStr($nc2_page, $nc2_sort_pages)
    {
        // 前提として、最低限のソートとして、同一階層でのソートができている。
        // ページデータを経路探索をキーに設定済みの配列から、親を探して、自分の経路探索キーを生成する。
        // 経路探索キーは 0021_0026 のように、{第1階層ページID}_{第2階層ページID}_{...} のように生成する。
        foreach($nc2_sort_pages as $nc2_sort_page_key => $nc2_sort_page) {
            if ($nc2_sort_page->page_id == $nc2_page->parent_id) {
                return $nc2_sort_page_key . '_' . $this->zeroSuppress($nc2_page->page_id);
            }
        }

        return $this->zeroSuppress($nc2_page->page_id);
    }

    /**
     * NC2 からデータをエクスポート
     */
    private function migrationNC2()
    {
        // NetCommons2 のページデータの取得

        // 【対象】
        // パブリックのみ（グループルームは今後、要考慮）
        // root_id = 0 は 'グループスペース' などの「くくり」なので不要
        // display_sequence = 0 はヘッダーカラムなどの共通部分

        // 【ソート】
        // space_type でソートする。（パブリック、グループ）
        // thread_num, display_sequence でソートする。

        $nc2_pages = Pages::where('private_flag', 0)
                          ->where('root_id', '<>', 0)
                          ->where('display_sequence', '<>', 0)
                          ->orderBy('space_type')
                          ->orderBy('thread_num')
                          ->orderBy('display_sequence')
                          ->get();

        // NC2 のページデータは隣接モデルのため、ページ一覧を一発でソートできない。
        // そのため、取得したページデータを一度、経路探索モデルに変換する。
        $nc2_sort_pages = array();

        // 経路探索の文字列をキーにしたページ配列の作成
        foreach($nc2_pages as $nc2_page) {
            $nc2_sort_pages[$this->getRouteStr($nc2_page, $nc2_sort_pages)] = $nc2_page;
        }

        // 経路探索の文字列（キー）でソート
        ksort($nc2_sort_pages);
        foreach($nc2_sort_pages as $nc2_sort_page_key => $nc2_sort_page) {
            echo $nc2_sort_page_key . ':' . $nc2_sort_page->page_name . "\n";
        }
    }
}
