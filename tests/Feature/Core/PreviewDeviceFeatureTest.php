<?php

namespace Tests\Feature\Core;

use App\Models\Common\Page;
use App\Models\Core\UsersRoles;
use App\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * 管理メニューから開始する画面サイズ選択付きプレビューを対象とするテスト。
 * 公開ルート経由で権限、初期選択、iframe URL、既存プレビューとの互換性を検証する。
 */
class PreviewDeviceFeatureTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed();
    }

    /** 指定した基本権限を持つ利用者を作る。 */
    private function createUserWithRole(string $role): User
    {
        $user = User::factory()->create();

        UsersRoles::factory()->create([
            'users_id' => $user->id,
            'target' => 'base',
            'role_name' => $role,
            'role_value' => 1,
        ]);

        return $user;
    }

    /** テスト対象となるページを固定URLから取得する。 */
    private function getPage(string $permanent_link = '/'): Page
    {
        return Page::where('permanent_link', $permanent_link)->firstOrFail();
    }

    /**
     * プラグイン管理者はページIDからプレビューを開始でき、現在の画面が初期選択になること。
     */
    public function testArrangementUserCanOpenPreviewWithCurrentDevice(): void
    {
        $user = $this->createUserWithRole('role_arrangement');
        $page = $this->getPage();

        $response = $this->actingAs($user)->get(route('preview.device', [
            'page_id' => $page->id,
        ]));

        $response->assertOk();
        $response->assertSee('data-selected-device="current"', false);
        $response->assertSee('現在の画面');
        $response->assertSee('<meta name="robots" content="noindex, nofollow, noarchive">', false);
        $response->assertSee(url('/?mode=preview&preview_frame=1'));
        $response->assertSee(url('/'));
    }

    /**
     * 固定画面サイズを再読み込みしても、利用者が選択したスマホ幅を復元できること。
     */
    public function testSelectedDeviceIsRestored(): void
    {
        $user = $this->createUserWithRole('role_arrangement');
        $page = $this->getPage();

        $response = $this->actingAs($user)->get(route('preview.device', [
            'page_id' => $page->id,
            'preview_device' => 'smartphone',
        ]));

        $response->assertOk();
        $response->assertSee('data-selected-device="smartphone"', false);
        $response->assertSee('data-preview-width="390"', false);
        $response->assertSee('data-preview-height="844"', false);
    }

    /**
     * プラグイン管理権限がない利用者は、URLを直接指定してもプレビュー枠を利用できないこと。
     */
    public function testUserWithoutArrangementRoleCannotOpenPreview(): void
    {
        $user = $this->createUserWithRole('role_article');
        $page = $this->getPage();

        $response = $this->actingAs($user)->get(route('preview.device', ['page_id' => $page->id]));

        $response->assertStatus(403);
    }

    /**
     * ページIDがないURLや、文字列、0以下のページIDからプレビューを開始できないこと。
     */
    public function testInvalidPageIdIsRejected(): void
    {
        $user = $this->createUserWithRole('role_arrangement');

        $missing_response = $this->actingAs($user)->get('/preview');
        $string_response = $this->actingAs($user)->get(route('preview.device', [
            'page_id' => 'invalid',
        ]));
        $zero_response = $this->actingAs($user)->get(route('preview.device', [
            'page_id' => 0,
        ]));
        $negative_response = $this->actingAs($user)->get(route('preview.device', [
            'page_id' => -1,
        ]));

        $missing_response->assertStatus(404);
        $string_response->assertStatus(404);
        $zero_response->assertStatus(404);
        $negative_response->assertStatus(404);
    }

    /**
     * 存在しないページIDを指定しても、プレビュー対象にできないこと。
     */
    public function testMissingPageIsRejected(): void
    {
        $user = $this->createUserWithRole('role_arrangement');
        $missing_page_id = (int) Page::max('id') + 1;

        $response = $this->actingAs($user)->get(route('preview.device', [
            'page_id' => $missing_page_id,
        ]));

        $response->assertStatus(404);
    }

    /**
     * ページIDが存在しても、固定URLが管理・更新用ルートと衝突するページはプレビューしないこと。
     */
    public function testPageWithConflictingPermanentLinkIsRejected(): void
    {
        $user = $this->createUserWithRole('role_arrangement');
        $page = Page::factory()->create(['permanent_link' => '/plugin/contents/save/1/1']);

        $response = $this->actingAs($user)->get(route('preview.device', [
            'page_id' => $page->id,
        ]));

        $response->assertStatus(404);
    }

    /**
     * 登録済みページでは、トップページ以外のページIDもプレビューできること。
     */
    public function testRegisteredPageIdIsAccepted(): void
    {
        $user = $this->createUserWithRole('role_arrangement');
        $page = Page::factory()->create(['permanent_link' => '/preview-page']);

        $response = $this->actingAs($user)->get(route('preview.device', [
            'page_id' => $page->id,
        ]));

        $response->assertOk();
        $response->assertSee(url('/preview-page?mode=preview&preview_frame=1'));
    }

    /**
     * プラグイン管理権限があっても、閲覧権限のないページをプレビュー対象にできないこと。
     */
    public function testPageWithoutViewPermissionIsRejected(): void
    {
        $user = $this->createUserWithRole('role_arrangement');
        $page = Page::factory()->create([
            'permanent_link' => '/members-only-preview',
            'membership_flag' => 1,
        ]);

        $response = $this->actingAs($user)->get(route('preview.device', [
            'page_id' => $page->id,
        ]));

        $response->assertStatus(403);
    }

    /**
     * 表示中URLに別のページIDがあっても、管理メニューは解決済みページのIDをプレビューへ渡すこと。
     */
    public function testManagementMenuUsesResolvedPageId(): void
    {
        $user = $this->createUserWithRole('role_arrangement');
        $page = $this->getPage();
        $other_page_id = (int) Page::max('id') + 1;

        $response = $this->actingAs($user)->get('/?page_id=' . $other_page_id);

        $response->assertOk();
        $response->assertSee(route('preview.device', ['page_id' => $page->id]));
        $response->assertDontSee(route('preview.device', [
            'page_id' => $other_page_id,
        ]));
    }

    /**
     * 管理機能メニューは従来の名称を維持し、画面サイズをプレビュー開始前に選ばせないこと。
     */
    public function testManagementMenuKeepsSinglePreviewEntry(): void
    {
        $user = $this->createUserWithRole('role_arrangement');

        $response = $this->actingAs($user)->get('/');

        $response->assertOk();
        $this->assertSame(1, preg_match_all('/>\s*プレビューモード\s*<\/a>/u', $response->getContent()));
        $response->assertDontSee('PCでプレビュー');
        $response->assertDontSee('スマホでプレビュー');
    }

    /**
     * iframe内の通常リンクやフォームを操作すると、プレビューを継続せず通常画面へ戻ること。
     */
    public function testPreviewFrameTargetsTopWindow(): void
    {
        $user = $this->createUserWithRole('role_arrangement');

        $response = $this->actingAs($user)->get('/?mode=preview&preview_frame=1');

        $response->assertOk();
        $response->assertSee('<meta name="robots" content="noindex, nofollow, noarchive">', false);
        $response->assertSee('<base target="_top">', false);
    }
}
