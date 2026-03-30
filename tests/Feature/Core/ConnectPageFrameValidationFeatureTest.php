<?php

namespace Tests\Feature\Core;

use App\Enums\AreaType;
use App\Enums\ContentOpenType;
use App\Http\Middleware\ConnectPage;
use App\Models\Common\Buckets;
use App\Models\Common\Frame;
use App\Models\Common\Page;
use App\Models\Core\Configs;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\Request;
use Illuminate\Routing\Router;
use Tests\TestCase;

/**
 * ConnectPage の page_id / frame_id 整合性判定テスト。
 *
 * テスト方針:
 * - private メソッドは直接テストせず、公開メソッド handle() の振る舞いで検証する。
 * - 期待値は内部実装ではなく、最終的な HTTP ステータスコード（200 / 403）で判定する。
 * - アプリ全体のルート定義順に依存しないよう、テスト専用の isolated Router / Route を使う。
 */
class ConnectPageFrameValidationFeatureTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        Page::create([
            'page_name' => '403',
            'permanent_link' => '/403',
            'base_display_flag' => 1,
        ]);
    }

    /**
     * ページツリー構築用に、必要最小限のページを1件生成する。
     */
    private function createPage(string $page_name, string $permanent_link, ?Page $parent = null): Page
    {
        $page = new Page([
            'page_name' => $page_name,
            'permanent_link' => $permanent_link,
            'base_display_flag' => 1,
        ]);

        if (is_null($parent)) {
            $page->save();
        } else {
            $page->appendToNode($parent)->save();
        }

        return $page->fresh();
    }

    /**
     * 祖先継承の組み合わせを検証するための標準的なページツリーを生成する。
     */
    private function createPageTree(): array
    {
        $root = $this->createPage('top', '/');
        $parent = $this->createPage('parent', '/parent', $root);
        $child = $this->createPage('child', '/parent/child', $parent);
        $sibling = $this->createPage('sibling', '/sibling', $root);

        return [$root, $parent, $child, $sibling];
    }

    /**
     * フレーム判定に必要な属性だけを持つ contents フレームを生成する。
     */
    private function createContentsFrame(Page $page, int $area_id, array $attributes = []): Frame
    {
        $bucket = Buckets::factory()->create([
            'plugin_name' => 'contents',
        ]);

        return Frame::create(array_merge([
            'page_id' => $page->id,
            'area_id' => $area_id,
            'frame_title' => 'test frame',
            'frame_design' => 'default',
            'plugin_name' => 'contents',
            'frame_col' => 12,
            'template' => 'default',
            'plug_name' => null,
            'bucket_id' => $bucket->id,
            'display_sequence' => 1,
            'browser_width' => null,
            'disable_whatsnews' => 0,
            'disable_searchs' => 0,
            'page_only' => 0,
            'default_hidden' => 0,
            'classname' => '',
            'classname_body' => '',
            'none_hidden' => 0,
            'content_open_type' => ContentOpenType::always_open,
            'content_open_date_from' => null,
            'content_open_date_to' => null,
        ], $attributes));
    }

    /**
     * handle() が参照する route / request 情報を、隔離した Laravel Router 上で組み立てる。
     */
    private function createRequest(int $page_id, ?int $frame_id = null): Request
    {
        $request = Request::create(
            is_null($frame_id) ? "/test/{$page_id}" : "/test/{$page_id}/{$frame_id}",
            'GET'
        );
        $request->attributes->set('configs', collect([
            new Configs([
                'name' => 'page_permanent_link_403',
                'category' => 'page_error',
                'value' => '/403',
            ]),
        ]));

        // アプリ本体の catch-all ルートには依存せず、Laravel 標準の Router / Route で route 解決だけ行う。
        $router = new Router($this->app['events'], $this->app);
        $router->get('/test/{page_id}/{frame_id?}', function () {
            return response('prepared', 200);
        })->name('get_plugin');
        $router->dispatchToRoute($request);

        $this->app->instance('request', $request);
        $this->app->instance(Request::class, $request);
        $this->app->instance(Router::class, $router);

        return $request;
    }

    /**
     * handle() を通し、middleware の公開振る舞いとして結果を取得する。
     */
    private function handleRequest(Request $request)
    {
        $middleware = new ConnectPage();

        return $middleware->handle($request, function ($handled_request) {
            $http_status_code = $handled_request->attributes->get('http_status_code', 200);
            return response('ok', $http_status_code);
        });
    }

    /**
     * テストの意図:
     * 子ページ表示時に親ページ配置の共通エリアフレームが有効なら、公開入口 handle() は操作を許可する。
     */
    public function testAncestorCommonAreaFrameCanBeAccessedFromDescendantPage(): void
    {
        [, $parent, $child] = $this->createPageTree();
        $frame = $this->createContentsFrame($parent, AreaType::header);

        $response = $this->handleRequest($this->createRequest($child->id, $frame->id));

        $this->assertSame(200, $response->getStatusCode());
        $this->assertSame('ok', $response->getContent());
    }

    /**
     * テストの意図:
     * page_only=2 の共通エリアフレームは配置ページ自身では非表示でも、子ページでは継承対象として操作可能なことを守る。
     */
    public function testAncestorCommonAreaFrameWithPageOnlyHiddenCanBeAccessedFromDescendantPage(): void
    {
        [, $parent, $child] = $this->createPageTree();
        $frame = $this->createContentsFrame($parent, AreaType::header, [
            'page_only' => 2,
        ]);

        $response = $this->handleRequest($this->createRequest($child->id, $frame->id));

        $this->assertSame(200, $response->getStatusCode());
    }

    /**
     * テストの意図:
     * page_only=1 の共通エリアフレームは配置ページ本人専用であり、子ページからは操作できないことを守る。
     */
    public function testAncestorCommonAreaFrameWithPageOnlyCurrentIsForbiddenFromDescendantPage(): void
    {
        [, $parent, $child] = $this->createPageTree();
        $frame = $this->createContentsFrame($parent, AreaType::header, [
            'page_only' => 1,
        ]);

        $response = $this->handleRequest($this->createRequest($child->id, $frame->id));

        $this->assertSame(403, $response->getStatusCode());
    }

    /**
     * テストの意図:
     * メインエリアは継承しないため、親ページ配置のフレームを子ページから操作できないことを守る。
     */
    public function testAncestorMainAreaFrameIsForbiddenFromDescendantPage(): void
    {
        [, $parent, $child] = $this->createPageTree();
        $frame = $this->createContentsFrame($parent, AreaType::main);

        $response = $this->handleRequest($this->createRequest($child->id, $frame->id));

        $this->assertSame(403, $response->getStatusCode());
    }

    /**
     * テストの意図:
     * 現在ページ系統に属さない別ページの共通エリアフレームは、不正な組み合わせとして拒否することを守る。
     */
    public function testUnrelatedPageFrameIsForbidden(): void
    {
        [, , $child, $sibling] = $this->createPageTree();
        $frame = $this->createContentsFrame($sibling, AreaType::header);

        $response = $this->handleRequest($this->createRequest($child->id, $frame->id));

        $this->assertSame(403, $response->getStatusCode());
    }

    /**
     * テストの意図:
     * 共通エリアは最も近い祖先の配置が優先され、より遠い祖先のフレームは子ページから操作できないことを守る。
     */
    public function testMoreDistantAncestorCommonAreaFrameIsForbiddenWhenCloserAncestorOverrides(): void
    {
        [$root, $parent, $child] = $this->createPageTree();
        $root_frame = $this->createContentsFrame($root, AreaType::header);
        $this->createContentsFrame($parent, AreaType::header);

        $response = $this->handleRequest($this->createRequest($child->id, $root_frame->id));

        $this->assertSame(403, $response->getStatusCode());
    }
}
