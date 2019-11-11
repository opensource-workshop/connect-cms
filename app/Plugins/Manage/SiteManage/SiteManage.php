<?php

namespace App\Plugins\Manage\SiteManage;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;

use DB;

use App\Models\Core\Configs;
use App\Models\Common\Categories;
use App\Models\Common\Page;

use App\Plugins\Manage\ManagePluginBase;

/**
 * サイト管理クラス
 *
 * @author 永原　篤 <nagahara@opensource-workshop.jp>
 * @copyright OpenSource-WorkShop Co.,Ltd. All Rights Reserved
 * @category ページ管理
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
        $role_ckeck_table["index"]          = array('admin_site');
        $role_ckeck_table["update"]         = array('admin_site');
        $role_ckeck_table["categories"]     = array('admin_site');
        $role_ckeck_table["saveCategories"] = array('admin_site');

        return $role_ckeck_table;
    }

    /**
     *  ページ初期表示
     *
     * @return view
     */
    public function index($request, $page_id = null, $errors = array())
    {
        // Config データの取得
        $configs = Configs::get();

        // Config データの変換
        $configs_array = array();
        foreach ( $configs as $config ) {
            $configs_array[$config->name] = $config->value;
        }

        // 管理画面プラグインの戻り値の返し方
        // view 関数の第一引数に画面ファイルのパス、第二引数に画面に渡したいデータを名前付き配列で渡し、その結果のHTML。
        return view('plugins.manage.site.site',[
            "function"    => __FUNCTION__,
            "plugin_name" => "site",
            "errors"      => $errors,
            "configs"     => $configs_array,
        ]);
    }

    /**
     *  更新
     */
    public function update($request, $page_id = null, $errors = array())
    {
        // httpメソッド確認
        if (!$request->isMethod('post')) {
            abort(403, '権限がありません。');
        }

        // サイト名
        $configs = Configs::updateOrCreate(
            ['name'     => 'base_site_name'],
            ['category' => 'general',
             'value'    => $request->base_site_name]
        );

        // 画面の基本のテーマ
        $configs = Configs::updateOrCreate(
            ['name'     => 'base_theme'],
            ['category' => 'general',
             'value'    => $request->base_theme]
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

        // 基本のヘッダー固定設定
        $configs = Configs::updateOrCreate(
            ['name'     => 'base_header_fix'],
            ['category' => 'general',
             'value'    => (isset($request->base_header_fix) ? $request->base_header_fix : 0)]
        );

        // ログインリンクの表示
        $configs = Configs::updateOrCreate(
            ['name'     => 'base_header_login_link'],
            ['category' => 'general',
             'value'    => $request->base_header_login_link]
        );

        // 自動ユーザ登録の使用
        $configs = Configs::updateOrCreate(
            ['name'     => 'user_register_enable'],
            ['category' => 'user_register',
             'value'    => $request->user_register_enable]
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

        // ページ管理画面に戻る
        return redirect("/manage/site");
    }

    /**
     *  カテゴリ表示画面
     */
    public function categories($request, $id, $errors = null)
    {
        // セッション初期化などのLaravel 処理。
        $request->flash();

        // カテゴリデータの取得
        $categories = Categories::orderBy('target', 'asc')
                                ->orderBy('plugin_id', 'asc')
                                ->orderBy('display_sequence', 'asc')
                                ->get();

        return view('plugins.manage.site.categories',[
            "function"    => __FUNCTION__,
            "id"          => $id,
            "categories"  => $categories,
            "create_flag" => true,
            "errors"      => $errors,
        ]);
    }

    /**
     *  カテゴリ保存処理
     */
    public function saveCategories($request, $id)
    {
        // 追加項目のどれかに値が入っていたら、行の他の項目も必須
        if (!empty($request->add_display_sequence) || !empty($request->add_category) || !empty($request->add_color) || !empty($request->add_background_color)) {

            // 項目のエラーチェック
            $validator = Validator::make($request->all(), [
                'add_display_sequence' => ['required'],
                'add_classname'        => ['required'],
                'add_category'         => ['required'],
                'add_color'            => ['required'],
                'add_background_color' => ['required'],
            ]);
            $validator->setAttributeNames([
                'add_display_sequence' => '追加行の表示順',
                'add_classname'        => '追加行のクラス名',
                'add_category'         => '追加行のカテゴリ',
                'add_color'            => '追加行の文字色',
                'add_background_color' => '追加行の背景色',
            ]);

            if ($validator->fails()) {
                return $this->categories($request, $id, $validator->errors());
            }
        }

        // 既存項目のidに値が入っていたら、行の他の項目も必須
        if (!empty($request->categories_id)) {
            foreach($request->categories_id as $category_id) {

                // 項目のエラーチェック
                $validator = Validator::make($request->all(), [
                    'display_sequence.'.$category_id => ['required'],
                    'classname.'.$category_id        => ['required'],
                    'category.'.$category_id         => ['required'],
                    'color.'.$category_id            => ['required'],
                    'background_color.'.$category_id => ['required'],
                ]);
                $validator->setAttributeNames([
                    'display_sequence.'.$category_id => '表示順',
                    'classname.'.$category_id        => 'クラス名',
                    'category.'.$category_id         => 'カテゴリ',
                    'color.'.$category_id            => '文字色',
                    'background_color.'.$category_id => '背景色',
                ]);

                if ($validator->fails()) {
                    return $this->categories($request, $id, $validator->errors());
                }
            }
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

            foreach($request->categories_id as $category_id) {

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

        return $this->categories($request, $id, null);

        // ページ管理画面に戻る
        return redirect("/manage/site/categories");
    }
}
