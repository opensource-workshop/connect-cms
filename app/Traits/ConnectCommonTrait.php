<?php

namespace App\Traits;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Hash;

use Session;

use App\User;
use App\Models\Common\Frame;
use App\Models\Common\Page;
use App\Models\Core\Configs;
use App\Models\Core\ConfigsLoginPermits;
use App\Models\Core\Plugins;
use App\Models\Core\UsersRoles;

trait ConnectCommonTrait
{
    var $directory_base = "uploads/";
    var $directory_file_limit = 1000;

    /**
     * Buckets のrole を配列で返却
     *
     * @return boolean
     */
    private function getBucketsRoles($buckets)
    {
        // Buckets オブジェクトがない場合はfalse を返す。
        if (empty($buckets)) {
            return false;
        }

        // Buckets オブジェクトでない場合もfalse
        if (!is_object($buckets) || get_class($buckets) != "App\Models\Common\Buckets") {
            return false;
        }

        return $buckets->getBucketsRoles();

//        // Buckets にrole がない場合などで、Buckets のrole を使用しない場合はfalse を返す。
//        if (empty($buckets)) {
//            return false;
//        }
//        // Buckets オブジェクトでない場合もfalse
//        if (!is_object($buckets) || get_class($buckets) != "App\Models\Common\Buckets") {
//            return false;
//        }
//        // role を配列にして返却
//        $roles = null;
//        if ($buckets->post_role) {
//            $roles = explode(',', $buckets->post_role);
//        }
//        if (empty($roles)) {
//            return false;
//        }
//        return $roles;
    }

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
//        list($post, $plugin_name, $mode_switch, $buckets_obj) = $this->check_args_obj($args);
        list($post, $plugin_name, $buckets_obj) = $this->check_args_obj($args);
//print_r( $buckets_obj );

        // モードスイッチがプレビューなら表示しないになっていれば、権限ナシで返す。
//        if ($mode_switch == 'preview_off' && $request->mode == 'preview') {

        // モードスイッチがプレビューなら、無条件に権限ナシで返す。
        if ($request->mode == 'preview') {
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

        // チェックする権限を決定
        // Backets にrole が指定されていれば、それを使用。
        // Backets にrole が指定されていなければ、標準のrole を使用
        $check_roles = config('cc_role.CC_AUTHORITY')[$authority];
        if (!empty($this->getBucketsRoles($buckets_obj))) {
            $check_roles = array();
            $buckets_roles = $this->getBucketsRoles($buckets_obj);
//Log::debug($buckets_roles);
            // Buckets に設定されたrole から、関連role を取得してチェック。
            foreach($buckets_roles as $buckets_role) {
                $check_roles = array_merge($check_roles, config('cc_role.CC_ROLE_HIERARCHY')[$buckets_role]);
            }
            // 配列は添字型になるので、array_merge で結合してから重複を取り除く
            $check_roles = array_unique($check_roles);
        }

        // 指定された権限を含むロールをループする。
//        foreach (config('cc_role.CC_AUTHORITY')[$authority] as $role) {
        foreach ($check_roles as $role) {

            // ユーザの保持しているロールをループ
            // bugfix:「ログイン状態を維持する」ONで1日たってからブラウザアクセスすると$user->user_roles = nullにより例外「Invalid argument supplied for foreach()」が発生するバグに対応するため、arrayにキャストする。
            //foreach ($user['user_roles'] as $target) {
            foreach ((array)$user->user_roles as $target) {

                // ターゲット処理をループ
                foreach ($target as $user_role => $user_role_value) {

                    // 要求されているのが承認権限の場合、Buckets の投稿権限にはないため、ここでチェックする。
                    if ($authority == 'posts.approval' && $user_role == 'role_approval') {
                        return true;
                    }

                    // 必要なロールを保持している
                    if ($role == $user_role && $user_role_value) {

                        // 他者の記事を更新できる権限の場合は、記事作成者のチェックは不要
                        if (($user_role == 'role_article_admin') ||
                            ($user_role == 'role_article') ||
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
        // 記事追加はコンテンツ管理者でもOKのような処理のため。
        foreach (config('cc_role.CC_ROLE_HIERARCHY')[$role] as $checck_role) {

            // ユーザの保持しているロールをループ
            // bugfix:「ログイン状態を維持する」ONで1日たってからブラウザアクセスすると$user->user_roles = nullにより例外「Invalid argument supplied for foreach()」が発生するバグに対応するため、arrayにキャストする。
            foreach ((array)$user->user_roles as $target) {

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
    public function can($roll_or_auth, $post = null, $plugin_name = null, $buckets = null)
    {
        $args = null;
        if ( $post != null || $plugin_name != null || $buckets != null ) {
            $args = [[$post, $plugin_name, $buckets]];
        }

        if (!Auth::check() || !Auth::user()->can($roll_or_auth, $args)) {
            return $this->view_error("403_inframe", null, "canメソッドチェック");
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
        return $plugin_instance->$action($request, $id, $sub_id);
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
        }
        else if ($this->isSpecialPath($path) === 2) {

            return $this->invokeManage($request, $cc_special_path[$path]['method']);
        }
        return;
    }

    /**
     *  文字列変換
     */
    public function replaceConnectTagAll($contents, $page, $configs)
    {
        // Connect-CMSタグを値に変換する。
        if (empty($contents)) {
            return $contents;
        }

        $patterns = array();
        $replacements = array();

        // 固定リンク(多言語切り替えで使用)
        $config_language_multi_on = null;
        foreach($configs as $config) {
            if ($config->name == 'language_multi_on') {
                $config_language_multi_on = $config->value;
            }
        }

        // 言語設定の取得
        $languages = array();
        foreach($configs as $config) {
            if ($config->category == 'language') {
                $languages[$config->additional1] = $config;
            }
        }
        $page_language = $this->getPageLanguage($page, $languages);

        // 確実に言語設定部分を取り除くために、permanent_link を / で分解して、1番目(/ の次)の内容を取得する。
        $permanent_link_array = explode('/', $page->permanent_link);

        // 多言語on＆現在のページがデフォルト以外の言語の場合、言語指定を取り除く
        if ($config_language_multi_on &&
            $page_language &&
            $permanent_link_array &&
            array_key_exists(1, $permanent_link_array) &&
            $permanent_link_array[1] == $page_language)
        {
            $patterns[0] = '/{{cc:permanent_link}}/';
            $replacements[0] = trim(mb_substr($page->permanent_link, mb_strlen('/'.$page_language)), '/');
        }
        else {
            $patterns[0] = '/{{cc:permanent_link}}/';
            $replacements[0] = trim($page->permanent_link, '/');
        }

        // 変換と値の返却
        $contents->content_text = preg_replace($patterns, $replacements, $contents->content_text);
        return $contents;
    }

    /**
     *  固定記事からスマホメニューを出すためのタグ生成
     */
    public function getSmpMenu($level1_pages, $page_id = null)
    {
        $sp_menu  = '' . "\n";
        $sp_menu .= '<nav class="sp_menu">' . "\n";
        $sp_menu .= '<ul>' . "\n";
        foreach ($level1_pages as $level1_page) {

            // ページの表示条件の反映（IP制限など）
            if (!$level1_page['parent']->isView(Auth::user())) {
                continue;
            }

            // コンストラクタで全体作業用として取得したページを使用する想定
            // そのため、ページの基本の表示設定を反映する。
            if ($level1_page['parent']->base_display_flag == 0) {
                continue;
            }

            // ルーツのチェック
            if ($level1_page['parent']->isAncestorOf($this->page)) {
                $active_class = ' class="active"';
            }
            else {
                $active_class = '';
            }
            //$sp_menu .= '<li class="' . $level1_page['parent']->getLinkUrl('/') . '_menu">' . "\n"; // ページにクラス名を保持する方式へ変更した。
            $sp_menu .= '<li class="' . $level1_page['parent']->getClass() . '">' . "\n";

            // クラス名取得
            $classes = explode(' ', $level1_page['parent']->getClass());

            // ページのクラスに "smp_a_link" がある場合は、a タグでリンクする。
            if (is_array($classes) && in_array('smp_a_link', $classes)) {
                $sp_menu .= '<a' . $active_class . ' href="' . $level1_page['parent']->getUrl() . '"' . $level1_page['parent']->getUrlTargetTag() . '>';
                $sp_menu .= $level1_page['parent']->page_name;
                $sp_menu .= '</a>' . "\n";
            }
            else {
                $sp_menu .= '<p' . $active_class . '>';
                $sp_menu .= $level1_page['parent']->page_name;
                $sp_menu .= '</p>' . "\n";
            }

            if (array_key_exists('child', $level1_page)) {
                $sp_menu .= '<ul' . $active_class . '>' . "\n";
                foreach ($level1_page['child'] as $child) {

                    // ページの表示条件の反映（IP制限など）
                    if (!$child->isView(Auth::user())) {
                        continue;
                    }

                    if ($child->base_display_flag == 0) {
                        continue;
                    }
                    else {
                        $child_depth = intval($child->depth) - 1;
                        $child_margin_left = ($child_depth > 0) ? $child_depth * 20 : 0;
                        $sp_menu .= '<li><a href="' . $child->getUrl() . '"' . $child->getUrlTargetTag() . ' style="margin-left:' . $child_margin_left . 'px"' . '>';
                        if ($page_id == $child->id) {
                            $sp_menu .= '<u>' . $child->page_name . '</u>';
                        }
                        else {
                            $sp_menu .= $child->page_name;
                        }
                        $sp_menu .= '</a></li>' . "\n";
                    }
                }
                $sp_menu .= '</ul>' . "\n";
            }
            $sp_menu .= '</li>' . "\n";
        }
        $sp_menu .= '</ul>' . "\n";
        $sp_menu .= '</nav>' . "\n";
        return $sp_menu;
    }

    /**
     *  CSRF用トークンの取得
     */
    public function getToken($arg)
    {
        if ($arg == 'hidden') {
            return '<input type="hidden" name="_token" value="' . Session::get('_token') . '">';
        }

        return Session::get('_token');
    }

    /**
     *  ページの言語の取得
     */
    public function getPageLanguage($page, $languages)
    {
        // ページの言語
        $page_language = null;

        // 今、表示しているページの言語を判定
        $page_paths = explode('/', $page['permanent_link']);
        if ($page_paths && is_array($page_paths) && array_key_exists(1, $page_paths)) {
            foreach($languages as $language) {
                if (trim($language->additional1, '/') == $page_paths[1]) {
                    $page_language = $page_paths[1];
                    break;
                }
            }
        }
        return $page_language;
    }

    /**
     *  ページの言語の取得
     */
    public function getPageLanguage4url($languages)
    {
        // ページの言語
        $page_language = null;

        $current_url = url()->current();
        $base_url = url('/');
        $current_permanent_link = str_replace( $base_url, '', $current_url);

        // 今、表示しているページの言語を判定
        $page_paths = explode('/', $current_permanent_link);
        if ($page_paths && is_array($page_paths) && array_key_exists(1, $page_paths)) {
            foreach($languages as $language) {
                if (trim($language->additional1, '/') == $page_paths[1]) {
                    $page_language = $page_paths[1];
                    break;
                }
            }
        }
        return $page_language;
    }

    /**
     *  現在の言語設定のトップページ
     */
    public function getTopPage($page, $languages = null)
    {
        $page_language = $this->getPageLanguage($page, $languages);

        // 言語トップのページ確認
        return Page::where('permanent_link', '/'.$page_language)->first();
    }

    /**
     *  対象ディレクトリの取得
     *
     */
    public function getDirectory($file_id)
    {
        // ファイルID がなければ0ディレクトリを返す。
        if (empty($file_id)) {
            return $this->directory_base . '0';
        }
        // 1000で割った余りがディレクトリ名
        $quotient = floor($file_id / $this->directory_file_limit);
        $remainder = $file_id % $this->directory_file_limit;
        $sub_directory = ($remainder == 0) ? $quotient : $quotient + 1;
        $directory = $this->directory_base . $sub_directory;

        return $directory;
    }

    /**
     *  外部認証
     *
     */
    public function authMethod($request)
    {

        // Config チェック
        $auth_method = Configs::where('name', 'auth_method')->first();

        // 外部認証ではない場合は戻る
        if (empty($auth_method) || $auth_method->value == '') {
            return;
        }

        // NetCommons2 認証
        if ($auth_method->value == 'netcommons2') {

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
                        $user           = new User;
                        $user->name     = $request['userid'];
                        $user->userid   = $request['userid'];
                        $user->password = Hash::make($request['password']);
                        $user->save();

                        // 追加権限設定があれば作成
                        if (!empty($auth_method['additional4'])) {
                            $original_rols_options = explode(':', $auth_method['additional4']);
                            UsersRoles::create([
                                'users_id'   => $user->id,
                                'target'     => 'original_role',
                                'role_name'  => $original_rols_options[1],
                                'role_value' => 1
                            ]);
                        }
                    }

                    // ログイン
                    Auth::login($user, true);
                    // トップページへ
                    return redirect("/");
                }
            }
        }
        return;
    }

    /**
     *  URLからページIDを取得
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
     *  URLから管理画面かどうかを判定
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
     *  page 変数がページオブジェクトか判定
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
     *  都道府県のリストの取得
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
}
