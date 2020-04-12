<?php

namespace App\Plugins\Manage\PageManage;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
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
        $role_ckeck_table["import"]        = array('admin_page');
        $role_ckeck_table["upload"]        = array('admin_page');

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

        // テーマの取得
        $themes = $this->getThemes();

        // 管理画面プラグインの戻り値の返し方
        // view 関数の第一引数に画面ファイルのパス、第二引数に画面に渡したいデータを名前付き配列で渡し、その結果のHTML。
        return view('plugins.manage.page.page',[
            "function"     => __FUNCTION__,
            "plugin_name"  => "page",
            "page"         => new Page(),
            "pages"        => $pages,
            "pages_select" => $pages_select,
            "themes"       => $themes,
            "errors"       => $errors,
        ]);

    }

    /**
     *  ページ編集画面表示
     *
     * @return view
     */
    public function edit($request, $page_id = null)
    {

        // 編集時と新規で処理を分ける
        if (empty($page_id)) {
            $page = new Page();
        }
        else {
            // ページID で1件取得
            $page = Page::where('id', $page_id)->first();
        }

        // ページデータの取得(laravel-nestedset 使用)
        $pages = Page::defaultOrderWithDepth();

        // テーマの取得
        $themes = $this->getThemes();

        // 画面呼び出し
        return view('plugins.manage.page.page_edit',[
            "function"    => __FUNCTION__,
            "plugin_name" => "page",
            "page"        => $page,
            "pages"       => $pages,
            "themes"      => $themes,
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
        $page->page_name            = $request->page_name;
        $page->permanent_link       = $request->permanent_link;
        $page->background_color     = $request->background_color;
        $page->header_color         = $request->header_color;
        $page->theme                = $request->theme;
        $page->layout               = $request->layout;
        $page->base_display_flag    = (isset($request->base_display_flag) ? $request->base_display_flag : 0);
        $page->ip_address           = $request->ip_address;
        $page->othersite_url        = $request->othersite_url;
        $page->othersite_url_target = (isset($request->othersite_url_target) ? $request->othersite_url_target : 0);
        $page->class                = $request->class;
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
                'page_name'            => $request->page_name,
                'permanent_link'       => $request->permanent_link,
                'background_color'     => $request->background_color,
                'header_color'         => $request->header_color,
                'theme'                => $request->theme,
                'layout'               => $request->layout,
                'base_display_flag'    => (isset($request->base_display_flag) ? $request->base_display_flag : 0),
                'ip_address'           => $request->ip_address,
                'othersite_url'        => $request->othersite_url,
                'othersite_url_target' => (isset($request->othersite_url_target) ? $request->othersite_url_target : 0),
                'class'                => $request->class,
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

    /**
     *  ページインポート画面表示
     *
     * @return view
     */
    public function import($request, $page_id, $errors = null)
    {
        // 画面呼び出し
        return view('plugins.manage.page.page_import',[
            "function"     => __FUNCTION__,
            "plugin_name"  => "page",
            'errors'       => $errors,
        ]);
    }

    /**
     *  CSVヘッダーチェック
     */
    private function checkHeader($header_columns)
    {
        // ヘッダーカラム
        $header_column_format = array("page_name","permanent_link","background_color","header_color","theme","layout","base_display_flag");

        if (empty($header_columns)) {
            return array("CSVファイルが空です。");
        }

        // 項目の不足チェック
        $shortness = array_diff($header_column_format, $header_columns);
        if (!empty($shortness)) {
            return array(implode(",", $shortness) . " が不足しています。");
        }
        // 項目の不要チェック
        $excess = array_diff($header_columns, $header_column_format);
        if (!empty($excess)) {
            return array(implode(",", $excess) . " は不要です。");
        }

        return;
    }

    /**
     *  CSVデータ行チェック
     */
    private function checkPageline($fp, $errors = null)
    {
        $line_count = 1;

        while (($csv_columns = fgetcsv($fp, 0, ",")) !== FALSE) {
            foreach($csv_columns as $column_index => $csv_column) {

                switch ($column_index) {
                case 0:
                    if (empty($csv_column)) {
                        $errors[] = $line_count . "行目の page_name は必須です。";
                    }
                    break;
                case 1:
                    if (empty($csv_column)) {
                        $errors[] = $line_count . "行目の permanent_link は必須です。";
                    }
                    break;
                case 2:
                    if (empty($csv_column)) {
                        $errors[] = $line_count . "行目の background_color は必須です。";
                    }
                    break;
                case 3:
                    if (empty($csv_column)) {
                        $errors[] = $line_count . "行目の header_color は必須です。";
                    }
                    break;
                case 4:
                    if (empty($csv_column)) {
                        $errors[] = $line_count . "行目の header_color は必須です。";
                    }
                    break;
                case 5:
                    if (empty($csv_column)) {
                        $errors[] = $line_count . "行目の layout は必須です。";
                    }
                    break;
                case 6:
                    if (!$csv_column == '0' && !$csv_column == '1') {
                        $errors[] = $line_count . "行目の base_display_flag は 0 もしくは 1 である必要があります。";
                    }
                    break;
                }
            }
            $line_count++;
        }

        return $errors;
    }

    /**
     *  ページインポート処理
     *
     * @return view
     */
    public function upload($request, $page_id)
    {

        // CSVファイルチェック
        $validator = Validator::make($request->all(), [
            'page_csv' => [
                'required',
                'file',
                'mimes:csv,txt', // mimesの都合上text/csvなのでtxtも許可が必要
                'mimetypes:text/plain',
            ],
        ]);
        $validator->setAttributeNames([
            'page_csv' => 'インポートCSV',
        ]);
        if ($validator->fails()) {
            return ( $this->import($request, $page_id, $validator->errors()->all()) );
        }

        // CSVファイル一時保孫
        $path = $request->file('page_csv')->store('tmp');

        // 一行目（ヘッダ）読み込み
        $fp = fopen(storage_path('app/') . $path, 'r');
        $header_columns = fgetcsv($fp);

        // ヘッダー項目のエラーチェック
        $error_msgs = $this->checkHeader($header_columns);
        if (!empty($error_msgs)) {
            return ( $this->import($request, $page_id, $error_msgs) );
        }

        // データ項目のエラーチェック
        $error_msgs = $this->checkPageline($fp, $error_msgs);
        if (!empty($error_msgs)) {
            return ( $this->import($request, $page_id, $error_msgs) );
        }

        // ファイルを閉じて、開きなおす
        fclose ($fp);
        $fp = fopen(storage_path('app/') . $path, 'r');

        // ヘッダー
        $header_columns = fgetcsv($fp);

        // データ
        while (($csv_columns = fgetcsv($fp, 0, ",")) !== FALSE) {

            // 固定リンクの先頭に / がない場合、追加する。
            if (strncmp($csv_columns[1], '/', 1) !== 0) {
                $csv_columns[1] = '/' . $csv_columns[1];
            }

            // ページ名をUTF-8 に変換
            $csv_columns[0] = mb_convert_encoding($csv_columns[0], "UTF-8", "SJIS-WIN, Shift_JIS");

            // ページ作成
            $page = Page::create([
                'page_name'         => $csv_columns[0],
                'permanent_link'    => $csv_columns[1],
                'background_color'  => ($csv_columns[2] == 'NULL') ? null : $csv_columns[2],
                'header_color'      => ($csv_columns[3] == 'NULL') ? null : $csv_columns[3],
                'theme'             => ($csv_columns[4] == 'NULL') ? null : $csv_columns[4],
                'layout'            => ($csv_columns[5] == 'NULL') ? null : $csv_columns[5],
                'base_display_flag' => $csv_columns[6]
            ]);
        }

        // 一時ファイルの削除
        fclose ($fp);
        Storage::delete($path);

        // ページ管理画面に戻る
        return redirect("/manage/page/import");
    }
}
