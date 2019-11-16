<?php

namespace App\Traits;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

use App\Models\Core\Configs;
use App\Models\Core\ConfigsLoginPermits;
use App\Models\Core\Plugins;
use App\Models\Core\UsersRoles;

trait ConnectCommonTrait
{
    /**
     * ユーザーが指定された権限を保持しているかチェックする。
     *
     * @return boolean
     */
    public function check_authority($user, $authority, $args = null)
    {
        // preview モードのチェック付きの場合はpreview モードなら権限ナシで返す。
        $request = app(\Illuminate\Http\Request::class);

        // 引数をバラシてPOST を取得
        list($post, $plugin_name, $mode_switch) = $this->check_args_obj($args);

        // モードスイッチがプレビューなら表示しないになっていれば、権限ナシで返す。
        if ($mode_switch == 'preview_off' && $request->mode == 'preview') {
            return false;
        }

        // プレビュー判断はココまで
        if ($authority == 'preview') {
            return true;
        }

        // ログインしていない場合は権限なし
        if (empty($user)) {
            return false;
        }

        // 指定された権限を含むロールをループする。
        foreach (config('cc_role.CC_AUTHORITY')[$authority] as $role) {

            // ユーザの保持しているロールをループ
            foreach ($user['user_roles'] as $target) {

                // ターゲット処理をループ
                foreach ($target as $user_role => $user_role_value) {

                    // 必要なロールを保持している
                    if ($role == $user_role && $user_role_value) {

                        // 他者の記事を更新できる権限の場合は、記事作成者のチェックは不要
                        if (($user_role == 'role_article_admin') ||
                            ($user_role == 'role_approval')) {
                            return true;
                        }

                        // 自分のオブジェクトチェックが必要ならチェックする
                        if (empty($post)) {
                            return true;
                        }
                        else {
                            if ((($authority == 'buckets.delete') ||
                                 ($authority == 'posts.create') ||
                                 ($authority == 'posts.update') ||
                                 ($authority == 'posts.delete')) &&
                                ($user->id == $post->created_id)) {
                                return true;
                            }
                            else {
                                // 複数ロールをチェックするため、ここではreturn しない。
                                // return false;
                            }
                        }
                        // 複数ロールをチェックするため、ここではreturn しない。
                        // return true;
                    }
                }
            }
        }
        return false;
    }

    /**
     * ユーザーが指定された役割を保持しているかチェックする。
     *
     * @return boolean
     */
    public function check_role($user, $role)
    {
        // ログインしていない場合は権限なし
        if (empty($user)) {
            return false;
        }

        // 指定された権限を含むロールをループする。
        // 記事追加は記事管理者でもOKのような処理のため。
        foreach (config('cc_role.CC_ROLE_HIERARCHY')[$role] as $checck_role) {

            // ユーザの保持しているロールをループ
            foreach ($user['user_roles'] as $target) {

                // ターゲット処理をループ
                foreach ($target as $user_role => $user_role_value) {

                    // 必要なロールを保持している場合は、権限ありとして true を返す。
                    if ($checck_role == $user_role && $user_role_value) {
                        return true;
                    }
                }
            }
        }

        return false;
    }

    /**
     * 権限チェック
     * roll_or_auth : 権限 or 役割
     */
    public function can($roll_or_auth, $post = null, $plugin_name = null)
    {
        $args = null;
        if ( $post != null || $plugin_name != null ) {
            $args = [[$post, $plugin_name]];
        }

        if (!Auth::check() || !Auth::user()->can($roll_or_auth, $args)) {
            return $this->view_error("403_inframe");
        }
    }

    /**
     * 権限チェック
     * roll_or_auth : 権限 or 役割
     */
    public function isCan($roll_or_auth, $post = null, $plugin_name = null)
    {
        $args = null;
        if ( $post != null || $plugin_name != null ) {
            $args = [[$post, $plugin_name]];
        }

        if (!Auth::check() || !Auth::user()->can($roll_or_auth, $args)) {
            return false;
        }
        return true;
    }

    /**
     * エラー画面の表示
     *
     */
    public function view_error($error_code, $message = null, $debug_message = null)
    {
        // 表示テンプレートを呼び出す。
        return view('errors.' . $error_code, ['message' => $message, 'debug_message' => $debug_message]);
    }

    /**
     * プラグイン一覧の取得
     *
     */
    public function getPlugins($arg_display_flag = true, $force_get = false)
    {
        // プラグイン一覧の取得
        $display_flag = ($arg_display_flag) ? 1 : 0;
        $plugins = Plugins::where('display_flag', $display_flag)->orderBy('display_sequence')->get();

        // 強制的に非表示にするプラグインを除外
        if ( !$force_get ) {
            foreach($plugins as $plugin_loop_key => $plugin) {
                if ( in_array(mb_strtolower($plugin->plugin_name), config('connect.PLUGIN_FORCE_HIDDEN'))) {
                    $plugins->forget($plugin_loop_key);
                }
            }
        }
        return $plugins;
    }

    /**
     *  曜日取得
     *
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
     *  IPアドレスが範囲内か
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
     *  ログイン可否チェック
     *
     */
    public function judgmentLogin($user)
    {
        // IP アドレス取得
        $remote_ip = \Request::ip();
        //Log::debug("--- IP：" . $remote_ip);

        // ログイン可否の基本設定を取得
        $configs = Configs::where('name', 'login_reject')->first();

        // ログイン可否の基本
        $login_reject = 0;
        if (!empty($configs)) {
            $login_reject = $configs->value;
        }
        //Log::debug("基本：" . $login_reject);

        // ユーザーオブジェクトにロールデータを付与
        $users_roles = new UsersRoles();
        $user->user_roles = $users_roles->getUsersRoles($user->id);
        //Log::debug("ユーザー：\n" . $user);

        // ログイン可否の個別設定を取得
        $configs_login_permits = ConfigsLoginPermits::orderBy('apply_sequence', 'asc')->get();

        // ログイン可否の個別設定がない場合はここで判断
        if (empty($configs_login_permits)) {
            return ($login_reject == 0) ? true : false;
        }

        // ログイン可否の個別設定をループ
        foreach($configs_login_permits as $configs_login_permit) {

            // IPアドレスが範囲内か
            if (!$this->isRangeIp($remote_ip, $configs_login_permit->ip_address)) {

                // IPアドレスが範囲外なら、チェック的にはOKなので、次のチェックへ。
                //Log::debug("IP範囲外：" . $remote_ip . "/" . $configs_login_permit->ip_address);
                continue;
            }

            // 権限が範囲内か
            // ロールが入っていない（全対象）の場合は、対象レコードとなるので、設定されている可否を使用
            if (empty($configs_login_permit->role)) {
                //Log::debug("role空で対象：" . $configs_login_permit->reject);
                $login_reject = $configs_login_permit->reject;
            }
            // 許可/拒否設定が自分のロールに合致すれば、対象の許可/拒否設定を反映
            else if ($this->check_role($user, $configs_login_permit->role)) {
                //Log::debug("role合致で対象：" . $configs_login_permit->reject);
                $login_reject = $configs_login_permit->reject;
            }
        }
        // 設定可否の適用
        //Log::debug("最終：" . $login_reject);
        return ($login_reject == 0) ? true : false;
    }

    /**
     *  特別なパスか判定
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
        return false;
    }

    /**
     *  管理プラグインのインスタンス生成
     *
     * @param String $plugin_name
     * @return obj 生成したインスタンス
     */
    private static function createManageInstance($plugin_name)
    {
        // プラグイン毎に動的にnew するので、use せずにここでrequire する。
        $file_path = base_path() . "/app/Plugins/Manage/" . ucfirst($plugin_name) . "Manage/" . ucfirst($plugin_name) . "Manage.php";

        // ファイルの存在確認
        if (!file_exists($file_path)) {
            abort(404);
        }

        // 指定されたプラグインファイルの読み込み
        require $file_path;

        /// インスタンスを生成して返す。
        $class_name = "app\Plugins\Manage\\" . ucfirst($plugin_name) . "Manage\\" . ucfirst($plugin_name) . "Manage";
        $plugin_instance = new $class_name;
        return $plugin_instance;
    }

    /**
     *  管理プラグインの呼び出し
     *
     * @param String $plugin_name
     * @return プラグインからの戻り値(HTMLなど)
     */
    private function invokeManage($request, $plugin_name, $action = 'index', $id = null)
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
            foreach($role_ckeck_tables[$action] as $role) {
                // プラグインで定義された権限が自分にあるかチェック
                if ($this->isCan($role)) {
                    $role_check = true;
                }
            }
        }
        else {
            abort(403, 'メソッドに権限が設定されていません。');
        }

        if (!$role_check) {
            abort(403, 'ユーザーにメソッドに対する権限がありません。');
        }

        // 指定されたアクションを呼ぶ。
        // 呼び出し先のアクションでは、view 関数でblade を呼び出している想定。
        // view 関数の戻り値はHTML なので、ここではそのままreturn して呼び出し元に返す。
        return $plugin_instance->$action($request, $id);
    }

    /**
     *  指定したパスの呼び出し
     */
    public function callSpecialPath($path, $request)
    {
        // インスタンスを生成して呼び出す。
        if ($this->isSpecialPath($path) === 1) {
            $cc_special_path = config('connect.CC_SPECIAL_PATH');
        }
        else {
            $cc_special_path = config('connect.CC_SPECIAL_PATH_MANAGE');
        }

        $file_path = base_path() . '/' . $cc_special_path[$path]['plugin'] . '.php';

        // 一般プラグインの場合は、ここでインスタンスを生成
        // 一般プラグインの場合、通常はコアでフレーム分、インスタンスを生成してからinvokeするが、SpecialPathの場合はここでインスタンス生成する。
        // 管理プラグインの場合は、この後で呼ぶinvokeManageでインスタンス生成する。
        if ($this->isSpecialPath($path) === 1) {

            // 指定されたプラグインファイルの読み込み
            require $file_path;

            // config の値を取得すると、\ が / に置き換えられているので、元に戻す。
            // こうしないとclass がないというエラーになる。
            $class_name = str_replace('/', "\\", $cc_special_path[$path]['plugin']);
            $plugin_instance = new $class_name;
        }

        // 一般プラグインか管理プラグインかで呼び方を変える。
        if ($this->isSpecialPath($path) === 1) {
            return $plugin_instance->invoke($plugin_instance, $request, $cc_special_path[$path]['method'], $cc_special_path[$path]['page_id'], $cc_special_path[$path]['flame_id']);
        }
        else if ($this->isSpecialPath($path) === 2) {

            return $this->invokeManage($request, $cc_special_path[$path]['method']);
        }
        return;
    }
}
