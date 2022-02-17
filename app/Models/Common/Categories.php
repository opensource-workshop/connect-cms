<?php

namespace App\Models\Common;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\Validator;

use App\UserableNohistory;

class Categories extends Model
{
    // 論理削除
    use SoftDeletes;

    // 保存時のユーザー関連データの保持（履歴なしUserable）
    use UserableNohistory;

    /**
     * create()やupdate()で入力を受け付ける ホワイトリスト
     */
    protected $fillable = ['classname', 'category', 'color', 'background_color', 'target', 'plugin_id', 'display_sequence'];

    /**
     * カテゴリに対するleftJoin追加
     */
    public static function appendCategoriesLeftJoin($query, string $plugin_name, string $categories_id_column, string $target_id_column)
    {
        // $categories_id_column = 'blogs_posts.categories_id';
        // $target_id_column = 'blogs_posts.blogs_id';

        $query
            ->leftJoin('categories', function ($join) use ($categories_id_column) {
                $join->on('categories.id', '=', $categories_id_column)
                    ->whereNull('categories.deleted_at');
            })
            ->leftJoin('plugin_categories', function ($join) use ($plugin_name, $target_id_column) {
                $join->on('plugin_categories.categories_id', '=', 'categories.id')
                    ->where('plugin_categories.target', $plugin_name)
                    ->whereColumn('plugin_categories.target_id', $target_id_column)
                    ->where('plugin_categories.view_flag', 1)   // 表示するカテゴリのみ
                    ->whereNull('plugin_categories.deleted_at');
            });

        return $query;
    }

    /**
     * カテゴリデータ（一般プラグイン-登録画面用）の取得
     */
    public static function getInputCategories(?string $plugin_name, ?int $target_id)
    {
        $categories = Categories::select('categories.*')
            ->join('plugin_categories', function ($join) use ($plugin_name, $target_id) {
                $join->on('plugin_categories.categories_id', '=', 'categories.id')
                    ->where('plugin_categories.target', '=', $plugin_name)
                    ->where('plugin_categories.target_id', '=', $target_id)
                    ->where('plugin_categories.view_flag', 1)
                    ->whereNull('plugin_categories.deleted_at');
            })
            ->whereNull('categories.plugin_id')
            ->orWhere('categories.plugin_id', $target_id)
            // ->orderBy('categories.target', 'asc')
            ->orderBy('plugin_categories.display_sequence', 'asc')
            ->get();

        return $categories;
    }

    /**
     * 共通カテゴリ（一般プラグイン-設定画面用）の取得
     */
    public static function getGeneralCategories(?string $plugin_name, ?int $target_id)
    {
        // 共通カテゴリ
        $general_categories = Categories::
            select(
                'categories.*',
                'plugin_categories.view_flag',
                'plugin_categories.display_sequence as general_display_sequence'
            )
            ->leftJoin('plugin_categories', function ($join) use ($plugin_name, $target_id) {
                $join->on('plugin_categories.categories_id', '=', 'categories.id')
                    ->where('plugin_categories.target', '=', $plugin_name)
                    ->where('plugin_categories.target_id', '=', $target_id)
                    ->whereNull('plugin_categories.deleted_at');
            })
            ->whereNull('categories.target')
            ->orderBy('plugin_categories.display_sequence', 'asc')
            ->orderBy('categories.display_sequence', 'asc')
            ->get();

        foreach ($general_categories as $general_category) {
            // （初期登録時を想定）プラグイン側カテゴリの表示順が空なので、カテゴリの表示順を初期値にセット
            if (is_null($general_category->general_display_sequence)) {
                $general_category->general_display_sequence = $general_category->display_sequence;
            }
        }

        return $general_categories;
    }

    /**
     * 個別カテゴリ（一般プラグイン-設定画面用）の取得
     */
    public static function getPluginCategories(?string $plugin_name, ?int $target_id)
    {
        // 個別カテゴリ（プラグイン）
        $plugin_categories = null;
        if ($target_id) {
            $plugin_categories = Categories::
                select(
                    'categories.*',
                    'plugin_categories.view_flag',
                    'plugin_categories.display_sequence as plugin_display_sequence'
                )
                ->leftJoin('plugin_categories', function ($join) use ($target_id) {
                    $join->on('plugin_categories.categories_id', '=', 'categories.id')
                            ->where('plugin_categories.target_id', '=', $target_id)
                            ->whereNull('plugin_categories.deleted_at');
                })
                ->where('categories.target', $plugin_name)
                ->where('categories.plugin_id', $target_id)
                ->orderBy('plugin_categories.display_sequence', 'asc')
                ->get();
        }

        return $plugin_categories;
    }

    /**
     * 一般プラグイン-カテゴリ設定の入力エラーチェック
     */
    public static function validatePluginCategories($request)
    {
        /* エラーチェック
        ------------------------------------ */

        $rules = [];

        // エラーチェックの項目名
        $setAttributeNames = [];

        // 追加項目のどれかに値が入っていたら、行の他の項目も必須
        if (!empty($request->add_display_sequence) || !empty($request->add_classname)  || !empty($request->add_category) || !empty($request->add_color)) {
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

        // 共通項目 のidに値が入っていたら、行の他の項目も必須
        if (!empty($request->general_categories_id)) {
            foreach ($request->general_categories_id as $category_id) {
                // 項目のエラーチェック
                $rules['general_display_sequence.'.$category_id] = ['required'];

                $setAttributeNames['general_display_sequence.'.$category_id] = '表示順';
            }
        }

        // 既存項目 のidに値が入っていたら、行の他の項目も必須
        if (!empty($request->plugin_categories_id)) {
            foreach ($request->plugin_categories_id as $category_id) {
                // 項目のエラーチェック
                $rules['plugin_display_sequence.'.$category_id] = ['required'];
                $rules['plugin_category.'.$category_id] = ['required'];
                $rules['plugin_color.'.$category_id] = ['required'];
                $rules['plugin_background_color.'.$category_id] = ['required'];

                $setAttributeNames['plugin_display_sequence.'.$category_id] = '表示順';
                $setAttributeNames['plugin_category.'.$category_id] = 'カテゴリ';
                $setAttributeNames['plugin_color.'.$category_id] = '文字色';
                $setAttributeNames['plugin_background_color.'.$category_id] = '背景色';
            }
        }

        // 項目のエラーチェック
        $validator = Validator::make($request->all(), $rules);
        $validator->setAttributeNames($setAttributeNames);

        return $validator;
    }

    /**
     * 一般プラグイン-カテゴリ設定の保存
     */
    public static function savePluginCategories($request, string $plugin_name, int $target_id)
    {
        // 追加項目アリ
        if (!empty($request->add_display_sequence)) {
            $add_category = Categories::create([
                'classname'        => $request->add_classname,
                'category'         => $request->add_category,
                'color'            => $request->add_color,
                'background_color' => $request->add_background_color,
                'target'           => $plugin_name,
                'plugin_id'        => $target_id,
                'display_sequence' => intval($request->add_display_sequence),
            ]);
            PluginCategory::create([
                'target'           => $plugin_name,
                'target_id'        => $target_id,
                'categories_id'    => $add_category->id,
                'view_flag'        => (isset($request->add_view_flag) && $request->add_view_flag == '1') ? 1 : 0,
                'display_sequence' => intval($request->add_display_sequence),
            ]);
        }

        // 既存項目アリ
        if (!empty($request->plugin_categories_id)) {
            foreach ($request->plugin_categories_id as $plugin_categories_id) {
                // モデルオブジェクト取得
                $category = Categories::where('id', $plugin_categories_id)->first();

                // データのセット
                $category->classname        = $request->plugin_classname[$plugin_categories_id];
                $category->category         = $request->plugin_category[$plugin_categories_id];
                $category->color            = $request->plugin_color[$plugin_categories_id];
                $category->background_color = $request->plugin_background_color[$plugin_categories_id];
                $category->target           = $plugin_name;
                $category->plugin_id        = $target_id;
                $category->display_sequence = $request->plugin_display_sequence[$plugin_categories_id];

                // 保存
                $category->save();
            }
        }

        /* 表示フラグ更新(共通カテゴリ)
        ------------------------------------ */
        if (!empty($request->general_categories_id)) {
            foreach ($request->general_categories_id as $general_categories_id) {
                // FAQプラグインのカテゴリー使用テーブルになければ追加、あれば更新
                PluginCategory::updateOrCreate(
                    [
                        'target' => $plugin_name,
                        'target_id' => $target_id,
                        'categories_id' => $general_categories_id,
                    ],
                    [
                        'target' => $plugin_name,
                        'target_id' => $target_id,
                        'categories_id' => $general_categories_id,
                        'view_flag' => (isset($request->general_view_flag[$general_categories_id]) && $request->general_view_flag[$general_categories_id] == '1') ? 1 : 0,
                        'display_sequence' => $request->general_display_sequence[$general_categories_id],
                    ]
                );
            }
        }

        /* 表示フラグ更新(自FAQのカテゴリ)
        ------------------------------------ */
        if (!empty($request->plugin_categories_id)) {
            foreach ($request->plugin_categories_id as $plugin_categories_id) {
                // FAQプラグインのカテゴリー使用テーブルになければ追加、あれば更新
                PluginCategory::updateOrCreate(
                    [
                        'target' => $plugin_name,
                        'target_id' => $target_id,
                        'categories_id' => $plugin_categories_id,
                    ],
                    [
                        'target' => $plugin_name,
                        'target_id' => $target_id,
                        'categories_id' => $plugin_categories_id,
                        'view_flag' => (isset($request->plugin_view_flag[$plugin_categories_id]) && $request->plugin_view_flag[$plugin_categories_id] == '1') ? 1 : 0,
                        'display_sequence' => $request->plugin_display_sequence[$plugin_categories_id],
                    ]
                );
            }
        }
    }

    /**
     * カテゴリ１件削除
     */
    public static function deleteCategories(string $plugin_name, int $id)
    {
        // 削除(プラグイン側のカテゴリ表示データ)
        $plugin_categories_id = PluginCategory::where('categories_id', $id)->pluck('id');
        PluginCategory::destroy($plugin_categories_id);

        // 削除(カテゴリ)
        $categories_id = Categories::where('id', $id)->where('target', $plugin_name)->pluck('id');
        Categories::destroy($categories_id);
    }

    /**
     * バケツ削除時のカテゴリ削除
     */
    public static function destroyBucketsCategories(string $plugin_name, int $target_id)
    {
        // カテゴリデータ取得
        $plugin_categories = PluginCategory::where('target_id', $target_id);
        $plugin_categories_categories_ids = $plugin_categories->pluck('categories_id');
        $plugin_categories_ids = $plugin_categories->pluck('id');

        // カテゴリ削除. カテゴリはバケツ毎に別々に存在してるため、削除する
        $categories_ids = Categories::whereIn('id', $plugin_categories_categories_ids)->where('target', $plugin_name)->pluck('id');
        Categories::destroy($categories_ids);

        // プラグインカテゴリ削除
        PluginCategory::destroy($plugin_categories_ids);
    }
}
