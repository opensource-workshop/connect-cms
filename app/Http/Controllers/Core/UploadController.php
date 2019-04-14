<?php

namespace App\Http\Controllers\Core;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

use DB;

use App\Http\Controllers\Core\ConnectController;
use App\Configs;
use App\Page;
use App\Uploads;

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

        // ファイルを返す
        return response()->download( storage_path('app/uploads/') . $id . '.' . $uploads->extension);
    }

    /**
     *  CSS送出
     *
     */
    public function getCss(Request $request, $page_id = null)
    {

        $base_background_color = Configs::where('name', '=', 'base_background_color')->first();

        header('Content-Type: text/css');
        echo 'body { background-color: ' . $base_background_color->value . '; }';
        exit;
    }
}
