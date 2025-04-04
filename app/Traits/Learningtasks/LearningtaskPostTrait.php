<?php

namespace App\Traits\Learningtasks;

use App\Enums\RoleName;
use App\Models\Common\Page;
use App\Models\Common\PageRole;
use App\Models\User\Learningtasks\LearningtasksPosts;
use App\User;

trait LearningtaskPostTrait
{
    /**
     * 科目
     * @var \App\Models\User\Learningtasks\LearningtasksPosts|null
     */
    protected ?LearningtasksPosts $learningtask_post = null;

    /**
     * 配置ページ
     * @var \App\Models\Common\Page|null
     */
    protected ?Page $page = null;

    /**
     * 設定が有効であるか
     * @param string $setting_name 設定名
     * @return bool 有効であるか
     */
    public function isSettingEnabled(string $setting_name): bool
    {
        // 科目に設定されている値を優先する
        $setting = $this->learningtask_post->post_settings->where('use_function', $setting_name)->first();
        if ($setting) {
            return $setting->value === 'on';
        }

        // 科目の設定がない場合は、課題管理の設定を参照する
        $setting = $this->learningtask_post->learningtask->learningtask_settings->where('use_function', $setting_name)->first();
        if ($setting) {
            return $setting->value === 'on';
        }
        return false;
    }

    /**
     * 科目に設定されている受講生を取得する
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public function fetchStudentUsers(): \Illuminate\Database\Eloquent\Collection
    {
        // student_join_flag = 0: 未使用
        // student_join_flag = 1: 未使用
        // student_join_flag = 2: 配置ページのメンバーシップユーザ全員
        // student_join_flag = 3: 配置ページのメンバーシップユーザから選ぶ

        // 配置ページのメンバーシップユーザ全員
        if ($this->learningtask_post->student_join_flag == 2) {
            return $this->fetchMembershipUsers(RoleName::student);
        }

        return $this->fetchLearningtaskPostStudents();
    }

    /**
     * 科目に設定されている教員を取得する
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public function fetchTeacherUsers(): \Illuminate\Database\Eloquent\Collection
    {
        // teacher_join_flag = 0: 未使用
        // teacher_join_flag = 1: 未使用
        // teacher_join_flag = 2: 配置ページのメンバーシップユーザ全員
        // teacher_join_flag = 3: 配置ページのメンバーシップユーザから選ぶ

        // 配置ページのメンバーシップユーザ全員
        if ($this->learningtask_post->teacher_join_flag == 2) {
            return $this->fetchMembershipUsers(RoleName::teacher);
        }

        return $this->fetchLearningtaskPostTeachers();
    }

    /**
     * 配置ページのメンバーシップユーザの受講生を全員を取得する
     * @param string $role_name 役割名
     * @return \Illuminate\Database\Eloquent\Collection
     */
    private function fetchMembershipUsers(string $role_name): \Illuminate\Database\Eloquent\Collection
    {
        // メンバーシップ設定は親ページから継承している場合がある
        $membership_page = $this->page->getInheritMembershipPage();
        $group_ids = PageRole::select('group_id')
            ->where('page_id', optional($membership_page)->id)
            ->groupBy('group_id')
            ->get()
            ->pluck('group_id');
        return User::query()
            ->whereHas('group_users', function ($query) use ($group_ids) {
                $query->whereIn('group_id', $group_ids);
            })
            ->whereExists(function ($query) use ($role_name) {
                $query->select(\DB::raw(1))
                    ->from('users_roles')
                    ->whereRaw('users_roles.users_id = users.id')
                    ->where('users_roles.target', '=', 'original_role')
                    ->where('users_roles.role_name', '=', $role_name);
            })
            ->orderBy('users.id')
            ->get();
    }

    /**
     * 科目に設定された受講生を取得する
     * @return \Illuminate\Database\Eloquent\Collection
     */
    private function fetchLearningtaskPostStudents(): \Illuminate\Database\Eloquent\Collection
    {
        return User::whereIn('id', $this->learningtask_post->students->pluck('user_id'))->orderBy('id')->get();
    }

    /**
     * 科目に設定された教員を取得する
     * @return \Illuminate\Database\Eloquent\Collection
     */
    private function fetchLearningtaskPostTeachers(): \Illuminate\Database\Eloquent\Collection
    {
        return User::whereIn('id', $this->learningtask_post->teachers->pluck('user_id'))->orderBy('id')->get();
    }
}
