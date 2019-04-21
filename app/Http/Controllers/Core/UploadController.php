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

        // bootstrap の@screen-xs-max 指定が効かないので、数値指定

        // ヘッダー固定設定(スマートフォン)
        if ($config_generals['base_header_fix_xs']['value'] == '1') {
            echo <<<EOD
@media (max-width: 767px) {
    .navbar-static-top {
        position: fixed;
        top: 0;
        width: 100%;
    }
    body {
        padding-top: 72px;
    }
}

EOD;
        }

        // ヘッダー固定設定(タブレット)
        if ($config_generals['base_header_fix_sm']['value'] == '1') {
            echo <<<EOD
@media (min-width:768px) and (max-width:991px) {
    .navbar-static-top {
        position: fixed;
        top: 0;
        width: 100%;
    }
    body {
        padding-top: 72px;
    }
}

EOD;
        }

        // ヘッダー固定設定(PC)
        if ($config_generals['base_header_fix_md']['value'] == '1') {
            echo <<<EOD
@media (min-width:992px) {
    .navbar-static-top {
        position: fixed;
        top: 0;
        width: 100%;
    }
    body {
        padding-top: 72px;
    }
}

EOD;
        }

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
}
