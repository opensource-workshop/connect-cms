<?php

namespace App\Plugins\Manage\GroupManage;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;

use App\Models\Common\Group;
use App\Models\Common\GroupUser;

use App\Plugins\Manage\ManagePluginBase;

/**
 * グループ管理クラス
 *
 * @author 永原　篤 <nagahara@opensource-workshop.jp>
 * @copyright OpenSource-WorkShop Co.,Ltd. All Rights Reserved
 * @category グループ管理
 * @package Contoroller
 * @plugin_title グループ管理
 * @plugin_desc ユーザをグループとして設定できます。<br />
                このグループにページ毎の権限を付与することができます。
 */
class GroupManage extends ManagePluginBase
{
    /**
     *  権限定義
     */
    public function declareRole()
    {
        // 権限チェックテーブル
        $role_ckeck_table = array();
        $role_ckeck_table["index"]              = array('admin_user');
        $role_ckeck_table["edit"]               = array('admin_user');
        $role_ckeck_table["update"]             = array('admin_user');
        $role_ckeck_table["delete"]             = array('admin_user');
        // $role_ckeck_table["list"]               = array('admin_user');
        return $role_ckeck_table;
    }

    /**
     *  データ取得
     */
    private function getGroups()
    {
        // グループデータ取得
        $groups = Group::orderBy('id', 'asc')->paginate(10);

        return $groups;
    }

    /**
     *  データ取得
     */
    private function getGroupUsers($id)
    {
        // グループデータ取得
        $groups = GroupUser::select('group_users.*', 'users.name as user_name')
                           ->join('users', 'users.id', '=', 'group_users.user_id')
                           ->where('group_id', $id)
                           ->orderBy('user_id', 'asc')
                           ->paginate(10);

        return $groups;
    }

    /**
     *  グループ初期表示
     *
     * @return view
     * @method_title グループ一覧
     * @method_desc グループの一覧を参照できます。
     * @method_detail
     */
    public function index($request, $id)
    {
        // グループデータの取得
        $groups = $this->getGroups();

        return view('plugins.manage.group.index', [
            "function"    => __FUNCTION__,
            "plugin_name" => "group",
            "groups"      => $groups,
        ]);
    }

    /**
     *  グループ登録・変更画面表示
     *
     * @method_title グループ登録
     * @method_desc グループ名の変更及び、参加ユーザを一覧で確認できます。
     * @method_detail
     */
    public function edit($request, $id = null)
    {
        // グループデータの取得
        if (empty($id)) {
            // グループデータの空枠
            $group = new Group();
        } else {
            // グループデータの呼び出し
            $group = Group::find($id);
        }

        // グループのユーザデータの取得
        $group_users = $this->getGroupUsers($id);

        return view('plugins.manage.group.edit', [
            "function"    => __FUNCTION__,
            "plugin_name" => "group",
            "id"          => $id,
            "group"       => $group,
            "group_users" => $group_users,
        ]);
    }

    /**
     *  グループ登録・変更処理
     */
    public function update($request, $id = null)
    {
        // 項目のエラーチェック
        $validator = Validator::make($request->all(), [
            'name'     => 'required|string|max:255',
        ]);
        $validator->setAttributeNames([
            'name'     => 'グループ名',
        ]);

        // エラーがあった場合は入力画面に戻る。
        if ($validator->fails()) {
            return redirect('manage/group/edit/')
                       ->withErrors($validator)
                       ->withInput();
        }

        // 登録 or 更新
        $group = Group::updateOrCreate(
            ['id'   => $id],
            ['name' => $request->name]
        );

        // 登録・更新後は一覧画面へ
        return redirect('manage/group');
    }

    /**
     *  グループ削除処理
     */
    public function delete($request, $id)
    {
        // カテゴリ削除
        Group::find($id)->delete();

        // 削除後は一覧画面へ
        return redirect('manage/group');
    }

    // delete: どこからも呼ばれてない
    /**
     *  グループ内ユーザー表示
     *
     * @return view
     */
    // public function list($request, $id)
    // {
    //     // グループデータの取得
    //     $group_users = $this->getGroupUsers($id);

    //     return view('plugins.manage.group.list', [
    //         "function"    => __FUNCTION__,
    //         "plugin_name" => "group",
    //         "group_users" => $group_users,
    //     ]);
    // }
}
