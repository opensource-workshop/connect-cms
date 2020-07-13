<?php

namespace App\Models\User\Databases;

use Illuminate\Database\Eloquent\Model;

class DatabasesColumnsSelects extends Model
{
    // 更新する項目の定義
    protected $fillable = ['databases_columns_id', 'value', 'display_sequence', 'created_at', 'updated_at'];
}
