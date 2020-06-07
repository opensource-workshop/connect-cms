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

use App\Models\Migration\Nc2\Announcement;
use App\Models\Migration\Nc2\Blocks;
use App\Models\Migration\Nc2\Pages;
use App\Models\Migration\Nc2\Upload;

use App\Traits\ConnectCommonTrait;

/**
 * 移行プログラム
 * データは storage/app/migration に保持し、そこからインポートする。
 * 数値のフォルダはインポート先のページ指定で実行したもの。
 * _付のフォルダは新規ページを作るもの。
 * @付のフォルダは共通データ（uploadsなど）
 */
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
     * ページ、フレームのCSV出力
     */
    private $frame_tree = "page_id,ページタイトル,固定リンク,モジュール,block_id,ブロックタイトル\n";

    /**
     * ログのパス
     * ログ自体はプログラムが途中でコケても残るように、append する。
     * ここは、ログファイルの名前を時分秒を付けて保存したいため、シングルトンでファイル名を保持するためのもの。
     */
    private $log_path = null;

    /**
     * uploads.ini
     */
    private $uploads_ini = null;

    /**
     * NC2 action_name -> Connect-CMS plugin_name 変換用テーブル
     * 開発中 or 開発予定のものは 'Development' にする。
     * 廃止のものは 'Abolition' にする。
     */
    protected $plugin_name = [
        'announcement'  => 'contents',     // お知らせ
        'assignment'    => 'Development',  // レポート
        'bbs'           => 'Development',  // 掲示板
        'cabinet'       => 'Development',  // キャビネット
        'calendar'      => 'Development',  // カレンダー
        'chat'          => 'Development',  // チャット
        'circular'      => 'Development',  // 回覧板
        'counter'       => 'Development',  // カウンター
        'iframe'        => 'Development',  // iFrame
        'imagine'       => 'Abolition',    // imagine
        'journal'       => 'blogs',        // ブログ
        'language'      => 'Development',  // 言語選択
        'linklist'      => 'Development',  // リンクリスト
        'login'         => 'Development',  // ログイン
        'menu'          => 'menus',        // メニュー
        'multidatabase' => 'databases',    // データベース
        'online'        => 'Development',  // オンライン状況
        'photoalbum'    => 'Development',  // フォトアルバム
        'pm'            => 'Abolition',    // プライベートメッセージ
        'questionnaire' => 'Development',  // アンケート
        'quiz'          => 'Development',  // 小テスト
        'registration'  => 'forms',        // フォーム
        'reservation'   => 'reservations', // 施設予約
        'rss'           => 'Development',  // RSS
        'search'        => 'searchs',      // 検索
        'todo'          => 'Development',  // ToDo
        'whatsnew'      => 'whatsnews',    // 新着情報
    ];

    /**
     * テストメソッド
     */
    private function getTestStr()
    {
        return "This is MigrationTrait test.";
    }

    /**
     * テストメソッド
     */
    private function putLog($str, $nc2_block = null, $filename = 'migration')
    {
        // ログのファイル名の設定
        if (empty($this->log_path)) {
            $this->log_path = "migration/" . $filename . "_" . date('His') . '.log';
        }

        $append_str = "";
        if (!empty($nc2_block)) {
            $append_str .= $nc2_block->page_id . ",";
            $append_str .= $nc2_block->block_id . ",";
        }

        Storage::append($this->log_path, $append_str . $str);
    }

    /**
     * Connect-CMS 移行形式のHTML をインポート
     */
    private function importSite()
    {
        // echo "importSite";

        // アップロード・ファイルの取り込み
        $this->importUpload();
return;

        // 新ページの取り込み
        $paths = File::glob(storage_path() . '/app/migration/_*');

        // 新ページのループ
        foreach($paths as $path) {

            // ページの設定取得
            $page_ini = parse_ini_file($path. '/page.ini', true);
            //print_r($page_ini);

            // 固定リンクでページの存在確認
            // 同じ固定リンクのページが存在した場合は、そのページを使用する。
            $page = Page::where('permanent_link', $page_ini['page_base']['permanent_link'])->first();
            // var_dump($page);

            // 対象のURL がなかった場合はページの作成
            if (empty($page)) {
                $page = Page::create(['page_name'         => $page_ini['page_base']['page_name'],
                                      'permanent_link'    => $page_ini['page_base']['permanent_link'],
                                      'base_display_flag' => $page_ini['page_base']['base_display_flag'],
                                    ]);
            }
            // ページの中身の作成
            $this->importHtmlImpl($page, $path);
        }
    }

    /**
     * Connect-CMS 移行形式のアップロード・ファイルをインポート
     */
    private function importUpload()
    {
        // アップロード・ファイル定義の取り込み
        $uploads_ini = parse_ini_file(storage_path() . '/app/migration/@uploads/uploads.ini', true);

        // アップロード・ファイルのループ
        if (array_key_exists('uploads', $uploads_ini) && array_key_exists('upload', $uploads_ini['uploads'])) {
            foreach($uploads_ini['uploads']['upload'] as $upload_key => $upload_item) {
                // Uploads テーブルの登録
                $upload = Uploads::create([
                    'client_original_name' => $uploads_ini[$upload_key]['client_original_name'],
                    'mimetype'             => $uploads_ini[$upload_key]['mimetype'],
                    'extension'            => $uploads_ini[$upload_key]['extension'],
                    'size'                 => $uploads_ini[$upload_key]['size'],
                    'plugin_name'          => $uploads_ini[$upload_key]['plugin_name'],
                    'page_id'              => $uploads_ini[$upload_key]['page_id'],
                    'temporary_flag'       => 0,
                ]);

                // ファイルのコピー
                $source_file_path = 'migration/@uploads/' . $upload_item;
                $destination_file_path = $this->getDirectory($upload->id) . '/' . $upload->id . '.' . $uploads_ini[$upload_key]['extension'];
                Storage::copy($source_file_path, $destination_file_path);
            }
        }
    }

    /**
     * Connect-CMS 移行形式のHTML をインポート
     */
    private function importHtml($page_id, $dir = null)
    {
        $page = Page::find($page_id);
        $this->importHtmlImpl($page);
    }

    /**
     * Connect-CMS 移行形式のHTML をインポート
     */
    private function importHtmlImpl($page, $dir = null)
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

        // インポート元のディレクトリが指定されていない場合は、ページid と同じ名前のディレクトリがあるとする
        if (empty($dir)) {
            $dir = storage_path() . '/app/migration/' . $page->id;
        }

        // フレーム単位のini ファイルの取得
        $frame_ini_paths = File::glob($dir . '/frame_*.ini');

        // フレームのループ
        $display_sequence = 0;
        foreach ($frame_ini_paths as $frame_ini_path) {
            // echo $frame_ini_path . "\n";

            $display_sequence++;

            // フレーム毎のini_file の解析
            $frame_ini = parse_ini_file($frame_ini_path, true);
            //print_r($ini_array);

            // プラグイン毎の登録処理へ
            $this->importPlugin($page, dirname($frame_ini_path), $frame_ini, $display_sequence);
        }
        // echo $page_id . ' の移行が完了';
    }

    /**
     * プラグイン毎の登録処理
     */
    private function importPlugin($page, $page_dir, $frame_ini, $display_sequence)
    {
        // プラグインが指定されていない場合は戻る
        if (!array_key_exists('frame_base', $frame_ini) || !array_key_exists('plugin_name', $frame_ini['frame_base'])) {
            return;
        }

        // プラグイン名
        $plugin_name = $frame_ini['frame_base']['plugin_name'];

        // プラグイン振り分け
        if ($plugin_name == 'contents') {
            $this->importPluginContents($page, $page_dir, $frame_ini, $display_sequence);
        }
    }

    /**
     * 固定記事プラグインの登録処理
     */
    private function importPluginContents($page, $page_dir, $frame_ini, $display_sequence)
    {
        // コンテンツが指定されていない場合は戻る
        if (!array_key_exists('contents', $frame_ini) || !array_key_exists('contents_file', $frame_ini['contents'])) {
            return;
        }

        // HTML コンテンツの取得（画像処理をループしながら、タグを編集するので、ここで読みこんでおく）
        $html_file_path = $page_dir . '/' . $frame_ini['contents']['contents_file'];
        $content_html = File::get($html_file_path);

        // Buckets 登録
        // echo "Buckets 登録\n";
        $bucket = Buckets::create(['bucket_name' => '無題', 'plugin_name' => 'contents']);

        // Frames 登録
        // echo "Frames 登録\n";

        // Frame タイトル
        $frame_title = '[無題]';
        if (array_key_exists('frame_base', $frame_ini) && array_key_exists('frame_title', $frame_ini['frame_base'])) {
            $frame_title = $frame_ini['frame_base']['frame_title'];
        }

        // Frame デザイン
        $frame_design = 'default';
        if (array_key_exists('frame_base', $frame_ini) && array_key_exists('frame_design', $frame_ini['frame_base'])) {
            $frame_design = $frame_ini['frame_base']['frame_design'];
        }

        $frame = Frame::create(['page_id'          => $page->id,
                                'area_id'          => 2,
                                'frame_title'      => $frame_title,
                                'frame_design'     => $frame_design,
                                'plugin_name'      => 'contents',
                                'frame_col'        => 0,
                                'template'         => 'default',
                                'bucket_id'        => $bucket->id,
                                'display_sequence' => $display_sequence,
                               ]);

        // NC2 から移行した場合：[upload_images] の画像を登録
        // 
        // --- uploads.ini
        // upload[1] = "upload_00001.jpg"
        // 
        // --- frame_0001.ini
        // [upload_images]
        // 2 = "upload_00002.jpg"
        // 
        // --- frame_0001.html
        // img src="../@uploads/upload_00002.jpg"
        // 
        // 
        // 
        // 
        if (array_key_exists('upload_images', $frame_ini)) {
        }

        // NC3 のHTTP から移行した場合：[image_names] の画像を登録
        if (array_key_exists('image_names', $frame_ini)) {
            foreach ($frame_ini['image_names'] as $filename => $client_original_name) {
                // ファイルサイズ
                if (File::exists($page_dir . "/" . $filename)) {
                    $file_size = File::size($page_dir . "/" . $filename);
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
                              'page_id'              => $page->id,
                              'temporary_flag'       => 0,
                          ]);

                // ファイルのコピー
                $source_file_path = $page_dir. "/" . $filename;
                $destination_file_dir = storage_path() . "/app/" . $this->getDirectory($upload->id);
                $destination_file_path = $destination_file_dir . '/' . $upload->id . '.' . $this->getExtension($filename);

                if (!File::isDirectory($destination_file_dir)) {
                    File::makeDirectory($destination_file_dir, 0775, true);
                }
                File::copy($source_file_path, $destination_file_path);

                // 画像のパスの修正
                $content_html = str_replace($filename, '/file/' . $upload->id, $content_html);
            }
        }

        // NC3 のHTTP から移行した場合：[file_names] の画像を登録
        if (array_key_exists('file_names', $frame_ini)) {
            foreach ($frame_ini['file_names'] as $filename => $client_original_name) {
                // ファイルサイズ
                if (File::exists($page_dir . "/" . $filename)) {
                    $file_size = File::size($page_dir . "/" . $filename);
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
                              'page_id'              => $page->id,
                              'temporary_flag'       => 0,
                          ]);

                // ファイルのコピー
                $source_file_path = $page_dir. "/" . $filename;
                $destination_file_dir = storage_path() . "/app/" . $this->getDirectory($upload->id);
                $destination_file_path = $destination_file_dir . '/' . $upload->id . '.' . $this->getExtension($filename);
                if (!File::isDirectory($destination_file_dir)) {
                    File::makeDirectory($destination_file_dir, 0775, true);
                }
                File::copy($source_file_path, $destination_file_path);

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
        //var_dump($url);

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
            $frame_ini = "[frame_base]\n";
            $frame_ini .= "area_id = 2\n";
            $frame_ini .= "frame_title = \"" . $this->getInnerHtml($frame_title) . "\"\n";

            // フレームデザイン
            $expression = './/@class';
            $frame_design = $xpath->query($expression, $section)->item(0);
            $frame_ini .= "frame_design = \"" . $this->getFrameDesign($frame_design->value) . "\"\n";

            // プラグイン情報
            $frame_ini .= "plugin_name = \"contents\"\n";
            $frame_ini .= "nc2_module_name = \"announcement\"\n";

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

            // フレーム設定ファイルに [contents] 追加
            $frame_ini .= "\n";
            $frame_ini .= "[contents]\n";
            $frame_ini .= "contents_file = \"frame_" . $frame_index_str . ".html\"\n";

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
    private function zeroSuppress($id, $size = 4)
    {
        // ページID がとりあえず、1万ページ未満で想定。
        // ここの桁数を上げれば、さらに大きなページ数でも処理可能
        $size_str = sprintf("%'.02d", $size);

        return sprintf("%'." . $size_str . "d", $id);
    }

    /**
     * 経路探索キーの取得（作成）
     */
    private function getRouteStr($nc2_page, $nc2_sort_pages, $get_display_sequence = false)
    {
        // 経路探索パス(配列の route_path )
        // r{root_id}_{parent_id}_{parent_id}_{...}_{page_id}

        // ソート用の配列のキー(root_id を最初に持ってきて、display_sequence でつなぐ)
        // r{root_id}_{display_sequence}_{display_sequence}_{...}_{display_sequence}

        // 前提として、最低限のソートとして、同一階層でのソートができている。
        // ページデータを経路探索をキーに設定済みの配列から、親を探して、自分の経路探索キーを生成する。
        // 経路探索キーは 0021_0026 のように、{第1階層ページID}_{第2階層ページID}_{...} のように生成する。
        foreach($nc2_sort_pages as $nc2_sort_page_key => $nc2_sort_page) {
            if ($nc2_sort_page->page_id == $nc2_page->parent_id) {
                if ($get_display_sequence) {
                    // ソート用の配列のキーを取得
                    return $nc2_sort_page_key . '_' . $this->zeroSuppress($nc2_page->display_sequence);
                }
                else {
                    // 経路探索パス
                    return $nc2_sort_page->route_path . '_' . $this->zeroSuppress($nc2_page->page_id);
                }
            }
        }

        // まだ配列になかった場合（各スペースのルートページ）
        if ($get_display_sequence) {
            return 'r' . $this->zeroSuppress($nc2_page->root_id) . '_' . $this->zeroSuppress($nc2_page->display_sequence);
        }
        else {
            return 'r' . $this->zeroSuppress($nc2_page->root_id) . '_' . $this->zeroSuppress($nc2_page->page_id);
        }
    }

    /**
     * NC2 からデータをエクスポート
     *
     * 動かし方
     *
     * 【.env で以下のNC2 用の定義を設定】
     *
     * NC2_DB_CONNECTION=mysql
     * NC2_DB_HOST=127.0.0.1
     * NC2_DB_PORT=3306
     * NC2_DB_DATABASE=xxxxxx
     * NC2_DB_USERNAME=xxxxxx
     * NC2_DB_PASSWORD=xxxxxx
     * NC2_DB_PREFIX=netcommons2_ (例)
     *
     * 【実行コマンド】
     * php artisan command:MigrationFromNc2
     *
     * 【ブロック・ツリーのCSV】
     * migrationNC2() 関数の最後で echo $this->frame_tree; しています。
     * これをコマンドでファイルに出力すればCSV になります。
     *
     * 【移行データ】
     * storage\app\migration にNC2 をエクスポートしたデータが入ります。
     *
     * 【ログ】
     * migration/migrationNC2_{His}.log
     *
     * 【画像】
     * src にhttp 指定などで、移行しなかった画像はログに出力
     *
     *
     */
    private function migrationNC2($uploads_path)
    {
        // NetCommons2 のページデータの取得

        // 【対象】
        // パブリックのみ（グループルームは今後、要考慮）
        // root_id = 0 は 'グループスペース' などの「くくり」なので不要
        // display_sequence = 0 はヘッダーカラムなどの共通部分

        // 【ソート】
        // space_type でソートする。（パブリック、グループ）
        // thread_num, display_sequence でソートする。

        $this->putLog("page_id,block_id,message");
        $this->putLog(",,Start migrationNC2.");

        // uploads_path の最後に / がなければ追加
        if (mb_substr($uploads_path,-1) != '/') {
            $uploads_path = $uploads_path . '/';
        }

        // uploads データとファイルのエクスポート
        $this->nc2Uploads($uploads_path);

        // uploads のini ファイルの読み込み
        $this->uploads_ini = parse_ini_file(storage_path() . '/app/migration/@uploads/uploads.ini', true);

        // NC2 のページデータ
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
            $nc2_page->route_path = $this->getRouteStr($nc2_page, $nc2_sort_pages);
            $nc2_sort_pages[$this->getRouteStr($nc2_page, $nc2_sort_pages, true)] = $nc2_page;
        }

        // 経路探索の文字列（キー）でソート
        ksort($nc2_sort_pages);
        //Log::debug($nc2_sort_pages);

        // 新規ページ用のインデックス
        // 新規ページは _99 のように _ 付でページを作っておく。（_ 付はデータ作成時に既存page_id の続きで採番する）
        $new_page_index = 0;

        // ページのループ
        foreach($nc2_sort_pages as $nc2_sort_page_key => $nc2_sort_page) {

            // ページ設定の保存用変数
            $page_ini = "[page_base]\n";
            $page_ini .= "page_name = \"" . $nc2_sort_page->page_name . "\"\n";
            $page_ini .= "permanent_link = \"/" . $nc2_sort_page->permalink . "\"\n";
            $page_ini .= "base_display_flag = 1\n";

            // ページディレクトリの作成
            $new_page_index++;
            Storage::makeDirectory('migration/_' . $this->zeroSuppress($new_page_index));

            // ページ設定ファイルの出力
            Storage::put('migration/_' . $this->zeroSuppress($new_page_index) . '/' . "/page.ini" , $page_ini);

            // echo $nc2_sort_page_key . ':' . $nc2_sort_page->page_name . "\n";

            // ブロック処理
            $this->nc2Block($nc2_sort_page, $new_page_index);
        }

        // ページ、ブロックの関係をCSV 形式で出力。ファイルにしたい場合はコマンドラインでファイルに出力
        // echo $this->frame_tree;
    }

    /**
     *  プラグインの変換
     */
    public function nc2GetPluginName($module_name)
    {
        // uploads の file_path にも対応するため、/ をトル。
        $module_name = trim($module_name, '/');

        // NC2 テンプレート変換配列にあれば、その値。
        // 定義のないものは 'NotFound' にする。
        if (array_key_exists($module_name, $this->plugin_name)) {
            return $this->plugin_name[$module_name];
        }
        return 'NotFound';
    }

    /**
     * NC2：アップロードファイルの移行
     *
     * uploads_ini の形式
     *
     * [uploads]
     * upload[upload_00001] = upload_00001.jpg
     * upload[upload_00002] = upload_00002.png
     * upload[upload_00003] = upload_00003.pdf
     *
     * [upload_00001]
     * file_name = 
     * mimetype = 
     * extension = 
     * plugin_name = 
     * page_id = 0
     *
     * [upload_00002]
     * ・・・
     */
    private function nc2Uploads($uploads_path)
    {
        // NC2 アップロードテーブルを移行する。
        $nc2_uploads = Upload::orderBy('upload_id')->get();

        // uploads,ini ファイル
        $uploads_ini = "[uploads]";
        Storage::put('migration/@uploads/uploads.ini', $uploads_ini);

        // uploads,ini ファイルの詳細（変数に保持、後でappend。[uploads] セクションが切れないため。）
        $uploads_ini_detail = "";

        // アップロード・ファイルのループ
        foreach($nc2_uploads as $nc2_upload) {

            // ファイルのコピー
            $source_file_path = $uploads_path . $nc2_upload->file_path . $nc2_upload->physical_file_name;
            $destination_file_dir = storage_path() . "/app/migration/@uploads";
            $destination_file_name = "upload_" . $this->zeroSuppress($nc2_upload->upload_id, 5);
            $destination_file_path = $destination_file_dir . '/' . $destination_file_name . '.' . $nc2_upload->extension;
            if (!File::isDirectory($destination_file_dir)) {
                File::makeDirectory($destination_file_dir, 0775, true);
            }
            File::copy($source_file_path, $destination_file_path);

            $uploads_ini = "upload[" . $nc2_upload->upload_id . "] = \"" . $destination_file_name . '.' . $nc2_upload->extension . "\"";
            Storage::append('migration/@uploads/uploads.ini', $uploads_ini);

            $uploads_ini_detail .= "\n";
            $uploads_ini_detail .= "[" . $nc2_upload->upload_id . "]\n";
            $uploads_ini_detail .= "client_original_name = \"" . $nc2_upload->file_name . "\"\n";
            $uploads_ini_detail .= "temp_file_name = \"" . $destination_file_name . '.' . $nc2_upload->extension . "\"\n";
            $uploads_ini_detail .= "size = \"" . $nc2_upload->file_size . "\"\n";
            $uploads_ini_detail .= "mimetype = \"" . $nc2_upload->mimetype . "\"\n";
            $uploads_ini_detail .= "extension = \"" . $nc2_upload->extension . "\"\n";
            $uploads_ini_detail .= "plugin_name = \"" . $this->nc2GetPluginName($nc2_upload->file_path) . "\"\n";
            $uploads_ini_detail .= "page_id = \"0\"\n";
        }

        // フレーム設定ファイルの出力
        Storage::append('migration/@uploads/uploads.ini', $uploads_ini_detail);
/*
            $upload = Uploads::create([
                          'client_original_name' => $nc2_upload->file_name,
                          'mimetype'             => $nc2_upload->mimetype,
                          'extension'            => $nc2_upload->extension,
                          'size'                 => $nc2_upload->file_size,
                          'plugin_name'          => $this->nc2GetPluginName($nc2_upload->file_path),
                          'page_id'              => 0,
                          'temporary_flag'       => 0,
                      ]);
*/

    }

    /**
     * NC2：ページ内のブロックをループ
     */
    private function nc2Block($nc2_page, $new_page_index)
    {
        // 指定されたページ内のブロックを取得
        $nc2_blocks = Blocks::where('page_id', $nc2_page->page_id)
                            ->orderBy('thread_num')
                            ->orderBy('row_num')
                            ->get();

        // ブロックをループ
        $frame_index = 0; // フレームの連番
        foreach ($nc2_blocks as $nc2_block) {

            // グループは対象外（後で実装する）
            if ($nc2_block->action_name == 'pages_view_grouping') {

                // ページ、ブロック構成を最後に出力するために保持
                $this->nc2BlockTree($nc2_page, $nc2_block);

                continue;
            }

            $frame_index++;
            $frame_index_str = sprintf("%'.04d", $frame_index);

            // フレーム設定の保存用変数
            $frame_ini = "[frame_base]\n";
            $frame_ini .= "area_id = 2\n";
            $frame_ini .= "frame_title = \"" . $nc2_block->block_name . "\"\n";
            $frame_ini .= "frame_design = \"" . $nc2_block->getFrameDesign() . "\"\n";
            $frame_ini .= "plugin_name = \"" . $nc2_block->getPluginName() . "\"\n";
            $frame_ini .= "nc2_module_name = \"" . $nc2_block->getModuleName() . "\"\n";

            // フレーム設定ファイルの出力
            Storage::put('migration/_' . $this->zeroSuppress($new_page_index) . "/frame_" . $frame_index_str . '.ini', $frame_ini);

            //echo $nc2_block->block_name . "\n";

            // ブロックのモジュールデータをエクスポート
            $this->nc2BlockExport($nc2_page, $nc2_block, $new_page_index, $frame_index_str);

            // ページ、ブロック構成を最後に出力するために保持
            $this->nc2BlockTree($nc2_page, $nc2_block);
        }
    }

    /**
     * NC2：ページ内のブロックに配置されているモジュールのエクスポート。
     * モジュールごとのエクスポート処理に振り分け。
     */
    private function nc2BlockExport($nc2_page, $nc2_block, $new_page_index, $frame_index_str)
    {
        // Connect-CMS のプラグイン名の取得
        $plugin_name = $nc2_block->getPluginName();

        // モジュールごとに振り分け
        if ($plugin_name == 'contents') {
            $this->nc2ExportContents($nc2_page, $nc2_block, $new_page_index, $frame_index_str);
        }
    }

    /**
     * NC2：固定記事（お知らせ）のエクスポート
     */
    private function nc2ExportContents($nc2_page, $nc2_block, $new_page_index, $frame_index_str)
    {
        // お知らせモジュールのデータの取得
        // 続きを読むはとりあえず、1つに統合。固定記事の方、対応すること。
        $announcement = Announcement::where('block_id', $nc2_block->block_id)->first();

        // 記事
        $content = trim($announcement->content);
        $content .= trim($announcement->more_content);

        // WYSIWYG 記事のエクスポート
        $this->nc2Wysiwyg($nc2_block, $new_page_index, $frame_index_str, $content);

        //echo "nc2ExportContents";
    }

    /**
     * NC2：WYSIWYG の記事の保持
     */
    private function nc2Wysiwyg($nc2_block, $new_page_index, $frame_index_str, $content)
    {
        // 画像を探す
        $img_srcs = $this->get_content_image($content);
        var_dump($img_srcs);

        if (!empty($img_srcs)) {

            // フレーム設定ファイルの追記
            $images_ini = "[upload_images]\n";

            foreach($img_srcs as $img_src) {
                // common_download_main があれば、NC2 の画像として移行する。
                if (stripos($img_src, 'common_download_main') !== false) {
                    // &amp; があれば、& に変換
                    $img_src_tmp = str_replace('&amp;', '&', $img_src);
                    // &で分割
                    $src_params = explode('&', $img_src_tmp);
                    foreach($src_params as $src_param) {
                        $param_split = explode('=', $src_param);
                        if ($param_split[0] == 'upload_id') {

                            // フレーム設定ファイルの追記
                            // 移行したアップロードファイルをini ファイルから探す
                            if ($this->uploads_ini && array_key_exists('uploads', $this->uploads_ini) && array_key_exists('upload', $this->uploads_ini['uploads']) && array_key_exists($param_split[1], $this->uploads_ini['uploads']['upload'])) {
                                $images_ini .= $param_split[1] . " = \"" . $this->uploads_ini['uploads']['upload'][$param_split[1]] . "\"\n";

                                // 画像のパスの修正
                                $content = str_replace($img_src, '../@uploads/' . $this->uploads_ini[$param_split[1]]['temp_file_name'], $content);
                            }
                        }
                    }
                }
                else {
                    // 移行しなかった画像のimg タグとしてログに記録
                    $this->putLog("no migrate img = " . $img_src, $nc2_block);
                }
            }
            Storage::append('migration/_' . $this->zeroSuppress($new_page_index) . "/frame_" . $frame_index_str . '.ini', $images_ini);
        }

        // HTML content の保存
        $content_file_name = "frame_" . $frame_index_str . '.html';
        Storage::put('migration/_' . $this->zeroSuppress($new_page_index) . "/" . $content_file_name, $content);

        // フレーム設定ファイルの追記
        $contents_ini = "[contents]\n";
        $contents_ini .= "contents_file = \"" . $content_file_name . "\"\n";
        Storage::append('migration/_' . $this->zeroSuppress($new_page_index) . "/frame_" . $frame_index_str . '.ini', $contents_ini);
    }

    /**
     * NC2：ページ内のブロックをCSV用に溜める
     */
    private function nc2BlockTree($nc2_page, $nc2_block)
    {
        // ページ、ブロック構成を最後に出力するために保持
        $this->frame_tree .= $nc2_page->page_id . ',' . $nc2_page->page_name . ',' . $nc2_page->permalink . ',' . $nc2_block->action_name . ',' . $nc2_block->block_id . ',' . $nc2_block->block_name . "\n";
    }
}
