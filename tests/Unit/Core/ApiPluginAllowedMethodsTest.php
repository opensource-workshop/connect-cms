<?php

namespace Tests\Unit\Core;

use App\Plugins\Api\Nc2Sso\Nc2Sso;
use App\Plugins\Api\Opac\OpacApi;
use App\Plugins\Api\Translate\Translate;
use App\Plugins\Api\User\UserApi;
use PHPUnit\Framework\TestCase;

/**
 * APIプラグインがAPIルートへ公開するメソッド一覧を検証するUnitテスト。
 * 動的ディスパッチの境界で使う許可リストが、意図した公開APIだけを含むことを完全一致で守る。
 */
class ApiPluginAllowedMethodsTest extends TestCase
{
    /**
     * UserApiでは、ユーザ情報取得APIだけがAPIルートから呼び出せること。
     */
    public function testUserApiAllowsOnlyInfo(): void
    {
        $this->assertSame(['info'], (new UserApi())->getAllowedApiMethods());
    }

    /**
     * OpacApiでは、外部公開する図書操作APIだけがAPIルートから呼び出せること。
     */
    public function testOpacApiAllowsOnlyPublicBookOperations(): void
    {
        $this->assertSame(
            [
                'book',
                'rent',
                'returnbook',
                'rentinfo',
            ],
            (new OpacApi())->getAllowedApiMethods()
        );
    }

    /**
     * Translateでは、翻訳実行APIだけがAPIルートから呼び出せること。
     */
    public function testTranslateAllowsOnlyPost(): void
    {
        $this->assertSame(['post'], (new Translate())->getAllowedApiMethods());
    }

    /**
     * Nc2Ssoでは、SSO入口だけがAPIルートから呼び出せること。
     */
    public function testNc2SsoAllowsOnlyIndex(): void
    {
        $this->assertSame(['index'], (new Nc2Sso())->getAllowedApiMethods());
    }
}
