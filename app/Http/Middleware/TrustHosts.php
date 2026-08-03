<?php

namespace App\Http\Middleware;

use Illuminate\Http\Request;
use Illuminate\Http\Middleware\TrustHosts as Middleware;

class TrustHosts extends Middleware
{
    /**
     * Handle the incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  callable  $next
     * @return \Illuminate\Http\Response
     */
    public function handle(Request $request, $next)
    {
        Request::setTrustedHosts(array_filter($this->hosts()));

        // Laravel標準のTrustHostsは信頼するHostパターンを設定するだけで、
        // Hostヘッダの検証はURL生成などでgetHost()が呼ばれるまで遅延する。
        // 許可外Hostをこのミドルウェアで即時拒否するため、明示的に検証を発火させる。
        $request->getHost();

        return $next($request);
    }

    /**
     * Get the host patterns that should be trusted.
     *
     * @return array
     */
    public function hosts()
    {
        return array_filter([$this->applicationUrlHostPattern()]);
    }

    /**
     * APP_URL のホストに一致する正規表現を生成する。
     *
     * @return string|null
     */
    private function applicationUrlHostPattern()
    {
        $app_url = config('app.url');
        $host = parse_url($app_url, PHP_URL_HOST) ?: parse_url('http://' . $app_url, PHP_URL_HOST);

        return $host ? '^' . preg_quote($host, '#') . '$' : null;
    }
}
