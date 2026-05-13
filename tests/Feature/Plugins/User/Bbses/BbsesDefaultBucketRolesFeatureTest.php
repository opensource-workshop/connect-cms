<?php

namespace Tests\Feature\Plugins\User\Bbses;

use App\Models\Common\Buckets;
use App\Models\Common\BucketsRoles;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Feature\Plugins\User\DefaultBucketRolesFeatureTestTrait;
use Tests\TestCase;

/**
 * Bbses の新規バケツ初期権限を検証する。
 *
 * 新規作成時だけ初期権限が入り、既存バケツ更新では
 * 権限を増やしたり上書きしたりしないことを守る。
 */
class BbsesDefaultBucketRolesFeatureTest extends TestCase
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
     * 新規作成時は、サイト管理の初期値どおりにBucketsRolesが作られること。
     */
    public function testSaveBucketsCreatesDefaultRolesOnlyOnNewBucket(): void
    {
        $this->assertSaveBucketsCreatesDefaultRoles('bbses', [
            'name' => '掲示板テスト',
            'use_like' => 0,
            'like_button_name' => '',
        ], 1, 0);
    }

    /**
     * 既存バケツ更新では新規初期化を走らせず、既存権限も上書きしないこと。
     */
    public function testSaveBucketsDoesNotInitializeRolesOnExistingBucketUpdate(): void
    {
        $admin = $this->createContentAdminUser();
        [$page, $frame] = $this->createPluginFrame('bbses');
        $bucket = Buckets::factory()->create([
            'bucket_name' => '既存掲示板',
            'plugin_name' => 'bbses',
        ]);
        $frame->update(['bucket_id' => $bucket->id]);
        BucketsRoles::create([
            'buckets_id' => $bucket->id,
            'role' => 'role_article',
            'post_flag' => 0,
            'approval_flag' => 1,
        ]);
        $this->setDefaultPostRoleConfigs(1, 1);

        $response = $this->actingAs($admin)->post("/redirect/plugin/bbses/saveBuckets/{$page->id}/{$frame->id}/{$bucket->id}", [
            'redirect_path' => url("/plugin/bbses/editBuckets/{$page->id}/{$frame->id}#frame-{$frame->id}"),
            'name' => '既存掲示板を更新',
            'use_like' => 0,
            'like_button_name' => '',
        ]);

        $response->assertStatus(302);

        $this->assertDatabaseCount('buckets_roles', 1);
        $this->assertDatabaseHas('buckets_roles', [
            'buckets_id' => $bucket->id,
            'role' => 'role_article',
            'post_flag' => 0,
            'approval_flag' => 1,
        ]);
        $this->assertDatabaseMissing('buckets_roles', [
            'buckets_id' => $bucket->id,
            'role' => 'role_reporter',
        ]);
    }
}
