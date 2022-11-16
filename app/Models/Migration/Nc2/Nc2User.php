<?php

namespace App\Models\Migration\Nc2;

use Illuminate\Database\Eloquent\Model;

class Nc2User extends Model
{
    /**
     * 使用するDB Connection
     */
    protected $connection = 'nc2';

    /**
     * テーブル名の指定
     */
    protected $table = 'users';

    /**
     * NC2ユーザIDからNC2ログインID取得
     */
    public static function getNc2LoginIdFromNc2UserId($nc2_users, $nc2_user_id)
    {
        $nc2_user = $nc2_users->firstWhere('user_id', $nc2_user_id);
        $nc2_user = $nc2_user ?? new Nc2User();
        return $nc2_user->login_id;
    }
}
