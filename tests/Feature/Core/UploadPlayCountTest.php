<?php

namespace Tests\Feature\Core;

use App\Models\Common\Uploads;
use App\Traits\ConnectCommonTrait;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

/**
 * アップロード再生回数APIの振る舞いを検証するFeatureテスト。
 */
class UploadPlayCountTest extends TestCase
{
    use RefreshDatabase;
    use ConnectCommonTrait;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed();
        Storage::fake('local');
    }

    private function putUploadFile(Uploads $upload): void
    {
        $path = $this->getDirectory($upload->id) . '/' . $upload->id . '.' . $upload->extension;
        Storage::disk('local')->put($path, 'dummy');
    }

    /**
     * 動画ファイルの再生でplay_countが増え、JSONで結果が返ること。
     */
    public function testPlayCountIncrementsForVideoFile(): void
    {
        $upload = Uploads::factory()->create([
            'client_original_name' => 'sample.mp4',
            'mimetype' => 'video/mp4',
            'extension' => 'mp4',
            'page_id' => 0,
            'temporary_flag' => 0,
        ]);

        $this->putUploadFile($upload);

        $response = $this->postJson("/api/uploads/play/{$upload->id}");

        $response->assertStatus(200)
            ->assertJson(['play_count' => 1]);

        $this->assertSame(1, $upload->fresh()->play_count);
    }

    /**
     * 非メディアは404となり、play_countが変わらないこと。
     */
    public function testPlayCountReturns404ForNonMediaFile(): void
    {
        $upload = Uploads::factory()->jpg()->create([
            'page_id' => 0,
            'temporary_flag' => 0,
        ]);

        $this->putUploadFile($upload);

        $response = $this->postJson("/api/uploads/play/{$upload->id}");

        $response->assertStatus(404);
        $this->assertSame(0, $upload->fresh()->play_count);
    }

    /**
     * 実体ファイルがない場合は404となり、play_countが変わらないこと。
     */
    public function testPlayCountReturns404WhenFileMissing(): void
    {
        $upload = Uploads::factory()->create([
            'client_original_name' => 'sample.mp4',
            'mimetype' => 'video/mp4',
            'extension' => 'mp4',
            'page_id' => 0,
            'temporary_flag' => 0,
        ]);

        $response = $this->postJson("/api/uploads/play/{$upload->id}");

        $response->assertStatus(404);
        $this->assertSame(0, $upload->fresh()->play_count);
    }
}
