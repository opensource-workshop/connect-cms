<?php

namespace App\Http\Middleware;

use Illuminate\Support\Facades\Route;

use Closure;

class ResponseHeader
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
        $response = $next($request);

        // 除外ルート名
        $exclude_route_names = ['get_file', 'get_userfile'];

        if (!in_array(Route::currentRouteName(), $exclude_route_names)) {
            // セキュリティ設定でHTTP ヘッダを指定する。
            $response->headers->set('Cache-Control', 'no-store');
            $response->headers->set('Expires', 'Thu, 01 Dec 1994 16:00:00 GMT');
        }

        return $response;
    }
}
