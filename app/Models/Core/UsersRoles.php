<?php

namespace App\Models\Core;

use Illuminate\Database\Eloquent\Model;

class UsersRoles extends Model
{

    /**
     * create()やupdate()で入力を受け付ける ホワイトリスト
     */
    protected $fillable = ['users_id', 'target', 'role_name', 'role_value'];

    /**
     * 権限
     */
    var $user_rolses = null;

    /**
     *  ユーザー権限の取得
     *
     * @param int $users_id
     * @return roles array
     */
    //public static function getUsersRoles($users_id, $target = null, $role_name = null)
    public static function getUsersRoles($users_id)
    {
        // 指定されたユーザの権限を取得する。
        // target、role_name が指定された場合は絞り込んで値を返す。(後で実装予定)
        $users_roles = self::where('users_id', $users_id)->get();

        // 配列の形式は[target][role_name] = value{1|0}
        $return_roles = array();
        foreach($users_roles as $users_role) {
            $return_roles[$users_role->target][$users_role->role_name] = $users_role->role_value;

        }

        return $return_roles;
    }
}
