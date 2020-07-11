<?php

namespace App\Models\User\Databases;

use Illuminate\Database\Eloquent\Model;

class DatabasesColumns extends Model
{
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
