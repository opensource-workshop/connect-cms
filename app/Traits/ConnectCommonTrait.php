<?php

namespace App\Traits;

// use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

//use Carbon\Carbon;
use Session;

use App\User;
use App\Models\Common\ConnectCarbon;
use App\Models\Common\Frame;
use App\Models\Common\Holiday;
use App\Models\Common\Page;
// use App\Models\Common\PageRole;
use App\Models\Common\YasumiHoliday;
use App\Models\Core\Configs;
use App\Models\Core\Plugins;
use App\Models\Core\UsersRoles;

use App\Enums\UserStatus;
use App\Enums\AuthMethodType;
use App\Enums\AuthLdapDnType;

use Yasumi\Yasumi;

trait ConnectCommonTrait
{
    //var $directory_base = "uploads/";
    //var $directory_file_limit = 1000;

    /**
     * 権限チェック ＆ エラー時
     * roll_or_auth : 権限 or 役割
     *
     * @return view|null 権限チェックの結果、エラーがあればエラー表示用HTML が返ってくる。
     *
     * @see \App\Providers\AppServiceProvider AppServiceProvider::boot()
     */
    public function can($roll_or_auth, $post = null, $plugin_name = null, $buckets = null, $frame = null)
    {
        $args = null;
        if ($post != null || $plugin_name != null || $buckets != null || $frame != null) {
            $args = [[$post, $plugin_name, $buckets, $frame]];
        }

        if (!Auth::check() || !Auth::user()->can($roll_or_auth, $args)) {
            return $this->viewError("403_inframe", null, "canメソッドチェック:{$roll_or_auth}");
        }
    }

    /**
     * 権限チェック
     * roll_or_auth : 権限 or 役割
     *
     * @return bool
     *
     * @see \App\Providers\AppServiceProvider AppServiceProvider::boot()
     */
    public function isCan($roll_or_auth, $post = null, $plugin_name = null, $buckets = null, $frame = null): bool
    {
        $args = null;
        if ($post != null || $plugin_name != null || $buckets != null || $frame != null) {
            $args = [[$post, $plugin_name, $buckets, $frame]];
        }

        if (!Auth::check() || !Auth::user()->can($roll_or_auth, $args)) {
            return false;
        }
        return true;
    }

    /**
     * エラー画面の表示
     */
    public function viewError($error_code, $message = null, $debug_message = null)
    {
        // 表示テンプレートを呼び出す。
        return view('errors.' . $error_code, ['message' => $message, 'debug_message' => $debug_message]);
    }

    /**
     * プラグイン一覧の取得
     */
    public function getPlugins($arg_display_flag = true, $force_get = false)
    {
        // プラグイン一覧の取得
        $display_flag = ($arg_display_flag) ? 1 : 0;
        $plugins = Plugins::where('display_flag', $display_flag)->orderBy('display_sequence')->get();

        // 強制的に非表示にするプラグインを除外
        if (!$force_get) {
            foreach ($plugins as $plugin_loop_key => $plugin) {
                if (in_array(mb_strtolower($plugin->plugin_name), config('connect.PLUGIN_FORCE_HIDDEN'))) {
                    $plugins->forget($plugin_loop_key);
                }
            }
        }
        return $plugins;
    }

    /**
     * 曜日取得
     *
     * @todo app\Plugins\User\Openingcalendars\OpeningcalendarsPlugin.php のみで使われている。今後移動予定
     */
    public function getWeekJp($date)
    {
        switch (date('N', strtotime($date))) {
            case 1:
                return "月";
            break;
            case 2:
                return "火";
            break;
            case 3:
                return "水";
            break;
            case 4:
                return "木";
            break;
            case 5:
                return "金";
            break;
            case 6:
                return "土";
            break;
            case 7:
                return "日";
            break;
        }
    }

    /**
     * IPアドレスが範囲内か
     */
    private function isRangeIp($remote_ip, $check_ips)
    {
        // * は範囲内
        if ($check_ips == "*") {
            return true;
        }

        // IP アドレス直接チェック
        if (strpos($check_ips, '/') === false) {
            return ($remote_ip === $check_ips);
        }

        // CIDR 形式
        list($check_ip, $mask) = explode('/', $check_ips);
        $check_ip_long  = ip2long($check_ip)  >> (32 - $mask);
        $remote_ip_long = ip2long($remote_ip) >> (32 - $mask);

        return ($check_ip_long == $remote_ip_long);
    }

    /**
     * 特別なパスか判定
     */
    public function isSpecialPath($path)
    {
        // 一般画面の特別なパス
        if (array_key_exists($path, config('connect.CC_SPECIAL_PATH'))) {
            return 1;
        }
        // 管理画面の特別なパス
        if (array_key_exists($path, config('connect.CC_SPECIAL_PATH_MANAGE'))) {
            return 2;
        }
        // マイページ画面の特別なパス
        if (array_key_exists($path, config('connect.CC_SPECIAL_PATH_MYPAGE'))) {
            return 3;
        }
        return false;
    }

    /**
     * 管理プラグインのインスタンス生成
     *
     * @param String $plugin_name
     * @return obj 生成したインスタンス
     */
    private static function createManageInstance($plugin_name)
    {
        // プラグイン毎に動的にnew するので、use せずにここでrequire する。
        $file_path = base_path() . "/app/Plugins/Manage/" . ucfirst($plugin_name) . "Manage/" . ucfirst($plugin_name) . "Manage.php";

        /// インスタンスを生成して返す。
        $class_name = "app\Plugins\Manage\\" . ucfirst($plugin_name) . "Manage\\" . ucfirst($plugin_name) . "Manage";

        // テンプレート・ディレクトリがない場合はオプションプラグインのテンプレートディレクトリを探す
        if (!file_exists($file_path)) {
            $file_path = base_path() . "/app/PluginsOption/Manage/" . ucfirst($plugin_name) . "Manage/" . ucfirst($plugin_name) . "Manage.php";

            $class_name = "app\PluginsOption\Manage\\" . ucfirst($plugin_name) . "Manage\\" . ucfirst($plugin_name) . "Manage";

            // ファイルの存在確認
            if (!file_exists($file_path)) {
                abort(404);
            }
        }

        // 指定されたプラグインファイルの読み込み
        require $file_path;

        $plugin_instance = new $class_name;
        return $plugin_instance;
    }

    /**
     * マイページ用プラグインのインスタンス生成
     *
     * @param String $plugin_name
     * @return obj 生成したインスタンス
     */
    private static function createMypageInstance($plugin_name)
    {
        // プラグイン毎に動的にnew するので、use せずにここでrequire する。
        $file_path = base_path() . "/app/Plugins/Mypage/" . ucfirst($plugin_name) . "Mypage/" . ucfirst($plugin_name) . "Mypage.php";

        // ファイルの存在確認
        if (!file_exists($file_path)) {
            abort(404);
        }

        // 指定されたプラグインファイルの読み込み
        require $file_path;

        /// インスタンスを生成して返す。
        $class_name = "app\Plugins\Mypage\\" . ucfirst($plugin_name) . "Mypage\\" . ucfirst($plugin_name) . "Mypage";
        $plugin_instance = new $class_name;
        return $plugin_instance;
    }

    /**
     * 管理プラグインの呼び出し
     *
     * @param String $plugin_name
     * @return プラグインからの戻り値(HTMLなど)
     */
    private function invokeManage($request, $plugin_name, $action = 'index', $id = null, $sub_id = null)
    {
        // ログインしているユーザー情報を取得
        $user = Auth::user();

        // 権限エラー
        if (empty($user)) {
            abort(403, 'ログインが必要です。');
        }

        // インスタンス生成
        $plugin_instance = self::createManageInstance($plugin_name);

        // 権限定義メソッドの有無確認
        if (!method_exists($plugin_instance, 'declareRole')) {
            abort(403, '権限定義メソッド(declareRole)がありません。');
        }

        // 権限チェック（管理系各プラグインの関数＆権限チェックデータ取得）
        $role_check = false;
        $role_ckeck_tables = $plugin_instance->declareRole();
        if (array_key_exists($action, $role_ckeck_tables)) {
            foreach ($role_ckeck_tables[$action] as $role) {
                // プラグインで定義された権限が自分にあるかチェック
                if ($this->isCan($role)) {
                    $role_check = true;
                }
            }
        } else {
            abort(403, 'メソッドに権限が設定されていません。');
        }

        if (!$role_check) {
            abort(403, 'ユーザーにメソッドに対する権限がありません。');
        }

//        // 操作ログの処理
//        $this->putAppLog($request, $this->getConfigs(), 'page');

        // 指定されたアクションを呼ぶ。
        // 呼び出し先のアクションでは、view 関数でblade を呼び出している想定。
        // view 関数の戻り値はHTML なので、ここではそのままreturn して呼び出し元に返す。
        return $plugin_instance->$action($request, $id, $sub_id);
    }

    /**
     * マイページ用プラグインの呼び出し
     *
     * @param String $plugin_name
     * @return プラグインからの戻り値(HTMLなど)
     */
    private function invokeMypage($request, $plugin_name, $action = 'index', $id = null, $sub_id = null)
    {
        // $action = 'index' が効かないため、改めてチェック
        if (empty($action)) {
            $action = 'index';
        }

        // ログインしているユーザー情報を取得
        $user = Auth::user();

        // 権限エラー
        if (empty($user)) {
            abort(403, 'ログインが必要です。');
        }

        // インスタンス生成
        $plugin_instance = self::createMypageInstance($plugin_name);

        // 指定されたアクションを呼ぶ。
        // 呼び出し先のアクションでは、view 関数でblade を呼び出している想定。
        // view 関数の戻り値はHTML なので、ここではそのままreturn して呼び出し元に返す。
        return $plugin_instance->$action($request, $id, $sub_id);
    }

    /**
     * 指定したパスの呼び出し
     */
    public function callSpecialPath($path, $request)
    {
        // インスタンスを生成して呼び出す。
        if ($this->isSpecialPath($path) === 1) {
            $cc_special_path = config('connect.CC_SPECIAL_PATH');
        } elseif ($this->isSpecialPath($path) === 2) {
            $cc_special_path = config('connect.CC_SPECIAL_PATH_MANAGE');
        } elseif ($this->isSpecialPath($path) === 3) {
            $cc_special_path = config('connect.CC_SPECIAL_PATH_MYPAGE');
        }

        $file_path = base_path() . '/' . $cc_special_path[$path]['plugin'] . '.php';

        // 一般プラグインの場合は、ここでインスタンスを生成
        // 一般プラグインの場合、通常はコアでフレーム分、インスタンスを生成してからinvokeするが、SpecialPathの場合はここでインスタンス生成する。
        // 管理プラグインの場合は、この後で呼ぶinvokeManageでインスタンス生成する。
        if ($this->isSpecialPath($path) === 1) {
            // Page とFrame の生成
            $page = Page::where('id', $cc_special_path[$path]['page_id'])->first();
            $frame = Frame::where('id', $cc_special_path[$path]['frame_id'])->first();

            // 指定されたプラグインファイルの読み込み
            require $file_path;

            // config の値を取得すると、\ が / に置き換えられているので、元に戻す。
            // こうしないとclass がないというエラーになる。
            $class_name = str_replace('/', "\\", $cc_special_path[$path]['plugin']);
            $plugin_instance = new $class_name($page, $frame);
        }

        // 一般プラグインか管理プラグインかで呼び方を変える。
        if ($this->isSpecialPath($path) === 1) {
            return $plugin_instance->invoke($plugin_instance, $request, $cc_special_path[$path]['method'], $cc_special_path[$path]['page_id'], $cc_special_path[$path]['frame_id']);
        } elseif ($this->isSpecialPath($path) === 2) {
            return $this->invokeManage($request, $cc_special_path[$path]['method']);
        } elseif ($this->isSpecialPath($path) === 3) {
            return $this->invokeMypage($request, $cc_special_path[$path]['method']);
        }
        return;
    }

    /**
     * CSRF用トークンの取得
     */
    public function getToken($arg)
    {
        if ($arg == 'hidden') {
            return '<input type="hidden" name="_token" value="' . Session::get('_token') . '">';
        }

        return Session::get('_token');
    }

    /**
     * ページの言語の取得
     */
    public function getPageLanguage($page, $languages)
    {
        // ページの言語
        $page_language = null;

        // 今、表示しているページの言語を判定
        $page_paths = explode('/', $page['permanent_link']);
        if ($page_paths && is_array($page_paths) && array_key_exists(1, $page_paths)) {
            foreach ($languages as $language) {
                if (trim($language->additional1, '/') == $page_paths[1]) {
                    $page_language = $page_paths[1];
                    break;
                }
            }
        }
        return $page_language;
    }

    /**
     * 対象ディレクトリの取得
     * uploads/1 のような形で返す。
     */
    public function getDirectory($file_id)
    {
        // ファイルID がなければ0ディレクトリを返す。
        if (empty($file_id)) {
            //return $this->directory_base . '0';
            return config('connect.directory_base') . '0';
        }
        // 1000で割った余りがディレクトリ名
        //$quotient = floor($file_id / $this->directory_file_limit);
        //$remainder = $file_id % $this->directory_file_limit;
        $quotient = floor($file_id / config('connect.directory_file_limit'));
        $remainder = $file_id % config('connect.directory_file_limit');
        $sub_directory = ($remainder == 0) ? $quotient : $quotient + 1;
        //$directory = $this->directory_base . $sub_directory;
        $directory = config('connect.directory_base') . $sub_directory;

        return $directory;
    }

    /**
     * 利用可能かチェック
     * 戻り値：true なら
     */
    public function checkUserStatus($request, &$error_msg = "")
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
                $user->status == UserStatus::temporary_delete) {

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
    public function authMethodShibboleth($request)
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
     * 外部ユーザ情報取得
     */
    public function getOtherAuthUser($request, $userid)
    {
        // 使用する外部認証 取得
        $auth_method_event = Configs::getAuthMethodEvent();

        // 外部認証ではない場合は戻る
        if (empty($auth_method_event->value)) {
            // 外部認証の対象外
            return array('code' => 100, 'message' => '', 'userid' => $userid, 'name' => '');
        }

        // NetCommons2 認証
        if ($auth_method_event->value == AuthMethodType::netcommons2) {
            // 外部認証設定 取得
            $auth_method = Configs::where('name', 'auth_method')->where('value', AuthMethodType::netcommons2)->first();

            // リクエストURLの組み立て
            $request_url = $auth_method['additional1'] . '?action=connectauthapi_view_main_getuser&userid=' . $userid . '&site_key=' . $auth_method['additional2'] . '&check_value=' . md5($auth_method['additional5'] . $auth_method['additional3']);
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
            if (is_array($check_result) &&
                array_key_exists('check', $check_result) &&
                array_key_exists('handle', $check_result)) {
                if ($check_result['check'] == true) {
                    // ユーザ情報が取得できた
                    return array('code' => 200, 'message' => '', 'userid' => $userid, 'name' => $check_result['handle']);
                } else {
                    // ユーザ情報が取得できなかった
                    return array('code' => 404, 'message' => $check_result['message'], 'userid' => $userid, 'name' => '');
                }
            } else {
                // システム的なエラー
                return array('code' => 500, 'message' => '戻り値の異常', 'userid' => $userid, 'name' => '');
            }
        }
        // 外部認証の対象外
        return array('code' => 100, 'message' => '', 'userid' => $userid, 'name' => '');
    }

    /**
     * NetCommons2 からの移行パスワードでの認証
     */
    public function authNetCommons2Password($request)
    {
        // ログインするユーザの存在を確認
        $user = User::where('userid', $request['userid'])->first();

        // ユーザが存在しない
        if (empty($user)) {
            return false;
        }

        // パスワードチェック
        if (Hash::check(md5($request->password), $user->password)) {
            // ログイン
            Auth::login($user, true);
            // トップページへ
            return redirect("/");
        }
    }

    /**
     * URLからページIDを取得
     */
    public function getPage($permanent_link, $language = null)
    {
        // 多言語指定されたとき
        if (!empty($language)) {
            $page = Page::where('permanent_link', '/' . $language . $permanent_link)->first();
            if (!empty($page)) {
                return $page;
            }
        }
        // 多言語指定されていない or 多言語側にページがない場合は全体から探す。

        // ページ確認
        return Page::where('permanent_link', $permanent_link)->first();
    }

    /**
     * URLから管理画面かどうかを判定
     */
    public function isManagePage($request)
    {
        $url_parts = explode('/', $request->path());
        if ($url_parts[0] == 'manage') {
            return true;
        }
        return false;
    }

    /**
     * page 変数がページオブジェクトか判定
     */
    public function isPageObj($page)
    {
        if (empty($page)) {
            return false;
        }
        if (get_class($page) == 'App\Models\Common\Page') {
            return true;
        }
        return false;
    }

    /**
     * 都道府県のリストの取得
     */
    public function getPrefList()
    {
        return array('北海道','青森県','岩手県','宮城県','秋田県','山形県','福島県',
                     '茨城県','栃木県','群馬県','埼玉県','千葉県','東京都','神奈川県',
                     '新潟県','富山県','石川県','福井県','山梨県','長野県','岐阜県','静岡県','愛知県',
                     '三重県','滋賀県','京都府','大阪府','兵庫県','奈良県','和歌山県',
                     '鳥取県','島根県','岡山県','広島県','山口県',
                     '徳島県','香川県','愛媛県','高知県',
                     '福岡県','佐賀県','長崎県','熊本県','大分県','宮崎県','鹿児島県','沖縄県');
    }

    /**
     * 祝日の追加（From-To指定）
     *
     * date に holiday 属性を追加する。
     * 年またぎを考慮。
     */
    protected function addHolidaysFromTo(ConnectCarbon $start_date, ConnectCarbon $end_date, array $dates) : array
    {
        // 年の祝日一覧を取得する。
        $yasumis = $this->getYasumis($start_date->year);

        // 独自設定祝日データの取得（From-To指定）
        $connect_holidays = Holiday::whereBetween('holiday_date', [$start_date, $end_date])->orderBy('holiday_date')->get();

        // 独自設定祝日を加味する。
        $dates = $this->addConnectHolidays($connect_holidays, $dates, $yasumis);

        // 年またぎ対応（開始と終了で年が違う場合、終了年の祝日もセット）
        if ($start_date->year != $end_date->year) {
            $end_yasumis = $this->getYasumis($end_date->year);
            $dates = $this->addConnectHolidays($connect_holidays, $dates, $end_yasumis);
        }

        return $dates;
    }

    /**
     * 年の祝日を取得
     */
    private function getYasumis($year, ?string $country = 'Japan', ?string $locale = 'ja_JP') : \Yasumi\Provider\AbstractProvider
    {
        return Yasumi::create($country, (int)$year, $locale);
    }

    /**
     * 独自設定祝日を加味する。
     */
    private function addConnectHolidays(Collection $connect_holidays, array $dates, \Yasumi\Provider\AbstractProvider $yasumis) : array
    {
        foreach ($connect_holidays as $holiday) {
            // 計算の祝日に同じ日があれば、追加設定を有効にするために、かぶせる。
            // Yasumi のメソッドに日付指定での抜き出しがないので、ループする。
            $found_flag = false;
            foreach ($yasumis as &$yasumi) {
                if ($yasumi->format('Y-m-d') == $holiday->holiday_date) {
                    // 独自設定の祝日と同じ日が計算の祝日にあれば、計算の祝日を消して、独自設定を有効にする。
                    $found_flag = true;
                    $yasumis->removeHoliday($yasumi->shortName);
                    $new_holiday = new YasumiHoliday($holiday->id, ['ja_JP' => $holiday->holiday_name], new ConnectCarbon($holiday->holiday_date), 'ja_JP', 2);
                    $yasumis->addHoliday($new_holiday);
                    break;
                }
            }
            // 計算の祝日にない独自設定は、追加祝日として扱う。
            if ($found_flag == false) {
                $new_holiday = new YasumiHoliday($holiday->id, ['ja_JP' => $holiday->holiday_name], new ConnectCarbon($holiday->holiday_date), 'ja_JP', 1);
                $new_holiday->orginal_holiday_post = $holiday;
                $yasumis->addHoliday($new_holiday);
            }
        }

        // 独自祝日を加味した祝日一覧をループ。対象の年月日があれば、date オブジェクトに holiday 属性として追加する。
        foreach ($yasumis as $yasumi) {
            if (isset($dates[$yasumi->format('Y-m-d')])) {
                $dates[$yasumi->format('Y-m-d')]->holiday = $yasumi;
            }
        }

        return $dates;
    }
}
