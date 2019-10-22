<?php

namespace App\Plugins\Manage\UserManage;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;
use DB;

use App\Models\Core\Configs;
use App\Models\Core\UsersRoles;
use App\Models\Common\Page;
use App\User;

use App\Plugins\Manage\ManagePluginBase;

/**
 * ユーザ管理クラス
 *
 * @author 永原　篤 <nagahara@opensource-workshop.jp>
 * @copyright OpenSource-WorkShop Co.,Ltd. All Rights Reserved
 * @category ページ管理
 * @package Contoroller
 */
class UserManage extends ManagePluginBase
{
    /**
     *  権限定義
     */
    public function declareRole()
    {
        // 権限チェックテーブル
        $role_ckeck_table = array();
        $role_ckeck_table["index"]   = array('admin_user');
        $role_ckeck_table["regist"]  = array('admin_user');
        $role_ckeck_table["edit"]    = array('admin_user');
        $role_ckeck_table["update"]  = array('admin_user');
        $role_ckeck_table["destroy"] = array('admin_user');
/*
        $role_ckeck_table = array();
        $role_ckeck_table["index"]   = array(config('cc_role.ROLE_SYSTEM_MANAGER'), config('cc_role.ROLE_USER_MANAGER'));
        $role_ckeck_table["regist"]  = array(config('cc_role.ROLE_SYSTEM_MANAGER'), config('cc_role.ROLE_USER_MANAGER'));
        $role_ckeck_table["edit"]    = array(config('cc_role.ROLE_SYSTEM_MANAGER'), config('cc_role.ROLE_USER_MANAGER'));
        $role_ckeck_table["update"]  = array(config('cc_role.ROLE_SYSTEM_MANAGER'), config('cc_role.ROLE_USER_MANAGER'));
        $role_ckeck_table["destroy"] = array(config('cc_role.ROLE_SYSTEM_MANAGER'), config('cc_role.ROLE_USER_MANAGER'));
*/
        return $role_ckeck_table;
    }

    /**
     *  データ取得
     */
    private function getUsers()
    {
        // ユーザデータ取得
        $users = DB::table('users')
                 ->orderBy('id', 'asc')
                 ->paginate(10);

        return $users;
    }

    /**
     *  役割取得
     */
    private function getRoles($id)
    {
        // ユーザデータ取得
        //$roles = UsersRoles::getUsersRoles($id);
        $users_roles = new UsersRoles();
        $roles = $users_roles->getUsersRoles($id);

        return $roles;
    }

    /**
     *  ページ初期表示
     *
     * @return view
     */
    public function index($request, $id)
    {
        // User データの取得
        $users = $this->getUsers();

        return view('plugins.manage.user.list',[
            "plugin_name" => "user",
            "function" => __FUNCTION__,
            "users"       => $users,
        ]);
    }

    /**
     *  ユーザ登録画面表示
     */
    public function regist($request, $id)
    {
        // ユーザデータの空枠
        $user = new User();

        return view('plugins.manage.user.regist',[
            "function" => __FUNCTION__,
            "user"     => $user,
        ]);
    }

    /**
     *  ユーザ変更画面表示
     */
    public function edit($request, $id)
    {
        // セッション初期化などのLaravel 処理。
        $request->flash();

        // ユーザデータ取得
        $user = User::where('id', $id)->first();

        // ユーザ権限取得
        $users_roles = $this->getRoles($id);

        return view('plugins.manage.user.regist',[
            "function"    => __FUNCTION__,
            "id"          => $id,
            "user"        => $user,
            "users_roles" => $users_roles,
        ]);
    }

    /**
     *  更新
     */
    public function update($request, $id = null)
    {
        // 項目のエラーチェック
        $validator = Validator::make($request->all(), [
            'name'     => 'required|string|max:255',
            'email'    => ['nullable', 'email', 'max:255', Rule::unique('users')->ignore($id)],
            'password' => 'nullable|string|min:6|confirmed',
        ]);
        $validator->setAttributeNames([
            'name'     => 'ユーザ名',
            'email'    => 'eメール',
            'password' => 'パスワード',
        ]);

        // エラーがあった場合は入力画面に戻る。
        if ($validator->fails()) {
            return redirect('manage/user/edit/' . $id)
                       ->withErrors($validator)
                       ->withInput();
        }

        // 更新内容の配列
        $update_array = array();
        $update_array = [
            'name'     => $request->name,
            'email'    => $request->email,
            'userid'   => $request->userid,
        ];

        // パスワードの入力があれば、更新
        if (!empty($request->password)) {
            $update_array['password'] = bcrypt($request->password);
        }

        // ユーザデータの更新
        User::where('id', $id)
            ->update($update_array);

        // ユーザ権限の更新（権限データの delete & insert）
        DB::table('users_roles')->where('users_id', '=', $id)->delete();

        // ユーザ権限の登録
        if (!empty($request->base)) {
            foreach($request->base as $role_name => $value) {
                UsersRoles::create([
                    'users_id'   => $id,
                    'target'     => 'base',
                    'role_name'  => $role_name,
                    'role_value' => 1
                ]);
            }
        }

        // 管理権限の登録
        if (!empty($request->manage)) {
            foreach($request->manage as $role_name => $value) {
                UsersRoles::create([
                    'users_id'   => $id,
                    'target'     => 'manage',
                    'role_name'  => $role_name,
                    'role_value' => 1
                ]);
            }
        }

        return $this->edit($request, $id);
    }

    /**
     *  削除処理
     */
    public function destroy($request, $id = null)
    {
        // id がある場合、データを削除
        if ( $id ) {

            // データを削除する。
            User::destroy($id);
        }
        // 削除後はユーザ一覧を呼ぶ。
        return redirect('manage/user');
    }
}
