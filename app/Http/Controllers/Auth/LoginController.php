<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Providers\RouteServiceProvider;

use Illuminate\Foundation\Auth\AuthenticatesUsers;
//use App\Traits\ConnectAuthenticatesUsers;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

// ログインエラーをCatch するために追加。
use Illuminate\Validation\ValidationException;

use App\Plugins\Manage\UserManage\UsersTool;

use App\User;
use App\Models\Core\Configs;
use App\Models\Core\UsersRoles;

use App\Enums\AuthMethodType;
use App\Enums\AuthLdapDnType;
use App\Enums\BaseLoginRedirectPage;
use App\Enums\UserStatus;

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
        // exceptで指定されたメソッドは除外する
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
        // 外部認証を使用
        $use_auth_method = Configs::where('name', 'use_auth_method')->first();

        if (empty($use_auth_method) || $use_auth_method->value == '0') {
            // 外部認証を使用しない(通常)

            // 利用可能かチェック
            if (!$this->checkUserStatus($request, $error_msg)) {
                throw ValidationException::withMessages([
                    $this->username() => [$error_msg],
                ]);
            }

            try {
                // 以下はもともとのAuthenticatesUsers@login 処理
                return $this->laravelLogin($request);
            } catch (ValidationException $e) {
                // ログインエラーの場合、NetCommons2 からの移行ユーザとして再度認証する。
                $redirectNc2 = $this->authNetCommons2Password($request);
                if (!empty($redirectNc2)) {
                    return $redirectNc2;
                }

                // NetCommons3 移行ユーザ認証
                $redirectNc3 = $this->authNetCommons3Password($request);
                if (!empty($redirectNc3)) {
                    return $redirectNc3;
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

            // 利用可能かチェック
            if (!$this->checkUserStatus($request, $error_msg)) {
                throw ValidationException::withMessages([
                    $this->username() => [$error_msg],
                ]);
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

    /**
     * Show the application's login form.
     *
     * @return \Illuminate\Http\Response
     */
    public function showLoginForm()
    {
        // ログイン後に移動するページ 設定
        $configs = Configs::where('category', 'general')->get();
        $base_login_redirect_previous_page = Configs::getConfigsValue($configs, 'base_login_redirect_previous_page');

        if ($base_login_redirect_previous_page == BaseLoginRedirectPage::previous_page) {
            // ログイン時に元いたページに遷移
            if (array_key_exists('HTTP_REFERER', $_SERVER)) {
                $path = parse_url($_SERVER['HTTP_REFERER']); // URLを分解
                if (array_key_exists('host', $path)) {
                    if ($path['host'] == $_SERVER['HTTP_HOST']) { // ホスト部分が自ホストと同じ
                        session(['url.intended' => $_SERVER['HTTP_REFERER']]);
                    }
                }
            }
        } elseif ($base_login_redirect_previous_page == BaseLoginRedirectPage::specified_page) {
            // 指定したページに遷移
            $base_login_redirect_select_page = Configs::getConfigsValue($configs, 'base_login_redirect_select_page', RouteServiceProvider::HOME);
            session(['url.intended' => $base_login_redirect_select_page]);
        }

        // サイトテーマ詰込
        $configs = Configs::getSharedConfigs();
        $base_theme = Configs::getConfigsValue($configs, 'base_theme', null);
        $additional_theme = Configs::getConfigsValue($configs, 'additional_theme', null);
        $themes = [
            'css' => $base_theme,
            'js' => $base_theme,
            'additional_css' => $additional_theme,
            'additional_js' => $additional_theme,
        ];

        return view('auth.login', [
            'themes' => $themes,
        ]);
    }

    /**
     * 利用可能かチェック
     * 戻り値：true なら
     */
    private function checkUserStatus($request, &$error_msg = "")
    {
        // userid は必要
        if (!$request->filled('userid')) {
            $error_msg = "ログインできません。";
            return false;
        }

        // ユーザが存在しなければfalse
        $user = User::where('userid', $request->userid)->first();
        if (empty($user)) {
            $error_msg = "ログインできません。";
            return false;
        }

        // 利用不可・仮登録・仮削除ならfalse
        if ($user->status == UserStatus::not_active ||
                $user->status == UserStatus::temporary ||
                $user->status == UserStatus::temporary_delete ||
                $user->status == UserStatus::pending_approval) {

            $error_msg = UserStatus::getDescription($user->status) . "のため、ログインできません。";
            return false;
        }

        return true;
    }

    /**
     * 外部認証
     */
    public function authMethod($request)
    {
        // 使用する外部認証 取得
        $auth_method_event = Configs::getAuthMethodEvent();

        // 外部認証がない場合は戻る
        if (empty($auth_method_event->value)) {
            return;
        }

        // NetCommons2 認証
        if ($auth_method_event->value == AuthMethodType::netcommons2) {
            // 外部認証設定 取得
            $auth_method = Configs::where('name', 'auth_method')->where('value', AuthMethodType::netcommons2)->first();

            // リクエストURLの組み立て
            $request_url = $auth_method['additional1'] . '?action=connectauthapi_view_main_init&login_id=' . $request["userid"] . '&site_key=' . $auth_method['additional2'] . '&check_value=' . md5(md5($request['password']) . $auth_method['additional3']);
            // Log::debug($request['password']);
            // Log::debug($auth_method['additional3']);
            // Log::debug(md5($request['password']));
            // Log::debug(md5(md5($request['password']) . $auth_method['additional3']));
            // Log::debug($request_url);

            // NC2 をCall
            $ch = curl_init();
            curl_setopt($ch, CURLOPT_URL, $request_url);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            $return_json = curl_exec($ch);

            // JSON データを複合化
            $check_result = json_decode($return_json, true);

            // 戻り値のチェック
            if (is_array($check_result) && array_key_exists('check', $check_result) && array_key_exists('ret_value', $check_result)) {
                if ($check_result['check'] == true && $check_result['ret_value'] == md5($request['userid'] . $auth_method['additional3'])) {
                    // ログインするユーザの存在を確認
                    $user = User::where('userid', $request['userid'])->first();

                    // ユーザが存在しない
                    if (empty($user)) {
                        // ユーザが存在しない場合、ログインのみ権限でユーザを作成して、自動ログイン
                        $user = new User;
                        $user->name = empty($check_result['handle']) ? $request['userid'] : $check_result['handle'];
                        $user->userid = $request['userid'];
                        $user->password = Hash::make($request['password']);
                        $user->created_event = AuthMethodType::netcommons2;
                        $user->columns_set_id = UsersTool::COLUMNS_SET_ID_DEFAULT;
                        $user->save();

                        // 追加権限設定があれば作成
                        if (!empty($auth_method['additional4'])) {

                            // NC2側権限値取得
                            $nc2_auth = "";
                            if (array_key_exists('auth', $check_result) == true) {
                                $nc2_auth = $check_result['auth'];
                            }

                            // |で区切る（|は複数権限の設定がある場合に区切り文字として利用される）
                            $set_rols = "";
                            $rols_options_list = explode('|', $auth_method['additional4']);
                            foreach ($rols_options_list as $value) {
                                // :で区切る（:は、NC2側権限とConnectCMS側権限の区切り文字として利用される）
                                $original_rols_options = explode(':', $value);

                                // 一致した権限を設定する
                                if ($original_rols_options[0] == $nc2_auth) {
                                    $set_rols = $original_rols_options[1];
                                    break;
                                }
                            }

                            UsersRoles::create([
                                'users_id'   => $user->id,
                                'target'     => 'original_role',
                                'role_name'  => $set_rols,
                                'role_value' => 1
                            ]);
                        }
                    } else {
                        // ユーザ登録が既にある場合、そのユーザが利用可能になっているかどうかをチェックし、利用不可になっている場合は処理を戻す
                        if ($user->status != UserStatus::active) {
                            return;
                        }
                    }

                    // ログイン
                    Auth::login($user, true);
                    // トップページへ
                    return redirect("/");
                }
            }

        } elseif ($auth_method_event->value == AuthMethodType::ldap) {
            // LDAP 認証

            // php-ldapが有効でなければ、ここで戻す. 戻さないと、Call to undefined function App\\Traits\\ldap_connect()エラーでログインできなくなる。
            if (! function_exists('ldap_connect')) {
                $this->errorLogAndFlashMessageForHeader('LDAP認証ONですがphp_ldapが無効なため、LDAP認証できませんでした。');
                return;
            }

            // 外部認証設定 取得
            $auth_method = Configs::firstOrNew(['name' => 'auth_method', 'value' => AuthMethodType::ldap]);

            // ldap バインドを使用する
            if ($auth_method->additional2 == AuthLdapDnType::active_directory) {
                // Active Directoryタイプ例：test001@example.com
                $ldaprdn = $request->userid . "@" . $auth_method->additional3;     // ldap rdn あるいは dn
            } else {
                // DNタイプ例：uid=test001,ou=People,dc=example,dc=com
                $ldaprdn = "uid=" . $request->userid . "," . $auth_method->additional3;
            }

            // ldap サーバーに接続する
            // $ldapconn = ldap_connect("ldap://localhost:389") or die("Could not connect to LDAP server.");
            $ldapconn = ldap_connect($auth_method->additional1);

            if ($ldapconn) {

                if (! ldap_set_option($ldapconn, LDAP_OPT_PROTOCOL_VERSION, 3)) {
                    ldap_close($ldapconn);
                    $this->errorLogAndFlashMessageForHeader('LDAPのプロトコルバージョンを 3 に設定できませんでした。');
                    return;
                }

                // ldap サーバーにバインドする
                //   システム管理者等、LDAPにいないユーザだと ldap_bind(): Unable to bind to server: Invalid credentialsエラーが出るため @ でエラー抑止する。
                //   LDAPサーバに繋げないエラー     Warning: ldap_bind(): Unable to bind to server: Can't contact LDAP server も @ で抑止され,falseが返ってくる。
                $ldapbind = @ldap_bind($ldapconn, $ldaprdn, $request->password);

                // バインド結果を検証する
                if ($ldapbind) {
                    // LDAP認証OK

                    // 以降でldap操作しないため、ここでクローズする。
                    ldap_close($ldapconn);

                    // ログインするユーザの存在を確認
                    $user = User::where('userid', $request->userid)->first();

                    // ユーザが存在しない
                    if (empty($user)) {
                        // パスワードは自動設定, 設定して教えない, 20文字 大文字小文字英数字ランダム
                        $password = Hash::make(Str::random(20));

                        // ユーザが存在しない場合、ログインのみ権限でユーザを作成して、自動ログイン
                        $user = new User;
                        $user->name = $request->userid;
                        $user->userid = $request->userid;
                        $user->password = $password;
                        $user->created_event = AuthMethodType::ldap;
                        $user->columns_set_id = UsersTool::COLUMNS_SET_ID_DEFAULT;
                        $user->save();

                    } else {
                        // ユーザ登録が既にある場合、そのユーザが利用可能になっているかどうかをチェックし、利用不可になっている場合は処理を戻す
                        if ($user->status != UserStatus::active) {
                            return;
                        }
                    }

                    // ログイン
                    Auth::login($user, true);

                    // トップページへ
                    return redirect("/");
                } else {
                    // Error 49: Invalid credentials（パスワード間違い）以外はログを出力する。
                    if (ldap_errno($ldapconn) != 49) {
                        $this->errorLogAndFlashMessageForHeader("LDAP-Error " . ldap_errno($ldapconn) . ": " . ldap_error($ldapconn));
                    }
                    ldap_close($ldapconn);
                }

            } else {
                $this->errorLogAndFlashMessageForHeader("LDAPサーバに接続できませんでした。");
                return;
            }
        }
        return;
    }

    /**
     * エラーメッセージを画面ヘッダー部分とログに出力
     */
    private function errorLogAndFlashMessageForHeader($message)
    {
        session()->flash('flash_message_for_header', $message);
        session()->flash('flash_message_for_header_class', 'alert-danger');
        Log::error($message);
    }

    /**
     * 外部認証: shibboleth 認証
     */
    private function authMethodShibboleth($request)
    {
        // 使用する外部認証 取得
        $auth_method_event = Configs::getAuthMethodEvent();

        // 外部認証がない場合は戻る
        if (empty($auth_method_event->value)) {
            return;
        }

        if ($auth_method_event->value == AuthMethodType::shibboleth) {
            // shibboleth 認証
            //
            // 必須
            // $userid = $request->server('REDIRECT_mail');
            $userid = $request->server(config('cc_shibboleth_config.userid'));

            // ログインするユーザの存在を確認
            $user = User::where('userid', $userid)->first();

            if (empty($user)) {
                // ユーザが存在しない
                //
                // 必須
                // $user_name = $request->server('REDIRECT_employeeNumber');
                $user_name = $request->server(config('cc_shibboleth_config.user_name'));

                // パスワードは自動設定, 設定して教えない, 20文字 大文字小文字英数字ランダム
                $password = Hash::make(Str::random(20));

                // 任意, $request->server()は値がなければnullになる
                // $email = $request->server('REDIRECT_mail');
                $email = $request->server(config('cc_shibboleth_config.user_email'));

                // ユーザが存在しない場合、ログインのみ権限でユーザを作成して、自動ログイン
                $user = new User;
                $user->name = $user_name;
                $user->userid = $userid;
                $user->email = $email;
                $user->password = $password;
                $user->created_event = AuthMethodType::shibboleth;
                $user->columns_set_id = UsersTool::COLUMNS_SET_ID_DEFAULT;
                $user->save();

                // [TODO] 区分 (unscoped-affiliation),    faculty (教員)，staff (職員), student (学生)
                //        によって、シボレス認証初回時の自動アカウント設定、何か設定する？
                // echo "<tr><td>区分</td><td>".$_SERVER['REDIRECT_unscoped-affiliation']."</td></tr>";

                // 追加権限設定があれば作成
                // if (!empty($auth_method['additional4'])) {
                //     $original_rols_options = explode(':', $auth_method['additional4']);
                //     UsersRoles::create([
                //         'users_id'   => $user->id,
                //         'target'     => 'original_role',
                //         'role_name'  => $original_rols_options[1],
                //         'role_value' => 1
                //     ]);
                // }
            } else {
                // ユーザが存在する
                //
                // 利用可能かチェック
                if ($user->status != 0) {
                    abort(403, "利用不可のため、ログインできません。");
                }
            }

            // ログイン
            Auth::login($user, true);
            // トップページへ
            return redirect("/");
        }
        return;
    }

    /**
     * NetCommons2 からの移行パスワードでの認証
     */
    private function authNetCommons2Password($request)
    {
        // ログインするユーザの存在を確認
        $user = User::where('userid', $request['userid'])->first();

        // ユーザが存在しない
        if (empty($user)) {
            return false;
        }

        // パスワードチェック
        if (Hash::check(md5($request->password), $user->password) || // v1.0.0以前
            md5($request->password) === $user->password) { // v1.0.0より後
            // ログイン
            Auth::login($user, true);

            $url = '/';
            // ログイン後の返却ページ対応
            if ($request->session()->get('url') && isset($request->session()->get('url')["intended"])) {
                $url = $request->session()->get('url')["intended"];
            }

            // パスワードを強化
            // 初回ログイン以降は通常のログインルートに入るようにする
            $user->password = Hash::make($request->password);
            $user->save();
            // トップページへ
            return redirect($url);
        }
    }

    /**
     * NetCommons3 からの移行パスワードでの認証
     */
    private function authNetCommons3Password($request)
    {
        // ログインするユーザの存在を確認
        $user = User::where('userid', $request['userid'])->first();

        // ユーザが存在しない
        if (empty($user)) {
            return false;
        }

        $nc3_security_salt = Configs::where('name', 'nc3_security_salt')->firstOrNew([]);

        // パスワードチェック
        if (hash("sha512", $nc3_security_salt->value . $request->password) === $user->password ||
            hash("sha512", $nc3_security_salt->value . md5($request->password)) === $user->password) {

            // ログイン
            Auth::login($user, true);

            $url = '/';
            // ログイン後の返却ページ対応
            if ($request->session()->get('url') && isset($request->session()->get('url')["intended"])) {
                $url = $request->session()->get('url')["intended"];
            }

            // パスワードを強化
            // 初回ログイン以降は通常のログインルートに入るようにする
            $user->password = Hash::make($request->password);
            $user->save();
            // トップページへ
            return redirect($url);
        }
    }
}
