<?php

namespace App\Models\User\Forms;

use Illuminate\Database\Eloquent\Model;

class FormsColumnsSelects extends Model
{
    // 更新する項目の定義
    protected $fillable = ['forms_columns_id', 'value', 'caption', 'default', 'display_sequence'];
}
