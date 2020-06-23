<?php

namespace App\Plugins\User;

use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

use Monolog\Logger;
use Monolog\Formatter\LineFormatter;
use Monolog\Handler\StreamHandler;
use Monolog\Handler\RotatingFileHandler;

use DB;
use File;

use App\Models\Common\Buckets;
use App\Models\Common\BucketsRoles;
use App\Models\Common\Frame;
use App\Models\Core\Configs;

use App\Plugins\PluginBase;

use App\Traits\ConnectCommonTrait;

/**
 * ユーザープラグイン
 *
 * ユーザ用プラグインの基底クラス
 *
 * @author 永原　篤 <nagahara@opensource-workshop.jp>
 * @copyright OpenSource-WorkShop Co.,Ltd. All Rights Reserved
 * @category ユーザープラグイン
 * @package Contoroller
 */
class UserPluginBase extends PluginBase
{

    use ConnectCommonTrait;

    /**
     *  ページオブジェクト
     */
    public $page = null;

    /**
     *  ページ一覧オブジェクト
     */
    public $pages = null;

    /**
     *  フレームオブジェクト
     */
    public $frame = null;

    /**
     *  Buckets オブジェクト
     */
    public $buckets = null;

    /**
     *  Configs オブジェクト
     */
    public $configs = null;

    /**
     *  アクション
     */
    public $action = null;

    /**
     *  リクエスト
     */
    public $request = null;

    /**
     *  id
     */
    public $id = null;

    /**
     *  画面間用メッセージ
     */
    public $cc_massage = null;

    /**
     *  コンストラクタ
     */
    function __construct($page = null, $frame = null, $pages = null)
    {
        // ページの保持
        $this->page = $page;

        // フレームの保持
        $this->frame = $frame;

        // ページ一覧の保持
        $this->pages = $pages;

        // Buckets の保持
        $this->buckets = Buckets::select('buckets.*')
                                ->join('frames', function ($join) use ($frame) {
                                    $join->on('frames.bucket_id', '=', 'buckets.id')
                                         ->where('frames.id', '=', $frame->id);
                                })
                                ->first();

        // Configs の保持
        $this->configs = Configs::get();
    }

    /**
     *  HTTPリクエストメソッドチェック
     *
     * @param String $plugin_name
     * @return view
     */
    private function checkHttpRequestMethod($request, $action)
    {
        // メソッドのhttp動詞チェック(定数 CC_METHOD_REQUEST_METHOD に設定があること。)
        if (array_key_exists($this->action, config('cc_role.CC_METHOD_REQUEST_METHOD'))) {
            foreach (config('cc_role.CC_METHOD_REQUEST_METHOD')[$this->action] as $method_request_method) {
                if ($request->isMethod($method_request_method)) {
                    return true;
                }
            }
        }
        // 定数にメソッドの設定がない or 指定されたメソッド以外で呼ばれたときはエラー。
        return false;
    }

    /**
     *  関数定義チェック
     *
     * @param String $plugin_name
     * @return view
     */
    private function checkPublicFunctions($obj, $request, $action)
    {
        // 関数定義メソッドの有無確認
        if (method_exists($obj, 'getPublicFunctions')) {
            // 関数リスト取得
            $public_functions = $obj->getPublicFunctions();

            if (array_key_exists(mb_strtolower($request->method()), $public_functions)) {
                if (in_array($action, $public_functions[mb_strtolower($request->method())])) {
                    return true;
                }
            }
        }
        return false;
    }

    /**
     *  画面表示用にページやフレームなど呼び出し
     *
     * @param String $plugin_name
     * @return view
     */
    public function invoke($obj, $request, $action, $page_id, $frame_id, $id = null)
    {
        // アクションを保持しておく
        $this->action = $action;

        // リクエストを保持しておく(Hookで必要になった)
        $this->request = $request;

        // idを保持しておく(Hookで必要になった)
        $this->id = $id;

        // 関数定義メソッドの有無確認
        if (!method_exists($obj, $action)) {
            return $this->view_error("403_inframe", null, "存在しないメソッド");
        }

        // メソッドの可視性チェック
        $objReflectionMethod = new \ReflectionMethod(get_class($obj), $action);
        if (!$objReflectionMethod->isPublic()) {
            return $this->view_error("403_inframe", null, "メソッドの可視性チェック");
        }

        // コアで定義しているHTTPリクエストメソッドチェック
        //if (!$this->checkHttpRequestMethod($request, $action)) {
        //    return $this->view_error("403_inframe");
        //}

        // プラグイン側の関数定義チェック
        //if (!$this->checkPublicFunctions($obj, $request, $action)) {
        //    return $this->view_error("403_inframe");
        //}

        // コアで定義しているHTTPリクエストメソッドチェック ＆ プラグイン側の関数定義チェック の両方がエラーの場合、権限エラー
        if (!$this->checkHttpRequestMethod($request, $action) && !$this->checkPublicFunctions($obj, $request, $action)) {
            return $this->view_error("403_inframe", null, "HTTPリクエストメソッドチェック ＆ プラグイン側の関数定義チェック");
        }

        // チェック用POST
        $post = null;

        // POST チェックに使用する getPost() 関数の有無をチェック
        // POST に関連しないメソッドは除外
        if ($action != "destroyBuckets") {
            if ($id && method_exists($obj, 'getPost')) {
                $post = $obj->getPost($id, $action);
            }
        }

        // 定数 CC_METHOD_AUTHORITY に設定があるものはここでチェックする。
        $ret = $this->checkFunctionAuthority(config('cc_role.CC_METHOD_AUTHORITY'), $post);
        // 権限チェック結果。値があれば、エラーメッセージ用HTML
        if (!empty($ret)) {
            return $ret;
        }

        // 関数定義メソッドの有無確認
        if (method_exists($obj, 'declareRole')) {
            // 関数リスト取得
            $role_ckeck_table = $obj->declareRole();

            // 記載されているメソッドすべての権限を有することをチェック
            $ret = $this->checkFunctionAuthority($role_ckeck_table, $post);

            // 権限チェック結果。値があれば、エラーメッセージ用HTML
            if (!empty($ret)) {
                return $ret;
            }
        }

        // 画面(コアの cms_frame)で指定されたクラスのアクションのメソッドを呼び出す。
        // 戻り値は各アクションでのメソッドでview 関数などで生成したHTML なので、そのままreturn して元の画面に戻す。
        return $obj->$action($request, $page_id, $frame_id, $id);
    }

    /**
     * 記載されているメソッドすべての権限を有することをチェック
     *
     * @return view 権限チェックの結果、エラーがあればエラー表示用HTML が返ってくる。
     */
    private function checkFunctionAuthority($role_ckeck_table, $post = null)
    {
        // 設定があるものはここでチェックする。
        if (array_key_exists($this->action, $role_ckeck_table)) {
            // 記載されているメソッドすべての権限を有すること。
            foreach ($role_ckeck_table[$this->action] as $function_authority) {
                // 権限チェックの結果、エラーがあればエラー表示用HTML が返ってくる。
                $ret = null;

//print_r($this->buckets);
                // POST があれば、POST の登録者チェックを行う
                if (empty($post)) {
                    $ret = $this->can($function_authority, null, null, $this->buckets);
                } else {
//print_r($post);
                    $ret = $this->can($function_authority, $post, null, $this->buckets);
                }

                // 権限チェック結果。値があれば、エラーメッセージ用HTML
                if (!empty($ret)) {
                    return $ret;
                }
            }
        }

        return null;
    }

    /**
     *  View のパス
     *
     * @return view
     */
    protected function getViewPath($blade_name)
    {
        // 指定したテンプレートのファイル存在チェック
        if (File::exists(resource_path().'/views/plugins/user/' . $this->frame->plugin_name . "/" . $this->frame->template . "/" . $blade_name . ".blade.php")) {
            return 'plugins.user.' . $this->frame->plugin_name . '.' . $this->frame->template . '.' . $blade_name;
        }

        // デフォルトテンプレートのファイル存在チェック
        if (File::exists(resource_path().'/views/plugins/user/' . $this->frame->plugin_name . "/default/" . $blade_name . ".blade.php")) {
            return 'plugins.user.' . $this->frame->plugin_name . '.default.' . $blade_name;
        }

        // オプションの指定したテンプレートのファイル存在チェック
        if (File::exists(resource_path().'/views/plugins_option/user/' . $this->frame->plugin_name . "/" . $this->frame->template . "/" . $blade_name . ".blade.php")) {
            return 'plugins_option.user.' . $this->frame->plugin_name . '.' . $this->frame->template . '.' . $blade_name;
        }

        // オプションのデフォルトテンプレートのファイル存在チェック
        if (File::exists(resource_path().'/views/plugins_option/user/' . $this->frame->plugin_name . "/default/" . $blade_name . ".blade.php")) {
            return 'plugins_option.user.' . $this->frame->plugin_name . '.default.' . $blade_name;
        }

        return 'errors/template_notfound';
    }

    /**
     *  View のパス
     *
     * @return view
     */
    protected function getCommonViewPath($blade_name)
    {
        return 'plugins.common' . '.' . $blade_name;
    }

    /**
     *  編集画面の最初のタブ
     *
     *  フレームの編集画面がある各プラグインからオーバーライドされることを想定。
     */
    public function getFirstFrameEditAction()
    {
        return "frame_setting";
    }

    /**
     *  テンプレート
     *
     * @return view
     */
    public function getTemplate()
    {
        return $this->frame->template;
    }

    /**
     *  テーマ取得
     *  配列で返却['css' => 'テーマ名', 'js' => 'テーマ名']
     *  値がなければキーのみで値は空
     */
    protected function getThemeName()
    {
        // ページ固有の設定がある場合
        $theme = $this->page->theme;
        if ($theme) {
            return  $this->page->theme;
        }
        // テーマが設定されていない場合は一般設定の取得
        foreach ($this->configs as $config) {
            if ($config->name == 'base_theme') {
                return $config->value;
            }
        }
        return "";
    }

    /**
     * view 関数のラッパー
     * 共通的な要素を追加する。
     */
    private function addArg($arg)
    {
        // アクションをview に渡す
        $arg['action'] = $this->action;

        // 表示しているページオブジェクト
        $arg['page'] = $this->page;

        // 表示しているフレームオブジェクト
        $arg['frame'] = $this->frame;

        // 表示しているページID
        $arg['page_id'] = $this->page->id;

        // 表示しているフレームID
        $arg['frame_id'] = $this->frame->id;

        // 表示しているBuckets
        $arg['buckets'] = empty($this->buckets) ? null : $this->buckets;

        // 表示しているテーマ
        $arg['theme'] = $this->getThemeName();

        // 画面間用メッセージ
        $arg['cc_massage'] = $this->cc_massage;

        // テーマ Default ディレクトリの確認（テーマがグループテーマなら、グループ内のDefault）
        if (strpos($arg['theme'], '/') !== false) {
            $arg['theme_group'] = mb_substr($arg['theme'], 0, strpos($arg['theme'], '/'));
            $arg['theme_group_default'] = mb_substr($arg['theme'], 0, strpos($arg['theme'], '/')) . '/Default';
        } else {
            $arg['theme_group'] = '';
            $arg['theme_group_default'] = '';
        }

        return $arg;
    }
    /**
     * view 関数のラッパー
     * 共通的な要素を追加する。
     */
    public function view($blade_name, $arg = null)
    {

        // view の共通引数のセット
        $arg = $this->addArg($arg);

        // action があること（indexは一旦、対象外）
        if (!empty($this->action)) {
            // クラス名をnamespace 毎取得
            $instance_name = explode('\\', get_class($this));
            if (is_array($instance_name) && $instance_name[0] == 'App' && $instance_name[1] == 'Plugins' && $instance_name[2] == 'User' && !empty($instance_name[3])) {
                // 引数のアクションと同じメソッドを呼び出す。
                $class_name = "App\Plugins\Hook\User\\" . $instance_name[3] . "\\" . $instance_name[3] . ucfirst($this->action) . "Hook";

                // クラスの存在チェック後、呼び出し
                if (class_exists($class_name)) {
                    $hookPlugin = new $class_name();
                    $arg['hook'] = $hookPlugin->hook($this->request, $this->page->id, $this->frame->id, $this->id);
                }
            }
        }

        // 表示テンプレートを呼び出す。
        return view($this->getViewPath($blade_name), $arg);
    }

    /**
     * view 関数のラッパー
     * 共通的な要素を追加する。
     */
    public function commonView($blade_name, $arg = null)
    {
        // view の共通引数のセット
        $arg = $this->addArg($arg);

        // 表示テンプレートを呼び出す。
        return view($this->getCommonViewPath($blade_name), $arg);
    }

    /**
     * フォーム選択表示関数
     */
    public function listBuckets($request, $page_id, $frame_id, $id = null)
    {
        // 対象のプラグイン
        $plugin_name = $this->frame->plugin_name;

        // Frame データ
        $plugin_frame = DB::table('frames')
                            ->select('frames.*')
                            ->where('frames.id', $frame_id)->first();

        // データ取得（1ページの表示件数指定）
        $plugins = DB::table($plugin_name)
                       ->select($plugin_name . '.*', $plugin_name . '.' . $plugin_name . '_name as plugin_bucket_name')
                       ->orderBy('created_at', 'desc')
                       ->paginate(10);

        // 表示テンプレートを呼び出す。
        return $this->commonView(
            'edit_datalist', [
            'plugin_frame' => $plugin_frame,
            'plugins'      => $plugins,
            ]
        );
    }

    /**
     * 画面からのソート指定があれば取得
     */
    protected function getRequestOrderBy($request_sort, $request_order_by)
    {
        // 画面からのソート指定があれば使用
        if (!empty($request_sort)) {
            $request_order_by = explode('|', $request_sort);
        }
        return $request_order_by;
    }

    /**
     * 画面でのリンク用ソート指示(ソート指定されている場合はソート指定を逆転したもの) 取得
     */
    protected function getSortOrderLink($request_sort, $sort_inits, $request_order_by)
    {
        // 画面からのソート指定があれば使用(ソート指定があった項目は、ソート設定の内容を入れ替える)
        if (!empty($request_sort)) {
            //$request_order_by = explode('|', $request_sort);
            if ($request_order_by[1] == "asc") {
                $sort_inits[$request_order_by[0]]=["asc", "desc"];
            } else {
                $sort_inits[$request_order_by[0]]=["desc", "asc"];
            }
        }

        // 画面でのリンク用ソート指示(ソート指定されている場合はソート指定を逆転したもの)
        $order_link = array();
        foreach ($sort_inits as $order_by_key => $order_by) {
            if ($request_order_by[0]==$order_by_key && $request_order_by[1]==$order_by[0]) {
                $order_link[$order_by_key] = array_reverse($order_by);
            } else {
                $order_link[$order_by_key] = $order_by;
            }
        }
        return $order_link;
    }

    /**
     *  フレームとBuckets 取得
     */
    protected function getBuckets($frame_id)
    {
        $backets = Buckets::select('buckets.*', 'frames.id as frames_id')
                      ->join('frames', 'frames.bucket_id', '=', 'buckets.id')
                      ->where('frames.id', $frame_id)
                      ->first();
        return $backets;
    }

    /**
     * 権限設定 変更画面
     */
    public function editBucketsRoles($request, $page_id, $frame_id, $id = null, $use_approval = true)
    {
        // Buckets の取得
        $buckets = $this->getBuckets($frame_id);

        return $this->commonView('frame_edit_buckets', [
            'buckets'      => $buckets,
            'plugin_name'  => $this->frame->plugin_name,
            'use_approval' => $use_approval,
        ]);
    }

    /**
     * チェックボックス値取得
     */
    private function isRequestRole($request_role, $check_role)
    {
        if (array_key_exists($check_role, $request_role)) {
            if ($request_role[$check_role] == '1') {
                return 1;
            }
        }
        return 0;
    }

    /**
     * Buckets権限の更新
     */
    private function saveRequestRole($request, $buckets, $role_name)
    {
        // 権限毎にBuckets権限を読み、更新。レコードがなければ追加。
        // 画面から該当の権限の項目が渡ってこなければ、権限をはく奪したものとしてレコード削除
        $buckets_role = BucketsRoles::where('buckets_id', $buckets->id)
                                    ->where('role', $role_name)
                                    ->first();
        if ($request->has($role_name)) {
            if (empty($buckets_role)) {
                $buckets_role = new BucketsRoles;
                $buckets_role->buckets_id  = $buckets->id;
                $buckets_role->role  = $role_name;
            }
            $buckets_role->post_flag     = $this->isRequestRole($request->$role_name, 'post');
            $buckets_role->approval_flag = $this->isRequestRole($request->$role_name, 'approval');
            $buckets_role->save();
        } else {
            if ($buckets_role) {
                $buckets_role->delete();
            }
        }
        return;
    }

    /**
     * 権限設定 保存処理
     */
    public function saveBucketsRoles($request, $page_id, $frame_id, $id = null, $use_approval = true)
    {
        // Buckets の取得
        $buckets = $this->getBuckets($frame_id);

        // buckets がまだない & 固定記事プラグインの場合
        if (empty($buckets) && $this->frame->plugin_name == 'contents') {
            $buckets = new Buckets;
            $buckets->bucket_name = '無題';
            $buckets->plugin_name = 'contents';
            // Buckets の更新
            $buckets->save();

            // Frame にbuckets_id を登録
            Frame::where('id', $frame_id)
                 ->update(['bucket_id' => $buckets->id]);
        }

        // BucketsRoles の更新
        $this->saveRequestRole($request, $buckets, 'role_reporter');
        $this->saveRequestRole($request, $buckets, 'role_article');

        // 画面の呼び出し
        return $this->commonView('frame_edit_buckets', [
            'buckets'      => $buckets,
            'plugin_name'  => $this->frame->plugin_name,
            'use_approval' => $use_approval,
        ]);
    }

    /**
     *  ページ取得
     */
    protected function getPages($format = null)
    {
        // format 指定なしはフラットな形式
        if ($format == null) {
            return $this->pages;
        }

        // layer1 は親とその下を1階層の配列に束ねるもの
        if ($format == 'layer1') {
            // 戻り値用
            $ret_array = array();

            // 一度ツリーにしてから、親と子を分ける。ツリーにしないと、親と子の見分けがし難かったので。
            $tree = $this->pages->toTree();

            // クロージャ。子を再帰呼び出しするためのもの。
            $recursiveMenu = function ($pages, $page_id) use (&$recursiveMenu, &$ret_array) {
                foreach ($pages as $page) {
                    //$ret_array[$page_id]['child'][] = $page->page_name;
                    $ret_array[$page_id]['child'][] = $page;
                    if (count($page->children) > 0) {
                        // 孫以降の呼び出し。page_id は親のものを引き継ぐことに、1階層に集約する。
                        $recursiveMenu($page->children, $page_id);
                    }
                };
            };

            // 親階層のループ
            foreach ($tree as $pages) {
                //$ret_array[$pages->id]['parent'] = $pages->page_name;
                $ret_array[$pages->id]['parent'] = $pages;
                if (count($pages->children) > 0) {
                    $recursiveMenu($pages->children, $pages->id);
                }
            }
            // Log::debug($ret_array);
            return $ret_array;
        }
    }

    /**
     *  言語の取得
     */
    protected function getLanguages()
    {
        $configs = $this->configs;
        if (empty($configs)) {
            return null;
        }

        $languages = array();
        foreach ($configs as $config) {
            if ($config->category == 'language') {
                $languages[$config->additional1] = $config;
            }
        }
        return $languages;
    }

    /**
     *  ログ出力
     */
    public function putLog($e)
    {
        // Config データの取得
        $configs = Configs::where('category', 'log')->get();

        // ログファイル名
        $log_filename = 'Laravel';

        $config_log_filename_choice_obj = $configs->where('name', 'log_filename_choice')->first();

        $config_log_filename_obj = $configs->where('name', 'log_filename')->first();
        if (empty($config_log_filename_obj)) {
            $config_log_filename = "";
        } else {
            $config_log_filename = $config_log_filename_obj->value;
        }

        if (!empty($config_log_filename_choice_obj) && $config_log_filename_choice_obj->value == '1' && isset($config_log_filename)) {
            $log_filename = $config_log_filename;
        }
        $log_path =  storage_path() .'/logs/' . $log_filename . '.log';

        // ログレベル
        $log_level =  config('app.log_level');

        // 以降のハンドラに処理を続行させるかどうかのフラグ、デフォルトは、true
        $bubble = true;

        // ログを生成
        $log = new Logger('connect_error_log');

        // ハンドラー（単一 or 日付毎）
        $log_handler_obj = $configs->where('name', 'log_handler')->first();
        if (!empty($log_handler_obj) && $log_handler_obj->value == '1') {
            $handler = new RotatingFileHandler($log_path, $maxFiles = 0, $log_level, $bubble);
        } else {
            $handler = new StreamHandler($log_path, $log_level, $bubble);
        }

        // StackTrace用フォーマッタで整形
        $formatter = new LineFormatter();
        $formatter->includeStacktraces(true);

        // ログ出力
        $log->pushHandler($handler->setFormatter($formatter));
        $log->error($e);
    }

    /**
     * 権限チェック
     * roll_or_auth : 権限 or 役割
     */
/*

Trait へ移動（App\Http\Controllers\Core\ConnectController）

    public function can($roll_or_auth, $post = null, $plugin_name = null)
    {
        $args = null;
        if ( $post != null || $plugin_name != null ) {
            $args = [[$post, $plugin_name]];
        }

        if (!Auth::check() || !Auth::user()->can($roll_or_auth, $args)) {
            return $this->view_error(403);
        }
    }
*/
    /**
     * エラー画面の表示
     *
     */
/*

Trait へ移動（App\Http\Controllers\Core\ConnectController）

    public function view_error($error_code)
    {
        // 表示テンプレートを呼び出す。
        return view('errors.' . $error_code);
    }
*/
}
