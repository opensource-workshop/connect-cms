<?php

namespace App\Models\User\Learningtasks;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

use App\Userable;

class LearningtasksConfigs extends Model
{
    // 論理削除
    use SoftDeletes;

    // 保存時のユーザー関連データの保持
    use Userable;

    // create()やupdate()で入力を受け付ける ホワイトリスト
    protected $fillable = ['learningtasks_id', 'post_id', 'type', 'task_status', 'value'];
}
