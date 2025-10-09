<?php

namespace App\Rules;

use Illuminate\Contracts\Validation\ImplicitRule;
use App\Models\User\Databases\DatabasesInputCols;

/**
 * データベース・プラグインのファイル必須バリデーション
 *
 * 概要:
 * - 新規登録時: 必須（アップロードが必要）
 * - 編集時    : 既存ファイルが残っており、削除チェックが入っていなければ再アップロードは不要
 *
 * 用途:
 * - ファイル/画像/動画などのファイル系カラムで、`required` の代わりに利用する
 */
/**
 * このルールは ImplicitRule として実行され、
 * フィールドが未送信（存在しない）場合でも評価されます。
 */
class CustomValiRequiredFileKeep implements ImplicitRule
{
    /**
     * 対象のカラムID（databases_columns.id）
     *
     * @var int
     */
    private $column_id;

    /**
     * コンストラクタ
     *
     * @param int $column_id データベースカラムID（databases_columns.id）
     */
    public function __construct(int $column_id)
    {
        $this->column_id = $column_id;
    }

    /**
     * バリデーション本体
     *
     * @param string $attribute 対象属性名（例: databases_columns_value.<column_id>）
     * @param mixed $value 入力値
     * @return bool 検証結果
     */
    public function passes($attribute, $value)
    {
        if ($this->hasNewUpload($attribute)) {
            return true;
        }

        $row_id = $this->getRowIdFromRoute();
        if (empty($row_id)) {
            // 新規登録はアップロード必須
            return false;
        }

        // 編集時：既存ファイルがあり、削除チェックが無ければOK
        if ($this->hasExistingFile($row_id) && !$this->isDeleteChecked()) {
            return true;
        }

        // 既存が無い、または削除チェックありで新規アップ無しはNG
        return false;
    }

    /**
     * 新規アップロード有無の判定
     *
     * @param string $attribute 属性名
     * @return bool アップロードがあれば true
     */
    private function hasNewUpload(string $attribute): bool
    {
        return request()->hasFile($attribute);
    }

    /**
     * ルートパラメータから行ID（databases_inputs.id）を取得
     *
     * @return int|null 行ID。未指定の場合は null
     */
    private function getRowIdFromRoute(): ?int
    {
        $route = request()->route();
        if (!$route) {
            return null;
        }
        $id = $route->parameter('id');
        if ($id === null) {
            return null;
        }
        return is_numeric($id) ? (int) $id : null;
    }

    /**
     * 既存ファイルの有無をDBで確認
     *
     * @param int $row_id databases_inputs.id
     * @return bool 既存ファイルがあれば true
     */
    private function hasExistingFile(int $row_id): bool
    {
        $existing = DatabasesInputCols::where('databases_inputs_id', $row_id)
            ->where('databases_columns_id', $this->column_id)
            ->first();
        return $existing && !empty($existing->value);
    }

    /**
     * 削除チェックが付いているか判定
     *
     * @return bool 削除チェック済みなら true
     */
    private function isDeleteChecked(): bool
    {
        $delete_column_ids = request()->input('delete_upload_column_ids', []);
        return is_array($delete_column_ids) && array_key_exists($this->column_id, $delete_column_ids);
    }

    /**
     * バリデーションメッセージ
     *
     * @return string
     */
    public function message()
    {
        return ':attribute は必須です。';
    }
}
