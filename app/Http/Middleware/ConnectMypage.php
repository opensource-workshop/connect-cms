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
        // $use_mypage = Configs::where('name', 'use_mypage')->first();
        $configs = Configs::getSharedConfigs();

        // if (empty($use_mypage) || $use_mypage->value == '0') {
        if (Configs::getConfigsValue($configs, 'use_mypage', '0') == '0') {
            abort(403, "マイページを使用しないため、表示できません。");
        }
        // サイトテーマ詰込
        $base_theme = Configs::getConfigsValue($configs, 'base_theme', null);
        $additional_theme = Configs::getConfigsValue($configs, 'additional_theme', null);
        $themes = [
                    'css' => $base_theme,
                    'js' => $base_theme,
                    'additional_css' => $additional_theme,
                    'additional_js' => $additional_theme,
        ];
        $request->themes = $themes;
        return $next($request);
    }
}
