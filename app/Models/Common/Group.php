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
     * 特定のページが指定されたときのグループに対する権限。ページ管理で使用。
     */
    public $page_roles = null;

    /**
     * hasMany 設定
     * - hasManyは、$group->group_user で使い変数名と同義になるので、このメソッド名はphpcs除外
     */
    public function group_user()    // phpcs:ignore
    {
        return $this->hasMany(GroupUser::class);
    }

    /**
     * 特定のページが指定されたときのグループに対するRole の存在チェック
     */
    public function hasRole($role_name)
    {
        // Role 設定がない場合
        if (empty($this->page_roles) || $this->page_roles->count() == 0) {
            return false;
        }

        // 指定されたRole を保持している場合
        if ($this->page_roles->where('role_name', $role_name)->count() > 0) {
            return true;
        }

        // 合致しない。
        return false;
    }

    /**
     * 特定のページが指定されたときのグループに対するRole 名の取得
     */
    public function getRoleNames()
    {
        // Role 設定がない場合
        if (empty($this->page_roles) || $this->page_roles->count() == 0) {
            return null;
        }

        // 保持しているRole 名
        $role_names = array();

        // Role をループして名称を結合
        foreach (config('cc_role.CC_ROLE_LIST') as $role_name => $cc_role_name) {
            // 管理権限は対象外
            if (stripos($role_name, 'admin_') === 0) {
                continue;
            }
            // Role を保持しているか確認
            if ($this->hasRole($role_name)) {
                $role_names[] = $cc_role_name;
            }
        }

        // 空の場合
        if (empty($role_names)) {
            return null;
        }

        return $role_names;
    }
}
