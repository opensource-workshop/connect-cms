<?php

namespace App\Models\User\Covids;

use Illuminate\Database\Eloquent\Model;

use App\Userable;

class Covid extends Model
{
    // 保存時のユーザー関連データの保持
    use Userable;

    // 更新する項目の定義
    protected $fillable = ['bucket_id', 'covids_name', 'source_base_url'];
}
