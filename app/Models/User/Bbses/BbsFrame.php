<?php

namespace App\Models\User\Bbses;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

use App\Userable;

class BbsFrame extends Model
{
    // 論理削除
    use SoftDeletes;

    // 保存時のユーザー関連データの保持
    use Userable;

    // 更新する項目の定義
    protected $fillable = ['bbs_id', 'frame_id', 'thread_sort_flag', 'view_count'];
}
