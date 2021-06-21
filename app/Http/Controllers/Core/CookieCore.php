<?php

namespace App\Http\Controllers\Core;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\View;

// use App\Http\Controllers\Core\ConnectController;

use App\Models\Common\Page;

// use App\Traits\ConnectCommonTrait;

/**
 * Cookie管理
 *
 * ClassControllerから呼び出されるもの
 *
 * @author 井上 雅人 <inoue@opensource-workshop.jp / masamasamasato0216@gmail.com>
 * @copyright OpenSource-WorkShop Co.,Ltd. All Rights Reserved
 * @category コア
 * @package Contoroller
 */
// class CookieController extends ConnectController
class CookieCore
{
    // use ConnectCommonTrait;

    /**
     * コンストラクタ
     *
     * /core/cookie/xxx 系アクション時に実行される.
     * ClassController::createCoreInstance() の new $class_name($page_id, $frame_id) で newされる。
     * 下記順で呼び出される。
     *   1. ConnectController::__construct （ClassControllerの親クラス）
     *   2-1. ClassController::invokeGetCore
     *   2-2. ClassController::createCoreInstance
     *   3. CookieController::__construct （当コンストラクタ）
     *   4. CookieController::xxx($request, $page_id, $frame_id); （実行アクション）
     *
     * ClassController の親クラスで ConnectController::__construct が既に実行済みであり、
     * 当クラスはClassControllerから new されて呼ばれる Controller のため、通常のControllerと違い、親クラスに ConnectController の指定は不要。
     *
     * @see \App\Http\Controllers\Core\ClassController
     */
    public function __construct($page_id, $frame_id)
    {
        // ルートパラメータを取得する
    }

    /**
     * パスワードチェック処理
     */
    public function setCookieForMessageFirst($request, $page_id)
    {
        // Page データ
        $page = Page::where('id', $page_id)->first();

        // cookieをセット（cookie名、値、有効期間（分））して、元ページへリダイレクト
        return redirect($page->permanent_link)->cookie('connect_cookie_message_first', 'agreed', 525600);
    }
}
