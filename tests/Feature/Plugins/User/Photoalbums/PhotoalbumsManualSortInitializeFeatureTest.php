<?php

namespace Tests\Feature\Plugins\User\Photoalbums;

use App\Enums\PhotoalbumFrameConfig;
use App\Enums\PhotoalbumPlayviewType;
use App\Enums\PhotoalbumSort;
use App\Enums\ResizedImageSize;
use App\Enums\ShowType;
use App\Enums\UploadMaxSize;
use App\Enums\UseType;
use App\Models\Common\Buckets;
use App\Models\Common\Frame;
use App\Models\Common\Page;
use App\Models\Common\Uploads;
use App\Models\Core\UsersRoles;
use App\Models\User\Photoalbums\Photoalbum;
use App\Models\User\Photoalbums\PhotoalbumContent;
use App\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * フォトアルバムの表示設定で、既存の並び順を元にカスタム順を作り直す振る舞いを検証する。
 * 実際の保存リクエスト経由で、フレーム設定保存と表示順の再採番が同時に成立することを守る。
 */
class PhotoalbumsManualSortInitializeFeatureTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed();
    }

    /**
     * アルバム並び順をカスタム順にするとき、選択した名前順でカスタム順を初期化できること。
     */
    public function testSaveViewInitializesFolderManualSortFromSelectedSort(): void
    {
        [$page, $frame, $root, $photoalbum] = $this->makePhotoalbumFrame();
        $gamma = $this->createFolderContent($root, $photoalbum->id, 1, 'gamma');
        $alpha = $this->createFolderContent($root, $photoalbum->id, 2, 'alpha');
        $beta = $this->createFolderContent($root, $photoalbum->id, 3, 'beta');

        $admin = $this->createContentAdminUser();
        $response = $this->actingAs($admin)->post(
            "/redirect/plugin/photoalbums/saveView/{$page->id}/{$frame->id}/{$photoalbum->id}",
            $this->makeSaveViewPayload([
                'redirect_path' => "/plugin/photoalbums/editView/{$page->id}/{$frame->id}/{$photoalbum->bucket_id}",
                'sort_folder' => PhotoalbumSort::manual_order,
                'sort_file' => PhotoalbumSort::name_asc,
                'manual_sort_initialize_folder' => PhotoalbumSort::name_asc,
            ])
        );

        $response->assertStatus(302);
        $this->assertSame(
            [$alpha->id, $beta->id, $gamma->id],
            $this->getOrderedChildIds($root->id, PhotoalbumContent::is_folder_on)
        );
        $this->assertDatabaseHas('frame_configs', [
            'frame_id' => $frame->id,
            'name' => PhotoalbumFrameConfig::sort_folder,
            'value' => PhotoalbumSort::manual_order,
        ]);
    }

    /**
     * 写真並び順をカスタム順にするとき、アップロードファイル名の降順でカスタム順を初期化できること。
     */
    public function testSaveViewInitializesFileManualSortFromSelectedSort(): void
    {
        [$page, $frame, $root, $photoalbum] = $this->makePhotoalbumFrame();
        $alpha = $this->createImageContent($root, $photoalbum->id, 1, 'alpha.jpg');
        $gamma = $this->createImageContent($root, $photoalbum->id, 2, 'gamma.jpg');
        $beta = $this->createImageContent($root, $photoalbum->id, 3, 'beta.jpg');

        $admin = $this->createContentAdminUser();
        $response = $this->actingAs($admin)->post(
            "/redirect/plugin/photoalbums/saveView/{$page->id}/{$frame->id}/{$photoalbum->id}",
            $this->makeSaveViewPayload([
                'redirect_path' => "/plugin/photoalbums/editView/{$page->id}/{$frame->id}/{$photoalbum->bucket_id}",
                'sort_folder' => PhotoalbumSort::name_asc,
                'sort_file' => PhotoalbumSort::manual_order,
                'manual_sort_initialize_file' => PhotoalbumSort::name_desc,
            ])
        );

        $response->assertStatus(302);
        $this->assertSame(
            [$gamma->id, $beta->id, $alpha->id],
            $this->getOrderedChildIds($root->id, PhotoalbumContent::is_folder_off)
        );
        $this->assertDatabaseHas('frame_configs', [
            'frame_id' => $frame->id,
            'name' => PhotoalbumFrameConfig::sort_file,
            'value' => PhotoalbumSort::manual_order,
        ]);
    }

    /**
     * フォトアルバム用のページ・フレーム・バケツ・ルートを作る。
     */
    private function makePhotoalbumFrame(): array
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

        return [$page, $frame, $root, $photoalbum];
    }

    /**
     * 表示設定保存に必要な既定リクエストを作る。
     */
    private function makeSaveViewPayload(array $overrides = []): array
    {
        return array_merge([
            'redirect_path' => '/',
            'hidden_folder_ids' => [''],
            PhotoalbumFrameConfig::download => ShowType::not_show,
            PhotoalbumFrameConfig::posted_at => ShowType::not_show,
            PhotoalbumFrameConfig::load_more_use_flag => UseType::not_use,
            PhotoalbumFrameConfig::embed_code => ShowType::not_show,
            PhotoalbumFrameConfig::play_view => PhotoalbumPlayviewType::play_in_list,
            PhotoalbumFrameConfig::description_list_length => null,
            PhotoalbumFrameConfig::load_more_count => null,
            PhotoalbumFrameConfig::sort_folder => PhotoalbumSort::name_asc,
            PhotoalbumFrameConfig::sort_file => PhotoalbumSort::name_asc,
            'manual_sort_initialize_folder' => null,
            'manual_sort_initialize_file' => null,
        ], $overrides);
    }

    /**
     * 子アルバムを作成する。
     */
    private function createFolderContent(
        PhotoalbumContent $parent,
        int $photoalbum_id,
        int $display_sequence,
        string $name
    ): PhotoalbumContent {
        return $parent->children()->create([
            'photoalbum_id' => $photoalbum_id,
            'upload_id' => null,
            'name' => $name,
            'is_folder' => PhotoalbumContent::is_folder_on,
            'is_cover' => PhotoalbumContent::is_cover_off,
            'display_sequence' => $display_sequence,
        ]);
    }

    /**
     * 画像コンテンツを作成する。
     */
    private function createImageContent(
        PhotoalbumContent $parent,
        int $photoalbum_id,
        int $display_sequence,
        string $original_name
    ): PhotoalbumContent {
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
     * 指定した親直下のコンテンツIDを表示順どおりに返す。
     */
    private function getOrderedChildIds(int $parent_id, int $is_folder): array
    {
        return PhotoalbumContent::where('parent_id', $parent_id)
            ->where('is_folder', $is_folder)
            ->orderBy('display_sequence')
            ->orderBy('id')
            ->pluck('id')
            ->all();
    }

    /**
     * コンテンツ管理者権限を持つユーザーを作成する。
     */
    private function createContentAdminUser(): User
    {
        $user = User::factory()->create();

        UsersRoles::factory()->create([
            'users_id' => $user->id,
            'target' => 'base',
            'role_name' => 'role_article_admin',
            'role_value' => 1,
        ]);

        return $user;
    }
}
