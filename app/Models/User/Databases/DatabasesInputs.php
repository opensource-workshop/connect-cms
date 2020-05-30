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
}
