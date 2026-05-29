<?php

namespace Tests\Feature\Plugins\User\Contents;

use App\Enums\StatusType;
use App\Models\Common\Buckets;
use App\Models\Common\Frame;
use App\Models\Common\Page;
use App\Models\User\Contents\Contents;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Feature\Plugins\User\DefaultBucketRolesFeatureTestTrait;
use Tests\TestCase;

/**
 * 固定記事のバケツ選択画面におけるキーワード検索を検証する。
 *
 * HTTP経路で一覧画面を開き、固定記事の検索対象項目が
 * 利用者に見える候補の絞り込みとして機能することを守る。
 */
class ContentsBucketSearchFeatureTest extends TestCase
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
     * 固定記事の選択一覧では、フレームタイトル・データ名・本文のいずれに一致しても候補に残ること。
     */
    public function testListBucketsCanSearchByFrameTitleBucketNameAndBody(): void
    {
        $admin = $this->createContentAdminUser();
        [$page, $frame] = $this->createPluginFrame('contents');
        $bucket_page = Page::factory()->create();

        $this->createContentBucket($bucket_page, 'search-by-frame-title', '通常本文', 'MATCHフレーム');
        $this->createContentBucket($bucket_page, 'MATCH-search-by-bucket-name', '通常本文', '通常フレーム');
        $this->createContentBucket($bucket_page, 'search-by-body', '本文にMATCHがあります', '通常フレーム2');
        $this->createContentBucket($bucket_page, 'outside-content', '対象外本文', '対象外フレーム');

        $response = $this->actingAs($admin)->get("/plugin/contents/listBuckets/{$page->id}/{$frame->id}?keyword=MATCH");

        $response->assertOk();
        $response->assertSee('search-by-frame-title');
        $response->assertSee('MATCH-search-by-bucket-name');
        $response->assertSee('search-by-body');
        $response->assertDontSee('outside-content');
        $response->assertSee('name="keyword"', false);
        $response->assertSee('value="MATCH"', false);
    }

    /**
     * 固定記事検索では、数値の0も有効な検索語として扱われ、ソートやページングでも条件を維持すること。
     */
    public function testListBucketsCanSearchByZeroKeyword(): void
    {
        $admin = $this->createContentAdminUser();
        [$page, $frame] = $this->createPluginFrame('contents');
        $bucket_page = Page::factory()->create();

        $this->createContentBucket($bucket_page, '2026年度固定記事', '通常本文', '通常フレーム');
        $this->createContentBucket($bucket_page, '通常固定記事', '対象外本文', '対象外フレーム');

        $response = $this->actingAs($admin)->get("/plugin/contents/listBuckets/{$page->id}/{$frame->id}?keyword=0");

        $response->assertOk();
        $response->assertSee('2026年度固定記事');
        $response->assertDontSee('通常固定記事');
        $response->assertSee('value="0"', false);
        $response->assertSee('keyword=0', false);
        $response->assertSee('クリア');
    }

    /**
     * 固定記事の検索確認に必要なバケツ・本文・利用フレームをまとめて作成する。
     */
    private function createContentBucket(
        Page $page,
        string $bucket_name,
        string $content_text,
        string $frame_title
    ): void {
        $bucket = Buckets::create([
            'bucket_name' => $bucket_name,
            'plugin_name' => 'contents',
        ]);

        Contents::create([
            'bucket_id' => $bucket->id,
            'content_text' => $content_text,
            'status' => StatusType::active,
        ]);

        Frame::create([
            'page_id' => $page->id,
            'area_id' => 2,
            'frame_title' => $frame_title,
            'plugin_name' => 'contents',
            'bucket_id' => $bucket->id,
            'template' => 'default',
            'display_sequence' => 1,
        ]);
    }
}
