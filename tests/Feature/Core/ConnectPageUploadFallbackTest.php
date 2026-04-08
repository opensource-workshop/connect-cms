<?php

namespace Tests\Feature\Core;

use App\Http\Controllers\Core\UploadController;
use App\Http\Middleware\ConnectPage;
use App\Models\Common\Group;
use App\Models\Common\GroupUser;
use App\Models\Common\Page;
use App\Models\Common\PageRole;
use App\Models\Common\Uploads;
use App\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\Request;
use Illuminate\Http\UploadedFile;
use Illuminate\Routing\Router;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

/**
 * ConnectPage の upload 専用 page_id fallback を検証する Feature テスト。
 *
 * テスト方針:
 * - /upload は実 HTTP リクエストで通し、ページ権限だけのユーザーでも認可が復旧することを確認する。
 * - /upload/face は外部 API だけを差し替え、postInvoke() の認可経路と page 文脈の復元を実運用どおり検証する。
 * - fallback の境界条件と優先順位は、middleware handle() の公開振る舞いとして isolated Router 上で確認する。
 */
class ConnectPageUploadFallbackTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed();
    }

    /**
     * ページツリー検証に使うページを、必要最小限の属性で生成する。
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
     * seed 済みのトップページを取得し、子ページ追加の起点にする。
     */
    private function getRootPage(): Page
    {
        return Page::where('permanent_link', '/')->firstOrFail();
    }

    /**
     * ベース権限を持たず、指定ページのページ権限だけを持つユーザーを生成する。
     */
    private function createPageRoleUser(Page $page, string $role_name = 'role_reporter'): User
    {
        $user = User::factory()->create();
        $group = Group::factory()->create();

        GroupUser::factory()->create([
            'group_id' => $group->id,
            'user_id' => $user->id,
        ]);

        PageRole::factory()->create([
            'page_id' => $page->id,
            'group_id' => $group->id,
            'target' => 'base',
            'role_name' => $role_name,
            'role_value' => 1,
        ]);

        return $user->fresh();
    }

    /**
     * middleware が参照する route / request 情報を isolated Router 上で組み立てる。
     */
    private function createRequest(string $uri, string $method, string $route_uri, string $route_name, array $parameters = []): Request
    {
        $request = Request::create($uri, $method, $parameters);

        $router = new Router($this->app['events'], $this->app);
        $router->match([strtoupper($method)], $route_uri, function () {
            return response('prepared', 200);
        })->name($route_name);
        $router->dispatchToRoute($request);

        $this->app->instance('request', $request);
        $this->app->instance(Request::class, $request);
        $this->app->instance(Router::class, $router);

        return $request;
    }

    /**
     * ConnectPage の公開入口 handle() を通し、処理後の request を取得する。
     */
    private function handleRequest(Request $request): Request
    {
        $handled_request = null;
        $middleware = new ConnectPage();

        $response = $middleware->handle($request, function ($request_after_handle) use (&$handled_request) {
            $handled_request = $request_after_handle;
            return response('ok', 200);
        });

        $this->assertSame(200, $response->getStatusCode());
        $this->assertInstanceOf(Request::class, $handled_request);

        return $handled_request;
    }

    /**
     * /upload のテスト用に postInvoke() を差し替え、既存の isCan() 判定と保存値を検証可能にする。
     */
    private function bindUploadControllerForFileTest(): void
    {
        $this->app->bind(UploadController::class, function () {
            return new class extends UploadController {
                public function postInvoke(Request $request, $method = null)
                {
                    $can_upload = $this->isCan('role_reporter') || $this->isCan('role_article');
                    if (!$can_upload) {
                        return [
                            'location' => 'error',
                            'resolved_page_id' => optional($request->attributes->get('page'))->id,
                            'can_upload' => false,
                        ];
                    }

                    $upload = Uploads::create([
                        'client_original_name' => $request->file('file')->getClientOriginalName(),
                        'mimetype' => $request->file('file')->getClientMimeType(),
                        'extension' => $request->file('file')->getClientOriginalExtension(),
                        'size' => $request->file('file')->getSize(),
                        'page_id' => $request->page_id,
                        'plugin_name' => $request->plugin_name,
                    ]);

                    return [
                        'location' => url('/') . '/file/' . $upload->id,
                        'resolved_page_id' => optional($request->attributes->get('page'))->id,
                        'saved_page_id' => $upload->page_id,
                        'can_upload' => true,
                    ];
                }
            };
        });
    }

    /**
     * /upload/face のテスト用に postInvoke() を差し替え、既存の isCan() 判定と保存値を検証可能にする。
     */
    private function bindUploadControllerForFaceTest(): void
    {
        $this->app->bind(UploadController::class, function () {
            return new class extends UploadController {
                public function postInvoke(Request $request, $method = null)
                {
                    $can_upload = $this->isCan('role_reporter') || $this->isCan('role_article');
                    if (!$can_upload) {
                        return [
                            'location' => 'error',
                            'resolved_page_id' => optional($request->attributes->get('page'))->id,
                            'can_upload' => false,
                        ];
                    }

                    $upload = Uploads::create([
                        'client_original_name' => $request->file('photo')->getClientOriginalName(),
                        'mimetype' => $request->file('photo')->getClientMimeType(),
                        'extension' => $request->file('photo')->getClientOriginalExtension(),
                        'size' => $request->file('photo')->getSize(),
                        'page_id' => $request->page_id,
                        'plugin_name' => $request->plugin_name,
                    ]);

                    return [
                        'location' => url('/') . '/file/' . $upload->id,
                        'resolved_page_id' => optional($request->attributes->get('page'))->id,
                        'saved_page_id' => $upload->page_id,
                        'can_upload' => true,
                    ];
                }
            };
        });
    }

    /**
     * テストの意図:
     * /upload は route に page_id がなくても、body の page_id からページ文脈を復元し、ページ権限だけのユーザーでも保存できることを守る。
     */
    public function testUploadUsesBodyPageIdFallbackForPageRoleUser(): void
    {
        Storage::fake('local');

        $root = $this->getRootPage();
        $page = $this->createPage('target', '/target', $root);
        $user = $this->createPageRoleUser($page);

        $this->bindUploadControllerForFileTest();

        $response = $this->actingAs($user)->post('/upload', [
            'page_id' => $page->id,
            'plugin_name' => 'contents',
            'file' => UploadedFile::fake()->create('sample.txt', 10, 'text/plain'),
        ]);

        $response->assertStatus(200);
        $response->assertJsonMissing(['location' => 'error']);

        $response_json = $response->json();
        $upload = Uploads::query()->latest('id')->first();

        $this->assertTrue($response_json['can_upload']);
        $this->assertSame($page->id, $response_json['resolved_page_id']);
        $this->assertSame($page->id, $response_json['saved_page_id']);
        $this->assertSame($page->id, $upload->page_id);
    }

    /**
     * テストの意図:
     * /upload/face も /upload と同じ fallback 対象であり、ページ権限だけのユーザーで認可と page 文脈復元が通ることを守る。
     */
    public function testFaceUploadUsesBodyPageIdFallbackForPageRoleUser(): void
    {
        $root = $this->getRootPage();
        $page = $this->createPage('target', '/target', $root);
        $user = $this->createPageRoleUser($page);

        $this->bindUploadControllerForFaceTest();

        $response = $this->actingAs($user)->post('/upload/face', [
            'page_id' => $page->id,
            'plugin_name' => 'contents',
            'image_size' => 120,
            'mosaic_fineness' => 10,
            'photo' => UploadedFile::fake()->image('face.jpg', 120, 120),
        ]);

        $response->assertStatus(200);
        $response->assertJsonMissing(['location' => 'error']);

        $response_json = $response->json();

        $this->assertTrue($response_json['can_upload']);
        $this->assertSame($page->id, $response_json['resolved_page_id']);
        $this->assertSame($page->id, $response_json['saved_page_id']);
    }

    /**
     * テストの意図:
     * upload 以外の POST では body の page_id を現在ページ解決に使わず、fallback が /upload 系に閉じていることを守る。
     */
    public function testBodyPageIdDoesNotFallbackOutsideUploadRoutes(): void
    {
        $root = $this->getRootPage();
        $page = $this->createPage('target', '/target', $root);

        $request = $this->createRequest('/not-upload', 'POST', '/not-upload', 'post_dummy', [
            'page_id' => $page->id,
        ]);

        $handled_request = $this->handleRequest($request);

        $this->assertFalse($handled_request->attributes->get('page'));
    }

    /**
     * テストの意図:
     * route に page_id がある場合は upload 系でも body より route を優先し、意図しないページ権限へのすり替わりを防ぐことを守る。
     */
    public function testRoutePageIdTakesPriorityOverBodyPageId(): void
    {
        $root = $this->getRootPage();
        $route_page = $this->createPage('route target', '/route-target', $root);
        $body_page = $this->createPage('body target', '/body-target', $root);

        $request = $this->createRequest('/upload/' . $route_page->id, 'POST', '/upload/{page_id}/{method?}', 'post_upload', [
            'page_id' => $body_page->id,
        ]);

        $handled_request = $this->handleRequest($request);
        $resolved_page = $handled_request->attributes->get('page');

        $this->assertInstanceOf(Page::class, $resolved_page);
        $this->assertSame($route_page->id, $resolved_page->id);
    }
}
