<?php
namespace App\Traits;

use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Route;

use App\User;
use App\Models\Core\Configs;
use App\Models\Core\AppLog;

/**
 * ログテーブル関係の共通処理
 *
 * @author 永原　篤 <nagahara@opensource-workshop.jp>
 * @copyright OpenSource-WorkShop Co.,Ltd. All Rights Reserved
 * @category ログ管理
 * @package CommonTrait
 */
trait ConnectLogTrait
{
    /**
     * ログテーブルの出力
     *
     * @return boolean
     */
    public function putAppLog($request, $config, $type = null, $send_address = null, $return_code = null, $value = null)
    {
        // ログ記録の対象かの判断
        $log_record_flag = false;

        // 判断の値
        $uri = $request->getRequestUri();
        $uri_array = explode('/', $uri);

        // --- プラグイン名の判断
        $route_name = Route::current()->getName();

        $plugin_name = '';
        if (($route_name == 'get_core')      ||
            ($route_name == 'post_core')     ||
            ($route_name == 'get_api')       ||
            ($route_name == 'get_manage')    ||
            ($route_name == 'post_manage')   ||
            ($route_name == 'get_mypage')    ||
            ($route_name == 'post_mypage')   ||
            ($route_name == 'get_plugin')    ||
            ($route_name == 'post_plugin')   ||
            ($route_name == 'post_redirect') ||
            ($route_name == 'get_redirect')  ||
            ($route_name == 'post_download')) {
            $plugin_name = (is_array($uri_array) && count($uri_array) > 2) ? $uri_array[2] : '';
        }

        // --- 記録

        // 記録範囲
        if ($config->where('name', 'app_log_scope')->where('value', 'all')->isNotEmpty()) {
            // 全て
            $log_record_flag = true;
        } else {
            // 選択したもののみ
            // ログイン操作
            if ($config->where('name', 'save_log_type_login')->where('value', '1')->isNotEmpty() && stripos($uri, '/login') === 0) {
                $log_record_flag = true;
            }
            // ログイン後のページ操作
            if ($config->where('name', 'save_log_type_authed')->where('value', '1')->isNotEmpty() && Auth::check()) {
                $log_record_flag = true;
            }
            // 検索キーワード（検索アクションの条件を後で確認）
            //if ($config->firstWhere('name', 'save_log_type_search_keyword')->get('value') == '1' && ) {
            //    $log_record_flag = true;
            //}
            // メール送信（メール送信アクションの条件を後で確認）
            //if ($config->firstWhere('name', 'save_log_type_search_keyword')->get('value') == '1' && ) {
            //    $log_record_flag = true;
            //}
        }

        // 条件に合致しない場合は、ログを記録しない。
        if (!$log_record_flag) {
            return;
        }

        // ログレコード
        $app_log = new AppLog();
        $app_log->ip_address   = $request->ip();
        $app_log->plugin_name  = $plugin_name;
        $app_log->uri          = $uri;
        $app_log->type         = $type;
        $app_log->send_address = $send_address;
        $app_log->return_code  = $return_code;
        $app_log->value        = $value;
        // ログイン後のみの項目
        if (Auth::check()) {
            $app_log->created_id = Auth::user()->id;
            $app_log->userid     = Auth::user()->userid;
        }
        $app_log->save();

        return;
    }
}
