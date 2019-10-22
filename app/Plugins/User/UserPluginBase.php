<?php

namespace App\Plugins\User;

use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

use DB;

use App\Models\Common\Frame;
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
     *  フレームオブジェクト
     */
    public $frame = null;

    /**
     *  アクション
     */
    public $action = null;

    /**
     *  コンストラクタ
     */
    function __construct($page = null, $frame = null, $plugin_name = null)
    {
        // ページの保持
        $this->page = $page;

        // フレームの保持
        $this->frame = $frame;
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
    public function invoke($obj, $request, $action, $page_id, $frame_id, $id)
    {
        // アクションを保持しておく
        $this->action = $action;

        // メソッドの可視性チェック
        $objReflectionMethod = new \ReflectionMethod(get_class($obj), $action);
        if (!$objReflectionMethod->isPublic()) {
            return $this->view_error("403_inframe");
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
            return $this->view_error("403_inframe");
        }

        // チェック用POST
        $post = null;

        // POST チェックに使用する getPost() 関数の有無をチェック
        if ( $id && method_exists($obj, 'getPost') ) { 
            $post = $obj->getPost($id);
        }

        // 定数 CC_METHOD_AUTHORITY に設定があるものはここでチェックする。
        if (array_key_exists($this->action, config('cc_role.CC_METHOD_AUTHORITY'))) {

            // 記載されているメソッドすべての権限を有すること。
            foreach (config('cc_role.CC_METHOD_AUTHORITY')[$this->action] as $function_authority) {

                // 権限チェックの結果、エラーがあればエラー表示用HTML が返ってくる。
                $ret = null;

                // POST があれば、POST の登録者チェックを行う
                if (empty($post)) {
                    $ret = $this->can($function_authority);
                }
                else {
                    $ret = $this->can($function_authority, $post);
                }

                // 権限チェック結果。値があれば、エラーメッセージ用HTML
                if (!empty($ret)) {
                    return $ret;
                }
            }
        }

        // 画面(コアの cms_frame)で指定されたクラスのアクションのメソッドを呼び出す。
        // 戻り値は各アクションでのメソッドでview 関数などで生成したHTML なので、そのままreturn して元の画面に戻す。
        return $obj->$action($request, $page_id, $frame_id, $id);
    }

    /**
     *  View のパス
     *
     * @return view
     */
    protected function getViewPath($blade_name)
    {
        return 'plugins.user.' . $this->frame->plugin_name . '.' . $this->frame->template . '.' . $blade_name;
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
        ]);
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
