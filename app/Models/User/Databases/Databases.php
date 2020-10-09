<?php

namespace App\Models\User\Databases;

use Illuminate\Database\Eloquent\Model;

use App\UserableNohistory;

class Databases extends Model
{
    // 保存時のユーザー関連データの保持（履歴なしUserable）
    use UserableNohistory;

    // 更新する項目の定義
    protected $fillable = [
        'bucket_id',
        'databases_name',
        'posted_role_display_control_flag',
        'data_save_flag',
    ];

    /**
     * DBカラムの権限を取得
     * メソッドの呼び出しは`$databases->databasesRoles` で()を付けない
     */
    public function databasesRoles()
    {
        // 1対多
        return $this->hasMany('App\Models\User\Databases\DatabasesRole', 'databases_id', 'id');
    }
}
