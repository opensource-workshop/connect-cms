<?php

namespace App\Models\Common;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

use App\Models\Common\GroupUser;
use App\UserableNohistory;

class PageRole extends Model
{
    // 論理削除
    use SoftDeletes;

    // 保存時のユーザー関連データの保持（履歴なしUserable）
    use UserableNohistory;

    /**
     * create()やupdate()で入力を受け付ける ホワイトリスト
     */
    protected $fillable = ['page_id', 'group_id', 'target', 'role_name', 'role_value'];

    /**
     * 日付型の場合、$dates にカラムを指定しておく。
     */
    protected $dates = ['created_at', 'updated_at', 'deleted_at'];
}
