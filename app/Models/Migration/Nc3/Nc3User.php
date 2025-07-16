<?php

namespace App\Models\Migration\Nc3;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Collection;

class Nc3User extends Model
{
    use HasFactory;
    
    /**
     * タイムスタンプの自動更新を無効にする
     */
    public $timestamps = false;
    
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

    /**
     * NC3ユーザIDからNC3ログインID取得
     */
    public static function getNc3LoginIdFromNc3UserId(Collection $nc3_users, ?int $nc3_user_id): ?string
    {
        $nc3_user = $nc3_users->firstWhere('id', $nc3_user_id) ?? new Nc3User();
        return $nc3_user->username;
    }

    /**
     * NC3ユーザIDからNC3ハンドル取得
     */
    public static function getNc3HandleFromNc3UserId(Collection $nc3_users, ?int $nc3_user_id): ?string
    {
        $nc3_user = $nc3_users->firstWhere('id', $nc3_user_id) ?? new Nc3User();
        return $nc3_user->handlename;
    }
}
