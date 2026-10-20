<?php

namespace Tests\Unit\Utilities\Preview;

use App\Utilities\Preview\PreviewDevice;
use Tests\TestCase;

/**
 * プレビュー画面サイズの許可値とiframe用URL生成を守るテスト。
 * 公開メソッドを通して、不正な端末値を固定値へ限定しプレビュー指定を生成できることを検証する。
 */
class PreviewDeviceTest extends TestCase
{
    /**
     * 画面サイズが未指定または不正でも、利用者の現在の画面から安全にプレビューを開始できること。
     */
    public function testInvalidDeviceFallsBackToCurrent(): void
    {
        $this->assertSame('current', PreviewDevice::normalize(null));
        $this->assertSame('current', PreviewDevice::normalize('"><script>alert(1)</script>'));
        $this->assertSame('現在の画面', PreviewDevice::label('invalid'));
    }

    /**
     * 利用者が選べる固定プリセットは、画面上で案内する論理寸法と一致すること。
     */
    public function testDevicesProvideExpectedDimensions(): void
    {
        $devices = PreviewDevice::devices();

        $this->assertNull($devices['current']['width']);
        $this->assertSame(1200, $devices['pc']['width']);
        $this->assertSame(768, $devices['tablet']['width']);
        $this->assertSame(390, $devices['smartphone']['width']);
        $this->assertSame(844, $devices['smartphone']['height']);
    }

    /**
     * iframe内では固定URLにプレビュー指定だけを付け、編集操作を隠すプレビューモードになること。
     */
    public function testFrameUrlAddsPreviewQuery(): void
    {
        $url = PreviewDevice::frameUrl('/news');

        $this->assertSame('/news?mode=preview&preview_frame=1', $url);
    }
}
