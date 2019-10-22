<?php

namespace App\Plugins\Manage\PluginaddManage;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;

use DB;

use App\Models\Common\Page;

use App\Plugins\Manage\ManagePluginBase;

/**
 * ページ管理クラス
 *
 * @author 永原　篤 <nagahara@opensource-workshop.jp>
 * @copyright OpenSource-WorkShop Co.,Ltd. All Rights Reserved
 * @category ページ管理
 * @package Contoroller
 */
class PluginaddManage extends ManagePluginBase
{
    /**
     *  ページ初期表示
     *
     * @return view
     */
	public function index($request, $page_id = null, $errors = array())
	{

        // ページの取得
        $page = null;
        if (!empty($page_id)) {
            $page = Page::where('id', $page_id)->first();
        }

        // ページデータの取得(laravel-nestedset 使用)
        $pages = Page::defaultOrderWithDepth();

        // 管理画面プラグインの戻り値の返し方
        // view 関数の第一引数に画面ファイルのパス、第二引数に画面に渡したいデータを名前付き配列で渡し、その結果のHTML。
        return view('plugins.manage.pluginadd.pluginadd',[
            "page_id"      => $page_id,
            "page"         => $page,
            "pages"        => $pages,
            "errors"       => $errors,
        ]);
    }
}
