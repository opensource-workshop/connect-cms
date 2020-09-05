<?php

namespace App\Plugins\Manage\ApiManage;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;

use File;
use DB;

use App\Models\Core\ApiSecret;

use App\Plugins\Manage\ManagePluginBase;

/**
 * API管理クラス
 *
 * @author 永原　篤 <nagahara@opensource-workshop.jp>
 * @copyright OpenSource-WorkShop Co.,Ltd. All Rights Reserved
 * @category プラグイン管理
 * @package Contoroller
 */
class ApiManage extends ManagePluginBase
{
    /**
     *  権限定義
     */
    public function declareRole()
    {
        // 権限チェックテーブル
        $role_ckeck_table = array();
        $role_ckeck_table["index"]  = array('admin_system');
        $role_ckeck_table["update"] = array('admin_system');
        $role_ckeck_table["delete"] = array('admin_system');
        return $role_ckeck_table;
    }

    /**
     *  ページ初期表示
     *
     * @return view
     */
    public function index($request)
    {
        // API のini ファイル
        $api_inis = array();

        // Apis データの取得
        $api_secrets = ApiSecret::orderBy('secret_name', 'asc')->orderBy('secret_code', 'asc')->get();

        // プラグインのディレクトリの取得
        $directories = File::directories(app_path().'/Plugins/Api');

        // オプション・プラグインのディレクトリの取得
        if (File::exists(app_path().'/PluginsOption/Api')) {
            $option_directories = File::directories(app_path().'/PluginsOption/Api');
            $directories = array_merge($directories, $option_directories);
        }

        // プラグインのini ファイルの取得
        foreach ($directories as $dirkey => $directorie) {
            if (File::exists($directorie."/plugin.ini")) {
                $plugin_inis = parse_ini_file($directorie."/plugin.ini", true);

                // ini ファイルで use_secret_code が true なら、API 管理する。
                if (array_key_exists('plugin_base', $plugin_inis) &&
                    array_key_exists('use_secret_code', $plugin_inis['plugin_base']) &&
                    $plugin_inis['plugin_base']['use_secret_code'] &&
                    array_key_exists('plugin_name_full', $plugin_inis['plugin_base']) &&
                    !empty($plugin_inis['plugin_base']['plugin_name_full'])) {
                    $api_inis[basename($directorie)] = $plugin_inis['plugin_base'];
                }
            }
        }

        // 管理画面プラグインの戻り値の返し方
        // view 関数の第一引数に画面ファイルのパス、第二引数に画面に渡したいデータを名前付き配列で渡し、その結果のHTML。
        return view('plugins.manage.api.api', [
            "function"    => __FUNCTION__,
            "plugin_name" => "api",
            "api_secrets" => $api_secrets,
            "api_inis"    => $api_inis,
        ]);
    }

    /**
     *  更新
     */
    public function update($request)
    {
        // httpメソッド確認
        if (!$request->isMethod('post')) {
            abort(403, '権限がありません。');
        }

        // 項目のエラーチェック
        $validator = Validator::make($request->all(), [
            'secret_name' => ['required_with:secret_code,apis,ip_address'],
            'secret_code' => ['required_with:secret_name,apis,ip_address'],
        ]);
        $validator->setAttributeNames([
            'secret_name' => '利用名',
            'secret_code' => '秘密コード',
            'ip_address'  => 'IPアドレス',
            'apis'        => '使用API',
        ]);

        // エラーがあった場合は入力画面に戻る。
        if ($validator->fails()) {
            return redirect()->back()->withErrors($validator)->withInput();
        }

        // 更新の入力があれば、更新する。
        if ($request->filled('api_secrets')) {
            foreach ($request->api_secrets as $api_secret) {
                $apis = null;
                if (array_key_exists('apis', $api_secret)) {
                    $apis = implode(',', $api_secret['apis']);
                }

                $api_secret = ApiSecret::updateOrCreate(
                    ['secret_name' => $api_secret['secret_name'], 'secret_code' => $api_secret['secret_code']],
                    ['secret_name' => $api_secret['secret_name'], 'secret_code' => $api_secret['secret_code'], 'apis' => $apis, 'ip_address' => $api_secret['ip_address']]
                );
            }
        }

        // 追加の入力があれば、登録する。
        if ($request->filled('secret_name') && $request->filled('secret_code')) {
            $apis = null;
            if ($request->filled('apis')) {
                $apis = implode(',', $request->apis);
            }

            $api_secret = ApiSecret::updateOrCreate(
                ['secret_name' => $request->secret_name, 'secret_code' => $request->secret_code],
                ['secret_name' => $request->secret_name, 'secret_code' => $request->secret_code, 'apis' => $apis, 'ip_address' => $request->ip_address]
            );
        }

        // 管理画面に戻る
        return redirect("/manage/api");
    }

    /**
     *  削除
     */
    public function delete($request, $id)
    {
        ApiSecret::destroy($id);

        // 管理画面に戻る
        return redirect("/manage/api");
    }
}
