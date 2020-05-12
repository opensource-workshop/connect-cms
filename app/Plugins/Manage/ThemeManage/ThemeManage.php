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
        $role_ckeck_table["index"]       = array('admin_site');
        $role_ckeck_table["create"]      = array('admin_site');
        $role_ckeck_table["editCss"]     = array('admin_site');
        $role_ckeck_table["saveCss"]     = array('admin_site');
        $role_ckeck_table["editJs"]      = array('admin_site');
        $role_ckeck_table["saveJs"]      = array('admin_site');
        $role_ckeck_table["editName"]    = array('admin_site');
        $role_ckeck_table["saveName"]    = array('admin_site');
        $role_ckeck_table["listImages"]  = array('admin_site');
        $role_ckeck_table["uploadImage"] = array('admin_site');
        $role_ckeck_table["deleteImage"] = array('admin_site');
        $role_ckeck_table["deleteTheme"] = array('admin_site');
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
        foreach ($tmp_dirs as $tmp_dir) {
            // テーマ設定ファイル取得
            $theme_inis = parse_ini_file(public_path() . '/themes/Users/' . basename($tmp_dir) . '/themes.ini');
            $theme_name = '';
            if (!empty($theme_inis) && array_key_exists('theme_name', $theme_inis)) {
                $theme_name = $theme_inis['theme_name'];
            }

            $dirs[basename($tmp_dir)] = array('dir' => basename($tmp_dir), 'theme_name' => $theme_name);
        }
        asort($dirs);  // ディレクトリが名前に対して逆順になることがあるのでソートしておく。

        // 管理画面プラグインの戻り値の返し方
        // view 関数の第一引数に画面ファイルのパス、第二引数に画面に渡したいデータを名前付き配列で渡し、その結果のHTML。
        return view('plugins.manage.theme.theme', [
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

        // themes.js ファイルの作成
        $result = File::put(public_path() . '/themes/Users/' . basename($request->dir_name) . '/themes.js', '');

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

        return view('plugins.manage.theme.theme_css_edit', [
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

        return view('plugins.manage.theme.theme_css_edit', [
            "function"    => __FUNCTION__,
            "plugin_name" => "theme",
            "dir_name"    => $dir_name,
            "css"         => $css,
        ]);
    }

    /**
     *  JavaScript 編集画面
     */
    public function editJs($request, $id)
    {
        // httpメソッド確認
        if (!$request->isMethod('post')) {
            abort(403, '権限がありません。');
        }

        // ディレクトリ名
        $dir_name = basename($request->dir_name);

        // ファイル名
        $js_path = public_path() . '/themes/Users/' . $dir_name . '/themes.js';

        // ファイルを存在チェックし、なければ空で作成する。
        if (!File::exists($js_path)) {
            $result = File::put($js_path, '');
        }

        // JavaScript ファイル取得
        $js = File::get($js_path);

        return view('plugins.manage.theme.theme_js_edit', [
            "function"    => __FUNCTION__,
            "plugin_name" => "theme",
            "dir_name"    => $dir_name,
            "js"          => $js,
        ]);
    }

    /**
     *  JavaScript 保存画面
     */
    public function saveJs($request, $id)
    {
        // httpメソッド確認
        if (!$request->isMethod('post')) {
            abort(403, '権限がありません。');
        }

        // ディレクトリ名
        $dir_name = basename($request->dir_name);

        // JavaScript
        $js = $request->js;

        // themes.css ファイルの保存
        $result = File::put(public_path() . '/themes/Users/' . $dir_name . '/themes.js', $js);

        return view('plugins.manage.theme.theme_js_edit', [
            "function"    => __FUNCTION__,
            "plugin_name" => "theme",
            "dir_name"    => $dir_name,
            "js"          => $js,
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

        return view('plugins.manage.theme.theme_name_edit', [
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

        return view('plugins.manage.theme.theme_name_edit', [
            "function"    => __FUNCTION__,
            "plugin_name" => "theme",
            "dir_name"    => $dir_name,
            "theme_name"  => $theme_name,
        ]);
    }

    /**
     *  画像管理
     */
    public function listImages($request, $id, $errors = array())
    {
        // httpメソッド確認
        if (!$request->isMethod('post')) {
            abort(403, '権限がありません。');
        }

        // ディレクトリ名
        $dir_name = basename($request->dir_name);

        // 画像ディレクトリ
        $images_dir = public_path() . '/themes/Users/' . $dir_name . '/images';

        // ディレクトリの存在チェック
        if (!File::isDirectory($images_dir)) {
            // ディレクトリがなければ空のリストを画面に渡す。
            return view('plugins.manage.theme.theme_image_list', [
                "function"    => __FUNCTION__,
                "plugin_name" => "theme",
                "dir_name"    => $dir_name,
                "files"       => array(),
                "errors"      => $errors,
            ]);
        }

        // 画像ファイルの一覧取得
        $tmp_files = File::files($images_dir);
        $files = array();
        foreach ($tmp_files as $tmp_file) {
            $files[] = basename($tmp_file);
        }
        asort($files);  // 名前に対して逆順になることがあるのでソートしておく。

        // 画像のリストを画面に渡す。
        return view('plugins.manage.theme.theme_image_list', [
            "function"    => __FUNCTION__,
            "plugin_name" => "theme",
            "dir_name"    => $dir_name,
            "files"       => $files,
            "errors"      => $errors,
        ]);
    }

    /**
     *  画像アップロード
     */
    public function uploadImage($request, $id)
    {
        // httpメソッド確認
        if (!$request->isMethod('post')) {
            abort(403, '権限がありません。');
        }

        // セッション初期化などのLaravel 処理。
        $request->flash();

        // ディレクトリ名
        $dir_name = basename($request->dir_name);

        // ファイルがアップロードされた。
        if ($request->hasFile('image')) {
            // ファイルの基礎情報
            $client_original_name = $request->file('image')->getClientOriginalName();
            $mimetype             = $request->file('image')->getClientMimeType();
            $extension            = $request->file('image')->getClientOriginalExtension();

            // 拡張子チェック
            if (mb_strtolower($extension) != 'jpg' && mb_strtolower($extension) != 'png' && mb_strtolower($extension) != 'gif') {
                $validator = Validator::make($request->all(), []);
                $validator->errors()->add('not_extension', 'jpg, png, gif 以外はアップロードできません。');
                return $this->listImages($request, $id)->withErrors($validator);
            }

            // ファイル名を英数記号のみに
            $tmp_filename = trim(mb_convert_kana($client_original_name, 'as', 'UTF-8'));
            $filename = preg_replace('/[^a-zA-Z0-9._-]/', '', $tmp_filename);

            // ファイルの保存
            $request->file('image')->storeAs('tmp', $filename);

            // ファイルパス
            $src_file = storage_path() . '/app/tmp/' . $filename;
            $image_dir = public_path() . '/themes/Users/' . $dir_name . '/images';
            $dst_file = $image_dir . '/' . $filename;

            // ディレクトリの存在チェック
            if (!File::isDirectory($image_dir)) {
                $result = File::makeDirectory($image_dir);
            }

            // テーマディレクトリへファイルの移動
            if (!rename($src_file, $dst_file)) {
                die("Couldn't rename file");
            }
        }

        // 画像一覧画面へ戻る
        return $this->listImages($request, $id);
    }

    /**
     *  画像削除
     */
    public function deleteImage($request, $id)
    {
        // httpメソッド確認
        if (!$request->isMethod('post')) {
            abort(403, '権限がありません。');
        }

        // ディレクトリ名
        $dir_name = basename($request->dir_name);

        // ファイル名を英数記号のみに
        $tmp_filename = trim(mb_convert_kana($request->file_name, 'as', 'UTF-8'));
        $filename = preg_replace('/[^a-zA-Z0-9._-]/', '', $tmp_filename);

        // ファイルパス
        $image_dir = public_path() . '/themes/Users/' . $dir_name . '/images';
        $delete_file = $image_dir . '/' . $filename;

        // ファイル削除
        File::delete($delete_file);

        // 画像一覧画面へ戻る
        return $this->listImages($request, $id);
    }

    /**
     *  画像削除
     */
    public function deleteTheme($request, $id)
    {
        // httpメソッド確認
        if (!$request->isMethod('post')) {
            abort(403, '権限がありません。');
        }

        // ディレクトリ名
        $dir_name = basename($request->dir_name);

        // テーマ・ディレクトリ
        $theme_dir = public_path() . '/themes/Users/' . $dir_name;

        // ディレクトリの存在チェック
        if (File::isDirectory(public_path() . '/themes/Users/' . $dir_name)) {
            // テーマの削除
            $success = File::deleteDirectory($theme_dir);
        }

        return redirect("/manage/theme");
    }
}
