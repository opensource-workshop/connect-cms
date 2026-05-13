<?php

namespace Tests\Feature\Plugins\User\Slideshows;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Feature\Plugins\User\DefaultBucketRolesFeatureTestTrait;
use Tests\TestCase;

/**
 * Slideshows の新規バケツ初期権限を検証する。
 *
 * スライドショー設定の新規保存経路で、サイト管理の投稿権限初期値が
 * 新しいバケツへ適用されることを守る。
 */
class SlideshowsDefaultBucketRolesFeatureTest extends TestCase
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
     * スライドショー新規作成では、サイト管理の初期値どおりの投稿権限が作られること。
     */
    public function testSaveBucketsCreatesDefaultRolesOnSlideshowCreation(): void
    {
        $this->assertSaveBucketsCreatesDefaultRoles('slideshows', [
            'slideshows_name' => 'スライドショーテスト',
            'control_display_flag' => 0,
            'indicators_display_flag' => 0,
            'fade_use_flag' => 0,
            'image_interval' => 5000,
            'height' => '',
        ]);
    }
}
