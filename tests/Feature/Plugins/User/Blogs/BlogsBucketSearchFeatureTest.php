<?php

namespace Tests\Feature\Plugins\User\Blogs;

use App\Enums\StatusType;
use App\Models\Common\Buckets;
use App\Models\User\Blogs\Blogs;
use App\Models\User\Blogs\BlogsPosts;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Feature\Plugins\User\DefaultBucketRolesFeatureTestTrait;
use Tests\TestCase;

/**
 * ブログのバケツ選択画面におけるキーワード検索を検証する。
 *
 * HTTP経路で一覧画面を開き、ブログ名だけを対象に候補が
 * 絞り込まれることを守る。
 */
class BlogsBucketSearchFeatureTest extends TestCase
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
     * ブログの選択一覧では、ブログ名に一致する候補だけが残り、記事本文だけの一致では候補にしないこと。
     */
    public function testListBucketsCanSearchByBlogNameOnly(): void
    {
        $admin = $this->createContentAdminUser();
        [$page, $frame] = $this->createPluginFrame('blogs');

        $this->createBlogBucket('MATCHブログ', '検索に一致しない記事本文');
        $this->createBlogBucket('対象外ブログ', '記事本文にMATCHがあります');

        $response = $this->actingAs($admin)->get("/plugin/blogs/listBuckets/{$page->id}/{$frame->id}?keyword=MATCH");

        $response->assertOk();
        $response->assertSee('MATCHブログ');
        $response->assertDontSee('対象外ブログ');
        $response->assertSee('name="keyword"', false);
        $response->assertSee('value="MATCH"', false);
    }

    /**
     * ブログ検索確認に必要なバケツ・ブログ・記事をまとめて作成する。
     */
    private function createBlogBucket(string $blog_name, string $post_text): void
    {
        $bucket = Buckets::create([
            'bucket_name' => $blog_name,
            'plugin_name' => 'blogs',
        ]);

        $blog = Blogs::create([
            'bucket_id' => $bucket->id,
            'blog_name' => $blog_name,
        ]);

        BlogsPosts::create([
            'contents_id' => null,
            'blogs_id' => $blog->id,
            'post_title' => $blog_name . 'の記事',
            'post_text' => $post_text,
            'status' => StatusType::active,
            'posted_at' => now(),
        ]);
    }
}
