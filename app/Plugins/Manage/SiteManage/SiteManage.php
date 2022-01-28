<?php

namespace App\Plugins\Manage\SiteManage;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\File;

use setasign\Fpdi\Tcpdf\Fpdi;

use App\Models\Core\ApiSecret;
use App\Models\Core\Configs;
use App\Models\Core\ConfigsLoginPermits;
use App\Models\Core\Plugins;
use App\Models\Common\Categories;
use App\Models\Common\Frame;
use App\Models\Common\Group;
use App\Models\Common\Holiday;
use App\Models\Common\Page;
use App\Models\Common\PageRole;

use App\Plugins\Manage\ManagePluginBase;
use App\Plugins\Manage\SiteManage\CCPDF;
use App\Enums\BaseLoginRedirectPage;

/**
 * サイト管理クラス
 *
 * @author 永原　篤 <nagahara@opensource-workshop.jp>
 * @copyright OpenSource-WorkShop Co.,Ltd. All Rights Reserved
 * @category サイト管理
 * @package Controller
 * @plugin_title サイト管理
 * @plugin_desc サイトの基本設定など、サイト全体に関する機能が集まった管理機能です。
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
        $role_ckeck_table["wysiwyg"]          = array('admin_site');
        $role_ckeck_table["saveWysiwyg"]      = array('admin_site');
        $role_ckeck_table["document"]         = array('admin_site');
        $role_ckeck_table["saveDocument"]     = array('admin_site');
        $role_ckeck_table["downloadDocument"] = array('admin_site');

        return $role_ckeck_table;
    }

    /**
     *  ページ初期表示
     *
     * @return view
     * @method_title サイト基本設定
     * @method_desc サイト名や基本のテーマなど、サイト全体の設定を行う画面です。
     * @method_detail 各項目の説明は画面の項目ごとのコメントを参照してください。
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
     *
     * @method_title カテゴリ設定
     * @method_desc サイトのカテゴリを設定できます。
     * @method_detail サイト全体カテゴリとして登録して、各プラグインで使用することで、統一感のあるカテゴリ設計ができるようになります。
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
     *
     * @method_title 多言語設定
     * @method_desc 多言語対応サイトを作成するときに使用します。
     * @method_detail 他言語設定することで、メニューが各言語ごとに表示されるようになり、他言語対応サイトとなります。
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
     *
     * @method_title レイアウト設定
     * @method_desc 画面幅をブラウザいっぱいまで広げたい場合などに使用します。
     * @method_detail タブレットを意識した授業用サイトでは、全てのエリアを100％、トップページのヘッダー部分を幅100％でデザインしたい場合は、ヘッダーエリアのみ100％などと設定します。
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
     * meta設定　表示画面
     *
     * @method_title meta情報
     * @method_desc 画面出力時のMETAタグの設定を行う画面です。
     * @method_detail 出力されるHTMLのHEAD部分の内容になります。
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
     *
     * @method_title エラーページ設定
     * @method_desc 404（該当ページなし）や403（該当ページに権限なし）の際に表示するエラーページを指定できます。
     * @method_detail 指定したエラーページは、通常のページと同じように作成します。また、メニュー表示はOFFにしておきます。
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
     *
     * @method_title アクセス解析設定
     * @method_desc GoogleAnalytics のトラッキングコードを埋め込むための画面です。
     * @method_detail ここで設定したトラッキングコードは、各画面で自動的に使用されます。
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
     * Favicon 設定　表示画面
     *
     * @method_title Favicon 設定
     * @method_desc Favicon を設定できます。
     * @method_detail サイトで使用するFavicon 画像をアップロードします。
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
     * Favicon 設定 更新
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
            // $client_original_name = $request->file('favicon')->getClientOriginalName();
            // $mimetype             = $request->file('favicon')->getClientMimeType();
            $extension            = $request->file('favicon')->getClientOriginalExtension();

            // 拡張子チェック
            if (mb_strtolower($extension) != 'ico') {
                $validator = Validator::make($request->all(), []);
                $validator->errors()->add('favicon', '.ico 以外はアップロードできません。');
                // return $this->favicon($request)->withErrors($validator);
                return redirect()->back()->withErrors($validator)->withInput();
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

            session()->flash('flash_message', 'Favicon を設定しました。');
        }

        // ファビコン管理画面に戻る
        return redirect("/manage/site/favicon");
    }

    /**
     * Favicon 設定 削除
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

    /**
     * WYSIWYG設定画面
     *
     * @method_title WYSIWYG 設定
     * @method_desc WYSIWYG の設定を変更できます。
     * @method_detail 文字サイズの変更をWYSIWYG で使用するかどうかを設定できます。自由度は増しますが、サイトの統一感を保持しにくい場合もあります。<br />画像アップロード時に初期に選択させる画像サイズもここで設定できます。
     */
    public function wysiwyg($request, $id = null)
    {
        // Config データの取得
        $configs = Configs::get();

        // 管理画面プラグインの戻り値の返し方
        return view('plugins.manage.site.wysiwyg', [
            "function" => __FUNCTION__,
            "plugin_name" => "site",
            "configs" => $configs,
            "gd_disabled_label" => !function_exists('gd_info') ? 'disabled' : '',
        ]);
    }

    /**
     * WYSIWYG設定 更新
     */
    public function saveWysiwyg($request, $id = null)
    {
        // wysiwygで文字サイズの使用
        $configs = Configs::updateOrCreate(
            ['name' => 'fontsizeselect'],
            ['category' => 'wysiwyg', 'value' => $request->fontsizeselect]
        );

        // 初期に選択させる画像サイズ
        $configs = Configs::updateOrCreate(
            ['name' => 'resized_image_size_initial'],
            ['category' => 'wysiwyg', 'value' => $request->resized_image_size_initial]
        );

        // WYSIWYG設定画面に戻る
        return redirect("/manage/site/wysiwyg")->with('flash_message', '更新しました。');
    }

    /**
     * サイト設計書出力指示画面
     *
     * @method_title サイト設計書
     * @method_desc サイト設計書の出力ができます。
     * @method_detail ページやフレームの設定をPDFの設計書で出力できます。
     */
    public function document($request, $id = null)
    {
        // Config データの取得
        $configs = Configs::get();

        // 管理画面プラグインの戻り値の返し方
        return view('plugins.manage.site.document', [
            "function" => __FUNCTION__,
            "plugin_name" => "site",
            "configs" => $configs,
        ]);
    }

    /**
     * Configs 更新
     */
    private function updateConfigs($request, $config_values)
    {
        foreach ($config_values as $category => $configs) {
            foreach ($configs as $config) {
                $configs = Configs::updateOrCreate(
                    ['name'     => $config],
                    ['category' => $category,
                     'value'    => $request->input($config)]
                );
            }
        }
    }

    /**
     * サイト設計書設定保存
     */
    public function saveDocument($request)
    {
        // httpメソッド確認
        if (!$request->isMethod('post')) {
            abort(403, '権限がありません。');
        }

        // Configs 保存内容
        $config_values = [
            'document' => [
                 'document_secret_name',
                 'document_auth_netcomons2_admin_password',
                 'document_support_org_title',
                 'document_support_org_txt',
                 'document_support_contact_title',
                 'document_support_contact_txt',
                 'document_support_other_title',
                 'document_support_other_txt',
            ],
        ];
        $this->updateConfigs($request, $config_values);

        // ページ管理画面に戻る
        return redirect("/manage/site/document");
    }

    /**
     * セクション出力
     */
    private function outputSection(&$pdf, &$sections)
    {
        foreach ($sections as $section) {
            $pdf->writeHTML(view('plugins.manage.site.pdf.' . $section[0], $section[1])->render(), false);
            $pdf->Bookmark($section[2], 1, 0, '', '', array(0, 0, 0));
        }
    }

    /**
     * サイト設計書ダウンロード
     */
    public function downloadDocument($request)
    {
        // 必要なデータの取得
        $configs = Configs::get(); // Config
        $categories = Categories::whereNull('target')->get(); // 共通カテゴリ

        // 出力内容の編集とため込み
        $output = collect();

        // サイト名
        $output->put('base_site_name', $configs->firstWhere('name', 'base_site_name')->value);

        // 出力するPDF の準備
        $pdf = new CCPDF(PDF_PAGE_ORIENTATION, PDF_UNIT, PDF_PAGE_FORMAT, true, 'UTF-8', false);

        // PDF プロパティ設定
        $pdf->SetTitle($output->get('base_site_name') . ' サイト設計書');
        //$pdf->SetAuthor('');
        //$pdf->SetSubject('');
        //$pdf->SetKeywords('');
        //$pdf->SetCreator('');

        // 余白
        $pdf->SetMargins(15, 20, 15);

        // フォントを登録
        // 追加フォントをtcpdf用フォントファイルに変換してvendor\tecnickcom\tcpdf\fontsに登録
        $font = new \TCPDF_FONTS();

        // ttfフォントファイルからtcpdf用フォントファイルを生成（tcpdf用フォントファイルがある場合は再生成しない）
        $fontX = $font->addTTFfont(resource_path('fonts/ipaexg.ttf'));

        // ヘッダーのフォントの設定（フォント情報を配列で渡す必要があるので、要注意）
        $pdf->setHeaderMargin(5);
        $pdf->setHeaderFont(array('ipaexg', '', 10));
        $pdf->setHeaderData('', 0, $configs->firstWhere('name', 'base_site_name')->value . " - " . url('/'), '');

        // フッター
        $pdf->setPrintFooter(true);

        // フォント設定
        $pdf->setFont('ipaexg', '', 12);

        // --- 表紙

        // 初期ページを追加
        $pdf->addPage();

        // サイト設計書表紙
        $pdf->writeHTML(view('plugins.manage.site.pdf.cover', compact('configs'))->render(), false);

        // --- サイト管理

        // ページを追加
        $pdf->addPage();
        $pdf->Bookmark('サイト基本設定', 0, 0, '', '', array(0, 0, 0));

        // サイト管理
        $sections = [
            ['base_main',      compact('configs'),    'サイト基本設定'],
            ['base_meta',      compact('configs'),    'メタ情報'],
            ['base_layout',    compact('configs'),    'レイアウト設定'],
            ['base_category',  compact('categories'), '共通カテゴリ設定'],
            ['base_language',  compact('configs'),    '他言語設定'],
            ['base_error',     compact('configs'),    'エラー設定'],
            ['base_analytics', compact('configs'),    'アクセス解析'],
            ['base_favicon',   compact('configs'),    'ファビコン'],
            ['base_wysiwyg',   compact('configs'),    'WYSIWYG設定'],
        ];
        $this->outputSection($pdf, $sections);

        // --- ページ管理

        // ページデータの取得(laravel-nestedset 使用)
        $return_obj = 'flat';
        $pages = Page::defaultOrderWithDepth($return_obj);

        // ページ権限を取得してGroup オブジェクトに保持する。
        $page_roles = PageRole::join('groups', 'groups.id', '=', 'page_roles.group_id')
                ->whereNull('groups.deleted_at')
                ->where('page_roles.role_value', 1)
                ->get();

        foreach ($pages as &$page) {
            $page->page_roles = $page_roles->where('page_id', $page->id);
        }

        // ページを追加
        $pdf->addPage();
        $pdf->Bookmark('ページ設定', 0, 0, '', '', array(0, 0, 0));

        // ページ設定
        $sections = [
            ['page',        compact('pages'), 'ページ設定'],
            ['page_limit',  compact('pages'), '制限関連'],
            ['page_design', compact('pages'), 'デザイン関連'],
            ['page_action', compact('pages'), '動作関連'],
        ];
        $this->outputSection($pdf, $sections);

        // --- フレーム管理

        // Page モデルのwithDepth() を使いたかったので、フレーム取得だけれど、Page をメインにしてFrame をJOIN させています。
        $frames = Page::select('pages.page_name', 'buckets.bucket_name', 'frames.*')
                       ->join('frames', 'pages.id', '=', 'frames.page_id')
                       ->leftJoin('buckets', 'frames.bucket_id', '=', 'buckets.id')
                       ->orderBy('pages._lft')
                       ->orderByRaw('FIELD(frames.area_id, 0, 1, 3, 4, 2)')
                       ->orderBy('frames.display_sequence')
                       ->withDepth()
                       ->get();

        // フレーム設定
        $pdf->addPage();
        $pdf->Bookmark('フレーム設定', 0, 0, '', '', array(0, 0, 0));

        // フレーム設定
        $sections = [
            ['frame',        compact('frames'), 'フレーム基本情報'],
            ['frame_data',   compact('frames'), 'フレームデータ情報'],
            ['frame_design', compact('frames'), 'フレームデザイン情報'],
            ['frame_limit',  compact('frames'), 'フレーム制限情報'],
        ];
        $this->outputSection($pdf, $sections);

        // --- ユーザ管理

        // ユーザ設定
        $pdf->addPage();
        $pdf->Bookmark('ユーザ設定', 0, 0, '', '', array(0, 0, 0));

        // フレーム設定
        $sections = [
            ['user_regist', compact('configs'), '自動ユーザ登録設定'],
        ];
        $this->outputSection($pdf, $sections);

        // --- グループ管理

        $groups = Group::select('groups.id', 'groups.name', DB::raw("count(group_users.id) as group_users_count"))
                       ->join('group_users', 'group_users.group_id', '=', 'groups.id')
                       ->groupBy('groups.id', 'groups.name')
                       ->get();

        // グループ設定
        $pdf->addPage();
        $pdf->Bookmark('グループ設定', 0, 0, '', '', array(0, 0, 0));

        // グループ設定
        $sections = [
            ['group_list', compact('groups'), 'グループ一覧'],
        ];
        $this->outputSection($pdf, $sections);

        // --- セキュリティ管理

        $login_permits = ConfigsLoginPermits::orderBy('apply_sequence')->get();

        // HTML記述制限
        $purifiers = config('cc_role.CC_HTMLPurifier_ROLE_LIST');

        // Config テーブルからHTML記述制限の取得
        // Config テーブルにデータがあれば、配列を上書きする。
        // 初期状態ではConfig テーブルはなく、cc_role.CC_HTMLPurifier_ROLE_LIST を初期値とするため。
        $config_purifiers = Configs::where('category', 'html_purifier')->get();
        foreach ($config_purifiers as $config_purifier) {
            if (array_key_exists($config_purifier->name, $purifiers)) {
                $purifiers[$config_purifier->name] = $config_purifier->value;
            }
        }

        // セキュリティ管理
        $pdf->addPage();
        $pdf->Bookmark('セキュリティ管理', 0, 0, '', '', array(0, 0, 0));

        // ログイン制限
        $sections = [
            ['security_login',    compact('login_permits'), 'ログイン制限'],
            ['security_purifier', compact('purifiers'),     'HTML記述制限'],
        ];
        $this->outputSection($pdf, $sections);

        // --- プラグイン管理

        $plugins = Plugins::orderBy('display_sequence')->get();

        // プラグイン管理
        $pdf->addPage();
        $pdf->Bookmark('プラグイン管理', 0, 0, '', '', array(0, 0, 0));

        // ログイン制限
        $sections = [
            ['plugin_list', compact('plugins'), 'プラグイン一覧'],
        ];
        $this->outputSection($pdf, $sections);

        // --- システム管理

        // システム管理
        $pdf->addPage();
        $pdf->Bookmark('システム管理', 0, 0, '', '', array(0, 0, 0));

        // ログイン制限
        $sections = [
            ['system_server', compact('configs'), 'サーバ設定'],
            ['system_log',    compact('configs'), 'エラーログ設定'],
        ];
        $this->outputSection($pdf, $sections);

        // --- API管理

        $api_secrets = ApiSecret::orderBy('id')->get();

        // API管理
        $pdf->addPage();
        $pdf->Bookmark('API管理', 0, 0, '', '', array(0, 0, 0));

        // API管理
        $sections = [
            ['api_list', compact('api_secrets'), 'Secret Code 一覧'],
        ];
        $this->outputSection($pdf, $sections);

        // --- メッセージ管理

        // メッセージ管理
        $pdf->addPage();
        $pdf->Bookmark('メッセージ管理', 0, 0, '', '', array(0, 0, 0));

        // メッセージ管理
        $sections = [
            ['massage_first', compact('configs'), '初回確認メッセージ'],
        ];
        $this->outputSection($pdf, $sections);

        // --- 外部認証

        // 外部認証
        $pdf->addPage();
        $pdf->Bookmark('外部認証', 0, 0, '', '', array(0, 0, 0));

        // 外部認証
        $sections = [
            ['auth_base',        compact('configs'), '認証設定'],
            ['auth_ldap',        compact('configs'), 'LDAP認証'],
            ['auth_shibboleth',  compact('configs'), 'Shibboleth認証'],
            ['auth_netcommons2', compact('configs'), 'NetCommons2認証'],
        ];
        $this->outputSection($pdf, $sections);

        // --- 外部サービス設定

        // 外部サービス設定
        $pdf->addPage();
        $pdf->Bookmark('外部サービス設定', 0, 0, '', '', array(0, 0, 0));

        // 外部サービス設定
        $sections = [
            ['service_base',  compact('configs'), 'WYSIWYG設定'],
            ['service_pdf',   compact('configs'), 'PDFアップロード'],
            ['service_face',  compact('configs'), 'AI顔認識'],
        ];
        $this->outputSection($pdf, $sections);

        // --- アップロードファイル

        // アップロードファイル
        $pdf->addPage();
        $pdf->Bookmark('アップロードファイル', 0, 0, '', '', array(0, 0, 0));

        // アップロードファイル
        $sections = [
            ['upload_userfile', compact('configs'), 'ユーザディレクトリ一覧'],
        ];
        $this->outputSection($pdf, $sections);

        // --- テーマ管理

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

        // テーマ管理
        $pdf->addPage();
        $pdf->Bookmark('テーマ管理', 0, 0, '', '', array(0, 0, 0));

        // テーマ管理
        $sections = [
            ['theme_user', compact('dirs'), 'ユーザ・テーマ'],
        ];
        $this->outputSection($pdf, $sections);

        // --- ログ管理

        // ログ管理
        $pdf->addPage();
        $pdf->Bookmark('ログ管理', 0, 0, '', '', array(0, 0, 0));

        // ログ管理
        $sections = [
            ['log_main', compact('configs'), 'ログ設定'],
        ];
        $this->outputSection($pdf, $sections);

        // --- 祝日管理

        $holidays = Holiday::orderBy('holiday_date', 'asc')->get();

        // 祝日管理
        $pdf->addPage();
        $pdf->Bookmark('祝日管理管理', 0, 0, '', '', array(0, 0, 0));

        // 祝日管理
        $sections = [
            ['holiday_main', compact('holidays'), '変更内容一覧'],
        ];
        $this->outputSection($pdf, $sections);

        // --- 問い合わせ先

        // 問い合わせ先ページを追加
        $pdf->addPage();

        // 問い合わせ先
        $pdf->writeHTML(view('plugins.manage.site.pdf.contact', compact('configs'))->render(), false);
        $pdf->Bookmark('お問い合わせ先', 0, 0, '', '', array(0, 0, 0));

        // 目次ページの追加
        $pdf->addTOCPage();

        // write the TOC title
        $pdf->SetFont('ipaexg', 'B', 28);
        $pdf->MultiCell(0, 0, 'Webサイト設計書　目次', 0, 'C', 0, 1, '', 30, true, 0);
        $pdf->Ln();

        $pdf->SetFont('ipaexg', '', 12);

        // add a simple Table Of Content at first page
        // (check the example n. 59 for the HTML version)
        $pdf->addTOC(2, 'ipaexg', '.', 'INDEX', 'B', array(0, 0, 0));

        // end of TOC page
        $pdf->endTOCPage();

        // 目次 --------------------/

        $disposition = ($request->filled('disposition') && $request->disposition == 'inline') ? 'I' : 'D';

        // 出力 ( D：Download, I：Inline )
        $pdf->output('SiteDocument-' . $output->get('base_site_name') . '.pdf', $disposition);
        return redirect()->back();
    }
}
