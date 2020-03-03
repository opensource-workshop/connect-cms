<?php

namespace App\Models\Common;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Log;

use App\Models\Common\BucketsRoles;

class Buckets extends Model
{
    /**
     * create()やupdate()で入力を受け付ける ホワイトリスト
     */
    protected $fillable = [
        'id',
        'bucket_name',
        'plugin_name',
    ];

    // Buckets のrole
    private $buckets_roles = null;

    /**
     *  投稿権限データをrole の配列にする。
     */
    private function editArrayBucketsRoles()
    {
        if (empty($this->buckets_roles)) {
            return array();
        }
        $return_roles = array();
        foreach($this->buckets_roles as $buckets_role) {
            if ($buckets_role->post_flag == 1) {
                $return_roles[] = $buckets_role->role;
            }
        }
        return $return_roles;
    }
    /**
     *  投稿権限の確認
     */
    public function getBucketsRoles()
    {
//echo $this->id;
        // すでに読み込み済みならば、読み込み済みデータを返す
        // 権限更新の際に、更新前データを保持 ＞ 更新でデータ変わる ＞ 古い状態を使用になるので、毎回、読む形に変更
        //if ($this->buckets_roles) {
        //    return $this->editArrayBucketsRoles();
        //}

        $this->buckets_roles = BucketsRoles::where('buckets_id', $this->id)
                                           ->get();
        return $this->editArrayBucketsRoles();
    }

    /**
     *  投稿権限の保持確認
     */
    public function canPost($role)
    {
        // すでに読み込み済みならば、読み込み済みデータを返す
        // 権限更新の際に、更新前データを保持 ＞ 更新でデータ変わる ＞ 古い状態を使用になるので、毎回、読む形に変更
        //if ($this->buckets_roles == null) {
        //    $this->getBucketsRoles();
        //}

        // Buckets に対するrole の取得
        $this->getBucketsRoles();

        // 渡された権限がバケツに投稿できる権限かどうかのチェック
        foreach($this->buckets_roles as $buckets_role) {
            if ($buckets_role->role == $role && $buckets_role->post_flag == 1) {
                return true;
            }
        }
        return false;

//        $roles = explode(',', $this->post_role);
//        return in_array($role, $roles);
    }

    /**
     *  ユーザーの投稿権限の有無確認
     */
    public function canPostUser($user)
    {
        // ユーザーの持つBASE権限を全て確認し、ひとつでも投稿権限があれば、投稿可能となる。
        if (empty($user)) {
            return false;
        }

        if (!array_key_exists('base', (array)$user->user_roles)) {
            return false;
        }

        $user_roles_base = $user->user_roles['base'];
        if (empty($user_roles_base)) {
            return false;
        }

        foreach($user_roles_base as $user_role => $value) {
            // 記事管理者権限、プラグイン配置権限があれば、投稿可能
            if ($user_role == 'role_article_admin' || $user_role == 'role_arrangement') {
                return true;
            }

            // 保持している権限の内、ひとつでも投稿可能なら、投稿OK
            if ($value == 1) {
                $can_post = $this->canPost($user_role);
                if ($can_post == true) {
                    return true;
                }
            }
        }
        return false;
    }

    /**
     *  承認の有無確認
     */
    public function needApproval($role)
    {
        // Buckets に対するrole の取得
        // 権限更新の際に、更新前データを保持 ＞ 更新でデータ変わる ＞ 古い状態を使用になるので、毎回、読む形に変更
        //if ($this->buckets_roles == null) {
        //    $this->getBucketsRoles();
        //}

        // Buckets に対するrole の取得
        $this->getBucketsRoles();

        // 渡された権限が承認が必要かどうかのチェック
        foreach($this->buckets_roles as $buckets_role) {
            if ($buckets_role->role == $role && $buckets_role->approval_flag == 1) {
                return true;
            }
        }
        return false;
    }

    /**
     *  ユーザーの承認の有無確認
     */
    public function needApprovalUser($user)
    {
        // ユーザーの持つBASE権限を全て確認し、全てで承認が必要な場合、承認が必要となる。
        // (権限のひとつでも、承認が不要な場合は、承認は不要になる)
        if (empty($user)) {
            return false;
        }

        $user_roles_base = $user->user_roles['base'];
        if (empty($user_roles_base)) {
            return false;
        }

        foreach($user_roles_base as $user_role => $value) {
            // 記事管理者権限、プラグイン配置権限、記事承認権限があれば、承認不要
            if ($user_role == 'role_article_admin' || $user_role == 'role_arrangement' || $user_role == 'role_approval') {
                return false;
            }

            // 保持している権限の内、ひとつでも承認不要なら、承認不要
            if ($value == 1) {
                $role_approval = $this->needApproval($user_role);
                if ($role_approval == false) {
                    return false;
                }
            }
        }
        return true;
    }
}
