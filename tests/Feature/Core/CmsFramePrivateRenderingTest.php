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
 * - ページをHTTP経由で表示し、表示対象外のフレームはCSSで隠すのではなく、
 *   フレームの要素・タイトル・プラグイン本文ともHTMLに出力されないことを確認する。
 * - 公開設定とログイン状態の組み合わせは、出力される／されないの両方向をデータプロバイダで網羅する。
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
     * 公開設定とログイン状態の組み合わせごとの、フレーム出力有無の一覧。
     *
     * 期間限定公開の日時はsetUp前に評価されないよう、現在日時からの日数だけを渡す。
     */
    public function contentOpenTypeProvider(): array
    {
        // [公開設定, 公開日時Fromの日数, 公開日時Toの日数, 閲覧者, プレビュー, 出力されるか]
        return [
            '公開 × ゲスト' => [ContentOpenType::always_open, null, null, 'guest', false, true],
            '非公開 × ゲスト' => [ContentOpenType::always_close, null, null, 'guest', false, false],
            '非公開 × ログインユーザー' => [ContentOpenType::always_close, null, null, 'user', false, false],
            '期間限定公開（期間内） × ゲスト' => [ContentOpenType::limited_open, -1, 1, 'guest', false, true],
            '期間限定公開（期間終了） × ゲスト' => [ContentOpenType::limited_open, -10, -1, 'guest', false, false],
            '期間限定公開（期間開始前） × ゲスト' => [ContentOpenType::limited_open, 1, 10, 'guest', false, false],
            'ログイン後表示 × ゲスト' => [ContentOpenType::login_open, null, null, 'guest', false, false],
            'ログイン後表示 × ログインユーザー' => [ContentOpenType::login_open, null, null, 'user', false, true],
            'ログイン後非表示 × ゲスト' => [ContentOpenType::login_close, null, null, 'guest', false, true],
            'ログイン後非表示 × ログインユーザー' => [ContentOpenType::login_close, null, null, 'user', false, false],
            '非公開 × フレーム配置権限' => [ContentOpenType::always_close, null, null, 'arrangement', false, true],
            '非公開 × フレーム配置権限（プレビュー）' => [ContentOpenType::always_close, null, null, 'arrangement', true, false],
        ];
    }

    /**
     * 公開設定で表示対象外のフレームはHTML自体を出力せず、表示対象のフレームは出力されること。
     *
     * @dataProvider contentOpenTypeProvider
     */
    public function testFrameRenderingFollowsContentOpenType(
        int $content_open_type,
        ?int $from_days,
        ?int $to_days,
        string $viewer,
        bool $preview,
        bool $expected_rendered
    ): void {
        [$page, $frame] = $this->createContentsFrame('target', [
            'content_open_type' => $content_open_type,
            'content_open_date_from' => is_null($from_days) ? null : Carbon::now()->addDays($from_days),
            'content_open_date_to' => is_null($to_days) ? null : Carbon::now()->addDays($to_days),
        ]);

        $user = $this->createViewer($viewer);
        if ($user) {
            $this->actingAs($user);
        }

        $response = $this->get($page->permanent_link . ($preview ? '?mode=preview' : ''));

        $response->assertOk();
        if ($expected_rendered) {
            $this->assertFrameRendered($response, $frame, 'target');
        } else {
            $this->assertFrameNotRendered($response, $frame, 'target');
        }
    }

    /**
     * 非公開フレームのアクションURLを直接指定しても、フレームの本文を出力しないこと。
     */
    public function testAlwaysCloseFrameIsNotRenderedByDirectActionUrl(): void
    {
        [$page, $frame] = $this->createContentsFrame('direct-close', [
            'content_open_type' => ContentOpenType::always_close,
        ]);

        $response = $this->post("/plugin/contents/index/{$page->id}/{$frame->id}");

        $response->assertOk();
        $this->assertFrameNotRendered($response, $frame, 'direct-close');
    }

    /**
     * 公開フレームは、アクションURLを直接指定した場合も従来どおり本文を出力すること。
     */
    public function testAlwaysOpenFrameIsRenderedByDirectActionUrl(): void
    {
        [$page, $frame] = $this->createContentsFrame('direct-open', [
            'content_open_type' => ContentOpenType::always_open,
        ]);

        $response = $this->post("/plugin/contents/index/{$page->id}/{$frame->id}");

        $response->assertOk();
        $this->assertFrameRendered($response, $frame, 'direct-open');
    }

    /**
     * 公開中で表示データが0件の「データがない場合にフレームも非表示にする」フレームは、従来どおりd-noneで出力されること。
     */
    public function testNoneHiddenFrameWithoutDataIsRenderedWithDNone(): void
    {
        [$page, $frame] = $this->createNoneHiddenWhatsnewsFrame(ContentOpenType::always_open);

        $response = $this->get($page->permanent_link);

        $response->assertOk();
        $this->assertMatchesRegularExpression(
            '/<div class="[^"]*\bd-none\b[^"]*" id="frame-' . $frame->id . '">/',
            $response->getContent()
        );
    }

    /**
     * 「データがない場合にフレームも非表示にする」設定の非公開フレームでも、ページ表示が壊れずHTMLを出力しないこと。
     *
     * 表示対象外のフレームで件数取得を省略しても、ページ表示に影響しないことの確認。
     */
    public function testNoneHiddenAlwaysCloseFrameDoesNotBreakPage(): void
    {
        [$page, $frame] = $this->createNoneHiddenWhatsnewsFrame(ContentOpenType::always_close);

        $response = $this->get($page->permanent_link);

        $response->assertOk();
        $response->assertDontSee('id="frame-' . $frame->id . '"', false);
    }

    /**
     * 判定用の目印をフレームタイトルと本文に持つ固定記事フレームを、専用ページの中央エリアに作成する。
     */
    private function createContentsFrame(string $marker, array $attributes): array
    {
        $page = $this->createPage($marker);

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
     * 件数取得で0件となる（新着設定のない）新着フレームを、「データがない場合にフレームも非表示にする」設定で作成する。
     *
     * 件数取得（getContentsCount）を持つのは新着プラグインのため、新着フレームを使う。
     */
    private function createNoneHiddenWhatsnewsFrame(int $content_open_type): array
    {
        $page = $this->createPage('none-hidden');

        $frame = Frame::create([
            'page_id' => $page->id,
            'area_id' => 2,
            'frame_title' => 'title-none-hidden',
            'frame_design' => 'default',
            'plugin_name' => 'whatsnews',
            'frame_col' => 0,
            'template' => 'default',
            'bucket_id' => null,
            'display_sequence' => 1,
            'none_hidden' => 1,
            'content_open_type' => $content_open_type,
        ]);

        return [$page, $frame];
    }

    /**
     * テスト用のページを作成する。
     */
    private function createPage(string $marker): Page
    {
        return Page::create([
            'page_name' => "page-{$marker}",
            'permanent_link' => "/page-{$marker}",
            'base_display_flag' => 1,
        ]);
    }

    /**
     * 閲覧者の種別に応じたユーザーを作成する。ゲストの場合はnullを返す。
     */
    private function createViewer(string $viewer): ?User
    {
        if ($viewer === 'guest') {
            return null;
        }

        /** @var User $user */
        $user = User::factory()->create();

        if ($viewer === 'arrangement') {
            UsersRoles::factory()->create([
                'users_id' => $user->id,
                'target' => 'base',
                'role_name' => 'role_arrangement',
                'role_value' => 1,
            ]);
        }

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
