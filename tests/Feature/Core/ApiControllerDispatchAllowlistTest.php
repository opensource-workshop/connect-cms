<?php

namespace Tests\Feature\Core;

use Tests\TestCase;

/**
 * ApiController の動的ディスパッチが明示許可したAPIメソッドだけを公開することを検証するFeatureテスト。
 * 継承メソッドや trait の公開メソッドがAPIルートから呼び出せる状態へ戻らないよう、ルート経由の境界で確認する。
 */
class ApiControllerDispatchAllowlistTest extends TestCase
{
    /**
     * APIルートでは、APIプラグインが明示許可したメソッド以外を存在しないものとして扱うこと。
     */
    public function testApiRouteRejectsInheritedPublicMethod(): void
    {
        $this->withoutMiddleware();

        $response = $this->getJson('/api/user/getPlugins');

        $response->assertStatus(200)
            ->assertExactJson([
                'code' => 404,
                'message' => '指定されたメソッドは存在しません。',
            ]);
    }

    /**
     * APIルートでは、明示許可された公開APIが従来どおり各API側の検証まで到達すること。
     */
    public function testApiRouteAllowsConfiguredPublicMethod(): void
    {
        $this->withoutMiddleware();

        $response = $this->getJson('/api/user/info/test-user');

        $response->assertStatus(200)
            ->assertExactJson([
                'code' => 403,
                'message' => '秘密コードが必要です。',
            ]);
    }
}
