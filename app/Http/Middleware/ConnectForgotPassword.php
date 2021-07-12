<?php

namespace App\Http\Middleware;

use Closure;

use App\Models\Core\Configs;

class ConnectForgotPassword
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
        // パスワードリセットの使用
        $base_login_password_reset = Configs::where('name', 'base_login_password_reset')->first();

        if (empty($base_login_password_reset) || $base_login_password_reset->value == '0') {
            abort(403, "パスワードリセットを使用しないため、表示できません。");
        }

        return $next($request);
    }
}
