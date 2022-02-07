<?php

namespace App\Plugins\Manage\AuthManage;

use Illuminate\Support\Facades\Validator;

use App\Models\Core\Configs;

use App\Plugins\Manage\ManagePluginBase;

use App\Enums\AuthMethodType;

/**
 * 外部認証クラス
 *
 * @author 牟田口 満 <mutaguchi@opensource-workshop.jp>
 * @copyright OpenSource-WorkShop Co.,Ltd. All Rights Reserved
 * @category 外部認証
 * @package Controller
 * @plugin_title 外部認証
 * @plugin_desc 他システムと連携した外部認証に関する設定が集まった管理機能です。
 */
class AuthManage extends ManagePluginBase
{
    /**
     * 権限定義
     */
    public function declareRole()
    {
        // 権限チェックテーブル
        $role_check_table = array();
        $role_check_table["index"] = array('admin_site');
        $role_check_table["update"] = array('admin_site');
        $role_check_table["netcommons2"] = array('admin_site');
        $role_check_table["netcommons2Update"] = array('admin_site');
        $role_check_table["ldap"] = array('admin_site');
        $role_check_table["ldapUpdate"] = array('admin_site');
        $role_check_table["shibboleth"] = array('admin_site');

        return $role_check_table;
    }

    /**
     * 初期表示
     *
     * @return view
     *
     * @method_title 認証設定
     * @method_desc 認証設定の基本設定を確認できます。
     * @method_detail 外部認証を使用するか、しないか。どの外部設定を使用するかなどの基本の設定を行えます。
     */
    public function index($request)
    {
        // 外部認証を使用
        $configs_use_auth_method = Configs::where('name', 'use_auth_method')->first();
        $use_auth_method = empty($configs_use_auth_method) ? null : $configs_use_auth_method->value;

        // 使用する外部認証
        $configs_auth_method_event = Configs::where('name', 'auth_method_event')->first();
        $auth_method_event = empty($configs_auth_method_event) ? null : $configs_auth_method_event->value;

        // 通常ログインも使用
        $configs_use_normal_login_along_with_auth_method = Configs::where('name', 'use_normal_login_along_with_auth_method')->first();
        $use_normal_login_along_with_auth_method = empty($configs_use_normal_login_along_with_auth_method) ? null : $configs_use_normal_login_along_with_auth_method->value;

        // 管理画面プラグインの戻り値の返し方
        // view 関数の第一引数に画面ファイルのパス、第二引数に画面に渡したいデータを名前付き配列で渡し、その結果のHTML。
        return view('plugins.manage.auth.auth', [
            "function" => __FUNCTION__,
            "plugin_name" => "auth",
            "use_auth_method" => $use_auth_method,
            "auth_method_event" => $auth_method_event,
            "use_normal_login_along_with_auth_method" => $use_normal_login_along_with_auth_method,
        ]);
    }

    /**
     * コード更新処理
     */
    public function update($request, $id)
    {
        // 項目のエラーチェック
        $validator = Validator::make($request->all(), [
            'confirm_auth' => ['required'],
        ]);
        $validator->setAttributeNames([
            'confirm_auth' => '通常ログインに対する注意点',
        ]);

        if ($validator->fails()) {
            return redirect('manage/auth/')
                       ->withErrors($validator)
                       ->withInput();
        }

        // --- 更新
        // 外部認証を使用
        $configs = Configs::updateOrCreate(
            ['name' => 'use_auth_method'],
            [
                'category' => 'auth',
                'value' => $request->use_auth_method
            ]
        );

        // 使用する外部認証
        $configs = Configs::updateOrCreate(
            ['name' => 'auth_method_event'],
            [
                'category' => 'auth',
                'value' => $request->auth_method_event
            ]
        );

        // 通常ログインも使用
        $configs = Configs::updateOrCreate(
            ['name' => 'use_normal_login_along_with_auth_method'],
            [
                'category' => 'auth',
                'value' => $request->use_normal_login_along_with_auth_method
            ]
        );

        // 画面に戻る
        return redirect("/manage/auth")->with('flash_message', '更新しました。');
    }

    /**
     * NetCommons2認証表示
     *
     * @return view
     * @method_title NetCommons2認証
     * @method_desc NetCommons2を使った認証を設定できます。
     * @method_detail NetCommons2認証を使用するには、NetCommons2側にも、Connect-CMS認証モジュールの導入が必要です。
     */
    public function netcommons2($request)
    {
        // Config データの取得
        $config = Configs::where('name', 'auth_method')->where('value', AuthMethodType::netcommons2)->first();

        // 管理画面プラグインの戻り値の返し方
        // view 関数の第一引数に画面ファイルのパス、第二引数に画面に渡したいデータを名前付き配列で渡し、その結果のHTML。
        return view('plugins.manage.auth.netcommons2', [
            "function" => __FUNCTION__,
            "plugin_name" => "auth",
            "config" => $config,
        ]);
    }

    /**
     * NetCommons2認証設定の保存
     */
    public function netcommons2Update($request, $id = null)
    {
        // httpメソッド確認
        if (!$request->isMethod('post')) {
            abort(403, '権限がありません。');
        }

        // 設定内容の保存
        $configs = Configs::updateOrCreate(
            [
                'name' => 'auth_method',
                'value' => AuthMethodType::netcommons2,
            ],
            [
                'category' => 'auth',
                'additional1' => $request->auth_netcomons2_site_url,
                'additional2' => $request->auth_netcomons2_site_key,
                'additional3' => $request->auth_netcomons2_salt,
                'additional4' => $request->auth_netcomons2_add_role,
                'additional5' => $request->auth_netcomons2_admin_password
            ]
        );

        // システム管理画面に戻る
        return redirect("/manage/auth/netcommons2")->with('flash_message', '更新しました。');
    }

    /**
     * LDAP認証表示
     *
     * @return view
     * @method_title LDAP認証
     * @method_desc LDAPを使った認証を設定できます。
     * @method_detail LDAP認証に必要な項目を設定します。
     */
    public function ldap($request)
    {
        // Config データの取得
        $config = Configs::firstOrNew(['name' => 'auth_method', 'value' => AuthMethodType::ldap]);

        return view('plugins.manage.auth.ldap', [
            "function" => __FUNCTION__,
            "plugin_name" => "auth",
            "config" => $config,
        ]);
    }

    /**
     * LDAP認証設定の保存
     */
    public function ldapUpdate($request, $id = null)
    {
        // httpメソッド確認
        if (!$request->isMethod('post')) {
            abort(403, '権限がありません。');
        }

        // 設定内容の保存
        $configs = Configs::updateOrCreate(
            [
                'name' => 'auth_method',
                'value' => AuthMethodType::ldap,
            ],
            [
                'category' => 'auth',
                'additional1' => $request->auth_ldap_uri,
                'additional2' => $request->auth_ldap_dn_type,
                'additional3' => $request->auth_ldap_dn,
            ]
        );

        // システム管理画面に戻る
        return redirect("/manage/auth/ldap")->with('flash_message', '更新しました。');
    }

    /**
     * Shibboleth認証表示
     *
     * @return view
     * @method_title shibboleth認証
     * @method_desc shibbolethを使った認証を設定できます。
     * @method_detail shibboleth認証に必要な項目が表示されます。shibboleth認証の設定自体はファイルで設定します。
     */
    public function shibboleth($request)
    {
        return view('plugins.manage.auth.shibboleth', [
            "function" => __FUNCTION__,
            "plugin_name" => "auth",
        ]);
    }
}
