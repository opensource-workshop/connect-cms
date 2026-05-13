<?php

namespace Tests\Feature\Plugins\User;

use App\Models\Common\Buckets;
use App\Models\Common\Frame;
use App\Models\Common\Page;
use App\Models\Core\Configs;
use App\Models\Core\UsersRoles;
use App\User;

/**
 * 新規バケツ投稿権限Featureテストの共通補助を提供する。
 *
 * プラグインごとの公開保存経路は各テストクラスに残し、
 * 管理者作成・フレーム作成・権限検証だけを共有する。
 */
trait DefaultBucketRolesFeatureTestTrait
{
    /**
     * 指定プラグインの新規保存経路で、投稿権限初期値が作られることを検証する。
     */
    protected function assertSaveBucketsCreatesDefaultRoles(
        string $plugin_name,
        array $payload,
        int $article_flag = 1,
        int $reporter_flag = 1
    ): void {
        $admin = $this->createContentAdminUser();
        [$page, $frame] = $this->createPluginFrame($plugin_name);
        $this->setDefaultPostRoleConfigs($article_flag, $reporter_flag);

        $response = $this->actingAs($admin)->post(
            "/redirect/plugin/{$plugin_name}/saveBuckets/{$page->id}/{$frame->id}",
            array_merge(
                [
                    'redirect_path' => url("/plugin/{$plugin_name}/createBuckets/{$page->id}/{$frame->id}#frame-{$frame->id}"),
                ],
                $payload
            )
        );

        $this->assertContains($response->getStatusCode(), [200, 302]);

        $bucket = Buckets::query()
            ->where('plugin_name', $plugin_name)
            ->latest('id')
            ->firstOrFail();

        $this->assertFrameUsesBucket($frame, $bucket);
        $this->assertDefaultPostRolesForBucket($bucket, $article_flag, $reporter_flag);
    }

    /**
     * フレームが作成されたバケツを表示対象にしていることを検証する。
     */
    protected function assertFrameUsesBucket(Frame $frame, Buckets $bucket): void
    {
        $this->assertDatabaseHas('frames', [
            'id' => $frame->id,
            'bucket_id' => $bucket->id,
        ]);
    }

    /**
     * 指定バケツに、サイト管理の初期値どおりの投稿権限が作られたことを検証する。
     */
    protected function assertDefaultPostRolesForBucket(Buckets $bucket, int $article_flag, int $reporter_flag): void
    {
        $this->assertDefaultPostRoleForBucket($bucket, 'role_article', $article_flag);
        $this->assertDefaultPostRoleForBucket($bucket, 'role_reporter', $reporter_flag);
    }

    /**
     * 指定ロールの投稿権限が、初期値ONなら存在し、OFFなら存在しないことを検証する。
     */
    private function assertDefaultPostRoleForBucket(Buckets $bucket, string $role, int $post_flag): void
    {
        if ($post_flag === 1) {
            $this->assertDatabaseHas('buckets_roles', [
                'buckets_id' => $bucket->id,
                'role' => $role,
                'post_flag' => 1,
                'approval_flag' => 0,
            ]);
            return;
        }

        $this->assertDatabaseMissing('buckets_roles', [
            'buckets_id' => $bucket->id,
            'role' => $role,
        ]);
    }

    /**
     * コンテンツ管理者権限を持つユーザーを作成する。
     */
    protected function createContentAdminUser(): User
    {
        $user = User::factory()->create();

        UsersRoles::factory()->create([
            'users_id' => $user->id,
            'target' => 'base',
            'role_name' => 'role_article_admin',
            'role_value' => 1,
        ]);

        return $user;
    }

    /**
     * バケツ未作成のプラグインフレームを作成する。
     *
     * @return array{0: \App\Models\Common\Page, 1: \App\Models\Common\Frame}
     */
    protected function createPluginFrame(string $plugin_name): array
    {
        $page = Page::factory()->create();
        $frame = Frame::create([
            'page_id' => $page->id,
            'area_id' => 2,
            'plugin_name' => $plugin_name,
            'bucket_id' => null,
            'template' => 'default',
            'display_sequence' => 1,
        ]);

        return [$page, $frame];
    }

    /**
     * 新規バケツ用の投稿権限Configを保存する。
     */
    protected function setDefaultPostRoleConfigs(int $article_flag, int $reporter_flag): void
    {
        Configs::updateOrCreate(
            ['name' => 'new_bucket_role_article_post_flag'],
            ['category' => 'general', 'value' => $article_flag]
        );
        Configs::updateOrCreate(
            ['name' => 'new_bucket_role_reporter_post_flag'],
            ['category' => 'general', 'value' => $reporter_flag]
        );
    }
}
