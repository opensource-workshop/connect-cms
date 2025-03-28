<?php

namespace App\Models\User\Learningtasks;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

use App\UserableNohistory;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Learningtasks extends Model
{
    // 論理削除
    use SoftDeletes;

    // 保存時のユーザー関連データの保持（履歴なしUserable）
    use UserableNohistory;
    use HasFactory;

    /**
     * 使用設定を取得（科目の設定は除く）
     */
    public function learningtask_settings()
    {
        // post_id = 0はバケツの設定となっている
        return $this->hasMany(LearningtasksUseSettings::class, 'learningtasks_id', 'id')->where('post_id', 0);
    }
}
