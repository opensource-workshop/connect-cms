<?php

namespace App\Traits;

use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

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
    public function view_error($error_code)
    {
        // 表示テンプレートを呼び出す。
        return view('errors.' . $error_code);
    }
}

