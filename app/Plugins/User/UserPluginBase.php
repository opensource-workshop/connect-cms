<?php

namespace App\Plugins\User;

use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;

use Monolog\Logger;
use Monolog\Formatter\LineFormatter;
use Monolog\Handler\StreamHandler;
use Monolog\Handler\RotatingFileHandler;

use DB;
use File;
use HTMLPurifier;
use HTMLPurifier_Config;
use Request;

use App\Jobs\ApprovalNoticeJob;
use App\Jobs\ApprovedNoticeJob;
use App\Jobs\DeleteNoticeJob;
use App\Jobs\PostNoticeJob;
use App\Jobs\RelateNoticeJob;

use App\Models\Common\Buckets;
use App\Models\Common\BucketsMail;
use App\Models\Common\BucketsRoles;
use App\Models\Common\Frame;
use App\Models\Core\Configs;
use App\Models\Core\FrameConfig;

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
     * FrameConfig オブジェクト
     * FrameConfigのCollection
     *
     */
    public $frame_configs = null;

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
     *  purifier
     *  保存処理時にインスタンスが代入され、シングルトンで使うことを想定
     */
    public $purifier = null;

    /**
     *  画面間用メッセージ
     */
    public $cc_massage = null;

    /**
     *  コンストラクタ
     */
    public function __construct($page = null, $frame = null, $pages = null)
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
        $this->configs = Configs::get();

        $this->setFrameConfigs();
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
     *  Buckets のメール設定取得
     */
    protected function getBucketMail($backet)
    {
        if (empty($backet)) {
            return new BucketsMail();
        }
        return BucketsMail::firstOrNew(['buckets_id' => $backet->id]);
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
     * メール送信設定 変更画面
     */
    public function editBucketsMails($request, $page_id, $frame_id, $id = null)
    {
        // Buckets の取得
        $bucket = $this->getBuckets($frame_id);

        // Backet が取れない場合はエラー。
        if (empty($bucket)) {
            return $this->view_error("error_inframe", "存在しないBucket");
        }
        // [debug]
        // var_dump(old('notice_on'));

        // Buckets のメール設定取得
        $bucket_mail = $this->getBucketMail($bucket);

        // 使用するメール送信メソッドが指定されている場合は、そのメソッドの指定のみできるようにする。
        if (method_exists($this, 'useBucketMailMethods')) {
            // メール送信メソッドの取得
            $use_bucket_mail_methods = $this->useBucketMailMethods();
        } else {
            // メール送信メソッドの初期値（全て：投稿通知、関連通知、承認待ち、承認済み）
            $use_bucket_mail_methods = ['notice', 'relate', 'approval', 'approved'];
        }

        return $this->commonView('frame_edit_mails', [
            'bucket'       => $bucket,
            'bucket_mail'  => $bucket_mail,
            'plugin_name'  => $this->frame->plugin_name,
            'use_bucket_mail_methods' => $use_bucket_mail_methods,
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

        // Backet が取れないとおかしな操作をした可能性があるのでエラーにしておく。
        if (empty($buckets)) {
            return $this->view_error("error_inframe", "存在しないBucket");
        }

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
     * 権限設定 保存処理
     */
    public function saveBucketsMails($request, $page_id, $frame_id, $block_id)
    {
        // Buckets の取得
        $bucket = $this->getBuckets($frame_id);

        // buckets がない場合
        if (empty($bucket)) {
            return $this->view_error("error_inframe", "存在しないBucket");
        }

        // redirect_path
        $redirect_path_array = [
            'redirect_path' => url('/') . "/plugin/" . $this->frame->plugin_name . "/editBucketsMails/" . $page_id . "/" . $frame_id . "/" . $bucket->id . "#frame-" . $frame_id
        ];

        // 項目のエラーチェック
        $validator = Validator::make($request->all(), [
            'notice_addresses' => ['nullable', 'email', Rule::requiredIf($request->notice_on)],
            'approval_addresses' => ['nullable', 'email', Rule::requiredIf($request->approval_on)],
            'approved_addresses' => ['nullable', 'email', Rule::requiredIf($request->approved_on)],

        ]);
        $validator->setAttributeNames([
            'notice_addresses' => '送信先メールアドレス',
            'approval_addresses' => '送信先メールアドレス',
            'approved_addresses' => '送信先メールアドレス',
        ]);

        // エラーがあった場合は入力画面に戻る。
        if ($validator->fails()) {
            // [debug]
            // セッション初期化などのLaravel 処理。
            // $request->flash();

            // 共通画面のため、redirect_pathが画面に書けないため、ここで設定
            $request->merge($redirect_path_array);

            // [debug]
            // dd(old('notice_on'), $request->notice_on);
            // $a = redirect()->back()->withErrors($validator)->withInput();
            // dd(old('notice_on'), $request->notice_on);
            // return $a;

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
        $bucket_mail->notice_groups      = $request->notice_groups;
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
        $bucket_mail->approval_subject   = $request->approval_subject;
        $bucket_mail->approval_body      = $request->approval_body;

        // 承認済み通知
        $bucket_mail->approved_on        = $this->inputNullToZero($request, "approved_on");
        $bucket_mail->approved_author    = $this->inputNullToZero($request, "approved_author");
        $bucket_mail->approved_addresses = $request->approved_addresses;
        $bucket_mail->approved_subject   = $request->approved_subject;
        $bucket_mail->approved_body      = $request->approved_body;

        // BucketsMails の更新
        $bucket_mail->save();

        // 登録後はリダイレクトしてメール設定ページを開く。
        return new Collection($redirect_path_array);
    }

    /**
     * 投稿通知の送信
     */
    public function sendPostNotice($post_row, $before_row, $show_method)
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
            return $this->view_error("error_inframe", "存在しないBucket");
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
            $notice_methods[] = "notice_approval";
        }

        // 承認済み通知がon の場合
        // before_row（更新前）の（status === 2）でpost_row の status が公開（=== 0）の場合に送信（notice_approved）
        if ($bucket_mail->approved_on === 1 && $before_row->status === 2 && $post_row->status === 0) {
            $notice_methods[] = "notice_approved";
        }

        // 投稿通知がon の場合
        // before_row（更新前）のfirst_committed_at が空でstatus が公開（=== 0）は「登録」（notice_create）
        // before_row（更新前）のfirst_committed_at が空ではなく、status が公開（=== 0）は「更新」（notice_update）
        if ($bucket_mail->notice_on === 1) {
            if ($bucket_mail->notice_create === 1 && empty($before_row->first_committed_at) && $post_row->status === 0) {
                // 対象（登録）
                $notice_methods[] = "notice_create";
            } elseif ($bucket_mail->notice_update === 1 && !empty($before_row->first_committed_at) && $post_row->status === 0) {
                // 対象（変更）
                $notice_methods[] = "notice_update";
            }
        }

        // 送信対象の命令がなかった場合は何もせずに戻る。
        if (empty($notice_methods)) {
            return;
        }

        // --- メール送信の処理を呼ぶ

        // 承認通知
        if (in_array("notice_approval", $notice_methods, true)) {
            // 送信方法の確認
            if ($bucket_mail->timing == 0) {
                // 即時送信
                dispatch_now(new ApprovalNoticeJob($this->frame, $this->buckets, $post_row, $show_method));
            } else {
                // スケジュール送信
                ApprovalNoticeJob::dispatch($this->frame, $this->buckets, $post_row, $show_method);
            }
        }

        // 承認済み通知
        if (in_array("notice_approved", $notice_methods, true)) {
            // 送信方法の確認
            if ($bucket_mail->timing == 0) {
                // 即時送信
                dispatch_now(new ApprovedNoticeJob($this->frame, $this->buckets, $post_row, $post_row->created_id, $show_method));
            } else {
                // スケジュール送信
                ApprovedNoticeJob::dispatch($this->frame, $this->buckets, $post_row, $post_row->created_id, $show_method);
            }
        }

        // 投稿通知（登録）
        if (in_array("notice_create", $notice_methods, true)) {
            // 送信方法の確認
            if ($bucket_mail->timing == 0) {
                // 即時送信
                dispatch_now(new PostNoticeJob($this->frame, $this->buckets, $post_row, $show_method, "notice_create"));
            } else {
                // スケジュール送信
                PostNoticeJob::dispatch($this->frame, $this->buckets, $post_row, $show_method, "notice_create");
            }
        }

        // 投稿通知（変更）
        if (in_array("notice_update", $notice_methods, true)) {
            // 送信方法の確認
            if ($bucket_mail->timing == 0) {
                // 即時送信
                dispatch_now(new PostNoticeJob($this->frame, $this->buckets, $post_row, $show_method, "notice_update"));
            } else {
                // スケジュール送信
                PostNoticeJob::dispatch($this->frame, $this->buckets, $post_row, $show_method, "notice_update");
            }
        }
    }

    /**
     * 関連投稿通知の送信
     */
    public function sendRelateNotice($post, $mail_users, $show_method)
    {
        // buckets がない場合
        if (empty($this->buckets)) {
            return $this->view_error("error_inframe", "存在しないBucket");
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

        // 送信方法の確認
        if ($bucket_mail->timing == 0) {
            // 即時送信
            dispatch_now(new RelateNoticeJob($this->frame, $this->buckets, $post, $show_method, $mail_users));
        } else {
            // スケジュール送信
            RelateNoticeJob::dispatch($this->frame, $this->buckets, $post, $show_method, $mail_users);
        }
    }

    /**
     * 削除通知の送信
     */
    public function sendDeleteNotice($post, $show_method, $delete_comment)
    {
        // buckets がない場合
        if (empty($this->buckets)) {
            return $this->view_error("error_inframe", "存在しないBucket");
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
        if ($bucket_mail->timing == 0) {
            // 即時送信
            dispatch_now(new DeleteNoticeJob($this->frame, $this->buckets, $post, $show_method, $delete_comment));
        } else {
            // スケジュール送信
            DeleteNoticeJob::dispatch($this->frame, $this->buckets, $post, $show_method, $delete_comment);
        }
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
     *  HTMLPurifier の実行
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

                $config->set('Attr.AllowedClasses', array()); // class指定を許可する
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
     *  HTMLPurifier 制限の判定
     *
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
            // セキュリティ管理のHTML制限を取得
            $config_html_purifiers = $this->configs->where('category', 'html_purifier');

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
     *  プラグイン名の取得
     *
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
