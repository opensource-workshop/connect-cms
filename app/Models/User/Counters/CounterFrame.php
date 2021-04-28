<?php

namespace App\Models\User\Counters;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

use App\UserableNohistory;

class CounterFrame extends Model
{
    // 論理削除
    use SoftDeletes;

    // 保存時のユーザー関連データの保持（履歴なしUserable）
    use UserableNohistory;

    // 更新する項目の定義
    protected $fillable = [
        'counter_id',
        'frame_id',
        'design_type',
        'use_total_count',
        'use_today_count',
        'use_yestday_count',
        'total_count_title',
        'today_count_title',
        'yestday_count_title',
    ];
}
