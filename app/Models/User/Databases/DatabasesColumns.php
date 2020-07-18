<?php

namespace App\Models\User\Databases;

use Illuminate\Database\Eloquent\Model;

class DatabasesColumns extends Model
{
    // 更新する項目の定義
    protected $fillable = ['databases_id', 'column_type', 'column_name', 'required', 'frame_col', 'list_hide_flag', 'detail_hide_flag', 'sort_flag', 'search_flag', 'select_flag', 'display_sequence', 'row_group', 'column_group'];

    /**
     * DBカラムの権限を取得
     * メソッドの呼び出しは`$databasesColumns->databasesColumnsRoles` で()を付けない
     */
    public function databasesColumnsRoles()
    {
        // 1対多
        return $this->hasMany('App\Models\User\Databases\DatabasesColumnsRole', 'databases_columns_id', 'id');
    }
}
