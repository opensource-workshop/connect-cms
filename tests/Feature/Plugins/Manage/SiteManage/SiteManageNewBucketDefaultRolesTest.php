<?php

namespace Tests\Feature\Plugins\Manage\SiteManage;

use App\Enums\BaseHeaderFontColorClass;
use App\Enums\BaseLoginRedirectPage;
use App\Enums\SmartphoneMenuTemplateType;
use App\Models\Core\Configs;
use App\Models\Core\UsersRoles;
use App\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * サイト管理のプラグイン新規作成時向け初期権限設定を検証する。
 *
 * 画面表示と保存結果の整合が崩れないことを、
 * 管理画面のHTTP経路経由で守る。
 *
 * @runTestsInSeparateProcesses
 * @preserveGlobalState disabled
 */
class SiteManageNewBucketDefaultRolesTest extends TestCase
{
    use RefreshDatabase;

    /**
     * テスト前に初期データを投入する。
     */
    protected function setUp(): void
    {
        parent::setUp();

        $this->seed();
    }

    /**
     * 未設定状態では両方のチェックボックスが未チェックで表示されること。
     */
    public function testIndexShowsBothCheckboxesUncheckedWhenConfigsAreMissing(): void
    {
        $admin = $this->createSiteAdminUser();

        $response = $this->actingAs($admin)->get('/manage/site');

        $response->assertOk();
        $response->assertSee('プラグインの権限設定');
        $response->assertSee('新規作成時の投稿権限');
        $response->assertDontSee('id="new_bucket_role_article_post_flag" class="custom-control-input" checked="checked"', false);
        $response->assertDontSee('id="new_bucket_role_reporter_post_flag" class="custom-control-input" checked="checked"', false);
    }

    /**
     * 保存済みの設定があれば、画面のチェック状態へ反映されること。
     */
    public function testIndexShowsSavedFlagValues(): void
    {
        $admin = $this->createSiteAdminUser();
        $this->setDefaultPostRoleConfigs(1, 0);

        $response = $this->actingAs($admin)->get('/manage/site');

        $response->assertOk();
        $response->assertSee('id="new_bucket_role_article_post_flag" class="custom-control-input" checked="checked"', false);
        $response->assertDontSee('id="new_bucket_role_reporter_post_flag" class="custom-control-input" checked="checked"', false);
    }

    /**
     * 両方ONで保存した設定がConfigに反映され、再表示時にも維持されること。
     */
    public function testUpdateCanSaveBothFlagsAsEnabled(): void
    {
        $admin = $this->createSiteAdminUser();

        $response = $this->actingAs($admin)->post('/manage/site/update', $this->buildBasePayload([
            'new_bucket_role_article_post_flag' => 1,
            'new_bucket_role_reporter_post_flag' => 1,
        ]));

        $response->assertRedirect('/manage/site');
        $this->assertDatabaseHas('configs', [
            'name' => 'new_bucket_role_article_post_flag',
            'category' => 'general',
            'value' => '1',
        ]);
        $this->assertDatabaseHas('configs', [
            'name' => 'new_bucket_role_reporter_post_flag',
            'category' => 'general',
            'value' => '1',
        ]);
    }

    /**
     * 片方だけONでも保存でき、画面再表示も保存内容に追従すること。
     */
    public function testUpdateCanSaveOnlyOneFlagAsEnabled(): void
    {
        $admin = $this->createSiteAdminUser();

        $response = $this->actingAs($admin)->post('/manage/site/update', $this->buildBasePayload([
            'new_bucket_role_article_post_flag' => 1,
        ]));

        $response->assertRedirect('/manage/site');
        $this->assertDatabaseHas('configs', [
            'name' => 'new_bucket_role_article_post_flag',
            'category' => 'general',
            'value' => '1',
        ]);
        $this->assertDatabaseHas('configs', [
            'name' => 'new_bucket_role_reporter_post_flag',
            'category' => 'general',
            'value' => '0',
        ]);
    }

    /**
     * 一度ONにした設定を外して保存すると、Configが0に戻ること。
     */
    public function testUpdateCanTurnFlagsOffAgain(): void
    {
        $admin = $this->createSiteAdminUser();
        $this->setDefaultPostRoleConfigs(1, 1);

        $response = $this->actingAs($admin)->post('/manage/site/update', $this->buildBasePayload());

        $response->assertRedirect('/manage/site');
        $this->assertDatabaseHas('configs', [
            'name' => 'new_bucket_role_article_post_flag',
            'category' => 'general',
            'value' => '0',
        ]);
        $this->assertDatabaseHas('configs', [
            'name' => 'new_bucket_role_reporter_post_flag',
            'category' => 'general',
            'value' => '0',
        ]);
    }

    /**
     * サイト管理者権限を持つユーザーを作成する。
     */
    private function createSiteAdminUser(): User
    {
        $user = User::factory()->create();

        UsersRoles::factory()->create([
            'users_id' => $user->id,
            'target' => 'manage',
            'role_name' => 'admin_site',
            'role_value' => 1,
        ]);

        return $user;
    }

    /**
     * プラグイン新規作成時用の投稿権限Configを保存する。
     */
    private function setDefaultPostRoleConfigs(int $articleFlag, int $reporterFlag): void
    {
        Configs::updateOrCreate(
            ['name' => 'new_bucket_role_article_post_flag'],
            ['category' => 'general', 'value' => $articleFlag]
        );
        Configs::updateOrCreate(
            ['name' => 'new_bucket_role_reporter_post_flag'],
            ['category' => 'general', 'value' => $reporterFlag]
        );
    }

    /**
     * サイト基本設定更新に必要な最低限の入力値を組み立てる。
     */
    private function buildBasePayload(array $overrides = []): array
    {
        return array_merge([
            'base_site_name' => 'テストサイト',
            'base_theme' => '',
            'base_layout' => '0|0|0|0',
            'additional_theme' => '',
            'base_background_color' => '',
            'base_header_color' => '',
            'base_header_font_color_class' => BaseHeaderFontColorClass::navbar_dark,
            'base_header_optional_class' => '',
            'body_optional_class' => '',
            'center_area_optional_class' => '',
            'footer_area_optional_class' => '',
            'base_header_hidden' => 0,
            'base_header_login_link' => 0,
            'base_login_password_reset' => 0,
            'base_login_redirect_previous_page' => BaseLoginRedirectPage::top_page,
            'base_login_redirect_select_page' => '',
            'use_mypage' => 0,
            'mypage_top_notice' => '',
            'mypage_bottom_notice' => '',
            'smartphone_menu_template' => SmartphoneMenuTemplateType::none,
        ], $overrides);
    }
}
