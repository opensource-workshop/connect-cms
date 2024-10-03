<?php

namespace App\Http\Middleware;

use Closure;

use App\Models\Core\Configs;

class ConnectMypage
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
        // マイページの使用
        $configs = Configs::getSharedConfigs();

        if (Configs::getConfigsValue($configs, 'use_mypage', '0') == '0') {
            abort(403, "マイページを使用しないため、表示できません。");
        }
        return $next($request);
    }
}
