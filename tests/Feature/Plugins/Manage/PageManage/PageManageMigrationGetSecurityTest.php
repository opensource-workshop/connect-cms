<?php

namespace Tests\Feature\Plugins\Manage\PageManage;

use App\Enums\WebsiteType;
use App\Models\Common\Page;
use App\Models\Core\UsersRoles;
use App\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

/**
 * 外部ページ移行（URL入力）のセキュリティ検証。
 *
 * @runTestsInSeparateProcesses
 * @preserveGlobalState disabled
 */
class PageManageMigrationGetSecurityTest extends TestCase
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
     * SSRFに悪用されうる内部/予約IP URLは取り込み前に拒否されること。
     *
     * @test
     * @dataProvider blockedMigrationSourceUrlProvider
     */
    public function internalOrReservedSourceUrlIsRejectedBeforeFetching(string $source_system, string $url): void
    {
        Storage::fake();

        $admin = $this->createPageAdminUser();
        $page = Page::query()->firstOrFail();

        $response = $this->actingAs($admin)->post("/manage/page/migrationGet/{$page->id}", [
            'source_system' => $source_system,
            'url' => $url,
            'destination_page_id' => $page->id,
        ]);

        $response->assertStatus(302);
        $response->assertRedirect("/manage/page/migrationOrder/{$page->id}");
        $response->assertSessionHasErrors(['url']);

        Storage::disk(config('filesystems.default'))->assertMissing('migration/import/migration_last_request_time.txt');
        Storage::disk(config('filesystems.default'))->assertMissing("migration/import/pages/{$page->id}/frame_0001.html");
    }

    /**
     * @return array<string, array{0: string, 1: string}>
     */
    public function blockedMigrationSourceUrlProvider(): array
    {
        return [
            'html_localhost_loopback' => [WebsiteType::html, 'http://127.0.0.1/private'],
            'html_link_local_metadata' => [WebsiteType::html, 'http://169.254.169.254/latest/meta-data'],
            'nc3_localhost_loopback' => [WebsiteType::netcommons3, 'http://127.0.0.1/nc3/index.html'],
            'nc3_link_local_metadata' => [WebsiteType::netcommons3, 'http://169.254.169.254/nc3/index.html'],
        ];
    }
}
