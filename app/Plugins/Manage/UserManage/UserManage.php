<?php

namespace App\Plugins\Manage\UserManage;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;
use DB;

use App\Models\Core\Configs;
use App\Models\Core\UsersRoles;
use App\Models\Common\Group;
use App\Models\Common\GroupUser;
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
        $role_ckeck_table["index"]              = array('admin_user');
        $role_ckeck_table["search"]             = array('admin_user');
        $role_ckeck_table["clearSearch"]        = array('admin_user');
        $role_ckeck_table["regist"]             = array('admin_user');
        $role_ckeck_table["edit"]               = array('admin_user');
        $role_ckeck_table["update"]             = array('admin_user');
        $role_ckeck_table["destroy"]            = array('admin_user');
        $role_ckeck_table["originalRole"]       = array('admin_user');
        $role_ckeck_table["saveOriginalRoles"]  = array('admin_user');
        $role_ckeck_table["deleteOriginalRole"] = array('admin_user');
        $role_ckeck_table["groups"]             = array('admin_user');
        $role_ckeck_table["saveGroups"]         = array('admin_user');

        return $role_ckeck_table;
    }

    /**
     *  データ取得
     */
    private function getUsers($request, $page)
    {
        /* 権限が指定されている場合は、権限を保持しているユーザID を抜き出しておき、後で whereIn する。
        ----------------------------------------------------------------------------------------------*/

        $in_users = null;

        // 権限が指定されている場合
        if ($request->session()->has('user_search_condition.role_article_admin') ||
            $request->session()->has('user_search_condition.role_arrangement') ||
            $request->session()->has('user_search_condition.role_article') ||
            $request->session()->has('user_search_condition.role_approval') ||
            $request->session()->has('user_search_condition.role_reporter') ||
            $request->session()->has('user_search_condition.admin_system') ||
            $request->session()->has('user_search_condition.admin_site') ||
            $request->session()->has('user_search_condition.admin_page') ||
            $request->session()->has('user_search_condition.admin_user')) {
            $in_users_query = UsersRoles::select('users_roles.users_id');

            // コンテンツ管理者
            if ($request->session()->get('user_search_condition.role_article_admin') == 1) {
                $in_users_query->orWhere('role_name', 'role_article_admin');
            }
            // プラグイン管理者
            if ($request->session()->get('user_search_condition.role_arrangement') == 1) {
                $in_users_query->orWhere('role_name', 'role_arrangement');
            }
            // モデレータ
            if ($request->session()->get('user_search_condition.role_article') == 1) {
                $in_users_query->orWhere('role_name', 'role_article');
            }
            // 承認者
            if ($request->session()->get('user_search_condition.role_approval') == 1) {
                $in_users_query->orWhere('role_name', 'role_approval');
            }
            // 編集者
            if ($request->session()->get('user_search_condition.role_reporter') == 1) {
                $in_users_query->orWhere('role_name', 'role_reporter');
            }
            // システム管理者
            if ($request->session()->get('user_search_condition.admin_system') == 1) {
                $in_users_query->orWhere('role_name', 'admin_system');
            }
            // サイト管理者
            if ($request->session()->get('user_search_condition.admin_site') == 1) {
                $in_users_query->orWhere('role_name', 'admin_site');
                $in_users_query->orWhere('role_name', 'admin_system');
            }
            // ページ管理者
            if ($request->session()->get('user_search_condition.admin_page') == 1) {
                $in_users_query->orWhere('role_name', 'admin_page');
                $in_users_query->orWhere('role_name', 'admin_system');
            }
            // ユーザ管理者
            if ($request->session()->get('user_search_condition.admin_user') == 1) {
                $in_users_query->orWhere('role_name', 'admin_user');
                $in_users_query->orWhere('role_name', 'admin_system');
            }

            $in_users = $in_users_query->get();
        }

        // ゲスト権限が指定されている場合
        if ($request->session()->has('user_search_condition.guest')) {
            $guest_users = User::select('users.id as users_id', DB::raw('count(users_roles.role_value) AS count'))
                               ->leftJoin('users_roles', function ($join) {
                                   $join->on('users_roles.users_id', '=', 'users.id')
                                        ->whereIn('target', ['base', 'manage']);
                               })
                               ->having('count', 0)
                               ->groupBy('users.id')
                               ->get();
            // 他のユーザ絞り込みがある場合は、結果のマージ
            if (empty($in_users)) {
                $in_users = $guest_users;
            } else {
                $in_users = $in_users->concat($guest_users);
            }
        }

        /* ユーザデータ取得
        ----------------------------------------------------------------------------------------------*/

        // ユーザデータ取得
        $users_query = User::select('users.*');

        // 権限
        if ($in_users) {
            $users_query->whereIn('id', $in_users->pluck('users_id'));
        }

        // ログインID
        if ($request->session()->has('user_search_condition.userid')) {
            $users_query->where('userid', 'like', '%' . $request->session()->get('user_search_condition.userid') . '%');
        }

        // ユーザー名
        if ($request->session()->has('user_search_condition.name')) {
            $users_query->where('name', 'like', '%' . $request->session()->get('user_search_condition.name') . '%');
        }

        // eメール
        if ($request->session()->has('user_search_condition.email')) {
            $users_query->where('email', 'like', '%' . $request->session()->get('user_search_condition.email') . '%');
        }

        // 表示順
        $sort = 'created_at_asc';
        if ($request->session()->has('user_search_condition.sort')) {
            $sort = session('user_search_condition.sort');
        }
        if ($sort == 'created_at_asc') {
            $users_query->orderBy('created_at', 'asc');
        } elseif ($sort == 'created_at_desc') {
            $users_query->orderBy('created_at', 'desc');
        } elseif ($sort == 'updated_at_asc') {
            $users_query->orderBy('updated', 'asc');
        } elseif ($sort == 'updated_at_desc') {
            $users_query->orderBy('updated', 'desc');
        } elseif ($sort == 'userid_asc') {
            $users_query->orderBy('userid', 'asc');
        } elseif ($sort == 'userid_desc') {
            $users_query->orderBy('userid', 'desc');
        }

        // データ取得
        $users = $users_query->paginate(10, null, 'page', $page);

        // ユーザデータからID の配列生成
        $user_ids = array();
        foreach ($users as $user) {
            $user_ids[] = $user->id;
        }

        // ユーザ権限取得
        $roles = null;
        if ($user_ids) {
            $roles = UsersRoles::whereIn('users_id', $user_ids)
                               ->where('target', 'manage')
                               ->orWhere('target', 'base')
                               ->get();
        }

        // ユーザ権限データをユーザデータへマージ
        if ($roles) {
            $user_roles = array();
            foreach ($roles as $role) {
                $user_roles[$role->users_id][] = $role;
            }
            foreach ($users as &$user) {
                if (array_key_exists($user->id, $user_roles)) {
                    // $user->user_roles に保持すると、値が消えるので、表示用の変数を用意した。
                    $user->view_user_roles = $user_roles[$user->id];
                }
            }
        }

        // 役割取得
        $original_roles = null;
        if ($user_ids) {
            $original_roles = UsersRoles::select('users_roles.*', 'configs.name', 'configs.value')
                                        ->leftJoin('configs', function ($join) {
                                            $join->on('configs.name', '=', 'users_roles.role_name')
                                                 ->where('configs.category', '=', 'original_role');
                                        })
                                        ->whereIn('users_id', $user_ids)
                                        ->where('target', 'original_role')
                                        ->get();
        }

        // 役割をユーザデータへマージ
        if ($original_roles) {
            $user_original_roles = array();
            foreach ($original_roles as $original_role) {
                $user_original_roles[$original_role->users_id][] = $original_role;
            }
            foreach ($users as &$user) {
                if (array_key_exists($user->id, $user_original_roles)) {
                    $user->user_original_roles = $user_original_roles[$user->id];
                }
            }
        }

        //$users = DB::table('users')
        //         ->orderBy('id', 'asc')
        //         ->paginate(10);
        //Log::debug($users);

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
        /* ページの処理（セッション）
        ----------------------------------------------*/

        // 表示ページ数。詳細で更新して戻ってきたら、元と同じページを表示したい。
        // セッションにあればページの指定があれば使用。
        // ただし、リクエストでページ指定があればそれが優先。(ページング操作)
        $page = 1;
        if ($request->session()->has('user_page_condition.page')) {
            $page = $request->session()->get('user_page_condition.page');
        }
        if ($request->filled('page')) {
            $page = $request->page;
        }

        // ページがリクエストで指定されている場合は、セッションの検索条件配列のページ番号を更新しておく。
        // 詳細画面や更新処理から戻ってきた時用
        if ($request->filled('page')) {
            session(["user_page_condition.page" => $request->page]);
        }

        /* データの取得（検索）
        ----------------------------------------------*/

        // User データの取得
        $users = $this->getUsers($request, $page);

        return view('plugins.manage.user.list', [
            "function"    => __FUNCTION__,
            "plugin_name" => "user",
            "users"       => $users,
        ]);
    }

    /**
     *  検索条件設定処理
     */
    public function search($request, $id)
    {
        // 検索ボタンが押されたときはここが実行される。検索条件を設定してindex を呼ぶ。
        $user_search_condition = [
            "userid"             => $request->input('user_search_condition.userid'),
            "name"               => $request->input('user_search_condition.name'),
            "email"              => $request->input('user_search_condition.email'),

            "role_article_admin" => $request->input('user_search_condition.role_article_admin'),
            "role_arrangement"   => $request->input('user_search_condition.role_arrangement'),
            "role_article"       => $request->input('user_search_condition.role_article'),
            "role_approval"      => $request->input('user_search_condition.role_approval'),
            "role_reporter"      => $request->input('user_search_condition.role_reporter'),

            "admin_system"       => $request->input('user_search_condition.admin_system'),
            "admin_site"         => $request->input('user_search_condition.admin_site'),
            "admin_page"         => $request->input('user_search_condition.admin_page'),
            "admin_user"         => $request->input('user_search_condition.admin_user'),

            "guest"              => $request->input('user_search_condition.guest'),

            "sort"               => $request->input('user_search_condition.sort'),
        ];

        session(["user_search_condition" => $user_search_condition]);

        return redirect("/manage/user");
    }

    /**
     *  検索条件クリア処理
     */
    public function clearSearch($request, $id)
    {
        // 検索条件をクリアし、index 処理を呼ぶ。
        $request->session()->forget('user_page_condition');
        $request->session()->forget('user_search_condition');
        return $this->index($request, $id);
    }

    /**
     *  ユーザ登録画面表示
     */
    public function regist($request, $id)
    {
        // ユーザデータの空枠
        $user = new User();

        // 役割設定取得
        $original_role_configs = Configs::select('configs.*', 'users_roles.role_value')
                                        ->leftJoin('users_roles', function ($join) use ($id) {
                                            $join->on('users_roles.role_name', '=', 'configs.name')
                                                 ->where('users_roles.users_id', '=', $id)
                                                 ->where('users_roles.target', '=', 'original_role');
                                        })
                                        ->where('category', 'original_role')
                                        ->orderBy('additional1', 'asc')
                                        ->get();

        return view('plugins.manage.user.regist', [
            "function"              => __FUNCTION__,
            "plugin_name"           => "user",
            "user"                  => $user,
            "original_role_configs" => $original_role_configs,
        ]);
    }

    /**
     *  ユーザ変更画面表示
     */
    public function edit($request, $id)
    {
        // bugfix: これがあると、なぜか管理プラグインではoldが設定されない
        // セッション初期化などのLaravel 処理。
        // $request->flash();

        // ユーザデータ取得
        $user = User::where('id', $id)->first();

        // ユーザ権限取得
        $users_roles = $this->getRoles($id);

        // 役割設定取得
        $original_role_configs = Configs::select('configs.*', 'users_roles.role_value')
                                        ->leftJoin('users_roles', function ($join) use ($id) {
                                            $join->on('users_roles.role_name', '=', 'configs.name')
                                                 ->where('users_roles.users_id', '=', $id)
                                                 ->where('users_roles.target', '=', 'original_role');
                                        })
                                        ->where('category', 'original_role')
                                        ->orderBy('additional1', 'asc')
                                        ->get();

        // 画面呼び出し
        return view('plugins.manage.user.regist', [
            "function"              => __FUNCTION__,
            "plugin_name"           => "user",
            "id"                    => $id,
            "user"                  => $user,
            "users_roles"           => $users_roles,
            "original_role_configs" => $original_role_configs,
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
            'status'   => 'required',
        ]);
        $validator->setAttributeNames([
            'name'     => 'ユーザ名',
            'email'    => 'eメール',
            'password' => 'パスワード',
            'status'   => '状態',
        ]);

        // エラーがあった場合は入力画面に戻る。
        if ($validator->fails()) {
            // Log::debug(var_export($request->old(), true));
            // エラーと共に編集画面を呼び出す
            // return redirect('manage/user/edit/' . $id)->withErrors($validator)->withInput();
            return redirect()->back()->withErrors($validator)->withInput();
        }

        // 更新内容の配列
        $update_array = [
            'name'     => $request->name,
            'email'    => $request->email,
            'userid'   => $request->userid,
            'status'   => $request->status,
        ];

        // パスワードの入力があれば、更新
        if (!empty($request->password)) {
            $update_array['password'] = bcrypt($request->password);
        }

        // ユーザデータの更新
        User::where('id', $id)->update($update_array);

        // ユーザ権限の更新（権限データの delete & insert）
        DB::table('users_roles')->where('users_id', '=', $id)->delete();

        // ユーザ権限の登録
        if (!empty($request->base)) {
            foreach ($request->base as $role_name => $value) {
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
            foreach ($request->manage as $role_name => $value) {
                UsersRoles::create([
                    'users_id'   => $id,
                    'target'     => 'manage',
                    'role_name'  => $role_name,
                    'role_value' => 1
                ]);
            }
        }

        // 役割設定の登録
        if (!empty($request->original_role)) {
            foreach ($request->original_role as $original_role => $value) {
                UsersRoles::create([
                    'users_id'   => $id,
                    'target'     => 'original_role',
                    'role_name'  => $original_role,
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

        // セッション初期化などのLaravel 処理。
        $request->flash();

        // ユーザID 取得
        $user_id = Auth::user()->id;

        // 自分自身は削除できない。
        if ($user_id == User::find($id)->id) {
            $validator = Validator::make($request->all(), []);
            $validator->errors()->add('undelete', '自分は削除できません。');
            return $this->edit($request, $id)->withErrors($validator);
        }

        // id がある場合、データを削除
        if ($id) {
            // データを削除する。
            User::destroy($id);

            // 権限データを削除する。
            UsersRoles::where('users_id', $id)->delete();
        }
        // 削除後はユーザ一覧を呼ぶ。
        return redirect('manage/user');
    }

    /**
     *  役割設定画面表示
     */
    public function originalRole($request, $id, $errors = null)
    {
        // セッション初期化などのLaravel 処理。
        $request->flash();

        // 役割設定取得
        $configs = Configs::where('category', 'original_role')->orderBy('additional1', 'asc')->get();

        return view('plugins.manage.user.original_role', [
            "function"    => __FUNCTION__,
            "plugin_name" => "user",
            "id"          => $id,
            "configs"     => $configs,
            "create_flag" => true,
            "errors"      => $errors,
        ]);
    }

    /**
     *  役割設定保存処理
     */
    public function saveOriginalRoles($request, $id)
    {
        // 追加項目のどれかに値が入っていたら、行の他の項目も必須
        if (!empty($request->add_additional1) || !empty($request->add_name) || !empty($request->add_value)) {
            // 項目のエラーチェック
            $validator = Validator::make($request->all(), [
                'add_additional1' => ['required', 'numeric'],
                'add_name'        => ['required', 'alpha_num'],
                'add_value'       => ['required'],
            ]);
            $validator->setAttributeNames([
                'add_additional1' => '追加行の表示順',
                'add_name'        => '追加行の定義名',
                'add_value'       => '追加行の表示名',
            ]);

            if ($validator->fails()) {
                return $this->originalRole($request, $id, $validator->errors());
            }
        }

        // 既存項目のidに値が入っていたら、行の他の項目も必須
        if (!empty($request->configs_id)) {
            foreach ($request->configs_id as $config_id) {
                // 項目のエラーチェック
                $validator = Validator::make($request->all(), [
                    'additional1.'.$config_id => ['required', 'numeric'],
                    'name.'.$config_id        => ['required', 'alpha_num'],
                    'value.'.$config_id       => ['required'],
                ]);
                $validator->setAttributeNames([
                    'additional1.'.$config_id => '表示順',
                    'name.'.$config_id        => '定義名',
                    'value.'.$config_id       => '表示名',
                ]);

                if ($validator->fails()) {
                    return $this->originalRole($request, $id, $validator->errors());
                }
            }
        }

        // 追加項目アリ
        if (!empty($request->add_additional1)) {
            Configs::create([
                'additional1' => intval($request->add_additional1),
                'name'        => $request->add_name,
                'category'    => 'original_role',
                'value'       => $request->add_value,
            ]);
        }

        // 既存項目アリ
        if (!empty($request->configs_id)) {
            foreach ($request->configs_id as $config_id) {
                // モデルオブジェクト取得
                $configs = Configs::where('id', $config_id)->first();

                // データのセット
                $configs->name        = $request->name[$config_id];
                $configs->value       = $request->value[$config_id];
                $configs->category    = 'original_role';
                $configs->additional1 = $request->additional1[$config_id];

                // 保存
                $configs->save();
            }
        }

        return $this->originalRole($request, $id, null);
    }

    /**
     *  カテゴリ削除処理
     */
    public function deleteOriginalRole($request, $id)
    {
        // カテゴリ削除
        Configs::where('id', $id)->delete();

        return $this->originalRole($request, $id, null);
    }

    /**
     *  参加グループ編集画面
     */
    public function groups($request, $id)
    {
        // ユーザデータ取得
        $user = User::find($id);

        // グループ取得
        $group_users = Group::select('groups.*', 'group_users.user_id', 'group_users.group_role')
                            ->leftJoin('group_users', function ($join) use ($id) {
                                $join->on('groups.id', '=', 'group_users.group_id')
                                     ->where('group_users.user_id', '=', $id)
                                     ->whereNull('group_users.deleted_at');
                            })
                            ->orderBy('groups.name', 'asc')
                            ->paginate(10);

        // 画面呼び出し
        return view('plugins.manage.user.groups', [
            "function"              => __FUNCTION__,
            "plugin_name"           => "user",
            "user"                  => $user,
            "group_users"           => $group_users,
        ]);
    }

    /**
     *  参加グループ保存処理
     */
    public function saveGroups($request, $id)
    {
        // 画面項目のチェック
        if ($request->has('group_roles')) {
            foreach ($request->group_roles as $group_id => $group_role) {
                // 権限の解除
                if (empty($group_role)) {
                    GroupUser::where('group_id', $group_id)->where('user_id', $id)->delete();
                } else {
                    // 登録 or 更新
                    $group_user = GroupUser::updateOrCreate(
                        ['group_id'   => $group_id, 'user_id' => $id],
                        ['group_id'   => $group_id,
                         'user_id'    => $id,
                         'group_role' => $group_role,
                         'deleted_id' => null,
                         'deleted_name' => null,
                         'deleted_at' => null]
                    );
                }
            }
        }

        // 削除後は一覧画面へ
        return redirect('manage/user/groups/' . $id);
    }
}
