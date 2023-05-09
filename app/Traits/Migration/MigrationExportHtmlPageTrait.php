<?php

namespace App\Traits\Migration;

use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Log;

use App\Utilities\Migration\MigrationUtils;
use App\Utilities\Curl\CurlUtils;

/**
 * １つのウェブページからデータをエクスポート（想定形式：HTML）
 *
 * @author 井上 雅人 <inoue@opensource-workshop.jp / masamasamasato0216@gmail.com>
 * @copyright OpenSource-WorkShop Co.,Ltd. All Rights Reserved
 * @category 移行
 * @package trait
 */
trait MigrationExportHtmlPageTrait
{
    /**
     * ファイル取得時のHTTPヘッダーからcontent_disposition
     * CURL でクロージャで取得するため、値の移送用インスタンス変数を用意
     * ここから日本語ファイル名を取得する。
     */
    private $content_disposition = "";

    /**
     * ページのHTML取得
     */
    private function migrationHtmlPage($url, $page_id)
    {
        // マイグレーション用のディレクトリに$page_idのディレクトリが存在する場合は削除する
        if (Storage::exists("migration/import/pages/" . $page_id)) {
            // 指定されたディレクトリを削除
            Storage::deleteDirectory("migration/import/pages/" . $page_id);
        }

        // $urlからルートURLとディレクトリまでのURLをそれぞれ抽出する
        $root_url = $this->extractRootURL($url);
        $target_dir_url = $this->extractUrlDirectory($url);

        // 画像ファイルや添付ファイルを取得する場合のテンポラリ・ディレクトリ
        Storage::makeDirectory('migration/import/pages/' . $page_id);

        // 指定されたページのHTML を取得
        $result_array = CurlUtils::execute($url);
        $html = $result_array['body'];

        // HTMLドキュメントの解析準備
        $dom = new \DOMDocument;

        // DOMDocument が返ってくる。
        @$dom->loadHTML($html);
        $xpath = new \DOMXPath($dom);

        // bodyタグ配下を抽出する
        $dom_nodes = $xpath->query('//body/*');

        // 除外タグを設定する TODO: 除外タグ（header, footer等）を画面から指定できるようにする
        $exclusion_tag_names = ['xxxx'];

        // 除外タグを除いたHTMLを抽出する
        $content_html = '';
        foreach ($dom_nodes as $dom_node) {
            // 除外タグの場合は次のノードへ
            if (in_array($dom_node->tagName, $exclusion_tag_names)) {
                continue;
            }
            // HTML抽出
            $content_html .= $this->getHtmlInnerHtml($dom_node) . PHP_EOL;
        }

        // フレームiniファイルの生成
        $frame_ini = "[frame_base]\n";
        $frame_ini .= "area_id = 2\n";
        $frame_ini .= "frame_title = \"" . '無題' . "\"\n";

        // フレームデザイン
        $expression = './/@class';
        $frame_ini .= "frame_design = \"" . 'default' . "\"\n";

        // プラグイン情報
        $frame_ini .= "plugin_name = \"contents\"\n";
        $frame_ini .= "template = \"default\"\n";

        // 元のNC3情報
        $frame_ini .= "\n";
        $frame_ini .= "[source_info]\n";

        // フレームID
        $frame_ini .= "source_key = \"" . 'xxxxx' . "\"\n";

        $frame_ini .= "target_source_table = \"announcement\"\n";
        

        // 画像ファイルの抽出 ※抽出ファイルがない場合はfalseが返る
        $image_paths = MigrationUtils::getContentImage($content_html);

        // 画像ファイルのダウンロード
        if ($image_paths) {
            // HTML 中の画像ファイルをループで処理
            $frame_ini .= "\n[image_names]\n";
            $image_index = 0;
            foreach ($image_paths as $image_path) {
                $image_index++;
                $download_img_path='';
                $img_extension = null;

                // $image_path が絶対パスか相対パスか判別して、絶対パスの場合はそのまま、相対パスの場合はアクセスURLを組成する
                if($this->isAbsoluteURL($image_path)) {
                    $download_img_path = $image_path;
                } else {
                    // $image_pathがスラッシュで始まっている場合はルートURLと連結、そうでない場合はディレクトリまで含んだURLと連結
                    $download_img_path = strpos($image_path, '/') === 0 ?  $root_url . $image_path : $target_dir_url . '/' . $image_path;
                }
                Log::debug('download_img_path: ' . $download_img_path);

                $file_name = "frame_0001_" . $image_index;
                $save_path = 'migration/import/pages/' . $page_id . "/" . $file_name;
                $save_storage_path = storage_path() . '/app/' . $save_path;

                // CURL 設定、ファイル取得
                $ch = curl_init($download_img_path);
                $fp = fopen($save_storage_path, 'w');
                curl_setopt($ch, CURLOPT_FILE, $fp);
                curl_setopt($ch, CURLOPT_HEADER, false);
                curl_setopt($ch, CURLOPT_HEADERFUNCTION, array(&$this,'callbackHtmlHeader'));
                $result = curl_exec($ch);

                // エラーがあった場合はスキップ
                if (!empty(curl_errno($ch))) {
                    Log::debug('curl_errno: ' . curl_errno($ch));
                    continue;
                }
                // ステータス404はスキップ
                Log::debug('curl_getinfo: ' . curl_getinfo($ch, CURLINFO_HTTP_CODE));
                if (trim(curl_getinfo($ch, CURLINFO_HTTP_CODE)) == '404') {
                    Log::debug('File deleted.');
                    Storage::delete($save_path);
                    continue;
                }

                curl_close($ch);
                fclose($fp);

                //@getimagesize関数で画像情報を取得する
                list($img_width, $img_height, $mime_type, $attr) = @getimagesize($save_storage_path);

                //list関数の第3引数には@getimagesize関数で取得した画像のMIMEタイプが格納されているので条件分岐で拡張子を決定する
                switch ($mime_type) {
                    case IMAGETYPE_JPEG:    // jpegの場合
                        //拡張子の設定
                        $img_extension = "jpg";
                        break;
                    case IMAGETYPE_PNG:     // pngの場合
                    //拡張子の設定
                        $img_extension = "png";
                        break;
                    case IMAGETYPE_GIF:     // gifの場合
                        //拡張子の設定
                        $img_extension = "gif";
                        break;
                    default:
                        Log::debug('Something is wrong: ' . $download_img_path);
                        break;
                }

                // 拡張子が取得できた場合はファイル名に拡張子を付与して保存
                if($img_extension){
                    Storage::move($save_path, $save_path . '.' . $img_extension);
    
                    // 画像の設定情報の記載
                    $frame_ini .= $file_name . '.' . $img_extension . ' = ""' . PHP_EOL;

                    // content 内の保存した画像のパスを修正
                    $content_html = str_replace($image_path, $file_name . '.' . $img_extension, $content_html);
                }
            }
        }

        // TODO: 画像以外のファイルの取得と保存

        // フレーム設定ファイルに [contents] 追加
        $frame_ini .= "\n";
        $frame_ini .= "[contents]\n";
        $frame_ini .= "contents_file = \"frame_0001.html\"\n";

        // フレーム設定ファイルの出力
        Storage::put('migration/import/pages/' . $page_id . '/frame_0001.ini', $frame_ini);

        // HTML content の保存
        Storage::put('migration/import/pages/' . $page_id . "/frame_0001.html", trim($content_html));
    }

    /**
     * nodeをHTMLとして取り出す
     */
    private function getHtmlInnerHtml($node)
    {
        // node が空の場合
        if (empty($node)) {
            return "";
        }
        return $node->ownerDocument->saveHTML($node);
    }

    /**
     * CURL のhttp ヘッダー処理コールバック関数
     */
    private function callbackHtmlHeader($ch, $header_line)
    {
        // Content-Disposition の場合に処理する。
        // （この関数はhttp ヘッダーの行数分、呼び出される）
        if (strpos($header_line, "Content-Disposition") !== false) {
            $this->content_disposition = urldecode($header_line);
        }

        return strlen($header_line);
    }

    /**
      * URLが絶対PATHかどうかを判定する
      *
      * @param string $url
      * @return boolean
      */
    function isAbsoluteURL(string $url) : bool
    {
        return parse_url($url, PHP_URL_SCHEME) !== null;
    }

    /**
     * URLからルートURLを抽出する
     *
     * @param string $url
     * @return string
     */
    function extractRootURL(string $url) : string
    {
        $parsed_url = parse_url($url);
        return $parsed_url['scheme'] . '://' . $parsed_url['host'];
    }

    /**
     * URLからディレクトリ部分までを抽出する
     *
     * @param string $url
     * @return string
     */
    function extractUrlDirectory(string $url) : string
    {
        $parsed_url = parse_url($url);
        $directory_url = $parsed_url['scheme'] . '://' . $parsed_url['host'] . $parsed_url['path'];
        return substr($directory_url, 0, strrpos($directory_url, "/") + 1);
    }
}