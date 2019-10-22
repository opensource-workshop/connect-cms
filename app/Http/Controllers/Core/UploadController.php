<?php

namespace App\Http\Controllers\Core;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;

use DB;

use App\Http\Controllers\Core\ConnectController;

use App\Models\Core\Configs;
use App\Models\Common\Page;
use App\Models\Common\Uploads;

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

    var $directory_base = "uploads/";
    var $directory_file_limit = 1000;

    /**
     *  ファイル送出
     *
     */
    public function getFile(Request $request, $id = null)
    {
        // id がない場合は空を返す。
        if (empty($id)) {
            return response()->download( storage_path(config('connect.no_image_path')));
        }

        // id のファイルを読んでhttp request に返す。
        $uploads = Uploads::where('id', $id)->first();

        // データベースがない場合は空で返す
        if (empty($uploads)) {
            return response()->download( storage_path(config('connect.no_image_path')));
        }

        // ファイルを返す(PDFの場合はinline)
        //$content = '';
        $content_disposition = '';
        if (isset($uploads['extension']) && strtolower($uploads['extension']) == 'pdf') {
            return response()
                     ->file( storage_path('app/') . $this->getDirectory($id) . '/' . $id . '.' . $uploads->extension);
        } else {
            return response()
                     ->download( storage_path('app/') . $this->getDirectory($id) . '/' . $id . '.' . $uploads->extension,
                                 $uploads['client_original_name']
                       );
        }

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
            foreach ( $page_tree as $page ) {

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

        // ヘッダー固定設定
        if (empty($header_color)) {
            $base_header_color = Configs::where('name', '=', 'base_header_color')->first();
            $header_color = $base_header_color->value;
        }

        header('Content-Type: text/css');

        // 背景色
        echo "body {background-color: " . $background_color . "; }\n";

        // ヘッダーの背景色
        echo ".navbar-default { background-color: " . $header_color . "; }\n";

        // 画像の保存機能の無効化(スマホ長押し禁止)
        if ($config_generals['base_touch_callout']['value'] == '1') {
            echo <<<EOD
img {
    -webkit-touch-callout: none;
}

EOD;
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
    private function getDirectory($file_id)
    {
        // ファイルID がなければ0ディレクトリを返す。
        if (empty($file_id)) {
            return $this->directory_base . '0';
        }
        // 1000で割った余りがディレクトリ名
        $quotient = floor($file_id / $this->directory_file_limit);
        $remainder = $file_id % $this->directory_file_limit;
        $sub_directory = ($remainder == 0) ? $quotient : $quotient + 1;
        $directory = $this->directory_base . $sub_directory;

        return $directory;
    }

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
        if ( !$this->isCan('posts.create') || !$this->isCan('posts.update') ) {
            echo json_encode(array('location' => 'error'));
            return;
        }

        // 画像アップロードの場合（TinyMCE標準プラグイン）
        if ($request->hasFile('file')) {
            if ($request->file('file')->isValid()) {

                // uploads テーブルに情報追加、ファイルのid を取得する
                $id = DB::table('uploads')->insertGetId([
                   'client_original_name' => $request->file('file')->getClientOriginalName(),
                   'mimetype'             => $request->file('file')->getClientMimeType(),
                   'extension'            => $request->file('file')->getClientOriginalExtension(),
                   'size'                 => $request->file('file')->getClientSize(),
                ]);

                $directory = $this->getDirectory($id);
                $upload_path = $request->file('file')->storeAs($directory, $id . '.' . $request->file('file')->getClientOriginalExtension());
                echo json_encode(array('location' => url('/') . '/file/' . $id));
            }
            return;
        }


        // アップロードしたパスの配列
        //$upload_paths = array();

        // クライアント（WYSIWYGのAjax通信）へ返すための配列（返す直前にjsonへ変換）
        $msg_array = array();

        // アップロード画面に合わせて、5回のループ
        for ($i = 1; $i <= 5; $i++) {
            $input_name = 'file' . $i;

            // Laravel のアップロード流儀に合わせて、hasFile() とisValid()でチェック
            if ($request->hasFile($input_name)) {
                if ($request->file($input_name)->isValid()) {

                    // uploads テーブルに情報追加、ファイルのid を取得する
                    $id = DB::table('uploads')->insertGetId([
                       'client_original_name' => $request->file($input_name)->getClientOriginalName(),
                       'mimetype'             => $request->file($input_name)->getClientMimeType(),
                       'extension'            => $request->file($input_name)->getClientOriginalExtension(),
                       'size'                 => $request->file($input_name)->getClientSize(),
                    ]);

                    $directory = $this->getDirectory($id);
                    //$upload_paths[$id] = $request->file($input_name)->storeAs($directory, $id . '.' . $request->file($input_name)->getClientOriginalExtension());
                    $upload_path = $request->file($input_name)->storeAs($directory, $id . '.' . $request->file($input_name)->getClientOriginalExtension());

                    // PDFの場合は、別ウィンドウで表示
                    $target = '';
                    if (strtolower($request->file($input_name)->getClientOriginalExtension()) == 'pdf') {
                        $target = ' target="_blank"';
                    }
                    $msg_array['link_texts'][] = '<p><a href="' . url('/') . '/file/' . $id . '" ' . $target . '>' . $request->file($input_name)->getClientOriginalName() . '</a></p>';
                }
            }
        }

        // アップロードファイルのパスをHTMLにして、さらにjsonに変換してechoでクライアント（WYSIWYGのAjax通信）へ返す。
        $msg_json = json_encode($msg_array);
        echo $msg_json;
    }

}
