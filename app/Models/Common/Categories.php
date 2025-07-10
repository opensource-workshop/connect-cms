<?php

namespace App\Models\Common;

use App\Enums\ColorName;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\Validator;
use Illuminate\Http\Request;

use App\Rules\CustomValiUniqueClassname;
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
     * サイト管理用カテゴリ設定のバリデーション
     *
     * @param Request $request
     * @return \Illuminate\Validation\Validator
     */
    public static function validateSiteCategories(Request $request): \Illuminate\Validation\Validator
    {
        $rules = [];
        $set_attribute_names = [];

        // 追加項目のバリデーション
        self::addValidationRulesForAddFields($request, $rules, $set_attribute_names);

        // 既存項目のバリデーション
        self::addValidationRulesForExistingFields(
            $request->categories_id ?? [],
            '',
            $rules,
            $set_attribute_names
        );

        return self::createValidator($request, $rules, $set_attribute_names);
    }

    /**
     * 一般プラグイン-カテゴリ設定の入力エラーチェック
     */
    public static function validatePluginCategories(Request $request): \Illuminate\Validation\Validator
    {
        $rules = [];
        $set_attribute_names = [];

        // 追加項目のバリデーション
        self::addValidationRulesForAddFields($request, $rules, $set_attribute_names);

        // 共通項目のバリデーション
        if (!empty($request->general_categories_id)) {
            foreach ($request->general_categories_id as $category_id) {
                $field_name = 'general_display_sequence.' . $category_id;
                $rules[$field_name] = ['required'];
                $set_attribute_names[$field_name] = '表示順';
            }
        }

        // 既存項目のバリデーション（プラグイン固有）
        self::addValidationRulesForExistingFields(
            $request->plugin_categories_id ?? [],
            'plugin_',
            $rules,
            $set_attribute_names
        );

        return self::createValidator($request, $rules, $set_attribute_names);
    }

    /**
     * 追加項目（新規行）のバリデーションルールを追加
     *
     * @param Request $request
     * @param array $rules
     * @param array $set_attribute_names
     * @return void
     */
    private static function addValidationRulesForAddFields(Request $request, array &$rules, array &$set_attribute_names): void
    {
        // 追加項目が入力されていない場合は、処理を抜ける
        if (!self::hasAddCategoryValue($request)) {
            return;
        }

        $rules['add_display_sequence'] = ['required'];
        $rules['add_classname'] = ['required', new CustomValiUniqueClassname()];
        $rules['add_category'] = ['required'];
        $rules['add_color'] = ['required'];
        $rules['add_background_color'] = ['required'];

        $set_attribute_names['add_display_sequence'] = '追加行の表示順';
        $set_attribute_names['add_classname'] = '追加行のクラス名';
        $set_attribute_names['add_category'] = '追加行のカテゴリ';
        $set_attribute_names['add_color'] = '追加行の文字色';
        $set_attribute_names['add_background_color'] = '追加行の背景色';
    }

    /**
     * 既存項目（更新行）のバリデーションルールを追加
     *
     * @param array $category_ids
     * @param string $field_prefix
     * @param array $rules
     * @param array $set_attribute_names
     * @return void
     */
    private static function addValidationRulesForExistingFields(array $category_ids, string $field_prefix, array &$rules, array &$set_attribute_names): void
    {
        foreach ($category_ids as $category_id) {
            $rules[$field_prefix . 'display_sequence.' . $category_id] = ['required'];
            $rules[$field_prefix . 'classname.' . $category_id] = ['required', new CustomValiUniqueClassname($category_id)];
            $rules[$field_prefix . 'category.' . $category_id] = ['required'];
            $rules[$field_prefix . 'color.' . $category_id] = ['required'];
            $rules[$field_prefix . 'background_color.' . $category_id] = ['required'];

            $set_attribute_names[$field_prefix . 'display_sequence.' . $category_id] = '表示順';
            $set_attribute_names[$field_prefix . 'classname.' . $category_id] = 'クラス名';
            $set_attribute_names[$field_prefix . 'category.' . $category_id] = 'カテゴリ';
            $set_attribute_names[$field_prefix . 'color.' . $category_id] = '文字色';
            $set_attribute_names[$field_prefix . 'background_color.' . $category_id] = '背景色';
        }
    }

    /**
     * 追加項目に値が入力されているかチェックして、true/falseを返す
     *
     * @param Request $request
     * @return bool
     */
    private static function hasAddCategoryValue(Request $request): bool
    {
        $add_fields = ['add_display_sequence', 'add_classname', 'add_category', 'add_color', 'add_background_color'];
        
        foreach ($add_fields as $field) {
            if (!empty($request->$field)) {
                return true;
            }
        }
        
        return false;
    }

    /**
     * バリデーターを作成して返却
     *
     * @param Request $request
     * @param array $rules
     * @param array $set_attribute_names
     * @return \Illuminate\Validation\Validator
     */
    private static function createValidator(Request $request, array $rules, array $set_attribute_names): \Illuminate\Validation\Validator
    {
        $validator = Validator::make($request->all(), $rules);
        $validator->setAttributeNames($set_attribute_names);

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

    /**
     * hoverのbackgroud-colorを取得する
     *
     * @return string カラーコード
     */
    public function getHoverBackgroundColorAttribute(): string
    {
        $background_color = $this->background_color;
        if (strpos($background_color, '#') !== 0) {
            $background_color = self::toColorCode($background_color);
        }

        // カラーネームからカラーコードに変換できない場合は、そのまま返却する
        if ($background_color === false) {
            return $background_color;
        }

        return self::adjustBrightness($background_color, -20);
    }

    /**
     * カラーネームをカラーコードに変換する
     *
     * @param string $color_name カラーネーム
     * @return string|boolean 変換できない場合はfalse
     */
    private function toColorCode(string $color_name)
    {
        $color_name = strtolower($color_name);
        // 対応しないカラーネーム
        if (!in_array($color_name, ColorName::getMemberKeys())) {
            return false;
        }
        $hex = ColorName::getDescription($color_name);

        return $hex;
    }
    /**
     * 色の明度を調節する
     *
     * @param string $hex 16進のカラーコード
     * @param string $steps -255から255 負数は暗くなり、正数は明るくなる
     * @return string 調節した16進のカラーコード
     */
    public static function adjustBrightness($hex, $steps)
    {
        // This code is written by Torkil Johnsen. https://stackoverflow.com/users/1034002/torkil-johnsen
        // From https://stackoverflow.com/questions/3512311/how-to-generate-lighter-darker-color-with-php/11951022#11951022
        // License CC BY-SA 3.0 https://creativecommons.org/licenses/by-sa/3.0/deed.ja

        // Steps should be between -255 and 255. Negative = darker, positive = lighter
        $steps = max(-255, min(255, $steps));

        // Normalize into a six character long hex string
        $hex = str_replace('#', '', $hex);
        if (strlen($hex) == 3) {
            $hex = str_repeat(substr($hex, 0, 1), 2) . str_repeat(substr($hex, 1, 1), 2) . str_repeat(substr($hex, 2, 1), 2);
        }

        // Split into three parts: R, G and B
        $color_parts = str_split($hex, 2);
        $return = '#';

        foreach ($color_parts as $color) {
            $color   = hexdec($color); // Convert to decimal
            $color   = max(0, min(255, $color + $steps)); // Adjust color
            $return .= str_pad(dechex($color), 2, '0', STR_PAD_LEFT); // Make two char hex code
        }

        return $return;
    }
}
