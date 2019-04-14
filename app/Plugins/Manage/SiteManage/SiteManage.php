<?php

namespace App\Plugins\Manage\SiteManage;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;

use DB;

use App\Configs;
use App\Page;
use App\Plugins\Manage\ManagePluginBase;

/**
 * サイト管理クラス
 *
 * @author 永原　篤 <nagahara@opensource-workshop.jp>
 * @copyright OpenSource-WorkShop Co.,Ltd. All Rights Reserved
 * @category ページ管理
 * @package Contoroller
 */
class SiteManage extends ManagePluginBase
{
    /**
     *  ページ初期表示
     *
     * @return view
     */
    public function index($request, $page_id = null, $errors = array())
    {
        // Config データの取得
        $configs = Configs::get();

        // Config データの変換
        $configs_array = array();
        foreach ( $configs as $config ) {
            $configs_array[$config->name] = $config->value;
        }

        // 管理画面プラグインの戻り値の返し方
        // view 関数の第一引数に画面ファイルのパス、第二引数に画面に渡したいデータを名前付き配列で渡し、その結果のHTML。
        return view('plugins.manage.site.site',[
            "errors"  => $errors,
            "configs" => $configs_array,
        ]);
    }

    /**
     *  更新
     */
    public function update($request, $page_id = null, $errors = array())
    {
        // 画面の基本の背景色
        $configs = Configs::updateOrCreate(
            ['name' => 'base_background_color'],
            ['value' => $request->base_background_color]
        );

        return $this->index($request, $page_id, $errors);
    }
}
