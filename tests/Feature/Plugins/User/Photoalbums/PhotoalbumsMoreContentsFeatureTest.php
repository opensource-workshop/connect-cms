<?php

namespace Tests\Feature\Plugins\User\Photoalbums;

use App\Enums\PhotoalbumFrameConfig;
use App\Enums\PhotoalbumSort;
use App\Enums\ResizedImageSize;
use App\Enums\UploadMaxSize;
use App\Enums\UseType;
use App\Models\Common\Buckets;
use App\Models\Common\Frame;
use App\Models\Common\Page;
use App\Models\Common\Uploads;
use App\Models\Core\FrameConfig;
use App\Models\User\Photoalbums\Photoalbum;
use App\Models\User\Photoalbums\PhotoalbumContent;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Config;
use Tests\TestCase;

class PhotoalbumsMoreContentsFeatureTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed();
    }

    /**
     * target が不正な場合は 400 を返すこと。
     */
    public function testMoreContentsReturns400WhenTargetIsInvalid(): void
    {
        [$page, $frame, $root] = $this->makePhotoalbumFrame();

        $response = $this->getJson("/json/photoalbums/moreContents/{$page->id}/{$frame->id}/{$root->id}?target=invalid");

        $response->assertStatus(400)
            ->assertJson([
                'message' => 'invalid target',
            ]);
    }

    /**
     * offset / limit の境界値を安全に扱うこと。
     */
    public function testMoreContentsOffsetAndLimitBoundary(): void
    {
        [$page, $frame, $root, $photoalbum] = $this->makePhotoalbumFrame([
            PhotoalbumFrameConfig::sort_file => PhotoalbumSort::manual_order,
            PhotoalbumFrameConfig::load_more_use_flag => UseType::not_use,
        ]);

        $first = $this->createImageContent($root, $photoalbum->id, 1, 'first.jpg');
        $second = $this->createImageContent($root, $photoalbum->id, 2, 'second.jpg');
        $this->createImageContent($root, $photoalbum->id, 3, 'third.jpg');
        $this->createImageContent($root, $photoalbum->id, 4, 'fourth.jpg');

        Config::set('photoalbums.load_more_max_limit', 2);

        $response = $this->getJson("/json/photoalbums/moreContents/{$page->id}/{$frame->id}/{$root->id}?target=image&offset=-5&limit=999");
        $response->assertStatus(200)
            ->assertJson([
                'next_offset' => 2,
                'total' => 4,
            ]);

        $ids = $this->extractImageIds($response->json('html'), $frame->id);
        $this->assertSame([$first->id, $second->id], $ids);

        $last_response = $this->getJson("/json/photoalbums/moreContents/{$page->id}/{$frame->id}/{$root->id}?target=image&offset=4&limit=1");
        $last_response->assertStatus(200)
            ->assertJson([
                'html' => '',
                'next_offset' => 4,
                'total' => 4,
            ]);
    }

    /**
     * index 初回表示と moreContents の並び順が一致すること（名前順・降順）。
     */
    public function testIndexAndMoreContentsKeepSameOrderForNameSort(): void
    {
        [$page, $frame, $root, $photoalbum] = $this->makePhotoalbumFrame([
            PhotoalbumFrameConfig::sort_file => PhotoalbumSort::name_desc,
            PhotoalbumFrameConfig::load_more_use_flag => UseType::use,
            PhotoalbumFrameConfig::load_more_count => 2,
        ]);

        $gamma = $this->createImageContent($root, $photoalbum->id, 1, 'gamma.jpg');
        $alpha = $this->createImageContent($root, $photoalbum->id, 2, 'alpha.jpg');
        $beta = $this->createImageContent($root, $photoalbum->id, 3, 'beta.jpg');

        $expected_order = [$gamma->id, $beta->id, $alpha->id];

        $index_response = $this->get("/plugin/photoalbums/changeDirectory/{$page->id}/{$frame->id}/{$root->id}");
        $index_response->assertStatus(200);
        $index_ids = $this->extractImageIds($index_response->getContent(), $frame->id);
        $this->assertSame(array_slice($expected_order, 0, 2), $index_ids);

        $first_more_response = $this->getJson("/json/photoalbums/moreContents/{$page->id}/{$frame->id}/{$root->id}?target=image&offset=0&limit=99");
        $first_more_response->assertStatus(200)
            ->assertJson([
                'next_offset' => 2,
                'total' => 3,
            ]);
        $first_more_ids = $this->extractImageIds($first_more_response->json('html'), $frame->id);
        $this->assertSame($index_ids, $first_more_ids);

        $second_more_response = $this->getJson("/json/photoalbums/moreContents/{$page->id}/{$frame->id}/{$root->id}?target=image&offset=2&limit=99");
        $second_more_response->assertStatus(200)
            ->assertJson([
                'next_offset' => 3,
                'total' => 3,
            ]);
        $second_more_ids = $this->extractImageIds($second_more_response->json('html'), $frame->id);

        $this->assertSame($expected_order, array_merge($first_more_ids, $second_more_ids));
    }

    /**
     * フォトアルバム用のページ・フレーム・バケツ・ルートを作る。
     *
     * @param array $frame_config_values
     * @return array
     */
    private function makePhotoalbumFrame(array $frame_config_values = []): array
    {
        $page = Page::where('permanent_link', '/')->first() ?? Page::factory()->create([
            'permanent_link' => '/',
            'page_name' => 'home',
        ]);

        $bucket = Buckets::factory()->create([
            'bucket_name' => 'テストフォトアルバム',
            'plugin_name' => 'photoalbums',
        ]);

        $frame = Frame::factory()->create([
            'page_id' => $page->id,
            'area_id' => 2,
            'plugin_name' => 'photoalbums',
            'bucket_id' => $bucket->id,
            'template' => 'default',
            'display_sequence' => 1,
        ]);

        $photoalbum = Photoalbum::create([
            'bucket_id' => $bucket->id,
            'name' => 'テストフォトアルバム',
            'image_upload_max_size' => UploadMaxSize::two_mega_byte,
            'image_upload_max_px' => ResizedImageSize::big,
            'video_upload_max_size' => UploadMaxSize::ten_mega_byte,
        ]);

        $root = PhotoalbumContent::create([
            'photoalbum_id' => $photoalbum->id,
            'upload_id' => null,
            'name' => $photoalbum->name,
            'is_folder' => PhotoalbumContent::is_folder_on,
            'is_cover' => PhotoalbumContent::is_cover_off,
            'display_sequence' => 1,
            'parent_id' => null,
        ]);

        foreach ($frame_config_values as $name => $value) {
            FrameConfig::updateOrCreate(
                ['frame_id' => $frame->id, 'name' => $name],
                ['value' => (string) $value]
            );
        }

        return [$page, $frame, $root, $photoalbum];
    }

    /**
     * 画像コンテンツを作成する。
     */
    private function createImageContent(PhotoalbumContent $parent, int $photoalbum_id, int $display_sequence, string $original_name): PhotoalbumContent
    {
        $upload = Uploads::factory()->jpg()->create([
            'client_original_name' => $original_name,
            'plugin_name' => 'photoalbums',
        ]);

        return $parent->children()->create([
            'photoalbum_id' => $photoalbum_id,
            'upload_id' => $upload->id,
            'name' => pathinfo($original_name, PATHINFO_FILENAME),
            'is_folder' => PhotoalbumContent::is_folder_off,
            'is_cover' => PhotoalbumContent::is_cover_off,
            'display_sequence' => $display_sequence,
            'mimetype' => $upload->mimetype,
        ]);
    }

    /**
     * レンダリングされた HTML から画像IDを順番に抽出する。
     *
     * @param string $html
     * @param int $frame_id
     * @return array
     */
    private function extractImageIds(string $html, int $frame_id): array
    {
        preg_match_all('/id="photo_' . $frame_id . '_(\d+)"/', $html, $matches);
        return array_map('intval', $matches[1] ?? []);
    }
}
