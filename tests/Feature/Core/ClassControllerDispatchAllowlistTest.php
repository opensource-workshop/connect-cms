<?php

namespace Tests\Feature\Core;

use App\Http\Middleware\ConnectPage;
use App\Models\Common\Page;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * ClassController の動的ディスパッチが明示許可したcoreメソッドだけを公開することを検証するFeatureテスト。
 * 継承メソッドや trait の公開メソッドがcoreルートから呼び出せる状態へ戻らないこと、
 * およびGETで状態変更操作を実行できる状態へ戻らないことをルート経由の境界で確認する。
 */
class ClassControllerDispatchAllowlistTest extends TestCase
{
    use RefreshDatabase;

    /**
     * GET で拒否すべき frame action の代表的な URL を返す。
     */
    public function unsafeFrameGetRouteProvider(): array
    {
        return [
            'add plugin' => ['/core/frame/addPlugin/1'],
            'destroy' => ['/core/frame/destroy/1/2'],
            'update' => ['/core/frame/update/1/2'],
            'sequence up' => ['/core/frame/sequenceUp/1/2'],
            'sequence down' => ['/core/frame/sequenceDown/1/2'],
        ];
    }

    /**
     * core GETルートでは、CoreクラスがGET用に明示許可したメソッド以外をHTTP 404で拒否すること。
     */
    public function testCoreGetRouteRejectsInheritedPublicMethod(): void
    {
        $this->withoutMiddleware(ConnectPage::class);

        $response = $this->get('/core/frame/getPlugins/1/1');

        $response->assertStatus(404);
    }

    /**
     * テストの意図:
     * フレームの状態を変更する操作は、URLを直接開いてもGETでは実行されず404で拒否されることを守る。
     *
     * @dataProvider unsafeFrameGetRouteProvider
     */
    public function testFrameStateChangingActionsAreRejectedOnGet(string $url): void
    {
        $this->withoutMiddleware(ConnectPage::class);

        $response = $this->get($url);

        $response->assertStatus(404);
    }

    /**
     * core POSTルートでは、CoreクラスがPOST用に明示許可したメソッド以外をHTTP 404で拒否すること。
     */
    public function testCorePostRouteRejectsInheritedPublicMethod(): void
    {
        $this->withoutMiddleware(ConnectPage::class);

        $response = $this->post('/core/frame/getPlugins/1/1');

        $response->assertStatus(404);
    }

    /**
     * core POSTルートでは、明示許可されたCoreメソッドが従来どおり実処理まで到達すること。
     */
    public function testCorePostRouteAllowsConfiguredPublicMethod(): void
    {
        $this->withoutMiddleware(ConnectPage::class);
        $page = Page::factory()->create([
            'permanent_link' => '/dispatch-allowlist',
        ]);

        $response = $this->post("/core/cookie/setCookieForMessageFirst/{$page->id}");

        $response->assertRedirect('/dispatch-allowlist')
            ->assertCookie('connect_cookie_message_first');
    }
}
