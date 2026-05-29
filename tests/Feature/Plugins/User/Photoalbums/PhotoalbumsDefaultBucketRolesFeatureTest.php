<?php

namespace Tests\Feature\Plugins\User\Photoalbums;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Feature\Plugins\User\DefaultBucketRolesFeatureTestTrait;
use Tests\TestCase;

/**
 * Photoalbums の新規バケツ初期権限を検証する。
 *
 * フォトアルバム設定の新規保存経路で、サイト管理の投稿権限初期値が
 * 新しいバケツへ適用されることを守る。
 */
class PhotoalbumsDefaultBucketRolesFeatureTest extends TestCase
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
     * フォトアルバム新規作成では、サイト管理の初期値どおりの投稿権限が作られること。
     */
    public function testSaveBucketsCreatesDefaultRolesOnPhotoalbumCreation(): void
    {
        $this->assertSaveBucketsCreatesDefaultRoles('photoalbums', [
            'name' => 'フォトアルバムテスト',
            'image_upload_max_size' => '2048',
            'image_upload_max_px' => 'asis',
            'video_upload_max_size' => '2048',
        ]);
    }
}
