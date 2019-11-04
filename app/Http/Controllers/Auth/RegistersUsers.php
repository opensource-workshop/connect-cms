<?php

namespace App\Http\Controllers\Auth;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Auth\Events\Registered;
use Illuminate\Foundation\Auth\RedirectsUsers;

// Connect-CMS 用設定データ
use App\Models\Core\Configs;
use App\Models\Core\UsersRoles;
use App\Traits\ConnectCommonTrait;

trait RegistersUsers
{
    use RedirectsUsers;
    use ConnectCommonTrait;

    /**
     * Show the application registration form.
     *
     * @return \Illuminate\Http\Response
     */
    public function showRegistrationForm()
    {

        // ユーザー登録関連設定の取得
        $configs = Configs::where('category', 'user_register')->get();
        $configs_array = array();
        foreach ($configs as $config) {
            $configs_array[$config['name']] = $config['value'];
        }

        // ログインしているユーザー情報を取得
        //$user = Auth::user();

        // ユーザ登録の権限があればOK
        //if (isset($user) && ($user->role == 1 || $user->role == 3)) {
        if ($this->isCan('admin_user')) {
            // OK で画面へ
        }
        // 未ログインの場合は、ユーザー登録が許可されていなければ、認証エラーとする。
        else if ($configs_array['user_register_enable'] != "1") {
            abort(403);
        }

        // フォームの初期値として空のユーザオブジェクトを渡す。
        return view('auth.register',[
            "user" => new User(),
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
//Log::debug("register start.");
        $this->validator($request->all())->validate();

        // ユーザー登録関連設定の取得
        $configs = Configs::where('category', 'user_register')->get();
        $configs_array = array();
        foreach ($configs as $config) {
            $configs_array[$config['name']] = $config['value'];
        }

        // ユーザ登録の権限があればOK
        //if (isset($user) && ($user->role == 1 || $user->role == 3)) {
        if ($this->isCan('admin_user')) {
            // OK で画面へ
        }
        // 未ログインの場合は、ユーザー登録が許可されていなければ、認証エラーとする。
        else if ($configs_array['user_register_enable'] != "1") {
            //Log::debug("register 403.");
            abort(403);
        }

        // ユーザーデータ登録
        event(new Registered($user = $this->create($request->all())));

        // ユーザ権限の登録
        if (!empty($request->base)) {
            foreach($request->base as $role_name => $value) {
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
            foreach($request->manage as $role_name => $value) {
                UsersRoles::create([
                    'users_id'   => $user->id,
                    'target'     => 'manage',
                    'role_name'  => $role_name,
                    'role_value' => 1
                ]);
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
