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

use App\Traits\ConnectCommonTrait;

//use App\User;
//use App\Repositories\UserRepository;

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

    use ConnectCommonTrait;

    /**
     *  管理プラグインのインスタンス生成
     *
     * @param String $plugin_name
     * @return obj 生成したインスタンス
     */
    private static function createManageInstance($plugin_name)
    {
        // プラグイン毎に動的にnew するので、use せずにここでrequire する。
        $file_path = base_path() . "/app/Plugins/Manage/" . ucfirst($plugin_name) . "Manage/" . ucfirst($plugin_name) . "Manage.php";
        require $file_path;

        /// インスタンスを生成して返す。
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
    public function invokeGetManage(Request $request, $plugin_name, $action = 'index', $id = null)
    {
        return $this->invokeManage($request, $plugin_name, $action, $id);
    }

    /**
     *  管理プラグインの呼び出し
     *
     * @param String $plugin_name
     * @return プラグインからの戻り値(HTMLなど)
     */
    public function invokePostManage(Request $request, $plugin_name, $action = 'index', $id = null)
    {
        return $this->invokeManage($request, $plugin_name, $action, $id);
    }

    /**
     *  管理プラグインの呼び出し
     *
     * @param String $plugin_name
     * @return プラグインからの戻り値(HTMLなど)
     */
    private function invokeManage($request, $plugin_name, $action = 'index', $id = null)
    {
        // ログインしているユーザー情報を取得
        $user = Auth::user();

        // 権限エラー
        if (empty($user)) {
            abort(403, 'ログインが必要です。');
        }

        // インスタンス生成
        $plugin_instance = self::createManageInstance($plugin_name);

        // 権限定義メソッドの有無確認
        if (!method_exists($plugin_instance, 'declareRole')) {
            abort(403, '権限定義メソッド(declareRole)がありません。');
        }

        // 権限チェック（管理系各プラグインの関数＆権限チェックデータ取得）
        $role_ckeck_tables = $plugin_instance->declareRole();
        if (array_key_exists($action, $role_ckeck_tables)) {
            foreach($role_ckeck_tables[$action] as $role) {
                // プラグインで定義された権限が自分にあるかチェック
                if (!$this->isCan($role)) {
                    abort(403, 'ユーザーにメソッドに対する権限がありません。');
                }
            }
        }
        else {
            abort(403, 'メソッドに権限がありません。');
        }

        // 指定されたアクションを呼ぶ。
        // 呼び出し先のアクションでは、view 関数でblade を呼び出している想定。
        // view 関数の戻り値はHTML なので、ここではそのままreturn して呼び出し元に返す。
        return $plugin_instance->$action($request, $id);
    }

    /**
     *  コアプラグインのインスタンス生成
     *
     * @param String $plugin_name
     * @return プラグインからの戻り値(HTMLなど)
     */
    private static function createCoreInstance($plugin_name, $page_id, $frame_id)
    {
        // Todo：コアの場合、ホワイトリストを作成して、呼び出せるクラストアクションを指定する。
        // プラグイン毎に動的にnew するので、use せずにここでrequire する。
        $file_path = base_path() . "/app/Http/Controllers/Core/" . ucfirst($plugin_name) . "Controller.php";
        require $file_path;

        /// インスタンス生成
        $class_name = "app\Http\Controllers\Core\\" . ucfirst($plugin_name) . "Controller";
        $plugin_instance = new $class_name($page_id, $frame_id);
        return $plugin_instance;
    }

    /**
     *  コアプラグインの呼び出し
     *
     * @param String $plugin_name
     * @return プラグインからの戻り値(HTMLなど)
     */
    public function invokeGetCore(Request $request, $action_type, $action, $page_id = null, $frame_id = null)
    {
        // インスタンス生成
        $plugin_instance = self::createCoreInstance($action_type, $page_id, $frame_id);

        // 指定されたアクションを呼ぶ。
        // 呼び出し先のアクションでは、view 関数でblade を呼び出している想定。
        // view 関数の戻り値はHTML なので、ここではそのままreturn して呼び出し元に返す。
        return $plugin_instance->$action($request, $page_id, $frame_id);
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
        $plugin_instance = self::createCoreInstance($action_type, $page_id, $frame_id);

        // 指定されたアクションを呼ぶ。
        // 呼び出し先のアクションでは、view 関数でblade を呼び出している想定。
        // view 関数の戻り値はHTML なので、ここではそのままreturn して呼び出し元に返す。
        return $plugin_instance->$action($request, $page_id, $frame_id, $area_id);
    }
}
