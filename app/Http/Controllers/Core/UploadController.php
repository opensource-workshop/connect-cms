<?php

namespace App\Http\Controllers\Core;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;

use DB;

use App\Http\Controllers\Core\ConnectController;

use App\Models\Common\Categories;
use App\Models\Common\Page;
use App\Models\Common\Uploads;
use App\Models\Core\Configs;

use App\Traits\ConnectCommonTrait;

/**
 * アップロードファイルの送出処理
 *
 * ルーティング処理から呼び出されるもの
 *
 * @author 永原　篤 <nagahara@opensource-workshop.jp>
 * @copyright OpenSource-WorkShop Co.,Ltd. All Rights Reserved
 * @category コア
 * @package Contoroller
 */
class UploadController extends ConnectController
{

    use ConnectCommonTrait;

//    var $directory_base = "uploads/";
//    var $directory_file_limit = 1000;

    /**
     *  ファイル送出
     *
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
            $page_roles = $this->getPageRoles(array($page->id));

            // 認証されていなくてパスワードを要求する場合、パスワード要求画面を表示
            if ($page->isRequestPassword($request, $this->page_tree)) {
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

        // ファイルを返す(PDFの場合はinline)
        //$content = '';
        $content_disposition = '';
        if (isset($uploads['extension']) && strtolower($uploads['extension']) == 'pdf') {
            return response()
                     ->file(
                         storage_path('app/') . $this->getDirectory($id) . '/' . $id . '.' . $uploads->extension,
                         ['Content-Disposition' =>
                                  'inline; filename="'. $uploads['client_original_name'] .'"' .
                                  "; filename*=UTF-8''" . rawurlencode($uploads['client_original_name'])
                             ]
                     );
        } else {
            return response()
                     ->download(
                         storage_path('app/') . $this->getDirectory($id) . '/' . $id . '.' . $uploads->extension,
                         $uploads['client_original_name'],
                         ['Content-Disposition' =>
                                      'inline; filename="'. $uploads['client_original_name'] .'"' .
                                      "; filename*=UTF-8''" . rawurlencode($uploads['client_original_name'])
                                 ]
                     );
        }
    }

    /**
     *  ファイルチェックメソッドの呼び出し
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
     *  CSS送出
     *
     */
    public function getCss(Request $request, $page_id = null)
    {
        // config のgeneral カテゴリーを読み込んでおく。
        // id のファイルを読んでhttp request に返す。
        $config_generals = array();
        $config_generals_rs = Configs::where('category', 'general')->get();
        foreach ($config_generals_rs as $config_general) {
            $config_generals[$config_general['name']]['value'] = $config_general['value'];
            $config_generals[$config_general['name']]['category'] = $config_general['category'];
        }
        // 自分のページと親ページを遡って取得し、ページの背景色を探す。
        // 最下位に設定されているものが採用される。

        // 背景色
        $background_color = null;

        // ヘッダーの背景色
        $base_header_color = null;

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
            $base_background_color = Configs::where('name', '=', 'base_background_color')->first();
            $background_color = $base_background_color->value;
        }

        // ヘッダーの背景色
        if (empty($header_color)) {
            $base_header_color = Configs::where('name', '=', 'base_header_color')->first();
            $header_color = $base_header_color->value;
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
        if ($config_generals['base_touch_callout']['value'] == '1') {
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

    /**
     *  ファイルのMIME Type 取得
     *
     */
    private function getMimetype($file_path)
    {
        $finfo = finfo_open(FILEINFO_MIME_TYPE);
        $mimetype = finfo_file($finfo, $file_path);
        finfo_close($finfo);
        return $mimetype;
    }

    /**
     *  対象ディレクトリの取得
     *
     */
//    private function getDirectory($file_id)
//    {
//        // ファイルID がなければ0ディレクトリを返す。
//        if (empty($file_id)) {
//            return $this->directory_base . '0';
//        }
//        // 1000で割った余りがディレクトリ名
//        $quotient = floor($file_id / $this->directory_file_limit);
//        $remainder = $file_id % $this->directory_file_limit;
//        $sub_directory = ($remainder == 0) ? $quotient : $quotient + 1;
//        $directory = $this->directory_base . $sub_directory;
//
//        return $directory;
//    }

    /**
     *  対象ディレクトリの取得、なければ作成も。
     *
     */
    private function makeDirectory($file_id)
    {
        $directory = $this->getDirectory($file_id);
        Storage::makeDirectory($directory);
        return $directory;
    }

    /**
     *  ファイル受け取り
     *
     */
    public function postFile(Request $request)
    {
        // ファイルアップロードには、記事の追加、変更の権限が必要
        //if (!$this->isCan('posts.create') || !$this->isCan('posts.update')) {

        // ファイルアップロードには、編集者権限が必要
        if (!$this->isCan('role_reporter')) {
            echo json_encode(array('location' => 'error'));
            return;
        }

        // 画像アップロードの場合（TinyMCE標準プラグイン）
        if ($request->hasFile('file')) {
            if ($request->file('file')->isValid()) {
                // uploads テーブルに情報追加、ファイルのid を取得する
                $upload = Uploads::create([
                   'client_original_name' => $request->file('file')->getClientOriginalName(),
                   'mimetype'             => $request->file('file')->getClientMimeType(),
                   'extension'            => $request->file('file')->getClientOriginalExtension(),
                   'size'                 => $request->file('file')->getClientSize(),
                   'page_id'              => $request->page_id,
                ]);

                $directory = $this->getDirectory($upload->id);
                $upload_path = $request->file('file')->storeAs($directory, $upload->id . '.' . $request->file('file')->getClientOriginalExtension());
                echo json_encode(array('location' => url('/') . '/file/' . $upload->id));
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
            return;
        }


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
                       'size'                 => $request->file($input_name)->getClientSize(),
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

        // アップロードファイルのパスをHTMLにして、さらにjsonに変換してechoでクライアント（WYSIWYGのAjax通信）へ返す。
        $msg_json = json_encode($msg_array);
        echo $msg_json;
    }
}
