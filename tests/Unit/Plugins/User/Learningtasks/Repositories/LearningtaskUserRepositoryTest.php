<?php

namespace Tests\Unit\Plugins\User\Learningtasks\Repositories;

use App\Enums\RoleName;
use App\Models\Common\Group;
use App\Models\Common\GroupUser;
use App\Models\Common\Page;
use App\Models\Common\PageRole;
use App\Models\Core\UsersRoles;
use App\Models\User\Learningtasks\Learningtasks;
use App\Models\User\Learningtasks\LearningtasksPosts;
use App\Models\User\Learningtasks\LearningtasksUsers;
use App\Plugins\User\Learningtasks\Repositories\LearningtaskUserRepository;
use App\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Collection;
use Tests\TestCase;

class LearningtaskUserRepositoryTest extends TestCase
{
    use RefreshDatabase;

    /**
     * テスト対象のリポジトリインスタンス
     * @var LearningtaskUserRepository
     */
    private $repository;

    /**
     * 各テスト前のセットアップ
     */
    protected function setUp(): void
    {
        parent::setUp();
        $this->repository = new LearningtaskUserRepository();
    }

    /**
     * 配置ページのメンバーシップユーザ全員の学生を取得するテスト
     *
     * @test
     * @group learningtasks
     * @group learningtasks-repository
     */
    public function getStudentsReturnsPageMembers()
    {
        // Arrange: テストデータの準備 (student_join_flag = 2 のシナリオ)
        // メンバーシップページを作成する
        $page = Page::factory()->create(['membership_flag' => 1]);
        // グループを作成し、ページロールを作成する
        $group = Group::factory()->create();
        $page_role = PageRole::factory()->create(['page_id' => $page->id, 'group_id' => $group->id]);

        // 期待されるユーザ
        // user1, user2は学生のロールかつ、グループに所属する（=メンバーシップユーザとなる）
        $user1 = User::factory()->create();
        $user2 = User::factory()->create();

        // 除外されるユーザ
        // other_role_userはグループに所属するが、教員のロール
        // not_in_group_userはグループに所属しない学生
        $other_role_user = User::factory()->create();
        $not_in_group_user = User::factory()->create();

        // ロール設定
        UsersRoles::factory()->create(['users_id' => $user1->id, 'target' => 'original_role', 'role_name' => RoleName::student]);
        UsersRoles::factory()->create(['users_id' => $user2->id, 'target' => 'original_role', 'role_name' => RoleName::student]);
        UsersRoles::factory()->create(['users_id' => $other_role_user->id, 'target' => 'original_role', 'role_name' => RoleName::teacher]);
        UsersRoles::factory()->create(['users_id' => $not_in_group_user->id, 'target' => 'original_role', 'role_name' => RoleName::student]);
        // グループにユーザを追加する
        GroupUser::factory()->create(['group_id' => $group->id, 'user_id' => $user1->id]);
        GroupUser::factory()->create(['group_id' => $group->id, 'user_id' => $user2->id]);
        GroupUser::factory()->create(['group_id' => $group->id, 'user_id' => $other_role_user->id]);

        $learningtask = Learningtasks::factory()->create();
        // 配置ページのメンバーシップユーザ全員 student_join_flag = 2
        $post = LearningtasksPosts::factory()->create(
            ['learningtasks_id' => $learningtask->id, 'student_join_flag' => 2]
        );

        // Act: メソッドの実行
        $result_students = $this->repository->getStudents($post, $page);

        // Assert: 結果の検証
        $this->assertCount(2, $result_students, '対象グループの受講生が2人だけ取得されるべき');
        $this->assertEquals($user1->id, $result_students[0]->id, '期待される受講生1が含まれていること');
        $this->assertEquals($user2->id, $result_students[1]->id, '期待される受講生2が含まれていること');
        $this->assertFalse($result_students->contains('id', $other_role_user->id), '教員は含まれないこと');
        $this->assertFalse($result_students->contains('id', $not_in_group_user->id), 'グループ外の受講生は含まれないこと');
    }

    /**
     * 配置ページのメンバーシップユーザから選んだ学生を取得するテスト
     *
     * @test
     * @group learningtasks
     * @group learningtasks-repository
     */
    public function getStudentsReturnsSelectedMembers()
    {
        // Arrange: テストデータの準備 (student_join_flag = 3 のシナリオ)
        // メンバーシップページを作成する
        $page = Page::factory()->create(['membership_flag' => 1]);
        // グループを作成し、ページロールを作成する
        $group = Group::factory()->create();
        $page_role = PageRole::factory()->create(['page_id' => $page->id, 'group_id' => $group->id]);

        // 期待されるユーザ
        // user1, user2は学生のロールかつ、グループに所属する（=メンバーシップユーザとなる）
        $user1 = User::factory()->create();

        // 除外されるユーザ
        // not_selected_userはグループに所属するが、選択されていない学生
        // other_role_userはグループに所属するが、教員のロール
        // not_in_group_userはグループに所属しない学生
        $not_selected_user = User::factory()->create();
        $other_role_user = User::factory()->create();
        $not_in_group_user = User::factory()->create();

        // ロール設定
        UsersRoles::factory()->create(['users_id' => $user1->id, 'target' => 'original_role', 'role_name' => RoleName::student]);
        UsersRoles::factory()->create(['users_id' => $not_selected_user->id, 'target' => 'original_role', 'role_name' => RoleName::student]);
        UsersRoles::factory()->create(['users_id' => $other_role_user->id, 'target' => 'original_role', 'role_name' => RoleName::teacher]);
        UsersRoles::factory()->create(['users_id' => $not_in_group_user->id, 'target' => 'original_role', 'role_name' => RoleName::student]);
        // グループにユーザを追加する
        GroupUser::factory()->create(['group_id' => $group->id, 'user_id' => $user1->id]);
        GroupUser::factory()->create(['group_id' => $group->id, 'user_id' => $not_selected_user->id]);
        GroupUser::factory()->create(['group_id' => $group->id, 'user_id' => $other_role_user->id]);

        $learningtask = Learningtasks::factory()->create();
        // 配置ページのメンバーシップユーザから選ぶ
        $post = LearningtasksPosts::factory()->create(
            ['learningtasks_id' => $learningtask->id, 'student_join_flag' => 3]
        );
        LearningtasksUsers::factory()->create(
            ['post_id' => $post->id, 'user_id' => $user1->id, 'role_name' => RoleName::student]
        );
        // not_selected_userはメンバーシップユーザだが、選択されていない

        // Act: メソッドの実行
        $result_students = $this->repository->getStudents($post, $page);

        // Assert: 結果の検証
        $this->assertCount(1, $result_students, '選ばれた学生が1人だけ取得されるべき');
        $this->assertEquals($user1->id, $result_students[0]->id, '期待される受講生1が含まれていること');
        $this->assertFalse($result_students->contains('id', $not_selected_user->id), 'メンバーシップユーザだが選ばれていない学生が含まれないこと');
        $this->assertFalse($result_students->contains('id', $other_role_user->id), '教員は含まれないこと');
        $this->assertFalse($result_students->contains('id', $not_in_group_user->id), 'グループ外の受講生は含まれないこと');
    }

    /**
     * getStudents メソッド, 該当する学生がいない場合に空のコレクションを返すことを確認するテスト
     * @test
     * @group learningtasks
     * @group learningtasks-repository
     */
    public function getStudentsReturnsEmptyWhenNoMatchingStudents(): void
    {
        // Arrange: 該当する学生がいない状況を作る
        $page = Page::factory()->create();
        $post_flag2 = LearningtasksPosts::factory()->create(['student_join_flag' => 2]);
        $post_flag3 = LearningtasksPosts::factory()->create(['student_join_flag' => 3]);
        User::factory()->create(); // ユーザーを作成するが、グループやロール、直接リンクは設定しない

        // Act & Assert for flag = 2
        $result_flag2 = $this->repository->getStudents($post_flag2, $page);
        $this->assertCount(0, $result_flag2, 'Flag=2で該当者がいない場合、空のコレクションが返るべき');
        $this->assertInstanceOf(Collection::class, $result_flag2);

        // Act & Assert for flag = 3
        $result_flag3 = $this->repository->getStudents($post_flag3, $page);
        $this->assertCount(0, $result_flag3, 'Flag=3で該当者がいない場合、空のコレクションが返るべき');
        $this->assertInstanceOf(Collection::class, $result_flag3);
    }

    /**
     * 配置ページのメンバーシップユーザ全員の教員を取得するテスト
     *
     * @test
     * @group learningtasks
     * @group learningtasks-repository
     */
    public function getTeachersReturnsPageMembers()
    {
        // Arrange: テストデータの準備 (teacher_join_flag = 2 のシナリオ)
        // メンバーシップページを作成する
        $page = Page::factory()->create(['membership_flag' => 1]);
        // グループを作成し、ページロールを作成する
        $group = Group::factory()->create();
        $page_role = PageRole::factory()->create(['page_id' => $page->id, 'group_id' => $group->id]);

        // 期待されるユーザ
        // user1, user2は教員のロールかつ、グループに所属する（=メンバーシップユーザとなる）
        $user1 = User::factory()->create();
        $user2 = User::factory()->create();

        // 除外されるユーザ
        // other_role_userはグループに所属するが、学生のロール
        // not_in_group_userはグループに所属しない教員
        $other_role_user = User::factory()->create();
        $not_in_group_user = User::factory()->create();

        // ロール設定
        UsersRoles::factory()->create(['users_id' => $user1->id, 'target' => 'original_role', 'role_name' => RoleName::teacher]);
        UsersRoles::factory()->create(['users_id' => $user2->id, 'target' => 'original_role', 'role_name' => RoleName::teacher]);
        UsersRoles::factory()->create(['users_id' => $other_role_user->id, 'target' => 'original_role', 'role_name' => RoleName::student]);
        UsersRoles::factory()->create(['users_id' => $not_in_group_user->id, 'target' => 'original_role', 'role_name' => RoleName::teacher]);
        // グループにユーザを追加する
        GroupUser::factory()->create(['group_id' => $group->id, 'user_id' => $user1->id]);
        GroupUser::factory()->create(['group_id' => $group->id, 'user_id' => $user2->id]);
        GroupUser::factory()->create(['group_id' => $group->id, 'user_id' => $other_role_user->id]);

        $learningtask = Learningtasks::factory()->create();
        // 配置ページのメンバーシップユーザ全員 teacher_join_flag = 2
        $post = LearningtasksPosts::factory()->create(
            ['learningtasks_id' => $learningtask->id, 'teacher_join_flag' => 2]
        );

        // Act: メソッドの実行
        $result_students = $this->repository->getTeachers($post, $page);

        // Assert: 結果の検証
        $this->assertCount(2, $result_students, '対象グループの教員が2人だけ取得されるべき');
        $this->assertEquals($user1->id, $result_students[0]->id, '期待される教員1が含まれていること');
        $this->assertEquals($user2->id, $result_students[1]->id, '期待される教員2が含まれていること');
        $this->assertFalse($result_students->contains('id', $other_role_user->id), '学生は含まれないこと');
        $this->assertFalse($result_students->contains('id', $not_in_group_user->id), 'グループ外の教員は含まれないこと');
    }

    /**
     * 配置ページのメンバーシップユーザから選んだ教員を取得するテスト
     *
     * @test
     * @group learningtasks
     * @group learningtasks-repository
     */
    public function getTeachersReturnsSelectedMembers()
    {
        // Arrange: テストデータの準備 (teacher_join_flag = 3 のシナリオ)
        // メンバーシップページを作成する
        $page = Page::factory()->create(['membership_flag' => 1]);
        // グループを作成し、ページロールを作成する
        $group = Group::factory()->create();
        $page_role = PageRole::factory()->create(['page_id' => $page->id, 'group_id' => $group->id]);

        // 期待されるユーザ
        // user1は教員のロールかつ、グループに所属する（=メンバーシップユーザとなる）
        $user1 = User::factory()->create();

        // 除外されるユーザ
        // not_selected_userはグループに所属するが、選択されていない教員
        // other_role_userはグループに所属するが、教員のロール
        // not_in_group_userはグループに所属しない教員
        $not_selected_user = User::factory()->create();
        $other_role_user = User::factory()->create();
        $not_in_group_user = User::factory()->create();

        // ロール設定
        UsersRoles::factory()->create(['users_id' => $user1->id, 'target' => 'original_role', 'role_name' => RoleName::teacher]);
        UsersRoles::factory()->create(['users_id' => $not_selected_user->id, 'target' => 'original_role', 'role_name' => RoleName::teacher]);
        UsersRoles::factory()->create(['users_id' => $other_role_user->id, 'target' => 'original_role', 'role_name' => RoleName::student]);
        UsersRoles::factory()->create(['users_id' => $not_in_group_user->id, 'target' => 'original_role', 'role_name' => RoleName::teacher]);
        // グループにユーザを追加する
        GroupUser::factory()->create(['group_id' => $group->id, 'user_id' => $user1->id]);
        GroupUser::factory()->create(['group_id' => $group->id, 'user_id' => $not_selected_user->id]);
        GroupUser::factory()->create(['group_id' => $group->id, 'user_id' => $other_role_user->id]);

        $learningtask = Learningtasks::factory()->create();
        // 配置ページのメンバーシップユーザから選ぶ
        $post = LearningtasksPosts::factory()->create(
            ['learningtasks_id' => $learningtask->id, 'teacher_join_flag' => 3]
        );
        LearningtasksUsers::factory()->create(
            ['post_id' => $post->id, 'user_id' => $user1->id, 'role_name' => RoleName::teacher]
        );
        // not_selected_userはメンバーシップユーザだが、選択されていない

        // Act: メソッドの実行
        $result_students = $this->repository->getTeachers($post, $page);

        // Assert: 結果の検証
        $this->assertCount(1, $result_students, '選ばれた学生が1人だけ取得されるべき');
        $this->assertEquals($user1->id, $result_students[0]->id, '期待される受講生1が含まれていること');
        $this->assertFalse($result_students->contains('id', $not_selected_user->id), 'メンバーシップユーザだが選ばれていない学生が含まれないこと');
        $this->assertFalse($result_students->contains('id', $other_role_user->id), '教員は含まれないこと');
        $this->assertFalse($result_students->contains('id', $not_in_group_user->id), 'グループ外の受講生は含まれないこと');
    }

    /**
     * getTeachers メソッド, 該当する教員がいない場合に空のコレクションを返すことを確認するテスト
     * @test
     * @group learningtasks
     * @group learningtasks-repository
     */
    public function getTeachersReturnsEmptyWhenNoMatchingTeacher(): void
    {
        // Arrange: 該当する教員がいない状況を作る
        $page = Page::factory()->create();
        $post_flag2 = LearningtasksPosts::factory()->create(['teacher_join_flag' => 2]);
        $post_flag3 = LearningtasksPosts::factory()->create(['teacher_join_flag' => 3]);
        User::factory()->create(); // ユーザーを作成するが、グループやロール、直接リンクは設定しない

        // Act & Assert for flag = 2
        $result_flag2 = $this->repository->getTeachers($post_flag2, $page);
        $this->assertCount(0, $result_flag2, 'Flag=2で該当者がいない場合、空のコレクションが返るべき');
        $this->assertInstanceOf(Collection::class, $result_flag2);

        // Act & Assert for flag = 3
        $result_flag3 = $this->repository->getTeachers($post_flag3, $page);
        $this->assertCount(0, $result_flag3, 'Flag=3で該当者がいない場合、空のコレクションが返るべき');
        $this->assertInstanceOf(Collection::class, $result_flag3);
    }
}
