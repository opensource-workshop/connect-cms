<?php

namespace App\Models\User\Databases;

use Illuminate\Database\Eloquent\Model;

class DatabasesInputCols extends Model
{
    // 更新する項目の定義
    protected $fillable = ['databases_inputs_id', 'databases_columns_id', 'value'];
}
