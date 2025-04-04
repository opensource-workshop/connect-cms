<?php

namespace App\Http\Controllers\Core;

use App\Enums\LinkOfPdfThumbnail;
use App\Enums\ResizedImageSize;
use App\Enums\WidthOfPdfThumbnail;
use App\Enums\UseType;
use App\Http\Controllers\Core\ConnectController;
use App\Models\Common\Categories;
use App\Models\Common\Page;
use App\Models\Common\Uploads;
use App\Models\Core\Configs;
use App\Traits\ConnectCommonTrait;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Storage;
use Intervention\Image\Facades\Image;

/**
 * アップロードファイルの送出処理
 *
 * ルーティング処理から呼び出されるもの
 *
 * @author 永原　篤 <nagahara@opensource-workshop.jp>
 * @author 牟田口 満 <mutaguchi@opensource-workshop.jp>
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
        $no_cache_headers = [
            'Cache-Control' => 'no-store',
            'Expires' => 'Thu, 01 Dec 1994 16:00:00 GMT'
        ];
        $headers = [
            'Cache-Control' => config('connect.CACHE_CONTROL'),
        ];

        // id がない場合は空を返す。（例：DBプラグイン－画像型で必須指定なしでの運用等）
        if (empty($id)) {
            $configs = Configs::getSharedConfigs();
            $no_image = Configs::getConfigsValue($configs, 'no_image', null);
            if ($no_image) {
                // カスタムno_image
                $no_image_path = public_path("/uploads/no_image/{$no_image}");
            } else {
                // 標準no_image
                $no_image_path = storage_path(config('connect.no_image_path'));
            }
            return response()->download($no_image_path, null, $headers)->setEtag(md5_file($no_image_path));
        }

        // id のファイルを読んでhttp request に返す。
        $uploads = Uploads::where('id', $id)->first();

        // レコードがない場合は404
        if (empty($uploads)) {
            abort(404);
        }

        // ファイルの実体がない場合は404
        if (!Storage::exists($this->getDirectory($id) . '/' . $id . '.' . $uploads->extension)) {
            abort(404);
        }

        // 一時保存ファイルの場合は所有者を確認して、所有者ならOK。所有者以外なら404
        // 一時保存ファイルは、登録時の確認画面を表示している際を想定している。
        if ($uploads->temporary_flag == 1) {
            $user_id = Auth::id();
            if ($user_id === null) {
                abort(404);
            }
            if ($uploads->created_id != $user_id) {
                abort(404);
            }

            // キャッシュしない
            $headers = $no_cache_headers;
        }

        // ファイルにページ情報がある場合
        if ($uploads->page_id) {
            $page = Page::find($uploads->page_id);
            // アップロードしたページがない場合、今まで500エラーで見えなかった。そのためページがない場合、非表示とする
            if (is_null($page)) {
                return;
            }

            // $page_roles = PageRole::getPageRoles(array($page->id));

            // 自分のページから親を遡って取得
            $page_tree = Page::reversed()->ancestorsAndSelf($page->id);

            // 認証されていなくてパスワードを要求する場合、パスワード要求画面を表示
            if ($page->isRequestPassword($request, $page_tree)) {
                return response()->download(storage_path(config('connect.forbidden_image_path')), null, $no_cache_headers);
            }

            // ファイルに閲覧権限がない場合
            if (!$page->isVisibleAncestorsAndSelf($page_tree)) {
                return response()->download(storage_path(config('connect.forbidden_image_path')), null, $no_cache_headers);
            }

            // 閲覧パスワード設定あるか
            if ($page->isRequestPasswordSetting($page_tree)) {
                // キャッシュしない
                $headers = $no_cache_headers;
            }
            // 表示制限設定あるか
            if ($page->isViewLimitSetting()) {
                // キャッシュしない
                $headers = $no_cache_headers;
            }
        }

        // ファイルに固有のチェック関数が設定されている場合は、チェック関数を呼ぶ
        if (!empty($uploads->check_method)) {
            list($return_boolean, $return_message) = $this->callCheckMethod($request, $uploads);
            if (!$return_boolean) {
                // \Log::debug($uploads);
                // \Log::debug($return_message);
                return response()->download(storage_path(config('connect.forbidden_image_path')), null, $no_cache_headers);
            }

            // キャッシュしない
            $headers = $no_cache_headers;
        }

        // カウントアップ
        $uploads->increment('download_count', 1);

        $fullpath = storage_path('app/') . $this->getDirectory($id) . '/' . $id . '.' . $uploads->extension;
        $content_disposition = "filename*=UTF-8''" . rawurlencode($uploads['client_original_name']);

        // インライン表示する拡張子
        $inline_extensions = [
            'pdf',
            'png',
            'jpg',
            'jpe',
            'jpeg',
            'gif',
            'html',
        ];

        // サムネイル指定の場合は、キャッシュを使ってファイルを返す。
        if ($request->has('size')) {
            $size = config('connect.THUMBNAIL_SIZE')['SMALL']; // SMALL を初期値で設定
            if ($request->size == 'medium') {
                $size = config('connect.THUMBNAIL_SIZE')['MEDIUM'];
            } elseif ($request->size == 'large') {
                $size = config('connect.THUMBNAIL_SIZE')['LARGE'];
            }

            $img = Image::cache(function ($image) use ($fullpath, $size) {
                return $image->make($fullpath)->resize(
                    $size,
                    $size,
                    function ($constraint) {
                        $constraint->aspectRatio();
                        $constraint->upsize();
                    }
                )->orientate();
            }, config('connect.CACHE_MINUTS'), true); // 第3引数のtrue は戻り値にImage オブジェクトを返す意味。（false の場合は画像データ）

            $headers['Content-Disposition'] = 'inline; ' . $content_disposition;
            return $this->setCacheControlPrivate($img->response()->withHeaders($headers)->setEtag(md5($img->response()->getContent())));
        }

        if (in_array(strtolower($uploads->extension), $inline_extensions) && $request->response != 'download') {
            if (strtolower($uploads->extension) == 'pdf') {
                // pdfはキャッシュしない
                $no_cache_headers['Content-Disposition'] = 'inline; ' . $content_disposition;
                return response()->file($fullpath, $no_cache_headers);
            } else {
                $headers['Content-Disposition'] = 'inline; ' . $content_disposition;
                return $this->setCacheControlPrivate(response()->file($fullpath, $headers)->setEtag(md5_file($fullpath)));
            }
        } else {
            $no_cache_headers['Content-Disposition'] = 'attachment; ' . $content_disposition;
            return response()->download($fullpath, $uploads['client_original_name'], $no_cache_headers);
        }
    }

    /**
     * Cache-Controlにprivateを含むならprivateをセット
     * - Laravel特有対応
     *   - headerでCache-Control にprivateを指定しても、Laravelだと自動的にpublicが付き２重定義になったため、setPrivate()で対応
     * @see \Symfony\Component\HttpFoundation\Response setPrivate()
     */
    private function setCacheControlPrivate($response)
    {
        if (strpos(config('connect.CACHE_CONTROL'), 'private') !== false) {
            return $response->setPrivate();
        }
        return $response;
    }

    /**
     * ユーザファイル送出
     */
    public function getUserFile(Request $request, $dir, $filename)
    {
        // dir、filename がない場合は空を返す。
        if (empty($dir) || empty($filename)) {
            return;
        }

        // ../ or ..\ が含まれる場合は空を返す。
        if (strpos($filename, '../') !== false || strpos($filename, "..\\") !== false) {
            return;
        }

        // ファイルの実体がない場合は空を返す。
        if (!Storage::disk('user')->exists($dir . '/' . $filename)) {
            return;
        }

        // ファイルの制限確認
        $userdir_allow = Configs::where('category', 'userdir_allow')->where('name', $dir)->first();

        // NGチェック
        if (empty($userdir_allow)) {
            // 該当ディレクトリの制限設定がされていないとき。
            return;
        }
        if (empty($userdir_allow->value)) {
            // 該当ディレクトリの制限設定が閲覧させない場合
            return;
        }

        $no_cache_headers = [
            'Cache-Control' => 'no-store',
            'Expires' => 'Thu, 01 Dec 1994 16:00:00 GMT'
        ];
        $headers = [
            'Cache-Control' => config('connect.CACHE_CONTROL'),
        ];

        // OKチェック
        if ($userdir_allow->value == 'allow_login') {
            // 該当ディレクトリの制限設定がログインユーザのみ閲覧許可の場合
            if (Auth::user()) {
                // ログイン中なのでOK

                // キャッシュしない
                $headers = $no_cache_headers;
            } else {
                // ログインしてないのでNG
                return;
            }
        } elseif ($userdir_allow->value = 'allow_all') {
            // 該当ディレクトリの制限設定が誰でも閲覧許可の場合
        } else {
            // OK条件に合致しない場合はNG
            return;
        }

        // ファイルを返す
        $fullpath = storage_path('user/') . $dir . '/' . $filename;

        // httpヘッダー
        $content_disposition = "filename*=UTF-8''" . rawurlencode($filename);

        // インライン表示する拡張子
        $inline_extensions = [
            'pdf',
            'png',
            'jpg',
            'jpe',
            'jpeg',
            'gif',
            'html',
            'js',
            'xml',
            'txt',
        ];

        if (in_array(strtolower(pathinfo($filename, PATHINFO_EXTENSION)), $inline_extensions) && $request->response != 'download') {
            if (strtolower(pathinfo($filename, PATHINFO_EXTENSION)) == 'pdf') {
                // pdfはキャッシュしない
                $no_cache_headers['Content-Disposition'] = 'inline; ' . $content_disposition;
                return response()->file($fullpath, $no_cache_headers);
            } else {
                $headers['Content-Disposition'] = 'inline; ' . $content_disposition;
                return $this->setCacheControlPrivate(response()->file($fullpath, $headers)->setEtag(md5_file($fullpath)));
            }
        } else {
            $no_cache_headers['Content-Disposition'] = 'attachment; ' . $content_disposition;
            return response()->download($fullpath, $filename, $no_cache_headers);
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
     * (CSS No1) サイト全体のCSS送出
     */
    public function getSiteCss(Request $request): void
    {
        $configs = Configs::getSharedConfigs();

        // 背景色
        $background_color = Configs::getConfigsValue($configs, 'base_background_color', null);

        // ヘッダーの背景色
        $header_color = Configs::getConfigsValue($configs, 'base_header_color', null);

        header('Content-Type: text/css');
        header("Content-Disposition: inline; filename=site.css");
        header('Cache-Control: ' . config('connect.CACHE_CONTROL'));

        // 背景色
        if ($background_color) {
            echo "body {background-color: " . $background_color . "; }\n";
        }

        // ヘッダーの背景色
        if ($header_color) {
            echo ".bg-dark  { background-color: " . $header_color . " !important; }\n";
        }

        // 画像の保存機能の無効化(スマホ長押し禁止)
        if (Configs::getConfigsValue($configs, 'base_touch_callout') == '1') {
            echo <<<EOD
img {
    -webkit-touch-callout: none;
}

EOD;
        }

        // カテゴリーCSS
        $categories = Categories::orderBy('target', 'asc')->orderBy('display_sequence', 'asc')->get();
        $categories = $categories->unique("classname");
        foreach ($categories as $category) {
            echo ".cc_category_" . $category->classname . " {\n";
            echo "    background-color: " . $category->background_color . ";\n";
            echo "    color: "            . $category->color . ";\n";
            echo "}\n";
        }
        exit;
    }

    /**
     * サイト全体のCSSタイムスタンプ(最大更新日時のTIMESTAMP)取得
     * （サイト全体のCSSのクエリストリングに使用。CSSをキャッシュしても、クエリストリングを変える事で設定更新時に新しいCSSを反映させる仕組み）
     */
    public static function getSiteCssTimestamp()
    {
        $configs = Configs::getSharedConfigs();

        $max_updated_at = null;

        // 背景色
        $max_updated_at = self::getConfigsMaxUpdatedAt($configs, 'base_background_color', $max_updated_at);

        // ヘッダーの背景色
        $max_updated_at = self::getConfigsMaxUpdatedAt($configs, 'base_header_color', $max_updated_at);

        // 画像の保存機能の無効化(スマホ長押し禁止)
        $max_updated_at = self::getConfigsMaxUpdatedAt($configs, 'base_touch_callout', $max_updated_at);

        // カテゴリーCSS
        $max_updated_at_category = Categories::max('updated_at');
        if ($max_updated_at_category) {
            $max_updated_at_category = new Carbon($max_updated_at_category);

            $max_updated_at = self::getMaxUpdatedAt($max_updated_at_category, $max_updated_at);
        }

        // format('U') でUNIXタイムスタンプへ変換
        $unix_timestamp = $max_updated_at ? $max_updated_at->format('U') : null;
        return $unix_timestamp;
    }

    /**
     * 設定のmax updated_at取得
     */
    private static function getConfigsMaxUpdatedAt($configs, $key, ?Carbon $max_updated_at): ?Carbon
    {
        $config = $configs->firstWhere('name', $key) ?? new Configs();

        // 設定値あり
        if ($config->value) {
            $max_updated_at = self::getMaxUpdatedAt($config->updated_at, $max_updated_at);
        }

        return $max_updated_at;
    }

    /**
     * 日付比較してより大きい updated_at 取得
     */
    private static function getMaxUpdatedAt(Carbon $target_updated_at, ?Carbon $max_updated_at): Carbon
    {
        // max_updated_at がある場合は、日付比較
        if ($max_updated_at) {
            // 大きい？ a > b
            if ($target_updated_at->gt($max_updated_at)) {
                $max_updated_at = $target_updated_at;
            }
        } else {
            // max_updated_at が null の場合は、updated_atを設定
            $max_updated_at = $target_updated_at;
        }

        return $max_updated_at;
    }

    /**
     * (CSS No2) ページ毎のCSS送出
     * getSiteCss() の後に呼び出し、もし同じcss classがあっても「ページ毎のCSS送出」が優先される。
     */
    public function getPageCss(Request $request, $page_id): void
    {
        // 自分のページと親ページを遡って取得し、ページの背景色を探す。
        // 最下位に設定されているものが採用される。

        // 背景色
        $background_color = null;

        // ヘッダーの背景色
        $header_color = null;

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

        // セッションにヘッダーの背景色がある場合（テーマ・チェンジャーで選択時の動き）
        if ($request && $request->session()->get('session_header_black') == true) {
            $header_color = '#000000';
        }

        header('Content-Type: text/css');
        header("Content-Disposition: inline; filename={$page_id}.css");
        header('Cache-Control: ' . config('connect.CACHE_CONTROL'));

        // 背景色
        if ($background_color) {
            echo "body {background-color: " . $background_color . "; }\n";
        }

        // ヘッダーの背景色
        if ($header_color) {
            echo ".bg-dark  { background-color: " . $header_color . " !important; }\n";
        }

        exit;
    }

    /**
     * ページ毎のCSSタイムスタンプ(最大更新日時のTIMESTAMP)取得
     * （ページ毎のCSSのクエリストリングに使用。CSSをキャッシュしても、クエリストリングを変える事で設定更新時に新しいCSSを反映させる仕組み）
     */
    public static function getPageCssTimestamp($page_id)
    {
        $max_updated_at = null;

        $request = app('request');

        // セッションにヘッダーの背景色がある場合（テーマ・チェンジャーで選択時の動き）は、最新の日時を返して強制表示
        if ($request && $request->session()->get('session_header_black') == true) {
            $max_updated_at = new Carbon();

            // format('U') でUNIXタイムスタンプへ変換
            $unix_timestamp = $max_updated_at->format('U');
            return $unix_timestamp;
        }

        // 自分のページと親ページを遡って取得し、ページの背景色を探す。
        // 最下位に設定されているものが採用される。

        // 背景色
        $background_color_updated_at = null;

        // ヘッダーの背景色
        $header_color_updated_at = null;

        if (!empty($page_id)) {
            $page_tree = Page::reversed()->ancestorsAndSelf($page_id);
            foreach ($page_tree as $page) {
                // 背景色
                if (empty($background_color) && $page->background_color) {
                    // $background_color = $page->background_color;
                    $background_color_updated_at = $page->updated_at;

                }
                // ヘッダーの背景色
                if (empty($header_color) && $page->header_color) {
                    // $header_color = $page->header_color;
                    $header_color_updated_at = $page->updated_at;
                }
            }
        }

        // 背景色
        if ($background_color_updated_at) {
            // echo "body {background-color: " . $background_color . "; }\n";
            $max_updated_at = $background_color_updated_at;
        }

        // ヘッダーの背景色
        if ($header_color_updated_at) {
            // echo ".bg-dark  { background-color: " . $header_color . " !important; }\n";
            $max_updated_at = self::getMaxUpdatedAt($header_color_updated_at, $max_updated_at);
        }

        // format('U') でUNIXタイムスタンプへ変換
        $unix_timestamp = $max_updated_at ? $max_updated_at->format('U') : null;
        return $unix_timestamp;
    }

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
     * ファイル受け取り処理の振り分け
     */
    public function postInvoke(Request $request, $method = null)
    {
        // ファイルアップロードには、記事の追加、変更の権限が必要
        //if (!$this->isCan('posts.create') || !$this->isCan('posts.update')) {

        // ファイルアップロードには、編集者 or モデレータ権限が必要
        if ($this->isCan('role_reporter') || $this->isCan('role_article')) {
            // 処理を続ける
        } else {
            // change: LaravelはArrayを返すだけで JSON形式になる
            // echo json_encode(array('location' => 'error'));
            // return;
            return array('location' => 'error');
        }

        // 対象の処理の呼び出し
        if ($method == null) {
            // method が空の場合は、初期値としてpostFile を呼ぶ
            return $this->postFile($request);
        } elseif ($method == 'face') {
            return $this->callFaceApi($request);
        }
    }

    /**
     * モザイクAPI の呼び出し
     */
    public function callFaceApi($request)
    {
        // ファイル受け取り(リクエスト内)
        if (!$request->hasFile('photo') || !$request->file('photo')->isValid()) {
            return array('location' => 'error');
        }
        $image_file = $request->file('photo');

        // GDのリサイズでメモリを多く使うため、memory_limitセット
        $configs = Configs::getSharedConfigs();
        $memory_limit_for_image_resize = Configs::getConfigsValue($configs, 'memory_limit_for_image_resize', '256M');
        ini_set('memory_limit', $memory_limit_for_image_resize);

        // ファイルのリサイズ(メモリ内)
        $image = Image::make($image_file);

        // リサイズ
        $resize_width = null;
        $resize_height = null;
        if ($image->width() > $image->height()) {
            $resize_width = $request->image_size;
        } else {
            $resize_height = $request->image_size;
        }

        $image = $image->resize($resize_width, $resize_height, function ($constraint) {
            // 横幅を指定する。高さは自動調整
            $constraint->aspectRatio();

            // 小さい画像が大きくなってぼやけるのを防止
            $constraint->upsize();
        });

        // 画像の回転対応: orientate()
        $image = $image->orientate();

        // cURLセッションを初期化する
        $ch = curl_init();

        // 送信データを指定
        $data = [
            'api_key' => config('connect.FACE_AI_API_KEY'),
            'mosaic_fineness' => $request->mosaic_fineness,
            //'photo' => base64_encode($request->file('photo')->get()),
            'photo' => base64_encode($image->stream()),
            'extension' => $request->file('photo')->getClientOriginalExtension(),
        ];
        //\Log::debug($data);

        // API URL取得
        $api_url = config('connect.FACE_AI_API_URL');

        // URLとオプションを指定する
        curl_setopt($ch, CURLOPT_URL, $api_url);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($data));
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

        // URLの情報を取得する
        $res = curl_exec($ch);
        //\Log::debug($res);

        // セッションを終了する
        curl_close($ch);

        // ファイルデータをdecode して復元、保存
        $res_base64 = json_decode($res, true);
        //\Log::debug($res_base64);

        // エラーチェック
        if (array_key_exists('errors', $res_base64) && array_key_exists('message', $res_base64['errors']) && !empty($res_base64['errors']['message'])) {
            $msg_array['link_text'] = '<p>エラーが発生しています：' . (array_key_exists('message', $res_base64['errors']) ? $res_base64['errors']['message'] : 'メッセージなし' ) . '</p>';
            return $msg_array;
        }

        // uploads テーブルに情報追加、ファイルのid を取得する
        $photo_upload = Uploads::create([
            'client_original_name' => $request->file('photo')->getClientOriginalName(),
            'mimetype'             => $request->file('photo')->getClientMimeType(),
            'extension'            => $request->file('photo')->getClientOriginalExtension(),
            'size'                 => $request->file('photo')->getSize(),
            'page_id'              => $request->page_id,
            'plugin_name'          => $request->plugin_name,
        ]);

        // ファイル保存
        $directory = $this->getDirectory($photo_upload->id);
        File::put(storage_path('app/') . $directory . '/' . $photo_upload->id . '.' . $request->file('photo')->getClientOriginalExtension(), base64_decode($res_base64['mosaic_photo']));

        // URLのフルパスを込めても、wysiwyg のJSでドメイン取り除かれるため、含めない => ディレクトリインストールの場合はディレクトリが必要なので、url 追加
        $msg_array = [];
        $msg_array['link_text'] = '<p><img src="' . url('/') . '/file/' . $photo_upload->id . '" class="img-fluid" alt="' . $request->alt . '"></p>';

        return $msg_array;
    }

    /**
     * ファイル受け取り
     */
    public function postFile($request)
    {
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
                    'plugin_name'          => $request->plugin_name,
                ]);

                $directory = $this->getDirectory($upload->id);
                $upload_path = $request->file('file')->storeAs($directory, $upload->id . '.' . $request->file('file')->getClientOriginalExtension());
                // change: LaravelはArrayを返すだけで JSON形式になる
                // echo json_encode(array('location' => url('/') . '/file/' . $upload->id));
                return array('location' => url('/') . '/file/' . $upload->id);
            }
            // change: LaravelはArrayを返すだけで JSON形式になる
            // return;
            return array('location' => 'error');
        }

        // image pluginの画像アップロードの場合
        if ($request->hasFile('image')) {
            if ($request->file('image')->isValid()) {
                $image_file = $request->file('image');
                $is_resize = false;

                // GDが有効
                if (function_exists('gd_info')) {

                    // リサイズする拡張子
                    $resize_extensions = ['png', 'jpg', 'jpe', 'jpeg', 'gif'];

                    if (in_array(strtolower($image_file->getClientOriginalExtension()), $resize_extensions)) {
                        // 値があって原寸以外はリサイズする
                        if (!empty($request->resize) && $request->resize != ResizedImageSize::asis) {
                            $is_resize = true;
                        }
                    }
                }

                if ($is_resize) {
                    // リサイズ

                    // GDのリサイズでメモリを多く使うため、memory_limitセット
                    $configs = Configs::getSharedConfigs();
                    $memory_limit_for_image_resize = Configs::getConfigsValue($configs, 'memory_limit_for_image_resize', '256M');
                    ini_set('memory_limit', $memory_limit_for_image_resize);

                    // GDが無いとここで GD Library extension not available with this PHP installation. エラーになる
                    // $image = Image::make($image_file)->resize($request->width, $request->height);
                    $image = Image::make($image_file);

                    $resize_width = $request->resize;
                    $resize_height = null;

                    // ※ [注意] リサイズ時メモリ多めに使った。8MB画像＋memory_limit=128Mでエラー。memory_limit=256Mで解消。
                    //           エラーメッセージ：ERROR: Allowed memory size of 134217728 bytes exhausted (tried to allocate 48771073 bytes) {"userId":1,"exception":"[object] (Symfony\\Component\\Debug\\Exception\\FatalErrorException(code: 1): Allowed memory size of 134217728 bytes exhausted (tried to allocate 48771073 bytes) at /path_to_connect-cms/vendor/intervention/image/src/Intervention/Image/Gd/Commands/ResizeCommand.php:58)
                    //           see) https://github.com/Intervention/image/issues/567#issuecomment-224230343
                    // $image = $image->fit($resize_width, $resize_height, function ($constraint) {
                    $image = $image->resize($resize_width, $resize_height, function ($constraint) {
                        // 横幅を指定する。高さは自動調整
                        $constraint->aspectRatio();

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
                        'plugin_name'          => $request->plugin_name,
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
                        'plugin_name'          => $request->plugin_name,
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

            if (Configs::getSharedConfigsValue('use_pdf_thumbnail', UseType::not_use) == UseType::not_use) {
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

            // URLのフルパスを込めても、wysiwyg のJSでドメイン取り除かれるため、含めない => ディレクトリインストールの場合はディレクトリが必要なので、url 追加
            $msg_array = [];
            $msg_array['link_text'] = '<p><a href="' . url('/') . '/file/' . $pdf_upload->id . '"  target="_blank">' . $request->file('pdf')->getClientOriginalName() . '</a><br />';


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
                File::put($thumbnail_path, base64_decode($base64_thumbnail));

                if (Configs::getSharedConfigsValue('link_of_pdf_thumbnails') == LinkOfPdfThumbnail::image) {
                    // サムネイルにリンク
                    $msg_array['link_text'] .= '<a href="' . url('/') . '/file/' . $thumbnail_upload->id . '"  target="_blank">';
                } else {
                    // PDFにリンク
                    $msg_array['link_text'] .= '<a href="' . url('/') . '/file/' . $pdf_upload->id . '"  target="_blank">';
                }

                $msg_array['link_text'] .= '<img src="' . url('/') . '/file/'.$thumbnail_upload->id.'" width="'.$request->width_of_pdf_thumbnails.'" class="img-fluid img-thumbnail" alt="'.$thumbnail_name.'" />';
                $msg_array['link_text'] .= '</a> ';

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
                        'plugin_name'          => $request->plugin_name,
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
