<?php

namespace Tests\Feature\Plugins\User\Contents;

use App\Models\Common\Buckets;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Feature\Plugins\User\DefaultBucketRolesFeatureTestTrait;
use Tests\TestCase;

/**
 * contents の新規バケツ初期権限を検証する。
 *
 * バケツ未作成時の画面表示と、初回投稿で実際に保存される権限が
 * 一致することをHTTP経路で守る。
 */
class ContentsDefaultBucketRolesFeatureTest extends TestCase
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
     * バケツ未作成の権限画面では、サイト管理の初期値をチェック状態に反映すること。
     */
    public function testEditBucketsRolesShowsSiteDefaultsBeforeBucketExists(): void
    {
        $admin = $this->createContentAdminUser();
        [$page, $frame] = $this->createPluginFrame('contents');
        $this->setDefaultPostRoleConfigs(1, 0);

        $response = $this->actingAs($admin)->get("/plugin/contents/editBucketsRoles/{$page->id}/{$frame->id}");

        $response->assertOk();
        $response->assertSee('id="role_article_post" checked="checked"', false);
        $response->assertDontSee('id="role_reporter_post" checked="checked"', false);
    }

    /**
     * サイト管理の初期値が両方OFFなら、未作成バケツの画面でも未チェック表示になること。
     */
    public function testEditBucketsRolesShowsUncheckedWhenSiteDefaultsAreOff(): void
    {
        $admin = $this->createContentAdminUser();
        [$page, $frame] = $this->createPluginFrame('contents');
        $this->setDefaultPostRoleConfigs(0, 0);

        $response = $this->actingAs($admin)->get("/plugin/contents/editBucketsRoles/{$page->id}/{$frame->id}");

        $response->assertOk();
        $response->assertDontSee('id="role_article_post" checked="checked"', false);
        $response->assertDontSee('id="role_reporter_post" checked="checked"', false);
    }

    /**
     * 初回投稿で作られるバケツには、サイト管理の初期値どおりの投稿権限だけが作られること。
     */
    public function testFirstStoreCreatesBucketsRolesFromSiteDefaults(): void
    {
        $admin = $this->createContentAdminUser();
        [$page, $frame] = $this->createPluginFrame('contents');
        $this->setDefaultPostRoleConfigs(1, 0);

        $response = $this->actingAs($admin)->post("/redirect/plugin/contents/store/{$page->id}/{$frame->id}", [
            'redirect_path' => url("/plugin/contents/edit/{$page->id}/{$frame->id}#frame-{$frame->id}"),
            'contents' => '初回投稿本文',
            'bucket_name' => '固定記事テスト',
        ]);

        $response->assertStatus(302);

        $bucket = Buckets::query()->findOrFail($frame->fresh()->bucket_id);
        $this->assertFrameUsesBucket($frame, $bucket);
        $this->assertDefaultPostRolesForBucket($bucket, 1, 0);
    }
}
