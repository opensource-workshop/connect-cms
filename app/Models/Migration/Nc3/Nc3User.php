<?php

namespace App\Models\Migration\Nc3;

use Illuminate\Database\Eloquent\Model;

class Nc3User extends Model
{
    /** ステータス */
    const
        status_active = 1,
        status_not_active = 0;

    /** 権限 */
    const
        role_system_administrator = 'system_administrator', // システム管理者
        role_administrator = 'administrator',               // サイト管理者
        role_common_user = 'common_user',                   // 一般
        role_guest_user = 'guest_user';                     // ゲスト

    /**
     * 使用するDB Connection
     */
    protected $connection = 'nc3';

    /**
     * テーブル名の指定
     */
    protected $table = 'users';
}
