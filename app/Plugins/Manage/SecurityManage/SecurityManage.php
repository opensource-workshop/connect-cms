<?php

namespace App\Plugins\Manage\SecurityManage;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;

use DB;

use App\Models\Core\Configs;
use App\Models\Core\ConfigsLoginPermits;

use App\Plugins\Manage\ManagePluginBase;

/**
 * セキュリティ管理クラス
 *
 * @author 永原　篤 <nagahara@opensource-workshop.jp>
 * @copyright OpenSource-WorkShop Co.,Ltd. All Rights Reserved
 * @category セキュリティ管理
 * @package Contoroller
 */
class SecurityManage extends ManagePluginBase
{
    /**
     *  権限定義
     */
    public function declareRole()
    {
        // 権限チェックテーブル
        $role_ckeck_table = array();
        $role_ckeck_table["index"]             = array('admin_site');
        //$role_ckeck_table["loginPermit"]       = array('admin_site');
        $role_ckeck_table["saveLoginPermit"]   = array('admin_site');
        $role_ckeck_table["deleteLoginPermit"] = array('admin_site');
        $role_ckeck_table["purifier"]          = array('admin_site');
        $role_ckeck_table["savePurifier"]      = array('admin_site');

        return $role_ckeck_table;
    }

    /**
     *  ログイン権限表示画面
     */
    public function index($request, $id, $errors = null)
    {
        // セッション初期化などのLaravel 処理。
        $request->flash();

        // Config データからログイン拒否設定の取得
        $configs_login_reject = Configs::where('name', 'login_reject')->first();

        // ログイン権限の取得
        $login_permits = ConfigsLoginPermits::orderBy('apply_sequence', 'asc')
                         ->orderBy('apply_sequence', 'asc')
                         ->get();

        return view('plugins.manage.security.loginpermit', [
            "function"             => __FUNCTION__,
            "plugin_name"          => "security",
            "login_permits"        => $login_permits,
            "configs_login_reject" => $configs_login_reject,
            "errors"               => $errors,
            "create_flag"          => true,
        ]);
    }

    /**
     *  ログイン権限保存処理
     */
    public function saveLoginPermit($request, $id, $errors = null)
    {

        // Config データのログイン拒否設定
        $configs = Configs::updateOrCreate(
            ['name'     => 'login_reject'],
            ['category' => 'login',
             'value'    => $request->login_reject]
        );

        // 追加項目のどれかに値が入っていたら、行の他の項目も必須
        if (!empty($request->add_apply_sequence) || !empty($request->add_ip_address) || !empty($request->add_reject)) {
            // 項目のエラーチェック
            $validator = Validator::make($request->all(), [
                'add_apply_sequence'   => ['required'],
                'add_ip_address'       => ['required'],
                'add_reject'           => ['required'],
            ]);
            $validator->setAttributeNames([
                'add_apply_sequence'   => '追加行の適用順',
                'add_ip_address'       => '追加行のIPアドレス',
                'add_reject'           => '追加行の許可設定',
            ]);

            if ($validator->fails()) {
                return $this->index($request, $id, $validator->errors());
            }
        }

        // 既存項目のidに値が入っていたら、行の他の項目も必須
        if (!empty($request->login_permits_id)) {
            foreach ($request->login_permits_id as $login_permit_id) {
                // 項目のエラーチェック
                $validator = Validator::make($request->all(), [
                    'apply_sequence.'.$login_permit_id   => ['required'],
                    'ip_address.'.$login_permit_id       => ['required'],
                    'reject.'.$login_permit_id           => ['required'],
                ]);
                $validator->setAttributeNames([
                    'apply_sequence.'.$login_permit_id   => '適用順',
                    'ip_address.'.$login_permit_id       => 'IPアドレス',
                    'reject.'.$login_permit_id           => '許可設定',
                ]);

                if ($validator->fails()) {
                    return $this->index($request, $id, $validator->errors());
                }
            }
        }

        // 追加項目アリ
        if (!empty($request->add_apply_sequence)) {
            ConfigsLoginPermits::create([
                                 'apply_sequence' => intval($request->add_apply_sequence),
                                 'ip_address'     => $request->add_ip_address,
                                 'role'           => $request->add_role,
                                 'reject'         => $request->add_reject,
            ]);
        }

        // 既存項目アリ
        if (!empty($request->login_permits_id)) {
            foreach ($request->login_permits_id as $login_permit_id) {
                // モデルオブジェクト取得
                $login_permits = ConfigsLoginPermits::where('id', $login_permit_id)->first();

                // データのセット
                $login_permits->apply_sequence = $request->apply_sequence[$login_permit_id];
                $login_permits->ip_address     = $request->ip_address[$login_permit_id];
                $login_permits->role           = $request->role[$login_permit_id];
                $login_permits->reject         = $request->reject[$login_permit_id];

                // 保存
                $login_permits->save();
            }
        }

        return redirect("/manage/security");
    }

    /**
     *  ログイン権限削除関数
     */
    public function deleteLoginPermit($request, $id)
    {
        ConfigsLoginPermits::where('id', '=', $id)->delete();

        // ページ管理画面に戻る
        return redirect("/manage/security");
    }

    /**
     *  HTML記述制限
     */
    public function purifier($request, $id)
    {
        // 設定されている権限
        $purifiers = config('cc_role.CC_HTMLPurifier_ROLE_LIST');

        // Config テーブルからHTML記述制限の取得
        // Config テーブルにデータがあれば、配列を上書きする。
        // 初期状態ではConfig テーブルはなく、cc_role.CC_HTMLPurifier_ROLE_LIST を初期値とするため。
        $config_purifiers = Configs::where('category', 'html_purifier')->get();
        foreach ($config_purifiers as $config_purifier) {
            if (array_key_exists($config_purifier->name, $purifiers)) {
                $purifiers[$config_purifier->name] = $config_purifier->value;
            }
        }

        // ページ管理画面に戻る
        return view('plugins.manage.security.purifier', [
            "function"    => __FUNCTION__,
            "plugin_name" => "security",
            "purifiers"   => $purifiers,
        ]);
    }

    /**
     *  HTML記述制限の保存
     */
    public function savePurifier($request, $id)
    {
        // 項目のエラーチェック
        $validator = Validator::make($request->all(), [
            'confirm_purifier'   => ['required'],
        ]);
        $validator->setAttributeNames([
            'confirm_purifier'   => 'XSSに対する注意点',
        ]);

        if ($validator->fails()) {
            return redirect('manage/security/purifier/')
                       ->withErrors($validator)
                       ->withInput();
        }

        // デフォルト権限
        $purifiers = config('cc_role.CC_HTMLPurifier_ROLE_LIST');

        // 設定保存
        $this->savePurifierCategory($request, 'role_article_admin');
        $this->savePurifierCategory($request, 'role_arrangement');
        $this->savePurifierCategory($request, 'role_article');
        $this->savePurifierCategory($request, 'role_approval');
        $this->savePurifierCategory($request, 'role_reporter');
        $this->savePurifierCategory($request, 'role_guest');

        return redirect("/manage/security/purifier");
    }

    /**
     *  HTML記述制限の保存
     */
    private function savePurifierCategory($request, $role_name)
    {
        // デフォルト権限
        $purifiers = config('cc_role.CC_HTMLPurifier_ROLE_LIST');

        // デフォルトと異なる設定が送られてきたら保存
        if ($request->has($role_name)) {
            $configs = Configs::updateOrCreate(
                ['name'     => $role_name],
                ['category' => 'html_purifier',
                 'value'    => $request->$role_name]
            );
        }
        return;
    }
}
