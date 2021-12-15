<?php

namespace App\Plugins\Manage\PageManage;

// use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;

use DB;

use App\Models\Common\Buckets;
use App\Models\Common\Frame;
use App\Models\Common\Group;
use App\Models\Common\Page;
use App\Models\Common\PageRole;
use App\Models\User\Contents\Contents;

use App\Rules\CustomValiTextMax;
use App\Rules\CustomValiUrlMax;

use App\Traits\Migration\MigrationTrait;

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
    // 移行用ライブラリ
    use MigrationTrait;

    /**
     * 権限定義
     */
    public function declareRole()
    {
        // 権限チェックテーブル
        $role_ckeck_table = array();
        $role_ckeck_table["index"]           = array('admin_page');
        $role_ckeck_table["edit"]            = array('admin_page');
        $role_ckeck_table["store"]           = array('admin_page');
        $role_ckeck_table["update"]          = array('admin_page');
        $role_ckeck_table["destroy"]         = array('admin_page');
        $role_ckeck_table["sequence_up"]     = array('admin_page');
        $role_ckeck_table["sequence_down"]   = array('admin_page');
        $role_ckeck_table["move_page"]       = array('admin_page');
        $role_ckeck_table["import"]          = array('admin_page');
        $role_ckeck_table["upload"]          = array('admin_page');
        $role_ckeck_table["role"]            = array('admin_page');
        $role_ckeck_table["saveRole"]        = array('admin_page');
        $role_ckeck_table["migration_order"] = array('admin_page');
        $role_ckeck_table["migration_get"]   = array('admin_page');
        $role_ckeck_table["migration_imort"] = array('admin_page');
        $role_ckeck_table["migration_file_delete"] = array('admin_page');

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
     * ページ初期表示
     *
     * @return view
     */
    public function index($request, $page_id = null, $errors = array())
    {
        // ページデータの取得(laravel-nestedset 使用)
        $return_obj = 'flat';
        $pages = Page::defaultOrderWithDepth($return_obj);


        // ページ権限を取得してGroup オブジェクトに保持する。
        $page_roles = PageRole::join('groups', 'groups.id', '=', 'page_roles.group_id')
                ->whereNull('groups.deleted_at')
                ->where('page_roles.role_value', 1)
                ->get();
        // \Log::debug(var_export($page_roles, true));

        foreach ($pages as &$page) {
            $page->page_roles = $page_roles->where('page_id', $page->id);
        }

        // 移動先用にコピー
        $pages_select = $pages;

        // テーマの取得
        $themes = $this->getThemes();

        // 管理画面プラグインの戻り値の返し方
        // view 関数の第一引数に画面ファイルのパス、第二引数に画面に渡したいデータを名前付き配列で渡し、その結果のHTML。
        return view('plugins.manage.page.page', [
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
     * ページ編集画面表示
     *
     * @return view
     */
    public function edit($request, $page_id = null)
    {
        // 編集時と新規で処理を分ける
        if (empty($page_id)) {
            $page = new Page();
        } else {
            // ページID で1件取得
            $page = Page::where('id', $page_id)->first();
        }

        // ページデータの取得(laravel-nestedset 使用)
        $pages = Page::defaultOrderWithDepth();

        // テーマの取得
        $themes = $this->getThemes();

        // 画面呼び出し
        return view('plugins.manage.page.page_edit', [
            "function"    => __FUNCTION__,
            "plugin_name" => "page",
            "page"        => $page,
            "pages"       => $pages,
            "themes"      => $themes,
        ]);
    }

    /**
     * ページ登録・変更時のエラーチェック
     */
    private function pageValidator($request)
    {
        // 項目のエラーチェック
        $validator = Validator::make($request->all(), [
            'page_name'        => ['required', 'max:255'],
            'permanent_link'   => ['nullable', new CustomValiUrlMax(true)],
            'password'         => ['nullable', 'max:255'],
            'background_color' => ['nullable', 'max:255'],
            'header_color'     => ['nullable', 'max:255'],
            'ip_address'       => ['nullable', new CustomValiTextMax()],
            'othersite_url'    => ['nullable', new CustomValiUrlMax()],
            'class'            => ['nullable', 'max:255'],
        ]);
        $validator->setAttributeNames([
            'page_name'        => 'ページ名',
            'permanent_link'   => '固定リンク',
            'password'         => 'パスワード',
            'background_color' => '背景色',
            'header_color'     => 'ヘッダーバーの背景色',
            'ip_address'       => 'IPアドレス制限',
            'othersite_url'    => '外部サイトURL',
            'class'            => 'クラス名',
        ]);
        return $validator;
    }

    /**
     * ページ登録処理
     */
    public function store($request)
    {
        // ページ登録・変更時のエラーチェック
        $validator = $this->pageValidator($request);

        // エラーがあった場合は入力画面に戻る。
        if ($validator->fails()) {
            // return ( $this->index($request, null, $validator->errors()) );
            return redirect()->back()->withErrors($validator)->withInput();
        }

        // 固定リンクの先頭に / がない場合、追加する。
        if (strncmp($request->permanent_link, '/', 1) !== 0) {
            $request->permanent_link = '/' . $request->permanent_link;
        }

        // ページデータの登録
        $page = new Page;
        $page->page_name            = $request->page_name;
        $page->permanent_link       = $request->permanent_link;
        $page->password             = $request->password;
        $page->background_color     = $request->background_color;
        $page->header_color         = $request->header_color;
        $page->theme                = $request->theme;
        $page->layout               = $request->layout;
        $page->base_display_flag    = (isset($request->base_display_flag) ? $request->base_display_flag : 0);
        $page->membership_flag      = (isset($request->membership_flag) ? $request->membership_flag : 0);
        $page->ip_address           = $request->ip_address;
        $page->othersite_url        = $request->othersite_url;
        $page->othersite_url_target = (isset($request->othersite_url_target) ? $request->othersite_url_target : 0);
        $page->transfer_lower_page_flag = $request->transfer_lower_page_flag ?? 0;
        $page->class                = $request->class;
        $page->save();

        // ページ管理画面に戻る
        return redirect("/manage/page");
    }

    /**
     * ページ更新処理
     */
    public function update($request, $page_id)
    {
        // ページ登録・変更時のエラーチェック
        $validator = $this->pageValidator($request);

        // エラーがあった場合は入力画面に戻る。
        if ($validator->fails()) {
            return redirect()->back()->withErrors($validator)->withInput();
        }

        // 固定リンクの先頭に / がない場合、追加する。
        if (strncmp($request->permanent_link, '/', 1) !== 0) {
            $request->permanent_link = '/' . $request->permanent_link;
        }

        // ページデータの更新
        Page::where('id', $page_id)
            ->update([
                'page_name'            => $request->page_name,
                'permanent_link'       => $request->permanent_link,
                'password'             => $request->password,
                'background_color'     => $request->background_color,
                'header_color'         => $request->header_color,
                'theme'                => $request->theme,
                'layout'               => $request->layout,
                'base_display_flag'    => (isset($request->base_display_flag) ? $request->base_display_flag : 0),
                'membership_flag'      => (isset($request->membership_flag) ? $request->membership_flag : 0),
                'ip_address'           => $request->ip_address,
                'othersite_url'        => $request->othersite_url,
                'othersite_url_target' => (isset($request->othersite_url_target) ? $request->othersite_url_target : 0),
                'transfer_lower_page_flag' => $request->transfer_lower_page_flag ?? 0,
                'class'                => $request->class,
        ]);

        // ページ管理画面に戻る
        return redirect("/manage/page");
    }

    /**
     * ページ削除関数
     */
    public function destroy($request, $page_id)
    {
        // Log::debug($id);
        DB::table('pages')->where('id', '=', $page_id)->delete();

        // ページ管理画面に戻る
        return redirect("/manage/page");
    }

    /**
     * ページ上移動
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
     * ページ下移動
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
     * ページ指定場所移動
     */
    public function move_page($request, $page_id)
    {
        // ルートへ移動
        if ($request->destination_id == "0") {
            // 移動元のオブジェクトを取得
            $page = Page::find($page_id);
            $page->saveAsRoot();
        } else {
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
     * ページインポート画面表示
     *
     * @return view
     */
    public function import($request, $page_id)
    {
        // 画面呼び出し
        return view('plugins.manage.page.page_import', [
            "function"     => __FUNCTION__,
            "plugin_name"  => "page",
        ]);
    }

    /**
     * CSVヘッダーチェック
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
     * CSVデータ行チェック
     */
    private function checkPageline($fp, $errors = null)
    {
        $line_count = 1;

        while (($csv_columns = fgetcsv($fp, 0, ",")) !== false) {
            foreach ($csv_columns as $column_index => $csv_column) {
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
     * 「固定記事」プラグインを新規で配置
     *
     * @return view
     */
    public function createContent($page_id)
    {
        // Buckets 登録
        $bucket = Buckets::create(['bucket_name' => '無題', 'plugin_name' => 'contents']);

        // フレーム作成
        $frame = Frame::create(['page_id'          => $page_id,
                                'area_id'          => 2,
                                'frame_title'      => '[無題]',
                                'frame_design'     => 'default',
                                'plugin_name'      => 'contents',
                                'frame_col'        => 0,
                                'template'         => 'default',
                                'bucket_id'        => $bucket->id,
                                'display_sequence' => 1,
                               ]);

        // Contents 登録
        $content = Contents::create(['bucket_id'    => $bucket->id,
                                     'status'       => 0]);

        return true;
    }

    /**
     * ページインポート処理
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
            // return ( $this->import($request, $page_id, $validator->errors()->all()) );
            return redirect()->back()->withErrors($validator)->withInput();
        }

        // CSVファイル一時保孫
        $path = $request->file('page_csv')->store('tmp');

        // bugfix: fgetcsv() は ロケール設定の影響を受け、xampp環境＋日本語文字列で誤動作したため、ロケール設定する。
        setlocale(LC_ALL, 'ja_JP.UTF-8');

        // 一行目（ヘッダ）読み込み
        $fp = fopen(storage_path('app/') . $path, 'r');
        $header_columns = fgetcsv($fp);

        // ヘッダー項目のエラーチェック
        $error_msgs = $this->checkHeader($header_columns);
        if (!empty($error_msgs)) {
            // 一時ファイルの削除
            fclose($fp);
            Storage::delete($path);

            // return ( $this->import($request, $page_id, $error_msgs) );
            return redirect()->back()->withErrors(['page_csv' => $error_msgs])->withInput();
        }

        // データ項目のエラーチェック
        $error_msgs = $this->checkPageline($fp, $error_msgs);
        if (!empty($error_msgs)) {
            // 一時ファイルの削除
            fclose($fp);
            Storage::delete($path);

            // return ( $this->import($request, $page_id, $error_msgs) );
            return redirect()->back()->withErrors(['page_csv' => $error_msgs])->withInput();
        }

        // ファイルを閉じて、開きなおす
        fclose($fp);
        $fp = fopen(storage_path('app/') . $path, 'r');

        // ヘッダー
        $header_columns = fgetcsv($fp);

        // データ
        while (($csv_columns = fgetcsv($fp, 0, ",")) !== false) {
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

            // 初期配置がある場合
            if ($request->has('deploy_content_plugin') && $request->deploy_content_plugin == '1') {
                $this->createContent($page->id);
            }
        }

        // 一時ファイルの削除
        fclose($fp);
        Storage::delete($path);

        // ページ管理画面に戻る
        return redirect("/manage/page/import")->with('flash_message', 'インポートしました。');
    }

    /**
     * グループ権限設定画面表示
     *
     * @return view
     */
    public function role($request, $page_id, $group_id)
    {
        // ページID で1件取得
        $page = Page::find($page_id);

        // ページデータ取得
        if (empty($page)) {
            // 画面呼び出し
            return view('plugins.manage.page.error', [
                "function"     => __FUNCTION__,
                "plugin_name"  => "page",
                "message"      => "指定されたページID が存在しません。",
            ]);
        }

        // グループの取得
        $groups = Group::orderBy('name', 'asc')->get();

        // ページ権限を取得してGroup オブジェクトに保持する。
        $page_roles = PageRole::where('page_id', $page->id)
                              ->where('role_value', 1)
                              ->orderBy('group_id', 'asc')
                              ->get();
        foreach ($groups as $group) {
            $group->page_roles = $page_roles->where('group_id', $group->id);
        }

        // 画面呼び出し
        return view('plugins.manage.page.role', [
            "function"     => __FUNCTION__,
            "plugin_name"  => "page",
            "page"         => $page,
            "group_id"     => $group_id,
            "groups"       => $groups,
        ]);
    }

    /**
     * page_roles テーブルの更新
     */
    private function updatePageRoles($page_id, $group_id, $role_name, $role_value)
    {
        if (empty($role_value)) {
            // role_value が空の場合は、チェックされていないということなので、delete
            // この時、もともとレコードがない場合は 0件削除されるだけなので、delete 処理する。
            PageRole::where('page_id', $page_id)->where('group_id', $group_id)->where('role_name', $role_name)->delete();
        } else {
            // 更新もしくは追加
            PageRole::updateOrCreate(
                ['page_id' => $page_id, 'group_id' => $group_id, 'target' => 'base', 'role_name' => $role_name,],
                ['page_id' => $page_id, 'group_id' => $group_id, 'target' => 'base', 'role_name' => $role_name, 'role_value' => 1]
            );
        }
        return;
    }

    /**
     * グループ権限保存
     *
     * @return view
     */
    public function saveRole($request, $page_id)
    {
        // Role をループ
        foreach (config('cc_role.CC_ROLE_LIST') as $role_name => $cc_role_name) {
            // 管理権限は対象外
            if (stripos($role_name, 'admin_') === 0) {
                continue;
            }
            // page_roles 更新
            $this->updatePageRoles($page_id, $request->group_id, $role_name, $request->input($role_name));
        }

        // 更新後は一覧画面へ
        return redirect('manage/page/role/' . $page_id . '/' . $request->group_id);
    }

    /**
     * 外部ページ取り込み指示画面
     *
     * @return view
     */
    public function migration_order($request, $page_id)
    {
        // ページID で1件取得
        $current_page = Page::find($page_id);

        // ページデータ取得
        if (empty($current_page)) {
            // 画面呼び出し
            return view('plugins.manage.page.error', [
                "function"     => __FUNCTION__,
                "plugin_name"  => "page",
                "message"      => "指定されたページID が存在しません。",
            ]);
        }

        // 移行先ページ用ページデータの取得(laravel-nestedset 使用)
        $return_obj = 'flat';
        $pages = Page::defaultOrderWithDepth($return_obj);

        // 移行用に取り込んだページ単位ディレクトリの取得
        $migration_directories = Storage::directories('migration/import/pages');

        // 移行用に取り込んだページ単位ディレクトリのページ情報
        $page_in = array();
        foreach ($migration_directories as $migration_directory) {
            $page_in[] = str_replace('migration/import/pages/', '', $migration_directory);
        }
        //print_r($page_in);

        // ページ一覧の取得
        $migration_pages = Page::whereIn('id', $page_in)->get();
        //var_dump($migration_pages);

        // 画面呼び出し
        return view('plugins.manage.page.migration_order', [
            "function"        => __FUNCTION__,
            "plugin_name"     => "page",
            "current_page"    => $current_page,
            "page"            => $current_page,  // bugfix: サブメニュー表示するのにpage変数必要
            "pages"           => $pages,
            "migration_pages" => $migration_pages,
        ]);
    }

    /**
     * 取り込み済み移行データ削除
     *
     * @return view
     */
    public function migration_file_delete($request, $page_id)
    {
        // 削除対象のディレクトリが指定されていること。
        if (!$request->has("delete_file_page_id") && !empty($request->delete_file_page_id)) {
            // 指示された画面に戻る。
            return $this->migration_order($request, $page_id);
        }

        // 指定されたディレクトリを削除
        Storage::deleteDirectory("migration/import/pages/" . $request->delete_file_page_id);

        // 指示された画面に戻る。
        return $this->migration_order($request, $page_id);
    }

    /**
     * 移行データ取り込み実行
     *
     * @return view
     */
    public function migration_get($request, $page_id)
    {
        // 項目のエラーチェック
        $validator = Validator::make($request->all(), [
            'source_system'       => 'required',
            'url'                 => 'required',
            'destination_page_id' => 'required',
        ]);
        $validator->setAttributeNames([
            'source_system'       => '移行元システム',
            'url'                 => '移行元URL',
            'destination_page_id' => '移行先ページ',
        ]);

        // エラーがあった場合は入力画面に戻る。
        if ($validator->fails()) {
            return redirect('manage/page/migration_order/' . $page_id)
                       ->withErrors($validator)
                       ->withInput();
        }

        // NC3 を画面から移行する
        $this->migrationNC3Page($request->url, $request->destination_page_id);

        // 指示された画面に戻る。
        return $this->migration_order($request, $page_id);
    }

    /**
     * 移行データインポート実行
     *
     * @return view
     */
    public function migration_imort($request, $page_id)
    {
        // 項目のエラーチェック
        $validator = Validator::make($request->all(), [
            'migration_page_id' => 'required',
        ]);
        $validator->setAttributeNames([
            'migration_page_id' => '取り込み済み移行データ',
        ]);

        // エラーがあった場合は入力画面に戻る。
        if ($validator->fails()) {
            return redirect('manage/page/migration_order/' . $page_id)
                       ->withErrors($validator)
                       ->withInput();
        }

        // migration_config を生成
        $this->migration_config['frames'] = ['import_frame_plugins'];
        $this->migration_config['frames']['import_frame_plugins'] = ['contents'];

        // Connect-CMS 移行形式のHTML をインポートする
        $this->importHtml($request->migration_page_id, storage_path() . '/app/migration/import/pages/' . $page_id);

        // 指示された画面に戻る。
        return $this->migration_order($request, $page_id);
    }
}
