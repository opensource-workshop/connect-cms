<?php

namespace App\Http\Controllers\Core;

use Illuminate\Http\Request;
use Illuminate\Routing\Router;
use Illuminate\Support\Facades\Auth;

use App\Http\Controllers\Controller;

// use App\Traits\ConnectCommonTrait;

/**
 * コア用の基底クラス
 *
 * @author 永原　篤 <nagahara@opensource-workshop.jp>
 * @copyright OpenSource-WorkShop Co.,Ltd. All Rights Reserved
 * @category ユーザープラグイン
 * @package Controller
 */
class ConnectController extends Controller
{
    // use ConnectCommonTrait;

    /**
     * 画面表示
     * ページ共通で必要な値をココで取得、viewに渡す。
     */
    protected function view($blade_path, $args)
    {
        // delete: 管理画面・一般画面全てのviewで参照できる全configsは、$cc_configsとしてセットしたため、ここは廃止。$cc_configsのセット場所は app\Http\Middleware\ConnectInit::handle().
        // 一般設定の取得
        // $configs = Configs::where('category', 'general')->orWhere('category', 'user_register')->get();
        // $configs_array = array();
        // foreach ($configs as $config) {
        //     $configs_array[$config['name']] = $config['value'];
        // }
        // $args["configs"] = $configs_array;

        $request = app(Request::class);

        // app\Http\Middleware\ConnectPage.php でセットした値
        $http_status_code = $request->get('http_status_code');

        // move: app\Http\Middleware\ConnectPage.php で処理＆全Viewに値セットするように変更
        // ハンバーガーメニューで使用するページの一覧
        // $args["page_list"] = $this->getPageList();
        //
        // ページに対する権限
        // $args["page_roles"] = $this->getPageRoles();

        // if ($this->http_status_code) {
        //     return response()->view($blade_path, $args, $this->http_status_code);
        // }
        //
        // HTTP ステータスコード（null なら200）
        if ($http_status_code) {
            return response()->view($blade_path, $args, $http_status_code);
        }

        return view($blade_path, $args);
    }
}
