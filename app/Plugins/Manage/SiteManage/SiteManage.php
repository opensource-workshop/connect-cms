<?php

namespace App\Plugins\Manage\SiteManage;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;

use DB;
use File;

use App\Models\Core\Configs;
use App\Models\Common\Categories;
use App\Models\Common\Page;

use App\Plugins\Manage\ManagePluginBase;
use App\Enums\BaseLoginRedirectPage;

/**
 * サイト管理クラス
 *
 * @author 永原　篤 <nagahara@opensource-workshop.jp>
 * @copyright OpenSource-WorkShop Co.,Ltd. All Rights Reserved
 * @category サイト管理
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
        $role_ckeck_table["index"]            = array('admin_site');
        $role_ckeck_table["update"]           = array('admin_site');
        $role_ckeck_table["layout"]           = array('admin_site');
        $role_ckeck_table["saveLayout"]       = array('admin_site');
        $role_ckeck_table["categories"]       = array('admin_site');
        $role_ckeck_table["saveCategories"]   = array('admin_site');
        $role_ckeck_table["deleteCategories"] = array('admin_site');
        $role_ckeck_table["loginPermit"]      = array('admin_site');
        $role_ckeck_table["saveLoginPermit"]  = array('admin_site');
        $role_ckeck_table["languages"]        = array('admin_site');
        $role_ckeck_table["saveLanguages"]    = array('admin_site');
        $role_ckeck_table["meta"]             = array('admin_site');
        $role_ckeck_table["saveMeta"]         = array('admin_site');
        $role_ckeck_table["pageError"]        = array('admin_site');
        $role_ckeck_table["savePageError"]    = array('admin_site');
        $role_ckeck_table["analytics"]        = array('admin_site');
        $role_ckeck_table["saveAnalytics"]    = array('admin_site');
        $role_ckeck_table["favicon"]          = array('admin_site');
        $role_ckeck_table["saveFavicon"]      = array('admin_site');
        $role_ckeck_table["deleteFavicon"]    = array('admin_site');

        return $role_ckeck_table;
    }

    /**
     *  ページ初期表示
     *
     * @return view
     */
    public function index($request, $page_id = null)
    {
        // Config データの取得
        $configs = Configs::get();

        // // Config データの変換
        // $configs_array = array();
        // foreach ($configs as $config) {
        //     $configs_array[$config->name] = $config->value;
        // }

        // 設定済みの基本テーマ
        $base_theme_obj = $configs->where('name', 'base_theme')->first();
        $current_base_theme = '';
        if (!empty($base_theme_obj)) {
            $current_base_theme = $base_theme_obj->value;
        }

        // 設定済みの追加テーマ
        $current_additional_theme = $configs->where('name', 'additional_theme')->first() ? $configs->where('name', 'additional_theme')->first()->value : '';

        // テーマの取得
        $themes = $this->getThemes();

        // ページデータの取得(laravel-nestedset 使用)
        $return_obj = 'flat';
        $pages_select = Page::defaultOrderWithDepth($return_obj);

        // 管理画面プラグインの戻り値の返し方
        // view 関数の第一引数に画面ファイルのパス、第二引数に画面に渡したいデータを名前付き配列で渡し、その結果のHTML。
        return view('plugins.manage.site.site', [
            "function"           => __FUNCTION__,
            "plugin_name"        => "site",
            // "configs"            => $configs_array,
            "configs"            => $configs,
            "current_base_theme" => $current_base_theme,
            "current_additional_theme" => $current_additional_theme,
            "themes"             => $themes,
            "pages_select" => $pages_select,
        ]);
    }

    /**
     * 更新
     */
    public function update($request, $page_id = null)
    {
        // httpメソッド確認
        if (!$request->isMethod('post')) {
            abort(403, '権限がありません。');
        }

        $validator_values = [];
        $validator_attributes['base_login_redirect_select_page'] = 'ログイン後に移動する指定ページ';

        $messages = [
            'base_login_redirect_select_page.required' => 'ログイン後に移動するページを指定したページにする場合、:attribute を選択してください。',
        ];

        // 項目のエラーチェック
        $validator = Validator::make($request->all(), $validator_values, $messages);
        $validator->setAttributeNames($validator_attributes);

        $validator->sometimes("base_login_redirect_select_page", 'required', function ($input) {
            // ログイン後に移動するページ が「指定したページ」なら、上記の ログイン後に移動する指定ページ 必須
            return $input->base_login_redirect_previous_page == BaseLoginRedirectPage::specified_page;
        });

        if ($validator->fails()) {
            // エラーと共に編集画面を呼び出す
            return redirect()->back()->withErrors($validator)->withInput();
        }

        // サイト名
        $configs = Configs::updateOrCreate(
            ['name'     => 'base_site_name'],
            ['category' => 'general',
             'value'    => $request->base_site_name]
        );

        // 基本テーマ
        $configs = Configs::updateOrCreate(
            ['name'     => 'base_theme'],
            ['category' => 'general',
             'value'    => $request->base_theme]
        );

        // 追加テーマ
        $configs = Configs::updateOrCreate(
            ['name'     => 'additional_theme'],
            ['category' => 'general',
             'value'    => $request->additional_theme]
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

        // 画面の基本のヘッダー文字色
        $configs = Configs::updateOrCreate(
            ['name'     => 'base_header_font_color_class'],
            [
                'category' => 'general',
                'value'    => $request->base_header_font_color_class
            ]
        );

        // ヘッダーバー任意クラス
        $configs = Configs::updateOrCreate(
            ['name'     => 'base_header_optional_class'],
            [
                'category' => 'general',
                'value'    => $request->base_header_optional_class
            ]
        );

        // bodyタグのclass属性
        $configs = Configs::updateOrCreate(
            ['name'     => 'body_optional_class'],
            ['category' => 'general',
             'value'    => $request->body_optional_class]
        );

        // センターエリア要素のclass属性
        $configs = Configs::updateOrCreate(
            ['name'     => 'center_area_optional_class'],
            ['category' => 'general',
             'value'    => $request->center_area_optional_class]
        );

        // フッターエリア要素のclass属性
        $configs = Configs::updateOrCreate(
            ['name'     => 'footer_area_optional_class'],
            ['category' => 'general',
             'value'    => $request->footer_area_optional_class]
        );

        // 基本のヘッダー固定設定
        $configs = Configs::updateOrCreate(
            ['name'     => 'base_header_fix'],
            ['category' => 'general',
             'value'    => (isset($request->base_header_fix) ? $request->base_header_fix : 0)]
        );

        // ヘッダーの表示
        $configs = Configs::updateOrCreate(
            ['name'     => 'base_header_hidden'],
            ['category' => 'general',
             'value'    => $request->base_header_hidden]
        );

        // ログインリンクの表示
        $configs = Configs::updateOrCreate(
            ['name'     => 'base_header_login_link'],
            ['category' => 'general',
             'value'    => $request->base_header_login_link]
        );

        // パスワードリセットの使用
        $configs = Configs::updateOrCreate(
            ['name'     => 'base_login_password_reset'],
            ['category' => 'general',
             'value'    => $request->base_login_password_reset]
        );

        // ログイン後に移動するページ 設定
        $configs = Configs::updateOrCreate(
            ['name' => 'base_login_redirect_previous_page'],
            [
                'category' => 'general',
                'value'    => $request->base_login_redirect_previous_page
            ]
        );

        // ログイン後に移動する指定ページ 設定
        $configs = Configs::updateOrCreate(
            ['name' => 'base_login_redirect_select_page'],
            [
                'category' => 'general',
                'value'    => $request->base_login_redirect_select_page
            ]
        );

        // マイページの使用
        $configs = Configs::updateOrCreate(
            ['name'     => 'use_mypage'],
            ['category' => 'general',
             'value'    => $request->use_mypage]
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

        // スマホメニューのフォーマット
        $configs = Configs::updateOrCreate(
            ['name'     => 'smartphone_menu_template'],
            ['category' => 'general',
             'value'    => (isset($request->smartphone_menu_template) ? $request->smartphone_menu_template : "")]
        );

        // ページ管理画面に戻る
        return redirect("/manage/site");
    }

    /**
     *  カテゴリ表示画面
     */
    public function categories($request, $id)
    {
        // セッション初期化などのLaravel 処理。
        // $request->flash();

        // カテゴリデータの取得
        $categories = Categories::orderBy('target', 'asc')
                ->orderBy('plugin_id', 'asc')
                ->orderBy('display_sequence', 'asc')
                ->get();

        return view('plugins.manage.site.categories', [
            "function"    => __FUNCTION__,
            "plugin_name" => "site",
            "id"          => $id,
            "categories"  => $categories,
        ]);
    }

    /**
     *  カテゴリ保存処理
     */
    public function saveCategories($request, $id)
    {
        /* エラーチェック
        ------------------------------------ */
        $rules = [];

        // エラーチェックの項目名
        $setAttributeNames = [];

        // 追加項目のどれかに値が入っていたら、行の他の項目も必須
        if (!empty($request->add_display_sequence) || !empty($request->add_classname)  || !empty($request->add_category) || !empty($request->add_color) || !empty($request->add_background_color)) {
            // 項目のエラーチェック
            $rules['add_display_sequence'] = ['required'];
            $rules['add_category'] = ['required'];
            $rules['add_color'] = ['required'];
            $rules['add_background_color'] = ['required'];

            $setAttributeNames['add_display_sequence'] = '追加行の表示順';
            $setAttributeNames['add_category'] = '追加行のカテゴリ';
            $setAttributeNames['add_color'] = '追加行の文字色';
            $setAttributeNames['add_background_color'] = '追加行の背景色';
        }

        // 既存項目のidに値が入っていたら、行の他の項目も必須
        if (!empty($request->categories_id)) {
            foreach ($request->categories_id as $category_id) {
                // 項目のエラーチェック
                $rules['display_sequence.'.$category_id] = ['required'];
                $rules['category.'.$category_id] = ['required'];
                $rules['color.'.$category_id] = ['required'];
                $rules['background_color.'.$category_id] = ['required'];

                $setAttributeNames['display_sequence.'.$category_id] = '表示順';
                $setAttributeNames['category.'.$category_id] = 'カテゴリ';
                $setAttributeNames['color.'.$category_id] = '文字色';
                $setAttributeNames['background_color.'.$category_id] = '背景色';
            }
        }

        // 項目のエラーチェック
        $validator = Validator::make($request->all(), $rules);
        $validator->setAttributeNames($setAttributeNames);

        if ($validator->fails()) {
            // return $this->categories($request, $id, $validator->errors());
            return redirect()->back()->withErrors($validator)->withInput();
        }

        // 追加項目アリ
        if (!empty($request->add_display_sequence)) {
            Categories::create([
                'display_sequence' => intval($request->add_display_sequence),
                'classname'        => $request->add_classname,
                'category'         => $request->add_category,
                'color'            => $request->add_color,
                'background_color' => $request->add_background_color
            ]);
        }

        // 既存項目アリ
        if (!empty($request->categories_id)) {
            foreach ($request->categories_id as $category_id) {
                // モデルオブジェクト取得
                $categories = Categories::where('id', $category_id)->first();

                // データのセット
                $categories->classname        = $request->classname[$category_id];
                $categories->color            = $request->color[$category_id];
                $categories->background_color = $request->background_color[$category_id];
                $categories->category         = $request->category[$category_id];
                $categories->display_sequence = $request->display_sequence[$category_id];

                // 保存
                $categories->save();
            }
        }

        // return $this->categories($request, $id, null, true);
        return redirect()->back();
    }

    /**
     *  カテゴリ削除処理
     */
    public function deleteCategories($request, $id)
    {
        // deleted_id, deleted_nameを自動セットするため、複数件削除する時はdestroy()を利用する。
        //
        // カテゴリ削除
        // Categories::where('id', $id)->delete();
        Categories::destroy($id);

        // return $this->categories($request, $id, null, true);
        return redirect()->back();
    }

    /**
     *  多言語設定　表示画面
     */
    public function languages($request, $id, $errors = null)
    {
        // セッション初期化などのLaravel 処理。
        $request->flash();

        // 多言語の使用有無取得
        $language_multi_on_record = Configs::where('name', 'language_multi_on')->first();
        $language_multi_on = ($language_multi_on_record) ? $language_multi_on_record->value : null;

        // 設定されている多言語のリスト取得
        $languages = Configs::where('category', 'language')->orderBy('additional1')->get();

        return view('plugins.manage.site.languages', [
            "function"          => __FUNCTION__,
            "plugin_name"       => "site",
            "id"                => $id,
            "language_multi_on" => $language_multi_on,
            "languages"         => $languages,
            "create_flag"       => true,
            "errors"            => $errors,
        ]);
    }

    /**
     *  言語設定の保存処理
     */
    public function saveLanguages($request, $id)
    {
        // httpメソッド確認
        if (!$request->isMethod('post')) {
            abort(403, '権限がありません。');
        }

        // サイト名
        $configs = Configs::updateOrCreate(
            ['name' => 'language_multi_on'],
            [
                'category' => 'general',
                'value' => $request->language_multi_on
            ]
        );

        // 追加項目のどれかに値が入っていたら、行の他の項目も必須
        if (!empty($request->add_language) || !empty($request->add_url)) {
            // 項目のエラーチェック
            $validator = Validator::make($request->all(), [
                'add_language' => ['required'],
                'add_url'      => ['required'],
            ]);
            $validator->setAttributeNames([
                'add_language' => '言語',
                'add_url'      => 'URL',
            ]);

            if ($validator->fails()) {
                return $this->languages($request, $id, $validator->errors());
            }
        }

        // 既存項目のidに値が入っていたら、行の他の項目も必須
        if (!empty($request->languages_id)) {
            foreach ($request->languages_id as $language_id) {
                // 項目のエラーチェック
                $validator = Validator::make($request->all(), [
                    'language.'.$language_id => ['required'],
                    'url.'.$language_id      => ['required'],
                ]);
                $validator->setAttributeNames([
                    'language.'.$language_id => '言語',
                    'url.'.$language_id      => 'URL',
                ]);

                if ($validator->fails()) {
                    return $this->languages($request, $id, $validator->errors());
                }
            }
        }

        // 追加項目アリ
        if (!empty($request->add_language)) {
            $new_configs = Configs::create([
                'name'        => 'language',
                'category'    => 'language',
                'value'       => $request->add_language,
                'additional1' => $request->add_url,
            ]);

            // name をユニークにするために更新(languageのname は特に使用していない)
            $new_configs->name = $new_configs->name . '_' . $new_configs->id;
            $new_configs->save();
        }

        // 既存項目アリ
        if (!empty($request->languages_id)) {
            foreach ($request->languages_id as $language_id) {
                // モデルオブジェクト取得
                $configs = Configs::where('id', $language_id)->first();

                // データのセット
                $configs->value        = $request->language[$language_id];
                $configs->additional1  = $request->url[$language_id];

                // 保存
                $configs->save();
            }
        }

        return $this->languages($request, $id, null);
    }

    /**
     *  レイアウト設定　表示画面
     */
    public function layout($request, $id, $errors = null)
    {
        // セッション初期化などのLaravel 処理。
        $request->flash();

        // 設定されている多言語のリスト取得
        $browser_widths = $this->getConfigs(null, 'browser_width');

        return view('plugins.manage.site.browserwidths', [
            "function"       => __FUNCTION__,
            "plugin_name"    => "site",
            "id"             => $id,
            "browser_widths" => $browser_widths,
        ]);
    }

    /**
     *  レイアウト設定　更新
     */
    public function saveLayout($request, $page_id = null, $errors = array())
    {
        // httpメソッド確認
        if (!$request->isMethod('post')) {
            abort(403, '権限がありません。');
        }

        // ブラウザ幅(ヘッダーエリア)
        $configs = Configs::updateOrCreate(
            ['name'     => 'browser_width_header'],
            ['category' => 'browser_width',
             'value'    => $request->browser_width_header]
        );

        // ブラウザ幅(センターエリア)
        $configs = Configs::updateOrCreate(
            ['name'     => 'browser_width_center'],
            ['category' => 'browser_width',
             'value'    => $request->browser_width_center]
        );

        // ブラウザ幅(フッターエリア)
        $configs = Configs::updateOrCreate(
            ['name'     => 'browser_width_footer'],
            ['category' => 'browser_width',
             'value'    => $request->browser_width_footer]
        );

        // ページ管理画面に戻る
        return redirect("/manage/site/layout");
    }

    /**
     *  meta設定　表示画面
     */
    public function meta($request, $id, $errors = null)
    {
        // セッション初期化などのLaravel 処理。
        $request->flash();

        // 設定されているmeta情報のリスト取得
        $meta = $this->getConfigs(null, 'meta');

        return view('plugins.manage.site.meta', [
            "function"    => __FUNCTION__,
            "plugin_name" => "site",
            "id"          => $id,
            "meta"        => $meta,
        ]);
    }

    /**
     *  meta設定　更新
     */
    public function saveMeta($request, $page_id = null, $errors = array())
    {
        // httpメソッド確認
        if (!$request->isMethod('post')) {
            abort(403, '権限がありません。');
        }

        // description
        $configs = Configs::updateOrCreate(
            ['name'     => 'description'],
            ['category' => 'meta',
             'value'    => $request->description]
        );

        // ページ管理画面に戻る
        return redirect("/manage/site/meta");
    }

    /**
     *  ページエラー設定　表示画面
     */
    public function pageError($request, $id, $errors = null)
    {
        // セッション初期化などのLaravel 処理。
        $request->flash();

        // 設定されているページエラー設定のリスト取得
        $page_errors = $this->getConfigs(null, 'page_error');

        return view('plugins.manage.site.page_error', [
            "function"    => __FUNCTION__,
            "plugin_name" => "site",
            "id"          => $id,
            "page_errors" => $page_errors,
        ]);
    }

    /**
     *  ページエラー設定　更新
     */
    public function savePageError($request, $page_id = null, $errors = array())
    {
        // httpメソッド確認
        if (!$request->isMethod('post')) {
            abort(403, '権限がありません。');
        }

        // 403
        $configs = Configs::updateOrCreate(
            ['name'     => 'page_permanent_link_403'],
            ['category' => 'page_error',
             'value'    => $request->page_permanent_link_403]
        );

        // 404
        $configs = Configs::updateOrCreate(
            ['name'     => 'page_permanent_link_404'],
            ['category' => 'page_error',
             'value'    => $request->page_permanent_link_404]
        );

        // ページ管理画面に戻る
        return redirect("/manage/site/pageError");
    }

    /**
     *  Analytics 設定　表示画面
     */
    public function analytics($request, $id, $errors = null)
    {
        // セッション初期化などのLaravel 処理。
        $request->flash();

        // 設定されているページエラー設定のリスト取得
        $analytics = $this->getConfigs('tracking_code');

        return view('plugins.manage.site.analytics', [
            "function"    => __FUNCTION__,
            "plugin_name" => "site",
            "id"          => $id,
            "analytics"   => $analytics,
        ]);
    }

    /**
     *  Analytics 設定　更新
     */
    public function saveAnalytics($request, $page_id = null, $errors = array())
    {
        // httpメソッド確認
        if (!$request->isMethod('post')) {
            abort(403, '権限がありません。');
        }

        // トラッキングコード
        $configs = Configs::updateOrCreate(
            ['name'     => 'tracking_code'],
            ['category' => 'analytics',
             'value'    => $request->tracking_code]
        );

        // ページ管理画面に戻る
        return redirect("/manage/site/analytics");
    }

    /**
     *  Favicon 設定　表示画面
     */
    public function favicon($request)
    {
        // ファビコン設定を取得
        $favicon = $this->getConfigs('favicon');

        return view('plugins.manage.site.favicon', [
            "function"    => __FUNCTION__,
            "plugin_name" => "site",
            "favicon"     => $favicon,
        ]);
    }

    /**
     *  Favicon 設定　更新
     */
    public function saveFavicon($request)
    {
        // httpメソッド確認
        if (!$request->isMethod('post')) {
            abort(403, '権限がありません。');
        }

        // ファイルがアップロードされた。
        if ($request->hasFile('favicon')) {
            // ファイルの基礎情報
            $client_original_name = $request->file('favicon')->getClientOriginalName();
            $mimetype             = $request->file('favicon')->getClientMimeType();
            $extension            = $request->file('favicon')->getClientOriginalExtension();

            // 拡張子チェック
            if (mb_strtolower($extension) != 'ico') {
                $validator = Validator::make($request->all(), []);
                $validator->errors()->add('favicon_error', '.ico 以外はアップロードできません。');
                return $this->favicon($request)->withErrors($validator);
            }

            // ファイルの保存
            $filename = 'favicon.ico';
            $request->file('favicon')->storeAs('tmp', $filename);

            // ファイルパス
            $src_file = storage_path() . '/app/tmp/' . $filename;
            $dst_dir  = public_path() . '/uploads/favicon';
            $dst_file = $dst_dir . '/' . $filename;

            // ディレクトリの存在チェック
            if (!File::isDirectory($dst_dir)) {
                $result = File::makeDirectory($dst_dir);
            }

            // Favicon ディレクトリへファイルの移動
            if (!rename($src_file, $dst_file)) {
                die("Couldn't rename file");
            }

            // Favicon
            $configs = Configs::updateOrCreate(
                ['name'     => 'favicon'],
                ['category' => 'favicon',
                 'value'    => $filename]
            );

            session()->flash('save_favicon', 'Favicon を設定しました。');
        }

        // ファビコン管理画面に戻る
        return redirect("/manage/site/favicon");
    }

    /**
     *  Favicon 設定　削除
     */
    public function deleteFavicon($request)
    {
        // httpメソッド確認
        if (!$request->isMethod('post')) {
            abort(403, '権限がありません。');
        }

        // ファビコン設定を取得
        $favicon = Configs::where('name', 'favicon')->first();
        if (empty($favicon)) {
            // ファビコン管理画面に戻る
            return redirect("/manage/site/favicon");
        }

        // ファイル削除
        $dst_file  = public_path() . '/uploads/favicon/favicon.ico';
        File::delete($dst_file);

        // データベース削除
        $favicon->delete();

        session()->flash('save_favicon', 'Favicon を削除しました。');

        // ファビコン管理画面に戻る
        return redirect("/manage/site/favicon");
    }
}
