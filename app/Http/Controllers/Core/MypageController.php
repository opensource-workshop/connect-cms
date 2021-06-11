<?php

namespace App\Http\Controllers\Core;

use Illuminate\Http\Request;    // 依存注入のための指定

use App\Http\Controllers\Core\ConnectController;

use App\Traits\ConnectCommonTrait;
use App\Models\Core\Configs;

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
        // マイページの使用
        $use_mypage = Configs::where('name', 'use_mypage')->first();

        if (empty($use_mypage) || $use_mypage->value == '0') {
            abort(403, "マイページを使用しないため、表示できません。");
        }
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
