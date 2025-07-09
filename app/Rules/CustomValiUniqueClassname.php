<?php

namespace App\Rules;

use Illuminate\Contracts\Validation\Rule;
use App\Models\Common\Categories;

/**
 * カテゴリのクラス名重複チェック用バリデーションルール
 * 
 * システム全体でカテゴリのクラス名が重複しないようにチェックします。
 * 新規追加時と既存編集時の両方に対応しています。
 * 
 * 使用例:
 * - 新規追加時: new CustomValiUniqueClassname()
 * - 既存編集時: new CustomValiUniqueClassname($category_id)
 * 
 * @package App\Rules
 */
class CustomValiUniqueClassname implements Rule
{
    /**
     * 除外するカテゴリID
     * 
     * @var int|null
     */
    private $exclude_id;

    /**
     * バリデーションルールインスタンスを作成
     *
     * @param int|null $exclude_id 除外するカテゴリID（既存編集時に自分自身を除外）
     */
    public function __construct($exclude_id = null)
    {
        $this->exclude_id = $exclude_id;
    }

    /**
     * バリデーションルールが通るかどうかを判定
     * 
     * 指定されたクラス名がcategoriesテーブルに存在するかチェックします。
     * 既存編集時は自分自身のレコードを除外してチェックします。
     * 
     * @param string $attribute バリデーション対象の属性名
     * @param mixed $value バリデーション対象の値
     * @return bool バリデーションが通る場合はtrue、通らない場合はfalse
     */
    public function passes($attribute, $value)
    {
        // 空の場合は required バリデーションで処理するため、ここではtrueを返す
        if (empty($value)) {
            return true;
        }

        $query = Categories::where('classname', $value);

        // 既存編集時は自分自身のレコードを除外
        if ($this->exclude_id) {
            $query->where('id', '!=', $this->exclude_id);
        }

        // 重複が存在しない場合はtrue（バリデーション通過）
        return !$query->exists();
    }

    /**
     * バリデーションエラー時のメッセージを取得
     *
     * @return string エラーメッセージ
     */
    public function message()
    {
        return 'クラス名が重複しています。';
    }
}
