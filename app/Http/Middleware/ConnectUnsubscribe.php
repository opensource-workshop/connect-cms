<?php

namespace App\Http\Middleware;

use App\Models\Core\Configs;
use Closure;
use Illuminate\Support\Facades\Auth;

class ConnectUnsubscribe
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
        // メール配信管理の使用
        $configs = Configs::getSharedConfigs();
        if (Configs::getConfigsValue($configs, 'use_unsubscribe', '0') == '0') {
            abort(403, "メール配信管理を使用しないため、表示できません。");
        }

        // ログインしているユーザー情報を取得
        $user = Auth::user();

        // 権限エラー
        if (empty($user)) {
            abort(403, 'ログインが必要です。');
        }

        return $next($request);
    }
}
