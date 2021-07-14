<?php

namespace App\Models\User\Cabinets;

use Illuminate\Database\Eloquent\Model;

use App\UserableNohistory;

class Cabinet extends Model
{
    // 保存時のユーザー関連データの保持
    use UserableNohistory;

    // 更新する項目の定義
    protected $fillable = ['bucket_id', 'name', 'upload_max_size'];
}
