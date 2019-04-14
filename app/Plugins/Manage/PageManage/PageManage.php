<?php

namespace App\Plugins\Manage\PageManage;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;

use DB;

use App\Page;
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
     *  ページ初期表示
     *
     * @return view
     */
	public function index($request, $page_id = null, $errors = array())
	{
        // ページデータの取得(laravel-nestedset 使用)
        $pages = Page::defaultOrderWithDepth();

        // 移動先用にコピー
        $pages_select = $pages;

        // 管理画面プラグインの戻り値の返し方
        // view 関数の第一引数に画面ファイルのパス、第二引数に画面に渡したいデータを名前付き配列で渡し、その結果のHTML。
        return view('plugins.manage.page.page',[
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

        // 画面呼び出し
        return view('plugins.manage.page.page_edit',[
            "page" => $page
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

        // ページデータの登録
        $page = new Page;
        $page->page_name        = $request->page_name;
        $page->permanent_link   = $request->permanent_link;
        $page->save();

        // ページ管理画面に戻る
        return redirect("/manage/page");
    }

    /**
     *  ページ更新処理
     */
    public function update($request, $page_id)
    {
        // ページデータの更新
        Page::where('id', $page_id)
            ->update([
                'page_name'        => $request->page_name,
                'permanent_link'   => $request->permanent_link
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
