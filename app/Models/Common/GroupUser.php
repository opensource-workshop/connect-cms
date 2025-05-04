<?php

namespace App\Models\Common;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

use App\Models\Common\Group;
use App\UserableNohistory;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class GroupUser extends Model
{
    // 論理削除
    use SoftDeletes;

    // 保存時のユーザー関連データの保持（履歴なしUserable）
    use UserableNohistory;
    use HasFactory;

    /**
     * create()やupdate()で入力を受け付ける ホワイトリスト
     */
    protected $fillable = ['group_id', 'user_id', 'group_role'];

    /**
     * キャストする必要のある属性
     */
    protected $casts = [
        'deleted_at' => 'datetime',
    ];

    /**
     * belongsTo 設定
     */
    public function group()
    {
        return $this->belongsTo(Group::class);
    }
}
