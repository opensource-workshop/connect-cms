<?php

namespace Tests\Feature\Plugins\User\Tabs;

use App\Enums\ContentOpenType;
use App\Enums\StatusType;
use App\Models\Common\Buckets;
use App\Models\Common\Frame;
use App\Models\Common\Page;
use App\Models\Core\UsersRoles;
use App\Models\User\Contents\Contents;
use App\Models\User\Tabs\Tabs;
use App\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Testing\TestResponse;
use Tests\TestCase;

/**
 * タブプラグインと、フレームの公開設定の組み合わせを検証する。
 *
 * テスト方針:
 * - ページをHTTP経由で表示し、公開設定で表示対象外のフレームはタブ（タイトル）も出力されないことを確認する。
 * - 初期表示フレームが表示対象外の場合は、表示できる先頭のタブが初期選択になることを確認する。
 * - フレーム配置権限を持つ利用者には、従来どおり全タブが出力されることを確認する。
 */
class TabsPrivateFrameFeatureTest extends TestCase
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
     * 非公開フレームは、ゲストに対してタブのタイトルも出力しないこと。
     */
    public function testPrivateFrameTabIsNotRenderedForGuest(): void
    {
        $page = $this->createPage();
        $tabs_frame = $this->createTabsFrame($page);
        $open_frame = $this->createContentsFrame($page, 'tab-open', ContentOpenType::always_open, 2);
        $close_frame = $this->createContentsFrame($page, 'tab-close', ContentOpenType::always_close, 3);
        $this->createTabs($tabs_frame, $open_frame, [$open_frame, $close_frame]);

        $response = $this->get($page->permanent_link);

        $response->assertOk();
        $response->assertSee('id="tab_' . $open_frame->id . '"', false);
        $response->assertSee('title-tab-open');
        $response->assertDontSee('id="tab_' . $close_frame->id . '"', false);
        $response->assertDontSee('title-tab-close');
        $response->assertDontSee("#frame-{$close_frame->id}", false);
        $this->assertTabNotFallback($response, $open_frame);
    }

    /**
     * 初期表示フレームが非公開の場合、表示できる先頭のタブを初期選択にすること。
     */
    public function testFirstVisibleTabIsSelectedWhenDefaultFrameIsPrivate(): void
    {
        $page = $this->createPage();
        $tabs_frame = $this->createTabsFrame($page);
        $close_frame = $this->createContentsFrame($page, 'tab-default-close', ContentOpenType::always_close, 2);
        $open_frame = $this->createContentsFrame($page, 'tab-first-open', ContentOpenType::always_open, 3);
        $this->createTabs($tabs_frame, $close_frame, [$close_frame, $open_frame]);

        $response = $this->get($page->permanent_link);

        $response->assertOk();
        $response->assertDontSee('title-tab-default-close');
        $response->assertSee('tab_a_' . $open_frame->id . '  active', false);
        $response->assertSee("$('.tab_{$open_frame->id}').trigger('click');", false);
    }

    /**
     * フレーム配置権限を持つ利用者には、非公開フレームのタブも出力され、設定どおりの初期表示になること。
     */
    public function testPrivateFrameTabIsRenderedForArrangementUser(): void
    {
        $page = $this->createPage();
        $tabs_frame = $this->createTabsFrame($page);
        $close_frame = $this->createContentsFrame($page, 'tab-admin-close', ContentOpenType::always_close, 2);
        $open_frame = $this->createContentsFrame($page, 'tab-admin-open', ContentOpenType::always_open, 3);
        $this->createTabs($tabs_frame, $close_frame, [$close_frame, $open_frame]);

        $response = $this->actingAs($this->createUserWithRole('role_arrangement'))->get($page->permanent_link);

        $response->assertOk();
        $response->assertSee('id="tab_' . $close_frame->id . '"', false);
        $response->assertSee('tab_a_' . $close_frame->id . '  active', false);
        $response->assertSee('id="tab_' . $open_frame->id . '"', false);
        $this->assertTabNotFallback($response, $open_frame);
    }

    /**
     * タブ確認用のページを作成する。
     */
    private function createPage(): Page
    {
        return Page::create([
            'page_name' => 'tabs-page',
            'permanent_link' => '/tabs-page',
            'base_display_flag' => 1,
        ]);
    }

    /**
     * タブプラグインのフレームを中央エリアの先頭に作成する。
     */
    private function createTabsFrame(Page $page): Frame
    {
        return Frame::create([
            'page_id' => $page->id,
            'area_id' => 2,
            'frame_title' => 'tabs-frame',
            'frame_design' => 'none',
            'plugin_name' => 'tabs',
            'frame_col' => 0,
            'template' => 'default',
            'display_sequence' => 1,
            'content_open_type' => ContentOpenType::always_open,
        ]);
    }

    /**
     * 判定用の目印をフレームタイトルに持つ固定記事フレームを、指定の公開設定で作成する。
     */
    private function createContentsFrame(Page $page, string $marker, int $content_open_type, int $display_sequence): Frame
    {
        $bucket = Buckets::create([
            'bucket_name' => "bucket-{$marker}",
            'plugin_name' => 'contents',
        ]);

        Contents::create([
            'bucket_id' => $bucket->id,
            'content_text' => "<p>body-{$marker}</p>",
            'status' => StatusType::active,
        ]);

        return Frame::create([
            'page_id' => $page->id,
            'area_id' => 2,
            'frame_title' => "title-{$marker}",
            'frame_design' => 'default',
            'plugin_name' => 'contents',
            'frame_col' => 0,
            'template' => 'default',
            'bucket_id' => $bucket->id,
            'display_sequence' => $display_sequence,
            'content_open_type' => $content_open_type,
        ]);
    }

    /**
     * タブで切り替えるフレームと初期表示フレームを設定する。
     */
    private function createTabs(Frame $tabs_frame, Frame $default_frame, array $frames): void
    {
        Tabs::create([
            'frame_id' => $tabs_frame->id,
            'default_frame_id' => $default_frame->id,
            'frame_ids' => collect($frames)->pluck('id')->implode(','),
        ]);
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
     * 初期表示を切り替えるためのクリック処理が出力されていないことを検証する。
     */
    private function assertTabNotFallback(TestResponse $response, Frame $frame): void
    {
        $response->assertDontSee("$('.tab_{$frame->id}').trigger('click');", false);
    }
}
