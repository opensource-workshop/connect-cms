<?php

namespace App\Http\Middleware;

use Illuminate\Support\Facades\View;
use Illuminate\Support\Facades\Schema;

use App\Models\Core\Configs;
// use App\Models\Core\FrameConfig;

use Closure;

class ConnectInit
{
    /**
     * Handle an incoming request.
     *
     * ・requestにセット
     *   ・configs
     * ・全ビュー間のデータ共有
     *   ・cc_configs
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle($request, Closure $next)
    {
        // \Log::debug('[' . __METHOD__ . '] ' . __FILE__ . ' (line ' . __LINE__ . ')');
        /* --- セッション関係 --- */

        // セッションのデバックモードは、null(env参照)、0(セッション内 OFF)、1(セッション内 On)
        // 初期値は環境変数
        // $now_debug_mode = Config('app.debug');

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

        // configsテーブルがなければ、DBマイグレーション未実行と推定。
        //   - configsテーブルないと Configs::get() で 500エラーになる。
        //   - APP_KEY は設定されてないとここには到達せず、500エラーになるため、php artisan key:generate は実行済みの想定
        if (! Schema::hasTable('configs')) {
            // configsテーブルなしのため、空のコレクション型をセット
            View::share('cc_configs', collect());

            abort(403, 'DBテーブルのconfigsが存在しません。php artisan migrate コマンドを実行してDBテーブルを作成してください。');
        }

        // Connect-CMS の各種設定
        // bugfix:【サイト管理・バグ】サイト名が サイト管理＞サイト基本設定 以外適用されない対応
        // $request->attributes->add(['configs' => Configs::get()]);
        $configs = Configs::get();

        // requestにセット
        $request->attributes->add(['configs' => $configs]);

        // *** 全ビュー間のデータ共有
        // サイト名
        // if (isset($configs)) {
        //     $base_site_name = $configs->firstWhere('name', 'base_site_name');
        //     $configs_base_site_name = $base_site_name->value ?? config('app.name', 'Connect-CMS');
        // } else {
        //     $configs_base_site_name = config('app.name', 'Connect-CMS');
        // }
        // View::share('configs_base_site_name', $configs_base_site_name);
        View::share('cc_configs', $configs);

        if ($configs->isEmpty()) {
            abort(403, 'DBテーブルのconfigsにデータが１件もありません。php artisan db:seed コマンドを実行して初期データを登録してください。');
        }
        // move: フレームは一般画面のみ使う変数のため、app\Http\Middleware\ConnectFrame.php に移動
        // // フレーム設定の共有
        // $frame_configs = FrameConfig::get();
        // $request->attributes->add(['frame_configs' => $frame_configs]);

        return $next($request);
    }
}
