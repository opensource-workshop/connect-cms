<?php

namespace App\Models\Common;

use App\Models\Common\GroupUser;
use App\UserableNohistory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * グループ
 *
 * @author 永原　篤 <nagahara@opensource-workshop.jp>
 * @author 牟田口 満 <mutaguchi@opensource-workshop.jp>
 * @copyright OpenSource-WorkShop Co.,Ltd. All Rights Reserved
 * @category グループ管理
 * @package Model
 */
class Group extends Model
{
    // 論理削除
    use SoftDeletes;

    // 保存時のユーザー関連データの保持（履歴なしUserable）
    use UserableNohistory;
    use HasFactory;

    /**
     * create()やupdate()で入力を受け付ける ホワイトリスト
     */
    protected $fillable = ['name', 'initial_group_flag', 'display_sequence'];

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
    public function getRoleNames(): array
    {
        // Role 設定がない場合
        if (empty($this->page_roles) || $this->page_roles->count() == 0) {
            return [];
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
            return [];
        }

        return $role_names;
    }
}
