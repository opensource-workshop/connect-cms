<?php

namespace App\Models\Common;

use Illuminate\Support\Facades\Auth;

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

    /**
     * ページの系統取得
     * ConnectCommonTrait から移動してきた）
     */
    public static function getPageRoles($page_ids = null)
    {
        // ページID、ユーザID は必須
        //if (empty($page_ids) || !is_array($page_ids)) {
        //    return null;
        //}

        // シングルトン
        //if ($this->page_roles) {
        //    return $this->page_roles;
        //}

        $user = Auth::user();
        if (empty($user)) {
            return null;
        }

        // ページ、ユーザでpage_roles を検索
        // ページは階層分、取得する。
        // グループは複数、含まれている状態で保持しておく。
        // ページやグループでデータを抜き出す場合は、Laravel のCollection メソッドでwhere を使用。
        //$this->page_role = PageRole::select('page_roles.page_id', 'page_roles.group_id', 'page_roles.role_name',
        $page_role_query = PageRole::
            select(
                'page_roles.page_id', 'page_roles.group_id', 'page_roles.role_name',
                'group_users.user_id', 'groups.name AS groups_name', 'group_users.group_role'
            )
            ->join('groups', function ($group_join) {
                $group_join->on('groups.id', '=', 'page_roles.group_id')
                    ->whereNull('groups.deleted_at');
            })
            ->join('group_users', function ($group_users_join) use ($user) {
                $group_users_join->on('group_users.group_id', '=', 'page_roles.group_id')
                    ->where('group_users.user_id', $user->id)
                    ->whereNull('group_users.deleted_at');
            });
        if ($page_ids) {
            $page_role_query->whereIn('page_roles.page_id', $page_ids);
        }
        $page_role = $page_role_query->whereNull('page_roles.deleted_at')->get();

        //return $this->page_role;
        return $page_role;
    }
}
