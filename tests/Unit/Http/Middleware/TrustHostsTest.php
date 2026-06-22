<?php

namespace Tests\Unit\Http\Middleware;

use App\Http\Middleware\TrustHosts;
use Tests\TestCase;

/**
 * TrustHostsミドルウェアの許可Host設定について、APP_URLから生成される検証パターンを単体で確認する。
 * DBやルーティングに依存せず、正規Hostのみを許可する境界条件を守る。
 */
class TrustHostsTest extends TestCase
{
    /**
     * APP_URLのHostだけを許可対象にすることを確認
     */
    public function testAppUrlHostIsTrusted(): void
    {
        config([
            'app.url' => 'https://connect.example.test',
        ]);

        $hosts = $this->trustedHosts();

        $this->assertSame(['^connect\.example\.test$'], $hosts);
    }

    /**
     * APP_URLにスキームがない場合でもHost部分を許可対象として扱うことを確認
     */
    public function testAppUrlWithoutSchemeIsTrusted(): void
    {
        config([
            'app.url' => 'connect.example.test',
        ]);

        $hosts = $this->trustedHosts();

        $this->assertSame(['^connect\.example\.test$'], $hosts);
    }

    /**
     * APP_URL配下のサブドメインを暗黙に許可しないことを確認
     */
    public function testSubdomainsOfAppUrlAreNotTrusted(): void
    {
        config([
            'app.url' => 'https://example.test',
        ]);

        $hosts = $this->trustedHosts();

        $this->assertSame(['^example\.test$'], $hosts);
        $this->assertNotContains('^(.+\.)?example\.test$', $hosts);
        $this->assertNotContains('^www\.example\.test$', $hosts);
    }

    /**
     * ミドルウェアが生成する許可Hostパターンを取得する。
     *
     * @return array
     */
    private function trustedHosts(): array
    {
        return (new TrustHosts($this->app))->hosts();
    }
}
