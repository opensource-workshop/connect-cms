<?php

namespace App\Http\Controllers\Core;

use App\Http\Controllers\Core\ConnectController;
use App\Traits\ConnectCommonTrait;
use Illuminate\Http\Request;    // 依存注入のための指定

/**
 * マイページを呼び出す振り分けコントローラ
 */
class MypageController extends ConnectController
{
    use ConnectCommonTrait;

    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->middleware('connect.mypage');
        // onlyで指定されたメソッドのみ適用
        $this->middleware('connect.themes')->only('invokeGetMypage');
    }

    /**
     * 管理プラグインの呼び出し
     *
     * @param String $plugin_name
     * @return プラグインからの戻り値(HTMLなど)
     */
    public function invokeGetMypage(Request $request, $plugin_name, $action = 'index', $id = null, $sub_id = null)
    {
        return $this->invokeMypage($request, $plugin_name, $action, $id, $sub_id);
    }

    /**
     * 管理プラグインの呼び出し
     *
     * @param String $plugin_name
     * @return プラグインからの戻り値(HTMLなど)
     */
    public function invokePostMypage(Request $request, $plugin_name, $action = 'index', $id = null)
    {
        return $this->invokeMypage($request, $plugin_name, $action, $id);
    }
}
