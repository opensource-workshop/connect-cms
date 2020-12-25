<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Traits\ConnectCommonTrait;

use Illuminate\Foundation\Auth\AuthenticatesUsers;
//use App\Traits\ConnectAuthenticatesUsers;
use Illuminate\Http\Request;
//use Illuminate\Support\Facades\Log;

// ログインエラーをCatch するために追加。
use Illuminate\Validation\ValidationException;

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
    protected $redirectTo = '/';
    //protected $redirectTo = '/home';

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

        // 外部認証の確認と外部認証の場合は関数側で認証してトップページを呼ぶ
        // 外部認証でない場合は戻ってくる。
        //
        // bugfix: $this->authMethod($request) メソッド内の return redirect("/"); は、すぐさまリダイレクトするのではなく、RedirectResponseオブジェクトを返して、後続は続行される。
        // RedirectResponseオブジェクトありの場合は、ちゃんとreturnしてあげないと、1度目は処理されず白画面->同じURLをreloadすると2度目でログインとバグが出る。
        // $this->authMethod($request);
        $redirect = $this->authMethod($request);
        if (!empty($redirect)) {
            return $redirect;
        }

        // 以下はもともとのAuthenticatesUsers@login 処理
        //return $this->laravelLogin($request);

        // ログインエラーの場合、NetCommons2 からの移行ユーザとして再度認証する。
        try {
            return $this->laravelLogin($request);
        } catch (ValidationException $e) {
            // 認証OK なら関数内でリダイレクトする。
            // bugfix
            // $this->authNetCommons2Password($request);
            $redirectNc2 = $this->authNetCommons2Password($request);
            if (!empty($redirectNc2)) {
                return $redirectNc2;
            }
    
            // ここに来るということは、NetCommons2 認証もNG
            throw $e;
        }
    }
}
