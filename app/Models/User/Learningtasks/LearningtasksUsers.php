<?php

namespace App\Models\User\Learningtasks;

use App\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

use App\UserableNohistory;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class LearningtasksUsers extends Model
{
    // 論理削除
    use SoftDeletes;

    // 保存時のユーザー関連データの保持（履歴なしUserable）
    use UserableNohistory;
    use HasFactory;

    // 更新する項目の定義
    protected $fillable = ['post_id', 'user_id', 'role_name'];

    /**
     * ユーザーを取得する
     */
    public function user()
    {
        return $this->belongsTo(User::class, 'user_id', 'id');
    }
}
