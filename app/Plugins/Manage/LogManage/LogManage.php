<?php

namespace App\Plugins\Manage\LogManage;

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
        return $role_ckeck_table;
    }

    /**
     *  ログ表示
     *
     * @return view
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
                $query->orWhere('type', '=', 'LOGIN');
            }
            // ログアウト
            if ($request->session()->has('app_log_search_condition.log_type_logout')) {
                $query->orWhere('type', '=', 'LOGOUT');
            }
            // ログイン後のページ操作
            if ($request->session()->has('app_log_search_condition.log_type_authed')) {
                $query->orWhereNotNull('userid');
            }
            // 検索キーワード
            if ($request->session()->has('app_log_search_condition.log_type_search_keyword')) {
                $query->orWhere('type', '=', 'SEARCH');
            }
            // メール送信
            if ($request->session()->has('app_log_search_condition.log_type_sendmail')) {
                $query->orWhere('type', '=', 'SENDMAIL');
            }
            // ページ操作
            if ($request->session()->has('app_log_search_condition.log_type_page')) {
                $query->orWhere('type', '=', 'PAGE');
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

        // データ取得
        $app_logs = $app_logs_query->orderBy('id', 'desc')->paginate(10);

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
    public function update($request)
    {
        // httpメソッド確認
        if (!$request->isMethod('post')) {
            abort(403, '権限がありません。');
        }

        // 記録範囲
        $configs = Configs::updateOrCreate(
            ['name'     => 'app_log_scope'],
            ['category' => 'app_log',
             'value'    => $request->app_log_scope]
        );

        // ログイン操作
        $configs = Configs::updateOrCreate(
            ['name'     => 'save_log_type_login'],
            ['category' => 'app_log',
             'value'    => $request->save_log_type_login]
        );

        // ログイン後のページ操作
        $configs = Configs::updateOrCreate(
            ['name'     => 'save_log_type_authed'],
            ['category' => 'app_log',
             'value'    => $request->save_log_type_authed]
        );

        // 検索キーワード
        $configs = Configs::updateOrCreate(
            ['name'     => 'save_log_type_search_keyword'],
            ['category' => 'app_log',
             'value'    => $request->save_log_type_search_keyword]
        );

        // メール送信
        $configs = Configs::updateOrCreate(
            ['name'     => 'save_log_type_sendmail'],
            ['category' => 'app_log',
             'value'    => $request->save_log_type_sendmail]
        );

        // HTTPメソッド GET
        $configs = Configs::updateOrCreate(
            ['name'     => 'save_log_type_http_get'],
            ['category' => 'app_log',
             'value'    => $request->save_log_type_http_get]
        );

        // HTTPメソッド POST
        $configs = Configs::updateOrCreate(
            ['name'     => 'save_log_type_http_post'],
            ['category' => 'app_log',
             'value'    => $request->save_log_type_http_post]
        );

        // ログ設定画面に戻る
        return redirect("/manage/log/edit");
    }
}
