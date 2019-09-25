<?php

namespace App\Plugins\User;

use Illuminate\Support\Facades\Log;

use DB;

use App\Plugins\PluginBase;
use App\Frame;

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
     *  画面表示用にページやフレームなど呼び出し
     *
     * @param String $plugin_name
     * @return view
     */
    public function invoke($obj, $request, $action, $page_id, $frame_id, $id)
    {
        // アクションを保持しておく
        $this->action = $action;

        // 画面(コアの cms_frame)で指定されたクラスのアクションのメソッドを呼び出す。
        // 戻り値は各アクションでのメソッドでview 関数などで生成したHTML なので、そのままreturn して元の画面に戻す。
        return $obj->$action($request, $page_id, $frame_id, $id);
    }

    /**
     *  View のパス
     *
     * @return view
     */
    public function getViewPath($blade_name)
    {
        return 'plugins.user.' . $this->frame->plugin_name . '.' . $this->frame->template . '.' . $blade_name;
    }

    /**
     *  View のパス
     *
     * @return view
     */
    public function getCommonViewPath($blade_name)
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
    public function addArg($arg)
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
    public function datalist($request, $page_id, $frame_id, $id = null)
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
     * エラー画面の表示
     *
     */
    public function view_error($error_code)
    {
        // 表示テンプレートを呼び出す。
        return view('errors.' . $error_code);
    }

}
