<?php

namespace App\Models\User\Databases;

use Illuminate\Database\Eloquent\Model;

use App\UserableNohistory;

class DatabasesInputs extends Model
{
    // 保存時のユーザー関連データの保持（履歴なしUserable）
    use UserableNohistory;

    /**
    *  指定したタイプの項目があるか判定
    */
    public function hasType($columns, $_type){
        $_types = array();
        
        if($_type && is_string($_type)){
            //文字列で項目のタイプが指定されていたとき
            $_types = $this->getColumnTypeAry($_type);
        }else{
            //不明の場合は偽を返す
            return false;
        }

        // 対応する行のカラム一覧
        $row_columns = $columns->where('databases_id', $this->databases_id);

        // 指定されたタイプの項目を探す
        foreach ($row_columns as $column) {
            //if ($column->column_type == $_type) {
            if(in_array($column->column_type, $_types)){
                return true;
            }
        }
        return false;
    }

    /**
    *  テキスト項目があるか判定
    */
    public function hasTitleType($columns){
        return $this->hasType($columns, 'title');
    }

    /**
    *  画像項目があるか判定
    */
    public function hasImageType($columns){
        return $this->hasType($columns, 'image');
    }

    /**
    *  文章の項目があるか判定
    */
    public function hasSentenceType($columns){
        return $this->hasType($columns, 'sentence');
    }

    /**
    *  n番目の画像型の値があるか判定
    */
    public function hasThImage($columns, $input_cols){
        if ($this->getThImage($columns, $input_cols)) {
            return true;
        }
        return false;
    }

    /**
    *  タイプを指定して、表示するデータの番号を返す
    */
    public function getNumType($columns, $type, $th_no=1){
        //コラムのデータを１行づつ確認する。
        foreach ($columns as $num => $col) {
            if(!$col['list_hide_flag']){
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
    }

    /**
    *  コラムをソートして返す（オブジェクトのままソートした方がいい？）
    */
    public function getColumns($columns, $_hide=null){
        $_columns = json_decode(json_encode($columns, JSON_UNESCAPED_UNICODE, 10), true);
        $_display_sequence = array_column($_columns, 'display_sequence');
        $_id = array_column($_columns, 'id');
        array_multisort( $_display_sequence, SORT_ASC, $_id, SORT_ASC, $_columns );

        if($_hide=='list' || $_hide=='detail'){
            // リストか 詳細 なら配列を加工して戻す。
            return $this->getColumnsSet($_columns, $_hide);

        }else{
            // ハイドがなければ配列のままもどす。
            return $_columns;
        }
    }
    // getColumns のエイリアス
    public function getColumnsDort($columns, $_hide=null){
        return $this->getColumns($columns, $_hide);
    }

    /**
    *  コラムのデータを配置しやすいように整理する
    */
    private function getColumnsSet($columns, $hide){
        if($hide != 'list' && $hide != 'detail'){
            return $columns;
        }
        //表示に必要な項目
        $_usedata = array('id', 'column_type', 'column_name', 'caption', 'caption_color', 'list_hide_flag', 'detail_hide_flag', 'classname');

        //タイトルに使用できるコラムタイプ
        $_title_type = array('text', 'radio', 'select');

        //サムネに使用できるコラムタイプ
        $_thum_type = array('image');

        //キャッチ用（メニュー）テキスト
        $_catch_type = array('text', 'radio', 'select', 'textarea','checkbox', 'wysiwyg', 'group');

        //戻り値の準備 タイトル用データとサムネ用データは別途準備する
        $_keys = array('thum', 'title', 'catch', 'item', 'cls', 'catchcls');
        $_res = array_fill_keys($_keys, null);
        $_res['item'] = array();

        foreach ($columns as $_column) {
            //使用する行のみ抜き出す
            if($_column[$hide.'_hide_flag'] != 1){
                $_col = array();

                foreach ($_column as $_key => $_val) {
                    // 表示必要な項目のみ選ぶ
                    if(in_array($_key, $_usedata)){
                        $_col[$_key] = $_val;
                    }
                }
                //最初のテキストはタイトルとして扱う
                if($_res['title'] == null && in_array($_col['column_type'], $_title_type)){
                    $_res['title'] = $_col;

                //最初のイメージはサムネールとして扱う（詳細の場合はメインイメージ？）
                }elseif($_res['thum'] == null && in_array($_col['column_type'], $_thum_type)){
                    $_res['thum'] = $_col;

                //メニュー用にキャッチコピーを準備する
                }elseif($_res['catch'] == null && in_array($_col['column_type'], $_catch_type)){
                    $_res['item'][$_column['id']] = $_res['catch'] = $_col;

                }else{
                    $_res['item'][$_column['id']] = $_col;
                }
            }
        }
        if(!$_res['title']){ $_res['cls'].= ' no-title';} //タイトルがない時のクラスを追加
        if(!$_res['thum']){ $_res['cls'].= ' no-thum';} //サムネールがない時のクラスを追加
        if(!$_res['catch']){ $_res['catchcls'] = ' no-catch';} //キャッチコピーがない時のクラスを追加
        return $_res;
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
    *  グループ化された項目のタイプ
    */
    private function getColumnTypeAry($_type){
        //入力値が文字列以外なら偽を返す。
        if(!is_string($_type)){
            return false;
        }

        if($_type == 'title'){
            //タイトルとして使えるタイプ
            $_res = array('text', 'radio', 'select');

        }elseif($_type == 'sentence'){
            //文字列として使えるタイプ   
            $_res = array('text', 'textarea', 'checkbox', 'wysiwyg', 'group');

        }else{
            //単独盲目でのチェック
            $_res = array($_type);
        }
        return $_res;
    }

    /**
    *  タイプに合わせた value をソースを返す
    */
    public function getTagType($input_cols, $column, $notag=null){

        $_obj = $this->getVolue($input_cols, $column['id']);

        if (empty($_obj) || empty($_obj->value)) {
            return '';
        }else{
            $_value = $_obj->value;
        }

        switch ($column["column_type"]) {
            case 'image':
                return '<img src="' . url('/') . '/file/' . $_value . '" class="img-fluid">';
                break;

            case 'video':
                return '<video src="' . url('/') . '/file/' . $_value . '" class="img-fluid" controls>';
                break;

            case 'file':
                return '<a href="' . url('/') . '/file/' . $_value . '" target="_blank">' . $_obj->client_original_name . '</a>';
                break;

            case 'checkbox':
                $_value = implode(', ', explode(',', $_value));
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

        if( isset($_obj->view_page_id) && isset($_obj->view_frame_id) && $_obj->view_page_id && $_obj->view_frame_id ){
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
     *  n番目の画像型の値を返却
     */
    public function getThImage($columns, $input_cols, $th_no = 1){

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
    public function getThText($columns, $input_cols, $th_no = 1){

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
