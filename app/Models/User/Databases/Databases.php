<?php

namespace App\Models\User\Databases;

use Illuminate\Database\Eloquent\Model;

class Databases extends Model
{
    // 更新する項目の定義
    protected $fillable = ['bucket_id', 'databases_name'];
}
