<?php

namespace Tests\Feature\Plugins\User\Cabinets;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Feature\Plugins\User\DefaultBucketRolesFeatureTestTrait;
use Tests\TestCase;

/**
 * Cabinets の新規バケツ初期権限を検証する。
 *
 * キャビネット設定の新規保存経路で、サイト管理の投稿権限初期値が
 * 新しいバケツへ適用されることを守る。
 */
class CabinetsDefaultBucketRolesFeatureTest extends TestCase
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
     * キャビネット新規作成では、サイト管理の初期値どおりの投稿権限が作られること。
     */
    public function testSaveBucketsCreatesDefaultRolesOnCabinetCreation(): void
    {
        $this->assertSaveBucketsCreatesDefaultRoles('cabinets', [
            'name' => 'キャビネットテスト',
            'upload_max_size' => '2048',
        ]);
    }
}
