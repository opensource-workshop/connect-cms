<?php

namespace Tests\Feature\Plugins\Manage\PageManage;

use App\Models\Common\Group;
use App\Models\Common\GroupUser;
use App\Models\Core\UsersRoles;
use App\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * ページ権限一覧の参加ユーザ表示を検証する。
 *
 * 管理者の画面応答を通じて、ユーザ名をHTMLとして解釈しないことと、
 * 従来の区切り文字をHTMLとして直接出力しないことを確認する。
 *
 * @runTestsInSeparateProcesses
 * @preserveGlobalState disabled
 */
class PageManageRoleListSecurityTest extends TestCase
{
    use RefreshDatabase;

    /**
     * 各テスト前のセットアップ
     */
    protected function setUp(): void
    {
        parent::setUp();
        $this->seed();
    }

    /**
     * admin_page権限を持つユーザーを作成する。
     */
    private function createPageAdminUser(): User
    {
        $user = User::factory()->create();
        UsersRoles::factory()->create([
            'users_id' => $user->id,
            'target' => 'manage',
            'role_name' => 'admin_page',
            'role_value' => 1,
        ]);

        return $user;
    }

    /**
     * 指定した名前のユーザーをグループに参加させる。
     */
    private function createGroupUserWithName(Group $group, string $name): User
    {
        $user = User::factory()->create([
            'name' => $name,
        ]);
        GroupUser::factory()->create([
            'group_id' => $group->id,
            'user_id' => $user->id,
        ]);

        return $user;
    }

    /**
     * 参加ユーザ名と区切り文字はHTMLとして解釈されない形でレスポンスに含まれること。
     *
     * @test
     */
    public function roleListEscapesGroupUserNamesAndSeparators(): void
    {
        $admin = $this->createPageAdminUser();
        $group = Group::factory()->create([
            'name' => 'security-test-group',
            'display_sequence' => 1,
        ]);
        $malicious_name = '<img src=x onerror=alert(1)>';
        $comma_name = '山田,太郎';

        $this->createGroupUserWithName($group, $malicious_name);
        $this->createGroupUserWithName($group, $comma_name);

        $response = $this->actingAs($admin)->get('/manage/page/roleList');

        $response->assertOk();
        $response->assertDontSee($malicious_name, false);
        $response->assertSee($malicious_name);
        $response->assertSee($comma_name);
        $response->assertSee('&lt;br&gt;', false);
    }
}
