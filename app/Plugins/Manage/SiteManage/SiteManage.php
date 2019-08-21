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
     *  権限定義
     */
    public function declareRole()
    {
        // 権限チェックテーブル
        $role_ckeck_table = array();
        $role_ckeck_table["index"]  = array(config('cc_role.ROLE_SYSTEM_MANAGER'), config('cc_role.ROLE_SITE_MANAGER'));
        $role_ckeck_table["update"] = array(config('cc_role.ROLE_SYSTEM_MANAGER'), config('cc_role.ROLE_SITE_MANAGER'));

        return $role_ckeck_table;
    }

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
            "plugin_name" => "site",
            "errors"      => $errors,
            "configs"     => $configs_array,
        ]);
    }

    /**
     *  更新
     */
    public function update($request, $page_id = null, $errors = array())
    {
        // サイト名
        $configs = Configs::updateOrCreate(
            ['name'     => 'base_site_name'],
            ['category' => 'general',
             'value'    => $request->base_site_name]
        );

        // 画面の基本のテーマ
        $configs = Configs::updateOrCreate(
            ['name'     => 'base_theme'],
            ['category' => 'general',
             'value'    => $request->base_theme]
        );

        // 画面の基本の背景色
        $configs = Configs::updateOrCreate(
            ['name'     => 'base_background_color'],
            ['category' => 'general',
             'value'    => $request->base_background_color]
        );

        // 画面の基本のヘッダー背景色
        $configs = Configs::updateOrCreate(
            ['name'     => 'base_header_color'],
            ['category' => 'general',
             'value'    => $request->base_header_color]
        );

        // 基本のヘッダー固定設定
        $configs = Configs::updateOrCreate(
            ['name'     => 'base_header_fix_xs'],
            ['category' => 'general',
             'value'    => (isset($request->base_header_fix_xs) ? $request->base_header_fix_xs : 0)]
        );
        $configs = Configs::updateOrCreate(
            ['name'     => 'base_header_fix_sm'],
            ['category' => 'general',
             'value'    => (isset($request->base_header_fix_sm) ? $request->base_header_fix_sm : 0)]
        );
        $configs = Configs::updateOrCreate(
            ['name'     => 'base_header_fix_md'],
            ['category' => 'general',
             'value'    => (isset($request->base_header_fix_md) ? $request->base_header_fix_md : 0)]
        );

        // ログインリンクの表示
        $configs = Configs::updateOrCreate(
            ['name'     => 'base_header_login_link'],
            ['category' => 'general',
             'value'    => $request->base_header_login_link]
        );

        // 自動ユーザ登録の使用
        $configs = Configs::updateOrCreate(
            ['name'     => 'user_register_enable'],
            ['category' => 'user_register',
             'value'    => $request->user_register_enable]
        );

        // 画像の保存機能の無効化
        $configs = Configs::updateOrCreate(
            ['name'     => 'base_mousedown_off'],
            ['category' => 'general',
             'value'    => (isset($request->base_mousedown_off) ? $request->base_mousedown_off : 0)]
        );
        $configs = Configs::updateOrCreate(
            ['name'     => 'base_contextmenu_off'],
            ['category' => 'general',
             'value'    => (isset($request->base_contextmenu_off) ? $request->base_contextmenu_off : 0)]
        );
        $configs = Configs::updateOrCreate(
            ['name'     => 'base_touch_callout'],
            ['category' => 'general',
             'value'    => (isset($request->base_touch_callout) ? $request->base_touch_callout : 0)]
        );

        return $this->index($request, $page_id, $errors);
    }
}
