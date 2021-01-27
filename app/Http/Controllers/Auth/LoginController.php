<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Providers\RouteServiceProvider;
use App\Traits\ConnectCommonTrait;

use Illuminate\Foundation\Auth\AuthenticatesUsers;
//use App\Traits\ConnectAuthenticatesUsers;
use Illuminate\Http\Request;
//use Illuminate\Support\Facades\Log;

// ログインエラーをCatch するために追加。
use Illuminate\Validation\ValidationException;

use App\Models\Core\Configs;

class LoginController extends Controller
{
    /*
    |--------------------------------------------------------------------------
    | Login Controller
    |--------------------------------------------------------------------------
    |
    | This controller handles authenticating users for the application and
    | redirecting them to your home screen. The controller uses a trait
    | to conveniently provide its functionality to your applications.
    |
    */

    //use AuthenticatesUsers;
    use AuthenticatesUsers { login as laravelLogin;
    }
    use ConnectCommonTrait;

    /**
     * Where to redirect users after login.
     *
     * @var string
     */
    protected $redirectTo = RouteServiceProvider::HOME;

    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->middleware('guest')->except('logout');
    }

    /**
     * 認証カラムの変更
     *
     * @return カラム
     */
    public function username()
    {
        return 'userid';
    }

    /**
     * login 処理
     *
     * Illuminate\Foundation\Auth\AuthenticatesUsers からlogin 関数だけ移植
     * 存在しないユーザでも、外部認証機能を使っている場合は、自動でユーザを作成するため。
     */
    public function login(Request $request)
    {
        // 利用可能かチェック
        if (!$this->checkUserStstus($request, $error_msg)) {
            throw ValidationException::withMessages([
                $this->username() => [$error_msg],
            ]);
        }

        // 外部認証を使用
        $use_auth_method = Configs::where('name', 'use_auth_method')->first();

        if (empty($use_auth_method) || $use_auth_method->value == '0') {
            // 外部認証を使用しない(通常)
            //
            // 以下はもともとのAuthenticatesUsers@login 処理
            //return $this->laravelLogin($request);

            try {
                return $this->laravelLogin($request);
            } catch (ValidationException $e) {
                // ログインエラーの場合、NetCommons2 からの移行ユーザとして再度認証する。
                // bugfix
                // $this->authNetCommons2Password($request);
                $redirectNc2 = $this->authNetCommons2Password($request);
                if (!empty($redirectNc2)) {
                    return $redirectNc2;
                }

                // ここに来るということは、NetCommons2 からの移行パスワードでの認証もNG
                throw $e;
            }
        } else {
            // 外部認証を使用する
            //
            // 外部認証の確認と外部認証の場合は関数側で認証してトップページを呼ぶ
            // Shibboleth認証の場合は戻ってくる。
            //
            // bugfix: $this->authMethod($request) メソッド内の return redirect("/"); は、すぐさまリダイレクトするのではなく、RedirectResponseオブジェクトを返して、後続は続行される。
            // RedirectResponseオブジェクトありの場合は、ちゃんとreturnしてあげないと、1度目は処理されず白画面->同じURLをreloadすると2度目でログインとバグが出る。
            // $this->authMethod($request);
            $redirect = $this->authMethod($request);
            if (!empty($redirect)) {
                return $redirect;
            }

            // 外部認証と併せて、通常ログインも使用
            $configs_use_normal_login_along_with_auth_method = Configs::where('name', 'use_normal_login_along_with_auth_method')->first();
            $use_normal_login_along_with_auth_method = empty($configs_use_normal_login_along_with_auth_method) ? null : $configs_use_normal_login_along_with_auth_method->value;

            if ($use_normal_login_along_with_auth_method) {
                // 通常ログインも使用する
                try {
                    // 以下はもともとのAuthenticatesUsers@login 処理
                    return $this->laravelLogin($request);
                } catch (ValidationException $e) {
                    // ログインエラーの場合、NetCommons2 からの移行ユーザとして再度認証する。
                    $redirectNc2 = $this->authNetCommons2Password($request);
                    if (!empty($redirectNc2)) {
                        return $redirectNc2;
                    }

                    // ここに来るということは、NetCommons2 からの移行パスワードでの認証もNG
                    throw $e;
                }
            } else {
                // 通常ログインを使用しない
                //
                // ここに来るということは、ログインエラー
                // NetCommons2認証で通常ログインを使用しない場合、NC2パスワード間違いでここに到達する。
                // Shibboleth認証で通常ログインを使用しない場合、通常ログインは無条件でここに到達する。
                $error_msg = "ログインできません。";
                throw ValidationException::withMessages([
                    $this->username() => [$error_msg],
                ]);
            }
        }
    }

    /**
     * shibboleth認証
     */
    public function shibboleth(Request $request)
    {
        // 外部認証の確認と外部認証の場合は関数側で認証してトップページを呼ぶ
        // 外部認証でない場合は戻ってくる。
        //
        // メソッド内の return redirect("/"); は、すぐさまリダイレクトするのではなく、RedirectResponseオブジェクトを返して、後続は続行される。
        // RedirectResponseオブジェクトなしの場合は、shibboleth認証設定なしとしてエラーにする。
        // RedirectResponseオブジェクトありの場合は、ちゃんとreturnしてあげないと、1度目は処理されず白画面->同じURLをreloadすると2度目でログインとバグが出る。
        $redirect = $this->authMethodShibboleth($request);
        if (empty($redirect)) {
            abort(403, "外部認証を使用しないため、表示できません。");
        }
        return $redirect;
    }
}
