<?php

namespace App\Plugins\User\Learningtasks\Repositories;

use App\Enums\RoleName;
use App\Models\Common\Page;
use App\Models\Common\PageRole;
use App\Models\User\Learningtasks\LearningtasksPosts;
use App\User;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\DB; // DB ファサードを利用

/**
 * 課題管理に関連するユーザー（受講生、教員）を取得するリポジトリクラス
 */
class LearningtaskUserRepository
{
    /**
     * 指定された課題投稿に関連する受講生を取得する
     *
     * 課題投稿の student_join_flag 設定に基づいて取得ロジックを分岐する。
     *
     * @param LearningtasksPosts $learningtask_post 対象の課題投稿
     * @param Page $page 課題が配置されているページ
     * @return Collection 受講生ユーザーモデルのコレクション
     */
    public function getStudents(LearningtasksPosts $learningtask_post, Page $page): Collection
    {
        // student_join_flag = 2: 配置ページのメンバーシップユーザー全員
        if ($learningtask_post->student_join_flag == 2) {
            // 配置ページのメンバーシップユーザーから受講生ロールを持つユーザーを取得
            return $this->fetchMembershipUsers($page, RoleName::student);
        }

        // student_join_flag = 3 (またはその他): 課題投稿に直接紐づけられたユーザー
        return $this->fetchLearningtaskPostStudents($learningtask_post);
    }

    /**
     * 指定された課題投稿に関連する教員を取得する
     *
     * 課題投稿の teacher_join_flag 設定に基づいて取得ロジックを分岐する。
     *
     * @param LearningtasksPosts $learningtask_post 対象の課題投稿
     * @param Page $page 課題が配置されているページ
     * @return Collection 教員ユーザーモデルのコレクション
     */
    public function getTeachers(LearningtasksPosts $learningtask_post, Page $page): Collection
    {
        // teacher_join_flag = 2: 配置ページのメンバーシップユーザー全員
        if ($learningtask_post->teacher_join_flag == 2) {
             // 配置ページのメンバーシップユーザーから教員ロールを持つユーザーを取得
            return $this->fetchMembershipUsers($page, RoleName::teacher);
        }

        // teacher_join_flag = 3 (またはその他): 課題投稿に直接紐づけられたユーザー
        return $this->fetchLearningtaskPostTeachers($learningtask_post);
    }

    /**
     * 配置ページのメンバーシップユーザー（指定ロール）を取得する
     * (Traitから移植した private ヘルパーメソッド)
     *
     * @param Page $page 配置ページ
     * @param string $role_name ロール名 (RoleName enum の値)
     * @return Collection
     */
    private function fetchMembershipUsers(Page $page, string $role_name): Collection
    {
        // メンバーシップを継承している親ページを取得
        $membership_page = $page->getInheritMembershipPage();

        // メンバーシップページに関連づくグループIDを取得
        $group_ids = PageRole::select('group_id')
            ->where('page_id', optional($membership_page)->id) // 親ページがない場合も考慮
            ->groupBy('group_id')
            ->get()
            ->pluck('group_id');

        // グループがなければ空のコレクションを返す
        if ($group_ids->isEmpty()) {
            return new Collection();
        }

        // 該当グループに所属し、かつ指定されたロールを持つユーザーを取得
        return User::query()
            ->whereHas('group_users', function ($query) use ($group_ids) {
                $query->whereIn('group_id', $group_ids);
            })
            ->whereExists(function ($query) use ($role_name) {
                $query->select(DB::raw(1))
                    ->from('users_roles')
                    ->whereRaw('users_roles.users_id = users.id')
                    ->where('users_roles.target', '=', 'original_role') // 元々の役割
                    ->where('users_roles.role_name', '=', $role_name);
            })
            ->orderBy('users.id')
            ->get();
    }

    /**
     * 課題投稿に直接紐づけられた受講生を取得する
     *
     * @param LearningtasksPosts $learningtask_post
     * @return Collection
     */
    private function fetchLearningtaskPostStudents(LearningtasksPosts $learningtask_post): Collection
    {
        // 'students' リレーションからユーザーIDを取得 (リレーションが存在する前提)
        $student_user_ids = $learningtask_post->students->pluck('user_id');

        if ($student_user_ids->isEmpty()) {
            return new Collection();
        }

        return User::whereIn('id', $student_user_ids)->orderBy('id')->get();
    }

    /**
     * 課題投稿に直接紐づけられた教員を取得する
     *
     * @param LearningtasksPosts $learningtask_post
     * @return Collection
     */
    private function fetchLearningtaskPostTeachers(LearningtasksPosts $learningtask_post): Collection
    {
        // 'teachers' リレーションからユーザーIDを取得 (リレーションが存在する前提)
        $teacher_user_ids = $learningtask_post->teachers->pluck('user_id');

        if ($teacher_user_ids->isEmpty()) {
            return new Collection();
        }

        return User::whereIn('id', $teacher_user_ids)->orderBy('id')->get();
    }
}
