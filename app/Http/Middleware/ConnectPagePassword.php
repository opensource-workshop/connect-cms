<?php

namespace App\Http\Middleware;

use Closure;

class ConnectPagePassword
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
        // \Log::debug('[' . __METHOD__ . '] ' . __FILE__ . ' (line ' . __LINE__ . ')');

        // app\Http\Middleware\ConnectPage.php でセットした値
        $page = $request->attributes->get('page');
        $page_tree = $request->attributes->get('page_tree');

        // パスワード付きページのチェック（パスワードを要求するか確認）
        if ($page && $page->isRequestPassword($request, $page_tree)) {
            // 認証されていなくてパスワードを要求する場合、パスワード要求画面を表示
            return redirect("/password/input/" . $page->id);
        }

        return $next($request);
    }
}
