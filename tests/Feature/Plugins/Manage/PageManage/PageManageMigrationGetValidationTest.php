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
 * 外部ページ移行（URL入力）の正常系バリデーション検証。
 *
 * @runTestsInSeparateProcesses
 * @preserveGlobalState disabled
 */
class PageManageMigrationGetValidationTest extends TestCase
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
     * グローバルHTTP URL は受け付けられ、取り込みリクエスト時刻が記録されること。
     *
     * @test
     */
    public function globalHttpUrlCanPassValidationAndRequestTimeIsRecorded(): void
    {
        Storage::fake();

        $admin = $this->createPageAdminUser();
        $page = Page::query()->firstOrFail();

        $response = $this->actingAs($admin)->post("/manage/page/migrationGet/{$page->id}", [
            'source_system' => WebsiteType::netcommons2,
            'url' => 'http://8.8.8.8/nc2/index.html',
            'destination_page_id' => $page->id,
        ]);

        $response->assertOk();
        $response->assertSee('本機能（Webスクレイピング）を利用するに当たっての注意点');
        $response->assertSessionDoesntHaveErrors(['url']);

        Storage::disk(config('filesystems.default'))->assertExists('migration/import/migration_last_request_time.txt');
        Storage::disk(config('filesystems.default'))->assertMissing("migration/import/pages/{$page->id}/frame_0001.html");

        $last_request_time = Storage::disk(config('filesystems.default'))->get('migration/import/migration_last_request_time.txt');
        $this->assertMatchesRegularExpression('/^\d+$/', $last_request_time);
    }

    /**
     * プロキシ設定が未設定でも、プロキシ使用チェック未選択なら検証エラーにならないこと。
     *
     * @test
     */
    public function migrationGetCanProceedWhenUseProxyCheckboxIsUncheckedEvenIfProxyConfigIsMissing(): void
    {
        Storage::fake();
        config([
            'connect.HTTPPROXYTUNNEL' => false,
            'connect.PROXY' => '',
        ]);

        $admin = $this->createPageAdminUser();
        $page = Page::query()->firstOrFail();

        $response = $this->actingAs($admin)->post("/manage/page/migrationGet/{$page->id}", [
            'source_system' => WebsiteType::netcommons2,
            'url' => 'http://8.8.8.8/nc2/index.html',
            'destination_page_id' => $page->id,
        ]);

        $response->assertOk();
        $response->assertSessionDoesntHaveErrors(['use_proxy']);
        Storage::disk(config('filesystems.default'))->assertExists('migration/import/migration_last_request_time.txt');
    }

    /**
     * プロキシ使用チェック選択時は、移行用プロキシ設定が未設定なら検証エラーになること。
     *
     * @test
     */
    public function migrationGetFailsWhenUseProxyCheckboxIsCheckedAndProxyConfigIsMissing(): void
    {
        Storage::fake();
        config([
            'connect.HTTPPROXYTUNNEL' => false,
            'connect.PROXY' => '',
        ]);

        $admin = $this->createPageAdminUser();
        $page = Page::query()->firstOrFail();

        $response = $this->actingAs($admin)->post("/manage/page/migrationGet/{$page->id}", [
            'source_system' => WebsiteType::netcommons2,
            'url' => 'http://8.8.8.8/nc2/index.html',
            'use_proxy' => 'on',
            'destination_page_id' => $page->id,
        ]);

        $response->assertStatus(302);
        $response->assertRedirect("/manage/page/migrationOrder/{$page->id}");
        $response->assertSessionHasErrors(['use_proxy']);

        Storage::disk(config('filesystems.default'))->assertMissing('migration/import/migration_last_request_time.txt');
    }
}
