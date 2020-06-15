<?php

namespace App\Models\User\Databases;

use Illuminate\Database\Eloquent\Model;

use App\UserableNohistory;

class DatabasesInputs extends Model
{
    // 保存時のユーザー関連データの保持（履歴なしUserable）
    use UserableNohistory;

    /**
    *  タイプを指定して、表示するデータの番号を返す
    */
    public function getNumType($columns, $type, $th_no=1){
        //コラムのデータを１行づつ確認する。
        foreach ($columns as $num => $col) {
            if($col['list_hide_flag']){
                break;
            }
            if($col['column_type'] == 'image' && $type == 'image' ){
                $th_no--;
                if($th_no == 0){
                    return $num;
                }
            }elseif(($col['column_type'] == 'text' || $col['column_type'] == 'textarea' ) && $type == 'text' ){
                $th_no--;
                if($th_no == 0){
                    return $num;
                }
            }
        }
    }
    /**
    *  画像項目があるか判定
    */
    public function hasImageType($columns)
    {

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
    *  コラムをソートして返す（オブジェクトのままソートした方がいい？）
    */
    public function getColumnsDort($columns){
        $_columns = json_decode(json_encode($columns, JSON_UNESCAPED_UNICODE, 10), true);
        $_display_sequence = array_column($_columns, 'display_sequence');
        $_id = array_column($_columns, 'id');
        array_multisort( $_display_sequence, SORT_ASC, $_id, SORT_ASC, $_columns );
        return $_columns;
    }

    /**
    *  指定した番号のコラムの値を返す
    */
    public function getVolue($input_cols, $column_id, $col='') {

        // 対応する行のカラムの値
        $input_col = $input_cols->
            where('databases_inputs_id', $this->id)->
            where('databases_columns_id', $column_id)->first();

        if (empty($input_col)) {
            return '';
        }
        if($col){
            return $input_col->$col;
        }else{
            return $input_col;
        }
    }

    /**
    *  タイプに合わせた value をソースを返す
    */
    public function getTagType($input_cols, $column, $notag=0){

        $_obj = $this->getVolue($input_cols, $column['id']);

        if (empty($_obj) || empty($_obj->value)) {
            return '';
        }else{
            $_value = $_obj->value;
        }

        switch ($column["column_type"]) {
            case 'image':
                return '<img src="'.url('/').'/file/'.$_value.'" class="img-fluid">';
                break;
            case 'video':
                return '<video src="' . url('/') . '/file/' . $_value . '" class="img-fluid" controls>';
                break;
            case 'file':
                return '<a href="' . url('/') . '/file/' . $_value . '" target="_blank">' . $_obj->client_original_name . '</a>';
                break;
            case 'checkbox':
                if($notag){
                    return implode(', ', explode(',', $_value));
                }else{
                    return '<p>'.implode(', ', explode(',', $_value)).'</p>';
                }
                break;
            default:
                if($notag){
                    return $_value;
                }else{
                    return  '<p>'.$_value.'</p>';
                }
                break;
        }
    }

    /**
    * メニュー用のリンクを返す
    */
    public function getPageFrameLink( $frames, $pageid, $frameid ){
        //データベースが存在するフレーム設定を読み込む
        $_obj = $frames->where( 'frames_id', $frameid )
            ->select( 'view_page_id', 'view_frame_id' )->first();

        if( $_obj->view_page_id && $_obj->view_frame_id ){
            if( $_obj->view_page_id != $pageid ){
                $pageid = $_obj->view_page_id;
            }
            if( $_obj->view_frame_id != $frameid ){
                $frameid = $_obj->view_frame_id;
            }
        }
        return url('/').'/plugin/databases/detail/'.$pageid.'/'.$frameid.'/';
    }

    /**
    *  n番目の画像型の値があるか判定
    */
    public function hasThImage($columns, $input_cols)
    {
        if ($this->getThImage($columns, $input_cols)) {
            return true;
        }
        return false;
    }

    /**
     *  n番目の画像型の値を返却
     */
    public function getThImage($columns, $input_cols, $th_no = 1)
    {

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
    public function getThText($columns, $input_cols, $th_no = 1)
    {

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
