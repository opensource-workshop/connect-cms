<?php

namespace App\Models\User\Conventions;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

use App\Userable;

class Convention extends Model
{
    // 論理削除
    use SoftDeletes;

    // 保存時のユーザー関連データの保持
    use Userable;

    // 更新する項目の定義
    protected $fillable = ['bucket_id', 'name', 'track_count', 'period_count', 'period_label'];
}
