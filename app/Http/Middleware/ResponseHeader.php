<?php

namespace App\Http\Middleware;

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

        // セキュリティ設定でHTTP ヘッダを指定する。
        $response->headers->set('Cache-Control', config('connect.CACHE_CONTROL'));
        $response->headers->set('Expires', config('connect.EXPIRES'));

        return $response;
    }
}
