<?php

namespace Tests\Unit\Core;

use App\Http\Controllers\Core\CookieCore;
use App\Http\Controllers\Core\FrameCore;
use PHPUnit\Framework\TestCase;

/**
 * Coreクラスがcoreルートへ公開するメソッド一覧を検証するUnitテスト。
 * 動的ディスパッチの境界で使う許可リストが、意図したcore操作だけを含むことを完全一致で守る。
 */
class CoreAllowedMethodsTest extends TestCase
{
    /**
     * FrameCoreでは、GETで呼び出せるcoreメソッドがないこと。
     */
    public function testFrameCoreAllowsNoGetMethods(): void
    {
        $this->assertSame(
            [],
            (new FrameCore(null, null))->getAllowedCoreGetMethods()
        );
    }

    /**
     * FrameCoreでは、POSTでフレーム操作として公開するメソッドだけがcoreルートから呼び出せること。
     */
    public function testFrameCoreAllowsOnlyFrameOperationsOnPost(): void
    {
        $this->assertSame(
            [
                'addPlugin',
                'destroy',
                'update',
                'sequenceUp',
                'sequenceDown',
            ],
            (new FrameCore(null, null))->getAllowedCorePostMethods()
        );
    }

    /**
     * CookieCoreでは、GETで呼び出せるcoreメソッドがないこと。
     */
    public function testCookieCoreAllowsNoGetMethods(): void
    {
        $this->assertSame(
            [],
            (new CookieCore(null, null))->getAllowedCoreGetMethods()
        );
    }

    /**
     * CookieCoreでは、POSTで初回メッセージ確認用のcookie設定だけがcoreルートから呼び出せること。
     */
    public function testCookieCoreAllowsOnlyMessageFirstCookieSetterOnPost(): void
    {
        $this->assertSame(
            [
                'setCookieForMessageFirst',
            ],
            (new CookieCore(null, null))->getAllowedCorePostMethods()
        );
    }
}
