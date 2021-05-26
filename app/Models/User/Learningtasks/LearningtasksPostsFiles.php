<?php

namespace App\Models\User\Learningtasks;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

use App\Userable;

/*
    task_flag
    0 : レポート用
    1 : 試験用
*/
class LearningtasksPostsFiles extends Model
{
    // 論理削除
    use SoftDeletes;

    // 保存時のユーザー関連データの保持
    use Userable;

    // Carbonインスタンス（日付）に自動的に変換
    protected $dates = ['posted_at'];

    /**
     * create()やupdate()で入力を受け付ける ホワイトリスト
     */
    protected $fillable = ['post_id', 'upload_id', 'task_flag'];
}
