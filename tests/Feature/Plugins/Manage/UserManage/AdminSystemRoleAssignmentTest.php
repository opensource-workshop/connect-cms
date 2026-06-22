<?php

namespace Tests\Feature\Plugins\Manage\UserManage;

use App\Enums\CsvCharacterCode;
use App\Models\Core\UsersRoles;
use App\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Tests\TestCase;

/**
 * ユーザ管理のシステム管理者権限付与制御を検証する。
 * 公開ルート経由で、画面更新・新規登録・CSVインポートの認可境界を守る。
 *
 * @runTestsInSeparateProcesses
 * @preserveGlobalState disabled
 */
class AdminSystemRoleAssignmentTest extends TestCase
{
    use RefreshDatabase;

    /**
     * ユーザ管理者が細工した更新リクエストでシステム管理者権限を付与できないことを守る。
     * 管理権限のチェックボックス表示に依存せず、サーバ側で拒否されることを検証する。
     *
     * @test
     */
    public function adminUserCannotGrantAdminSystemOnUserUpdate()
    {
        $admin_user = $this->createUserWithManageRole('admin_user');
        $target = User::factory()->create(['userid' => 'target-user']);

        $response = $this->actingAs($admin_user)->post("/manage/user/update/{$target->id}", [
            'name' => 'Target User',
            'userid' => 'target-user',
            'email' => 'target@example.com',
            'status' => 0,
            'columns_set_id' => 1,
            'manage' => [
                'admin_system' => 1,
                'admin_user' => 1,
            ],
        ]);

        $response->assertForbidden();
        $this->assertDatabaseMissing('users_roles', [
            'users_id' => $target->id,
            'target' => 'manage',
            'role_name' => 'admin_system',
        ]);
    }

    /**
     * システム管理者はユーザー更新でシステム管理者権限を付与できる仕様を守る。
     * 拒否条件が過剰になり、正当な管理操作まで止めないことを検証する。
     *
     * @test
     */
    public function adminSystemCanGrantAdminSystemOnUserUpdate()
    {
        $system_admin = $this->createUserWithManageRole('admin_system');
        $target = User::factory()->create(['userid' => 'target-user']);

        $response = $this->actingAs($system_admin)->post("/manage/user/update/{$target->id}", [
            'name' => 'Target User',
            'userid' => 'target-user',
            'email' => 'target@example.com',
            'status' => 0,
            'columns_set_id' => 1,
            'manage' => [
                'admin_system' => 1,
                'admin_user' => 1,
            ],
        ]);

        $response->assertRedirect("/manage/user/edit/{$target->id}");
        $this->assertDatabaseHas('users_roles', [
            'users_id' => $target->id,
            'target' => 'manage',
            'role_name' => 'admin_system',
        ]);
    }

    /**
     * ユーザ管理者が新規登録リクエストにシステム管理者権限を混入できないことを守る。
     * 登録処理でも更新処理と同じ権限境界が適用されることを検証する。
     *
     * @test
     */
    public function adminUserCannotGrantAdminSystemOnUserRegistration()
    {
        $admin_user = $this->createUserWithManageRole('admin_user');

        $response = $this->actingAs($admin_user)->post('/register', [
            'name' => 'New User',
            'userid' => 'new-user',
            'email' => 'new@example.com',
            'password' => 'password',
            'password_confirmation' => 'password',
            'status' => 0,
            'columns_set_id' => 1,
            'manage' => [
                'admin_system' => 1,
                'admin_user' => 1,
            ],
        ]);

        $response->assertForbidden();
        $this->assertDatabaseMissing('users', [
            'userid' => 'new-user',
        ]);
    }

    /**
     * ユーザ管理者がCSVインポートでシステム管理者権限を指定できないことを守る。
     * 一括登録でも直接POSTと同じく、システム管理者権限の付与を処理前に拒否する。
     *
     * @test
     */
    public function adminUserCannotGrantAdminSystemOnCsvImport()
    {
        $admin_user = $this->createUserWithManageRole('admin_user');
        $csv_file = UploadedFile::fake()->createWithContent(
            'users.csv',
            "id,ログインID,ユーザ名,グループ,メールアドレス,パスワード,権限,役割設定,状態\n" .
            ",csv-user,CSV User,,csv@example.com,password,admin_system,,0\n"
        );

        $response = $this->actingAs($admin_user)->post('/manage/user/uploadCsv', [
            'users_csv' => $csv_file,
            'columns_set_id' => 1,
            'character_code' => CsvCharacterCode::utf_8,
        ]);

        $response->assertSessionHasErrors('users_csv');
        $this->assertDatabaseMissing('users', [
            'userid' => 'csv-user',
        ]);
    }

    /**
     * ユーザ管理者がCSVインポートで既存のシステム管理者を更新できないことを守る。
     * パスワードや状態などの変更を含む一括更新でも、保護対象ユーザーの更新を拒否する。
     *
     * @test
     */
    public function adminUserCannotUpdateExistingAdminSystemOnCsvImport()
    {
        $admin_user = $this->createUserWithManageRole('admin_user');
        $system_admin = $this->createUserWithManageRole('admin_system', ['userid' => 'system-admin']);
        $csv_file = UploadedFile::fake()->createWithContent(
            'users.csv',
            "id,ログインID,ユーザ名,グループ,メールアドレス,パスワード,権限,役割設定,状態\n" .
            "{$system_admin->id},system-admin,Changed Name,,system@example.com,,admin_user,,0\n"
        );

        $response = $this->actingAs($admin_user)->post('/manage/user/uploadCsv', [
            'users_csv' => $csv_file,
            'columns_set_id' => 1,
            'character_code' => CsvCharacterCode::utf_8,
        ]);

        $response->assertSessionHasErrors('users_csv');
        $this->assertDatabaseHas('users', [
            'id' => $system_admin->id,
            'name' => $system_admin->name,
        ]);
    }

    /**
     * テストに必要な管理権限付きユーザーを作成する。
     */
    private function createUserWithManageRole(string $role_name, array $attributes = []): User
    {
        $user = User::factory()->create($attributes);
        UsersRoles::factory()->create([
            'users_id' => $user->id,
            'target' => 'manage',
            'role_name' => $role_name,
            'role_value' => 1,
        ]);

        return $user;
    }
}
