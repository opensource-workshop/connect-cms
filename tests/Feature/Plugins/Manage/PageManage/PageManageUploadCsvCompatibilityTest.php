<?php

namespace Tests\Feature\Plugins\Manage\PageManage;

use App\Models\Core\UsersRoles;
use App\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Tests\TestCase;

/**
 * ページ管理CSVインポートの互換性を検証する。
 *
 * @runTestsInSeparateProcesses
 * @preserveGlobalState disabled
 */
class PageManageUploadCsvCompatibilityTest extends TestCase
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
     * CSV文字列を生成する。
     *
     * @param  array<int, string>  $headers
     * @param  array<int, array<int, string>>  $rows
     */
    private function createCsvContent(array $headers, array $rows): string
    {
        $output = fopen('php://temp', 'r+');
        fputcsv($output, $headers);
        foreach ($rows as $row) {
            fputcsv($output, $row);
        }
        rewind($output);
        $content = stream_get_contents($output);
        fclose($output);

        return $content;
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
     * 旧形式ヘッダー（layout_inherit_flag なし）でもインポートできること。
     *
     * @test
     */
    public function legacyCsvWithoutLayoutInheritFlagCanBeImported(): void
    {
        $admin = $this->createPageAdminUser();

        $csv_content = $this->createCsvContent(
            ['page_name', 'permanent_link', 'background_color', 'header_color', 'theme', 'layout', 'base_display_flag'],
            [['legacy', '/legacy-import-test', 'NULL', 'NULL', 'NULL', 'NULL', '0']]
        );
        $csv_file = UploadedFile::fake()->createWithContent('legacy_page.csv', $csv_content);

        $response = $this->actingAs($admin)->post('/manage/page/upload', [
            'page_csv' => $csv_file,
        ]);

        $response->assertStatus(302);
        $response->assertRedirect('/manage/page/import');
        $response->assertSessionHas('flash_message', 'インポートしました。');
        $this->assertDatabaseHas('pages', [
            'page_name' => 'legacy',
            'permanent_link' => '/legacy-import-test',
            'layout_inherit_flag' => 1,
            'base_display_flag' => 0,
        ]);
    }

    /**
     * 新形式ヘッダーでは layout_inherit_flag の値をそのまま使うこと。
     *
     * @test
     */
    public function currentCsvLayoutInheritFlagValueIsAppliedAsIs(): void
    {
        $admin = $this->createPageAdminUser();

        $csv_content = $this->createCsvContent(
            ['page_name', 'permanent_link', 'background_color', 'header_color', 'theme', 'layout', 'layout_inherit_flag', 'base_display_flag'],
            [['current', '/current-import-test', 'NULL', 'NULL', 'NULL', 'NULL', '0', '1']]
        );
        $csv_file = UploadedFile::fake()->createWithContent('current_page.csv', $csv_content);

        $response = $this->actingAs($admin)->post('/manage/page/upload', [
            'page_csv' => $csv_file,
        ]);

        $response->assertStatus(302);
        $response->assertRedirect('/manage/page/import');
        $response->assertSessionHas('flash_message', 'インポートしました。');
        $this->assertDatabaseHas('pages', [
            'page_name' => 'current',
            'permanent_link' => '/current-import-test',
            'layout_inherit_flag' => 0,
            'base_display_flag' => 1,
        ]);
    }
}
