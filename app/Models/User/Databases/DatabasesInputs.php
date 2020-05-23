<?php

namespace App\Models\User\Databases;

use Illuminate\Database\Eloquent\Model;

use App\UserableNohistory;

class DatabasesInputs extends Model
{
    // 保存時のユーザー関連データの保持（履歴なしUserable）
    use UserableNohistory;

    /**
     *  画像項目があるか判定
     */
    public function hasImageType($columns) {

        // 対応する行のカラム一覧
        $row_columns = $columns->where('databases_id', $this->databases_id);

        // 画像型を探す
        foreach ($row_columns as $column) {
            if ($column->column_type == 'image') {
                return true;
            }
        }
        return false;
    }

    /**
     *  n番目の画像型の値があるか判定
     */
    public function hasThImage($columns, $input_cols) {
        if ($this->getThImage($columns, $input_cols)) {
            return true;
        }
        return false;
    }

    /**
     *  n番目の画像型の値を返却
     */
    public function getThImage($columns, $input_cols, $th_no = 1) {

        // 対応する行のカラム一覧
        $row_columns_nosort = $columns->where('databases_id', $this->databases_id);
        $row_columns = $row_columns_nosort->sortBy('display_sequence');

        // 画像型を探す
        $column_id = null;
        foreach ($row_columns as $column) {
            if ($column->column_type == 'image') {
                // 探す順番のデクリメント
                $th_no--;
                if ($th_no == 0) {
                    $column_id = $column->id;
                    break;
                }
            }
        }

        // 対応する行のカラムの値
        $input_col = $input_cols->where('databases_inputs_id', $this->id)->where('databases_columns_id', $column_id)->first();
        if (empty($input_col)) {
            return '';
        }

        return $input_col->value;
    }

    /**
     *  n番目のテキスト型の値を返却
     */
    public function getThText($columns, $input_cols, $th_no = 1) {

        // 対応する行のカラム一覧
        $row_columns_nosort = $columns->where('databases_id', $this->databases_id);
        $row_columns = $row_columns_nosort->sortBy('display_sequence');

        // テキスト型を探す
        $column_id = null;
        foreach ($row_columns as $column) {
            if ($column->column_type == 'text' || $column->column_type == 'textarea') {
                // 探す順番のデクリメント
                $th_no--;
                if ($th_no == 0) {
                    $column_id = $column->id;
                    break;
                }
            }
        }

        // 対応する行のカラムの値
        $input_col = $input_cols->where('databases_inputs_id', $this->id)->where('databases_columns_id', $column_id)->first();
        if (empty($input_col)) {
            return '';
        }

        return $input_col->value;
    }
}
