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
    private $user_roles = null;

    /**
     * ユーザー権限の取得
     *
     * @param int $users_id
     * @return roles array
     */
    //public static function getUsersRoles($users_id, $target = null, $role_name = null)
    public function getUsersRoles($users_id, $target = null)
    {

        // すでに内容を保持している場合は保持している内容を返却
        if (!empty($this->user_roles)) {
            return $this->user_roles;
        }

        // 指定されたユーザの権限を取得する。
        // target、role_name が指定された場合は絞り込んで値を返す。(後で実装予定)
        if ($target) {
            $users_roles = self::where('users_id', $users_id)->where('target', $target)->get();
        } else {
            $users_roles = self::where('users_id', $users_id)->get();
        }

        // 配列の形式は[target][role_name] = value{1|0}
        $this->user_roles = array();
        foreach ($users_roles as $users_role) {
            $this->user_roles[$users_role->target][$users_role->role_name] = $users_role->role_value;
        }

        return $this->user_roles;
    }

    /**
     * 権限を保持しているかの判断
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
     * 管理系権限を保持しているかの判断
     */
    public function haveAdmin($users_id)
    {
        $targets = $this->getUsersRoles($users_id);
        foreach ($targets as $roles) {
            foreach ($roles as $role => $value) {
                if ($role == 'admin_system' || $role == 'admin_page' || $role == 'admin_site' || $role == 'admin_user') {
                    return true;
                }
            }
        }
        return false;
    }

    /**
     * 権限を保持していないかの判断
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

    /**
     * 指定された権限しか保有していないかの判断
     */
    public function isOnlyRole($target_role, $users_id)
    {
        $target_check = false;
        $targets = $this->getUsersRoles($users_id);
        foreach ($targets as $roles) {
            foreach ($roles as $role => $value) {
                if ($role == $target_role) {
                    $target_check = true;
                } else {
                    return false;
                }
            }
        }
        return $target_check;
    }

    /**
     * 指定された権限に該当するターゲットを取得
     */
    public static function getTargetByRole(string $target_role)
    {
        switch ($target_role) {
            // コンテンツ権限
            case 'role_article_admin':
            case 'role_arrangement':
            case 'role_article':
            case 'role_approval':
            case 'role_reporter':
                return 'base';
            // 管理権限
            case 'admin_system':
            case 'admin_site':
            case 'admin_page':
            case 'admin_user':
                return 'manage';
            default:
                return null;
        }
    }
}
