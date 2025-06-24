<?php

namespace App\Plugins\Manage\UserManage;

use App\Enums\CsvCharacterCode;
use App\Enums\EditType;
use App\Enums\Required;
use App\Enums\ShowType;
use App\Enums\UserColumnType;
use App\Enums\UserRegisterNoticeEmbeddedTag;
use App\Enums\UserStatus;
use App\Enums\UseType;
use App\Models\Core\Configs;
use App\Models\Core\Section;
use App\Models\Core\UsersColumns;
use App\Models\Core\UsersColumnsSelects;
use App\Models\Core\UsersColumnsSet;
use App\Models\Core\UsersRoles;
use App\Models\Core\UsersInputCols;
use App\Models\Core\UsersLoginHistories;
use App\Models\Core\UserSection;
use App\Models\Common\Group;
use App\Models\Common\GroupUser;
use App\Plugins\Manage\ManagePluginBase;
use App\Rules\CustomValiUserEmailUnique;
use App\Rules\CustomValiEmails;
use App\Rules\CustomValiCsvExistsName;
use App\Rules\CustomValiLoginIdAndPasswordDoNotMatch;
use App\Traits\ConnectMailTrait;
use App\User;
use App\Utilities\Csv\CsvUtils;
use App\Utilities\String\StringUtils;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Symfony\Component\HttpFoundation\StreamedResponse;

/**
 * ユーザ管理クラス
 *
 * @author 永原　篤 <nagahara@opensource-workshop.jp>
 * @author 牟田口 満 <mutaguchi@opensource-workshop.jp>
 * @copyright OpenSource-WorkShop Co.,Ltd. All Rights Reserved
 * @category ユーザ管理
 * @package Controller
 * @plugin_title ユーザ管理
 * @plugin_desc ユーザの一覧や追加など、ユーザに関する機能が集まった管理機能です。
 */
class UserManage extends ManagePluginBase
{
    use ConnectMailTrait;

    /**
     *  権限定義
     */
    public function declareRole()
    {
        // 権限チェックテーブル
        $role_check_table = [];
        $role_check_table["index"]                 = ['admin_user'];
        $role_check_table["search"]                = ['admin_user'];
        $role_check_table["clearSearch"]           = ['admin_user'];
        $role_check_table["regist"]                = ['admin_user'];
        $role_check_table["edit"]                  = ['admin_user'];
        $role_check_table["update"]                = ['admin_user'];
        $role_check_table["destroy"]               = ['admin_user'];
        $role_check_table["originalRole"]          = ['admin_user'];
        $role_check_table["saveOriginalRoles"]     = ['admin_user'];
        $role_check_table["deleteOriginalRole"]    = ['admin_user'];
        $role_check_table["groups"]                = ['admin_user'];
        $role_check_table["saveGroups"]            = ['admin_user'];
        $role_check_table["autoRegist"]            = ['admin_user'];
        $role_check_table["autoRegistUpdate"]      = ['admin_user'];
        $role_check_table["downloadCsv"]           = ['admin_user'];
        $role_check_table["downloadCsvFormat"]     = ['admin_user'];
        $role_check_table["import"]                = ['admin_user'];
        $role_check_table["uploadCsv"]             = ['admin_user'];
        $role_check_table["bulkDelete"]            = ['admin_user'];
        $role_check_table["bulkDestroy"]           = ['admin_user'];
        $role_check_table["loginHistory"]          = ['admin_user'];
        $role_check_table["mail"]                  = ['admin_user'];
        $role_check_table["mailSend"]              = ['admin_user'];
        // 項目セット
        $role_check_table["columnSets"]            = ['admin_site'];
        $role_check_table["registColumnSet"]       = ['admin_site'];
        $role_check_table["storeColumnSet"]        = ['admin_site'];
        $role_check_table["editColumnSet"]         = ['admin_site'];
        $role_check_table["updateColumnSet"]       = ['admin_site'];
        $role_check_table["destroyColumnSet"]      = ['admin_site'];
        // 項目設定
        $role_check_table["editColumns"]           = ['admin_site'];
        $role_check_table["addColumn"]             = ['admin_site'];
        $role_check_table["updateColumn"]          = ['admin_site'];
        $role_check_table["updateColumnSequence"]  = ['admin_site'];
        $role_check_table["updateColumnSequenceAll"]  = ['admin_site'];
        $role_check_table["deleteColumn"]          = ['admin_site'];
        // 項目詳細設定
        $role_check_table["editColumnDetail"]      = ['admin_site'];
        $role_check_table["updateColumnDetail"]    = ['admin_site'];
        $role_check_table["addSelect"]             = ['admin_site'];
        $role_check_table["updateSelect"]          = ['admin_site'];
        $role_check_table["updateSelectSequence"]  = ['admin_site'];
        $role_check_table["updateSelectSequenceAll"]  = ['admin_site'];
        $role_check_table["updateAgree"]           = ['admin_site'];
        $role_check_table["deleteSelect"]          = ['admin_site'];
        $role_check_table["addSection"]            = ['admin_site'];
        $role_check_table["updateSection"]         = ['admin_site'];
        $role_check_table["updateSectionSequence"] = ['admin_site'];
        $role_check_table["updateSectionSequenceAll"] = ['admin_site'];
        $role_check_table["deleteSection"]         = ['admin_site'];

        return $role_check_table;
    }

    /**
     * ユーザquery取得
     */
    private function getUsersQuery($request, $users_columns, int $columns_set_id)
    {
        return $this->getUsersPaginate($request, null, $users_columns, $columns_set_id, false);
    }

    /**
     * ユーザデータ取得(paginate or query)
     */
    private function getUsersPaginate($request, $page, $users_columns, int $columns_set_id, $is_paginate = true)
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

        // 最終ログイン日 のサブクエリ
        $sub_query_users_login_histories = UsersLoginHistories::select('users_id', DB::raw('MAX(logged_in_at) AS max_logged_in_at'))->groupBy('users_id');

        // ユーザデータ取得
        // $users_query = User::select('users.*');
        // ユーザー追加項目のソートなし
        if (empty($sort_column_id)) {
            $users_query = User::
                select(
                    'users.*',
                    'users_login_histories.max_logged_in_at',
                    'users_columns_sets.name as columns_set_name'
                )
                ->leftJoin(DB::raw("({$sub_query_users_login_histories->toSql()}) AS users_login_histories"), 'users_login_histories.users_id', '=', 'users.id')
                ->leftJoin("users_columns_sets", 'users_columns_sets.id', '=', 'users.columns_set_id');
        } else {
            // ユーザー追加項目のソートあり
            $users_query = User::
                select(
                    'users.*',
                    'users_input_cols.value',
                    'users_login_histories.max_logged_in_at',
                    'users_columns_sets.name as columns_set_name'
                )
                ->leftJoin('users_input_cols', function ($join) use ($sort_column_id) {
                    $join->on('users_input_cols.users_id', '=', 'users.id')
                        ->where('users_input_cols.users_columns_id', '=', $sort_column_id);
                })
                ->leftJoin(DB::raw("({$sub_query_users_login_histories->toSql()}) AS users_login_histories"), 'users_login_histories.users_id', '=', 'users.id')
                ->leftJoin("users_columns_sets", 'users_columns_sets.id', '=', 'users.columns_set_id');
        }

        // 所属型の項目をEager Loading
        if ($users_columns->where('column_type', UserColumnType::affiliation)->isNotEmpty()) {
            $users_query->with('section');
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
                            ->groupBy('users_id');

                    if (UsersColumns::isSearchExactMatchColumnType($users_column->column_type)) {
                        // 完全一致
                        $query->where('value', $search_keyword);
                    } else {
                        // 部分一致（通常）
                        $query->where('value', 'like', '%' . $search_keyword . '%');
                    }
                });
            }
        }

        // 項目セット
        if ($columns_set_id) {
            $users_query->where('users.columns_set_id', $columns_set_id);
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
            $users_query->orderBy('users.updated_at', 'asc');
        } elseif ($sort == 'updated_at_desc') {
            $users_query->orderBy('users.updated_at', 'desc');
        } elseif ($sort == 'userid_asc') {
            $users_query->orderBy('users.userid', 'asc');
        } elseif ($sort == 'userid_desc') {
            $users_query->orderBy('users.userid', 'desc');
        } elseif (array_key_exists($sort, $sort_column_orders)) {
            // ユーザー追加項目のソートあり
            $users_query->orderBy('users_input_cols.value', $sort_column_orders[$sort]);
        } elseif ($sort == 'logged_in_at_asc') {
            $users_query->orderBy('users_login_histories.max_logged_in_at', 'asc');
        } elseif ($sort == 'logged_in_at_desc') {
            $users_query->orderBy('users_login_histories.max_logged_in_at', 'desc');
        }
        // dd($sort_column_orders);

        // データ取得
        if ($is_paginate) {
            // ページャーで取得
            $users = $users_query->paginate(10, null, 'page', $page);
            // ユーザデータ取得後の追加処理
            return $this->getUsersAfter($users);

        } else {
            // query取得
            return $users_query;
        }
    }

    /**
     * ユーザデータ取得後の追加処理
     */
    private function getUsersAfter($users)
    {
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
            // 処理高速化の為、配列に詰め直す
            $tmp_group = [];
            foreach ($group_users as $val) {
                $tmp_group[$val->user_id][] = $val;
            }
            foreach ($users as &$user) {
                // 取得方法を変更
                $user->group_users = (isset($tmp_group[$user->id])) ? $tmp_group[$user->id] : [];
            }
        }

        $input_cols = UsersInputCols::whereIn('users_id', $user_ids)->get();
        foreach ($users as $user) {
            // （シリアライズで参照渡しになり）項目値をセット
            $user->inputs_column_value = $input_cols->where('users_id', $user->id)->pluck('value')->implode('|');
        }

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
     * @method_title ユーザ一覧
     * @method_desc サイトに登録されているユーザを一覧で確認できます。
     * @method_detail 絞り込み条件で権限やグループで絞り込むこともできます。
     */
    public function index($request, $id)
    {
        // 項目セットID
        // columns_set_idをURL等で指定時、表示ページ数をクリアするため、ページの処理（セッション）より前に処理する
        $columns_set_id = $this->getColumnsSetIdFromRequestOrSession($request, 'user.columns_set_id');

        /* ページの処理（セッション）
        ----------------------------------------------*/

        // 表示ページ数。詳細で更新して戻ってきたら、元と同じページを表示したい。
        // セッションにあればページの指定があれば使用。
        // ただし、リクエストでページ指定があればそれが優先。(ページング操作)
        $page = $this->getPaginatePageFromRequestOrSession($request, 'user_page_condition.page', 'page');

        /* データの取得（検索）
        ----------------------------------------------*/

        // ユーザーのカラム
        $users_columns = UsersTool::getUsersColumns($columns_set_id);
        // カラムの選択肢
        $users_columns_id_select = UsersTool::getUsersColumnsSelects($columns_set_id);

        // User データの取得
        $users = $this->getUsersPaginate($request, $page, $users_columns, $columns_set_id, true);

        // ユーザーの追加項目データ
        $input_cols = UsersTool::getUsersInputCols($users->pluck('id')->all());

        // get()で取得すると、ソフトデリート（deleted_at）は取得されない
        $groups_select = Group::get();
        // dd($groups);

        // ユーザ権限取得
        $auth_users_roles = $this->getRoles(Auth::user()->id);
        // 自身のシステム管理者権限持ち
        $has_auth_role_admin_system = Arr::get($auth_users_roles, 'manage.admin_system') == 1 ? true : false;

        return view('plugins.manage.user.list', [
            "function" => __FUNCTION__,
            "plugin_name" => "user",
            "users" => $users,
            'columns_set_id' => $columns_set_id,
            'columns_sets' => UsersColumnsSet::orderBy('display_sequence')->get(),
            "users_columns" => $users_columns,
            "users_columns_id_select" => $users_columns_id_select,
            "input_cols" => $input_cols,
            "groups_select" => $groups_select,
            "sections" => Section::orderBy('display_sequence')->get(),
            "has_auth_role_admin_system" => $has_auth_role_admin_system,
        ]);
    }

    /**
     * columns_set_idを、セッションorリクエストから取得
     * @see ManagePluginBase copy from getPaginatePageFromRequestOrSession()
     */
    private function getColumnsSetIdFromRequestOrSession(\Illuminate\Http\Request $request, string $session_name): int
    {
        $variable = 'columns_set_id';
        $columns_set_id = $this->getColumnsSetIdManageDefault();

        if ($request->session()->has($session_name)) {
            $columns_set_id = $request->session()->get($session_name);
        }
        if ($request->filled($variable)) {
            $columns_set_id = $request->$variable;
        }

        if ($request->filled($variable)) {
            session([$session_name => $request->$variable]);

            // columns_set_idをURL等で指定時、表示ページ数をクリア
            $request->session()->forget('user_page_condition.page');
        }

        return $columns_set_id;
    }

    /**
     * ユーザ管理の columns_set_id の初期値を取得
     */
    private function getColumnsSetIdManageDefault(): int
    {
        if (config('connect.USE_USERS_COLUMNS_SET')) {
            // 0:ユーザ一覧（全て）
            $columns_set_id = 0;
        } else {
            // 1:ユーザ一覧（基本）
            $columns_set_id = UsersTool::COLUMNS_SET_ID_DEFAULT;
        }

        return $columns_set_id;
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

        // *** ユーザーの追加項目
        // 項目セットID
        $columns_set_id = $this->getColumnsSetIdFromRequestOrSession($request, 'user.columns_set_id');
        // ユーザーのカラム
        $users_columns = UsersTool::getUsersColumns($columns_set_id);

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

        // 検索時、表示ページ数をクリア
        $request->session()->forget('user_page_condition.page');

        // is_search_collapse_show=1で検索エリアを開いたままにできる（オプションプラグイン等で利用）
        return redirect("/manage/user")->with('is_search_collapse_show', $request->is_search_collapse_show);
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
     *
     * @method_title ユーザ登録画面
     * @method_desc ユーザの登録や編集を行えます。
     * @method_detail
     */
    public function regist($request, $id)
    {
        // post ＆ URLのなかに'/manage/user/edit'が含まれている場合、oldに値をセット。
        // 入力エラー時はリダイレクトでget通信がくるので、その時は通さない
        if ($request->isMethod('post') && strpos($request->url(), '/manage/user/regist') !== false) {
            // old()に全inputをセット
            $request->flash();
        }

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

        // *** ユーザの追加項目
        // 項目セットID
        $columns_set_id = $request->input('columns_set_id', $this->getColumnsSetIdManageDefault());
        // ユーザーのカラム
        $users_columns = UsersTool::getUsersColumns($columns_set_id);
        // カラムの選択肢
        $users_columns_id_select = UsersTool::getUsersColumnsSelects($columns_set_id);
        // カラムの登録データ
        $input_cols = null;

        // ユーザー登録関連設定の取得
        $configs = Configs::where('category', 'general')
            ->orWhere(function ($query) use ($columns_set_id) {
                $query->where('category', 'user_register')
                    ->where('additional1', $columns_set_id);
            })
            ->get();

        return view('plugins.manage.user.regist', [
            "function" => __FUNCTION__,
            "plugin_name" => "user",
            "user" => $user,
            "original_role_configs" => $original_role_configs,
            'columns_set_id' => $columns_set_id,
            'columns_sets' => UsersColumnsSet::orderBy('display_sequence')->get(),
            'users_columns' => $users_columns,
            'users_columns_id_select' => $users_columns_id_select,
            'input_cols' => $input_cols,
            'sections' => Section::orderBy('display_sequence')->get(),
            'user_section' => new UserSection(),
            'configs' => $configs,
        ]);
    }

    /**
     *  ユーザ変更画面表示
     */
    public function edit($request, $id)
    {
        // post ＆ URLのなかに'/manage/user/edit'が含まれている場合、oldに値をセット。
        // 入力エラー時はリダイレクトでget通信がくるので、その時は通さない
        if ($request->isMethod('post') && strpos($request->url(), '/manage/user/edit') !== false) {
            // old()に全inputをセット
            $request->flash();
        }

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

        // 項目セットID
        $columns_set_id = $request->input('columns_set_id', $user->columns_set_id);
        // ユーザーのカラム
        $users_columns = UsersTool::getUsersColumns($columns_set_id);
        // カラムの選択肢
        $users_columns_id_select = UsersTool::getUsersColumnsSelects($columns_set_id);
        // カラムの登録データ
        $input_cols = UsersTool::getUsersInputCols([$id]);

        // ユーザー登録関連設定の取得
        $configs = Configs::where('category', 'general')
            ->orWhere(function ($query) use ($columns_set_id) {
                $query->where('category', 'user_register')
                    ->where('additional1', $columns_set_id);
            })
            ->get();

        // 削除できる
        $can_deleted = true;

        // システム管理者ありユーザー
        if (Arr::get($users_roles, 'manage.admin_system') == 1) {
            // 利用不可等を含めたシステム管理者権限の人数
            $in_users = UsersRoles::select('users_roles.users_id')
                ->where('role_name', 'admin_system')
                ->get();
            // ここでは users.status 見ず、仮削除とかのユーザも取得。入力チェックで users.status 見て最後の１人管理者チェックする
            $admin_system_user_count = User::whereIn('users.id', $in_users->pluck('users_id'))->count();

            // システム管理者権限持ちが１人
            if ($admin_system_user_count <= 1) {
                // 削除させない
                $can_deleted = false;
            }
        }

        // 対象がシステム管理者ありユーザー
        if (Arr::get($users_roles, 'manage.admin_system') == 1) {
            // 自身のユーザ権限取得
            $auth_users_roles = $this->getRoles(Auth::user()->id);

            // 自身がシステム管理者でない場合はエラー
            if (empty(Arr::get($auth_users_roles, 'manage.admin_system'))) {
                abort(403, '権限がありません。');
            }
        }

        // 画面呼び出し
        return view('plugins.manage.user.regist', [
            "function" => __FUNCTION__,
            "plugin_name" => "user",
            "id" => $id,
            "user" => $user,
            "users_roles" => $users_roles,
            "original_role_configs" => $original_role_configs,
            'columns_set_id' => $columns_set_id,
            'columns_sets' => UsersColumnsSet::orderBy('display_sequence')->get(),
            'users_columns' => $users_columns,
            'users_columns_id_select' => $users_columns_id_select,
            'input_cols' => $input_cols,
            'sections' => Section::orderBy('display_sequence')->get(),
            'user_section' => UserSection::where('user_id', $user->id)->firstOrNew(),
            'can_deleted' => $can_deleted,
            'configs' => $configs,
        ]);
    }

    /**
     * 更新
     */
    public function update($request, $id = null)
    {
        // ユーザ権限取得
        $users_roles = $this->getRoles($id);

        // 対象がシステム管理者ありユーザー
        if (Arr::get($users_roles, 'manage.admin_system') == 1) {
            // 自身のユーザ権限取得
            $auth_users_roles = $this->getRoles(Auth::user()->id);

            // 自身がシステム管理者でない場合はエラー
            if (empty(Arr::get($auth_users_roles, 'manage.admin_system'))) {
                abort(403, '権限がありません。');
            }
        }

        // ユーザーのカラム
        $users_columns = UsersTool::getUsersColumns($request->columns_set_id);

        // 項目のエラーチェック
        $validator_array = [
            'column' => [
                'name'           => 'required|string|max:255',
                // ログインID
                'userid'         => ['required', 'max:255', Rule::unique('users', 'userid')->ignore($id)],
                'email'          => ['nullable', 'email', 'max:255', new CustomValiUserEmailUnique($request->columns_set_id, $id)],
                'password'       => [
                    'nullable',
                    'string',
                    'min:6',
                    'confirmed',
                    new CustomValiLoginIdAndPasswordDoNotMatch($request->userid, UsersColumns::getLabelLoginId($users_columns)),
                ],
                'status'         => ['required'],
                'columns_set_id' => ['required'],
            ],
            'message' => [
                'name'           => UsersColumns::getLabelUserName($users_columns),
                'userid'         => UsersColumns::getLabelLoginId($users_columns),
                'email'          => UsersColumns::getLabelUserEmail($users_columns),
                'password'       => UsersColumns::getLabelUserPassword($users_columns),
                'status'         => '状態',
                'columns_set_id' => '項目セット',
            ]
        ];

        foreach ($users_columns as $users_column) {
            if (UsersColumns::isLoopNotShowColumnType($users_column->column_type)) {
                // 既に入力チェックセット済みのため、ここではチェックしない
                continue;
            }
            // バリデータールールをセット
            $validator_array = UsersTool::getValidatorRule($validator_array, $users_column, $request->columns_set_id, $id);
        }

        // 項目のエラーチェック
        $validator = Validator::make($request->all(), $validator_array['column']);
        $validator->setAttributeNames($validator_array['message']);

        // 更新前のステータス
        $user = User::find($id);
        $before_status = $user ? $user->status : null;

        // 任意のバリデーションを追加
        $validator->after(function ($validator) use ($users_roles, $request, $before_status) {
            // システム管理者持ちユーザーで && システム管理者権限持ちが１人 && システム管理者権限が外れてたら入力エラー

            // システム管理者持ちユーザー
            if (Arr::get($users_roles, 'manage.admin_system') == 1) {
                // 利用可能なシステム管理者権限の人数
                $in_users = UsersRoles::select('users_roles.users_id')
                    ->where('role_name', 'admin_system')
                    ->get();
                $admin_system_user_count = User::where('users.status', UserStatus::active)
                    ->whereIn('users.id', $in_users->pluck('users_id'))
                    ->count();

                // 利用可能なシステム管理者権限持ちが１人 && システム管理者権限が外れてる
                if ($admin_system_user_count <= 1 && empty(Arr::get($request->manage, 'admin_system'))) {
                    // 入力エラー追加
                    $validator->errors()->add('undelete', '最後のシステム管理者保持者のため、管理権限のシステム管理者権限を外さないでください。');
                }

                // 利用可能なシステム管理者権限持ちが１人 && システム管理者権限付き && 利用可能なユーザから、利用できないユーザに変更
                if ($admin_system_user_count <= 1 && Arr::get($request->manage, 'admin_system') == 1 && $before_status == UserStatus::active && $request->status != UserStatus::active) {
                    // 入力エラー追加
                    $validator->errors()->add('undelete', '最後のシステム管理者保持者のため、状態を利用可能以外にしないでください。');
                }
            }
        });

        // エラーがあった場合は入力画面に戻る。
        if ($validator->fails()) {
            // Log::debug(var_export($request->old(), true));
            // エラーと共に編集画面を呼び出す
            // return redirect('manage/user/edit/' . $id)->withErrors($validator)->withInput();
            return redirect()->back()->withErrors($validator)->withInput();
        }

        // 更新内容の配列
        $update_array = [
            'name'           => $request->name,
            'email'          => $request->email,
            'userid'         => $request->userid,
            'status'         => $request->status,
            'columns_set_id' => $request->columns_set_id,
        ];

        // パスワードの入力があれば、更新
        if (!empty($request->password)) {
            // change to laravel6.
            // $update_array['password'] = bcrypt($request->password);
            $update_array['password'] = Hash::make($request->password);
        }

        // ユーザデータの更新
        User::where('id', $id)->update($update_array);
        // 更新後を再取得
        $user = User::find($id);

        // ユーザーの追加項目.
        // id（行 id）が渡ってきたら、詳細データは一度消す。その後、登録と同じ処理にする。delete -> insert
        UsersInputCols::where('users_id', $id)->delete();

        // users_input_cols 登録
        foreach ($users_columns as $users_column) {
            if (UsersColumns::isLoopNotShowColumnType($users_column->column_type)) {
                // 既に登録済みのため、ここでは登録しない
                continue;
            }

            $value = "";
            if (!isset($request->users_columns_value[$users_column->id])) {
                // 値なし
                $value = null;
            } elseif (is_array($request->users_columns_value[$users_column->id])) {
                $value = implode(UsersTool::CHECKBOX_SEPARATOR, $request->users_columns_value[$users_column->id]);
            } else {
                $value = $request->users_columns_value[$users_column->id];
            }

            // 所属型は個別のテーブルに書き込む
            if ($users_column->column_type === UserColumnType::affiliation) {
                // 値無しは所属情報を削除
                if (empty($value)) {
                    UserSection::where('user_id', $user->id)->delete();
                } else {
                    UserSection::updateOrCreate(
                        ['user_id' => $user->id],
                        ['section_id' => $value]
                    );
                    // users_input_cols には　名称を設定する
                    $value = Section::find($value)->name;
                }
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

        // 承認完了メール送信
        if ($before_status === UserStatus::pending_approval
            && (int)$request->status === UserStatus::active) {
            $this->sendMailApproved($user);
        }

        // 変更画面に戻る
        // return $this->edit($request, $id);
        return redirect("/manage/user/edit/$id")->with('flash_message', 'ユーザ変更しました。');
    }

    /**
     * 削除処理
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

        // ユーザ権限取得
        $users_roles = $this->getRoles($id);

        // システム管理者ありユーザー
        if (Arr::get($users_roles, 'manage.admin_system') == 1) {
            // 利用不可等を含めたシステム管理者権限の人数
            $in_users = UsersRoles::select('users_roles.users_id')
                ->where('role_name', 'admin_system')
                ->get();
            $admin_system_user_count = User::whereIn('users.id', $in_users->pluck('users_id'))->count();
            if ($admin_system_user_count <= 1) {
                $validator = Validator::make($request->all(), []);
                $validator->errors()->add('undelete', '最後のシステム管理者保持者は削除できません。');
                return $this->edit($request, $id)->withErrors($validator);
            }
        }

        // 対象がシステム管理者ありユーザー
        if (Arr::get($users_roles, 'manage.admin_system') == 1) {
            // 自身のユーザ権限取得
            $auth_users_roles = $this->getRoles($user_id);

            // 自身がシステム管理者でない場合はエラー
            if (empty(Arr::get($auth_users_roles, 'manage.admin_system'))) {
                $validator = Validator::make($request->all(), []);
                $validator->errors()->add('undelete', '権限がありません。');
                return $this->edit($request, $id)->withErrors($validator);
            }
        }

        // id がある場合、データを削除
        if ($id) {
            // 権限データを削除する。
            UsersRoles::where('users_id', $id)->delete();

            // ユーザ任意追加項目データを削除する。
            $users_input_cols_ids = UsersInputCols::where('users_id', $id)->pluck('id');
            UsersInputCols::destroy($users_input_cols_ids);

            // 参加グループ削除
            $group_user_ids = GroupUser::where('user_id', $id)->pluck('id');
            GroupUser::destroy($group_user_ids);

            // データを削除する。
            User::destroy($id);
        }
        // 削除後はユーザ一覧を呼ぶ。
        return redirect('manage/user');
    }

    /**
     * 役割設定画面表示
     *
     * @method_title 役割設定
     * @method_desc ユーザの属性として役割を設定することができます。
     * @method_detail 通常の権限内でさらに役割を分けることができます。内容は各プラグインの仕様となります。
     */
    public function originalRole($request, $id)
    {
        // セッション初期化などのLaravel 処理。
        // $request->flash();

        // 役割設定取得
        $configs = Configs::where('category', 'original_role')->orderBy('additional1', 'asc')->get();

        return view('plugins.manage.user.original_role', [
            "function"    => __FUNCTION__,
            "plugin_name" => "user",
            "id"          => $id,
            "configs"     => $configs,
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
            $rules['add_name'] = ['required', 'alpha_dash'];
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
                $rules['name.'.$config_id] = ['required', 'alpha_dash'];
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
            // return $this->originalRole($request, $id, $validator->errors());
            return redirect()->back()->withErrors($validator)->withInput();
        }

        // 既存項目のidに値が入っていたら、行の他の項目も必須
        if (!empty($request->configs_id)) {
            foreach ($request->configs_id as $config_id) {
                // 項目のエラーチェック
                $validator = Validator::make($request->all(), [
                    'additional1.'.$config_id => ['required', 'numeric'],
                    'name.'.$config_id        => ['required', 'alpha_dash'],
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

        // return $this->originalRole($request, $id, null);
        return redirect()->back();
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
        // ユーザ新規登録＞グループ参加で、画面再表示してもセッション保持
        $request->session()->keep(['password']);
        // グループ参加＞メール送信 遷移先
        $request->session()->keep(['register_redirectTo']);

        // ユーザデータ取得
        $user = User::find($id);

        // グループ取得
        $group_users = Group::select('groups.*', 'group_users.user_id', 'group_users.group_role')
            ->leftJoin('group_users', function ($join) use ($id) {
                $join->on('groups.id', '=', 'group_users.group_id')
                    ->where('group_users.user_id', '=', $id)
                    ->whereNull('group_users.deleted_at');
            })
            ->orderBy('groups.display_sequence', 'asc')
            ->get();
            // ->paginate(10);

        // register_redirectTo あり（ユーザ新規登録＞グループ参加）の場合
        if (session('register_redirectTo')) {
            foreach ($group_users as &$group_user) {
                // 初期参加グループなら、参加で初期表示
                if ($group_user->initial_group_flag) {
                    $group_user->group_role = 'general';
                }
            }
        }

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
        // ユーザ新規登録＞グループ参加＞メール送信でもセッション保持
        $request->session()->keep(['password']);

        // 画面項目のチェック
        if ($request->has('group_roles')) {
            foreach ($request->group_roles as $group_id => $group_role) {
                // 権限の解除
                if (empty($group_role)) {
                    // bugfix: 論理削除時にdeleted_id、deleted_nameが入ってないバグ修正
                    // GroupUser::where('group_id', $group_id)->where('user_id', $id)->delete();
                    $group_user = GroupUser::where('group_id', $group_id)->where('user_id', $id)->first();
                    if ($group_user) {
                        $group_user->delete();
                    }
                } else {
                    // 登録 or 更新
                    $group_user = GroupUser::updateOrCreate(
                        ['group_id' => $group_id, 'user_id' => $id],
                        [
                            'group_id' => $group_id,
                            'user_id' => $id,
                            'group_role' => $group_role,
                        ]
                    );
                }
            }
        }

        if (session('register_redirectTo')) {
            // ユーザ新規登録＞グループ参加＞メール送信 の メール送信画面へ
            return redirect(session('register_redirectTo'))->with('flash_message', '参加グループを変更しました。');
        } else {
            // 参加グループ編集画面へ
            return redirect('manage/user/groups/' . $id)->with('flash_message', '参加グループを変更しました。');
        }
    }

    /**
     * 自動ユーザ登録設定 画面表示
     *
     * @method_title 自動ユーザ登録設定
     * @method_desc 希望者が自らサイトにユーザ登録できるようにする設定です。
     * @method_detail 自動ユーザ登録の許可や仮登録の動き、本登録の動きを設定できます。
     */
    public function autoRegist($request, $id)
    {
        if (!$id) {
            abort(404, 'URLにIDが含まれてません。');
        }

        // Config データの取得
        $configs = Configs::where('category', 'user_register')
            ->where('additional1', $id)
            ->orWhere(function ($query) {
                $query->where('category', 'user_register')
                    ->where('additional1', 'all');
            })
            ->get();

        return view('plugins.manage.user.auto_regist', [
            "function" => __FUNCTION__,
            "plugin_name" => "user",
            "configs" => $configs,
            'columns_set_id' => $id,
            'columns_sets' => UsersColumnsSet::orderBy('display_sequence')->get(),
            "users_columns" => UsersTool::getUsersColumns($id),
        ]);
    }

    /**
     * 自動ユーザ登録設定 更新
     */
    public function autoRegistUpdate($request, $id)
    {
        if (!$id) {
            abort(404, 'URLにIDが含まれてません。');
        }

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
            ['name' => 'user_register_enable', 'additional1' => $id],
            [
                'category' => 'user_register',
                'value' => $request->user_register_enable
            ]
        );

        // 管理者の承認
        $configs = Configs::updateOrCreate(
            ['name' => 'user_registration_require_approval', 'additional1' => $id],
            [
                'category' => 'user_register',
                'value' => $request->user_registration_require_approval
            ]
        );

        // 自動ユーザ登録後の自動ログイン
        $configs = Configs::updateOrCreate(
            ['name' => 'user_register_auto_login_flag', 'additional1' => $id],
            [
                'category' => 'user_register',
                'value' => $request->user_register_auto_login_flag
            ]
        );

        // 以下のアドレスにメール送信する
        $configs = Configs::updateOrCreate(
            ['name' => 'user_register_mail_send_flag', 'additional1' => $id],
            [
                'category' => 'user_register',
                'value' => $request->user_register_mail_send_flag ?? 0
            ]
        );

        // 送信するメールアドレス
        $configs = Configs::updateOrCreate(
            ['name' => 'user_register_mail_send_address', 'additional1' => $id],
            [
                'category' => 'user_register',
                'value' => $request->user_register_mail_send_address
            ]
        );

        // 登録者にメール送信する
        $configs = Configs::updateOrCreate(
            ['name' => 'user_register_user_mail_send_flag', 'additional1' => $id],
            [
                'category' => 'user_register',
                'value' => $request->user_register_user_mail_send_flag ?? 0
            ]
        );

        // 登録者に仮登録メールを送信する
        $configs = Configs::updateOrCreate(
            ['name' => 'user_register_temporary_regist_mail_flag', 'additional1' => $id],
            [
                'category' => 'user_register',
                'value' => $request->user_register_temporary_regist_mail_flag ?? 0
            ]
        );

        // 仮登録メール件名
        $configs = Configs::updateOrCreate(
            ['name' => 'user_register_temporary_regist_mail_subject', 'additional1' => $id],
            [
                'category' => 'user_register',
                'value' => $request->user_register_temporary_regist_mail_subject
            ]
        );

        // 仮登録メールフォーマット
        $configs = Configs::updateOrCreate(
            ['name' => 'user_register_temporary_regist_mail_format', 'additional1' => $id],
            [
                'category' => 'user_register',
                'value' => $request->user_register_temporary_regist_mail_format
            ]
        );

        // 仮登録後のメッセージ
        $configs = Configs::updateOrCreate(
            ['name' => 'user_register_temporary_regist_after_message', 'additional1' => $id],
            [
                'category' => 'user_register',
                'value' => $request->user_register_temporary_regist_after_message
            ]
        );

        // 本登録メール件名
        $configs = Configs::updateOrCreate(
            ['name' => 'user_register_mail_subject', 'additional1' => $id],
            [
                'category' => 'user_register',
                'value' => $request->user_register_mail_subject
            ]
        );

        // 本登録メールフォーマット
        $configs = Configs::updateOrCreate(
            ['name' => 'user_register_mail_format', 'additional1' => $id],
            [
                'category' => 'user_register',
                'value' => $request->user_register_mail_format
            ]
        );

        // 本登録後のメッセージ
        $configs = Configs::updateOrCreate(
            ['name' => 'user_register_after_message', 'additional1' => $id],
            [
                'category' => 'user_register',
                'value' => $request->user_register_after_message
            ]
        );

        // 承認完了メール件名
        $configs = Configs::updateOrCreate(
            ['name' => 'user_register_approved_mail_subject', 'additional1' => $id],
            [
                'category' => 'user_register',
                'value' => $request->user_register_approved_mail_subject
            ]
        );

        // 承認完了メールフォーマット
        $configs = Configs::updateOrCreate(
            ['name' => 'user_register_approved_mail_format', 'additional1' => $id],
            [
                'category' => 'user_register',
                'value' => $request->user_register_approved_mail_format
            ]
        );

        // *** ユーザ登録画面
        // 自動ユーザ登録時に個人情報保護方針への同意を求めるか
        $configs = Configs::updateOrCreate(
            ['name' => 'user_register_requre_privacy', 'additional1' => $id],
            [
                'category' => 'user_register',
                'value' => $request->user_register_requre_privacy
            ]
        );

        // 自動ユーザ登録時に求める個人情報保護方針の表示内容
        $configs = Configs::updateOrCreate(
            ['name' => 'user_register_privacy_description', 'additional1' => $id],
            [
                'category' => 'user_register',
                'value' => $request->user_register_privacy_description
            ]
        );

        // 自動ユーザ登録時に求めるユーザ登録についての文言
        $configs = Configs::updateOrCreate(
            ['name' => 'user_register_description', 'additional1' => $id],
            [
                'category' => 'user_register',
                'value' => $request->user_register_description
            ]
        );

        // 初期コンテンツ権限
        // 空要素の削除
        $base_roles = array_filter($request->base_roles);
        $configs = Configs::updateOrCreate(
            ['name' => 'user_register_base_roles', 'additional1' => $id],
            [
                'category' => 'user_register',
                'value' => $base_roles ? implode(',', $base_roles) : '',
            ]
        );

        // 項目セット名（全ての自動ユーザ登録設定で共通設定. additional1=all）
        $configs = Configs::updateOrCreate(
            ['name' => 'user_columns_set_label_name', 'additional1' => 'all'],
            [
                'category' => 'user_register',
                'value' => $request->user_columns_set_label_name
            ]
        );

        // 自動ユーザ登録設定画面に戻る
        return redirect("/manage/user/autoRegist/$id")->with('flash_message', '更新しました。');
    }

    /**
     * CSVインポートのフォーマットダウンロード
     */
    public function downloadCsvFormat($request, $id = null, $sub_id = null)
    {
        // データ出力しない（フォーマットのみ出力）
        $data_output_flag = false;
        return $this->downloadCsv($request, $id, $sub_id, $data_output_flag);
    }

    /**
     * データダウンロード
     */
    public function downloadCsv($request, $id = null, $sub_id = null, $data_output_flag = true)
    {
        // ユーザーのカラム
        $users_columns = UsersTool::getUsersColumns($id);

        // ユーザquery取得
        $users_query = $this->getUsersQuery($request, $users_columns, $id);

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
        // データ行用の空配列
        $copy_base = array();

        // 見出し行
        $head = array();

        // インポートカラムの取得
        $import_column = $this->getImportColumn($users_columns);

        // 見出し行
        foreach ($import_column as $key => $column_name) {
            $head[$key] = $column_name;
            $copy_base[$key] = '';
        }

        // レスポンス
        if (config('connect.USE_USERS_COLUMNS_SET')) {
            $columns_set = UsersColumnsSet::findOrNew($id);
            $filename = "users_$columns_set->name.csv";
        } else {
            $filename = 'users.csv';
        }
        $headers = [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => 'attachment; filename="'.$filename.'"',
        ];

        $character_code = $request->character_code;

        // $data_output_flag = falseは、CSVフォーマットダウンロード処理
        if (!$data_output_flag) {
            // データ
            $csv_array[0] = $head;
            $csv_data = CsvUtils::getResponseCsvData($csv_array, $character_code);
            return response()->make($csv_data, 200, $headers);
        }

        // Symfony の StreamedResponse で出力 ＆ chunk でデータ取得することにより
        // 大容量の出力に対応
        return new StreamedResponse(
            function () use ($users_query, $head, $copy_base, $character_code) {
                $stream = fopen('php://output', 'w');

                // 文字コード変換
                if ($character_code == CsvCharacterCode::utf_8) {
                    mb_convert_variables(CsvCharacterCode::utf_8, CsvCharacterCode::utf_8, $head);
                    // BOM付きにさせる場合にファイルの先頭に書き込む
                    fwrite($stream, CsvUtils::bom);
                } else {
                    mb_convert_variables(CsvCharacterCode::sjis_win, CsvCharacterCode::utf_8, $head);
                }
                fputcsv($stream, $head);

                // データの処理
                $users_query->chunk(1000, function ($users) use ($stream, $copy_base, $character_code) {

                    // ユーザデータ取得後の追加処理
                    $users = $this->getUsersAfter($users);

                    // 追加項目データの取得
                    $input_cols = UsersTool::getUsersInputCols($users->pluck('id')->all());

                    foreach ($users as $user) {
                        // ベースをセット
                        $csv_array = $copy_base;

                        // 初回で固定項目をセット
                        $csv_array['id'] = $user->id;
                        $csv_array['userid'] = $user->userid;     // ログインID
                        $csv_array['name'] = $user->name;

                        // グループ
                        $csv_array['group'] = $user->convertLoopValue('group_users', 'name', UsersTool::CHECKBOX_SEPARATOR);

                        $csv_array['email'] = $user->email;
                        $csv_array['password'] = '';              // パスワード、中身は空で出力

                        // 権限
                        $csv_array['view_user_roles'] = $user->convertLoopValue('view_user_roles', 'role_name', UsersTool::CHECKBOX_SEPARATOR);

                        // 役割設定
                        $csv_array['user_original_roles'] = $user->convertLoopValue('user_original_roles', 'value', UsersTool::CHECKBOX_SEPARATOR);

                        $csv_array['status'] = $user->status;

                        $input_cols_solo = $input_cols->where('users_id', $user->id);

                        // 追加項目データ
                        foreach ($input_cols_solo as $input_col) {
                            $csv_array[$input_col->users_columns_id] = UsersTool::getUsersInputColValue($input_col);
                        }

                        // 文字コード変換
                        if ($character_code == CsvCharacterCode::utf_8) {
                            mb_convert_variables(CsvCharacterCode::utf_8, CsvCharacterCode::utf_8, $csv_array);
                        } else {
                            mb_convert_variables(CsvCharacterCode::sjis_win, CsvCharacterCode::utf_8, $csv_array);
                        }
                        fputcsv($stream, $csv_array);
                    }
                });
                fclose($stream);
            },
            200,
            $headers
        );
    }

    /**
     * インポートカラムの取得
     */
    private function getImportColumn($users_columns)
    {
        // 見出し行-頭（固定項目）
        $import_column['id']       = 'id';
        $import_column['userid']   = UsersColumns::getLabelLoginId($users_columns);
        $import_column['name']     = UsersColumns::getLabelUserName($users_columns);
        $import_column['group']    = 'グループ';
        $import_column['email']    = UsersColumns::getLabelUserEmail($users_columns);
        $import_column['password'] = UsersColumns::getLabelUserPassword($users_columns);

        // 見出し行
        foreach ($users_columns as $column) {
            if (UsersColumns::isLoopNotShowColumnType($column->column_type)) {
                continue;
            }

            $import_column[$column->id] = $column->column_name;
        }

        // 見出し行-末尾（固定項目）
        $import_column['view_user_roles'] = '権限';
        $import_column['user_original_roles'] = '役割設定';
        $import_column['status'] = '状態';

        return $import_column;
    }

    /**
     * インポートカラムの列番号の取得
     */
    private function getImportColumnColNo($users_columns)
    {
        // 見出し行-頭（固定項目）
        $import_column[0] = 'id';
        $import_column[1] = 'userid';
        $import_column[2] = 'name';
        $import_column[3] = 'group';
        $import_column[4] = 'email';
        $import_column[5] = 'password';

        // bugfix: 追加項目なしの場合、$no未定義でエラーとなるため修正
        $no = -1;

        // 見出し行
        foreach ($users_columns as $no => $column) {
            $import_column[$no + 6] = $column->id;
        }

        // 見出し行-末尾（固定項目）
        $import_column[$no + 7] = 'view_user_roles';
        $import_column[$no + 8] = 'user_original_roles';
        $import_column[$no + 9] = 'status';
        return $import_column;
    }

    /**
     * インポート画面表示
     *
     * @method_title CSVインポート
     * @method_desc CSVファイルからユーザを作成できます。
     * @method_detail ID カラムの指定により、ユーザの登録、更新にも対応しています。
     */
    public function import($request, $page_id = null)
    {
        // 管理画面プラグインの戻り値の返し方
        return view('plugins.manage.user.import', [
            "function"       => __FUNCTION__,
            "plugin_name"    => "user",
            'columns_set_id' => $this->getColumnsSetIdManageDefault(),
            'columns_sets' => UsersColumnsSet::orderBy('display_sequence')->get(),
        ]);
    }

    /**
     * インポート
     */
    public function uploadCsv($request, $page_id = null)
    {
        // csv
        $rules = [
            'users_csv' => [
                'required',
                'file',
                'mimes:csv,txt', // mimesの都合上text/csvなのでtxtも許可が必要
                'mimetypes:application/csv,text/plain,text/csv',
            ],
            'columns_set_id' => ['required'],
        ];

        // 画面エラーチェック
        $validator = Validator::make($request->all(), $rules);
        $validator->setAttributeNames([
            'users_csv'      => 'CSVファイル',
            'columns_set_id' => '項目セット',
        ]);

        if ($validator->fails()) {
            // エラーと共に編集画面を呼び出す
            return redirect()->back()->withErrors($validator)->withInput();
        }

        // CSVファイル一時保存
        $path = $request->file('users_csv')->store('tmp');
        $csv_full_path = storage_path('app/') . $path;

        // ファイル拡張子取得
        $file_extension = $request->file('users_csv')->getClientOriginalExtension();
        // 小文字に変換
        $file_extension = strtolower($file_extension);

        // 文字コード
        $character_code = $request->character_code;

        // 文字コード自動検出
        if ($character_code == CsvCharacterCode::auto) {
            // 文字コードの自動検出(文字エンコーディングをsjis-win, UTF-8の順番で自動検出. 対象文字コード外の場合、false戻る)
            $character_code = CsvUtils::getCharacterCodeAuto($csv_full_path);
            if (!$character_code) {
                // 一時ファイルの削除
                Storage::delete($path);

                $error_msgs = "文字コードを自動検出できませんでした。CSVファイルの文字コードを " . CsvCharacterCode::getSelectMembersDescription(CsvCharacterCode::sjis_win) .
                            ", " . CsvCharacterCode::getSelectMembersDescription(CsvCharacterCode::utf_8) . " のいずれかに変更してください。";

                return redirect()->back()->withErrors(['users_csv' => $error_msgs])->withInput();
            }
        }

        // 読み込み
        $fp = fopen($csv_full_path, 'r');
        // CSVファイル：Shift-JIS -> UTF-8変換時のみ
        if (CsvCharacterCode::isShiftJis($character_code)) {
            // ストリームフィルタ内で、Shift-JIS -> UTF-8変換
            $fp = CsvUtils::setStreamFilterRegisterSjisToUtf8($fp);
        }

        CsvUtils::setLocale();

        // 一行目（ヘッダ）
        $header_columns = fgetcsv($fp, 0, ',');
        // CSVファイル：UTF-8のみ
        if ($character_code == CsvCharacterCode::utf_8) {
            // UTF-8のみBOMコードを取り除く
            $header_columns = CsvUtils::removeUtf8Bom($header_columns);
        }

        // 任意カラムの取得
        $users_columns = UsersTool::getUsersColumns($request->columns_set_id);
        // インポートカラムの取得
        $import_column = $this->getImportColumn($users_columns);

        // ヘッダー項目のエラーチェック
        $error_msgs = CsvUtils::checkCsvHeader($header_columns, $import_column);
        if (!empty($error_msgs)) {
            // 一時ファイルの削除
            fclose($fp);
            Storage::delete($path);

            return redirect()->back()->withErrors(['users_csv' => $error_msgs])->withInput();
        }

        // 固定項目を取り除いたユーザカラム. values()でキーをリセットしたコレクション取得
        $users_columns_not_fixed_column = $users_columns->whereNotIn('column_type', UsersColumns::loopNotShowColumnTypes())->values();

        $group = Group::get();
        $import_column_col_no = $this->getImportColumnColNo($users_columns_not_fixed_column);
        // 役割設定
        $configs_original_role = Configs::where('category', 'original_role')->get();

        // データ項目のエラーチェック
        $error_msgs = $this->checkCvslines($fp, $users_columns, $group, $import_column_col_no, $configs_original_role, $request->columns_set_id);
        if (!empty($error_msgs)) {
            // 一時ファイルの削除
            fclose($fp);
            Storage::delete($path);

            return redirect()->back()->withErrors(['users_csv' => $error_msgs])->withInput();
        }

        // ファイルポインタの位置を先頭に戻す
        rewind($fp);

        // ヘッダー
        $header_columns = fgetcsv($fp, 0, ',');
        // CSVファイル：UTF-8のみ
        if ($character_code == CsvCharacterCode::utf_8) {
            // UTF-8のみBOMコードを取り除く
            $header_columns = CsvUtils::removeUtf8Bom($header_columns);
        }

        // データ
        while (($csv_columns = fgetcsv($fp, 0, ',')) !== false) {
            // --- 入力値変換

            // 入力値をトリム(preg_replace(/u)で置換. /u = UTF-8 として処理)
            $csv_columns = StringUtils::trimInput($csv_columns);

            // $users_id = array_shift($csv_columns);
            $id_col_no = array_search('id', $import_column_col_no);
            $users_id = $csv_columns[$id_col_no];

            // 空文字をnullに変換
            $users_id = StringUtils::convertEmptyStringsToNull($users_id);

            foreach ($csv_columns as $col => &$csv_column) {
                // 空文字をnullに変換
                $csv_column = StringUtils::convertEmptyStringsToNull($csv_column);
            }

            // --- User
            if (empty($users_id)) {
                // 登録
                $user = new User();
            } else {
                // 更新
                // users_idはバリデートでUser存在チェック済みなので、必ずデータある想定
                $user = User::where('id', $users_id)->first();
            }

            // ログインID
            $userid_col_no = array_search('userid', $import_column_col_no);
            $user->userid = $csv_columns[$userid_col_no];
            // ユーザ名
            $name_col_no = array_search('name', $import_column_col_no);
            $user->name = $csv_columns[$name_col_no];
            // メールアドレス
            $email_col_no = array_search('email', $import_column_col_no);
            $user->email = $csv_columns[$email_col_no];

            // パスワード（新規(id空)は必須でバリデーション追加. 更新はnullOK）
            $password_col_no = array_search('password', $import_column_col_no);
            $password = $csv_columns[$password_col_no];
            if (empty($users_id)) {
                // 登録
                $user->password = Hash::make($password);
            } else {
                // 更新
                if ($password) {
                    // 値ありのみパスワード処理する
                    $user->password = Hash::make($password);
                }
            }

            // 状態
            $status_col_no = array_search('status', $import_column_col_no);
            $user->status = $csv_columns[$status_col_no];
            // 項目セットID
            $user->columns_set_id = $request->columns_set_id;

            $user->save();

            // --- グループ
            $group_col_no = array_search('group', $import_column_col_no);
            // 配列に変換する。
            $csv_groups = explode(UsersTool::CHECKBOX_SEPARATOR, $csv_columns[$group_col_no] ?? '');
            // 配列値の入力値をトリム (preg_replace(/u)で置換. /u = UTF-8 として処理)
            $csv_groups = StringUtils::trimInput($csv_groups);

            // 全グループ分ループ
            foreach ($group as $group_row) {
                // CSVにグループ名あり
                if (in_array($group_row->name, $csv_groups)) {
                    // グループ参加
                    $group_user = GroupUser::updateOrCreate(
                        ['group_id' => $group_row->id, 'user_id' => $user->id],
                        [
                            'group_id' => $group_row->id,
                            'user_id' => $user->id,
                            'group_role' => 'general',
                            'deleted_id' => null,
                            'deleted_name' => null,
                            'deleted_at' => null
                        ]
                    );
                } else {
                    // グループ不参加. deletingイベント対応
                    $group_user = GroupUser::where('group_id', $group_row->id)->where('user_id', $user->id)->first();
                    if ($group_user) {
                        $group_user->delete();
                    }
                }
            }

            // --- ユーザーの追加項目
            // id（行 id）が渡ってきたら、詳細データは一度消す。その後、登録と同じ処理にする。delete -> insert
            UsersInputCols::where('users_id', $user->id)->delete();

            // users_input_cols 登録
            foreach ($users_columns as $users_column) {
                if (UsersColumns::isLoopNotShowColumnType($users_column->column_type)) {
                    continue;
                }

                $users_column_col_no = array_search($users_column->id, $import_column_col_no, true);
                $value = $csv_columns[$users_column_col_no];

                $users_input_cols = new UsersInputCols();
                $users_input_cols->users_id = $user->id;
                $users_input_cols->users_columns_id = $users_column->id;
                $users_input_cols->value = $value;
                $users_input_cols->save();

                // 所属型
                if ($users_column->column_type === UserColumnType::affiliation) {
                    // 値無しは所属情報を削除
                    if (empty($value)) {
                        UserSection::where('user_id', $user->id)->delete();
                    } else {
                        UserSection::updateOrCreate(
                            ['user_id' => $user->id],
                            ['section_id' => Section::where('name', $value)->first()->id]
                        );
                    }
                }
            }

            // --- 権限(コンテンツ権限 & 管理権限)
            $view_user_roles_col_no = array_search('view_user_roles', $import_column_col_no);
            // 配列に変換する。nullの場合[0 => ""]になる
            $csv_view_user_roles = explode(UsersTool::CHECKBOX_SEPARATOR, (string)$csv_columns[$view_user_roles_col_no]);
            // 配列値の入力値をトリム (preg_replace(/u)で置換. /u = UTF-8 として処理)
            $csv_view_user_roles = StringUtils::trimInput($csv_view_user_roles);

            // ユーザ権限の更新（権限データの delete & insert）
            $users_roles_ids = UsersRoles::where('users_id', $user->id)->pluck('id');
            UsersRoles::destroy($users_roles_ids);

            foreach ($csv_view_user_roles as $role_name) {
                // bugfix: csv値がnullの場合、explodeすると[0 => ""]になったため対応
                if ($role_name) {
                    UsersRoles::create([
                        'users_id'   => $user->id,
                        'target'     => UsersRoles::getTargetByRole($role_name),
                        'role_name'  => $role_name,
                        'role_value' => 1
                    ]);
                }
            }

            // --- 役割設定
            $user_original_roles_col_no = array_search('user_original_roles', $import_column_col_no);
            // 配列に変換する。
            $csv_user_original_roles_names = explode(UsersTool::CHECKBOX_SEPARATOR, $csv_columns[$user_original_roles_col_no] ?? '');
            // 配列値の入力値をトリム (preg_replace(/u)で置換. /u = UTF-8 として処理)
            $csv_user_original_roles_names = StringUtils::trimInput($csv_user_original_roles_names);

            $user_original_roles = $configs_original_role->whereIn('value', $csv_user_original_roles_names);

            foreach ($user_original_roles as $user_original_role) {
                // bugfix: csv値がnullの場合、explodeすると[0 => ""]になったため対応
                if ($user_original_role) {
                    UsersRoles::create([
                        'users_id'   => $user->id,
                        'target'     => 'original_role',
                        'role_name'  => $user_original_role->name,
                        'role_value' => 1
                    ]);
                }
            }
        }

        // 一時ファイルの削除
        fclose($fp);
        Storage::delete($path);

        return redirect()->back()->with('flash_message', 'インポートしました。');
    }

    /**
     * CSVデータ行チェック
     */
    private function checkCvslines($fp, $users_columns, $group, $import_column_col_no, $configs_original_role, int $columns_set_id)
    {
        // 行頭（固定項目）
        $rules = [
            // id ※ログインユーザは一括処理の対象外
            0 => [
                'nullable',
                'numeric',
                'exists:users,id',
                Rule::notIn([Auth::user()->id])
            ],
            // ログインID. 後でセット
            1 => [],
            // ユーザ名
            2 => 'required|string|max:255',
            // グループ. (グループ名の存在チェック。複数値あり)
            // 3 => new CustomValiCsvExistsGroupName($group),
            3 => new CustomValiCsvExistsName($group->pluck('name')->toArray()),
            // メールアドレス. 後でセット
            4 => [],
            // パスワード. 後でセット
            5 => [],
        ];

        // エラーチェック配列
        $validator_array = array('column' => array(), 'message' => array());

        // 行末（固定項目）
        // 行頭（固定項目）分で+6, 行末に追加で+1 = col+7ずらす
        // 権限
        // \Log::debug('[' . __METHOD__ . '] ' . __FILE__ . ' (line ' . __LINE__ . ')');
        // \Log::debug(var_export($col, true));
        // \Log::debug(var_export($users_columns->count(), true));

        // 固定項目を取り除いたユーザカラム. values()でキーをリセットしたコレクション取得
        $users_columns_not_fixed_column = $users_columns->whereNotIn('column_type', UsersColumns::loopNotShowColumnTypes())->values();

        $col = $users_columns_not_fixed_column->count() - 1;
        // $rules[$col + 7] = ['nullable', Rule::in([
        $rules[$col + 7] = ['nullable', new CustomValiCsvExistsName([
            'role_article_admin',
            'role_arrangement',
            'role_article',
            'role_approval',
            'role_reporter',
            'admin_system',
            'admin_site',
            'admin_page',
            'admin_user',
        ])];

        // 役割設定.  (役割名の存在チェック。複数値あり)
        // $configs_original_role = Configs::where('category', 'original_role')->get();
        // $rules[$col + 8] = ['nullable', new CustomValiCsvExistsRoleName($configs_original_role)];
        $rules[$col + 8] = ['nullable', new CustomValiCsvExistsName($configs_original_role->pluck('value')->toArray())];
        // 状態
        $rules[$col + 9] = ['required', Rule::in(UserStatus::getChooseableKeys())];

        // ヘッダー行が1行目なので、2行目からデータ始まる
        $line_count = 2;
        $errors = [];

        while (($csv_columns = fgetcsv($fp, 0, ',')) !== false) {
            // 入力値をトリム (preg_replace(/u)で置換. /u = UTF-8 として処理)
            $csv_columns = StringUtils::trimInput($csv_columns);

            // $users_id = array_shift($csv_columns);
            $users_id = $csv_columns[0];

            // ユニークチェックを含むバリデーション追加
            // ログインID
            $rules[1] = ['required', 'max:255', Rule::unique('users', 'userid')->ignore($users_id)];
            // メールアドレス
            $rules[4] = ['nullable', 'email', 'max:255', new CustomValiUserEmailUnique($columns_set_id, $users_id)];
            // パスワード
            if ($users_id) {
                // ユーザ変更時
                $rules[5] = 'nullable|string|min:6';
            } else {
                // ユーザ登録時
                $rules[5] = 'required|string|min:6';
            }

            // ユーザの任意項目（メールのユニークチェックで自分以外をチェックするため、ここでチェック追加）
            foreach ($users_columns_not_fixed_column as $col => $users_column) {
                // $validator_array['column']['users_columns_value.' . $users_column->id] = $validator_rule;
                // $validator_array['message']['users_columns_value.' . $users_column->id] = $users_column->column_name;

                // バリデータールールを取得
                $validator_array = UsersTool::getValidatorRule($validator_array, $users_column, $columns_set_id, $users_id);

                // バリデータールールあるか
                if (isset($validator_array['column']['users_columns_value.' . $users_column->id])) {
                    // 行頭（固定項目）の id 分　col をずらすため、+1
                    $rules[$col + 6] = $validator_array['column']['users_columns_value.' . $users_column->id];
                } else {
                    // ルールなしは空配列入れないと、バリデーション項目がずれるのでセット
                    $rules[$col + 6] = [];
                }
            }

            foreach ($csv_columns as $col => &$csv_column) {
                // 空文字をnullに変換
                $csv_column = StringUtils::convertEmptyStringsToNull($csv_column);

                // csv値あり
                if ($csv_column) {
                    // id取り出したので+1
                    // $column_id = $import_column_col_no[$col + 1];
                    $column_id = $import_column_col_no[$col];

                    // intであれば任意項目
                    if (is_int($column_id)) {
                        // 任意項目. 必ずある想定
                        $users_column = $users_columns_not_fixed_column->firstWhere('id', $column_id);

                        // [TODO] ユーザ任意項目のチェックボックスはarray型にしてバリデーションできるけど、権限はarrayでRule::inしてもうまくいかなかった。原因おいきれなかった。
                        // 複数選択型
                        if ($users_column->column_type == UserColumnType::checkbox) {
                            // 複数選択のバリデーションの入力値は、配列が前提のため、配列に変換する。
                            $csv_column = explode(UsersTool::CHECKBOX_SEPARATOR, $csv_column);
                            // 配列値の入力値をトリム (preg_replace(/u)で置換. /u = UTF-8 として処理)
                            $csv_column = StringUtils::trimInput($csv_column);
                            // Log::debug(var_export($csv_column, true));
                        }

                        // 所属型
                        if ($users_column->column_type == UserColumnType::affiliation) {
                            $section = Section::where('name', $csv_column)->first();
                            // マスタにない組織名が設定されたら、後続のバリデーションでエラーになるようにIDとしてありえない文字列を設定する
                            $csv_column = $section ? $section->id : '-';
                        }
                    }
                }
            }

            // 頭のIDをarrayに戻す
            // array_unshift($csv_columns, $users_id);
            // キーの数字で昇順ソート. どうもcsvのバリデーションはrulesのindexは見てなくて、入ってる順番でチェックしてるようなのでこれが必要。
            ksort($rules, SORT_NUMERIC);

            // バリデーション
            $validator = Validator::make($csv_columns, $rules);
            // \Log::debug($line_count . '行目の$csv_columns:' . var_export($csv_columns, true));
            // \Log::debug(var_export($rules, true));

            $attribute_names = [];
            // 行頭（固定項目）
            // id
            $attribute_names[0] = $line_count . '行目のid';
            $attribute_names[1] = $line_count . '行目の' . UsersColumns::getLabelLoginId($users_columns);
            $attribute_names[2] = $line_count . '行目の' . UsersColumns::getLabelUserName($users_columns);
            $attribute_names[3] = $line_count . '行目のグループ';
            $attribute_names[4] = $line_count . '行目の' . UsersColumns::getLabelUserEmail($users_columns);
            $attribute_names[5] = $line_count . '行目の' . UsersColumns::getLabelUserPassword($users_columns);

            // bugfix: 追加項目なしの場合、$colが初期化されないので修正
            $col = -1;

            foreach ($users_columns_not_fixed_column as $col => $users_column) {
                // 行数＋項目名
                // 頭-固定項目 の id 分　col をずらすため、+1
                $attribute_names[$col + 6] = $line_count . '行目の' . $users_column->column_name;
            }
            // 行末（固定項目）
            // 行頭（固定項目）分で+6, 行末に追加で+1 = col+7ずらす
            $attribute_names[$col + 7] = $line_count . '行目の権限';
            $attribute_names[$col + 8] = $line_count . '行目の役割設定';
            $attribute_names[$col + 9] = $line_count . '行目の状態';

            $validator->setAttributeNames($attribute_names);
            // Log::debug(var_export($attribute_names, true));

            if ($validator->fails()) {
                $errors = array_merge($errors, $validator->errors()->all());
                // continue;
            }

            $line_count++;
        }

        return $errors;
    }

    /**
     * 一括削除画面表示
     *
     * @method_title 一括削除
     * @method_desc 仮削除に設定してあるユーザを一括削除できます。
     * @method_detail 安全に一括削除するため、最初に削除対象ユーザを仮削除にしてください。
     */
    public function bulkDelete($request, $id = null)
    {
        $users = User::where('status', UserStatus::temporary_delete)->get();

        // 管理画面プラグインの戻り値の返し方
        return view('plugins.manage.user.bulk_delete', [
            "function" => __FUNCTION__,
            "plugin_name" => "user",
            "users" => $users,
        ]);
    }

    /**
     * （状態=仮削除のユーザを）一括削除処理
     */
    public function bulkDestroy($request, $id = null)
    {
        $user_ids = User::where('status', UserStatus::temporary_delete)->pluck('id');

        // 権限データを削除する。
        $users_roles_ids = UsersRoles::whereIn('users_id', $user_ids)->pluck('id');
        UsersRoles::destroy($users_roles_ids);

        // ユーザ任意追加項目データを削除する。
        $users_input_cols_ids = UsersInputCols::whereIn('users_id', $user_ids)->pluck('id');
        UsersInputCols::destroy($users_input_cols_ids);

        // データを削除する。
        User::destroy($user_ids);

        // 削除後は一括削除画面を呼ぶ。
        return redirect()->back()->with('flash_message', '一括削除しました。');
    }

    /**
     * ログイン履歴画面
     *
     * @method_title ログイン履歴
     * @method_desc 今までのログイン日時を確認できます。
     * @method_detail ログインしてきたIPアドレスやユーザエージェントも確認できます。
     */
    public function loginHistory($request, $id = null)
    {
        // ユーザデータ取得
        $user = User::where('id', $id)->first();

        // ログイン履歴取得
        $users_login_histories = UsersLoginHistories::where('users_id', $id)
                ->orderBy('logged_in_at', 'desc')
                ->paginate(10, ["*"]);

        // 管理画面プラグインの戻り値の返し方
        return view('plugins.manage.user.login_history', [
            "function" => __FUNCTION__,
            "plugin_name" => "user",
            "user" => $user,
            "users_login_histories" => $users_login_histories,
        ]);
    }

    /**
     * 承認完了メールを送信する
     *
     * @param User $user 承認したユーザ
     */
    private function sendMailApproved($user)
    {
        $configs = Configs::where('category', 'general')
            ->orWhere(function ($query) use ($user) {
                $query->where('category', 'user_register')
                    ->where('additional1', $user->columns_set_id);
            })
            ->get();

        // 登録者にメール送信する
        $user_register_user_mail_send_flag = Configs::getConfigsValue($configs, 'user_register_user_mail_send_flag');

        // メール送信
        if ($user_register_user_mail_send_flag && $user->email) {
            // メール件名
            $subject = Configs::getConfigsValue($configs, 'user_register_approved_mail_subject');
            // メール本文
            $mail_format = Configs::getConfigsValue($configs, 'user_register_approved_mail_format');

            // 埋め込みタグ
            $notice_embedded_tags = UsersTool::getNoticeEmbeddedTags($user);

            $subject = UserRegisterNoticeEmbeddedTag::replaceEmbeddedTags($subject, $notice_embedded_tags);
            $mail_text = UserRegisterNoticeEmbeddedTag::replaceEmbeddedTags($mail_format, $notice_embedded_tags);

            // メールオプション
            $mail_options = ['subject' => $subject, 'template' => 'mail.send'];

            $this->sendMail($user->email, $mail_options, ['content' => $mail_text], 'RegistersUsers');
        }
    }

    /**
     * メール送信画面
     *
     * @method_title メール送信
     * @method_desc ユーザ登録後に登録内容のメールを送信できます。
     * @method_detail メールアドレスがあった場合のみ当画面を開きます。
     */
    public function mail($request, $id = null)
    {
        // 画面再表示してもセッション保持
        $request->session()->keep(['password']);

        // ユーザデータ取得
        $user = User::where('id', $id)->first();

        // 本登録メール設定取得
        $configs = Configs::where('category', 'user_register')->where('additional1', $user->columns_set_id)->get();
        $subject = Configs::getConfigsValue($configs, 'user_register_mail_subject', '');
        $body = Configs::getConfigsValue($configs, 'user_register_mail_format', '');

        // 埋め込みタグ
        $notice_embedded_tags = UsersTool::getNoticeEmbeddedTags($user);

        $subject = UserRegisterNoticeEmbeddedTag::replaceEmbeddedTags($subject, $notice_embedded_tags);
        $body = UserRegisterNoticeEmbeddedTag::replaceEmbeddedTags($body, $notice_embedded_tags);

        // 管理画面プラグインの戻り値の返し方
        return view('plugins.manage.user.mail', [
            "function" => __FUNCTION__,
            "plugin_name" => "user",
            "user" => $user,
            "subject" => $subject,
            "body" => $body,
        ]);
    }

    /**
     * メール送信
     */
    public function mailSend($request, $id = null)
    {
        // ユーザデータ取得
        $user = User::where('id', $id)->first();

        // メールオプション
        $mail_options = ['subject' => $request->subject, 'template' => 'mail.send'];

        // メール送信（Trait のメソッド）
        $this->sendMail($user->email, $mail_options, ['content' => $request->body], 'UserManage');

        // ユーザ管理画面に戻る
        return redirect("/manage/user")->with('flash_message', 'メール送信しました。');
    }

    /**
     * 項目セット一覧 初期表示
     *
     * @return view
     * @method_title 項目セット一覧
     * @method_desc ユーザの項目セット一覧を表示、登録します。
     * @method_detail ユーザには、項目セットで設定した項目を割り当てることができます。
     */
    public function columnSets($request, $id = null)
    {
        $page = $this->getPaginatePageFromRequestOrSession($request, 'users_columns_set_page_condition.page', 'page');

        /* データの取得
        ----------------------------------------------*/

        // 項目セット取得
        $columns_sets = UsersColumnsSet::orderBy('display_sequence')->paginate(10, '*', 'page', $page);

        $columns = UsersColumns::whereIn('columns_set_id', $columns_sets->pluck('id'))
            ->orderBy('display_sequence')
            ->get();

        foreach ($columns_sets as $columns_set) {
            // 項目名をセット
            $columns_set->column_name = $columns->where('columns_set_id', $columns_set->id)
                ->pluck('column_name')->implode(',');
        }

        return view('plugins.manage.user.column_sets', [
            "function"      => __FUNCTION__,
            "plugin_name"   => "user",
            "columns_sets"  => $columns_sets,
        ]);
    }

    /**
     * 項目セット 登録画面表示
     *
     * @return view
     * @method_title 項目設定
     * @method_desc 施設の項目セットに項目を登録します。
     * @method_detail 項目名や型、条件などを設定して項目を設定します。
     */
    public function registColumnSet($request)
    {
        return $this->editColumnSet($request, null, 'registColumnSet');
    }

    /**
     * 項目セット 登録処理
     */
    public function storeColumnSet($request)
    {
        return $this->updateColumnSet($request, null);
    }

    /**
     * 項目セット 変更画面表示
     *
     * @return view
     */
    public function editColumnSet($request, $id = null, $function = null)
    {
        $columns_set = UsersColumnsSet::firstOrNew(['id' => $id]);

        $function = $function ?? 'editColumnSet';

        return view('plugins.manage.user.edit_column_set', [
            "function" => $function,
            "plugin_name" => "user",
            "columns_set" => $columns_set,
        ]);
    }

    /**
     * 項目セット 更新処理
     */
    public function updateColumnSet($request, $id)
    {
        // 項目のエラーチェック
        $validator_values = [
            'name' => ['required', 'max:191'],
            'display_sequence' => ['nullable', 'numeric'],
        ];
        $validator_attributes = [
            'name' => '項目セット名',
            'display_sequence' => '表示順',
        ];

        // 変数名の使用で必須チェック
        if ($request->use_variable) {
            $validator_values['variable_name'] = ['required', 'max:255'];
            $validator_attributes['variable_name'] = '変数名';
        }

        // エラーチェック
        $validator = Validator::make($request->all(), $validator_values);
        $validator->setAttributeNames($validator_attributes);

        // エラーがあった場合は入力画面に戻る。
        if ($validator->fails()) {
            return redirect()->back()->withErrors($validator)->withInput();
        }

        // 表示順が空なら、自分を省いた最後の番号+1 をセット
        $display_sequence = UsersColumnsSet::getSaveDisplaySequence(UsersColumnsSet::query(), $request->display_sequence, $id);

        $columns_set = UsersColumnsSet::firstOrNew(['id' => $id]);
        $columns_set->name             = $request->name;
        $columns_set->use_variable     = $request->use_variable ? UseType::use : UseType::not_use;
        if ($request->use_variable) {
            $columns_set->variable_name = $request->variable_name;
        }
        $columns_set->display_sequence = $display_sequence;
        $columns_set->save();

        if ($id) {
            $message = '【 '. $request->name .' 】を変更しました。';
        } else {
            $message = '【 '. $request->name .' 】を登録しました。';

            // UsersColumnsSet登録時のUsersColumns初期登録
            UsersColumns::initInsertForRegistUsersColumnsSet($columns_set->id);
        }

        // 一覧画面に戻る
        return redirect("/manage/user/columnSets")->with('flash_message', $message);
    }

    /**
     * 項目セット 削除処理
     */
    public function destroyColumnSet($request, $id)
    {
        // 項目セットに紐づいてる項目・選択肢はあえて削除しない。

        $columns_set = UsersColumnsSet::find($id);
        $columns_set_name = $columns_set->name;
        $columns_set->delete();

        // 施設一覧画面に戻る
        return redirect("/manage/user/columnSets")->with('flash_message', '【 '. $columns_set_name .' 】を削除しました。');
    }

    /**
     * 項目設定 初期表示
     *
     * @method_title 項目編集
     * @method_desc ユーザ項目の設定を行います。
     * @method_detail カラム名と型を指定してカラムを作成します。
     */
    public function editColumns($request, $id)
    {
        $columns_set = UsersColumnsSet::find($id);
        if (!$columns_set) {
            abort(404, '項目セットデータがありません。');
        }

        // ユーザーのカラム
        $columns = UsersTool::getUsersColumns($id);

        foreach ($columns as &$column) {
            if (UsersColumns::isSelectColumnType($column->column_type)) {
                // 選択肢
                $column->selects = UsersColumnsSelects::where('columns_set_id', $id)
                    ->where('users_columns_id', $column->id)
                    ->orderBy('users_columns_id', 'asc')
                    ->orderBy('display_sequence', 'asc')
                    ->get();
            } else {
                $column->selects = collect();
            }
        }

        return view('plugins.manage.user.edit_columns', [
            "function"             => __FUNCTION__,
            "plugin_name"          => "user",
            'columns_set'          => $columns_set,
            'columns'              => $columns,
            'exists_user_sections' => UserSection::exists(),
        ]);
    }

    /**
     * 項目の登録
     */
    public function addColumn($request, $id)
    {
        // エラーチェック
        $rules = [
            'column_name' => ['required'],
            'column_type' => ['required'],
        ];
        // 所属型は一個まで
        if ($request->column_type === UserColumnType::affiliation) {
            $rules['column_type'][] =  'unique:users_columns';
        }

        $validator = Validator::make($request->all(), $rules);
        $validator->setAttributeNames([
            'column_name' => '項目名',
            'column_type' => '型',
        ]);

        if ($validator->fails()) {
            return redirect()->back()->withErrors($validator)->withInput();
        }

        // 新規登録時の表示順を設定
        $max_display_sequence = UsersColumns::where('columns_set_id', $request->columns_set_id)->max('display_sequence');
        $max_display_sequence = $max_display_sequence ? $max_display_sequence + 1 : 1;

        // 項目の登録処理
        $column = new UsersColumns();
        $column->columns_set_id = $request->columns_set_id;
        $column->column_name = $request->column_name;
        $column->column_type = $request->column_type;
        $message = '項目【 '. $request->column_name .' 】を追加しました。';

        if (UsersColumns::isShowOnlyColumnType($column->column_type)) {
            $column->required = Required::off;
            $message = '項目【 '.$column->column_name.' 】を追加し、表示のみの型のため、必須入力を【 off 】に設定しました。';
        } else {
            // 通常
            $column->required = $request->required ? Required::on : Required::off;
        }

        $column->is_show_auto_regist = ShowType::show;
        $column->is_show_my_page = ShowType::show;
        $column->is_edit_my_page = EditType::ng;
        $column->display_sequence = $max_display_sequence;
        $column->save();

        // 編集画面を呼び出す
        return redirect("/manage/user/editColumns/$request->columns_set_id")->with('flash_message', $message);
    }

    /**
     * 項目の更新
     */
    public function updateColumn($request, $id)
    {
        // 明細行から更新対象を抽出する為のnameを取得
        $str_column_name = "column_name_"."$request->column_id";
        $str_column_type = "column_type_"."$request->column_id";
        $str_required = "required_"."$request->column_id";

        // エラーチェック
        $validator = Validator::make($request->all(), [
            $str_column_name => ['required'],
            $str_column_type => ['required'],
        ]);
        $validator->setAttributeNames([
            $str_column_name => '項目名',
            $str_column_type => '型',
        ]);

        if ($validator->fails()) {
            return redirect()->back()->withErrors($validator)->withInput();
        }

        // 項目の更新処理
        $column = UsersColumns::where('id', $request->column_id)->where('columns_set_id', $request->columns_set_id)->first();
        $column->column_name = $request->$str_column_name;
        $column->column_type = $request->$str_column_type;
        $message = '項目【 '. $column->column_name .' 】を更新しました。';

        if (UsersColumns::isShowOnlyColumnType($column->column_type)) {
            $column->required = Required::off;
            $message = '項目【 '.$column->column_name.' 】を更新し、表示のみの型のため、必須入力を【 off 】に設定しました。';
        } else {
            // 通常
            $column->required = $request->$str_required ? Required::on : Required::off;
        }

        // 固定項目以外
        if (!UsersColumns::isFixedColumnType($column->column_type)) {
            // 必須入力
            if ($column->required == Required::on) {
                $column->is_show_auto_regist = ShowType::show;
                $message = '項目【 '.$column->column_name.' 】を更新し、必須入力のため、自動登録時の表示指定【 '.ShowType::getDescription($column->is_show_auto_regist).' 】を設定しました。';
            }
        }

        $column->save();

        // 編集画面を呼び出す
        return redirect("/manage/user/editColumns/$request->columns_set_id")->with('flash_message', $message);
    }

    /**
     * 項目の表示順の更新
     */
    public function updateColumnSequence($request, $id)
    {
        // ボタンが押された行の施設データ
        $target_column = UsersColumns::where('id', $request->column_id)
            ->where('columns_set_id', $request->columns_set_id)
            ->first();

        // ボタンが押された前（後）の施設データ
        $query = UsersColumns::where('columns_set_id', $request->columns_set_id);
        $pair_column = $request->display_sequence_operation == 'up' ?
            $query->where('display_sequence', '<', $request->display_sequence)->orderby('display_sequence', 'desc')->limit(1)->first() :
            $query->where('display_sequence', '>', $request->display_sequence)->orderby('display_sequence', 'asc')->limit(1)->first();

        // それぞれの表示順を退避
        $target_column_display_sequence = $target_column->display_sequence;
        $pair_column_display_sequence = $pair_column->display_sequence;

        // 入れ替えて更新
        $target_column->display_sequence = $pair_column_display_sequence;
        $target_column->save();
        $pair_column->display_sequence = $target_column_display_sequence;
        $pair_column->save();

        $message = '項目【 '. $target_column->column_name .' 】の表示順を更新しました。';

        // 編集画面を呼び出す
        return redirect("/manage/user/editColumns/$request->columns_set_id")->with('flash_message', $message);
    }

    /**
     * つまんで移動した項目の表示順を更新
     */
    public function updateColumnSequenceAll($request, $page_id, $frame_id)
    {
        DB::beginTransaction();
        try {
            // より安全に更新するため、columns_set_idも指定して、まとめて取得
            $columns = UsersColumns::where('columns_set_id', $request->columns_set_id)->whereIn('id', $request->column_ids_order)->get();

            foreach ($request->column_ids_order as $key => $column_id) {
                $column = $columns->firstWhere('id', $column_id);
                if ($column) {
                    // display_sequenceを1から順に全項目を振り直し
                    $column->display_sequence = $key + 1;
                    $column->save();
                }
            }

            DB::commit();
        } catch (\Exception $e) {
            DB::rollBack();
            throw $e;
        }

        $message = '項目の表示順を更新しました。';

        // 編集画面を呼び出す
        return redirect("/manage/user/editColumns/" . $request->columns_set_id)->with('flash_message', $message);
    }

    /**
     * 項目の削除
     */
    public function deleteColumn($request, $id)
    {
        // 明細行から削除対象の項目名を抽出
        $str_column_name = "column_name_"."$request->column_id";

        // 所属型の関連テーブルを削除
        $users_column = UsersColumns::findOrFail($request->column_id);
        if ($users_column->column_type === UserColumnType::affiliation) {
            UserSection::query()->delete();
            Section::query()->delete();
        }

        // 項目の削除
        UsersColumns::destroy('id', $request->column_id);

        // 項目に紐づく選択肢の削除
        // deleted_id, deleted_nameを自動セットするため、複数件削除する時は collectionのpluck('id')でid配列を取得して destroy()で消す。
        $select_ids = UsersColumnsSelects::where('users_columns_id', $request->column_id)->pluck('id');
        UsersColumnsSelects::destroy($select_ids);

        $message = '項目【 '. $request->$str_column_name .' 】を削除しました。';

        // 編集画面を呼び出す
        return redirect("/manage/user/editColumns/$request->columns_set_id")->with('flash_message', $message);
    }

    /**
     * 項目の設定画面の表示
     *
     * @method_title 項目の詳細編集
     * @method_desc ユーザ項目の詳細設定を行います。
     * @method_detail 入力チェック、キャプションやプレースホルダなどを設定できます。
     */
    public function editColumnDetail($request, $id)
    {
        // --- 画面に値を渡す準備
        $column = UsersColumns::where('id', $id)->first();
        if (!$column) {
            abort(404, 'カラムデータがありません。');
        }

        $columns_set = UsersColumnsSet::find($column->columns_set_id);
        if (!$columns_set) {
            abort(404, '項目セットデータがありません。');
        }

        $selects = UsersColumnsSelects::where('users_columns_id', $column->id)->orderby('display_sequence')->get();
        $select_agree = $selects->first() ?? new UsersColumnsSelects();

        return view('plugins.manage.user.edit_column_detail', [
            "function"     => __FUNCTION__,
            "plugin_name"  => "user",
            'columns_set'  => $columns_set,
            'column'       => $column,
            'selects'      => $selects,
            'select_agree' => $select_agree,
            'sections'     => Section::orderBy('display_sequence')->get(),
        ]);
    }

    /**
     * 項目に紐づく詳細設定の更新
     */
    public function updateColumnDetail($request, $id)
    {
        $validator_values = null;
        $validator_attributes = null;

        // 桁数チェックの指定時、入力値が数値であるかチェック
        if ($request->rule_digits_or_less) {
            $validator_values['rule_digits_or_less'] = [
                'numeric',
            ];
            $validator_attributes['rule_digits_or_less'] = '入力桁数';
        }
        // 最大値の指定時、入力値が数値であるかチェック
        if ($request->rule_max) {
            $validator_values['rule_max'] = [
                'numeric',
            ];
            $validator_attributes['rule_max'] = '最大値';
        }
        // 最小値の指定時、入力値が数値であるかチェック
        if ($request->rule_min) {
            $validator_values['rule_min'] = [
                'numeric',
            ];
            $validator_attributes['rule_min'] = '最小値';
        }
        // 入力文字数の指定時、入力値が数値であるかチェック
        if ($request->rule_word_count) {
            $validator_values['rule_word_count'] = [
                'numeric',
            ];
            $validator_attributes['rule_word_count'] = '入力最大文字数';
        }
        // 変数名の使用で必須チェック
        if ($request->use_variable) {
            $validator_values['variable_name'] = ['required', 'max:255'];
            $validator_attributes['variable_name'] = '変数名';
        }

        // エラーチェック
        if ($validator_values) {
            $validator = Validator::make($request->all(), $validator_values);
            $validator->setAttributeNames($validator_attributes);

            if ($validator->fails()) {
                return redirect()->back()->withErrors($validator)->withInput();
            }
        }


        $column = UsersColumns::where('id', $request->column_id)->where('columns_set_id', $request->columns_set_id)->first();

        // 項目の更新処理
        $column->caption = $request->caption;
        if ($request->caption_color) {
            $column->caption_color = $request->caption_color;
        }
        $column->place_holder = $request->place_holder;
        $column->is_show_auto_regist = $request->is_show_auto_regist ? EditType::ok : EditType::ng;
        $column->is_show_my_page = $request->is_show_my_page ? ShowType::show : ShowType::not_show;
        $column->is_edit_my_page = $request->is_edit_my_page ? EditType::ok : EditType::ng;
        $column->use_variable = $request->use_variable ? UseType::use : UseType::not_use;
        if ($request->use_variable) {
            $column->variable_name = $request->variable_name;
        }
        // 数値のみ許容
        $column->rule_allowed_numeric = (empty($request->rule_allowed_numeric)) ? 0 : $request->rule_allowed_numeric;
        // 英数値のみ許容
        $column->rule_allowed_alpha_numeric = (empty($request->rule_allowed_alpha_numeric)) ? 0 : $request->rule_allowed_alpha_numeric;
        // 入力桁数
        $column->rule_digits_or_less = $request->rule_digits_or_less;
        // 入力文字数
        $column->rule_word_count = $request->rule_word_count;
        // 最大値
        $column->rule_max = $request->rule_max;
        // 最小値
        $column->rule_min = $request->rule_min;
        // 正規表現
        $column->rule_regex = $request->rule_regex;

        // 保存
        $column->save();

        $message = '項目【 '. $column->column_name .' 】の詳細設定を更新しました。';

        return redirect("/manage/user/editColumnDetail/" . $request->column_id)->with('flash_message', $message);
    }

    /**
     * 予約詳細項目（選択肢）の登録
     */
    public function addSelect($request, $id)
    {
        // エラーチェック
        $validator = Validator::make($request->all(), [
            'select_name'  => ['required'],
        ]);
        $validator->setAttributeNames([
            'select_name'  => '選択肢名',
        ]);

        if ($validator->fails()) {
            return redirect()->back()->withErrors($validator)->withInput();
        }

        // 新規登録時の表示順を設定
        $max_display_sequence = UsersColumnsSelects::where('users_columns_id', $request->column_id)->max('display_sequence');
        $max_display_sequence = $max_display_sequence ? $max_display_sequence + 1 : 1;

        // 施設の登録処理
        $select = new UsersColumnsSelects();
        $select->columns_set_id = $request->columns_set_id;
        $select->users_columns_id = $request->column_id;
        $select->value = $request->select_name;
        $select->display_sequence = $max_display_sequence;
        $select->save();
        $message = '選択肢【 '. $request->select_name .' 】を追加しました。';

        // 編集画面を呼び出す
        return redirect("/manage/user/editColumnDetail/" . $request->column_id)->with('flash_message', $message);
    }

    /**
     * 選択肢の更新
     */
    public function updateSelect($request, $id)
    {
        // 明細行から更新対象を抽出する為のnameを取得
        $str_select_name = "select_name_"."$request->select_id";

        // エラーチェック
        $validator = Validator::make($request->all(), [
            $str_select_name => ['required'],
        ]);
        $validator->setAttributeNames([
            $str_select_name => '選択肢名',
        ]);

        if ($validator->fails()) {
            return redirect()->back()->withErrors($validator)->withInput();
        }

        // 項目の更新処理
        $select = UsersColumnsSelects::where('id', $request->select_id)->first();
        $select->value = $request->$str_select_name;
        $select->save();
        $message = '選択肢【 '. $request->$str_select_name .' 】を更新しました。';

        // 編集画面を呼び出す
        return redirect("/manage/user/editColumnDetail/" . $request->column_id)->with('flash_message', $message);
    }

    /**
     * 選択肢の表示順の更新
     */
    public function updateSelectSequence($request, $id)
    {
        // ボタンが押された行の選択肢データ
        $target_select = UsersColumnsSelects::where('id', $request->select_id)->first();

        // ボタンが押された前（後）の選択肢データ
        $query = UsersColumnsSelects::where('users_columns_id', $request->column_id)->where('columns_set_id', $request->columns_set_id);
        $pair_select = $request->display_sequence_operation == 'up' ?
            $query->where('display_sequence', '<', $request->display_sequence)->orderby('display_sequence', 'desc')->limit(1)->first() :
            $query->where('display_sequence', '>', $request->display_sequence)->orderby('display_sequence', 'asc')->limit(1)->first();

        // それぞれの表示順を退避
        $target_select_display_sequence = $target_select->display_sequence;
        $pair_select_display_sequence = $pair_select->display_sequence;

        // 入れ替えて更新
        $target_select->display_sequence = $pair_select_display_sequence;
        $target_select->save();
        $pair_select->display_sequence = $target_select_display_sequence;
        $pair_select->save();

        $message = '選択肢【 '. $target_select->select_name .' 】の表示順を更新しました。';

        // 編集画面を呼び出す
        return redirect("/manage/user/editColumnDetail/" . $request->column_id)->with('flash_message', $message);
    }

    /**
     * つまんで移動した選択肢の表示順を更新
     */
    public function updateSelectSequenceAll($request, $page_id, $frame_id)
    {
        DB::beginTransaction();
        try {
            // より安全に更新するため、columns_set_idも指定して、まとめて取得
            $selects = UsersColumnsSelects::where('columns_set_id', $request->columns_set_id)->whereIn('id', $request->select_ids_order)->get();

            foreach ($request->select_ids_order as $key => $select_id) {
                $select = $selects->firstWhere('id', $select_id);
                if ($select) {
                    // display_sequenceを1から順に全選択肢を振り直し
                    $select->display_sequence = $key + 1;
                    $select->save();
                }
            }

            DB::commit();
        } catch (\Exception $e) {
            DB::rollBack();
            throw $e;
        }

        $message = '選択肢の表示順を更新しました。';

        // 編集画面を呼び出す
        return redirect("/manage/user/editColumnDetail/" . $request->column_id)->with('flash_message', $message);
    }

    /**
     * 同意内容の更新
     */
    public function updateAgree($request, $id)
    {
        // エラーチェック
        $validator = Validator::make($request->all(), [
            'value' => ['required'],
        ]);
        $validator->setAttributeNames([
            'value' => 'チェックボックスの名称',
        ]);

        if ($validator->fails()) {
            return redirect()->back()->withErrors($validator)->withInput();
        }

        // 項目の更新処理
        $select = UsersColumnsSelects::where('id', $request->select_id)->firstOrNew([]);
        $select->columns_set_id = $request->columns_set_id;
        $select->users_columns_id = $request->column_id;
        $select->value = $request->value;
        $select->agree_description = $request->agree_description;
        $select->display_sequence = 1;
        $select->save();
        $message = '同意内容を更新しました。';

        // 編集画面を呼び出す
        return redirect("/manage/user/editColumnDetail/" . $request->column_id)->with('flash_message', $message);
    }

    /**
     * 項目に紐づく選択肢の削除
     */
    public function deleteSelect($request, $id)
    {
        // 削除
        UsersColumnsSelects::destroy('id', $request->select_id);

        // 明細行から削除対象の選択肢名を抽出
        $str_select_name = "select_name_"."$request->select_id";
        $message = '選択肢【 '. $request->$str_select_name .' 】を削除しました。';

        // 編集画面を呼び出す
        return redirect("/manage/user/editColumnDetail/" . $request->column_id)->with('flash_message', $message);
    }

    /**
     * 組織の登録
     */
    public function addSection($request, $id)
    {
        // エラーチェック
        $validator = Validator::make($request->all(), [
            'section_name'  => ['required', 'max:191', Rule::unique('sections', 'name')],
            'section_code'  => ['max:191'],
        ]);
        $validator->setAttributeNames([
            'section_name'  => '組織名',
            'section_code'  => 'コード',
        ]);

        if ($validator->fails()) {
            return redirect()->back()->withErrors($validator)->withInput();
        }

        // 新規登録時の表示順を設定
        $max_display_sequence = Section::max('display_sequence');
        $max_display_sequence = $max_display_sequence ? $max_display_sequence + 1 : 1;

        // 組織の登録処理
        $section = new Section();
        $section->name = $request->section_name;
        $section->code = $request->section_code;
        $section->display_sequence = $max_display_sequence;
        $section->save();
        $message = '組織【 '. $request->section_name .' 】を追加しました。';

        // 編集画面を呼び出す
        return redirect("/manage/user/editColumnDetail/" . $request->column_id)->with('flash_message', $message);
    }

    /**
     * 組織の更新
     */
    public function updateSection($request, $id)
    {
        // 明細行から更新対象を抽出する為のnameを取得
        $str_section_name = "section_name_"."$request->section_id";
        $str_section_code = "section_code_"."$request->section_id";

        // エラーチェック
        $validator = Validator::make($request->all(), [
            $str_section_name => ['required', 'max:191', Rule::unique('sections', 'name')->ignore($request->section_id)],
            $str_section_code => ['max:191'],
        ]);
        $validator->setAttributeNames([
            $str_section_name => '組織名',
            $str_section_code => 'コード',
        ]);

        if ($validator->fails()) {
            return redirect()->back()->withErrors($validator)->withInput();
        }

        // 項目の更新処理
        $section = Section::where('id', $request->section_id)->first();
        $before_name = $section->name;
        $section->name = $request->$str_section_name;
        $section->code = $request->$str_section_code;
        $section->save();

        // users_input_colsに登録されているデータを更新内容に合わせる
        $column = UsersColumns::where('column_type', UserColumnType::affiliation)->first();
        UsersInputCols::query()
            ->where('users_columns_id', $column->id)
            ->where('value', $before_name)
            ->update(['value' => $request->$str_section_name]);

        $message = '組織【 '. $request->$str_section_name .' 】を更新しました。';

        // 編集画面を呼び出す
        return redirect("/manage/user/editColumnDetail/" . $request->column_id)->with('flash_message', $message);
    }

    /**
     * 組織の表示順の更新
     */
    public function updateSectionSequence($request, $id)
    {
        // ボタンが押された行の組織データ
        $target_section = Section::where('id', $request->section_id)->first();

        // ボタンが押された前（後）の組織データ
        $query = Section::query();
        $pair_section = $request->display_sequence_operation == 'up' ?
            $query->where('display_sequence', '<', $request->display_sequence)->orderby('display_sequence', 'desc')->limit(1)->first() :
            $query->where('display_sequence', '>', $request->display_sequence)->orderby('display_sequence', 'asc')->limit(1)->first();

        // それぞれの表示順を退避
        $target_section_display_sequence = $target_section->display_sequence;
        $pair_section_display_sequence = $pair_section->display_sequence;

        // 入れ替えて更新
        $target_section->display_sequence = $pair_section_display_sequence;
        $target_section->save();
        $pair_section->display_sequence = $target_section_display_sequence;
        $pair_section->save();

        $message = '組織【 '. $target_section->section_name .' 】の表示順を更新しました。';

        // 編集画面を呼び出す
        return redirect("/manage/user/editColumnDetail/" . $request->column_id)->with('flash_message', $message);
    }

    /**
     * つまんで移動した組織の表示順を更新
     */
    public function updateSectionSequenceAll($request, $page_id, $frame_id)
    {
        DB::beginTransaction();
        try {
            // まとめて取得
            $sections = Section::whereIn('id', $request->section_ids_order)->get();

            foreach ($request->section_ids_order as $key => $section_id) {
                $section = $sections->firstWhere('id', $section_id);
                if ($section) {
                    // display_sequenceを1から順に全組織を振り直し
                    $section->display_sequence = $key + 1;
                    $section->save();
                }
            }

            DB::commit();
        } catch (\Exception $e) {
            DB::rollBack();
            throw $e;
        }

        $message = '組織の表示順を更新しました。';

        // 編集画面を呼び出す
        return redirect("/manage/user/editColumnDetail/" . $request->column_id)->with('flash_message', $message);
    }

    /**
     * 項目に紐づく組織の削除
     */
    public function deleteSection($request, $id)
    {
        // 削除
        Section::destroy('id', $request->section_id);

        // 明細行から削除対象の組織名を抽出
        $str_section_name = "section_name_"."$request->section_id";
        $message = '組織【 '. $request->$str_section_name .' 】を削除しました。';

        // 編集画面を呼び出す
        return redirect("/manage/user/editColumnDetail/" . $request->column_id)->with('flash_message', $message);
    }
}
