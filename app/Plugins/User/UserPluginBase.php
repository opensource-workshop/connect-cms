<?php

namespace App\Plugins\User;

use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;

use Monolog\Logger;
use Monolog\Formatter\LineFormatter;
use Monolog\Handler\StreamHandler;
use Monolog\Handler\RotatingFileHandler;

use Symfony\Component\Process\PhpExecutableFinder;
// use Symfony\Component\Process\Process;

use HTMLPurifier;
use HTMLPurifier_Config;
use Request;

use Carbon\Carbon;

use App\Jobs\ApprovalNoticeJob;
use App\Jobs\ApprovedNoticeJob;
use App\Jobs\DeleteNoticeJob;
use App\Jobs\PostNoticeJob;
use App\Jobs\RelateNoticeJob;

use App\Models\Common\Buckets;
use App\Models\Common\BucketsMail;
use App\Models\Common\BucketsRoles;
use App\Models\Common\Frame;
use App\Models\Common\Group;
use App\Models\Core\Configs;
use App\Models\Core\FrameConfig;

use App\Enums\NoticeJobType;
use App\Enums\StatusType;

use App\Plugins\PluginBase;
use App\Plugins\Manage\UserManage\UsersTool;

use App\Rules\CustomValiEmails;
use App\Rules\CustomValiRequiredWithoutAllSupportsArrayInput;

use App\Traits\ConnectCommonTrait;
use App\Traits\ConnectRoleTrait;

/**
 * ユーザープラグイン
 *
 * ユーザ用プラグインの基底クラス
 *
 * @author 永原　篤 <nagahara@opensource-workshop.jp>
 * @copyright OpenSource-WorkShop Co.,Ltd. All Rights Reserved
 * @category ユーザープラグイン
 * @package Controller
 */
class UserPluginBase extends PluginBase
{
    use ConnectCommonTrait, ConnectRoleTrait;

    /**
     * ページオブジェクト
     */
    public $page = null;

    /**
     * ページ一覧オブジェクト
     */
    public $pages = null;

    /**
     * フレームオブジェクト
     */
    public $frame = null;

    /**
     * Buckets オブジェクト
     */
    public $buckets = null;

    /**
     * Configs オブジェクト
     */
    public $configs = null;

    /**
     * FrameConfig オブジェクト
     * FrameConfigのCollection
     */
    public $frame_configs = null;

    /**
     * アクション
     */
    public $action = null;

    /**
     * リクエスト
     */
    public $request = null;

    /**
     * id
     */
    public $id = null;

    /**
     * purifier
     * 保存処理時にインスタンスが代入され、シングルトンで使うことを想定
     */
    public $purifier = null;

    /**
     * 画面間用メッセージ
     */
    public $cc_massage = null;

    /**
     * POST チェックに使用する getPost() 関数を使うか
     */
    public $use_getpost = true;

    /**
     * コンストラクタ
     */
    public function __construct($page = null, $frame = null, $pages = null, $page_tree = null)
    {
        // ページの保持
        $this->page = $page;

        // bugfix: URLのフレームIDを手で書き換えられた場合、frameがnullになる事がありえるため対応
        $frame = $frame ?? new Frame();

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
        // $this->configs = Configs::get();
        $this->configs = Configs::getSharedConfigs();

        $this->setFrameConfigs();
    }

    /**
     * HTTPリクエストメソッドチェック
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
     * 関数定義チェック
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
     * 画面表示用にページやフレームなど呼び出し
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
            // [TODO] DefaultController::invokePostRedirect() から「invokeを通して呼び出すことで権限チェックを実施」している関係で、この処理をいれていて、ちょっと無理くり対応している。
            // Redirect時のエラー対応. リダイレクトせずエラー画面表示する。
            // Redirect時に権限エラー等で403のViewオブジェクトを返しても、リダイレクト処理が走り、エラー画面が表示されないため、ここでエラー時はリダイレクトしない設定をリクエストに入れる。
            $request->merge(['return_mode' => 'asis']);
            return $this->viewError("403_inframe", null, "存在しないメソッド");
        }

        // メソッドの可視性チェック
        $objReflectionMethod = new \ReflectionMethod(get_class($obj), $action);
        if (!$objReflectionMethod->isPublic()) {
            // Redirect時のエラー対応. リダイレクトせずエラー画面表示する。
            $request->merge(['return_mode' => 'asis']);
            return $this->viewError("403_inframe", null, "メソッドの可視性チェック");
        }

        // コアで定義しているHTTPリクエストメソッドチェック
        //if (!$this->checkHttpRequestMethod($request, $action)) {
        //    return $this->viewError("403_inframe");
        //}

        // プラグイン側の関数定義チェック
        //if (!$this->checkPublicFunctions($obj, $request, $action)) {
        //    return $this->viewError("403_inframe");
        //}

        // コアで定義しているHTTPリクエストメソッドチェック ＆ プラグイン側の関数定義チェック の両方がエラーの場合、権限エラー
        if (!$this->checkHttpRequestMethod($request, $action) && !$this->checkPublicFunctions($obj, $request, $action)) {
            // Redirect時のエラー対応. リダイレクトせずエラー画面表示する。
            $request->merge(['return_mode' => 'asis']);
            return $this->viewError("403_inframe", null, "HTTPリクエストメソッドチェック ＆ プラグイン側の関数定義チェック");
        }

        // チェック用POST
        $post = null;

        // POST チェックに使用する getPost() 関数の有無をチェック
        if ($this->use_getpost) {
            // POST に関連しないメソッドは除外
            if ($action != "destroyBuckets") {
                if ($id && method_exists($obj, 'getPost')) {
                    $post = $obj->getPost($id, $action);
                }
            }
        }

        // 定数 CC_METHOD_AUTHORITY に設定があるものはここでチェックする。
        $ret = $this->checkFunctionAuthority(config('cc_role.CC_METHOD_AUTHORITY'), $post);
        // 権限チェック結果。値があれば、エラーメッセージ用HTML
        if (!empty($ret)) {
            // Redirect時のエラー対応. リダイレクトせずエラー画面表示する。
            $request->merge(['return_mode' => 'asis']);
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
                // Redirect時のエラー対応. リダイレクトせずエラー画面表示する。
                $request->merge(['return_mode' => 'asis']);
                return $ret;
            }
        }

        // 画面(コアの cms_frame)で指定されたクラスのアクションのメソッドを呼び出す。
        // 戻り値は各アクションでのメソッドでview 関数などで生成したHTML なので、そのままreturn して元の画面に戻す。
        return $obj->$action($request, $page_id, $frame_id, $id);
    }

    /**
     * バケツID取得
     */
    protected function getBucketId()
    {
        if (empty($this->buckets)) {
            return null;
        }
        return $this->buckets->id;
    }

    /**
     * 記載されているメソッドすべての権限を有することをチェック
     *
     * @return view|null 権限チェックの結果、エラーがあればエラー表示用HTML が返ってくる。
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
                    // $ret = $this->can($function_authority, null, null, $this->buckets);
                    $ret = $this->can($function_authority, null, $this->frame->plugin_name, $this->buckets, $this->frame);
                } else {
//print_r($post);
                    // $ret = $this->can($function_authority, $post, null, $this->buckets);
                    $ret = $this->can($function_authority, $post, $this->frame->plugin_name, $this->buckets, $this->frame);
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
     * View のパス
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
     * View のパス
     *
     * @return view
     */
    protected function getCommonViewPath($blade_name)
    {
        return 'plugins.common' . '.' . $blade_name;
    }

    /**
     * 編集画面の最初のタブ
     *
     * フレームの編集画面がある各プラグインからオーバーライドされることを想定。
     */
    public function getFirstFrameEditAction()
    {
        return "frame_setting";
    }

    /**
     * テンプレート
     *
     * @return view
     */
    public function getTemplate()
    {
        return $this->frame->template;
    }

    /**
     * テーマ取得
     * 配列で返却['css' => 'テーマ名', 'js' => 'テーマ名']
     * 値がなければキーのみで値は空
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

        // 表示しているフレームのフレーム設定
        $arg['frame_configs'] = $this->frame_configs;

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
     * バケツ選択表示関数
     *
     * @method_title 選択
     * @method_desc このフレームに表示するプラグインのバケツを選択します。
     * @method_detail
     */
    public function listBuckets($request, $page_id, $frame_id, $id = null)
    {
        // 対象のプラグイン
        $plugin_name = $this->frame->plugin_name;

        // Frame データ
        $plugin_frame = Frame::where('frames.id', $frame_id)->first();

        // データ取得（1ページの表示件数指定）
        $plugins = DB::table($plugin_name)
            ->select($plugin_name . '.*', $plugin_name . '.' . $plugin_name . '_name as plugin_bucket_name')
            ->orderBy('created_at', 'desc')
            ->paginate(10, ["*"], "frame_{$frame_id}_page");

        if ($plugins->isEmpty()) {
            // バケツ空テンプレートを呼び出す。
            return $this->commonView('empty_bucket_setting');
        }

        // 表示テンプレートを呼び出す。
        return $this->commonView('edit_datalist', [
            'plugin_frame' => $plugin_frame,
            'plugins'      => $plugins,
        ]);
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
     * フレームとBuckets 取得
     */
    protected function getBuckets($frame_id)
    {
        $buckets = Buckets::select('buckets.*', 'frames.id as frames_id')
                      ->join('frames', 'frames.bucket_id', '=', 'buckets.id')
                      ->where('frames.id', $frame_id)
                      ->first();
        return $buckets;
    }

    /**
     * Buckets のメール設定取得
     */
    protected function getBucketMail($bucket)
    {
        if (empty($bucket)) {
            return new BucketsMail();
        }
        return BucketsMail::firstOrNew(['buckets_id' => $bucket->id]);
    }

    /**
     * 権限設定 変更画面
     */
    public function editBucketsRoles($request, $page_id, $frame_id, $id = null, $use_approval = true)
    {
        // Buckets の取得
        $buckets = $this->getBuckets($frame_id);

        if ($this->frame->plugin_name == 'contents' || $buckets) {
            // 固定記事プラグイン(=コンテンツプラグイン)はバケツありなし、どちらでも表示する。
            // 固定記事プラグイン(=コンテンツプラグイン)以外はバケツありのみ、表示する。
        } else {
            // 表示しない。
            return $this->commonView('empty_bucket_setting');
        }

        return $this->commonView('frame_edit_buckets', [
            'buckets'      => $buckets,
            'plugin_name'  => $this->frame->plugin_name,
            'use_approval' => $use_approval,
        ]);
    }

    /**
     * メール送信設定 変更画面
     *
     * @method_title メール送信設定
     * @method_desc プラグインのメール送信条件を設定します。
     * @method_detail 送信タイミングや送信先、件名、本文などを設定します。
     */
    public function editBucketsMails($request, $page_id, $frame_id, $id = null)
    {
        // Buckets の取得
        $bucket = $this->getBuckets($frame_id);

        // Bucket が取れない場合は表示しない。
        if (empty($bucket)) {
            return $this->commonView('empty_bucket_setting');
        }

        // Buckets のメール設定取得
        $bucket_mail = $this->getBucketMail($bucket);
        // チェックボックス値の配列化
        $bucket_mail->notice_groups_array   = explode(UsersTool::CHECKBOX_SEPARATOR, $bucket_mail->notice_groups);
        $bucket_mail->approval_groups_array = explode(UsersTool::CHECKBOX_SEPARATOR, $bucket_mail->approval_groups);
        $bucket_mail->approved_groups_array = explode(UsersTool::CHECKBOX_SEPARATOR, $bucket_mail->approved_groups);

        // 使用するメール送信メソッドが指定されている場合は、そのメソッドの指定のみできるようにする。
        if (method_exists($this, 'useBucketMailMethods')) {
            // メール送信メソッドの取得
            $use_bucket_mail_methods = $this->useBucketMailMethods();
        } else {
            // メール送信メソッドの初期値（全て：投稿通知、関連通知、承認待ち、承認済み）
            $use_bucket_mail_methods = ['notice', 'relate', 'approval', 'approved'];
        }

        $groups = Group::orderBy('id', 'asc')->get();

        return $this->commonView('frame_edit_mails', [
            'bucket'       => $bucket,
            'bucket_mail'  => $bucket_mail,
            'plugin_name'  => $this->frame->plugin_name,
            'use_bucket_mail_methods' => $use_bucket_mail_methods,
            'groups'       => $groups,
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

        // Backet が取れないとおかしな操作をした可能性があるのでエラーにしておく。
        if (empty($buckets)) {
            return $this->viewError("error_inframe", "存在しないBucket");
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
     * リクエストで指定の値が空なら 0 を返す
     */
    protected function inputNullToZero($request, $name)
    {
        if (empty($request->$name)) {
            return 0;
        }
        return $request->$name;
    }

    /**
     * メール設定 保存処理
     */
    public function saveBucketsMails($request, $page_id, $frame_id, $block_id)
    {
        // Buckets の取得
        $bucket = $this->getBuckets($frame_id);

        // buckets がない場合
        if (empty($bucket)) {
            return $this->viewError("error_inframe", "存在しないBucket");
        }

        // redirect_path
        $redirect_path_array = [
            'redirect_path' => url('/') . "/plugin/" . $this->frame->plugin_name . "/editBucketsMails/" . $page_id . "/" . $frame_id . "/" . $bucket->id . "#frame-" . $frame_id
        ];

        // 項目のエラーチェック
        $rules = [
            'notice_addresses' => [],
            'approval_addresses' => [],
            'approved_addresses' => [],
        ];

        // １項目複数ルール時に nullable を入れると CustomVali を入れても null でチェックOKになってしまうため、入力が有った時だけemailチェック追加
        if ($request->notice_addresses) {
            $rules['notice_addresses'][] = new CustomValiEmails();
        }
        if ($request->approval_addresses) {
            $rules['approval_addresses'][] = new CustomValiEmails();
        }
        if ($request->approved_addresses) {
            $rules['approved_addresses'][] = new CustomValiEmails();
        }

        if ($request->notice_on) {
            // 投稿通知onの時、送信先メール or 送信先グループ いずれか必須
            $name = '送信先メールアドレス, 全ユーザに通知, 送信先グループ';
            $rules['notice_addresses'][]  = new CustomValiRequiredWithoutAllSupportsArrayInput([$request->notice_groups, $request->notice_everyone], $name);
            $rules['notice_groups']       = [new CustomValiRequiredWithoutAllSupportsArrayInput([$request->notice_addresses, $request->notice_everyone], $name)];
            $rules['notice_everyone']     = [new CustomValiRequiredWithoutAllSupportsArrayInput([$request->notice_groups, $request->notice_addresses], $name)];
        }
        if ($request->approval_on) {
            // 承認通知onの時、送信先メール or 送信先グループ いずれか必須
            $name = '送信先メールアドレス, 送信先グループ';
            $rules['approval_addresses'][]  = new CustomValiRequiredWithoutAllSupportsArrayInput([$request->approval_groups], $name);
            $rules['approval_groups']       = [new CustomValiRequiredWithoutAllSupportsArrayInput([$request->approval_addresses], $name)];
        }
        if ($request->approved_on) {
            // 承認済み通知onの時、投稿者へ通知 or 送信先メール or 送信先グループ いずれか必須
            $name = '投稿者へ通知する, 送信先メールアドレス, 送信先グループ';
            $rules['approved_author']      = [new CustomValiRequiredWithoutAllSupportsArrayInput([$request->approved_addresses, $request->approved_groups], $name)];
            $rules['approved_addresses'][] = new CustomValiRequiredWithoutAllSupportsArrayInput([$request->approved_author, $request->approved_groups], $name);
            $rules['approved_groups']      = [new CustomValiRequiredWithoutAllSupportsArrayInput([$request->approved_author, $request->approved_addresses], $name)];
        }

        $validator = Validator::make($request->all(), $rules);
        $validator->setAttributeNames([
            'notice_addresses' => '送信先メールアドレス',
            'notice_groups' => '送信先グループ',
            'notice_everyone' => '全ユーザに通知',
            'approval_addresses' => '送信先メールアドレス',
            'approval_groups' => '送信先グループ',
            'approved_author' => '投稿者へ通知する',
            'approved_addresses' => '送信先メールアドレス',
            'approved_groups' => '送信先グループ',
        ]);

        // エラーがあった場合は入力画面に戻る。
        if ($validator->fails()) {
            // 共通画面のため、redirect_pathが画面に書けないため、ここで設定
            $request->merge($redirect_path_array);
            return redirect()->back()->withErrors($validator)->withInput();
        }

        // Buckets のメール設定取得
        $bucket_mail = $this->getBucketMail($bucket);

        // BucketsMails の設定受け取り
        $bucket_mail->buckets_id         = $bucket->id;  // メール設定がまだ存在しない場合は buckets_id がないので、設定する。

        // 投稿通知
        $bucket_mail->timing             = $this->inputNullToZero($request, "timing");
        $bucket_mail->notice_on          = $this->inputNullToZero($request, "notice_on");
        $bucket_mail->notice_create      = $this->inputNullToZero($request, "notice_create");
        $bucket_mail->notice_update      = $this->inputNullToZero($request, "notice_update");
        $bucket_mail->notice_delete      = $this->inputNullToZero($request, "notice_delete");
        $bucket_mail->notice_addresses   = $request->notice_addresses;
        $bucket_mail->notice_everyone    = $this->inputNullToZero($request, "notice_everyone");
        // array_filter()でarrayの空要素削除, implode()でarrayを文字列化
        $bucket_mail->notice_groups      = $request->notice_groups ? implode(UsersTool::CHECKBOX_SEPARATOR, array_filter($request->notice_groups)) : null;
        $bucket_mail->notice_roles       = $request->notice_roles;
        $bucket_mail->notice_subject     = $request->notice_subject;
        $bucket_mail->notice_body        = $request->notice_body;

        // 関連記事通知
        $bucket_mail->relate_on          = $this->inputNullToZero($request, "relate_on");
        $bucket_mail->relate_subject     = $request->relate_subject;
        $bucket_mail->relate_body        = $request->relate_body;

        // 承認通知
        $bucket_mail->approval_on        = $this->inputNullToZero($request, "approval_on");
        $bucket_mail->approval_addresses = $request->approval_addresses;
        $bucket_mail->approval_groups    = $request->approval_groups ? implode(UsersTool::CHECKBOX_SEPARATOR, array_filter($request->approval_groups)) : null;
        $bucket_mail->approval_subject   = $request->approval_subject;
        $bucket_mail->approval_body      = $request->approval_body;

        // 承認済み通知
        $bucket_mail->approved_on        = $this->inputNullToZero($request, "approved_on");
        $bucket_mail->approved_author    = $this->inputNullToZero($request, "approved_author");
        $bucket_mail->approved_addresses = $request->approved_addresses;
        $bucket_mail->approved_groups    = $request->approved_groups ? implode(UsersTool::CHECKBOX_SEPARATOR, array_filter($request->approved_groups)) : null;
        $bucket_mail->approved_subject   = $request->approved_subject;
        $bucket_mail->approved_body      = $request->approved_body;

        // BucketsMails の更新
        $bucket_mail->save();

        // 登録後はリダイレクトしてメール設定ページを開く。
        return new Collection($redirect_path_array);
    }

    /**
     * 投稿通知の送信
     *
     * 基本：
     *   - 投稿通知を実装するには指定したデータ（$post_row, $before_row）のテーブルに status, first_committed_at カラムがある事
     *   - 指定したデータのテーブルのモデルで trait UserableNohistory を使ってる事。（[TODO] 2021/10/15時点 Userableは first_committed_at の自動セット未対応）
     */
    public function sendPostNotice($post_row, $before_row, $show_method, array $overwrite_notice_embedded_tags = [])
    {
        // 行を表すレコードかのチェック
        //if (Schema::hasColumn($post_row->getTable(), 'id') && Schema::hasColumn($post_row->getTable(), 'first_committed_at') && Schema::hasColumn($post_row->getTable(), 'status')) {
        //    // 続き
        //} else {
        //    // 通知の送信をしない
        //    return;
        //}

        // buckets がない場合
        if (empty($this->buckets)) {
            return $this->viewError("error_inframe", "存在しないBucket");
        }

        // Buckets のメール設定取得
        $bucket_mail = $this->getBucketMail($this->buckets);

        // --- メール送信の条件を確認

        // メールを送信すべき命令を確認
        $notice_methods = array();

        // 記事が一時保存（status === 1）状態の場合は何もしない。
        if ($post_row->status === 1) {
            return;
        }

        // 承認通知がon の場合
        // 記事が承認待ち（status === 2）の場合は、承認待ちメールを送る。（notice_approval）
        // before_row（更新前）をチェックしない。
        // 承認待ちの記事を変更した場合は、再度、承認待ちメールが飛ぶが、それでOKだと考える。
        if ($bucket_mail->approval_on === 1 && $post_row->status === 2) {
            $notice_methods[] = NoticeJobType::notice_approval;
        }

        // 承認済み通知がon の場合
        // before_row（更新前）の（status === 2）でpost_row の status が公開（=== 0）の場合に送信（notice_approved）
        if ($bucket_mail->approved_on === 1 && $before_row->status === 2 && $post_row->status === 0) {
            $notice_methods[] = NoticeJobType::notice_approved;
        }

        // 投稿通知がon の場合
        // before_row（更新前）のfirst_committed_at が空でstatus が公開（=== 0）は「登録」（notice_create）
        // before_row（更新前）のfirst_committed_at が空ではなく、status が公開（=== 0）は「更新」（notice_update）
        if ($bucket_mail->notice_on === 1) {
            if ($bucket_mail->notice_create === 1 && empty($before_row->first_committed_at) && $post_row->status === 0) {
                // 対象（登録）
                $notice_methods[] = NoticeJobType::notice_create;
            } elseif ($bucket_mail->notice_update === 1 && !empty($before_row->first_committed_at) && $post_row->status === 0) {
                // 対象（変更）
                $notice_methods[] = NoticeJobType::notice_update;
            }
        }

        // 送信対象の命令がなかった場合は何もせずに戻る。
        if (empty($notice_methods)) {
            return;
        }

        // --- メール送信の処理を呼ぶ

        // 承認通知
        if (in_array(NoticeJobType::notice_approval, $notice_methods, true)) {
            // // 送信方法の確認
            // if ($bucket_mail->timing == 0) {
            //     // 即時送信
            //     dispatch_now(new ApprovalNoticeJob($this->frame, $this->buckets, $post_row, $show_method));
            // } else {
            //     // スケジュール送信
            //     ApprovalNoticeJob::dispatch($this->frame, $this->buckets, $post_row, $show_method);
            // }

            // 埋め込みタグ
            $notice_embedded_tags = BucketsMail::getNoticeEmbeddedTags($this->frame, $this->buckets, $post_row, $overwrite_notice_embedded_tags, $show_method, NoticeJobType::notice_approval);

            // 送信
            //   - .envのQUEUE_CONNECTION=syncの場合、dispatch()でも即時送信になるため、メール送信メソッド１本化
            ApprovalNoticeJob::dispatch($this->buckets, $notice_embedded_tags);
        }

        // 承認済み通知
        if (in_array(NoticeJobType::notice_approved, $notice_methods, true)) {
            // // 送信方法の確認
            // if ($bucket_mail->timing == 0) {
            //     // 即時送信
            //     dispatch_now(new ApprovedNoticeJob($this->frame, $this->buckets, $post_row, $post_row->created_id, $show_method));
            // } else {
            //     // スケジュール送信
            //     ApprovedNoticeJob::dispatch($this->frame, $this->buckets, $post_row, $post_row->created_id, $show_method);
            // }

            $notice_embedded_tags = BucketsMail::getNoticeEmbeddedTags($this->frame, $this->buckets, $post_row, $overwrite_notice_embedded_tags, $show_method, NoticeJobType::notice_approved);

            ApprovedNoticeJob::dispatch($this->buckets, $notice_embedded_tags, $post_row->created_id);
        }

        // 投稿通知（登録）
        if (in_array(NoticeJobType::notice_create, $notice_methods, true)) {
            // // 送信方法の確認
            // if ($bucket_mail->timing == 0) {
            //     // 即時送信
            //     dispatch_now(new PostNoticeJob($this->frame, $this->buckets, $post_row, $show_method, "notice_create"));
            // } else {
            //     // スケジュール送信
            //     PostNoticeJob::dispatch($this->frame, $this->buckets, $post_row, $show_method, "notice_create");
            // }

            $notice_embedded_tags = BucketsMail::getNoticeEmbeddedTags($this->frame, $this->buckets, $post_row, $overwrite_notice_embedded_tags, $show_method, NoticeJobType::notice_create);

            PostNoticeJob::dispatch($this->buckets, $notice_embedded_tags);
        }

        // 投稿通知（変更）
        if (in_array(NoticeJobType::notice_update, $notice_methods, true)) {
            // // 送信方法の確認
            // if ($bucket_mail->timing == 0) {
            //     // 即時送信
            //     dispatch_now(new PostNoticeJob($this->frame, $this->buckets, $post_row, $show_method, "notice_update"));
            // } else {
            //     // スケジュール送信
            //     PostNoticeJob::dispatch($this->frame, $this->buckets, $post_row, $show_method, "notice_update");
            // }

            $notice_embedded_tags = BucketsMail::getNoticeEmbeddedTags($this->frame, $this->buckets, $post_row, $overwrite_notice_embedded_tags, $show_method, NoticeJobType::notice_update);

            PostNoticeJob::dispatch($this->buckets, $notice_embedded_tags);
        }

        // 非同期でキューワーカ実行
        $this->asyncQueueWork();
    }

    /**
     * 関連投稿通知の送信
     */
    public function sendRelateNotice($post, $before_post, $mail_users, $show_method, array $overwrite_notice_embedded_tags = [])
    {
        // buckets がない場合
        if (empty($this->buckets)) {
            return $this->viewError("error_inframe", "存在しないBucket");
        }

        // Buckets のメール設定取得
        $bucket_mail = $this->getBucketMail($this->buckets);

        // 関連記事通知がon の場合
        // status が公開（=== 0）は「関連記事通知」（notice_relate）
        // 送信先はJob クラスのhandle() メソッドで取得する。
        if ($bucket_mail->relate_on === 1 && $post->status === 0) {
            // 関連通知を送信する。
        } else {
            // 関連通知を送信しない。
            return;
        }

        // before_row（更新前）のfirst_committed_at が空でstatus が公開（=== 0）は「登録」（notice_create）
        // before_row（更新前）のfirst_committed_at が空ではなく、status が公開（=== 0）は「更新」（notice_update）
        if ($bucket_mail->notice_create === 1 && empty($before_post->first_committed_at) && $post->status === 0) {
            // 対象（登録）- 関連通知を送信する。
        } elseif ($bucket_mail->notice_update === 1 && !empty($before_post->first_committed_at) && $post->status === 0) {
            // 対象（変更）- 関連通知を送信しない。
            return;
        }

        // // 送信方法の確認
        // if ($bucket_mail->timing == 0) {
        //     // 即時送信
        //     dispatch_now(new RelateNoticeJob($this->frame, $this->buckets, $post, $show_method, $mail_users));
        // } else {
        //     // スケジュール送信
        //     RelateNoticeJob::dispatch($this->frame, $this->buckets, $post, $show_method, $mail_users);
        // }

        // 関連通知するメール
        $relate_user_emails = [];
        foreach ($mail_users as $relate_user) {
            if ($relate_user->email) {
                $relate_user_emails[] = $relate_user->email;
            }
        }

        // 埋め込みタグ
        $notice_embedded_tags = BucketsMail::getNoticeEmbeddedTags($this->frame, $this->buckets, $post, $overwrite_notice_embedded_tags, $show_method, NoticeJobType::notice_relate);

        RelateNoticeJob::dispatch($this->buckets, $notice_embedded_tags, $relate_user_emails);

        // 非同期でキューワーカ実行
        $this->asyncQueueWork();
    }

    /**
     * 削除通知の送信
     */
    public function sendDeleteNotice($post, $show_method, $delete_comment, array $overwrite_notice_embedded_tags = [])
    {
        // buckets がない場合
        if (empty($this->buckets)) {
            return $this->viewError("error_inframe", "存在しないBucket");
        }

        // Buckets のメール設定取得
        $bucket_mail = $this->getBucketMail($this->buckets);

        // 投稿通知の送信 on の確認
        if ($bucket_mail->notice_on == 0) {
            return;
        }

        // メールを送信すべき命令が送信対象か確認
        if ($bucket_mail->notice_delete == 1) {
            // 対象（登録）
        } else {
            // 対象外
            return;
        }

        // 送信方法の確認
        // if ($bucket_mail->timing == 0) {
        // if ($timing == SendMailTiming::sync) {
        //     // 同期送信
        //     // （物理削除時は、非同期だとメール送信前にデータが消えてしまいModelNotFoundExceptionエラーになるため、同期でメール送信）
        //     dispatch_now(new DeleteNoticeJob($this->frame, $this->buckets, $post, $title, $show_method, $delete_comment));
        // } else {
        //     // スケジュール送信
        //     DeleteNoticeJob::dispatch($this->frame, $this->buckets, $post, $title, $show_method, $delete_comment);

        //     // 非同期でキューワーカ実行
        //     $this->asyncQueueWork();
        // }

        // 埋め込みタグ
        $notice_embedded_tags = BucketsMail::getNoticeEmbeddedTags($this->frame, $this->buckets, $post, $overwrite_notice_embedded_tags, $show_method, NoticeJobType::notice_delete, $delete_comment);

        DeleteNoticeJob::dispatch($this->buckets, $notice_embedded_tags);

        // 非同期でキューワーカ実行
        $this->asyncQueueWork();
    }

    /**
     * 非同期でキューワーカ実行
     * - キューをセット後に非同期でキューワーカを実行、キューされたすべてのジョブを実行して、キューワーカをプロセス停止する。
     *
     * @see https://readouble.com/laravel/6.x/ja/queues.html#running-the-queue-worker キューされたすべてのジョブを処理し、終了する - Laravel 6.x キュー
     * @see config\queue.php キューの設定ファイル。[connections => [database => [retry_after]]](リトライ時間) > --timeout(タイムアウト) でないと例外が発生する。
     */
    private function asyncQueueWork()
    {
        if (config('queue.default') != 'database') {
            // キュードライバがdatabase以外は実行しない
            return;
        }

        // 実行可能な PHP バイナリの検索
        $php_binary_finder = new PhpExecutableFinder();
        $php = $php_binary_finder->find();

        // artisanコマンドのパス
        $artisan = base_path('artisan');

        // キューされたすべてのジョブを処理し、終了する。最大でも１時間でタイムアウト（強制終了）する。
        // php artisan queue:work --stop-when-empty --timeout=3600
        $php_artisan_command = "{$php} \"{$artisan}\" queue:work --stop-when-empty --timeout=3600";

        // 非同期実行する
        if (strpos(PHP_OS, 'WIN') !== false) {
            // Windows
            // - start /B 新しいウインドウを開かずにアプリケーションを起動する。
            $command = "start /B {$php_artisan_command}";
            $fp = popen($command, 'r');
            pclose($fp);
        } else {
            // Linux
            $command = "{$php_artisan_command} > /dev/null &";
            exec($command);
        }

        // [TODO] Laravel 6.x = Process v4.4.22 のため$process->setOptions()使えない。残念
        // $timeout = null;    // 念のため、Processクラスでのコマンド実行のタイムアウトの無効化（非同期実行で一瞬でコマンド実行終わるため、問題ないと思うけど念のため）
        // $process = new Process([$php_binary_path, 'artisan', 'queue:work', '--stop-when-empty', '--timeout=3600'], base_path(), null, null, $timeout);

        // @see https://symfony.com/doc/current/components/process.html Processのsymfony公式Doc
        // // このオプションを使用すると、メインスクリプトが終了した後も、サブプロセスを実行し続けることができます。
        // // - (Required Process >= 5.2)
        // // - Laravel 6.x = Process v4.4.22 のため使えない。残念
        // $process->setOptions(['create_new_console' => true]);

        // // 非同期実行（xamppではうまく動かなかった。Linuxサーバでは動いた。）
        // $process->start();

        // [debug] 同期実行
        // $process->mustRun();
    }

    /**
     * ページ取得
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

    // delete: どこからも呼ばれていなかったため、コメントアウト
    // /**
    //  * 言語の取得
    //  */
    // protected function getLanguages()
    // {
    //     return Configs::getLanguages();
    //     // $configs = $this->configs;
    //     // if (empty($configs)) {
    //     //     return null;
    //     // }

    //     // $languages = array();
    //     // foreach ($configs as $config) {
    //     //     if ($config->category == 'language') {
    //     //         $languages[$config->additional1] = $config;
    //     //     }
    //     // }
    //     // return $languages;
    // }

    /**
     * ログ出力
     */
    public function putLog($e)
    {
        // Config データの取得
        $configs = Configs::where('category', 'log')->get();

        // ログファイル名
        $log_filename = 'laravel';

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

        // ログレベル（Laravel6 でログのレベル指定が変わったため修正）
        $log_level =  config('logging.channels.errorlog.level');

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
     * HTMLPurifier の実行
     */
    public function clean($text)
    {
        if ($this->isHtmlPurifier()) {
            if (empty($this->purifier)) {
                // HTMLPurifierを設定するためのクラスを生成する
                $config = HTMLPurifier_Config::createDefault();

                if (!Storage::exists('tmp/htmlpurifier')) {
                    Storage::makeDirectory('tmp/htmlpurifier');
                }
                $config->set('Cache.SerializerPath', storage_path('app/tmp/htmlpurifier'));

                // bugfix: class指定を許可は、デフォルト null ですべてのクラスが許可されている http://htmlpurifier.org/live/configdoc/plain.html#Attr.AllowedClasses
                // $config->set('Attr.AllowedClasses', array()); // class指定を許可する
                $config->set('Attr.AllowedClasses', null); // class指定を許可する
                $config->set('Attr.EnableID', true);          // id属性を許可する
                $config->set('Filter.YouTube', true);         // Youtube埋め込みを許可する
                $config->set('HTML.TargetBlank', true);       // target="_blank" が使えるようにする
                $config->set('Attr.AllowedFrameTargets', ['_blank']);
                $config->set('HTML.SafeIframe', true);
                $config->set('URI.SafeIframeRegexp', '%^(https?:)?//(www\.youtube(?:-nocookie)?\.com/embed/|player\.vimeo\.com/video/)%'); //allow YouTube and Vimeo

                $this->purifier = new HTMLPurifier($config);
            }

            return $this->purifier->purify($text);
        }
        return $text;
    }

    /**
     * HTMLPurifier 制限の判定
     */
    private function isHtmlPurifier()
    {
        // 初期値は HTML制限する。
        $html_purifier = true;

        // ログインしていない場合は、制限する。
        if (!Auth::check()) {
            return $html_purifier;
        }

        // ユーザ情報
        $user = Auth::user();

        // コンテンツ権限がついていること。
        if (!empty($user->user_roles) && array_key_exists('base', $user->user_roles)) {
            // delete: 使ってない変数
            // セキュリティ管理のHTML制限を取得
            // $config_html_purifiers = $this->configs->where('category', 'html_purifier');

            // 設定されている権限
            $purifiers = config('cc_role.CC_HTMLPurifier_ROLE_LIST');

            // Config テーブルからHTML記述制限の取得
            // Config テーブルにデータがあれば、配列を上書きする。
            // 初期状態ではConfig テーブルはなく、cc_role.CC_HTMLPurifier_ROLE_LIST を初期値とするため。
            $config_purifiers = $this->configs->where('category', 'html_purifier');
            foreach ($config_purifiers as $config_purifier) {
                if (array_key_exists($config_purifier->name, $purifiers)) {
                    $purifiers[$config_purifier->name] = $config_purifier->value;
                }
            }

            // ユーザが持つコンテンツ権限をループして、対応する権限のHTML制限を確認する。
            foreach ($user->user_roles['base'] as $base_role_name => $base_role_value) {
                if (!empty($purifiers) && $purifiers[$base_role_name] === '0') {
                    // ひとつでも制限なしの権限を持っている場合は、HTML制限は「なし」
                    return false;
                }
            }
        }

        return $html_purifier;
    }

    /**
     * プラグイン名の取得
     */
    protected function getPluginName()
    {
        if (empty($this->frame)) {
            return "";
        }
        return $this->frame->plugin_name;
    }

    /**
     * フレーム設定をフレームIDで絞り込んで設定する。
     */
    protected function setFrameConfigs()
    {
        // frame_idが設定されない場合があるので、なかったら設定しない。
        if (empty($this->frame->id)) {
            return;
        }

        $this->frame_configs = Request::get('frame_configs')->where('frame_id', $this->frame->id);
    }

    /**
     * フレーム設定を再取得し、フレームIDで絞り込んで設定しなおす。
     */
    protected function refreshFrameConfigs()
    {
        $this->frame_configs = FrameConfig::where('frame_id', $this->frame->id)->get();
    }

    /**
     * 要承認の判断
     */
    protected function isApproval()
    {
        if (empty($this->buckets)) {
            return false;
        }
        return $this->buckets->needApprovalUser(Auth::user(), $this->frame);
    }

    /**
     * 権限によって表示する記事を絞る
     *
     * 基本：
     *   - 承認機能を実装するには指定したテーブル($table_name)に status, created_id カラムがある事
     * オプション：
     *   - テーブルに posted_at(投稿日時) カラムがある場合、投稿日時前＋権限なし or 未ログインなら表示しない
     *
     * status = 0:公開(Active)
     * status = 1:Temporary（一時保存）
     * status = 2:Approval pending（承認待ち）
     * status = 9:History（履歴・データ削除）
     * 参考) https://github.com/opensource-workshop/connect-cms/wiki/Data-history-policy（データ履歴の方針）
     *
     * コンテンツ管理者(role_article_admin): 無条件に全記事見れる
     * モデレータ(role_article):            無条件に全記事見れる
     * 承認者(role_approval):               0:公開(Active) or 2:承認待ち の全記事見れる
     * 編集者(role_reporter):               0:公開(Active) or 自分の作成した記事 見れる
     * 権限なし（コンテンツ管理者・モデレータ・承認者・編集者以外）or 未ログイン:    0:公開(Active) 記事見れる
     *
     * [オプション：posted_at(投稿日時) カラムがある]
     * 権限なし（コンテンツ管理者・モデレータ・承認者・編集者以外）or 未ログイン:    0:公開(Active) and 投稿日時前 記事見れる
     *
     * 例) $table_name = 'blogs_posts';
     * 例) $table_name = 'databases_inputs';
     */
    protected function appendAuthWhereBase($query, $table_name)
    {
        // 各条件でSQL を or 追記する場合は、クロージャで記載することで、元のSQL とAND 条件でつながる。
        // クロージャなしで追記した場合、or は元の whereNull('calendar_posts.parent_id') を打ち消したりするので注意。

        if (empty($query)) {
            // 空なら何もしない
            return $query;
        }

        // モデレータ(記事修正, role_article)権限
        // コンテンツ管理者(role_article_admin)   = 全記事の取得
        if ($this->isCan('role_article') || $this->isCan('role_article_admin')) {
            // 全件取得のため、追加条件なしで戻る。
            return $query;
        }

        if ($this->isCan('role_approval')) {
            //
            // 承認者(role_approval)権限 = Active ＋ 承認待ちの取得
            //
            $query->WhereIn($table_name . '.status', [StatusType::active, StatusType::approval_pending]);

        } elseif ($this->isCan('role_reporter')) {
            //
            // 編集者(role_reporter)権限 = Active ＋ 自分の全ステータス記事の取得
            //
            $query->where(function ($tmp_query) use ($table_name) {
                $tmp_query->where($table_name . '.status', StatusType::active)
                        ->orWhere($table_name . '.created_id', Auth::user()->id);
            });
        } else {
            //
            // 共通条件（Active）
            // 権限なし（コンテンツ管理者・モデレータ・承認者・編集者以外）
            // 未ログイン
            //
            $query->where($table_name . '.status', StatusType::active);

            // DBカラム posted_at(投稿日時) 存在するか
            if (Schema::hasColumn($table_name, 'posted_at')) {
                $query->where($table_name . '.posted_at', '<=', Carbon::now());
            }

            // DBカラム expires_at(終了日時) 存在するか
            if (Schema::hasColumn($table_name, 'expires_at')) {
                $query->where(function ($query) use ($table_name) {
                    $query->whereNull($table_name . '.expires_at')
                        ->orWhere($table_name . '.expires_at', '>', Carbon::now());
                });
            }
        }

        return $query;
    }
}
