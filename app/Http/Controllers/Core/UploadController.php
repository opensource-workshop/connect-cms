<?php

namespace App\Http\Controllers\Core;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;

use App\Http\Controllers\Core\ConnectController;

use App\Enums\WidthOfPdfThumbnail;

use App\Models\Common\Categories;
use App\Models\Common\Page;
use App\Models\Common\PageRole;
use App\Models\Common\Uploads;
use App\Models\Core\Configs;

use App\Traits\ConnectCommonTrait;

use Intervention\Image\Facades\Image;

/**
 * アップロードファイルの送出処理
 *
 * ルーティング処理から呼び出されるもの
 *
 * @author 永原　篤 <nagahara@opensource-workshop.jp>
 * @copyright OpenSource-WorkShop Co.,Ltd. All Rights Reserved
 * @category コア
 * @package Controller
 */
class UploadController extends ConnectController
{
    use ConnectCommonTrait;

    // var $directory_base = "uploads/";
    // var $directory_file_limit = 1000;

    /**
     * コンストラクタ
     */
    public function __construct()
    {
        $this->middleware('connect.page');
    }

    /**
     * ファイル送出
     */
    public function getFile(Request $request, $id = null)
    {
        // id がない場合は空を返す。
        if (empty($id)) {
            return response()->download(storage_path(config('connect.no_image_path')));
        }

        // id のファイルを読んでhttp request に返す。
        $uploads = Uploads::where('id', $id)->first();

        // データベースがない場合は空で返す
        if (empty($uploads)) {
            return response()->download(storage_path(config('connect.no_image_path')));
        }

        // ファイルの実体がない場合は空を返す。
        if (!Storage::exists($this->getDirectory($id) . '/' . $id . '.' . $uploads->extension)) {
            return;
        }

        // 一時保存ファイルの場合は所有者を確認して、所有者ならOK
        // 一時保存ファイルは、登録時の確認画面を表示している際を想定している。
        if ($uploads->temporary_flag == 1) {
            $user_id = Auth::id();
            if ($uploads->created_id != $user_id) {
                return response()->download(storage_path(config('connect.no_image_path')));
            }
        }

        // ファイルにページ情報がある場合
        if ($uploads->page_id) {
            $page = Page::find($uploads->page_id);
            // $page_roles = $this->getPageRoles(array($page->id));
            $page_roles = PageRole::getPageRoles(array($page->id));

            // 自分のページから親を遡って取得
            $page_tree = Page::reversed()->ancestorsAndSelf($page->id);

            // 認証されていなくてパスワードを要求する場合、パスワード要求画面を表示
            // if ($page->isRequestPassword($request, $this->page_tree)) {
            if ($page->isRequestPassword($request, $page_tree)) {
                return response()->download(storage_path(config('connect.forbidden_image_path')));
            }

            // ファイルに閲覧権限がない場合
            if (!$page->isView(Auth::user(), true, true, $page_roles)) {
                return response()->download(storage_path(config('connect.forbidden_image_path')));
            }
        }

        // ファイルに固有のチェック関数が設定されている場合は、チェック関数を呼ぶ
        if (!empty($uploads->check_method)) {
            list($return_boolean, $return_message) = $this->callCheckMethod($request, $uploads);
            if (!$return_boolean) {
                //Log::debug($uploads);
                //Log::debug($return_message);
                return response()->download(storage_path(config('connect.forbidden_image_path')));
            }
        }

        // カウントアップの対象拡張子ならカウントアップ
        $cc_count_extension = config('connect.CC_COUNT_EXTENSION');
        if (isset($uploads['extension']) && is_array($cc_count_extension) && in_array(strtolower($uploads['extension']), $cc_count_extension)) {
            $uploads->increment('download_count', 1);
        }

        // ファイルを返す
        //$content = '';
        $fullpath = storage_path('app/') . $this->getDirectory($id) . '/' . $id . '.' . $uploads->extension;

        // $content_disposition = '';
        $content_disposition = 'inline; filename="'. $uploads['client_original_name'] .'"' .
            "; filename*=UTF-8''" . rawurlencode($uploads['client_original_name']);

        // インライン表示する拡張子
        $inline_extensions = [
            'pdf',
            'png',
            'jpg',
            'jpe',
            'jpeg',
            'gif',
        ];

        // if (isset($uploads['extension']) && strtolower($uploads['extension']) == 'pdf') {
        // if (strtolower($uploads->extension) == 'pdf') {
        if (in_array(strtolower($uploads->extension), $inline_extensions)) {
            return response()
                    ->file(
                        // storage_path('app/') . $this->getDirectory($id) . '/' . $id . '.' . $uploads->extension,
                        // [
                        //     'Content-Disposition' =>
                        //         'inline; filename="'. $uploads['client_original_name'] .'"' .
                        //         "; filename*=UTF-8''" . rawurlencode($uploads['client_original_name'])
                        // ]
                        $fullpath,
                        ['Content-Disposition' => $content_disposition]
                    );
        } else {
            return response()
                    ->download(
                        // storage_path('app/') . $this->getDirectory($id) . '/' . $id . '.' . $uploads->extension,
                        // $uploads['client_original_name'],
                        // [
                        //     'Content-Disposition' =>
                        //         'inline; filename="'. $uploads['client_original_name'] .'"' .
                        //         "; filename*=UTF-8''" . rawurlencode($uploads['client_original_name'])
                        // ]
                        $fullpath,
                        $uploads['client_original_name'],
                        ['Content-Disposition' => $content_disposition]
                    );
        }
    }

    /**
     * ファイルチェックメソッドの呼び出し
     */
    private function callCheckMethod($request, $upload)
    {
        if (empty($upload)) {
            return false;
        }

        // プラグイン・クラスファイルの存在を確認

        // 標準プラグインとして存在するか確認
        $class_name = "App\Plugins\User\\" . ucfirst($upload->plugin_name) . "\\" . ucfirst($upload->plugin_name) . "Plugin";
        if (!class_exists($class_name)) {
            // 標準プラグインになければ、オプションプラグインとして存在するか確認
            $class_name = "App\PluginsOption\User\\" . ucfirst($upload->plugin_name) . "\\" . ucfirst($upload->plugin_name) . "Plugin";
            if (!class_exists($class_name)) {
                return false;
            }
        }

        // プラグイン・クラスファイルのメソッドの存在を確認
        if (empty($upload->check_method)) {
            // チェックの必要なし
            return true;
        } else {
            if (method_exists($class_name, $upload->check_method)) {
                // チェックする。
            } else {
                // チェック用のメソッドが設定されているのにメソッドがない。
                // 権限なしとして処理する。
                return false;
            }
        }

        // チェック・メソッドの呼び出し（可変関数として変数に関数名を設定してから呼び出し）
        $check_method = $upload->check_method;
        return $class_name::$check_method($request, $upload);
    }

    /**
     * CSS送出
     */
    public function getCss(Request $request, $page_id = null)
    {
        // \Log::debug('[' . __METHOD__ . '] ' . __FILE__ . ' (line ' . __LINE__ . ')');

        // config のgeneral カテゴリーを読み込んでおく。
        // id のファイルを読んでhttp request に返す。
        // $config_generals = array();
        // $config_generals_rs = Configs::where('category', 'general')->get();
        // foreach ($config_generals_rs as $config_general) {
        //     $config_generals[$config_general['name']]['value'] = $config_general['value'];
        //     $config_generals[$config_general['name']]['category'] = $config_general['category'];
        // }
        $configs = Configs::getSharedConfigs();

        // 自分のページと親ページを遡って取得し、ページの背景色を探す。
        // 最下位に設定されているものが採用される。

        // 背景色
        $background_color = null;

        // ヘッダーの背景色
        $header_color = null;

        if (!empty($page_id)) {
            $page_tree = Page::reversed()->ancestorsAndSelf($page_id);
            foreach ($page_tree as $page) {
                // 背景色
                if (empty($background_color) && $page->background_color) {
                    $background_color = $page->background_color;
                }
                // ヘッダーの背景色
                if (empty($header_color) && $page->header_color) {
                    $header_color = $page->header_color;
                }
            }
        }

        // ページ設定で背景色が指定されていなかった場合は、基本設定を使用する。

        // 背景色
        if (empty($background_color)) {
            // $base_background_color = Configs::where('name', '=', 'base_background_color')->first();
            // $background_color = $base_background_color->value;
            $background_color = Configs::getConfigsValue($configs, 'base_background_color', null);
        }

        // ヘッダーの背景色
        if (empty($header_color)) {
            // $base_header_color = Configs::where('name', '=', 'base_header_color')->first();
            // $header_color = $base_header_color->value;
            $header_color = Configs::getConfigsValue($configs, 'base_header_color', null);
        }

        // セッションにヘッダーの背景色がある場合（テーマ・チェンジャーで選択時の動き）
        if ($request && $request->session()->get('session_header_black') == true) {
            $header_color = '#000000';
        }

        header('Content-Type: text/css');

        // 背景色
        if ($background_color) {
            echo "body {background-color: " . $background_color . "; }\n";
        }

        // ヘッダーの背景色
        if ($header_color) {
            echo ".bg-dark  { background-color: " . $header_color . " !important; }\n";
        }

        // 画像の保存機能の無効化(スマホ長押し禁止)
        // if ($config_generals['base_touch_callout']['value'] == '1') {
        if (Configs::getConfigsValue($configs, 'base_touch_callout') == '1') {
            echo <<<EOD
img {
    -webkit-touch-callout: none;
}

EOD;
        }

        // カテゴリーCSS
        $categories = Categories::orderBy('target', 'asc')->orderBy('display_sequence', 'asc')->get();
        foreach ($categories as $category) {
            echo ".cc_category_" . $category->classname . " {\n";
            echo "    background-color: " . $category->background_color . ";\n";
            echo "    color: "            . $category->color . ";\n";
            echo "}\n";
        }
        exit;
    }

    // delete: どこからも呼ばれてないprivateメソッドのため、コメントアウト
    // /**
    //  * ファイルのMIME Type 取得
    //  */
    // private function getMimetype($file_path)
    // {
    //     $finfo = finfo_open(FILEINFO_MIME_TYPE);
    //     $mimetype = finfo_file($finfo, $file_path);
    //     finfo_close($finfo);
    //     return $mimetype;
    // }

    /**
     * 対象ディレクトリの取得
     */
    // private function getDirectory($file_id)
    // {
    //     // ファイルID がなければ0ディレクトリを返す。
    //     if (empty($file_id)) {
    //         return $this->directory_base . '0';
    //     }
    //     // 1000で割った余りがディレクトリ名
    //     $quotient = floor($file_id / $this->directory_file_limit);
    //     $remainder = $file_id % $this->directory_file_limit;
    //     $sub_directory = ($remainder == 0) ? $quotient : $quotient + 1;
    //     $directory = $this->directory_base . $sub_directory;

    //     return $directory;
    // }

    /**
     * 対象ディレクトリの取得、なければ作成も。
     */
    private function makeDirectory($file_id)
    {
        $directory = $this->getDirectory($file_id);
        Storage::makeDirectory($directory);
        return $directory;
    }

    /**
     * ファイル受け取り
     */
    public function postFile(Request $request)
    {
        // ファイルアップロードには、記事の追加、変更の権限が必要
        //if (!$this->isCan('posts.create') || !$this->isCan('posts.update')) {

        // ファイルアップロードには、編集者権限が必要
        if (!$this->isCan('role_reporter')) {
            // change: LaravelはArrayを返すだけで JSON形式になる
            // echo json_encode(array('location' => 'error'));
            // return;
            return array('location' => 'error');
        }

        // アップロードの場合（TinyMCE標準プラグイン）
        if ($request->hasFile('file')) {
            if ($request->file('file')->isValid()) {
                // uploads テーブルに情報追加、ファイルのid を取得する
                $upload = Uploads::create([
                    'client_original_name' => $request->file('file')->getClientOriginalName(),
                    'mimetype'             => $request->file('file')->getClientMimeType(),
                    'extension'            => $request->file('file')->getClientOriginalExtension(),
                    'size'                 => $request->file('file')->getSize(),
                    'page_id'              => $request->page_id,
                ]);

                $directory = $this->getDirectory($upload->id);
                $upload_path = $request->file('file')->storeAs($directory, $upload->id . '.' . $request->file('file')->getClientOriginalExtension());
                // change: LaravelはArrayを返すだけで JSON形式になる
                // echo json_encode(array('location' => url('/') . '/file/' . $upload->id));
                return array('location' => url('/') . '/file/' . $upload->id);

                /*
                $id = DB::table('uploads')->insertGetId([
                    'client_original_name' => $request->file('file')->getClientOriginalName(),
                    'mimetype'             => $request->file('file')->getClientMimeType(),
                    'extension'            => $request->file('file')->getClientOriginalExtension(),
                    'size'                 => $request->file('file')->getClientSize(),
                    'page_id'              => $request->page_id,
                ]);

                $directory = $this->getDirectory($id);
                $upload_path = $request->file('file')->storeAs($directory, $id . '.' . $request->file('file')->getClientOriginalExtension());
                echo json_encode(array('location' => url('/') . '/file/' . $id));
                */
            }
            // change: LaravelはArrayを返すだけで JSON形式になる
            // return;
            return array('location' => 'error');
        }

        // image pluginの画像アップロードの場合
        if ($request->hasFile('image')) {
            if ($request->file('image')->isValid()) {

                $image_file = $request->file('image');
                $extension = strtolower($image_file->getClientOriginalExtension());
                $is_resize = false;

                // 画像のオリジナル縦横サイズを取得
                list($original_width, $original_height, $type, $attr) = getimagesize($image_file->getPathname());
                // \Log::debug(var_export($image_file->getPathname(), true));
                // \Log::debug(var_export(getimagesize($image_file->getPathname()), true));

                // GDが有効
                if (function_exists('gd_info')) {
                    // 対象画像 jpg|png. gitはアニメーションgitが変換するとアニメーションしなくなる＆主要な画像形式ではないので外しました。
                    // if ($extension == 'jpg' || $extension == 'jpeg' || $extension == 'gif' || $extension == 'png') {
                    if ($extension == 'jpg' || $extension == 'jpeg' || $extension == 'png') {
                        // サイズ指定が原寸（画像の縦横サイズと同じ）なら、リサイズしない
                        if ((int)$request->width === $original_width && (int)$request->height === $original_height) {
                            // リサイズしない
                        } elseif ((int)$request->width > 0 && (int)$request->height > 0) {
                            // 幅、高さが0より大きい = リサイズ
                            $is_resize = true;
                        }
                    }
                }
                // \Log::debug(var_export($request->width, true));
                // \Log::debug(var_export($original_width, true));
                // \Log::debug(var_export($request->height, true));
                // \Log::debug(var_export($original_height, true));

                if ($is_resize) {
                    // リサイズ

                    // GDが無いとここで GD Library extension not available with this PHP installation. エラーになる
                    // $image = Image::make($image_file)->resize($request->width, $request->height);
                    $image = Image::make($image_file);

                    $resize_width = null;
                    $resize_height = null;

                    // [TODO] ※ 傾きがあるとウィジウィグが縦横サイズを逆にセットするため、結果的にリサイズ対象になる。（ウィジウィグ標準の画像プラグインで無理くりリサイズしている事の限界）
                    // 下記の縦横サイズ入替も、ウィジウィグ標準の画像プラグインで無理くりやっているため、入れてる処理。
                    // 今後独自でウィジウィグ画像プラグイン作成時は、縦横サイズ入替処理の見直し必要です。

                    // see) https://www.php.net/manual/ja/function.exif-read-data.php#110894
                    // see) https://qiita.com/yoshu/items/c83c239eb32ed295fca8
                    switch($image->exif('Orientation')) {
                        // iOS系は 3,6,8 入ってくる。
                        case 5:     // 水平反転、反時計回りに270回転
                        case 6:     // 反時計回りに270回転(傾きあり)
                        case 7:     // 水平反転、反時計回りに90度回転
                        case 8:     // 反時計回りに90度回転(傾きあり)

                            // 縦横サイズを入れ替える（傾きあるとウィジウィグが縦横サイズを逆にセットするため）
                            $resize_width = $request->height;
                            $resize_height = $request->width;
                            break;

                        case 1:     // nothing
                        case 2:     // 水平反転
                        case 3:     // 180度回転
                        case 4:     // 垂直反転
                        default:    // それ以外
                            // 通常通り
                            $resize_width = $request->width;
                            $resize_height = $request->height;
                    }

                    // GDのリサイズでメモリを多く使うため、memory_limitセット
                    $configs = Configs::getSharedConfigs();
                    $memory_limit_for_image_resize = Configs::getConfigsValue($configs, 'memory_limit_for_image_resize', '256M');
                    ini_set('memory_limit', $memory_limit_for_image_resize);

                    // 画像の歪み対応: fit().
                    // ※ [注意] リサイズ時メモリ多めに使った。8MB画像＋memory_limit=128Mでエラー。memory_limit=256Mで解消。
                    //           エラーメッセージ：ERROR: Allowed memory size of 134217728 bytes exhausted (tried to allocate 48771073 bytes) {"userId":1,"exception":"[object] (Symfony\\Component\\Debug\\Exception\\FatalErrorException(code: 1): Allowed memory size of 134217728 bytes exhausted (tried to allocate 48771073 bytes) at /path_to_connect-cms/vendor/intervention/image/src/Intervention/Image/Gd/Commands/ResizeCommand.php:58)
                    //           see) https://github.com/Intervention/image/issues/567#issuecomment-224230343
                    $image = $image->fit($resize_width, $resize_height, function($constraint) {
                        // 小さい画像が大きくなってぼやけるのを防止
                        $constraint->upsize();
                    });

                    // 画像の回転対応: orientate()
                    $image = $image->orientate();

                    $upload = Uploads::create([
                        'client_original_name' => $image_file->getClientOriginalName(),
                        'mimetype'             => $image_file->getClientMimeType(),
                        'extension'            => $image_file->getClientOriginalExtension(),
                        'size'                 => $image->filesize(),
                        'page_id'              => $request->page_id,
                    ]);

                    // bugfix: 新規インストール時、画像アップロードでリサイズ時にUploadsフォルダがなく500エラーになるバグ対応
                    // $directory = $this->getDirectory($upload->id);
                    $directory = $this->makeDirectory($upload->id);

                    $image->save(storage_path('app/') . $directory . '/' . $upload->id . '.' . $image_file->getClientOriginalExtension());

                    // bugfix: リサイズ後のfilesizeは、$image->save()後でないと取得できないため、filesizeをupdate.
                    $upload->size = $image->filesize();
                    $upload->save();
                } else {
                    // そのまま画像

                    // uploads テーブルに情報追加、ファイルのid を取得する
                    $upload = Uploads::create([
                        'client_original_name' => $image_file->getClientOriginalName(),
                        'mimetype'             => $image_file->getClientMimeType(),
                        'extension'            => $image_file->getClientOriginalExtension(),
                        'size'                 => $image_file->getSize(),
                        'page_id'              => $request->page_id,
                    ]);

                    $directory = $this->getDirectory($upload->id);
                    $upload_path = $image_file->storeAs($directory, $upload->id . '.' . $image_file->getClientOriginalExtension());
                }

                return array('location' => url('/') . '/file/' . $upload->id);
            }
            return array('location' => 'error');
        }

        // pdf pluginのPDFアップロードの場合. リクエスト中にファイルが存在しているか
        if ($request->hasFile('pdf')) {

            // API URL取得
            $api_url = config('connect.PDF_THUMBNAIL_API_URL');
            if (empty($api_url)) {
                // API URLを設定しないとこの処理は通らないため、通常ここに入らない想定。そのためシステム的なメッセージを表示
                return ['link_text' => 'error: 設定ファイル.envにPDF_THUMBNAIL_API_URLが設定されていません。'];
            }

            $configs = Configs::getSharedConfigs();
            if (!Configs::getConfigsValue($configs, 'use_pdf_thumbnail')) {
                // 通常ここに入らない想定。（入る場合の例：誰かがウィジウィグでPDFアップロードを使用中に、管理者がPDFを使用しないに設定変更して、PDFアップロードが行われた場合等）
                return ['link_text' => 'error: PDFアップロードの使用設定がONになっていません。'];
            }

            // アップロードに失敗したらエラー
            if (! $request->file('pdf')->isValid()) {
                return ['link_text' => 'error: アップロードに失敗しました。'];
            }

            if (strtolower($request->file('pdf')->getClientOriginalExtension()) != 'pdf') {
                return ['link_text' => 'error: PDFをアップロードしてください。'];
            }


            // uploads テーブルに情報追加、ファイルのid を取得する
            $pdf_upload = Uploads::create([
                'client_original_name' => $request->file('pdf')->getClientOriginalName(),
                'mimetype'             => $request->file('pdf')->getClientMimeType(),
                'extension'            => $request->file('pdf')->getClientOriginalExtension(),
                'size'                 => $request->file('pdf')->getSize(),
                'page_id'              => $request->page_id,
                'plugin_name'          => $request->plugin_name,
            ]);

            $directory = $this->getDirectory($pdf_upload->id);
            $pdf_upload_path = $request->file('pdf')->storeAs($directory, $pdf_upload->id . '.' . $request->file('pdf')->getClientOriginalExtension());

            // URLのフルパスを込めても、wysiwyg のJSでドメイン取り除かれるため、含めない
            $msg_array = [];
            $msg_array['link_text'] = '<p><a href="/file/' . $pdf_upload->id . '"  target="_blank">' . $request->file('pdf')->getClientOriginalName() . '</a><br />';


            // cURLセッションを初期化する
            $ch = curl_init();

            // 送信データを指定
            $data = [
                'api_key' => config('connect.PDF_THUMBNAIL_API_KEY'),
                'pdf' => base64_encode($request->file('pdf')->get()),
                'pdf_password' => $request->pdf_password,
                'scale_of_pdf_thumbnails' => WidthOfPdfThumbnail::getScale($request->width_of_pdf_thumbnails),
                'number_of_pdf_thumbnails' => $request->number_of_pdf_thumbnails,
            ];

            // URLとオプションを指定する
            curl_setopt($ch, CURLOPT_URL, $api_url);
            curl_setopt($ch, CURLOPT_POST, true);
            curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($data));
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

            // URLの情報を取得する
            $res = curl_exec($ch);

            $base64_thumbnails = json_decode($res, true);
            // \Log::debug(var_export($base64_thumbnails, true));

            // エラーメッセージが有ったら、メッセージを出力して終了
            if (isset($base64_thumbnails['errors']['message'])) {
                // セッションを終了する
                curl_close($ch);

                $msg_array['link_text'] .= '</a></p>';
                $msg_array['link_text'] .= '<p>サムネイル作成エラー：' . $base64_thumbnails['errors']['message'] . '</p>';
                return $msg_array;
            }

            $thumbnail_no = 1;
            foreach ($base64_thumbnails as $base64_thumbnail) {

                $thumbnail_name = $request->file('pdf')->getClientOriginalName() . 'の' . $thumbnail_no . 'ページ目のサムネイル';

                $thumbnail_upload = Uploads::create([
                    'client_original_name' => $thumbnail_name . '.png',
                    'mimetype'             => 'image/png',
                    'extension'            => 'png',
                    'size'                 => 0,
                    'page_id'              => $request->page_id,
                    'plugin_name'          => $request->plugin_name,
                ]);

                $directory = $this->getDirectory($thumbnail_upload->id);
                $thumbnail_path = storage_path('app/') . $directory . '/' . $thumbnail_upload->id . '.png';
                // File::put($thumbnail_path, file_get_contents($base64_thumbnail));
                File::put($thumbnail_path, base64_decode($base64_thumbnail));
                // 下記はGDが必要なため、使わない。
                // Image::make(file_get_contents($base64_thumbnail))->save(storage_path('app/') . $directory . '/' . $thumbnail_upload->id . '.png');

                $msg_array['link_text'] .= '<a href="/file/' . $pdf_upload->id . '"  target="_blank">';
                // $msg_array['link_text'] .= '<a href="/file/' . $thumbnail_upload->id . '"  target="_blank">';
                $msg_array['link_text'] .= '<img src="/file/'.$thumbnail_upload->id.'" width="'.$request->width_of_pdf_thumbnails.'" class="img-fluid img-thumbnail" alt="'.$thumbnail_name.'" /> ';
                $msg_array['link_text'] .= '</a>';

                // sizeはファイルにしてから取得する
                $thumbnail_upload->size = File::size($thumbnail_path);
                $thumbnail_upload->save();

                $thumbnail_no++;
            }

            // セッションを終了する
            curl_close($ch);

            $msg_array['link_text'] .= '</p>';
            return $msg_array;
        }


        // ここまで来たら、file pluginとみなす
        // (pdf pluginでPDFなしでアップロードした場合、ここを通り return []になる)

        // アップロードしたパスの配列
        //$upload_paths = array();

        // クライアント（WYSIWYGのAjax通信）へ返すための配列（返す直前にjsonへ変換）
        $msg_array = array();

        // アップロードファイルの数。タグ出力する際、ファイルが1つなら<a>のみ、複数あれば<p><a>とするため。
        $file_count = 0;
        for ($i = 1; $i <= 5; $i++) {
            $input_name = 'file' . $i;
            if ($request->hasFile($input_name)) {
                if ($request->file($input_name)->isValid()) {
                    $file_count++;
                }
            }
        }

        // アップロード画面に合わせて、5回のループ
        for ($i = 1; $i <= 5; $i++) {
            $input_name = 'file' . $i;

            // Laravel のアップロード流儀に合わせて、hasFile() とisValid()でチェック
            if ($request->hasFile($input_name)) {
                if ($request->file($input_name)->isValid()) {
                    // uploads テーブルに情報追加、ファイルのid を取得する
                    $upload = Uploads::create([
                        'client_original_name' => $request->file($input_name)->getClientOriginalName(),
                        'mimetype'             => $request->file($input_name)->getClientMimeType(),
                        'extension'            => $request->file($input_name)->getClientOriginalExtension(),
                        'size'                 => $request->file($input_name)->getSize(),
                        'page_id'              => $request->page_id,
                    ]);

                    $directory = $this->getDirectory($upload->id);
                    //$upload_paths[$id] = $request->file($input_name)->storeAs($directory, $id . '.' . $request->file($input_name)->getClientOriginalExtension());
                    $upload_path = $request->file($input_name)->storeAs($directory, $upload->id . '.' . $request->file($input_name)->getClientOriginalExtension());

                    // PDFの場合は、別ウィンドウで表示
                    $target = '';
                    if (strtolower($request->file($input_name)->getClientOriginalExtension()) == 'pdf') {
                        $target = ' target="_blank"';
                    }

                    // ファイルが1つなら<a>のみ、複数あれば<p><a>とする。
                    if ($file_count > 1) {
                        $msg_array['link_texts'][] = '<p><a href="' . url('/') . '/file/' . $upload->id . '" ' . $target . '>' . $request->file($input_name)->getClientOriginalName() . '</a></p>';
                    } else {
                        $msg_array['link_texts'][] = '<a href="' . url('/') . '/file/' . $upload->id . '" ' . $target . '>' . $request->file($input_name)->getClientOriginalName() . '</a>';
                    }
                }
            }
        }

        // change: LaravelはArrayを返すだけで JSON形式になる
        // アップロードファイルのパスをHTMLにして、さらにjsonに変換してechoでクライアント（WYSIWYGのAjax通信）へ返す。
        // $msg_json = json_encode($msg_array);
        // echo $msg_json;
        return $msg_array;
    }
}
