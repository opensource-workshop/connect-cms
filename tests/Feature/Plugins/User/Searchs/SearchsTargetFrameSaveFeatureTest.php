<?php

namespace Tests\Feature\Plugins\User\Searchs;

use App\Enums\SearchsFrameSelect;
use App\Enums\SearchsPageSelect;
use App\Models\Common\Buckets;
use App\Models\User\Searchs\Searchs;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Feature\Plugins\User\DefaultBucketRolesFeatureTestTrait;
use Tests\TestCase;

/**
 * サイト内検索設定の対象フレーム保存を検証する。
 *
 * 設定保存の公開ルートを通して、フレーム選択UIの表示状態に左右されず、
 * 既存の対象フレーム指定が意図せず失われないことを守る。
 */
class SearchsTargetFrameSaveFeatureTest extends TestCase
{
    use DefaultBucketRolesFeatureTestTrait;
    use RefreshDatabase;

    /**
     * テスト前に初期データを投入する。
     */
    protected function setUp(): void
    {
        parent::setUp();

        $this->seed();
    }

    /**
     * 「全て表示する」で保存しても、画面上では使わない既存の対象フレーム指定を保持すること。
     */
    public function testSaveBucketsKeepsTargetFrameIdsWhenAllFramesRequestOmitsThem(): void
    {
        $admin = $this->createContentAdminUser();
        [$page, $frame] = $this->createPluginFrame('searchs');
        $searchs = $this->createSearchsBucket($frame->id, '10,20');

        $response = $this->actingAs($admin)->post("/redirect/plugin/searchs/saveBuckets/{$page->id}/{$frame->id}", [
            'redirect_path' => url("/plugin/searchs/editBuckets/{$page->id}/{$frame->id}#frame-{$frame->id}"),
            'searchs_id' => $searchs->id,
            'search_name' => 'サイト内検索を更新',
            'count' => 20,
            'view_posted_name' => 0,
            'view_posted_at' => 0,
            'target_plugin' => ['blogs' => 'blogs'],
            'frame_select' => SearchsFrameSelect::all_frames,
            'recieve_keyword' => 0,
            'page_select' => SearchsPageSelect::all_pages,
        ]);

        $response->assertStatus(302);
        $this->assertDatabaseHas('searchs', [
            'id' => $searchs->id,
            'frame_select' => SearchsFrameSelect::all_frames,
            'target_frame_ids' => '10,20',
        ]);
    }

    /**
     * 「選択したものだけ表示する」で保存した場合は、送信された対象フレーム指定へ更新すること。
     */
    public function testSaveBucketsUpdatesTargetFrameIdsWhenSelectedOnlySendsThem(): void
    {
        $admin = $this->createContentAdminUser();
        [$page, $frame] = $this->createPluginFrame('searchs');
        $searchs = $this->createSearchsBucket($frame->id, '10,20');

        $response = $this->actingAs($admin)->post("/redirect/plugin/searchs/saveBuckets/{$page->id}/{$frame->id}", [
            'redirect_path' => url("/plugin/searchs/editBuckets/{$page->id}/{$frame->id}#frame-{$frame->id}"),
            'searchs_id' => $searchs->id,
            'search_name' => 'サイト内検索を更新',
            'count' => 20,
            'view_posted_name' => 0,
            'view_posted_at' => 0,
            'target_plugin' => ['blogs' => 'blogs'],
            'frame_select' => SearchsFrameSelect::selected_only,
            'target_frame_ids' => [30 => 30],
            'recieve_keyword' => 0,
            'page_select' => SearchsPageSelect::all_pages,
        ]);

        $response->assertStatus(302);
        $this->assertDatabaseHas('searchs', [
            'id' => $searchs->id,
            'frame_select' => SearchsFrameSelect::selected_only,
            'target_frame_ids' => '30',
        ]);
    }

    /**
     * 既存バケツとサイト内検索設定を作成し、指定フレームに紐づける。
     */
    private function createSearchsBucket(int $frame_id, string $target_frame_ids): Searchs
    {
        $bucket = Buckets::factory()->create([
            'bucket_name' => 'サイト内検索',
            'plugin_name' => 'searchs',
        ]);
        $this->assertDatabaseHas('frames', [
            'id' => $frame_id,
        ]);

        app('db')->table('frames')
            ->where('id', $frame_id)
            ->update(['bucket_id' => $bucket->id]);

        return Searchs::create([
            'bucket_id' => $bucket->id,
            'search_name' => 'サイト内検索',
            'count' => 10,
            'view_posted_name' => 0,
            'view_posted_at' => 0,
            'target_plugins' => 'blogs',
            'frame_select' => SearchsFrameSelect::selected_only,
            'target_frame_ids' => $target_frame_ids,
            'recieve_keyword' => 0,
            'page_select' => SearchsPageSelect::all_pages,
        ]);
    }
}
