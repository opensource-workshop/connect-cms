<?php

namespace App\Models\Common;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

use App\Models\Common\GroupUser;
use App\UserableNohistory;

class Group extends Model
{
    // 論理削除
    use SoftDeletes;

    // 保存時のユーザー関連データの保持（履歴なしUserable）
    use UserableNohistory;

    /**
     * create()やupdate()で入力を受け付ける ホワイトリスト
     */
    protected $fillable = ['name'];

    /**
     * 日付型の場合、$dates にカラムを指定しておく。
     */
    protected $dates = ['created_at', 'updated_at', 'deleted_at'];

    /**
     * hasMany 設定
     */
    public function group_user()
    {
        return $this->hasMany(GroupUser::class);
    }
}
