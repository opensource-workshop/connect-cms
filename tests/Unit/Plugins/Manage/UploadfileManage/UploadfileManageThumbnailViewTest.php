<?php

namespace Tests\Unit\Plugins\Manage\UploadfileManage;

use App\Models\Common\Uploads;
use Tests\TestCase;

/**
 * アップロードファイル管理一覧の画像サムネイル表示部品を検証するUnitテスト。
 *
 * 一覧全体ではなくBlade partial単体を通して、画像ファイルの確認に必要なHTML属性が出力されることを守る。
 */
class UploadfileManageThumbnailViewTest extends TestCase
{
    /**
     * 画像ファイルは一覧上のサムネイルと、ホバー拡大表示に必要なpopover属性を持つこと。
     */
    public function testImageFileShowsThumbnailWithHoverPreviewAttributes(): void
    {
        $upload = new Uploads([
            'client_original_name' => 'preview-target.jpg',
            'mimetype' => 'image/jpeg',
            'extension' => 'jpg',
        ]);
        $upload->id = 123;

        $html = view('plugins.manage.uploadfile.upload_thumbnail', ['upload' => $upload])->render();

        $this->assertStringContainsString('class="uploadfile-thumbnail-link"', $html);
        $this->assertStringContainsString('data-toggle="popover"', $html);
        $this->assertStringContainsString('data-preview-src="' . url('/file/123') . '"', $html);
        $this->assertStringContainsString('class="uploadfile-thumbnail"', $html);
        $this->assertStringContainsString('preview-target.jpg のサムネイル', $html);
    }

    /**
     * 画像以外のファイルには画像プレビューを出さず、通常のファイルリンクだけを表示すること。
     */
    public function testNonImageFileDoesNotShowThumbnailPreview(): void
    {
        $upload = new Uploads([
            'client_original_name' => 'document.pdf',
            'mimetype' => 'application/pdf',
            'extension' => 'pdf',
        ]);
        $upload->id = 456;

        $html = view('plugins.manage.uploadfile.upload_thumbnail', ['upload' => $upload])->render();

        $this->assertStringContainsString('document.pdf', $html);
        $this->assertStringNotContainsString('uploadfile-thumbnail-link', $html);
        $this->assertStringNotContainsString('data-toggle="popover"', $html);
    }
}
