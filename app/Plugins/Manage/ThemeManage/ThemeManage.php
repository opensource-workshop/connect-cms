<?php

namespace App\Plugins\Manage\ThemeManage;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;

use File;
//use DB;

use App\Plugins\Manage\ManagePluginBase;

/**
 * テーマ管理クラス
 *
 * @author 永原　篤 <nagahara@opensource-workshop.jp>
 * @copyright OpenSource-WorkShop Co.,Ltd. All Rights Reserved
 * @category テーマ管理
 * @package Contoroller
 */
class ThemeManage extends ManagePluginBase
{
    /**
     *  権限定義
     */
    public function declareRole()
    {
        // 権限チェックテーブル
        $role_ckeck_table = array();
        $role_ckeck_table["index"]    = array('admin_site');
        $role_ckeck_table["create"]   = array('admin_site');
        $role_ckeck_table["editCss"]  = array('admin_site');
        $role_ckeck_table["saveCss"]  = array('admin_site');
        $role_ckeck_table["editName"] = array('admin_site');
        $role_ckeck_table["saveName"] = array('admin_site');
        return $role_ckeck_table;
    }

    /**
     *  ページ初期表示
     *
     * @return view
     */
    public function index($request, $page_id = null, $errors = array())
    {
        // Users テーマディレクトリの取得
        $tmp_dirs = File::directories(public_path() . '/themes/Users/');
        $dirs = array();
        foreach($tmp_dirs as $tmp_dir) {
            $dirs[] = basename($tmp_dir);
        }
        asort($dirs);  // ディレクトリが名前に対して逆順になることがあるのでソートしておく。

        // 管理画面プラグインの戻り値の返し方
        // view 関数の第一引数に画面ファイルのパス、第二引数に画面に渡したいデータを名前付き配列で渡し、その結果のHTML。
        return view('plugins.manage.theme.theme',[
            "function"    => __FUNCTION__,
            "plugin_name" => "theme",
            "dirs"        => $dirs,
            "errors"      => $errors,
        ]);
    }

    /**
     *  ディレクトリ作成
     */
    public function create($request, $id)
    {
        // httpメソッド確認
        if (!$request->isMethod('post')) {
            abort(403, '権限がありません。');
        }

        // セッション初期化などのLaravel 処理
        $request->flash();

        // 項目のエラーチェック
        $validator = Validator::make($request->all(), [
            'dir_name'   => ['required'],
            'theme_name' => ['required'],
        ]);
        $validator->setAttributeNames([
            'dir_name'   => 'ディレクトリ名',
            'theme_name' => 'テーマ名',
        ]);

        // エラーがあった場合は入力画面に戻る。
        if ($validator->fails()) {
            return $this->index($request, $id, $validator->errors());
        }

        // ディレクトリの存在チェック
        if (File::isDirectory(public_path() . '/themes/Users/' . $request->dir_name)) {
            $validator->errors()->add('dir_name', 'このディレクトリは存在します。');
            return $this->index($request, $id, $validator->errors());
        }

        // ディレクトリとテーマ初期ファイルの生成
        $result = File::makeDirectory(public_path() . '/themes/Users/' . basename($request->dir_name), 0775);

        // themes.ini ファイルの作成
        $themes_ini = '[base]' . "\n" . 'theme_name = ' . $request->theme_name;
        $result = File::put(public_path() . '/themes/Users/' . basename($request->dir_name) . '/themes.ini', $themes_ini);

        // themes.css ファイルの作成
        $result = File::put(public_path() . '/themes/Users/' . basename($request->dir_name) . '/themes.css', '');

        return redirect("/manage/theme");
    }

    /**
     *  CSS 編集画面
     */
    public function editCss($request, $id)
    {
        // httpメソッド確認
        if (!$request->isMethod('post')) {
            abort(403, '権限がありません。');
        }

        // ディレクトリ名
        $dir_name = basename($request->dir_name);

        // CSS ファイル取得
        $css = File::get(public_path() . '/themes/Users/' . $dir_name . '/themes.css');

        return view('plugins.manage.theme.theme_css_edit',[
            "function"    => __FUNCTION__,
            "plugin_name" => "theme",
            "dir_name"    => $dir_name,
            "css"         => $css,
        ]);
    }

    /**
     *  CSS 保存画面
     */
    public function saveCss($request, $id)
    {
        // httpメソッド確認
        if (!$request->isMethod('post')) {
            abort(403, '権限がありません。');
        }

        // ディレクトリ名
        $dir_name = basename($request->dir_name);

        // CSS
        $css = $request->css;

        // themes.css ファイルの保存
        $result = File::put(public_path() . '/themes/Users/' . $dir_name . '/themes.css', $css);

        return view('plugins.manage.theme.theme_css_edit',[
            "function"    => __FUNCTION__,
            "plugin_name" => "theme",
            "dir_name"    => $dir_name,
            "css"         => $css,
        ]);
    }

    /**
     *  テーマ名編集画面
     */
    public function editName($request, $id, $errors = array())
    {
        // httpメソッド確認
        if (!$request->isMethod('post')) {
            abort(403, '権限がありません。');
        }

        // ディレクトリ名
        $dir_name = basename($request->dir_name);

        // テーマ設定ファイル取得
        $theme_inis = parse_ini_file(public_path() . '/themes/Users/' . $dir_name . '/themes.ini');
        $theme_name = '';
        if (!empty($theme_inis) && array_key_exists('theme_name', $theme_inis)) {
            $theme_name = $theme_inis['theme_name'];
        }

        return view('plugins.manage.theme.theme_name_edit',[
            "function"    => __FUNCTION__,
            "plugin_name" => "theme",
            "dir_name"    => $dir_name,
            "theme_name"  => $theme_name,
            "errors"      => $errors,
        ]);
    }

    /**
     *  テーマ名保存画面
     */
    public function saveName($request, $id)
    {
        // httpメソッド確認
        if (!$request->isMethod('post')) {
            abort(403, '権限がありません。');
        }

        // セッション初期化などのLaravel 処理
        $request->flash();

        // 項目のエラーチェック
        $validator = Validator::make($request->all(), [
            'theme_name' => ['required'],
        ]);
        $validator->setAttributeNames([
            'theme_name' => 'テーマ名',
        ]);

        // エラーがあった場合は入力画面に戻る。
        if ($validator->fails()) {
            return $this->editName($request, $id, $validator->errors());
        }

        // ディレクトリ名
        $dir_name = basename($request->dir_name);

        // テーマ名
        $theme_name = $request->theme_name;

        // themes.ini ファイルの保存
        $themes_ini = '[base]' . "\n" . 'theme_name = ' . $theme_name;
        $result = File::put(public_path() . '/themes/Users/' . $dir_name . '/themes.ini', $themes_ini);

        return view('plugins.manage.theme.theme_name_edit',[
            "function"    => __FUNCTION__,
            "plugin_name" => "theme",
            "dir_name"    => $dir_name,
            "theme_name"  => $theme_name,
        ]);
    }
}
