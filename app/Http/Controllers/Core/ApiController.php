<?php

namespace App\Http\Controllers\Core;

use Illuminate\Http\Request;    // 依存注入のための指定
use Illuminate\Routing\Router;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Route;

use App\Http\Controllers\Core\ConnectController;
use App\Http\Requests;

use App\User;

//use App\User;
//use App\Repositories\UserRepository;

/**
 * APIクラスを呼び出す振り分けコントローラ
 *
 * @author 永原　篤 <nagahara@opensource-workshop.jp>
 * @copyright OpenSource-WorkShop Co.,Ltd. All Rights Reserved
 * @category コア
 * @package Contoroller
 */
class ApiController extends ConnectController
{

    /**
     *  APIプラグインのインスタンス生成
     *
     * @param String $plugin_name
     * @return obj 生成したインスタンス
     */
    private static function createApiInstance($plugin_name)
    {
        // プラグイン毎に動的にnew するので、use せずにここでrequire する。
        $file_path = base_path() . "/app/Plugins/Api/" . ucfirst($plugin_name) . "/" . ucfirst($plugin_name) . ".php";
        require $file_path;

        /// 引数のアクションと同じメソッドを呼び出す。
        $class_name = "app\Plugins\Api\\" . ucfirst($plugin_name) . "\\" . ucfirst($plugin_name);
        $plugin_instance = new $class_name;
        return new $plugin_instance;
    }

    /**
     *  APIプラグインの呼び出し
     *
     * @param String $plugin_name
     * @return プラグインからの戻り値(HTMLなど)
     */
    public function invokeApi(Request $request, $plugin_name, $action, $arg1 = null, $arg2 = null, $arg3 = null, $arg4 = null, $arg5 = null)
    {
        // インスタンス生成
        $plugin_instance = self::createApiInstance($plugin_name);

//        // 権限定義メソッドの有無確認
//        if (!method_exists($plugin_instance, 'declareRole')) {
//            abort(403, '権限定義メソッド(declareRole)がありません。');
//        }

//        // 権限エラー
//        $role_ckeck_table = $plugin_instance->declareRole();
//        if (array_key_exists($action, $role_ckeck_table)) {
//            if (!in_array($user->role, $role_ckeck_table[$action])) {
//                abort(403, 'ユーザーにメソッドに対する権限がありません。');
//            }
//        }
//        else {
//            abort(403, 'メソッドに権限がありません。');
//        }

        // 指定されたアクションを呼ぶ。
        return $plugin_instance->$action($request, $arg1, $arg2, $arg3, $arg4, $arg5);
    }
}
