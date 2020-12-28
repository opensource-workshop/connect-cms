<?php

namespace App\Http\Middleware;

use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Route;

use App\Models\Core\AppLog;

use Closure;

/**
 * ログミドルウェア
 *
 * @author 永原　篤 <nagahara@opensource-workshop.jp>
 * @copyright OpenSource-WorkShop Co.,Ltd. All Rights Reserved
 * @category コア
 * @package Middleware
 */
class ConnectLog
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
        // Configs
        $configs = $request->get('configs');

        // ルート名の取得
        $route_name = Route::current()->getName();

        // プラグイン名の取得
        $plugin_name = Route::current()->parameter('plugin_name');

        // ログを出力するかどうかの判定（最初はfalse、条件に合致したらtrue にする）
        $log_record_flag = false;

        // 変数の初期化
        $value = null;
        $return_code = null;

        // 種別
        // Login（ログイン）,logout（ログアウト）,Password（パスワード関連）,Register（ユーザ登録）,
        // Core（コア）,API（API）,Manage（管理処理）,MyPage（マイページ）,Page（一般ページ）,
        // Download（ダウンロード）,CSS（CSS）,File（ファイル）,Language（言語）,PasswordPage（パスワードページ）
        $type = 'PAGE';
        if ($route_name == 'login') {
            $type = 'Login';
        } elseif ($route_name == 'logout') {
            $type = 'logout';
        } elseif ($route_name == 'password.request') {
            $type = 'Password';
        } elseif ($route_name == 'password.email') {
            $type = 'Password';
        } elseif ($route_name == 'password.reset') {
            $type = 'Password';
        } elseif ($route_name == 'password.resetpost') {
            $type = 'Password';
        } elseif ($route_name == 'register') {
            $type = 'Register';
        } elseif ($route_name == 'get_core') {
            $type = 'Core';
        } elseif ($route_name == 'post_core') {
            $type = 'Core';
        } elseif ($route_name == 'get_api') {
            $type = 'API';
        } elseif ($route_name == 'get_manage') {
            $type = 'Manage';
        } elseif ($route_name == 'post_manage') {
            $type = 'Manage';
        } elseif ($route_name == 'get_mypage') {
            $type = 'MyPage';
        } elseif ($route_name == 'post_mypage') {
            $type = 'MyPage';
        } elseif ($route_name == 'get_plugin') {
            $type = 'Page';
        } elseif ($route_name == 'post_plugin') {
            $type = 'Page';
        } elseif ($route_name == 'post_redirect') {
            $type = 'Page';
        } elseif ($route_name == 'get_redirect') {
            $type = 'Page';
        } elseif ($route_name == 'post_download') {
            $type = 'Download';
        } elseif ($route_name == 'get_download') {
            $type = 'Download';
        } elseif ($route_name == 'get_css') {
            $type = 'CSS';
        } elseif ($route_name == 'post_upload') {
            $type = 'File';
        } elseif ($route_name == 'get_file') {
            $type = 'File';
        } elseif ($route_name == 'get_language') {
            $type = 'Language';
        } elseif ($route_name == 'password_input') {
            $type = 'PasswordPage';
        } elseif ($route_name == 'get_all') {
            $type = 'Page';
        } elseif ($route_name == 'post_all') {
            $type = 'Page';
        }

        // 記録範囲
        if ($configs->where('name', 'app_log_scope')->where('value', 'all')->isNotEmpty()) {
            // 全て
            $log_record_flag = true;
        } else {
            // 選択したもののみ
            // ログイン
            if ($configs->where('name', 'save_log_type_login')->where('value', '1')->isNotEmpty() && $route_name == 'login') {
                $value = $request->input("userid");
                $log_record_flag = true;
                $type = 'LOGIN';
            }
            // ログアウト
            if ($configs->where('name', 'save_log_type_login')->where('value', '1')->isNotEmpty() && $route_name == 'logout') {
                $value = Auth::user() ? Auth::user()->userid : null;
                $log_record_flag = true;
                $type = 'LOGOUT';
            }
            // ログイン後のページ操作
            if ($configs->where('name', 'save_log_type_authed')->where('value', '1')->isNotEmpty() && Auth::check()) {
                $log_record_flag = true;
            }

            // 一般ページ
            if ($configs->where('name', 'save_log_type_page')->where('value', '1')->isNotEmpty() && $type == 'Page') {
                $log_record_flag = true;
            }

            // 管理画面
            if ($configs->where('name', 'save_log_type_manage')->where('value', '1')->isNotEmpty() && $type == 'Manage') {
                $log_record_flag = true;
            }

            // マイページ
            if ($configs->where('name', 'save_log_type_mypage')->where('value', '1')->isNotEmpty() && $type == 'MyPage') {
                $log_record_flag = true;
            }

            // API
            if ($configs->where('name', 'save_log_type_api')->where('value', '1')->isNotEmpty() && $type == 'API') {
                $log_record_flag = true;
            }

            // 検索キーワード（検索アクションの条件を後で確認）
            if ($configs->where('name', 'save_log_type_search_keyword')->where('value', '1')->isNotEmpty()) {
                if ($request->filled('search_keyword')) {
                    $value = $request->input("search_keyword");
                    $log_record_flag = true;
                    $type = 'Search';
                }
            }

            // メール送信（メール送信アクションの条件を後で確認）
            // メール送信は、メール送信処理の際に、メール送信用共通関数を呼ぶようにして、そこでログを出力する。

            // パスワードページ認証
            if ($configs->where('name', 'save_log_type_passwordpage')->where('value', '1')->isNotEmpty() && $type == 'PasswordPage') {
                $log_record_flag = true;
            }

            // ダウンロード
            if ($configs->where('name', 'save_log_type_download')->where('value', '1')->isNotEmpty() && $type == 'Download') {
                $log_record_flag = true;
            }

            // CSS
            if ($configs->where('name', 'save_log_type_css')->where('value', '1')->isNotEmpty() && $type == 'CSS') {
                $log_record_flag = true;
            }

            // ファイル
            if ($configs->where('name', 'save_log_type_file')->where('value', '1')->isNotEmpty() && $type == 'File') {
                $log_record_flag = true;
            }

            // パスワード関係
            if ($configs->where('name', 'save_log_type_password')->where('value', '1')->isNotEmpty() && $type == 'Password') {
                if ($request->filled('email')) {
                    $value = $request->input("email");
                }
                $log_record_flag = true;
            }

            // ユーザ登録
            if ($configs->where('name', 'save_log_type_register')->where('value', '1')->isNotEmpty() && $type == 'Register') {
                // ログインID とeメールアドレスを記録する。
                $tmp_value = array();
                if ($request->filled('userid')) {
                    $tmp_value[] = 'userid:' . $request->input("userid");
                }
                if ($request->filled('email')) {
                    $tmp_value[] = 'email:' . $request->input("email");
                }
                $value = implode(',', $tmp_value);
                $log_record_flag = true;
            }

            // コア側処理
            if ($configs->where('name', 'save_log_type_core')->where('value', '1')->isNotEmpty() && $type == 'Core') {
                $log_record_flag = true;
            }

            // 言語切り替え
            if ($configs->where('name', 'save_log_type_language')->where('value', '1')->isNotEmpty() && $type == 'Language') {
                $log_record_flag = true;
            }

            // HTTPメソッド GET
            if ($configs->where('name', 'save_log_type_http_get')->where('value', '1')->isNotEmpty() && $request->isMethod('get')) {
                $log_record_flag = true;
            }

            // HTTPメソッド POST
            if ($configs->where('name', 'save_log_type_http_post')->where('value', '1')->isNotEmpty() && $request->isMethod('post')) {
                $log_record_flag = true;
            }

            // メール送信（メール送信アクションの条件を後で確認）
            // メール送信は、メール送信処理の際に、メール送信用共通関数を呼ぶようにして、そこでログを出力する。
        }

        // 条件に合致しない場合は、ログを記録しない。
        if (!$log_record_flag) {
            return $next($request);
        }

        // 判断の値
        $uri = $request->getRequestUri();

        // ログレコード
        $app_log = new AppLog();
        $app_log->ip_address   = $request->ip();
        $app_log->plugin_name  = $plugin_name;
        $app_log->uri          = $uri;
        $app_log->route_name   = $route_name;
        $app_log->method       = $request->method();
        $app_log->type         = $type;
        $app_log->return_code  = $return_code;
        $app_log->value        = $value;

        // ログイン後のみの項目
        if (Auth::check()) {
            $app_log->created_id = Auth::user()->id;
            $app_log->userid     = Auth::user()->userid;
        }
        $app_log->save();

        return $next($request);
    }
}
