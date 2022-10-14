<?php

namespace App\Traits\Migration;

use Illuminate\Support\Facades\Storage;

use App\Utilities\Migration\MigrationUtils;

/**
 * NC3 の１つのウェブページからデータをエクスポート
 * MigrationTraitから切り出し
 *
 * @author 牟田口 満 <mutaguchi@opensource-workshop.jp>
 * @copyright OpenSource-WorkShop Co.,Ltd. All Rights Reserved
 * @category 移行
 * @package trait
 */
trait MigrationExportNc3PageTrait
{
    /**
     * ファイル取得時のHTTPヘッダーからcontent_disposition
     * CURL でクロージャで取得するため、値の移送用いインスタンス変数を用意
     * ここから日本語ファイル名を取得する。
     */
    private $content_disposition = "";

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
        Storage::makeDirectory('migration/import/pages/' . $page_id);

        // 指定されたページのHTML を取得
        // $html = $this->getHTMLPage($url);
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        $html =  curl_exec($ch);
        curl_close($ch);

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
            //var_dump($this->getNc3InnerHtml($frame_title));
            //Log::debug($this->getNc3InnerHtml($frame_title));

            // フレーム設定の保存用変数
            $frame_ini = "[frame_base]\n";
            $frame_ini .= "area_id = 2\n";
            $frame_ini .= "frame_title = \"" . $this->getNc3InnerHtml($frame_title) . "\"\n";

            // フレームデザイン
            $expression = './/@class';
            $frame_design = $xpath->query($expression, $section)->item(0);
            $frame_ini .= "frame_design = \"" . $this->getNc3FrameDesign($frame_design->value) . "\"\n";

            // プラグイン情報
            $frame_ini .= "plugin_name = \"contents\"\n";
            $frame_ini .= "template = \"default\"\n";

            // 元のNC3情報
            $frame_ini .= "\n";
            $frame_ini .= "[source_info]\n";
            $frame_ini .= "target_source_table = \"announcement\"\n";

            // 本文を抜き出します。
            $expression = './/div[contains(@class, "panel-body")]/article';
            $content = $xpath->query($expression, $section)->item(0);
            //var_dump($this->getNc3InnerHtml($content));
            //Log::debug($this->getNc3InnerHtml($content));

            // HTML の保存用変数
            $content_html = $this->getNc3InnerHtml($content);

            // 本文から画像(img src)を抜き出す
            $images = MigrationUtils::getContentImage($content_html);
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
                    $savePath = 'migration/import/pages/' . $page_id . "/" . $file_name;
                    $saveStragePath = storage_path() . '/app/' . $savePath;

                    // CURL 設定、ファイル取得
                    $ch = curl_init($downloadPath);
                    $fp = fopen($saveStragePath, 'w');
                    curl_setopt($ch, CURLOPT_FILE, $fp);
                    curl_setopt($ch, CURLOPT_HEADER, false);
                    curl_setopt($ch, CURLOPT_HEADERFUNCTION, array(&$this,'callbackNc3Header'));
                    $result = curl_exec($ch);
                    if (!empty(curl_errno($ch))) {
                        continue;
                    }
                    curl_close($ch);
                    fclose($fp);
                    //echo $this->content_disposition;

                    //@getimagesize関数で画像情報を取得する
                    list($img_width, $img_height, $mime_type, $attr) = @getimagesize($saveStragePath);

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
                    }

                    // 拡張子の変更
                    Storage::delete($savePath . '.' . $img_extension);
                    Storage::move($savePath, $savePath . '.' . $img_extension);

                    // 画像の設定情報の記載
                    $frame_ini .= $file_name . '.' . $img_extension . ' = "' . $this->searchNc3FileName($this->content_disposition) . "\"\n";

                    // content 内の保存した画像のパスを修正
                    $content_html = str_replace($image_url, $file_name . '.' . $img_extension, $content_html);

                    //拡張子の出力
                    //echo $img_extension;
                    //echo "\n";
                }
            }

            // 本文からアンカー(a href)を抜き出す
            $anchors = MigrationUtils::getContentAnchor($content_html);
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
                        curl_setopt($ch, CURLOPT_HEADERFUNCTION, array(&$this,'callbackNc3Header'));
                        $result = curl_exec($ch);
                        curl_close($ch);
                        fclose($fp);

                        //echo $this->content_disposition;

                        // ファイルの拡張子の取得
                        $file_extension = MigrationUtils::getExtension($this->searchNc3FileName($this->content_disposition));

                        // 拡張子の変更
                        Storage::delete($savePath . '.' . $file_extension);
                        Storage::move($savePath, $savePath . '.' . $file_extension);

                        // ファイルの設定情報の記載
                        $frame_ini .= $file_name . '.' . $file_extension . ' = "' . $this->searchNc3FileName($this->content_disposition) . "\"\n";

                        // content 内の保存したファイルのパスを修正
                        $content_html = str_replace($anchor_href, $file_name . '.' . $file_extension, $content_html);

                        //拡張子の出力
                        //echo $file_extension;
                        //echo "\n";
                    }
                }
            }

            // フレーム設定ファイルに [contents] 追加
            $frame_ini .= "\n";
            $frame_ini .= "[contents]\n";
            $frame_ini .= "contents_file = \"frame_" . $frame_index_str . ".html\"\n";

            // フレーム設定ファイルの出力
            Storage::put('migration/import/pages/' . $page_id . "/frame_" . $frame_index_str . '.ini', $frame_ini);

            // Contents 変換
            $content_html = $this->migrationNc3Html($content_html);

            // HTML content の保存
            Storage::put('migration/import/pages/' . $page_id . "/frame_" . $frame_index_str . '.html', trim($content_html));
        }
    }

    /**
     * nodeをHTMLとして取り出す
     */
    private function getNc3InnerHtml($node)
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
    private function getNc3FrameDesign($classes)
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
     * NC3 からConnect-CMS へタグ変換
     */
    private function migrationNc3Html($content_html)
    {
        // 画像のレスポンスCSS
        $content_html = $this->replaceNc3Css('img-responsive', 'img-fluid', $content_html);

        // NC3 用画像CSS（削除）
        $content_html = $this->replaceNc3Css('nc3-img-block', '', $content_html);
        $content_html = $this->replaceNc3Css('nc3-img', '', $content_html);
        $content_html = $this->replaceNc3Css('thumbnail', 'img-thumbnail', $content_html);

        return $content_html;
    }

    /**
     * CSS 中のクラス名の変換
     */
    private function replaceNc3Css($search, $replace, $subject)
    {
        $pattern = '/class=((?:\s|")?)(.*?)((?:\s|")+)' . $search . '((?:\s|")+)(.*?)((?:\s|")?)/';
        $replacement = 'class=$1$2$3' . $replace . '$4$5$6';
        $content_html = preg_replace($pattern, $replacement, $subject);
        return $content_html;
    }

    /**
     * CURL のhttp ヘッダー処理コールバック関数
     */
    private function callbackNc3Header($ch, $header_line)
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
     * content_disposition からファイル名の抜き出し
     */
    private function searchNc3FileName($content_disposition)
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
}
