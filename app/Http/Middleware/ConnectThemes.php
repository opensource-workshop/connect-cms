<?php

namespace App\Http\Middleware;

use App\Models\Core\Configs;
use Closure;

class ConnectThemes
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
        // サイトテーマ詰込
        $configs = Configs::getSharedConfigs();
        $base_theme = Configs::getConfigsValue($configs, 'base_theme', null);
        $additional_theme = Configs::getConfigsValue($configs, 'additional_theme', null);
        $request->themes = [
            'css' => $base_theme,
            'js' => $base_theme,
            'additional_css' => $additional_theme,
            'additional_js' => $additional_theme,
        ];
        return $next($request);
    }
}
