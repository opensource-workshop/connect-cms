<?php

namespace Tests\Feature\Core;

use App\Enums\ContentOpenType;
use App\Enums\StatusType;
use App\Models\Common\Buckets;
use App\Models\Common\Frame;
use App\Models\Common\Page;
use App\Models\Core\UsersRoles;
use App\Models\User\Contents\Contents;
use App\User;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Testing\TestResponse;
use Tests\TestCase;

/**
 * フレームの公開設定によるページ表示時のHTML出力を検証する。
 *
 * テスト方針:
 * - ページをHTTP経由で表示し、非公開等のフレームがCSSで隠されるのではなく、
 *   フレームの要素・タイトル・プラグイン本文ともHTMLに出力されないことを確認する。
 * - フレーム配置権限を持つ利用者には従来どおり出力され、プレビュー時は一般利用者と同じ見え方になることを確認する。
 */
class CmsFramePrivateRenderingTest extends TestCase
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
     * 非公開フレームは、ゲストに対してフレーム要素も本文もHTMLに出力しないこと。
     */
    public function testAlwaysCloseFrameIsNotRenderedForGuest(): void
    {
        [$page, $frame] = $this->createContentsFrame('always-close', [
            'content_open_type' => ContentOpenType::always_close,
        ]);

        $response = $this->get($page->permanent_link);

        $response->assertOk();
        $this->assertFrameNotRendered($response, $frame, 'always-close');
    }

    /**
     * 期間限定公開フレームは、公開期間外ならHTMLに出力しないこと。
     */
    public function testLimitedOpenFrameOutsidePeriodIsNotRenderedForGuest(): void
    {
        [$page, $frame] = $this->createContentsFrame('limited-expired', [
            'content_open_type' => ContentOpenType::limited_open,
            'content_open_date_from' => Carbon::now()->subDays(10),
            'content_open_date_to' => Carbon::now()->subDays(1),
        ]);

        $response = $this->get($page->permanent_link);

        $response->assertOk();
        $this->assertFrameNotRendered($response, $frame, 'limited-expired');
    }

    /**
     * 期間限定公開フレームは、公開期間内なら出力されること。
     */
    public function testLimitedOpenFrameWithinPeriodIsRenderedForGuest(): void
    {
        [$page, $frame] = $this->createContentsFrame('limited-active', [
            'content_open_type' => ContentOpenType::limited_open,
            'content_open_date_from' => Carbon::now()->subDays(1),
            'content_open_date_to' => Carbon::now()->addDays(1),
        ]);

        $response = $this->get($page->permanent_link);

        $response->assertOk();
        $this->assertFrameRendered($response, $frame, 'limited-active');
    }

    /**
     * ログイン後表示フレームは、未ログインのゲストにはHTMLを出力しないこと。
     */
    public function testLoginOpenFrameIsNotRenderedForGuest(): void
    {
        [$page, $frame] = $this->createContentsFrame('login-open', [
            'content_open_type' => ContentOpenType::login_open,
        ]);

        $response = $this->get($page->permanent_link);

        $response->assertOk();
        $this->assertFrameNotRendered($response, $frame, 'login-open');
    }

    /**
     * ログイン後非表示フレームは、配置権限のないログインユーザーにはHTMLを出力しないこと。
     */
    public function testLoginCloseFrameIsNotRenderedForLoggedInUser(): void
    {
        [$page, $frame] = $this->createContentsFrame('login-close', [
            'content_open_type' => ContentOpenType::login_close,
        ]);

        /** @var User $user */
        $user = User::factory()->create();

        $response = $this->actingAs($user)->get($page->permanent_link);

        $response->assertOk();
        $this->assertFrameNotRendered($response, $frame, 'login-close');
    }

    /**
     * 公開フレームは、ゲストにも出力されること。
     */
    public function testAlwaysOpenFrameIsRenderedForGuest(): void
    {
        [$page, $frame] = $this->createContentsFrame('always-open', [
            'content_open_type' => ContentOpenType::always_open,
        ]);

        $response = $this->get($page->permanent_link);

        $response->assertOk();
        $this->assertFrameRendered($response, $frame, 'always-open');
    }

    /**
     * フレーム配置権限を持つ利用者には、非公開フレームも編集のため出力されること。
     */
    public function testAlwaysCloseFrameIsRenderedForArrangementUser(): void
    {
        [$page, $frame] = $this->createContentsFrame('close-for-admin', [
            'content_open_type' => ContentOpenType::always_close,
        ]);

        $response = $this->actingAs($this->createUserWithRole('role_arrangement'))->get($page->permanent_link);

        $response->assertOk();
        $this->assertFrameRendered($response, $frame, 'close-for-admin');
    }

    /**
     * フレーム配置権限を持つ利用者でも、プレビュー時は一般利用者と同様に非公開フレームを出力しないこと。
     */
    public function testAlwaysCloseFrameIsNotRenderedForArrangementUserInPreview(): void
    {
        [$page, $frame] = $this->createContentsFrame('close-preview', [
            'content_open_type' => ContentOpenType::always_close,
        ]);

        $response = $this->actingAs($this->createUserWithRole('role_arrangement'))->get($page->permanent_link . '?mode=preview');

        $response->assertOk();
        $this->assertFrameNotRendered($response, $frame, 'close-preview');
    }

    /**
     * 「データがない場合にフレームも非表示にする」設定の非公開フレームでも、ページ表示が壊れずHTMLを出力しないこと。
     */
    public function testNoneHiddenAlwaysCloseFrameIsNotRenderedForGuest(): void
    {
        [$page, $frame] = $this->createContentsFrame('none-hidden-close', [
            'content_open_type' => ContentOpenType::always_close,
            'none_hidden' => 1,
        ]);

        $response = $this->get($page->permanent_link);

        $response->assertOk();
        $this->assertFrameNotRendered($response, $frame, 'none-hidden-close');
    }

    /**
     * 判定用の目印をフレームタイトルと本文に持つ固定記事フレームを、専用ページの中央エリアに作成する。
     */
    private function createContentsFrame(string $marker, array $attributes): array
    {
        $page = Page::create([
            'page_name' => "page-{$marker}",
            'permanent_link' => "/page-{$marker}",
            'base_display_flag' => 1,
        ]);

        $bucket = Buckets::create([
            'bucket_name' => "bucket-{$marker}",
            'plugin_name' => 'contents',
        ]);

        Contents::create([
            'bucket_id' => $bucket->id,
            'content_text' => "<p>body-{$marker}</p>",
            'status' => StatusType::active,
        ]);

        $frame = Frame::create(array_merge([
            'page_id' => $page->id,
            'area_id' => 2,
            'frame_title' => "title-{$marker}",
            'frame_design' => 'default',
            'plugin_name' => 'contents',
            'frame_col' => 0,
            'template' => 'default',
            'bucket_id' => $bucket->id,
            'display_sequence' => 1,
            'content_open_type' => ContentOpenType::always_open,
        ], $attributes));

        return [$page, $frame];
    }

    /**
     * フレーム配置権限などのベースロールを持つユーザーを作成する。
     */
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

    /**
     * フレーム要素・タイトル・本文がHTMLに出力されていることを検証する。
     */
    private function assertFrameRendered(TestResponse $response, Frame $frame, string $marker): void
    {
        $response->assertSee('id="frame-' . $frame->id . '"', false);
        $response->assertSee("title-{$marker}");
        $response->assertSee("body-{$marker}");
    }

    /**
     * フレーム要素・タイトル・本文がHTMLに出力されていないことを検証する。
     */
    private function assertFrameNotRendered(TestResponse $response, Frame $frame, string $marker): void
    {
        $response->assertDontSee('id="frame-' . $frame->id . '"', false);
        $response->assertDontSee("title-{$marker}");
        $response->assertDontSee("body-{$marker}");
    }
}
