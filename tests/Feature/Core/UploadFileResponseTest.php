<?php

namespace Tests\Feature\Core;

use App\Models\Common\Uploads;
use App\Models\Core\Configs;
use App\Traits\ConnectCommonTrait;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

/**
 * アップロードファイル配信ヘッダの振る舞いを検証するFeatureテスト。
 */
class UploadFileResponseTest extends TestCase
{
    use RefreshDatabase;
    use ConnectCommonTrait;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed();
    }

    private function putUploadFile(Uploads $upload, string $content = 'dummy'): void
    {
        $path = $this->getDirectory($upload->id) . '/' . $upload->id . '.' . $upload->extension;
        Storage::disk('local')->put($path, $content);
    }

    /**
     * /file/{id} では html が attachment で返ること（インライン禁止）。
     */
    public function testGetFileReturnsAttachmentForHtml(): void
    {
        $upload = Uploads::factory()->create([
            'client_original_name' => 'sample.html',
            'mimetype' => 'text/html',
            'extension' => 'html',
            'plugin_name' => 'forms',
            'page_id' => 0,
            'temporary_flag' => 0,
        ]);

        $this->putUploadFile($upload, '<html><body>test</body></html>');

        $response = $this->get("/file/{$upload->id}");

        $response->assertStatus(200);
        $this->assertStringContainsString('attachment', (string) $response->headers->get('Content-Disposition'));
    }

    /**
     * /file/user/{dir}/{filename} では html のインライン表示を維持すること。
     */
    public function testGetUserFileKeepsInlineForHtml(): void
    {
        $dir = 'feature_userdir';
        $filename = 'sample.html';
        Storage::disk('user')->put($dir . '/' . $filename, '<html><body>test</body></html>');

        Configs::create([
            'category' => 'userdir_allow',
            'name' => $dir,
            'value' => 'allow_all',
        ]);

        $response = $this->get("/file/user/{$dir}/{$filename}");

        $response->assertStatus(200);
        $this->assertStringContainsString('inline', (string) $response->headers->get('Content-Disposition'));
    }
}
