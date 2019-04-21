<?php

namespace App\Http\Controllers\Core;

use Illuminate\Http\Request;    // 依存注入のための指定
use Illuminate\Support\Facades\Log;

use App\Http\Controllers\Core\ConnectController;

/**
 * クラスを呼び出す振り分けコントローラ
 *
 * @author 永原　篤 <nagahara@opensource-workshop.jp>
 * @copyright OpenSource-WorkShop Co.,Ltd. All Rights Reserved
 * @category コア
 * @package Contoroller
 */
class ClassController extends ConnectController
{
    /**
     *  管理プラグインのインスタンス生成
     *
     * @param String $plugin_name
     * @return obj 生成したインスタンス
     */
    public static function createManageInstance($plugin_name)
    {
        // プラグイン毎に動的にnew するので、use せずにここでrequire する。
        $file_path = base_path() . "/app/Plugins/Manage/" . ucfirst($plugin_name) . "Manage/" . ucfirst($plugin_name) . "Manage.php";
        require $file_path;

        /// 引数のアクションと同じメソッドを呼び出す。
        $class_name = "app\Plugins\Manage\\" . ucfirst($plugin_name) . "Manage\\" . ucfirst($plugin_name) . "Manage";
        $plugin_instance = new $class_name;
        return new $plugin_instance;
    }

    /**
     *  管理プラグインの呼び出し
     *
     * @param String $plugin_name
     * @return プラグインからの戻り値(HTMLなど)
     */
    public function invokeGetManage(Request $request, $plugin_name, $action = 'index', $page_id = null)
    {
        // インスタンス生成
        $plugin_instance = self::createManageInstance($plugin_name);

        // 指定されたアクションを呼ぶ。
        // 呼び出し先のアクションでは、view 関数でblade を呼び出している想定。
        // view 関数の戻り値はHTML なので、ここではそのままreturn して呼び出し元に返す。
        return $plugin_instance->$action($request, $page_id);
    }

    /**
     *  管理プラグインの呼び出し
     *
     * @param String $plugin_name
     * @return プラグインからの戻り値(HTMLなど)
     */
    public function invokePostManage(Request $request, $plugin_name, $action = 'index', $page_id = null)
    {
        // インスタンス生成
        $plugin_instance = self::createManageInstance($plugin_name);

        // 指定されたアクションを呼ぶ。
        // 呼び出し先のアクションでは、view 関数でblade を呼び出している想定。
        // view 関数の戻り値はHTML なので、ここではそのままreturn して呼び出し元に返す。
        return $plugin_instance->$action($request, $page_id);
    }

    /**
     *  コアプラグインのインスタンス生成
     *
     * @param String $plugin_name
     * @return プラグインからの戻り値(HTMLなど)
     */
    public static function createCoreInstance($plugin_name)
    {
        // Todo：コアの場合、ホワイトリストを作成して、呼び出せるクラストアクションを指定する。

        // プラグイン毎に動的にnew するので、use せずにここでrequire する。
        $file_path = base_path() . "/app/Http/Controllers/Core/" . ucfirst($plugin_name) . "Controller.php";
        require $file_path;

        /// 引数のアクションと同じメソッドを呼び出す。
        $class_name = "app\Http\Controllers\Core\\" . ucfirst($plugin_name) . "Controller";
        $plugin_instance = new $class_name;
        return new $plugin_instance;
    }

    /**
     *  コアプラグインの呼び出し
     *
     * @param String $plugin_name
     * @return プラグインからの戻り値(HTMLなど)
     */
    public function invokeGetCore(Request $request, $action_type, $action, $page_id = null, $id = null)
    {
        // インスタンス生成
        $plugin_instance = self::createCoreInstance($action_type);

        // 指定されたアクションを呼ぶ。
        // 呼び出し先のアクションでは、view 関数でblade を呼び出している想定。
        // view 関数の戻り値はHTML なので、ここではそのままreturn して呼び出し元に返す。
        return $plugin_instance->$action($request, $page_id, $id);
    }

    /**
     *  コアプラグインの呼び出し
     *
     * @param String $plugin_name
     * @return プラグインからの戻り値(HTMLなど)
     */
    public function invokePostCore(Request $request, $action_type, $action, $page_id = null, $frame_id = null, $area_id = null)
    {
        // インスタンス生成
        $plugin_instance = self::createCoreInstance($action_type);

        // 指定されたアクションを呼ぶ。
        // 呼び出し先のアクションでは、view 関数でblade を呼び出している想定。
        // view 関数の戻り値はHTML なので、ここではそのままreturn して呼び出し元に返す。
        return $plugin_instance->$action($request, $page_id, $frame_id, $area_id);
    }
}
