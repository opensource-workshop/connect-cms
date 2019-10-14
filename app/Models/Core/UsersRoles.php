<?php

namespace App\Models\Core;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Log;

class UsersRoles extends Model
{

    /**
     * create()やupdate()で入力を受け付ける ホワイトリスト
     */
    protected $fillable = ['users_id', 'target', 'role_name', 'role_value'];

    /**
     * 権限
     */
    var $user_roles = null;

    /**
     *  ユーザー権限の取得
     *
     * @param int $users_id
     * @return roles array
     */
    //public static function getUsersRoles($users_id, $target = null, $role_name = null)
    public function getUsersRoles($users_id)
    {

        // すでに内容を保持している場合はh時している内容を返却
        if (!empty($this->user_roles)) {
            return $this->user_roles;
        }

        // 指定されたユーザの権限を取得する。
        // target、role_name が指定された場合は絞り込んで値を返す。(後で実装予定)
        $users_roles = self::where('users_id', $users_id)->get();

        // 配列の形式は[target][role_name] = value{1|0}
        $this->user_roles = array();
        foreach($users_roles as $users_role) {
            $this->user_roles[$users_role->target][$users_role->role_name] = $users_role->role_value;

        }

        return $this->user_roles;
    }

    /**
     *  権限を保持しているかの判断
     */
    public function haveRole($target_role, $users_id)
    {
        $targets = $this->getUsersRoles($users_id);
        foreach ($targets as $roles) {
            foreach ($roles as $role => $value) {
                if ($role == $target_role) {
                    return true;
                }
            }
        }
        return false;
    }

    /**
     *  権限を保持していないかの判断
     */
    public function notRole($target_role, $users_id)
    {
        $targets = $this->getUsersRoles($users_id);
        foreach ($targets as $roles) {
            foreach ($roles as $role => $value) {
                if ($role == $target_role) {
                    return false;
                }
            }
        }
        return true;
    }
}
