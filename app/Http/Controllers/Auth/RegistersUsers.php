<?php

namespace App\Http\Controllers\Auth;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
// use Illuminate\Support\Facades\Log;
use Illuminate\Auth\Events\Registered;
use Illuminate\Foundation\Auth\RedirectsUsers;

// Connect-CMS 用設定データ
use App\User;
use App\Models\Core\Configs;
use App\Models\Core\UsersRoles;
use App\Traits\ConnectCommonTrait;

use App\Plugins\Manage\UserManage\UsersTool;

trait RegistersUsers
{
    use RedirectsUsers;
    use ConnectCommonTrait;

    /**
     * Show the application registration form.
     * ユーザー登録画面表示（自動登録）
     *
     * @return \Illuminate\Http\Response
     */
    public function showRegistrationForm()
    {
        // ユーザー登録関連設定の取得
        $configs = Configs::where('category', 'general')->orWhere('category', 'user_register')->get();
        $configs_array = array();
        foreach ($configs as $config) {
            $configs_array[$config['name']] = $config['value'];
        }
        $configs = $configs_array;

        // ログインしているユーザー情報を取得
        //$user = Auth::user();

        // ユーザ登録の権限チェック
        //if (isset($user) && ($user->role == 1 || $user->role == 3)) {
        if ($this->isCan('admin_user')) {
            // ユーザ登録の権限があればOK
        } elseif ($configs_array['user_register_enable'] != "1") {
            // 未ログインの場合は、ユーザー登録が許可されていなければ、認証エラーとする。
            abort(403);
        }

        //// ユーザの追加項目.
        // ユーザーのカラム
        $users_columns = UsersTool::getUsersColumns();
        // カラムの選択肢
        $users_columns_id_select = UsersTool::getUsersColumnsSelects();
        // カラムの登録データ
        $input_cols = null;

        // フォームの初期値として空のユーザオブジェクトを渡す。
        return view('auth.register', [
            "user" => new User(),
            "configs" => $configs,
            'users_columns' => $users_columns,
            'users_columns_id_select' => $users_columns_id_select,
            'input_cols' => $input_cols,
        ]);
    }

    /**
     * Handle a registration request for the application.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function register(Request $request)
    {
        $this->validator($request->all())->validate();

        // ユーザー登録関連設定の取得
        $configs = Configs::where('category', 'user_register')->get();
        $user_register_enable = $configs->firstWhere('name', 'user_register_enable');

        // ユーザ登録の権限チェック
        //if (isset($user) && ($user->role == 1 || $user->role == 3)) {
        if ($this->isCan('admin_user')) {
            // ユーザ登録の権限があればOK
        } elseif ($user_register_enable->value != "1") {
            // 未ログインの場合は、ユーザー登録が許可されていなければ、認証エラーとする。
            //Log::debug("register 403.");
            abort(403);
        }

        // ユーザーデータ登録
        event(new Registered($user = $this->create($request->all())));

        // ユーザー管理権限がある場合は、各権限の付与
        if ($this->isCan('admin_user')) {
            // ユーザ権限の登録
            if (!empty($request->base)) {
                foreach ($request->base as $role_name => $value) {
                    UsersRoles::create([
                        'users_id'   => $user->id,
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
                        'users_id'   => $user->id,
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
                        'users_id'   => $user->id,
                        'target'     => 'original_role',
                        'role_name'  => $original_role,
                        'role_value' => 1
                    ]);
                }
            }
        }

        // ユーザー自動登録（未ログイン）の場合、.env のSELF_REGISTER_ROLE を元に権限登録する。
        if (!Auth::user()) {
            $self_register_base_roles_env = config('connect.SELF_REGISTER_BASE_ROLES');
            $self_register_base_roles = array();
            if (!empty($self_register_base_roles_env)) {
                $self_register_base_roles = explode(',', $self_register_base_roles_env);
            }
            if (!empty($self_register_base_roles)) {
                foreach ($self_register_base_roles as $self_register_base_role) {
                    UsersRoles::create([
                        'users_id'   => $user->id,
                        'target'     => 'base',
                        'role_name'  => $self_register_base_role,
                        'role_value' => 1
                    ]);
                }
            }
        }

        // ユーザー自動登録（未ログイン）の場合の登録完了メッセージ。
        if (!Auth::user()) {
            // change: ユーザ仮登録対応
            // session()->flash('flash_message_for_header', 'ユーザ登録が完了しました。登録したログインID、パスワードでログインしてください。');
            //
            // 登録者に仮登録メールを送信する
            $user_register_temporary_regist_mail_flag = $configs->firstWhere('name', 'user_register_temporary_regist_mail_flag');
            if ($user_register_temporary_regist_mail_flag->value) {
                // 仮登録
                $user_register_temporary_regist_after_message = $configs->firstWhere('name', 'user_register_temporary_regist_after_message');
                session()->flash('flash_message_for_header', $user_register_temporary_regist_after_message->value);
            } else {
                // 本登録
                $user_register_after_message = $configs->firstWhere('name', 'user_register_after_message');
                session()->flash('flash_message_for_header', $user_register_after_message->value);
            }
        }

        // 作成したユーザでのログイン処理は行わない。mod by nagahara@opensource-workshop.jp
        // $this->guard()->login($user);

        //Log::debug("register end brfore.");
        return $this->registered($request, $user)
                        ?: redirect($this->redirectPath());
    }

    /**
     * Get the guard to be used during registration.
     *
     * @return \Illuminate\Contracts\Auth\StatefulGuard
     */
    protected function guard()
    {
        return Auth::guard();
    }

    /**
     * The user has been registered.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  mixed  $user
     * @return mixed
     */
    protected function registered(Request $request, $user)
    {
        //
    }
}
