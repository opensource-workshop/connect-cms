<?php

namespace App\Http\Controllers\Core;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\View;

use App\Http\Controllers\Core\ConnectController;

use App\Models\Common\Page;

// use App\Traits\ConnectCommonTrait;

/**
 * Cookie管理
 *
 * ルーティング処理から呼び出されるもの
 *
 * @author 井上 雅人 <inoue@opensource-workshop.jp / masamasamasato0216@gmail.com>
 * @copyright OpenSource-WorkShop Co.,Ltd. All Rights Reserved
 * @category コア
 * @package Contoroller
 */
class CookieController extends ConnectController
{
    // use ConnectCommonTrait;

    /**
     *  コンストラクタ
     */
    public function __construct($page_id, $frame_id)
    {
        // ルートパラメータを取得する
    }

    /**
     *  パスワードチェック処理
     */
    public function setCookieForMessageFirst($request, $page_id)
    {
        // Page データ
        $page = Page::where('id', $page_id)->first();

        // cookieをセット（cookie名、値、有効期間（分））して、元ページへリダイレクト
        return redirect($page->permanent_link)->cookie('connect_cookie_message_first', 'agreed', 525600);
    }
}
