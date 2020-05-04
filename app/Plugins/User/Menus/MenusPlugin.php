<?php

namespace App\Plugins\User\Menus;

use Illuminate\Support\Facades\Log;

use App\Models\Common\Frame;
use App\Models\Common\Page;
use App\Models\User\Menus\Menu;

use App\Plugins\User\UserPluginBase;

/**
 * メニュープラグイン
 *
 * ページデータの表示を行う。
 * ページデータは入れ子集合モデルで表されている。
 * 入れ子集合モデルの処理には lazychaser/laravel-nestedset を使用
 * (https://github.com/lazychaser/laravel-nestedset)
 *
 * @author 永原　篤 <nagahara@opensource-workshop.jp>
 * @copyright OpenSource-WorkShop Co.,Ltd. All Rights Reserved
 * @category ページプラグイン
 * @package Contoroller
 */
class MenusPlugin extends UserPluginBase
{

    /**
     *  編集画面の最初のタブ
     *
     *  スーパークラスをオーバーライド
     */
    public function getFirstFrameEditAction()
    {
        return "select";
    }

    /**
     *  関数定義（コアから呼び出す）
     */
    public function getPublicFunctions()
    {
        // 標準関数以外で画面などから呼ばれる関数の定義
        $functions = array();
        $functions['get']  = ['select'];
        $functions['post'] = ['saveSelect'];
        return $functions;
    }

    /**
     *  ページデータ取得関数
     *
     *  ページデータを取得し、深さを追加して画面に。
     *
     * @return view
     */
    public function index($request, $page_id, $frame_id)
    {
        // メニュー
        $menu = Menu::where('frame_id', $frame_id)->first();
        //Log::debug(json_encode( $menu, JSON_UNESCAPED_UNICODE));

        // ページに対する権限
        $page_roles = $this->getPageRoles();

        // ページデータ＆深さを全て取得
        // 表示順は入れ子集合モデルの順番
        $format = null;
        $pages = Page::defaultOrderWithDepth($format, $this->page, $menu);

        //Log::debug(json_encode( $multi_language_root_page, JSON_UNESCAPED_UNICODE));
        //Log::debug(json_encode( $pages, JSON_UNESCAPED_UNICODE));

        // パンくずリスト用に自分と上位階層のページを取得
        $ancestors = Page::ancestorsAndSelf($page_id);

        // パンくずリスト用ページに対する権限
        $ancestors_page_roles = $this->getPageRoles($ancestors->pluck('id'));

        // 画面へ
        return $this->view('menus', [
            'page_id'      => $page_id,
            'pages'        => $pages,
            'ancestors'    => $ancestors,
            'current_page' => $this->page,
            'menu'         => $menu,
            'page_roles'   => $page_roles,
            'ancestors_page_roles' => $ancestors_page_roles,
//            'page'      => $this->page,
        ]);
    }

    /**
     *  ページ選択画面
     */
    public function select($request, $page_id, $frame_id)
    {
        // 権限チェック
        // ページ選択プラグインの特別処理。個別に権限チェックする。
        if ($this->can('role_arrangement')) {
            return $this->view_error(403);
        }

        // ページデータ＆深さを全て取得
        // 表示順は入れ子集合モデルの順番
        $format = null;
        $menu = null;
        $setting_mode = true;
        $pages = Page::defaultOrderWithDepth($format, $this->page, $menu, $setting_mode);
        //Log::debug(json_encode( $pages, JSON_UNESCAPED_UNICODE));

        // メニュー
        $menu = Menu::where('frame_id', $frame_id)->first();

        // 画面へ
        return $this->view('menus_select', [
            'page_id'       => $page_id,
            'pages'         => $pages,
            'current_pages' => $this->page,
            'frame'         => $this->frame,
            'menu'          => $menu,
        ]);
    }

    /**
     *  ページ選択保存
     */
    public function saveSelect($request, $page_id, $frame_id)
    {
        // 権限チェック
        // ページ選択プラグインの特別処理。個別に権限チェックする。
        if ($this->can('role_arrangement')) {
            return $this->view_error(403);
        }

        // メニューデータ作成 or 更新
        Menu::updateOrCreate(
            ['frame_id'          => $frame_id],
            [
             'frame_id'          => $frame_id,
             'select_flag'       => $request->select_flag,
             'page_ids'          => (empty($request->page_select)) ? '' : implode(',', $request->page_select),
             'folder_close_font' => $request->folder_close_font,
             'folder_open_font'  => $request->folder_open_font,
             'indent_font'       => $request->indent_font,
            ]
        );

        // 画面へ
        return $this->select($request, $page_id, $frame_id);
    }
}
