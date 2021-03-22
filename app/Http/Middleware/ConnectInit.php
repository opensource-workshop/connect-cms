<?php

namespace App\Http\Middleware;

use App\Models\Core\Configs;

use Closure;

class ConnectInit
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
        /* --- セッション関係 --- */

        // セッションのデバックモードは、null(env参照)、0(セッション内 OFF)、1(セッション内 On)
        // 初期値は環境変数
        $now_debug_mode = Config('app.debug');

        // セッションのデバックモードの取得
        $debug_mode_session = session('app_debug');

        // セッションに設定されていない状態
        // 環境変数のデバックモードの取得(現在の動作モード)
        if ($debug_mode_session == null or $debug_mode_session == '') {
            // 初期値のまま
        } elseif ($debug_mode_session === '0') {
            config(['app.debug' => false]);
        } elseif ($debug_mode_session === '1') {
            config(['app.debug' => true]);
        }

        /* --- 共通で使用するDB --- */

        // Connect-CMS の各種設定
        // bugfix:【サイト管理・バグ】サイト名が サイト管理＞サイト基本設定 以外適用されない対応
        // $request->attributes->add(['configs' => Configs::get()]);
        $configs = Configs::get();
        if (isset($configs)) {
            $base_site_name = $configs->firstWhere('name', 'base_site_name');
            $configs_base_site_name = $base_site_name->value;
        } else {
            $configs_base_site_name = config('app.name', 'Connect-CMS');
        }
        // requestにセット
        $request->attributes->add(['configs' => $configs]);

        // *** 全ビュー間のデータ共有
        // サイト名
        \View::share('configs_base_site_name', $configs_base_site_name);

        return $next($request);
    }
}
