<?php

namespace Tests\Feature\Plugins\User\Faqs;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Feature\Plugins\User\DefaultBucketRolesFeatureTestTrait;
use Tests\TestCase;

/**
 * Faqs の新規バケツ初期権限を検証する。
 *
 * Buckets::create() へ寄せた新規作成経路で、
 * 初期権限が正しく適用されることを守る。
 */
class FaqsDefaultBucketRolesFeatureTest extends TestCase
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
     * FAQ新規作成では、サイト管理の初期値どおりの投稿権限が作られること。
     */
    public function testSaveBucketsCreatesDefaultRolesOnFaqCreation(): void
    {
        $this->assertSaveBucketsCreatesDefaultRoles('faqs', [
            'faqs_id' => '',
            'faq_name' => 'FAQテスト',
            'view_count' => 10,
            'rss' => 0,
            'rss_count' => 0,
            'sequence_conditions' => 0,
            'display_posted_at_flag' => 0,
        ], 1, 0);
    }
}
