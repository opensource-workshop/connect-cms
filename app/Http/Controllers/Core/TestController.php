<?php

namespace App\Http\Controllers\Core;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\View;

use DB;

use App\Http\Controllers\Core\ConnectController;

use App\Models\Common\Frame;
use App\Models\Common\Page;

/**
 * テスト用コントローラ
 *
 * ルーティング処理から呼び出されるもの
 *
 * @author 永原　篤 <nagahara@opensource-workshop.jp>
 * @copyright OpenSource-WorkShop Co.,Ltd. All Rights Reserved
 * @category コア
 * @package Controller
 */
class TestController extends ConnectController
{
    /**
     *  画面表示用にページやフレームなど呼び出し
     *
     * @param String $plugin_name
     * @return view
     */
    public function invokeGet(Request $request, $language = null, $id = null)
    {
        print_r($language);
        echo "<br />";
        print_r($id);
        exit;

        return $this->view('test.ajaxtest', [
            'id'     => $id,
        ]);
    }

    /**
     *  画面表示用にページやフレームなど呼び出し
     *
     * @param String $plugin_name
     * @return view
     */
    public function invokePost(Request $request, $id = null)
    {
        return $this->view('test.ajaxtest', [
            'id'     => $id,
        ]);
    }
}
