<?php

namespace App\Models\User\Forms;

use Illuminate\Database\Eloquent\Model;

class FormsInputCols extends Model
{
    // 更新する項目の定義
    protected $fillable = ['forms_inputs_id', 'forms_columns_id', 'value'];
}
