<?php

namespace App\Plugins\Manage\PageManage;

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
class PageManage extends ManagePluginBase
{
    /**
     *  権限定義
     */
    public function declareRole()
    {
        // 権限チェックテーブル
        $role_ckeck_table = array();
        $role_ckeck_table["index"]         = array('admin_page');
        $role_ckeck_table["edit"]          = array('admin_page');
        $role_ckeck_table["store"]         = array('admin_page');
        $role_ckeck_table["update"]        = array('admin_page');
        $role_ckeck_table["destroy"]       = array('admin_page');
        $role_ckeck_table["sequence_up"]   = array('admin_page');
        $role_ckeck_table["sequence_down"] = array('admin_page');
        $role_ckeck_table["move_page"]     = array('admin_page');
/*
        $role_ckeck_table = array();
        $role_ckeck_table["index"]         = array(config('cc_role.ROLE_SYSTEM_MANAGER'), config('cc_role.ROLE_PAGE_MANAGER'));
        $role_ckeck_table["edit"]          = array(config('cc_role.ROLE_SYSTEM_MANAGER'), config('cc_role.ROLE_PAGE_MANAGER'));
        $role_ckeck_table["store"]         = array(config('cc_role.ROLE_SYSTEM_MANAGER'), config('cc_role.ROLE_PAGE_MANAGER'));
        $role_ckeck_table["update"]        = array(config('cc_role.ROLE_SYSTEM_MANAGER'), config('cc_role.ROLE_PAGE_MANAGER'));
        $role_ckeck_table["destroy"]       = array(config('cc_role.ROLE_SYSTEM_MANAGER'), config('cc_role.ROLE_PAGE_MANAGER'));
        $role_ckeck_table["sequence_up"]   = array(config('cc_role.ROLE_SYSTEM_MANAGER'), config('cc_role.ROLE_PAGE_MANAGER'));
        $role_ckeck_table["sequence_down"] = array(config('cc_role.ROLE_SYSTEM_MANAGER'), config('cc_role.ROLE_PAGE_MANAGER'));
        $role_ckeck_table["move_page"]     = array(config('cc_role.ROLE_SYSTEM_MANAGER'), config('cc_role.ROLE_PAGE_MANAGER'));
*/
        return $role_ckeck_table;
    }

    /**
     *  ページ初期表示
     *
     * @return view
     */
	public function index($request, $page_id = null, $errors = array())
	{
        // ページデータの取得(laravel-nestedset 使用)
        $return_obj = 'flat';
        $pages = Page::defaultOrderWithDepth($return_obj);

        // 移動先用にコピー
        $pages_select = $pages;

        // 管理画面プラグインの戻り値の返し方
        // view 関数の第一引数に画面ファイルのパス、第二引数に画面に渡したいデータを名前付き配列で渡し、その結果のHTML。
        return view('plugins.manage.page.page',[
            "plugin_name"  => "page",
            "page"         => new Page(),
            "pages"        => $pages,
            "pages_select" => $pages_select,
            "errors"       => $errors,
        ]);

    }

    /**
     *  ページ編集画面表示
     *
     * @return view
     */
    public function edit($request, $page_id)
    {
        // ページID で1件取得
        $page = Page::where('id', $page_id)->first();

        // ページデータの取得(laravel-nestedset 使用)
        $pages = Page::defaultOrderWithDepth();

        // 画面呼び出し
        return view('plugins.manage.page.page_edit',[
            "plugin_name" => "page",
            "page"        => $page,
            "pages"       => $pages,
        ]);
    }

    /**
     *  ページ登録処理
     */
    public function store($request)
    {
        // 項目のエラーチェック
        $validator = Validator::make($request->all(), [
            'page_name' => ['required'],
        ]);
        $validator->setAttributeNames([
            'page_name' => 'ページ名',
        ]);

        // エラーがあった場合は入力画面に戻る。
        if ($validator->fails()) {
            return ( $this->index($request, null, $validator->errors()) );
        }

        // 固定リンクの先頭に / がない場合、追加する。
        if (strncmp($request->permanent_link, '/', 1) !== 0) {
            $request->permanent_link = '/' . $request->permanent_link;
        }

        // ページデータの登録
        $page = new Page;
        $page->page_name         = $request->page_name;
        $page->permanent_link    = $request->permanent_link;
        $page->background_color  = $request->background_color;
        $page->header_color      = $request->header_color;
        $page->theme             = $request->theme;
        $page->layout            = $request->layout;
        $page->base_display_flag = (isset($request->base_display_flag) ? $request->base_display_flag : 0);
        $page->save();

        // ページ管理画面に戻る
        return redirect("/manage/page");
    }

    /**
     *  ページ更新処理
     */
    public function update($request, $page_id)
    {

        // 固定リンクの先頭に / がない場合、追加する。
        if (strncmp($request->permanent_link, '/', 1) !== 0) {
            $request->permanent_link = '/' . $request->permanent_link;
        }

        // ページデータの更新
        Page::where('id', $page_id)
            ->update([
                'page_name'         => $request->page_name,
                'permanent_link'    => $request->permanent_link,
                'background_color'  => $request->background_color,
                'header_color'      => $request->header_color,
                'theme'             => $request->theme,
                'layout'            => $request->layout,
                'base_display_flag' => (isset($request->base_display_flag) ? $request->base_display_flag : 0),
        ]);

        // ページ管理画面に戻る
        return redirect("/manage/page");
    }

    /**
     *  ページ削除関数
     */
    public function destroy($request, $page_id)
    {
        // Log::debug($id);
        DB::table('pages')->where('id', '=', $page_id)->delete();

        // ページ管理画面に戻る
        return redirect("/manage/page");
    }

    /**
     *  ページ上移動
     */
    public function sequence_up($request, $page_id)
    {
        // 移動元のオブジェクトを取得して、up
        $pages = Page::find($page_id);
        $pages->up();

        // ページ管理画面に戻る
        return redirect("/manage/page");
    }

    /**
     *  ページ下移動
     */
    public function sequence_down($request, $page_id)
    {
        // 移動元のオブジェクトを取得して、down
        $pages = Page::find($page_id);
        $pages->down();

        // ページ管理画面に戻る
        return redirect("/manage/page");
    }

    /**
     *  ページ指定場所移動
     */
    public function move_page($request, $page_id)
    {
        // ルートへ移動
        if ($request->destination_id == "0") {

            // 移動元のオブジェクトを取得
            $page = Page::find($page_id);
            $page->saveAsRoot();
        }
        else {
            // その他の場所へ移動

            // 移動元のオブジェクトを取得
            $source_page = Page::find($page_id);

            // 移動先のオブジェクトを取得
            $destination_page = Page::find($request->destination_id);

            // 移動
            $source_page->appendToNode($destination_page)->save();
        }

        // ページ管理画面に戻る
        return redirect("/manage/page");
    }
}
