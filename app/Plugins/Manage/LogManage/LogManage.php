<?php

namespace App\Plugins\Manage\LogManage;

use Symfony\Component\HttpFoundation\StreamedResponse;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;

use App\Models\Core\AppLog;
use App\Models\Core\Configs;

use App\Plugins\Manage\ManagePluginBase;

/**
 * ログ管理クラス
 *
 * @author 永原　篤 <nagahara@opensource-workshop.jp>
 * @copyright OpenSource-WorkShop Co.,Ltd. All Rights Reserved
 * @category ログ管理
 * @package Contoroller
 * @plugin_title ログ管理
 * @plugin_desc ログに関する機能が集まった管理機能です。
 */
class LogManage extends ManagePluginBase
{
    /**
     *  権限定義
     */
    public function declareRole()
    {
        // 権限チェックテーブル
        $role_ckeck_table = array();
        $role_ckeck_table["index"]       = array('admin_system');
        $role_ckeck_table["search"]      = array('admin_system');
        $role_ckeck_table["clearSearch"] = array('admin_system');
        $role_ckeck_table["edit"]        = array('admin_system');
        $role_ckeck_table["update"]      = array('admin_system');
        $role_ckeck_table["downloadCsv"] = array('admin_system');
        return $role_ckeck_table;
    }

    /**
     *  ログデータSQL取得
     *
     * @return view
     */
    private function getQuery($request)
    {
        // ログデータ取得
        $app_logs_query = AppLog::select('app_logs.*');

        // ログインID
        if ($request->session()->has('app_log_search_condition.userid')) {
            $app_logs_query->where('userid', 'like', '%' . $request->session()->get('app_log_search_condition.userid') . '%');
        }

        // 詳細条件
        $app_logs_query->where(function ($query) use ($request) {
            // ログイン
            if ($request->session()->has('app_log_search_condition.log_type_login')) {
                $query->orWhere('type', '=', 'Login');
            }
            // ログアウト
            if ($request->session()->has('app_log_search_condition.log_type_logout')) {
                $query->orWhere('type', '=', 'logout');
            }
            // ログイン後のページ操作
            if ($request->session()->has('app_log_search_condition.log_type_authed')) {
                $query->orWhereNotNull('userid');
            }
            // 一般ページ
            if ($request->session()->has('app_log_search_condition.log_type_search_keyword')) {
                $query->orWhere('type', '=', 'Page');
            }
            // 管理画面
            if ($request->session()->has('app_log_search_condition.log_type_manage')) {
                $query->orWhere('type', '=', 'Manage');
            }
            // マイページ
            if ($request->session()->has('app_log_search_condition.log_type_mypage')) {
                $query->orWhere('type', '=', 'MyPage');
            }
            // API
            if ($request->session()->has('app_log_search_condition.log_type_api')) {
                $query->orWhere('type', '=', 'API');
            }
            // 検索キーワード
            if ($request->session()->has('app_log_search_condition.log_type_search_keyword')) {
                $query->orWhere('type', '=', 'Search');
            }
            // メール送信
            if ($request->session()->has('app_log_search_condition.log_type_sendmail')) {
                $query->orWhere('type', '=', 'SendMail');
            }
            // パスワードページ認証
            if ($request->session()->has('app_log_search_condition.log_type_passwordpage')) {
                $query->orWhere('type', '=', 'PasswordPage');
            }
            // ダウンロード
            if ($request->session()->has('app_log_search_condition.log_type_download')) {
                $query->orWhere('type', '=', 'Download');
            }
            // CSS
            if ($request->session()->has('app_log_search_condition.log_type_css')) {
                $query->orWhere('type', '=', 'CSS');
            }
            // ファイル
            if ($request->session()->has('app_log_search_condition.log_type_file')) {
                $query->orWhere('type', '=', 'File');
            }
            // パスワード関係
            if ($request->session()->has('app_log_search_condition.log_type_password')) {
                $query->orWhere('type', '=', 'Password');
            }
            // ユーザ登録
            if ($request->session()->has('app_log_search_condition.log_type_register')) {
                $query->orWhere('type', '=', 'Register');
            }
            // コア側処理
            if ($request->session()->has('app_log_search_condition.log_type_core')) {
                $query->orWhere('type', '=', 'Core');
            }
            // 言語切り替え
            if ($request->session()->has('app_log_search_condition.log_type_language')) {
                $query->orWhere('type', '=', 'Language');
            }
            // 検索キーワード
            if ($request->session()->has('app_log_search_condition.log_type_search_keyword')) {
                $query->orWhere('type', '=', 'Search');
            }
            // メール送信
            if ($request->session()->has('app_log_search_condition.log_type_sendmail')) {
                $query->orWhere('type', '=', 'SendMail');
            }
            // ページ操作
            if ($request->session()->has('app_log_search_condition.log_type_page')) {
                $query->orWhere('type', '=', 'Page');
            }
            // HTTPメソッド(GET)
            if ($request->session()->has('app_log_search_condition.log_type_http_get')) {
                $query->orWhere('METHOD', '=', 'GET');
            }
            // HTTPメソッド(POST)
            if ($request->session()->has('app_log_search_condition.log_type_http_post')) {
                $query->orWhere('METHOD', '=', 'POST');
            }
        });

        // Query取得
        return $app_logs_query;
    }

    /**
     *  ログデータ取得
     *
     * @return view
     */
    private function getData($request)
    {
        // ログデータ取得
        $app_logs_query = $this->getQuery($request);

        // データ取得
        return $app_logs_query->orderBy('id', 'desc')->paginate(10);
    }

    /**
     *  ログ表示
     *
     * @return view
     * @method_title ログ一覧
     * @method_desc 保存したログを一覧で確認できます。
     * @method_detail ログの絞り込みやダウンロードができます。
     */
    public function index($request)
    {
        // Config データの取得
        $configs = Configs::get();

        // Config データの変換
        $configs_array = array();
        foreach ($configs as $config) {
            $configs_array[$config->name] = $config->value;
        }

        // データ取得
        $app_logs = $this->getData($request);

        // 画面の呼び出し
        return view('plugins.manage.log.log', [
            "function"    => __FUNCTION__,
            "plugin_name" => "log",
            "app_logs"    => $app_logs,
            "configs"     => $configs_array,
        ]);
    }

    /**
     *  検索条件設定処理
     */
    public function search($request, $id)
    {
        // 検索ボタンが押されたときはここが実行される。検索条件を設定してindex を呼ぶ。
        // 画面上、検索条件は app_log_search_condition という名前で配列になっているので、
        // app_log_search_condition をセッションに持つことで、条件の持ち回りが可能。
        session(["app_log_search_condition" => $request->input('app_log_search_condition')]);

        return redirect("/manage/log");
    }

    /**
     *  検索条件クリア処理
     */
    public function clearSearch($request, $id)
    {
        // 検索条件をクリアし、index 処理を呼ぶ。
        $request->session()->forget('app_log_search_condition');
        return $this->index($request, $id);
    }

    /**
     *  ログ設定画面
     *
     * @return view
     * @method_title ログ設定
     * @method_desc 保存するログの種類を指定できます。
     * @method_detail ログイン関係や処理の種別、HTTPメソッドなど複数の切り口で設定が可能です。
     */
    public function edit($request)
    {
        // Config データの取得
        $configs = Configs::get();

        // Config データの変換
        $configs_array = array();
        foreach ($configs as $config) {
            $configs_array[$config->name] = $config->value;
        }

        // 画面の呼び出し
        return view('plugins.manage.log.edit', [
            "function" => __FUNCTION__,
            "plugin_name" => "log",
            "configs" => $configs_array,
        ]);
    }

    /**
     *  ログ設定更新
     */
    private function updateImpl($request, $category, $name)
    {
        $configs = Configs::updateOrCreate(
            ['name'     => $name],
            ['category' => $category,
             'value'    => $request->input($name)]
        );
        return;
    }

    /**
     *  ログ設定更新
     */
    public function update($request)
    {
        // httpメソッド確認
        if (!$request->isMethod('post')) {
            abort(403, '権限がありません。');
        }

        // 記録範囲
        $this->updateImpl($request, 'app_log', 'app_log_scope');

        // ログイン・ログアウト
        $this->updateImpl($request, 'app_log', 'save_log_type_login');

        // ログイン後のページ操作
        $this->updateImpl($request, 'app_log', 'save_log_type_authed');

        // 一般ページ
        $this->updateImpl($request, 'app_log', 'save_log_type_page');

        // 管理画面
        $this->updateImpl($request, 'app_log', 'save_log_type_manage');

        // マイページ
        $this->updateImpl($request, 'app_log', 'save_log_type_mypage');

        // API
        $this->updateImpl($request, 'app_log', 'save_log_type_api');

        // 検索キーワード
        $this->updateImpl($request, 'app_log', 'save_log_type_search_keyword');

        // メール送信
        $this->updateImpl($request, 'app_log', 'save_log_type_sendmail');

        // パスワードページ認証
        $this->updateImpl($request, 'app_log', 'save_log_type_passwordpage');

        // ダウンロード
        $this->updateImpl($request, 'app_log', 'save_log_type_download');

        // CSS
        $this->updateImpl($request, 'app_log', 'save_log_type_css');

        // ファイル
        $this->updateImpl($request, 'app_log', 'save_log_type_file');

        // パスワード関係
        $this->updateImpl($request, 'app_log', 'save_log_type_password');

        // ユーザ登録
        $this->updateImpl($request, 'app_log', 'save_log_type_register');

        // コア側処理
        $this->updateImpl($request, 'app_log', 'save_log_type_core');

        // 言語切り替え
        $this->updateImpl($request, 'app_log', 'save_log_type_language');

        // HTTPメソッド GET
        $this->updateImpl($request, 'app_log', 'save_log_type_http_get');

        // HTTPメソッド POST
        $this->updateImpl($request, 'app_log', 'save_log_type_http_post');

        // ログ設定画面に戻る
        return redirect("/manage/log/edit");
    }

    /**
     * データダウンロード
     */
    public function downloadCsv($request)
    {
        // セッションにある検索条件なども加味した検索クエリを取得
        $query = $this->getQuery($request);

        // Symfony の StreamedResponse で出力 ＆ chunk でデータ取得することにより
        // 大容量の出力に対応
        return new StreamedResponse(
            function () use ($query) {
                $stream = fopen('php://output', 'w');

                // ヘッダの設定
                $head = [
                    'ID',
                    '日時',
                    'ログインID',
                    'IPアドレス',
                    '種別',
                    '値など',
                    'メソッド',
                    'プラグイン名',
                    'Route名',
                    'URI',
                ];
                mb_convert_variables('SJIS-win', 'UTF-8', $head);
                fputcsv($stream, $head);

                // データの処理
                $query->chunk(1000, function ($logs) use ($stream) {
                    foreach ($logs as $log) {
                        mb_convert_variables('SJIS-win', 'UTF-8', $log);
                        fputcsv($stream, [
                            $log->id,
                            $log->created_at,
                            $log->userid,
                            $log->ip_address,
                            $log->type,
                            $log->value,
                            $log->method,
                            $log->plugin_name,
                            $log->route_name,
                            $log->uri,
                        ]);
                    }
                });
                fclose($stream);
            },
            200,
            [
                'Content-Type' => 'text/csv',
                'Content-Disposition' => 'attachment; filename="log.csv"',
            ]
        );
    }
}
