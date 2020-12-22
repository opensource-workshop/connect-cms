<?php

namespace App\Http\Middleware;

use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Route;

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
        // ルート名の取得
        $route_name = Route::current()->getName();

        // プラグイン名の取得
        $plugin_name = Route::current()->parameter('plugin_name');
Log::debug("---");
Log::debug($route_name);
Log::debug($plugin_name);

        // 認証情報
        if (Auth::check()) {
            //$app_log->created_id = Auth::user()->id;
            //$app_log->userid     = Auth::user()->userid;
        }

        // Log::debug("ConnectLog handle route_name = " . $route_name);

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
//            $plugin_name = (is_array($uri_array) && count($uri_array) > 2) ? $uri_array[2] : '';
        }


        return $next($request);
    }
}
