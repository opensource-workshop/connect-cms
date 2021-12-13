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
        $role_ckeck_table["generateIndex"]    = array('admin_site');
        $role_ckeck_table["generate"]    = array('admin_site');
        return $role_ckeck_table;
    }
    public const COLORLIST = [
        'white'=>'#ffffff',
        'black'=>'#000000',
        'azuki'=>'#941e2a',
        'blue'=>'#04419a',
        'gray'=>'#808080',
        'green'=>'#1b9000',
        'kikyou'=>'#624498',
        'koke'=>'#69821b',
        'lime'=>'#6bc800',
        'orange'=>'#f88b00',
        'pink'=>'#ff74d3',
        'red'=>'#B30000',
        'sakura'=>'#f09199',
        'yamabuki'=>'#f8b500',
        'beige'=>'#decf96',
        'deepblue'=>'#12335c',
        'deepgreen'=>'#155604',
        'deepred'=>'#7c2828',
        'purple'=>'#e2ccf2',
        'darkbrown'=>'#6B493D',
        'skyblue'=>'#87ceeb',
        'steelblue'=>'#4682b4',
        'tan'=>'#d2b48c',
        'salmon'=>'#fa8072',
        'darkslategray' => '#2f4f4f',
        'gold' => '#b28d3a',
        'palevioletred' => '#db7093',
    ];
    /* ブラウザで名前が定義されている140色
    public const COLORLIST = [
        'black'=>'#000000',
        'aliceblue'=>'#f0f8ff',
        'darkcyan'=>'#008b8b',
        'lightyellow'=>'#ffffe0',
        'coral'=>'#ff7f50',
        'dimgray'=>'#696969',
        'lavender'=>'#e6e6fa',
        'teal'=>'#008080',
        'lightgoldenrodyellow'=>'#fafad2',
        'tomato'=>'#ff6347',
        'gray'=>'#808080',
        'lightsteelblue'=>'#b0c4de',
        'darkslategray'=>'#2f4f4f',
        'lemonchiffon'=>'#fffacd',
        'orangered'=>'#ff4500',
        'darkgray'=>'#a9a9a9',
        'lightslategray'=>'#778899',
        'darkgreen'=>'#006400',
        'wheat'=>'#f5deb3',
        'red'=>'#ff0000',
        'silver'=>'#c0c0c0',
        'slategray'=>'#708090',
        'green'=>'#008000',
        'burlywood'=>'#deb887',
        'crimson'=>'#dc143c',
        'lightgray'=>'#d3d3d3',
        'steelblue'=>'#4682b4',
        'forestgreen'=>'#228b22',
        'tan'=>'#d2b48c',
        'mediumvioletred'=>'#c71585',
        'gainsboro'=>'#dcdcdc',
        'royalblue'=>'#4169e1',
        'seagreen'=>'#2e8b57',
        'khaki'=>'#f0e68c',
        'deeppink'=>'#ff1493',
        'whitesmoke'=>'#f5f5f5',
        'midnightblue'=>'#191970',
        'mediumseagreen'=>'#3cb371',
        'yellow'=>'#ffff00',
        'hotpink'=>'#ff69b4',
        'white'=>'#ffffff',
        'navy'=>'#000080',
        'mediumaquamarine'=>'#66cdaa',
        'gold'=>'#ffd700',
        'palevioletred'=>'#db7093',
        'snow'=>'#fffafa',
        'darkblue'=>'#00008b',
        'darkseagreen'=>'#8fbc8f',
        'orange'=>'#ffa500',
        'pink'=>'#ffc0cb',
        'ghostwhite'=>'#f8f8ff',
        'mediumblue'=>'#0000cd',
        'aquamarine'=>'#7fffd4',
        'sandybrown'=>'#f4a460',
        'lightpink'=>'#ffb6c1',
        'floralwhite'=>'#fffaf0',
        'blue'=>'#0000ff',
        'palegreen'=>'#98fb98',
        'darkorange'=>'#ff8c00',
        'thistle'=>'#d8bfd8',
        'linen'=>'#faf0e6',
        'dodgerblue'=>'#1e90ff',
        'lightgreen'=>'#90ee90',
        'goldenrod'=>'#daa520',
        'magenta'=>'#ff00ff',
        'antiquewhite'=>'#faebd7',
        'cornflowerblue'=>'#6495ed',
        'springgreen'=>'#00ff7f',
        'peru'=>'#cd853f',
        'fuchsia'=>'#ff00ff',
        'papayawhip'=>'#ffefd5',
        'deepskyblue'=>'#00bfff',
        'mediumspringgreen'=>'#00fa9a',
        'darkgoldenrod'=>'#b8860b',
        'violet'=>'#ee82ee',
        'blanchedalmond'=>'#ffebcd',
        'lightskyblue'=>'#87cefa',
        'lawngreen'=>'#7cfc00',
        'chocolate'=>'#d2691e',
        'plum'=>'#dda0dd',
        'bisque'=>'#ffe4c4',
        'skyblue'=>'#87ceeb',
        'chartreuse'=>'#7fff00',
        'sienna'=>'#a0522d',
        'orchid'=>'#da70d6',
        'moccasin'=>'#ffe4b5',
        'lightblue'=>'#add8e6',
        'greenyellow'=>'#adff2f',
        'saddlebrown'=>'#8b4513',
        'mediumorchid'=>'#ba55d3',
        'navajowhite'=>'#ffdead',
        'powderblue'=>'#b0e0e6',
        'lime'=>'#00ff00',
        'maroon'=>'#800000',
        'darkorchid'=>'#9932cc',
        'peachpuff'=>'#ffdab9',
        'paleturquoise'=>'#afeeee',
        'limegreen'=>'#32cd32',
        'darkred'=>'#8b0000',
        'darkviolet'=>'#9400d3',
        'mistyrose'=>'#ffe4e1',
        'lightcyan'=>'#e0ffff',
        'yellowgreen'=>'#9acd32',
        'brown'=>'#a52a2a',
        'darkmagenta'=>'#8b008b',
        'lavenderblush'=>'#fff0f5',
        'cyan'=>'#00ffff',
        'darkolivegreen'=>'#556b2f',
        'firebrick'=>'#b22222',
        'purple'=>'#800080',
        'seashell'=>'#fff5ee',
        'aqua'=>'#00ffff',
        'olivedrab'=>'#6b8e23',
        'indianred'=>'#cd5c5c',
        'indigo'=>'#4b0082',
        'oldlace'=>'#fdf5e6',
        'turquoise'=>'#40e0d0',
        'olive'=>'#808000',
        'rosybrown'=>'#bc8f8f',
        'darkslateblue'=>'#483d8b',
        'ivory'=>'#fffff0',
        'mediumturquoise'=>'#48d1cc',
        'darkkhaki'=>'#bdb76b',
        'darksalmon'=>'#e9967a',
        'blueviolet'=>'#8a2be2',
        'honeydew'=>'#f0fff0',
        'darkturquoise'=>'#00ced1',
        'palegoldenrod'=>'#eee8aa',
        'lightcoral'=>'#f08080',
        'mediumpurple'=>'#9370db',
        'mintcream'=>'#f5fffa',
        'lightseagreen'=>'#20b2aa',
        'cornsilk'=>'#fff8dc',
        'salmon'=>'#fa8072',
        'slateblue'=>'#6a5acd',
        'azure'=>'#f0ffff',
        'cadetblue'=>'#5f9ea0',
        'beige'=>'#f5f5dc',
        'lightsalmon'=>'#ffa07a',
        'mediumslateblue'=>'#7b68ee',
    ];
    */

    public const BORDERLIST = [
        'none'=>'非表示',
        'hidden'=>'非表示（強）',
        'solid'=>'実線',
        'dotted'=>'点線',
        'dashed'=>'破線',
    ];

    public const FONTFAMILYLIST = [
        'ゴシック（sans-serif）'=>'sans-serif',
        '明朝体（serif）'=>'serif',
        'OS依存（system-ui）'=>'system-ui',
        '等幅（monospace）'=>'monospace,monospace',
        '手書き（cursive）'=>'cursive',
        '装飾（fantasy）'=>'fantasy',
        'UD デジタル 教科書体'=>'"UD デジタル 教科書体 N-R",sans-serif',
        'メイリオ'=>'Meiryo,sans-serif',
        'Connect公式サイト'=>'-apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, "Helvetica Neue", Arial, "Noto Sans", sans-serif, "Apple Color Emoji", "Segoe UI Emoji", "Segoe UI Symbol", "Noto Color Emoji"',
    ];



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

    /**
     *  カスタムテーマ生成画面
     */
    public function generateIndex($request, $page_id = null, $errors = array())
    {
        $dirs = array();
        /* 組み合わせ */
        $theme_set['normal-blue']=['menu_horizon'=>'white|blue|solid|none','menu_vertical'=>'white|blue|none|none','frame_tittle'=>'white|blue|solid|none',];
        $theme_set['simple-red']=['menu_horizon'=>'white|red|solid|none','menu_vertical'=>'red|red|none|circle','frame_tittle'=>'red|white|solid|center',];
        $theme_set['simple-black']=['menu_horizon'=>'black|black|solid|underline','menu_vertical'=>'black|white|none|none','frame_tittle'=>'black|white|solid|emphasis',];
        $theme_set['ribbon-darkslategray']=['menu_horizon'=>'darkslategray|darkslategray|solid|underline','menu_vertical'=>'darkslategray|darkslategray|solid|underline','frame_tittle'=>'white|darkslategray|none|ribbon',];
        $theme_set['balloon-palevioletred']=['menu_horizon'=>'palevioletred|palevioletred|solid|underline','menu_vertical'=>'palevioletred|palevioletred|solid|underline','frame_tittle'=>'white|palevioletred|none|balloon',];
        /* clear */
        $theme_set['clear-azuki']=['menu_horizon'=>'azuki|azuki|solid|clear','menu_vertical'=>'white|azuki|none|none','frame_tittle'=>'azuki|azuki|solid|rectangle',];
        $theme_set['clear-blue']=['menu_horizon'=>'blue|blue|solid|clear','menu_vertical'=>'white|blue|none|none','frame_tittle'=>'blue|blue|solid|rectangle',];
        $theme_set['clear-gray']=['menu_horizon'=>'gray|gray|solid|clear','menu_vertical'=>'white|gray|none|none','frame_tittle'=>'gray|gray|solid|rectangle',];
        $theme_set['clear-green']=['menu_horizon'=>'green|green|solid|clear','menu_vertical'=>'white|green|none|none','frame_tittle'=>'green|green|solid|rectangle',];
        $theme_set['clear-kikyou']=['menu_horizon'=>'kikyou|kikyou|solid|clear','menu_vertical'=>'white|kikyou|none|none','frame_tittle'=>'kikyou|kikyou|solid|rectangle',];
        $theme_set['clear-koke']=['menu_horizon'=>'koke|koke|solid|clear','menu_vertical'=>'white|koke|none|none','frame_tittle'=>'koke|koke|solid|rectangle',];
        $theme_set['clear-lime']=['menu_horizon'=>'lime|lime|solid|clear','menu_vertical'=>'white|lime|none|none','frame_tittle'=>'lime|lime|solid|rectangle',];
        $theme_set['clear-orange']=['menu_horizon'=>'orange|orange|solid|clear','menu_vertical'=>'white|orange|none|none','frame_tittle'=>'orange|orange|solid|rectangle',];
        $theme_set['clear-pink']=['menu_horizon'=>'pink|pink|solid|clear','menu_vertical'=>'white|pink|none|none','frame_tittle'=>'pink|pink|solid|rectangle',];
        $theme_set['clear-red']=['menu_horizon'=>'red|red|solid|clear','menu_vertical'=>'white|red|none|none','frame_tittle'=>'red|red|solid|rectangle',];
        $theme_set['clear-sakura']=['menu_horizon'=>'sakura|sakura|solid|clear','menu_vertical'=>'white|sakura|none|none','frame_tittle'=>'sakura|sakura|solid|rectangle',];
        $theme_set['clear-yamabuki']=['menu_horizon'=>'yamabuki|yamabuki|solid|clear','menu_vertical'=>'white|yamabuki|none|none','frame_tittle'=>'yamabuki|yamabuki|solid|rectangle',];
        /* craft */
        $theme_set['craft-beige']=['menu_horizon'=>'darkbrown|beige|solid|craft','menu_vertical'=>'darkbrown|beige|none|craft','frame_tittle'=>'darkbrown|beige|solid|craft',];
        $theme_set['craft-blue']=['menu_horizon'=>'blue|blue|solid|craft','menu_vertical'=>'blue|blue|none|craft','frame_tittle'=>'blue|blue|solid|craft',];
        $theme_set['craft-deepblue']=['menu_horizon'=>'white|deepblue|solid|craft','menu_vertical'=>'deepblue|deepblue|none|craft','frame_tittle'=>'deepblue|deepblue|solid|craft',];
        $theme_set['craft-deepgreen']=['menu_horizon'=>'deepgreen|deepgreen|solid|craft','menu_vertical'=>'deepgreen|deepgreen|none|craft','frame_tittle'=>'deepgreen|deepgreen|solid|craft',];
        $theme_set['craft-deepred']=['menu_horizon'=>'deepred|deepred|solid|craft','menu_vertical'=>'deepred|deepred|none|craft','frame_tittle'=>'deepred|deepred|solid|craft',];
        $theme_set['craft-gray']=['menu_horizon'=>'gray|gray|solid|craft','menu_vertical'=>'gray|gray|none|craft','frame_tittle'=>'gray|gray|solid|craft',];
        $theme_set['craft-green']=['menu_horizon'=>'green|green|solid|craft','menu_vertical'=>'green|green|none|craft','frame_tittle'=>'green|green|solid|craft',];
        $theme_set['craft-pink']=['menu_horizon'=>'pink|pink|solid|craft','menu_vertical'=>'pink|pink|none|craft','frame_tittle'=>'pink|pink|solid|craft',];
        $theme_set['craft-purple']=['menu_horizon'=>'purple|purple|solid|craft','menu_vertical'=>'purple|purple|none|craft','frame_tittle'=>'purple|purple|solid|craft',];
        /* ledge */
        $theme_set['ledge-azuki']=['menu_horizon'=>'white|azuki|solid|ledge','menu_vertical'=>'azuki|azuki|solid|circle','frame_tittle'=>'azuki|azuki|solid|rectangle',];
        $theme_set['ledge-blue']=['menu_horizon'=>'white|blue|solid|ledge','menu_vertical'=>'blue|blue|solid|circle','frame_tittle'=>'blue|blue|solid|rectangle',];
        $theme_set['ledge-gray']=['menu_horizon'=>'white|gray|solid|ledge','menu_vertical'=>'gray|gray|solid|circle','frame_tittle'=>'gray|gray|solid|rectangle',];
        $theme_set['ledge-green']=['menu_horizon'=>'white|green|solid|ledge','menu_vertical'=>'green|green|solid|circle','frame_tittle'=>'green|green|solid|rectangle',];
        $theme_set['ledge-kikyou']=['menu_horizon'=>'white|kikyou|solid|ledge','menu_vertical'=>'kikyou|kikyou|solid|circle','frame_tittle'=>'kikyou|kikyou|solid|rectangle',];
        $theme_set['ledge-koke']=['menu_horizon'=>'white|koke|solid|ledge','menu_vertical'=>'koke|koke|solid|circle','frame_tittle'=>'koke|koke|solid|rectangle',];
        $theme_set['ledge-lime']=['menu_horizon'=>'white|lime|solid|ledge','menu_vertical'=>'lime|lime|solid|circle','frame_tittle'=>'lime|lime|solid|rectangle',];
        $theme_set['ledge-orange']=['menu_horizon'=>'white|orange|solid|ledge','menu_vertical'=>'orange|orange|solid|circle','frame_tittle'=>'orange|orange|solid|rectangle',];
        $theme_set['ledge-pink']=['menu_horizon'=>'white|pink|solid|ledge','menu_vertical'=>'pink|pink|solid|circle','frame_tittle'=>'pink|pink|solid|rectangle',];
        $theme_set['ledge-red']=['menu_horizon'=>'white|red|solid|ledge','menu_vertical'=>'red|red|solid|circle','frame_tittle'=>'red|red|solid|rectangle',];
        $theme_set['ledge-sakura']=['menu_horizon'=>'sakura|sakura|solid|ledge','menu_vertical'=>'sakura|sakura|solid|circle','frame_tittle'=>'sakura|sakura|solid|rectangle',];
        $theme_set['ledge-yamabuki']=['menu_horizon'=>'white|yamabuki|solid|ledge','menu_vertical'=>'yamabuki|yamabuki|solid|circle','frame_tittle'=>'yamabuki|yamabuki|solid|rectangle',];
        /* shiny */
        $theme_set['shiny-purple']=['menu_horizon'=>'white|purple|none|shiny','menu_vertical'=>'white|purple|none|shiny','frame_tittle'=>'white|purple|solid|shiny',];
        $theme_set['shiny-blue']=['menu_horizon'=>'white|blue|none|shiny','menu_vertical'=>'white|blue|none|shiny','frame_tittle'=>'white|blue|solid|shiny',];
        $theme_set['shiny-deepblue']=['menu_horizon'=>'white|deepblue|none|shiny','menu_vertical'=>'white|deepblue|none|shiny','frame_tittle'=>'white|deepblue|solid|shiny',];
        $theme_set['shiny-deepgreen']=['menu_horizon'=>'white|deepgreen|none|shiny','menu_vertical'=>'white|deepgreen|none|shiny','frame_tittle'=>'white|deepgreen|solid|shiny',];
        $theme_set['shiny-deepred']=['menu_horizon'=>'white|deepred|none|shiny','menu_vertical'=>'white|deepred|none|shiny','frame_tittle'=>'white|deepred|solid|shiny',];
        $theme_set['shiny-gray']=['menu_horizon'=>'white|gray|none|shiny','menu_vertical'=>'white|gray|none|shiny','frame_tittle'=>'white|gray|solid|shiny',];
        $theme_set['shiny-green']=['menu_horizon'=>'white|green|none|shiny','menu_vertical'=>'white|green|none|shiny','frame_tittle'=>'white|green|solid|shiny',];
        /* washed */
        $theme_set['washed-azuki']=['menu_horizon'=>'azuki|azuki|solid|washed','menu_vertical'=>'white|azuki|none|none','frame_tittle'=>'azuki|azuki|solid|rectangle',];
        $theme_set['washed-blue']=['menu_horizon'=>'blue|blue|solid|washed','menu_vertical'=>'white|blue|none|none','frame_tittle'=>'blue|blue|solid|rectangle',];
        $theme_set['washed-gray']=['menu_horizon'=>'gray|gray|solid|washed','menu_vertical'=>'white|gray|none|none','frame_tittle'=>'gray|gray|solid|rectangle',];
        $theme_set['washed-green']=['menu_horizon'=>'green|green|solid|washed','menu_vertical'=>'white|green|none|none','frame_tittle'=>'green|green|solid|rectangle',];
        $theme_set['washed-kikyou']=['menu_horizon'=>'kikyou|kikyou|solid|washed','menu_vertical'=>'white|kikyou|none|none','frame_tittle'=>'kikyou|kikyou|solid|rectangle',];
        $theme_set['washed-koke']=['menu_horizon'=>'koke|koke|solid|washed','menu_vertical'=>'white|koke|none|none','frame_tittle'=>'koke|koke|solid|rectangle',];
        $theme_set['washed-lime']=['menu_horizon'=>'lime|lime|solid|washed','menu_vertical'=>'white|lime|none|none','frame_tittle'=>'lime|lime|solid|rectangle',];
        $theme_set['washed-orange']=['menu_horizon'=>'orange|orange|solid|washed','menu_vertical'=>'white|orange|none|none','frame_tittle'=>'orange|orange|solid|rectangle',];
        $theme_set['washed-pink']=['menu_horizon'=>'pink|pink|solid|washed','menu_vertical'=>'white|pink|none|none','frame_tittle'=>'pink|pink|solid|rectangle',];
        $theme_set['washed-red']=['menu_horizon'=>'red|red|solid|washed','menu_vertical'=>'white|red|none|none','frame_tittle'=>'red|red|solid|rectangle',];
        $theme_set['washed-sakura']=['menu_horizon'=>'sakura|sakura|solid|washed','menu_vertical'=>'white|sakura|none|none','frame_tittle'=>'sakura|sakura|solid|rectangle',];
        $theme_set['washed-yamabuki']=['menu_horizon'=>'yamabuki|yamabuki|solid|washed','menu_vertical'=>'white|yamabuki|none|none','frame_tittle'=>'yamabuki|yamabuki|solid|rectangle',];
        /* stitch */
        $theme_set['stitch-sakura']=['menu_horizon'=>'white|sakura|dashed|stitch','menu_vertical'=>'sakura|sakura|dashed|stitch','frame_tittle'=>'sakura|pink|dashed|stitch',];
        $theme_set['stitch-pink']=['menu_horizon'=>'white|pink|dashed|stitch','menu_vertical'=>'pink|pink|dashed|stitch','frame_tittle'=>'pink|pink|dashed|stitch',];
        $theme_set['stitch-blue']=['menu_horizon'=>'white|blue|dashed|stitch','menu_vertical'=>'blue|blue|dashed|stitch','frame_tittle'=>'blue|blue|dashed|stitch',];
        $theme_set['stitch-green']=['menu_horizon'=>'white|green|dashed|stitch','menu_vertical'=>'green|green|dashed|stitch','frame_tittle'=>'green|green|dashed|stitch',];
        $theme_set['stitch-tan']=['menu_horizon'=>'white|tan|dashed|stitch','menu_vertical'=>'tan|orange|dashed|stitch','frame_tittle'=>'tan|orange|dashed|stitch',];
        $theme_set['stitch-mix']=['menu_horizon'=>'white|skyblue|dashed|stitch','menu_vertical'=>'tan|orange|dashed|stitch','frame_tittle'=>'gray|green|dashed|stitch',];

        $theme_css = "";
        if ($request->has('theme_css')) {
            $theme_css = $request->theme_css;
        }

        $colors = self::COLORLIST;
        $borders = self::BORDERLIST;
        $fontfamilys = self::FONTFAMILYLIST;
        return view('plugins.manage.theme.theme_generate', [
            "function"    => __FUNCTION__,
            "plugin_name" => "theme",
            "theme_set"   => $theme_set,
            "theme_css"   => $theme_css,
            "colors"      => $colors,
            "borders"     => $borders,
            "fontfamilys" => $fontfamilys,
            "dirs"        => $dirs,
            "errors"      => $errors,
        ]);
    }

    /**
     *  カスタムテーマ生成
     */
    public function generate($request, $id)
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
            return $this->generateIndex($request, $id, $validator->errors());
        }
/* TODO 上書きさせるか否か決める
        // ディレクトリの存在チェック
        if (File::isDirectory(public_path() . '/themes/Users/' . $request->dir_name)) {
            $validator->errors()->add('dir_name', 'このディレクトリは存在します。');
            return $this->generateIndex($request, $id, $validator->errors());
        }
*/

        // 確認ボタンの場合は入力画面に戻る。
        $themes_css = $this->getSelectStyle($request);
        if ($request->has('confirm')) {
            $request['theme_css'] = $themes_css;
            return $this->generateIndex($request, $id);
        }

        // ディレクトリとテーマ初期ファイルの生成
        $result = File::makeDirectory(public_path() . '/themes/Users/' . basename($request->dir_name), 0775);

        // themes.ini ファイルの作成
        $themes_ini = '[base]' . "\n" . 'theme_name = ' . $request->theme_name;
        $result = File::put(public_path() . '/themes/Users/' . basename($request->dir_name) . '/themes.ini', $themes_ini);

        // themes.css ファイルの作成
        $result = File::put(public_path() . '/themes/Users/' . basename($request->dir_name) . '/themes.css', $themes_css);

        // themes.js ファイルの作成
        $themes_script = $this->getThemesScript($request);
        $result = File::put(public_path() . '/themes/Users/' . basename($request->dir_name) . '/themes.js', $themes_script);

        return redirect("/manage/theme");
    }
    /* テーマで利用する初期スクリプトを返却する */
    private function getThemesScript($request)
    {
        $script = <<<EOM
/* ページ上部へ戻る */
$(function(){
    $('#ccFooterArea').prepend('<p id="page-top"><a href="#"><i class="fas fa-arrow-up"></i></a></p>');
    var topBtn = $('#page-top');
    topBtn.hide();
    $(window).scroll(function () {
        if ($(this).scrollTop() > 300) {
            topBtn.fadeIn();
        } else {
            topBtn.fadeOut();
        }
    });
    topBtn.click(function () {
        $('body,html').animate({
            scrollTop: 0
        }, 500);
        return false;
    });
});

EOM;

        return $script;
    }
    /* カスタムテーマ生成画面で選択された値を取得しCSS形式で返却する */
    private function getSelectStyle($request)
    {
        $paramkeys = [
            'menu_horizon',
            'menu_vertical',
            'frame_tittle',
        ];
        // 以下のキー以外は全部取得する
        $attributes = $request->except(["_token","dir_name","theme_name"]);
        // 各種設定項目ごとに分割する
        $arr_params = [];
        foreach ($attributes as $selectkey => $val) {
            foreach ($paramkeys as $paramkey) {
                if (false !== strpos($selectkey, $paramkey)) {
                    $style = str_replace($paramkey.'_', '', $selectkey);
                    $style = str_replace('_', '-', $style);
                    $arr_params[$paramkey][$style] = $val;
                }
            }
        }

        $css_contents = "";
        if ($request->has('font_family')) {
            $css_contents .= $this->getBodyFontFamilyStyle($attributes['font_family']);
        }
        foreach ($arr_params as $target => $styles) {
            switch ($target) {
                case 'menu_horizon':
                    $css_contents .= $this->getMenuHorizonStyle($styles);
                    break;
                case 'menu_vertical':
                    $css_contents .= $this->getMenuVerticalnStyle($styles);
                    break;
                case 'frame_tittle':
                    $css_contents .= $this->getFrameTittleStyle($styles);
                    break;
            }
        }

        return $css_contents;
    }
    /* 選択された値を元にCSSを返却する menu_horizon*/
    private function getMenuHorizonStyle($styles)
    {

        $css_contents = <<<EOM
/* 横型メニューのCSS */

EOM;

        $gnav_font_color = self::COLORLIST[$styles['color']];
        $gnav_bk_color = ($styles['background'] != 'none') ? self::COLORLIST[$styles['background']] : false;
        if ($gnav_font_color && $gnav_bk_color) {
            $css_contents = <<<EOM
/* globalナビはメニューの色合いと合わせる */
.bg-dark {
    background-color: $gnav_bk_color !important;
    padding: 0;
}
.navbar-dark .navbar-brand {
    font-size : 1rem;
    padding-left: 0.5rem;
}
.navbar-dark .navbar-nav .nav-link {
    font-size : 0.8rem;
}
/* 横型メニューのCSS */

EOM;
        }

        foreach ($styles as $style => $val) {
            
            $tmp_style = '';
            switch ($style) {
                case 'color':
                    $code = self::COLORLIST[$val];
                    $tmp_style = <<<EOM
/* 横型メニュー color */
.plugin-menus .nav-tabs li.nav-item > a {
    color : $code;
}
.plugin-menus .nav-tabs li.nav-item > a.active {
    border-color : transparent;
}

EOM;
                    break;
                case 'background':
                    if ($val == 'none') {
                        break;
                    }
                    $code = self::COLORLIST[$val];
                    $tmp_style = <<<EOM
/* 横型メニュー background */
.plugin-menus .nav-tabs li.nav-item > a {
    background : $code;
}

EOM;
                    break;
                case 'border':
                    $selected_color = self::COLORLIST[$styles['color']];
                    $tmp_style = <<<EOM
/* 横型メニュー border */
.plugin-menus .nav-tabs li.nav-item {
    border-top : $val 2px $selected_color;
    border-bottom : $val 2px $selected_color;
    border-left : $val 2px $selected_color;
}
.plugin-menus .nav-tabs li.nav-item:last-child {
    border-right : $val 2px $selected_color;
}
.plugin-menus .nav-tabs .nav-link {
    border-radius : 0;
}

EOM;
                    break;
                case 'background-image':
                    if ($val == 'none') {// 指定無しは返却
                        break;
                    }
                    if ($styles['background'] == 'none') {
                        break;
                    }
                    $bk_param = $this->getHorizonBkimageParam($val, $styles['background']);
                    $tmp_style = <<<EOM
/* 横型メニュー background negative */
.plugin-menus .nav-tabs li.nav-item > a {
    background : transparent;
    padding: 5px 0 0 0;
    display: block;
    height: 100%;
}
.plugin-menus .nav-tabs .nav-link.active {
    height: 100%;
}
.plugin-menus .nav-tabs li.nav-item > a.active ,
.plugin-menus .nav-tabs li.nav-item > a:hover {
    background-color: #f5f8fa50;
}

/* 横型メニュー background-image */
.plugin-menus .nav-tabs {

EOM;
                    // 背景画像の設定値を追記
                    // ただし'washed'の場合はliタグに背景表示するので除く
                    if ($val !== 'washed') {
                        foreach ($bk_param as $style => $style_val) {
                            $tmp_style .= <<<EOM
        $style : $style_val;

EOM;
                        }
                    }
                    $tmp_style .= <<<EOM
}

EOM;
                    /* 背景画像ごとの独自CSS */
                    if ($val == 'clear') {
                        $tmp_style .= <<<EOM
/* 横型メニュー $val */
.plugin-menus .nav-tabs li.nav-item {
    border-top : 0;
    border-bottom : 0;
    height : 38px;
}
.plugin-menus .nav-tabs li.nav-item:first-child {
    border-left : 0;
}
.plugin-menus .nav-tabs li.nav-item:last-child {
    border-right : 0;
}

EOM;
                    }
                    if ($val == 'craft') {
                        $tmp_style .= <<<EOM
/* 横型メニュー $val */
.plugin-menus .nav-tabs li.nav-item {
    border-top : 0;
    border-bottom : 0;
}
.plugin-menus .nav-tabs li.nav-item:first-child {
    border-left : 0;
}
.plugin-menus .nav-tabs li.nav-item:last-child {
    border-right : 0;
}
.plugin-menus .nav-tabs li.nav-item > a:hover,
.plugin-menus .nav-tabs li.nav-item > a.active {
    border-radius : 10px;
}
EOM;
                    }
                    if ($val == 'ledge') {
                        $tmp_style .= <<<EOM
/* 横型メニュー $val */
.plugin-menus .nav-tabs li.nav-item {
    border-top : 0;
    border-bottom : 0;
    height : 37px;
}
.plugin-menus .nav-tabs li.nav-item:first-child {
    border-left : 0;
}
.plugin-menus .nav-tabs li.nav-item:last-child {
    border-right : 0;
}
.plugin-menus .nav-tabs li.nav-item > a:hover,
.plugin-menus .nav-tabs li.nav-item > a.active {
    font-weight: bold;
    background-color : transparent;
}
EOM;
                    }
                    if ($val == 'shiny') {
                        $color = self::COLORLIST[$styles['background']];
                        $tmp_style .= <<<EOM
/* 横型メニュー $val */
.plugin-menus .nav-tabs li.nav-item {
    border-top : 0;
    border-bottom : 0;
    height : 37px;
}
.plugin-menus .nav-tabs li.nav-item:first-child {
    border-left : 0;
}
.plugin-menus .nav-tabs li.nav-item:last-child {
    border-right : 0;
}
.plugin-menus .nav-tabs li.nav-item > a {
    padding: 7px 0 0 0;
}
.plugin-menus .nav-tabs li.nav-item > a:hover,
.plugin-menus .nav-tabs li.nav-item > a.active {
    background-color: #ffffff30;
}
EOM;

                    }
                    if ($val == 'washed') {
                        $background_image = $bk_param['background-image'];
                        $background_image_hover = str_replace('_on.png', '.png', $background_image);
                        $background_repeat = $bk_param['background-repeat'];
                        $background_position = $bk_param['background-position'];
                        $background_color = $bk_param['background-color'];
                        $tmp_style .= <<<EOM
/* 横型メニュー $val */
.plugin-menus .nav-tabs li.nav-item {
    border-top : 0;
    border-bottom : 0;
    height : 43px;
    background-image : $background_image;
    background-repeat : $background_repeat;
    background-position : $background_position;
    background-color : $background_color;
}
.plugin-menus .nav-tabs li.nav-item:first-child {
    border-left : 0;
}
.plugin-menus .nav-tabs li.nav-item:last-child {
    border-right : 0;
}
.plugin-menus .nav-tabs li.nav-item > a:hover,
.plugin-menus .nav-tabs li.nav-item > a.active {
    background-image : $background_image_hover;
    background-repeat : $background_repeat;
    background-position : $background_position;
    background-color : $background_color;
}

EOM;
                    }
                    if ($val == 'stitch') {
                        $background = $styles['background'];
                        if ($background == 'none') {
                            break;
                        }
                        $background_color = self::COLORLIST[$background];
                        $background_color_hover = $background_color. '50';
                        $border = $styles['border'];
                        $selected_color = self::COLORLIST[$styles['color']];
                        $tmp_style .= <<<EOM
/* 横型メニュー $val */
.plugin-menus .nav-tabs {
    border-bottom : 0;
}
.plugin-menus .nav-tabs li.nav-item {
    padding: 3px;
    display: table-cell;
    vertical-align: middle;
    height: 100%;
    background-color: $background_color;
    border-radius: 10px;
    /* border 設定があっても消す */
    border-top : 0;
    border-bottom : 0;
    border-left : 0;
    border-right : 0;
}
.plugin-menus .nav-tabs li.nav-item > a {
    border : $border 2px $selected_color;
    border-radius: 10px;
    padding: 10px;
    margin: 1px;
    font-weight : bold;
}
.plugin-menus .nav-tabs .nav-link.active {
    color : $selected_color;
}
.plugin-menus .nav-tabs li.nav-item > a.active ,
.plugin-menus .nav-tabs li.nav-item > a:hover {
    border : $border 2px $selected_color;
    background-color: #ffffff80;
}
/* stitchの場合は!important で打ち消す */
@media (min-width: 768px) {
    .plugin-menus .nav-tabs {
        display : table !important;
    }
}

EOM;
                    }

                    if ($val == 'underline') {
                        $background = $styles['background'];
                        if ($background == 'none') {
                            break;
                        }
                        $background_color = self::COLORLIST[$background];
                        $tmp_style .= <<<EOM
/* 横型メニュー $val */
.nav-tabs {
    border : 0;
}
.nav-tabs .nav-item {
    margin-bottom: 0;
}
.plugin-menus .nav-tabs li.nav-item {
    border-top : 0;
    border-right : 0;
    border-left : 0;
}
.plugin-menus .nav-tabs li.nav-item:last-child {
    border-right : 0;
}

.plugin-menus .nav-tabs li.nav-item > a.active ,
.plugin-menus .nav-tabs li.nav-item > a:hover {
    background-color: unset;
    font-weight: bold;
    border : 0;
    color : $background_color;
}
.plugin-menus .nav-tabs li.nav-item > a {
    position: relative;
    padding: 10px 0 10px 0px;
}
.plugin-menus .nav-tabs li.nav-item > a.active:after {
    position: absolute;
    vertical-align: bottom;
    bottom: -2px;
    left: 45%;
    width: 0;
    height: 0;
    content: '';
    border-width: 0 10px 10px 10px;
    border-style: solid;
    border-color: transparent transparent $background_color transparent;
}

EOM;
                    }
                    break;
                default:
                    $tmp_style = '';
                    break;
            }
            $css_contents .= <<<EOM
$tmp_style
EOM;
        }





        /* ページ上部へ戻るボタン */
        if ($gnav_bk_color) {
            $css_contents .= <<<EOM
/* ページ上部へ戻るボタン page-top */
#page-top {
    position: fixed;
    bottom: 20px;
    right: 20px;
    font-size: 80%;
    z-index: 9999;
}
#page-top a {
    display: block;
    background: $gnav_bk_color;
    color: #fff;
    width: 100px;
    padding: 5px 0;
    text-align: center;
    text-decoration: none;
    font-size:3rem;
    opacity:1;
} 
#page-top a:hover {
    background: $gnav_bk_color;
    text-decoration: none;
    opacity:0.8;
}

EOM;
        }

        return $css_contents;
    }
    private function getHorizonBkimageParam($val, $bkcolor)
    {
        $background_image_filepath = url('').'/images/core/theme/menu/horizon/' .$val;
        $background_code = self::COLORLIST[$bkcolor];
        $bk_param = [];
        switch ($val) {
            case 'none':
                $bk_param = [];
                break;
            case 'clear':
                $bk_param = [
                    'background-image'=> 'url(' .$background_image_filepath .'/' .$bkcolor.'.gif)',
                    'background-repeat'=>'repeat-x',
                    'background-position'=>'top left',
                    'border-top' =>  'solid ' .$background_code .' 5px',
                    'background-color' => 'transparent',
                    'height' => '43px',
                ];
                break;
            case 'craft':
                $bk_param = [
                    'background-image' => 'url(' .$background_image_filepath .'/opacity.png)',
                    'background-repeat' => 'repeat-x',
                    'background-position' => 'center center',
                    'background-color' => $background_code,
                    'padding' => '5px 5px',
                    'border-radius' => '10px',
                ];
                break;
            case 'ledge':
                $bk_param = [
                    'background-image'=> 'url(' .$background_image_filepath .'/' .$bkcolor.'.gif)',
                    'background-repeat' => 'repeat-x',
                    'background-position' => 'bottom left',
                    'background-color' => 'transparent',
                ];
                break;
            case 'shiny':
                $bk_param = [
                    'background-image'=> 'url(' .$background_image_filepath .'/opacity.png)',
                    'background-repeat' => 'repeat',
                    'background-position' => 'center center',
                    'background-color' => $background_code,
                ];
                break;
            case 'stitch':
                $bk_param = [
                    //'background-color' => $background_code,
                    'border-collapse' => 'separate',
                    'border-spacing' => '8px',
                    'width' => '100%',
                ];
                break;
            case 'washed':
                // washedの場合はliタグに背景表示する
                $bk_param = [
                    'background-image' => 'url(' .$background_image_filepath .'/' .$bkcolor.'_on.png)',
                    'background-repeat' => 'no-repeat',
                    'background-position' => '-3px center',
                    'background-color' => 'transparent',
                ];
                break;
            case 'underline':
                $bk_param = [
                    'background' => 'transparent',
                ];
                break;
        }
        return $bk_param;
    }
    /* 選択された値を元にCSSを返却する menu_vertical*/
    private function getMenuVerticalnStyle($styles)
    {
        $css_contents = <<<EOM


/* 縦型（サイド）メニュー menu_vertical */

EOM;
        $color = $styles['color'];
        $color_code = self::COLORLIST[$color];
        $background_color = $styles['background'];
        if ($background_color !== 'none') {
            $background_color_code = self::COLORLIST[$background_color];
        }
        foreach ($styles as $style => $val) {
            $tmp_style = '';
            switch ($style) {
                case 'color':
                    $tmp_style = <<<EOM
/* 縦型（サイド）メニュー color */
.plugin-menus .list-group > a.list-group-item {
    color : $color_code;
}

EOM;
                    break;
                case 'background':
                    if ($val == 'none') {// 指定無しはそのまま返却
                        break;
                    }
                    $tmp_style = <<<EOM
/* 縦型（サイド）メニュー background */
.plugin-menus .list-group > a.list-group-item {
    color : $color_code;
    background : $background_color_code;
}
.plugin-menus .list-group > a.list-group-item.active,
.plugin-menus .list-group > a.list-group-item:hover {
    background-color : #55555550;
    z-index : 0;
    border-color : transparent;
    text-decoration: none;
}

EOM;
                    break;
                case 'border':
                    if ($val == 'none' || $val == 'hidden') {// 指定無しは返却
                        $tmp_style = <<<EOM
/* 縦型（サイド）メニュー border */
.plugin-menus .list-group > a.list-group-item {
    border : $val;
    border-radius : unset;
}

EOM;

                        
                        break;
                    }
                    if ($styles['background'] == 'none') {
                        break;
                    }
                    $border_val = $val ." 2px " .$color_code;
                    $tmp_style = <<<EOM
/* 縦型（サイド）メニュー border */
.plugin-menus .list-group > a.list-group-item {
    border-top : $border_val;
    border-left : $border_val;
    border-right : $border_val;
    border-radius : 0;
    text-decoration: none;
}
.plugin-menus .list-group > a.list-group-item:last-child {
    border-bottom : $border_val;
}

EOM;

                    break;
                case 'background-image':
                    if ($val == 'none') {// 指定無しはそのまま返却
                        break;
                    }
                    if ($styles['background'] == 'none') {
                        break;
                    }
    
                    $bk_param = $this->getVerticalBkimageParam($val, $background_color);
                    $tmp_style = <<<EOM
/* 縦型（サイド）メニュー background-image */
.plugin-menus .list-group > a.list-group-item {

EOM;
                    foreach ($bk_param as $style => $style_val) {
                        $tmp_style .= <<<EOM
$style : $style_val;

EOM;
                    }
                    // bk_paramの出力閉じ
                    $tmp_style .= <<<EOM
}

EOM;
                    /* bk-image指定時のborder共通設定 */
                    $border_selected = $styles['border'];
                    if ($border_selected == 'none' || $border_selected == 'hidden') {
                        $tmp_style .= <<<EOM
.plugin-menus .list-group > a.list-group-item,
.plugin-menus .list-group > a.list-group-item.active,
.plugin-menus .list-group > a.list-group-item:hover,
.plugin-menus .list-group > a.list-group-item.active:last-child,
.plugin-menus .list-group > a.list-group-item:hover:last-child {
    /* border */
    border : unset;
}

EOM;
                    } else {
                        $tmp_style .= <<<EOM
.plugin-menus .list-group > a.list-group-item.active,
.plugin-menus .list-group > a.list-group-item:hover,
.plugin-menus .list-group > a.list-group-item.active:last-child,
.plugin-menus .list-group > a.list-group-item:hover:last-child {
    /* border */
    border-color : $color_code;
}

EOM;
                    }
                    // 背景画像ごとの独自CSS
                    if ($val == 'circle') {
                        $tmp_style .= <<<EOM
.plugin-menus .list-group > a.list-group-item.active,
.plugin-menus .list-group > a.list-group-item:hover {
    background-color : transparent;
    font-weight : bold;
    color : $background_color_code;
}
.plugin-menus .list-group > a.list-group-item:before {
    position: absolute;
    top: calc(50% - 7px);
    left: 12px;
    top: 18px;
    width: 8px;
    height: 8px;
    content: '';
    border-radius: 50%;
    background: $background_color_code;
}

EOM;
                    }
                    if ($val == 'clear') {
                        $tmp_style .= <<<EOM
.plugin-menus .list-group > a.list-group-item.active,
.plugin-menus .list-group > a.list-group-item:hover {
    background-color : $background_color_code;
    background-image : unset;
    font-weight : bold;
    color : $color_code;
}

EOM;
                    }
                    if ($val == 'craft') {
                        $background_image = $bk_param['background-image'];
                        $background_image_hover = str_replace('.png', '_on.png', $background_image);
                        $tmp_style .= <<<EOM
.plugin-menus .list-group > a.list-group-item.active,
.plugin-menus .list-group > a.list-group-item:hover {
    background-image : $background_image_hover;
    background-color: $background_color_code;
}

EOM;
                    }
                    if ($val == 'shiny') {
                        $background_image = $bk_param['background-image'];
                        $background_image_hover = str_replace('.png', '_on.png', $background_image);
                        $tmp_style .= <<<EOM
.plugin-menus .list-group > a.list-group-item.active,
.plugin-menus .list-group > a.list-group-item:hover {
    background-image : $background_image_hover;
    background-color: $background_color_code;
    font-weight : bold;
}

EOM;
                    }
                    if ($val == 'stitch') {
                        $background_image = $bk_param['background-image'];
                        $background_image_hover = str_replace('.png', '_on.png', $background_image);
                        $tmp_style .= <<<EOM
.plugin-menus .list-group > a.list-group-item.active,
.plugin-menus .list-group > a.list-group-item:hover {
    background-image : $background_image_hover;
    background-color: $background_color_code;
}

EOM;
                    }
                    if ($val == 'underline') {
                        $tmp_style .= <<<EOM
/*  縦型（サイド）メニュー underline horizon */
.plugin-menus .list-group > a.list-group-item,
.plugin-menus .list-group > a.list-group-item.active,
.plugin-menus .list-group > a.list-group-item:hover {
    border-bottom : $color_code $border_selected 2px;
}

EOM;
                    }


                    break;
                default:
                    $tmp_style = '';
                    break;
            }
            $css_contents .= <<<EOM
$tmp_style
EOM;
        }
        return $css_contents;
    }

    private function getVerticalBkimageParam($val, $bkcolor)
    {
        $background_image_filepath = url('').'/images/core/theme/menu/vertical/' .$val;
        $background_code = self::COLORLIST[$bkcolor];
        $bk_param = [];
        switch ($val) {
            case 'none':
                $bk_param = [];
                break;
            case 'circle':
                $bk_param = [
                    'position' => 'relative',
                    'display' => 'inline-block',
                    'padding-left' => '30px',
                    'background-color' => 'transparent',
                ];
                break;
            case 'clear':
                $bk_param = [
                    'background-image' => 'url(' .$background_image_filepath .'/'. $bkcolor. '.gif)',
                    'background-size' => 'contain',
                    'background-repeat' => 'repeat',
                    'background-position' => 'left bottom',
                    'background-attachment' => 'scroll',
                ];
                break;
            case 'craft':
                $bk_param = [
                    'background-image' => 'url(' .$background_image_filepath .'/opacity.png)',
                    'background-repeat' => 'repeat',
                    'background-position' => 'center center',
                    'font-weight' => 'bold',

                ];
                break;
            case 'shiny':
                $bk_param = [
                    'background-image' => 'url(' .$background_image_filepath .'/opacity.png)',
                    'background-repeat' => 'repeat-x',
                    'background-position' => 'center center',
                    'background-color' => $background_code ,
                ];
                break;
            case 'stitch':
                $bk_param = [
                    'background-image' => 'url(' .$background_image_filepath .'/opacity.png)',
                    'background-repeat' =>'repeat',
                    'background-position' =>'center center',
                    'background-color' => $background_code ,
                ];
                break;
            case 'underline':
                $bk_param = [
                    'background' => 'transparent',
                    'border-left' => '0',
                    'border-top' => '0',
                    'border-right' => '0',
                ];
                break;
        }
        return $bk_param;
    }
    /* 選択された値を元にCSSを返却する frame_tittle*/
    private function getFrameTittleStyle($styles)
    {

        $css_contents = <<<EOM


/* フレームタイトル frame_tittle */

EOM;

        foreach ($styles as $style => $val) {
            $tmp_style = '';
            switch ($style) {
                case 'color':
                    $code = self::COLORLIST[$val];
                    if ($val == 'white' && $styles['background'] != 'none') {
                        $anchor_code = self::COLORLIST[$styles['background']];
                    } else {
                        $anchor_code = $code;
                    }
                    $anchor_code_80 = $anchor_code. '80';
                    $tmp_style .= <<<EOM
/************/
/* サイト全体設定(メインエリアのみ) site all anchor */
#ccMainArea a:hover {
    color: $anchor_code_80;
    text-decoration: underline;
}
#ccMainArea a {
    color: $anchor_code;
    text-decoration: underline;
}
/************/

/* フレームタイトル color */
.card-header.bg-default {
    color : $code;
}

EOM;
                    break;
                case 'background':
                    if ($val == 'none') {
                        break;
                    }
                    $background_code = self::COLORLIST[$val];
                    $tmp_style .= <<<EOM
/* フレームタイトル background */
.card-header.bg-default {
    background : $background_code;
}

EOM;
                    break;
                case 'border':
                    $color = $styles['color'];
                    $color_code = self::COLORLIST[$color];
                    $tmp_style .= <<<EOM
/* フレームタイトル border */
.card-header.bg-default {
    border-color : $color_code;
    border-bottom-style : $val;
    border-bottom-width : 2px;
}

EOM;

                    break;
                case 'background-image':
                    if ($val == 'none') {// 指定無しはそのまま返却
                        break;
                    }
                    if ($styles['background'] == 'none') {
                        break;
                    }
                    $bk_param = $this->getFrameTittleBkimageParam($val, $styles['background']);
                    $tmp_style = <<<EOM
/* フレームタイトル background-image */
/*******/
/* ブログの記事タイトルの大きさ調整*/
header h2 {
    font-size: 1.3rem;
}
/*******/
.card-header.bg-default {
    font-size: 1.1rem;
    padding-bottom: 10px;
EOM;

                    $color = $styles['color'];
                    $color_code = self::COLORLIST[$color];
                    $background = $styles['background'];
                    $background_color_code = self::COLORLIST[$background];
                    $border = $styles['border'];
                    // 背景画像の設定値を追記
                    foreach ($bk_param as $style => $style_val) {
                        $tmp_style .= <<<EOM
    $style : $style_val;

EOM;
                    }
                    $tmp_style .= <<<EOM
}
EOM;

                    // 背景画像ごとの独自CSS
                    if ($val == 'circle') {
                        $tmp_style .= <<<EOM
.card-header.bg-default:before {
    position: absolute;
    top: calc(50% - 7px);
    left: 12px;
    top: 18px;
    width: 8px;
    height: 8px;
    content: '';
    border-radius: 50%;
    background: $color_code;
}

EOM;
                    }
                    if ($val == 'clear') {
                        $tmp_style .= <<<EOM
.card {
    border : unset;
}

EOM;
                    }
                    if ($val == 'craft') {
                        $tmp_style .= <<<EOM
.card-header.bg-default:before {
    position: absolute;
    top: .25em;
    left: .5em;
    margin-right: 8px;
    content: '|';
    color: $color_code;
    font-size: 24px;
    font-weight: bold;
}

.card {
    border-color : $color_code;
}

EOM;
                    }
                    if ($val == 'stitch') {
                        $background_image_filepath = url('').'/images/core/theme/frame/stitch/kuina.png';
                        $tmp_style .= <<<EOM
/* フレームタイトル stitch border unset */
.card {
    border : unset;
}

.card-header.bg-default:before {
    position : absolute;
    /* 表示する画像に合わせて以下のtop,right,scaleは調整してください */
    top: -70px;
    right: -60px;
    content: url($background_image_filepath);
    transform: scale(0.25);
}

.card-header.bg-default .float-right {
    /* cardheader画像に合わせて調整 */
    margin-right: 40px;
}

EOM;
                    }
                    if ($val == 'rectangle') {
                        $tmp_style .= <<<EOM
.card {
    border : unset;
}

EOM;
                    }
                    if ($val == 'center') {
                        $tmp_style .= <<<EOM
.card {
    border : unset;
}
.card-header.bg-default:before,
.card-header.bg-default:after {
    position: absolute;
    top: calc(50% - 3px);
    width: 50px;
    height: 6px;
    content: '';
    border-top: $border 2px $color_code;
    border-bottom: $border 2px $color_code;
}
.card-header.bg-default:before {
    left: 0;
}
.card-header.bg-default:after {
    right: 0;
}

EOM;
                    }

                    if ($val == 'ribbon') {
                        $background_color_code_80 = $background_color_code. '80';
                        $tmp_style .= <<<EOM
.card {
    border : unset;
}
.card-header.bg-default:before,
.card-header.bg-default:after {
    position: absolute;
    content: '';
}
.card-header.bg-default:before {
    bottom: -10px;
    left: 0;
    width: 0;
    height: 0;
    border-top: 10px solid $background_color_code_80;
    border-left: 10px solid transparent;
}
.card-header.bg-default:after {
    right: 0;
    bottom: -10px;
    width: 0;
    height: 0;
    border-top: 10px solid $background_color_code_80;
    border-right: 10px solid transparent;
}

EOM;
                    }
                    if ($val == 'balloon') {
                        $tmp_style .= <<<EOM
.card {
    border : unset;
}
.card-header.bg-default:after {
    position: absolute;
    bottom: -9px;
    left: 1em;
    width: 0;
    height: 0;
    content: '';
    border-width: 10px 10px 0 10px;
    border-style: solid;
    border-color: $background_color_code transparent transparent transparent;
}

EOM;
                    }
                    if ($val == 'emphasis') {
                        $tmp_style .= <<<EOM
.card {
    border : unset;
}
.card-header.bg-default:first-letter {
    font-size: 2rem;
    color: $color_code;
}

EOM;
                    }


                    if ($val == 'underline') {
                        $tmp_style .= <<<EOM
.card {
    border : unset;
}
.card-header.bg-default {
    border-bottom: $background_color_code $border 2px;
    background-color: transparent;
}

EOM;
                    }
                    

                    break;
            }
            $css_contents .= <<<EOM
$tmp_style
EOM;
        }
        return $css_contents;
    }
    private function getFrameTittleBkimageParam($val, $bkcolor)
    {
        $background_image_filepath = url('').'/images/core/theme/frame/' .$val;
        $background_code = self::COLORLIST[$bkcolor];
        $bk_param = [];

        switch ($val) {
            case 'none':
                $bk_param = [];
                break;
            case 'circle':
                $bk_param = [
                    'position' => 'relative',
                    'display' => 'inline-block',
                    'padding-left' => '30px',
                    'background-color' => $background_code,
                ];
                break;
            case 'rectangle':
                $bk_param = [
                    'background-image' => 'url(' .$background_image_filepath . '/'. $bkcolor. '.gif)',
                    'background-size' => 'contain',
                    'background-position' => 'left center',
                    'border-radius' => 'unset',
                    'border-left' => 'solid ' .$background_code .' 15px',
                    'background-color' => 'unset',
                ];
                break;
            case 'craft':
                $bk_param = [
                    'background-image' => 'url(' .$background_image_filepath .'/opacity.png)',
                    'background-repeat' => 'repeat',
                    'background-position-x' => 'center',
                    'background-position-y' => 'center',
                    'background-color' => $background_code,
                    'padding-left' => '30px',
                ];
                break;
            case 'shiny':
                $bk_param = [
                    'background-image' => 'url(' .$background_image_filepath .'/opacity.png)',
                    'background-repeat' => 'no-repeat',
                    'background-position' => 'right bottom',
                    'background-color' => $background_code,
                    'background-size' => 'contain',
                ];
                break;
            case 'stitch':
                $bk_param = [
                    'background-image' => 'url(' .$background_image_filepath .'/' .$bkcolor .'.png)',
                    'background-repeat' => 'no-repeat',
                    'background-position-x' => '8px',
                    'background-position-y' => '10px',
                    'background-size' => '20px',
                    'background-color' => 'unset',
                    'padding-left' => '35px',
                    'font-weight' => 'bold',
                ];
                break;
            case 'center':
                $bk_param = [
                    'position' => 'relative',
                    'display' => 'inline-block',
                    'text-align' => 'center',
                ];
                break;
            case 'ribbon':
                $bk_param = [
                    'position' => 'relative',
                    'margin' => '0 -10px',
                    'background' => $background_code,
                ];
                break;
            case 'balloon':
                $bk_param = [
                    'position' => 'relative',
                    'border-radius' => 'unset',
                ];
                break;
            case 'emphasis':
                $bk_param = [
                ];
                break;
            case 'underline':
                $bk_param = [
                    'border-radius' => 'unset',
                    'border-top' => 0,
                    'border-right' => 0,
                    'border-left' => 0,
                ];
                break;
        }
        return $bk_param;
    }
    /* 選択された値を元にCSSを返却する font_family*/
    private function getBodyFontFamilyStyle($font_family)
    {
        $css_contents = <<<EOM
/* 共通設定 */
/* font_family */
body {
    font-family : $font_family;
}

EOM;
        return $css_contents;
    }
}
