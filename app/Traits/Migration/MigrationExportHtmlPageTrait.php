<?php

namespace App\Traits\Migration;

use GuzzleHttp\Client;
use GuzzleHttp\Psr7\Uri;
use GuzzleHttp\Psr7\UriResolver;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Log;

use App\Utilities\Migration\MigrationHttpClientUtils;
use App\Utilities\Migration\MigrationUtils;
use App\Utilities\Url\UrlUtils;

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
     *
     * @param string $url
     * @param integer $page_id
     * @return void
     */
    private function migrationHtmlPage(string $url, int $page_id, array $http_options = []) : void
    {
        if (!UrlUtils::isGlobalHttpUrl($url)) {
            Log::warning('[migrationHtmlPage] Rejected non-global URL: ' . $url);
            return;
        }

        // マイグレーション用のディレクトリに$page_idのディレクトリが存在する場合は削除する
        if (Storage::exists("migration/import/pages/" . $page_id)) {
            // 指定されたディレクトリを削除
            Storage::deleteDirectory("migration/import/pages/" . $page_id);
        }

        // 画像ファイルや添付ファイルを取得する場合のテンポラリ・ディレクトリ
        Storage::makeDirectory('migration/import/pages/' . $page_id);

        // 指定されたページのHTML を取得（リダイレクト先URLも都度検証する）
        $result_array = $this->executeMigrationHtmlRequest($url, $http_options);
        if ($result_array === null) {
            return;
        }

        $html = $result_array['body'];
        $effective_url = $result_array['effective_url'];

        // 実際に取得したURLからルートURLとディレクトリまでのURLを抽出する
        $root_url = $this->extractRootURL($effective_url);
        $target_dir_url = $this->extractUrlDirectory($effective_url);

        // HTMLドキュメントの解析準備
        $dom = new \DOMDocument;

        // 外部ページには非標準タグ/壊れたEntityが含まれることがあるため、
        // libxml警告は内部エラーとして扱い、取り込み処理は継続する。
        $this->loadHtmlDocument($dom, $html);
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
            $http_client = $this->migrationHttpCreateClient($http_options);

            // HTML 中の画像ファイルをループで処理
            $frame_ini .= "\n[image_names]\n";
            $image_index = 0;
            foreach ($image_paths as $image_path) {
                $image_index++;
                $download_img_path='';
                $img_extension = null;

                // $image_path が絶対パスか相対パスか判別して、絶対パスの場合はそのまま、相対パスの場合はアクセスURLを組成する
                if ($this->isAbsoluteURL($image_path)) {
                    $download_img_path = $image_path;
                } else {
                    // $image_pathがスラッシュで始まっている場合はルートURLと連結、そうでない場合はディレクトリまで含んだURLと連結
                    $download_img_path = strpos($image_path, '/') === 0 ?  $root_url . $image_path : $target_dir_url . '/' . $image_path;
                }
                Log::debug('download_img_path: ' . $download_img_path);

                if (!UrlUtils::isGlobalHttpUrl((string) $download_img_path)) {
                    Log::warning('[migrationHtmlPage] Skip non-global resource URL: ' . $download_img_path);
                    continue;
                }

                $file_name = "frame_0001_" . $image_index;
                $save_path = 'migration/import/pages/' . $page_id . "/" . $file_name;
                $save_storage_path = storage_path() . '/app/' . $save_path;

                try {
                    $download_response = $this->migrationHttpDownloadToFile($http_client, $download_img_path, $save_storage_path, $http_options);
                } catch (\RuntimeException $e) {
                    Storage::delete($save_path);
                    continue;
                }

                // ステータス404はスキップ
                Log::debug('http_code: ' . $download_response['http_code']);
                if ((int) $download_response['http_code'] === 404) {
                    Log::debug('File deleted.');
                    Storage::delete($save_path);
                    continue;
                }

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

                // ファイルが存在する、且つ、拡張子が取得できた場合、ファイル名に拡張子を付与して保存
                if (Storage::exists($save_path) && $img_extension) {
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
     * ページのHTMLを取得する（リダイレクト時は遷移先URLを都度検証）
     *
     * @param string $url
     * @return array|null ['body' => string, 'effective_url' => string]
     */
    private function executeMigrationHtmlRequest(string $url, array $http_options = []): ?array
    {
        $current_url = $url;
        $max_redirects = 5;
        $http_client = $this->migrationHttpCreateClient($http_options);

        for ($redirect_count = 0; $redirect_count <= $max_redirects; $redirect_count++) {
            $response = $this->executeSingleMigrationRequest($http_client, $current_url, $http_options);

            if (!$this->isRedirectHttpCode($response['http_code'])) {
                return [
                    'body' => $response['body'],
                    'effective_url' => $current_url,
                ];
            }

            if ($redirect_count === $max_redirects) {
                Log::warning('[migrationHtmlPage] Too many redirects: ' . $url);
                return null;
            }

            $redirect_url = $this->buildRedirectUrl($current_url, $response['location']);
            if ($redirect_url === '') {
                Log::warning('[migrationHtmlPage] Invalid redirect URL. Source URL: ' . $current_url . ' Location: ' . $response['location']);
                return null;
            }

            if (!UrlUtils::isGlobalHttpUrl($redirect_url)) {
                Log::warning('[migrationHtmlPage] Rejected redirect destination URL: ' . $redirect_url);
                return null;
            }

            $current_url = $redirect_url;
        }

        return null;
    }

    /**
     * 単一URLへHTTPリクエストを実行する（自動リダイレクト追跡なし）
     *
     * @param Client $http_client
     * @param string $url
     * @return array ['body' => string, 'http_code' => int, 'location' => string]
     */
    private function executeSingleMigrationRequest(Client $http_client, string $url, array $http_options = []): array
    {
        if (!UrlUtils::isGlobalHttpUrl($url)) {
            Log::warning('[migrationHtmlPage] Rejected non-global URL: ' . $url);
            throw new \RuntimeException('[migrationHtmlPage] Rejected non-global URL: ' . $url);
        }

        return $this->migrationHttpGet($http_client, $url, $http_options);
    }

    /**
     * 移行処理用HTTPクライアントを生成する
     *
     * @return Client
     */
    protected function migrationHttpCreateClient(array $http_options = []): Client
    {
        return MigrationHttpClientUtils::createClient($http_options);
    }

    /**
     * 移行処理用HTTP GET（文字列レスポンス）
     *
     * @param Client $http_client
     * @param string $url
     * @return array
     */
    protected function migrationHttpGet(Client $http_client, string $url, array $http_options = []): array
    {
        return MigrationHttpClientUtils::get($http_client, $url, $http_options);
    }

    /**
     * 移行処理用HTTP GET（ファイル保存）
     *
     * @param Client $http_client
     * @param string $url
     * @param string $sink_path
     * @return array
     */
    protected function migrationHttpDownloadToFile(Client $http_client, string $url, string $sink_path, array $http_options = []): array
    {
        return MigrationHttpClientUtils::downloadToFile($http_client, $url, $sink_path, $http_options);
    }

    /**
     * HTTPステータスコードがリダイレクトかどうかを判定する
     *
     * @param int $http_code
     * @return bool
     */
    private function isRedirectHttpCode(int $http_code): bool
    {
        return in_array($http_code, [301, 302, 303, 307, 308], true);
    }

    /**
     * レスポンスLocationヘッダーを絶対URLへ解決する
     *
     * @param string $current_url
     * @param string $location
     * @return string
     */
    private function buildRedirectUrl(string $current_url, string $location): string
    {
        $location = trim($location);
        if ($location === '') {
            return '';
        }

        try {
            return (string) UriResolver::resolve(new Uri($current_url), new Uri($location));
        } catch (\InvalidArgumentException $e) {
            return '';
        }
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
     * HTMLをDOMへ読み込む（libxml警告は内部で処理）
     *
     * @param \DOMDocument $dom
     * @param string $html
     * @return void
     */
    private function loadHtmlDocument(\DOMDocument $dom, string $html): void
    {
        $previous_internal_errors = libxml_use_internal_errors(true);
        libxml_clear_errors();

        try {
            $dom->loadHTML($html);
        } finally {
            libxml_clear_errors();
            libxml_use_internal_errors($previous_internal_errors);
        }
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
    private function isAbsoluteURL(string $url) : bool
    {
        return parse_url($url, PHP_URL_SCHEME) !== null;
    }

    /**
     * URLからルートURLを抽出する
     *
     * @param string $url
     * @return string
     */
    private function extractRootURL(string $url) : string
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
    private function extractUrlDirectory(string $url) : string
    {
        $parsed_url = parse_url($url);
        $directory_url = $parsed_url['scheme'] . '://' . $parsed_url['host'] . $parsed_url['path'];
        return substr($directory_url, 0, strrpos($directory_url, "/") + 1);
    }
}
