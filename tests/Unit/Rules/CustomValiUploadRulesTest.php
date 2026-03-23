<?php

namespace Tests\Unit\Rules;

use App\Rules\CustomValiUploadExtensions;
use App\Rules\CustomValiUploadMimetypes;
use PHPUnit\Framework\TestCase;

/**
 * ファイルアップロード判定 Rule のユニットテスト
 */
class CustomValiUploadRulesTest extends TestCase
{
    /**
     * 許可拡張子なら通ること
     */
    public function testUploadExtensionsPassesWhenExtensionIsAllowed()
    {
        $rule = new CustomValiUploadExtensions(['jpg', 'png']);

        $file = new class {
            public function getClientOriginalExtension()
            {
                return 'JPG';
            }
        };

        $this->assertTrue($rule->passes('file', $file));
    }

    /**
     * 許可外拡張子なら弾くこと
     */
    public function testUploadExtensionsFailsWhenExtensionIsDisallowed()
    {
        $rule = new CustomValiUploadExtensions(['jpg', 'png']);

        $file = new class {
            public function getClientOriginalExtension()
            {
                return 'js';
            }
        };

        $this->assertFalse($rule->passes('file', $file));
    }

    /**
     * MIME はサーバ側判定値を優先すること
     */
    public function testUploadMimetypesUsesDetectedMimeTypeFirst()
    {
        $rule = new CustomValiUploadMimetypes([
            'jpg' => ['image/jpeg'],
        ], ['jpg']);

        $file = new class {
            public function getClientOriginalExtension()
            {
                return 'jpg';
            }
            public function getMimeType()
            {
                return 'text/html';
            }
            public function getClientMimeType()
            {
                return 'image/jpeg';
            }
        };

        $this->assertFalse($rule->passes('file', $file));
    }

    /**
     * 拡張子とサーバ側判定MIMEタイプが一致する場合は通ること
     */
    public function testUploadMimetypesPassesWhenExtensionAndDetectedMimeTypeMatch()
    {
        $rule = new CustomValiUploadMimetypes([
            'jpg' => ['image/jpeg'],
        ], ['jpg']);

        $file = new class {
            public function getClientOriginalExtension()
            {
                return 'jpg';
            }
            public function getMimeType()
            {
                return 'image/jpeg';
            }
        };

        $this->assertTrue($rule->passes('file', $file));
    }

    /**
     * 拡張子とMIMEタイプの組み合わせが不一致なら弾くこと
     */
    public function testUploadMimetypesFailsWhenExtensionAndMimeTypeDoNotMatch()
    {
        $rule = new CustomValiUploadMimetypes([
            'jpg' => ['image/jpeg'],
            'txt' => ['text/plain'],
        ], ['jpg', 'txt']);

        $file = new class {
            public function getClientOriginalExtension()
            {
                return 'jpg';
            }
            public function getMimeType()
            {
                return 'text/plain';
            }
        };

        $this->assertFalse($rule->passes('file', $file));
    }

    /**
     * サーバ側判定 MIME が空なら失敗すること（クライアント申告 MIME にはフォールバックしない）
     */
    public function testUploadMimetypesFailsWhenDetectedMimeTypeIsEmpty()
    {
        $rule = new CustomValiUploadMimetypes([
            'jpg' => ['image/jpeg'],
        ], ['jpg']);

        $file = new class {
            public function getClientOriginalExtension()
            {
                return 'jpg';
            }
            public function getMimeType()
            {
                return '';
            }
            public function getClientMimeType()
            {
                return 'image/jpeg';
            }
        };

        $this->assertFalse($rule->passes('file', $file));
    }

    /**
     * エラーメッセージに許可拡張子の表示が含まれること
     */
    public function testUploadMimetypesMessageContainsAllowedExtensions()
    {
        $rule = new CustomValiUploadMimetypes([
            'jpg' => ['image/jpeg'],
            'png' => ['image/png'],
        ], ['jpg', 'png']);

        $message = $rule->message();
        $this->assertStringContainsString('.jpg', $message);
        $this->assertStringContainsString('.png', $message);
    }
}
