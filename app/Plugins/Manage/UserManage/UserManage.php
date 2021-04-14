<?php

namespace App\Plugins\Manage\UserManage;

// use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
// use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
// use Illuminate\Validation\Rule;
use DB;

use App\Models\Core\Configs;
use App\Models\Core\UsersRoles;
use App\Models\Core\UsersInputCols;
use App\Models\Common\Group;
use App\Models\Common\GroupUser;
// use App\Models\Common\Page;
use App\User;

use App\Plugins\Manage\ManagePluginBase;

use App\Rules\CustomValiUserEmailUnique;
use App\Rules\CustomValiEmails;

use App\Utilities\Csv\CsvUtils;

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
        $role_ckeck_table["autoRegist"]         = array('admin_user');
        $role_ckeck_table["autoRegistUpdate"]   = array('admin_user');
        $role_ckeck_table["downloadCsv"] = array('admin_user');

        return $role_ckeck_table;
    }

    /**
     * データgetで取得
     */
    private function getUsers($request, $users_columns)
    {
        return $this->getUsersPaginate($request, null, $users_columns, false);
    }

    /**
     * データ取得(paginate or get)
     */
    private function getUsersPaginate($request, $page, $users_columns, $is_paginate = true)
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

            // 権限複数チェックするとOR検索
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

        // ユーザー追加項目のソートカラム
        $sort_column_id = null;

        // ユーザー追加項目のソート順
        $sort_column_orders = [];
        foreach ($users_columns as $users_column) {
            // ソート順
            $sort_column_orders[$users_column->id . '_asc'] = 'asc';
            $sort_column_orders[$users_column->id . '_desc'] = 'desc';
        }

        if ($request->session()->has('user_search_condition.sort')) {
            // ソートあり
            $sort = session('user_search_condition.sort');

            if (array_key_exists($sort, $sort_column_orders)) {
                $sort_flag = explode('_', $sort);
                if (count($sort_flag) == 2) {
                    // ユーザー追加項目のソートカラム取得
                    $sort_column_id = $sort_flag[0];
                    //$sort_column_order = $sort_flag[1];
                }
            }
        }

        // ユーザデータ取得
        // $users_query = User::select('users.*');
        // ユーザー追加項目のソートなし
        if (empty($sort_column_id)) {
            $users_query = User::select('users.*');
        } else {
            // ユーザー追加項目のソートあり
            $users_query = User::select('users.*', 'users_input_cols.value')
                ->leftjoin('users_input_cols', function ($join) use ($sort_column_id) {
                    $join->on('users_input_cols.users_id', '=', 'users.id')
                        ->where('users_input_cols.users_columns_id', '=', $sort_column_id);
                });
        }

        // 権限
        if ($in_users) {
            $users_query->whereIn('users.id', $in_users->pluck('users_id'));
        }

        // ログインID
        if ($request->session()->has('user_search_condition.userid')) {
            $users_query->where('users.userid', 'like', '%' . $request->session()->get('user_search_condition.userid') . '%');
        }

        // ユーザー名
        if ($request->session()->has('user_search_condition.name')) {
            $users_query->where('users.name', 'like', '%' . $request->session()->get('user_search_condition.name') . '%');
        }

        // グループ
        if ($request->session()->has('user_search_condition.groups')) {
            // グループ複数チェックするとOR検索
            $groups = $request->session()->get('user_search_condition.groups');
            $in_group_users_query = GroupUser::select('group_users.user_id');
            foreach ($groups as $group) {
                $in_group_users = $in_group_users_query->orWhere('group_id', $group);
            }
            $users_query->whereIn('users.id', $in_group_users->pluck('user_id'));
        }

        // eメール
        if ($request->session()->has('user_search_condition.email')) {
            $users_query->where('users.email', 'like', '%' . $request->session()->get('user_search_condition.email') . '%');
        }

        // 状態
        if ($request->session()->has('user_search_condition.status')) {
            $users_query->where('users.status', $request->session()->get('user_search_condition.status'));
        }

        foreach ($users_columns as $users_column) {
            if ($request->session()->has('user_search_condition.users_columns_value.'. $users_column->id)) {
                // [TODO] 追加項目でチェックボックスを複数チェック入れるとAND検索。OR検索に今後見直す。既にデータベースで対応しているようだ。
                $search_keyword = $request->session()->get('user_search_condition.users_columns_value.'. $users_column->id);

                // $users_query->whereIn('users_inputs.id', function ($query) use ($search_keyword, $users_columns_id, $hide_columns_ids) {
                $users_query->whereIn('users.id', function ($query) use ($search_keyword, $users_column) {
                    // 縦持ちのvalue を検索して、行の id を取得。
                    $query->select('users_id')
                            ->from('users_input_cols')
                            ->join('users_columns', 'users_columns.id', '=', 'users_input_cols.users_columns_id')
                            ->where('users_columns.id', $users_column->id)
                            //->whereNotIn('users_columns.id', $hide_columns_ids)
                            ->where('value', 'like', '%' . $search_keyword . '%')
                            ->groupBy('users_id');
                });
            }
        }

        // 表示順
        $sort = 'created_at_asc';
        if ($request->session()->has('user_search_condition.sort')) {
            $sort = session('user_search_condition.sort');
        }
        if ($sort == 'created_at_asc') {
            $users_query->orderBy('users.created_at', 'asc');
        } elseif ($sort == 'created_at_desc') {
            $users_query->orderBy('users.created_at', 'desc');
        } elseif ($sort == 'updated_at_asc') {
            $users_query->orderBy('users.updated', 'asc');
        } elseif ($sort == 'updated_at_desc') {
            $users_query->orderBy('users.updated', 'desc');
        } elseif ($sort == 'userid_asc') {
            $users_query->orderBy('users.userid', 'asc');
        } elseif ($sort == 'userid_desc') {
            $users_query->orderBy('users.userid', 'desc');
        } elseif (array_key_exists($sort, $sort_column_orders)) {
            // ユーザー追加項目のソートあり
            $users_query->orderBy('users_input_cols.value', $sort_column_orders[$sort]);
        }
        // dd($sort_column_orders);

        // データ取得
        if ($is_paginate) {
            // ページャーで取得
            $users = $users_query->paginate(10, null, 'page', $page);
        } else {
            // getで取得
            $users = $users_query->get();
        }

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

        // グループ取得
        $group_users = null;
        if ($user_ids) {
            // グループ取得
            $group_users = Group::select('groups.*', 'group_users.user_id', 'group_users.group_role')
                                ->leftJoin('group_users', function ($join) {
                                    $join->on('groups.id', '=', 'group_users.group_id')
                                        ->whereNull('group_users.deleted_at');
                                })
                                ->whereIn('group_users.user_id', $user_ids)
                                ->orderBy('group_users.user_id', 'asc')
                                ->orderBy('groups.name', 'asc')
                                ->get();
        }

        if ($group_users) {
            foreach ($users as &$user) {
                $user->group_users = $group_users->where('user_id', $user->id);
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

        // ユーザーのカラム
        $users_columns = UsersTool::getUsersColumns();
        // カラムの選択肢
        $users_columns_id_select = UsersTool::getUsersColumnsSelects();

        // User データの取得
        $users = $this->getUsersPaginate($request, $page, $users_columns);

        // ユーザーの追加項目データ
        $input_cols = UsersTool::getUsersInputCols($users->pluck('id')->all());

        // get()で取得すると、ソフトデリート（deleted_at）は取得されない
        $groups_select = Group::get();
        // dd($groups);

        return view('plugins.manage.user.list', [
            "function" => __FUNCTION__,
            "plugin_name" => "user",
            "users" => $users,
            "users_columns" => $users_columns,
            "users_columns_id_select" => $users_columns_id_select,
            "input_cols" => $input_cols,
            "groups_select" => $groups_select,
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
            "groups"             => $request->input('user_search_condition.groups'),
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

            "status"             => $request->input('user_search_condition.status'),

            "sort"               => $request->input('user_search_condition.sort'),
        ];

        //// ユーザーの追加項目.
        // ユーザーのカラム
        $users_columns = UsersTool::getUsersColumns();

        foreach ($users_columns as $users_column) {
            $value = "";
            if (!isset($request->users_columns_value[$users_column->id])) {
                // 値なし
                $value = null;
            } elseif (is_array($request->users_columns_value[$users_column->id])) {
                $value = implode(UsersTool::CHECKBOX_SEPARATOR, $request->users_columns_value[$users_column->id]);
            } else {
                $value = $request->users_columns_value[$users_column->id];
            }
            $user_search_condition['users_columns_value'][$users_column->id] = $value;
        }

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

        //// ユーザの追加項目.
        // ユーザーのカラム
        $users_columns = UsersTool::getUsersColumns();
        // カラムの選択肢
        $users_columns_id_select = UsersTool::getUsersColumnsSelects();
        // dd($users_columns, $users_columns_id_select);
        // カラムの登録データ
        $input_cols = null;

        return view('plugins.manage.user.regist', [
            "function" => __FUNCTION__,
            "plugin_name" => "user",
            "user" => $user,
            "original_role_configs" => $original_role_configs,
            'users_columns' => $users_columns,
            'users_columns_id_select' => $users_columns_id_select,
            'input_cols' => $input_cols,
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

        // ユーザーのカラム
        $users_columns = UsersTool::getUsersColumns();
        // カラムの選択肢
        $users_columns_id_select = UsersTool::getUsersColumnsSelects();
        // dd($users_columns, $users_columns_id_select);
        // カラムの登録データ
        $input_cols = UsersTool::getUsersInputCols([$id]);

        // 画面呼び出し
        return view('plugins.manage.user.regist', [
            "function" => __FUNCTION__,
            "plugin_name" => "user",
            "id" => $id,
            "user" => $user,
            "users_roles" => $users_roles,
            "original_role_configs" => $original_role_configs,
            'users_columns' => $users_columns,
            'users_columns_id_select' => $users_columns_id_select,
            'input_cols' => $input_cols,
        ]);
    }

    /**
     * 更新
     */
    public function update($request, $id = null)
    {
        // 項目のエラーチェック
        // change: ユーザーの追加項目に対応
        // $validator = Validator::make($request->all(), [
        //     'name'     => 'required|string|max:255',
        //     'email'    => ['nullable', 'email', 'max:255', Rule::unique('users')->ignore($id)],
        //     'password' => 'nullable|string|min:6|confirmed',
        //     'status'   => 'required',
        // ]);
        // $validator->setAttributeNames([
        //     'name'     => 'ユーザ名',
        //     'email'    => 'eメール',
        //     'password' => 'パスワード',
        //     'status'   => '状態',
        // ]);
        $validator_array = [
            'column' => [
                'name' => 'required|string|max:255',
                'email' => ['nullable', 'email', 'max:255', new CustomValiUserEmailUnique($id)],
                'password' => 'nullable|string|min:6|confirmed',
                'status' => 'required',
            ],
            'message' => [
                'name' => 'ユーザ名',
                'email' => 'eメール',
                'password' => 'パスワード',
                'status' => '状態',
            ]
        ];

        // ユーザーのカラム
        $users_columns = UsersTool::getUsersColumns();

        foreach ($users_columns as $users_column) {
            // バリデータールールをセット
            $validator_array = UsersTool::getValidatorRule($validator_array, $users_column, $id);
        }

        // 項目のエラーチェック
        $validator = Validator::make($request->all(), $validator_array['column']);
        $validator->setAttributeNames($validator_array['message']);
        // Log::debug(var_export($request->all(), true));
        // Log::debug(var_export($validator_array, true));

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
            // change to laravel6.
            // $update_array['password'] = bcrypt($request->password);
            $update_array['password'] = Hash::make($request->password);
        }

        // ユーザデータの更新
        User::where('id', $id)->update($update_array);

        // ユーザーの追加項目.
        // id（行 id）が渡ってきたら、詳細データは一度消す。その後、登録と同じ処理にする。
        // delete -> insertのため、権限非表示カラムは消さずに残す。
        UsersInputCols::where('users_id', $id)
                            // ->whereNotIn('users_columns_id', $hide_columns_ids)
                            ->delete();

        // users_input_cols 登録
        foreach ($users_columns as $users_column) {
            $value = "";
            if (!isset($request->users_columns_value[$users_column->id])) {
                // 値なし
                $value = null;
            } elseif (is_array($request->users_columns_value[$users_column->id])) {
                $value = implode(UsersTool::CHECKBOX_SEPARATOR, $request->users_columns_value[$users_column->id]);
            } else {
                $value = $request->users_columns_value[$users_column->id];
            }

            // データ登録フラグを見て登録
            $users_input_cols = new UsersInputCols();
            $users_input_cols->users_id = $id;
            $users_input_cols->users_columns_id = $users_column->id;
            $users_input_cols->value = $value;
            $users_input_cols->save();
        }

        // ユーザ権限の更新（権限データの delete & insert）
        UsersRoles::where('users_id', '=', $id)->delete();

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
            "errors"      => $errors,
        ]);
    }

    /**
     *  役割設定保存処理
     */
    public function saveOriginalRoles($request, $id)
    {
        /* エラーチェック
        ------------------------------------ */
        $rules = [];

        // エラーチェックの項目名
        $setAttributeNames = [];

        // 追加項目のどれかに値が入っていたら、行の他の項目も必須
        if (!empty($request->add_additional1) || !empty($request->add_name) || !empty($request->add_value)) {
            // 項目のエラーチェック
            $rules['add_additional1'] = ['required', 'numeric'];
            $rules['add_name'] = ['required', 'alpha_num'];
            $rules['add_value'] = ['required'];

            $setAttributeNames['add_additional1'] = '追加行の表示順';
            $setAttributeNames['add_name'] = '追加行の定義名';
            $setAttributeNames['add_value'] = '追加行の表示名';
        }

        // 既存項目のidに値が入っていたら、行の他の項目も必須
        if (!empty($request->configs_id)) {
            foreach ($request->configs_id as $config_id) {
                // 項目のエラーチェック
                $rules['additional1.'.$config_id] = ['required', 'numeric'];
                $rules['name.'.$config_id] = ['required', 'alpha_num'];
                $rules['value.'.$config_id] = ['required'];

                $setAttributeNames['additional1.'.$config_id] = '表示順';
                $setAttributeNames['name.'.$config_id] = '定義名';
                $setAttributeNames['value.'.$config_id] = '表示名';
            }
        }

        // 項目のエラーチェック
        $validator = Validator::make($request->all(), $rules);
        $validator->setAttributeNames($setAttributeNames);

        if ($validator->fails()) {
            return $this->originalRole($request, $id, $validator->errors());
            // return redirect()->back()->withErrors($validator)->withInput();
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

    /**
     * 自動ユーザ登録設定 画面表示
     */
    public function autoRegist($request, $id)
    {
        // Config データの取得
        $configs = Configs::where('category', 'user_register')->get();

        return view('plugins.manage.user.auto_regist', [
            "function" => __FUNCTION__,
            "plugin_name" => "user",
            "configs" => $configs,
        ]);
    }

    /**
     * 自動ユーザ登録設定 更新
     */
    public function autoRegistUpdate($request, $page_id = null)
    {
        // httpメソッド確認
        if (!$request->isMethod('post')) {
            abort(403, '権限がありません。');
        }

        $validator_values['user_register_mail_send_address'] = ['nullable', new CustomValiEmails()];
        $validator_attributes['user_register_mail_send_address'] = '送信するメールアドレス';

        // 「以下のアドレスにメール送信する」がONの場合、送信するメールアドレスは必須
        if ($request->user_register_mail_send_flag) {
            $validator_values['user_register_mail_send_address'] = ['required', new CustomValiEmails()];
        }

        $validator_attributes['user_register_user_mail_send_flag'] = '登録者にメール送信する';
        $validator_attributes['user_register_temporary_regist_mail_format'] = '仮登録メールフォーマット';

        $messages = [
            'user_register_user_mail_send_flag.accepted' => '仮登録メールを送信する場合、:attribute にチェックを付けてください。',
            'user_register_temporary_regist_mail_format.regex' => '仮登録メールを送信する場合、:attribute に[[entry_url]]を含めてください。',
        ];

        // 項目のエラーチェック
        $validator = Validator::make($request->all(), $validator_values, $messages);
        $validator->setAttributeNames($validator_attributes);

        $validator->sometimes("user_register_user_mail_send_flag", 'accepted', function ($input) {
            // 仮登録メールがONなら、上記の 登録者にメール送信する ONであること
            return $input->user_register_temporary_regist_mail_flag;
        });
        $validator->sometimes("user_register_temporary_regist_mail_format", 'regex:/\[\[entry_url\]\]/', function ($input) {
            // 仮登録メールがONなら、上記の 登録者にメール送信する ONであること
            return $input->user_register_temporary_regist_mail_flag;
        });

        if ($validator->fails()) {
            // Log::debug(var_export($validator->errors(), true));
            // エラーと共に編集画面を呼び出す
            return redirect()->back()->withErrors($validator)->withInput();
        }

        // 自動ユーザ登録の使用
        $configs = Configs::updateOrCreate(
            ['name' => 'user_register_enable'],
            [
                'category' => 'user_register',
                'value' => $request->user_register_enable
            ]
        );

        // 以下のアドレスにメール送信する
        $configs = Configs::updateOrCreate(
            ['name' => 'user_register_mail_send_flag'],
            [
                'category' => 'user_register',
                'value' => $request->user_register_mail_send_flag ?? 0
            ]
        );

        // 送信するメールアドレス
        $configs = Configs::updateOrCreate(
            ['name' => 'user_register_mail_send_address'],
            [
                'category' => 'user_register',
                'value' => $request->user_register_mail_send_address
            ]
        );

        // 登録者にメール送信する
        $configs = Configs::updateOrCreate(
            ['name' => 'user_register_user_mail_send_flag'],
            [
                'category' => 'user_register',
                'value' => $request->user_register_user_mail_send_flag ?? 0
            ]
        );

        // 登録者に仮登録メールを送信する
        $configs = Configs::updateOrCreate(
            ['name' => 'user_register_temporary_regist_mail_flag'],
            [
                'category' => 'user_register',
                'value' => $request->user_register_temporary_regist_mail_flag ?? 0
            ]
        );

        // 仮登録メール件名
        $configs = Configs::updateOrCreate(
            ['name' => 'user_register_temporary_regist_mail_subject'],
            [
                'category' => 'user_register',
                'value' => $request->user_register_temporary_regist_mail_subject
            ]
        );

        // 仮登録メールフォーマット
        $configs = Configs::updateOrCreate(
            ['name' => 'user_register_temporary_regist_mail_format'],
            [
                'category' => 'user_register',
                'value' => $request->user_register_temporary_regist_mail_format
            ]
        );

        // 仮登録後のメッセージ
        $configs = Configs::updateOrCreate(
            ['name' => 'user_register_temporary_regist_after_message'],
            [
                'category' => 'user_register',
                'value' => $request->user_register_temporary_regist_after_message
            ]
        );

        // 本登録メール件名
        $configs = Configs::updateOrCreate(
            ['name' => 'user_register_mail_subject'],
            [
                'category' => 'user_register',
                'value' => $request->user_register_mail_subject
            ]
        );

        // 本登録メールフォーマット
        $configs = Configs::updateOrCreate(
            ['name' => 'user_register_mail_format'],
            [
                'category' => 'user_register',
                'value' => $request->user_register_mail_format
            ]
        );

        // 本登録後のメッセージ
        $configs = Configs::updateOrCreate(
            ['name' => 'user_register_after_message'],
            [
                'category' => 'user_register',
                'value' => $request->user_register_after_message
            ]
        );

        // *** ユーザ登録画面
        // 自動ユーザ登録時に個人情報保護方針への同意を求めるか
        $configs = Configs::updateOrCreate(
            ['name' => 'user_register_requre_privacy'],
            [
                'category' => 'user_register',
                'value' => $request->user_register_requre_privacy
            ]
        );

        // 自動ユーザ登録時に求める個人情報保護方針の表示内容
        $configs = Configs::updateOrCreate(
            ['name' => 'user_register_privacy_description'],
            [
                'category' => 'user_register',
                'value' => $request->user_register_privacy_description
            ]
        );

        // 自動ユーザ登録時に求めるユーザ登録についての文言
        $configs = Configs::updateOrCreate(
            ['name' => 'user_register_description'],
            [
                'category' => 'user_register',
                'value' => $request->user_register_description
            ]
        );

        // ページ管理画面に戻る
        return redirect("/manage/user/autoRegist");
    }

    /**
     * データダウンロード
     */
    public function downloadCsv($request, $id = null, $sub_id = null, $data_output_flag = true)
    {
        // ユーザーのカラム
        $users_columns = UsersTool::getUsersColumns();

        // User データの取得
        $users = $this->getUsers($request, $users_columns);

        /*
        ダウンロード前の配列イメージ。
        0行目をUsersColumns から生成して、1行目以降は0行目の キーのみのコピーを作成し、データを入れ込んでいく。
        1行目以降の行番号は users_id の値を使用

        0 [
            37 => 姓
            40 => 名
            45 => テキスト
        ]
        1 [
            37 => 永原
            40 => 篤
            45 => テストです。
        ]
        2 [
            37 => 田中
            40 =>
            45 =>
        ]
        */
        // 返却用配列
        $csv_array = array();

        // データ行用の空配列
        $copy_base = array();

        // 見出し行-頭（固定項目）
        $csv_array[0]['id'] = 'id';
        $csv_array[0]['userid'] = 'ログインID';
        $csv_array[0]['name'] = 'ユーザ名';
        $csv_array[0]['group'] = 'グループ';
        $csv_array[0]['email'] = 'eメールアドレス';
        $csv_array[0]['password'] = 'パスワード';     // パスワード、中身は空で出力
        $copy_base['id'] = '';
        $copy_base['userid'] = '';
        $copy_base['name'] = '';
        $copy_base['group'] = '';
        $copy_base['email'] = '';
        $copy_base['password'] = '';
        // 見出し行
        foreach ($users_columns as $column) {
            $csv_array[0][$column->id] = $column->column_name;
            $copy_base[$column->id] = '';
        }
        // 見出し行-末尾（固定項目）
        $csv_array[0]['view_user_roles'] = '権限';
        $csv_array[0]['user_original_roles'] = '役割設定';
        $csv_array[0]['status'] = '状態';
        $copy_base['view_user_roles'] = '';
        $copy_base['user_original_roles'] = '';
        $copy_base['status'] = '';

        // $data_output_flag = falseは、CSVフォーマットダウンロード処理
        if ($data_output_flag) {
            // usersデータ
            foreach ($users as $user) {
                // ベースをセット
                $csv_array[$user->id] = $copy_base;

                // 初回で固定項目をセット
                $csv_array[$user->id]['id'] = $user->id;
                $csv_array[$user->id]['userid'] = $user->userid;     // ログインID
                $csv_array[$user->id]['name'] = $user->name;

                // グループ
                $csv_array[$user->id]['group'] = $user->convertLoopValue('group_users', 'name', UsersTool::CHECKBOX_SEPARATOR);

                $csv_array[$user->id]['email'] = $user->email;
                $csv_array[$user->id]['password'] = '';              // パスワード、中身は空で出力

                // 権限
                $csv_array[$user->id]['view_user_roles'] = $user->convertLoopValue('view_user_roles', 'role_name', UsersTool::CHECKBOX_SEPARATOR);

                // 役割設定
                $csv_array[$user->id]['user_original_roles'] = $user->convertLoopValue('user_original_roles', 'value', UsersTool::CHECKBOX_SEPARATOR);

                $csv_array[$user->id]['status'] = $user->status;
            }

            // 追加項目データの取得
            $input_cols = UsersTool::getUsersInputCols($users->pluck('id')->all());

            // 追加項目データ
            foreach ($input_cols as $input_col) {
                $csv_array[$input_col->users_id][$input_col->users_columns_id] = $input_col->value;
            }
        }

        // レスポンス
        $filename = 'users.csv';
        $headers = [
            'Content-Type' => 'text/csv',
            'content-Disposition' => 'attachment; filename="'.$filename.'"',
        ];

        // データ
        $csv_data = '';
        foreach ($csv_array as $csv_line) {
            foreach ($csv_line as $csv_col) {
                $csv_data .= '"' . $csv_col . '",';
            }
            // 末尾カンマを削除
            $csv_data = substr($csv_data, 0, -1);
            $csv_data .= "\n";
        }

        // Log::debug(var_export($request->character_code, true));

        // 文字コード変換
        if ($request->character_code == \CsvCharacterCode::utf_8) {
            $csv_data = mb_convert_encoding($csv_data, \CsvCharacterCode::utf_8);
            // UTF-8のBOMコードを追加する(UTF-8 BOM付きにするとExcelで文字化けしない)
            $csv_data = CsvUtils::addUtf8Bom($csv_data);
        } else {
            $csv_data = mb_convert_encoding($csv_data, \CsvCharacterCode::sjis_win);
        }

        return response()->make($csv_data, 200, $headers);
    }
}
