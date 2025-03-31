<?php

namespace Tests\Unit\Traits\Learningtasks;

use Tests\TestCase;
use App\Enums\LearningtaskUseFunction;
use App\Enums\RoleName;
use App\Models\Common\Group;
use App\Models\Common\GroupUser;
use App\Models\User\Learningtasks\LearningtasksPosts;
use App\Models\Common\Page;
use App\Models\Common\PageRole;
use App\Models\Core\UsersRoles;
use App\Models\User\Learningtasks\Learningtasks;
use App\Models\User\Learningtasks\LearningtasksUsers;
use App\Models\User\Learningtasks\LearningtasksUseSettings;
use App\Traits\Learningtasks\LearningtaskPostTrait;
use App\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

class LearningtaskPostTraitTest extends TestCase
{
    use RefreshDatabase;

    /**
     * @var App\Traits\Learningtasks\LearningtaskPostTrait
     */
    protected $trait = null;

    protected function setUp(): void
    {
        parent::setUp();
    }

    /**
     * モックのセットアップ
     *
     * @param LearningtasksPosts $learningtask_post
     * @param Page $page
     */
    private function setupTrait($learningtask_post, $page)
    {
        $this->trait = new class($learningtask_post, $page) {
            use LearningtaskPostTrait;
            public function __construct($learningtask_post, $page)
            {
                $this->learningtask_post = $learningtask_post;
                $this->page = $page;
            }
        };
    }

    /**
     * 設定が有効かどうかのテスト
     */
    public function testIsSettingEnabled()
    {
        // データ準備
        $page = Page::factory()->create();
        $learningtask = Learningtasks::factory()->create();
        $learningtask_post = LearningtasksPosts::factory()->create(['learningtasks_id' => $learningtask->id]);

        // 課題と科目に設定する
        // 科目の設定を優先される = post_id が設定されている設定が優先される
        LearningtasksUseSettings::factory()->create([
            'learningtasks_id' => $learningtask->id,
            'use_function' => LearningtaskUseFunction::use_report_comment,
            'value' => 'off'
        ]);
        LearningtasksUseSettings::factory()->create([
            'learningtasks_id' => $learningtask->id,
            'use_function' => LearningtaskUseFunction::use_report_file,
            'value' => 'on'
        ]);
        LearningtasksUseSettings::factory()->create([
            'learningtasks_id' => $learningtask->id,
            'post_id' => $learningtask_post->id,
            'use_function' => LearningtaskUseFunction::use_report_comment,
            'value' => 'on' #
        ]);
        LearningtasksUseSettings::factory()->create([
            'learningtasks_id' => $learningtask->id,
            'post_id' => $learningtask_post->id,
            'use_function' => LearningtaskUseFunction::use_report_file,
            'value' => 'off'
        ]);

        // 課題のみに設定する
        LearningtasksUseSettings::factory()->create([
            'learningtasks_id' => $learningtask->id,
            'use_function' => LearningtaskUseFunction::use_report_evaluate,
            'value' => 'on'
        ]);
        LearningtasksUseSettings::factory()->create([
            'learningtasks_id' => $learningtask->id,
            'use_function' => LearningtaskUseFunction::use_report_evaluate_comment,
            'value' => 'off'
        ]);

        // Traiのモックを作成する
        $this->setupTrait($learningtask_post, $page);

        // テスト実行
        $this->assertTrue($this->trait->isSettingEnabled(LearningtaskUseFunction::use_report_comment));
        $this->assertFalse($this->trait->isSettingEnabled(LearningtaskUseFunction::use_report_file));
        $this->assertTrue($this->trait->isSettingEnabled(LearningtaskUseFunction::use_report_evaluate));
        $this->assertFalse($this->trait->isSettingEnabled(LearningtaskUseFunction::use_report_evaluate_comment));
        $this->assertFalse($this->trait->isSettingEnabled(LearningtaskUseFunction::use_examination)); // 未登録の設定はfalseとなる
    }

    /**
     * 学生ユーザーを取得するテスト
     */
    public function testFetchStudentUsers()
    {
        // データ準備
        // メンバーシップページを作成する
        $page = Page::factory()->create(['membership_flag' => 1]);
        // グループを作成し、ページロールを作成する
        $group = Group::factory()->create();
        $page_role = PageRole::factory()->create(['page_id' => $page->id, 'group_id' => $group->id]);

        // 学生ユーザを4人作成する
        // user1, user2, user3はグループに所属する = メンバーシップユーザとなる
        // user4はグループに所属しない = メンバーシップユーザでない
        $user1 = User::factory()->create();
        $user2 = User::factory()->create();
        $user3 = User::factory()->create();
        $user4 = User::factory()->create();
        UsersRoles::factory()->create(['users_id' => $user1->id, 'target' => 'original_role', 'role_name' => RoleName::student]);
        UsersRoles::factory()->create(['users_id' => $user2->id, 'target' => 'original_role', 'role_name' => RoleName::student]);
        UsersRoles::factory()->create(['users_id' => $user3->id, 'target' => 'original_role', 'role_name' => RoleName::student]);
        UsersRoles::factory()->create(['users_id' => $user4->id, 'target' => 'original_role', 'role_name' => RoleName::student]);
        // グループにユーザを追加する
        GroupUser::factory()->create(['group_id' => $group->id, 'user_id' => $user1->id]);
        GroupUser::factory()->create(['group_id' => $group->id, 'user_id' => $user2->id]);
        GroupUser::factory()->create(['group_id' => $group->id, 'user_id' => $user3->id]);

        $learningtask = Learningtasks::factory()->create();
        // 配置ページのメンバーシップユーザ全員
        $learningtask_post_membership_students = LearningtasksPosts::factory()->create(['learningtasks_id' => $learningtask->id, 'student_join_flag' => 2]);
        // 配置ページのメンバーシップユーザから選ぶ
        $learningtask_post_students_selected = LearningtasksPosts::factory()->create(['learningtasks_id' => $learningtask->id, 'student_join_flag' => 3]);
        LearningtasksUsers::factory()->create(['post_id' => $learningtask_post_students_selected->id, 'user_id' => $user1->id, 'role_name' => RoleName::student]);
        LearningtasksUsers::factory()->create(['post_id' => $learningtask_post_students_selected->id, 'user_id' => $user2->id, 'role_name' => RoleName::student]);
        // user3はメンバーシップユーザだが、選択されていない
        // user4はメンバーシップユーザでないし、選択もされていない

        // テスト実行
        // 配置ページのメンバーシップユーザ全員
        $this->setupTrait($learningtask_post_membership_students, $page);
        $students = $this->trait->fetchStudentUsers();
        $this->assertCount(3, $students);
        $this->assertEquals($user1->id, $students[0]->id);
        $this->assertEquals($user2->id, $students[1]->id);
        $this->assertEquals($user3->id, $students[2]->id);
        // 配置ページのメンバーシップユーザから選ぶ
        $this->setupTrait($learningtask_post_students_selected, $page);
        $students = $this->trait->fetchStudentUsers();
        $this->assertCount(2, $students);
        $this->assertEquals($user1->id, $students[0]->id);
        $this->assertEquals($user2->id, $students[1]->id);
    }

    /**
     * 教員ユーザーを取得するテスト
     */
    public function testFetchTeacherUsers()
    {
        // データ準備
        // メンバーシップページを作成する
        $page = Page::factory()->create(['membership_flag' => 1]);
        // グループを作成し、ページロールを作成する
        $group = Group::factory()->create();
        $page_role = PageRole::factory()->create(['page_id' => $page->id, 'group_id' => $group->id]);

        // 教員ユーザを4人作成する
        // user1, user2, user3はグループに所属する = メンバーシップユーザとなる
        // user4はグループに所属しない = メンバーシップユーザでない
        $user1 = User::factory()->create();
        $user2 = User::factory()->create();
        $user3 = User::factory()->create();
        $user4 = User::factory()->create();
        UsersRoles::factory()->create(['users_id' => $user1->id, 'target' => 'original_role', 'role_name' => RoleName::teacher]);
        UsersRoles::factory()->create(['users_id' => $user2->id, 'target' => 'original_role', 'role_name' => RoleName::teacher]);
        UsersRoles::factory()->create(['users_id' => $user3->id, 'target' => 'original_role', 'role_name' => RoleName::teacher]);
        UsersRoles::factory()->create(['users_id' => $user4->id, 'target' => 'original_role', 'role_name' => RoleName::teacher]);
        // グループにユーザを追加する
        GroupUser::factory()->create(['group_id' => $group->id, 'user_id' => $user1->id]);
        GroupUser::factory()->create(['group_id' => $group->id, 'user_id' => $user2->id]);
        GroupUser::factory()->create(['group_id' => $group->id, 'user_id' => $user3->id]);

        $learningtask = Learningtasks::factory()->create();
        // 配置ページのメンバーシップユーザ全員
        $learningtask_post_membership_teachers = LearningtasksPosts::factory()->create(['learningtasks_id' => $learningtask->id, 'teacher_join_flag' => 2]);
        // 配置ページのメンバーシップユーザから選ぶ
        $learningtask_post_teachers_selected = LearningtasksPosts::factory()->create(['learningtasks_id' => $learningtask->id, 'teacher_join_flag' => 3]);
        LearningtasksUsers::factory()->create(['post_id' => $learningtask_post_teachers_selected->id, 'user_id' => $user1->id, 'role_name' => RoleName::teacher]);
        LearningtasksUsers::factory()->create(['post_id' => $learningtask_post_teachers_selected->id, 'user_id' => $user2->id, 'role_name' => RoleName::teacher]);
        // user3はメンバーシップユーザだが、選択されていない
        // user4はメンバーシップユーザでないし、選択もされていない

        // テスト実行
        // 配置ページのメンバーシップユーザ全員
        $this->setupTrait($learningtask_post_membership_teachers, $page);
        $teachers = $this->trait->fetchTeacherUsers();
        $this->assertCount(3, $teachers);
        $this->assertEquals($user1->id, $teachers[0]->id);
        $this->assertEquals($user2->id, $teachers[1]->id);
        $this->assertEquals($user3->id, $teachers[2]->id);
        // 配置ページのメンバーシップユーザから選ぶ
        $this->setupTrait($learningtask_post_teachers_selected, $page);
        $teachers = $this->trait->fetchTeacherUsers();
        $this->assertCount(2, $teachers);
        $this->assertEquals($user1->id, $teachers[0]->id);
        $this->assertEquals($user2->id, $teachers[1]->id);
    }
}
