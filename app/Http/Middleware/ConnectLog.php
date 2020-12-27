<?php

namespace App\Http\Middleware;

use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Route;

use App\Models\Core\AppLog;

use Closure;

/**
 * ログミドルウェア
 *
 * @author 永原　篤 <nagahara@opensource-workshop.jp>
 * @copyright OpenSource-WorkShop Co.,Ltd. All Rights Reserved
 * @category コア
 * @package Middleware
 */
class ConnectLog
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle($request, Closure $next)
    {
        // Configs
        $configs = $request->get('configs');

        // ルート名の取得
        $route_name = Route::current()->getName();

        // プラグイン名の取得
        $plugin_name = Route::current()->parameter('plugin_name');

        // ログを出力するかどうかの判定（最初はfalse、条件に合致したらtrue にする）
        $log_record_flag = false;

        // 変数の初期化
        $value = null;
        $type = 'PAGE';
        $return_code = null;

        // 記録範囲
        if ($configs->where('name', 'app_log_scope')->where('value', 'all')->isNotEmpty()) {
            // 全て
            $log_record_flag = true;
        } else {
            // 選択したもののみ
            // ログイン
            if ($configs->where('name', 'save_log_type_login')->where('value', '1')->isNotEmpty() && $route_name == 'login') {
                $value = $request->input("userid");
                $log_record_flag = true;
                $type = 'LOGIN';
            }
            // ログアウト
            if ($configs->where('name', 'save_log_type_login')->where('value', '1')->isNotEmpty() && $route_name == 'logout') {
                $value = Auth::user() ? Auth::user()->userid : null;
                $log_record_flag = true;
                $type = 'LOGOUT';
            }
            // ログイン後のページ操作
            if ($configs->where('name', 'save_log_type_authed')->where('value', '1')->isNotEmpty() && Auth::check()) {
                $log_record_flag = true;
            }
            // 検索キーワード（検索アクションの条件を後で確認）
            if ($configs->where('name', 'save_log_type_search_keyword')->where('value', '1')->isNotEmpty()) {
                if ($request->filled('search_keyword')) {
                    $value = $request->input("search_keyword");
                    $log_record_flag = true;
                    $type = 'SEARCH';
                }
            }
            // メール送信（メール送信アクションの条件を後で確認）
            // メール送信は、メール送信処理の際に、メール送信用共通関数を呼ぶようにして、そこでログを出力する。
        }

        // 条件に合致しない場合は、ログを記録しない。
        if (!$log_record_flag) {
            return $next($request);
        }

        // 判断の値
        $uri = $request->getRequestUri();

        // ログレコード
        $app_log = new AppLog();
        $app_log->ip_address   = $request->ip();
        $app_log->plugin_name  = $plugin_name;
        $app_log->uri          = $uri;
        $app_log->route_name   = $route_name;
        $app_log->method       = $request->method();
        $app_log->type         = $type;
        $app_log->return_code  = $return_code;
        $app_log->value        = $value;

        // ログイン後のみの項目
        if (Auth::check()) {
            $app_log->created_id = Auth::user()->id;
            $app_log->userid     = Auth::user()->userid;
        }
        $app_log->save();

        return $next($request);
    }
}
