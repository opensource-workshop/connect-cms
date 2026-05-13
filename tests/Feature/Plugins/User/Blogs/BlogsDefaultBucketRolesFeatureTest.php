<?php

namespace Tests\Feature\Plugins\User\Blogs;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Feature\Plugins\User\DefaultBucketRolesFeatureTestTrait;
use Tests\TestCase;

/**
 * Blogs の新規バケツ初期権限を検証する。
 *
 * ブログ設定の新規保存経路で、サイト管理の投稿権限初期値が
 * 新しいバケツへ適用されることを守る。
 */
class BlogsDefaultBucketRolesFeatureTest extends TestCase
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
     * ブログ新規作成では、サイト管理の初期値どおりの投稿権限が作られること。
     */
    public function testSaveBucketsCreatesDefaultRolesOnBlogCreation(): void
    {
        $this->assertSaveBucketsCreatesDefaultRoles('blogs', [
            'blogs_id' => '',
            'blog_name' => 'ブログテスト',
            'rss' => 0,
            'rss_count' => 10,
            'use_like' => 0,
            'like_button_name' => '',
            'use_view_count_spectator' => 0,
            'narrowing_down_type' => 0,
            'narrowing_down_type_for_created_id' => 0,
            'narrowing_down_type_for_posted_month' => 0,
        ]);
    }
}
