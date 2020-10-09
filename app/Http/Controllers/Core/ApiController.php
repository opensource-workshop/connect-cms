<?php

namespace App\Http\Controllers\Core;

use Illuminate\Http\Request;    // 依存注入のための指定
use Illuminate\Routing\Router;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Route;

use App\Http\Controllers\Core\ConnectController;
use App\Http\Requests;

use File;

use App\Plugins\Api\ApiPluginBase;

use App\User;

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
     *  json encode
     */
    public function encodeJson($value, $request = null)
    {
        // UNOCIDE エスケープ指定
        if (!empty($request) && $request->filled('escape') && $request->escape == 'json_unescaped_unicode') {
            return json_encode($value, JSON_UNESCAPED_UNICODE);
        }
        return json_encode($value);
    }

    /**
     *  APIプラグインのインスタンス生成
     *
     * @param String $plugin_name
     * @return obj 生成したインスタンス
     */
    private static function createApiInstance($plugin_name)
    {
        // クラス名。初期値はプラグイン名
        $class_name = $plugin_name;

        // プラグイン毎に動的にnew するので、use せずにここでrequire する。
        $file_path = base_path() . "/app/Plugins/Api/" . ucfirst($plugin_name) . "/" . ucfirst($plugin_name) . ".php";

        if (!File::exists($file_path)) {
            $file_path = base_path() . "/app/Plugins/Api/" . ucfirst($plugin_name) . "/" . ucfirst($plugin_name) . "Api.php";
            if (File::exists($file_path)) {
                $class_name = $plugin_name . 'Api';
            } else {
                // 指定されたファイルがない
                return false;
            }
        }

        require $file_path;

        /// 引数のアクションと同じメソッドを呼び出す。
        $class_path = "app\Plugins\Api\\" . ucfirst($plugin_name) . "\\" . ucfirst($class_name);
        $plugin_instance = new $class_path;
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

        // インスタンスが API プラグインか判定（スーパークラスで判定）
        if (!$plugin_instance instanceof ApiPluginBase) {
            $ret = array('code' => 404, 'message' => '指定されたインターフェースは存在しません。');
            return $this->encodeJson($ret, $request);
        }

        // メソッドの有無確認
        if (!method_exists($plugin_instance, $action)) {
            $ret = array('code' => 404, 'message' => '指定されたメソッドは存在しません。');
            return $this->encodeJson($ret, $request);
        }

        // 指定されたアクションを呼ぶ。
        return $plugin_instance->$action($request, $arg1, $arg2, $arg3, $arg4, $arg5);
    }
}
