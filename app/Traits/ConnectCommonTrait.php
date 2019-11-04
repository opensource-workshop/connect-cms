<?php

namespace App\Traits;

use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

use App\Models\Core\Plugins;

trait ConnectCommonTrait
{
    /**
     * 権限チェック
     * roll_or_auth : 権限 or 役割
     */
    public function can($roll_or_auth, $post = null, $plugin_name = null)
    {
        $args = null;
        if ( $post != null || $plugin_name != null ) {
            $args = [[$post, $plugin_name]];
        }

        if (!Auth::check() || !Auth::user()->can($roll_or_auth, $args)) {
            return $this->view_error("403_inframe");
        }
    }

    /**
     * 権限チェック
     * roll_or_auth : 権限 or 役割
     */
    public function isCan($roll_or_auth, $post = null, $plugin_name = null)
    {
        $args = null;
        if ( $post != null || $plugin_name != null ) {
            $args = [[$post, $plugin_name]];
        }

        if (!Auth::check() || !Auth::user()->can($roll_or_auth, $args)) {
            return false;
        }
        return true;
    }

    /**
     * エラー画面の表示
     *
     */
    public function view_error($error_code, $message = null, $debug_message = null)
    {
        // 表示テンプレートを呼び出す。
        return view('errors.' . $error_code, ['message' => $message, 'debug_message' => $debug_message]);
    }

    /**
     * プラグイン一覧の取得
     *
     */
    public function getPlugins($arg_display_flag = true, $force_get = false)
    {
        // プラグイン一覧の取得
        $display_flag = ($arg_display_flag) ? 1 : 0;
        $plugins = Plugins::where('display_flag', $display_flag)->orderBy('display_sequence')->get();

        // 強制的に非表示にするプラグインを除外
        if ( !$force_get ) {
            foreach($plugins as $plugin_loop_key => $plugin) {
                if ( in_array(mb_strtolower($plugin->plugin_name), config('connect.PLUGIN_FORCE_HIDDEN'))) {
                    $plugins->forget($plugin_loop_key);
                }
            }
        }
        return $plugins;
    }

    /**
     *  曜日取得
     *
     */
    public function getWeekJp($date)
    {
        switch (date('N', strtotime($date))) {
        case 1:
            return "月";
            break;
        case 2:
            return "火";
            break;
        case 3:
            return "水";
            break;
        case 4:
            return "木";
            break;
        case 5:
            return "金";
            break;
        case 6:
            return "土";
            break;
        case 7:
            return "日";
            break;
        }
    }
}

