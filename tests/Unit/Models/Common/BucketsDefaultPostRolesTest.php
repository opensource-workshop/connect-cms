<?php

namespace Tests\Unit\Models\Common;

use App\Models\Common\Buckets;
use App\Models\Common\BucketsRoles;
use App\Models\Core\Configs;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\Request;
use Tests\TestCase;

/**
 * Buckets の新規バケツ向け投稿権限初期化を検証する。
 *
 * Config 解決、重複防止、既存権限の保護、削除時の後始末を
 * 公開メソッド経由で守るテストに絞る。
 */
class BucketsDefaultPostRolesTest extends TestCase
{
    use RefreshDatabase;

    /**
     * 各テストで共有Configの差し込みを外し、DBフォールバックを検証しやすくする。
     */
    protected function setUp(): void
    {
        parent::setUp();

        $this->clearSharedConfigsFromRequest();
    }

    /**
     * Config未登録の環境では既存挙動を保ち、初期権限を追加しないこと。
     */
    public function testInitializeDefaultPostRolesDoesNotCreateRolesWhenConfigIsMissing(): void
    {
        $bucket = $this->createBucket();

        $bucket->initializeDefaultPostRoles();

        $this->assertDatabaseCount('buckets_roles', 0);
    }

    /**
     * モデレータだけONなら、そのロールだけに投稿権限を初期投入すること。
     */
    public function testInitializeDefaultPostRolesCreatesOnlyArticleRoleWhenEnabled(): void
    {
        $bucket = $this->createBucket();
        $this->setDefaultPostRoleConfigs(1, 0);

        $bucket->initializeDefaultPostRoles();

        $this->assertDatabaseHas('buckets_roles', [
            'buckets_id' => $bucket->id,
            'role' => 'role_article',
            'post_flag' => 1,
            'approval_flag' => 0,
        ]);
        $this->assertDatabaseMissing('buckets_roles', [
            'buckets_id' => $bucket->id,
            'role' => 'role_reporter',
        ]);
    }

    /**
     * 編集者だけONなら、そのロールだけに投稿権限を初期投入すること。
     */
    public function testInitializeDefaultPostRolesCreatesOnlyReporterRoleWhenEnabled(): void
    {
        $bucket = $this->createBucket();
        $this->setDefaultPostRoleConfigs(0, 1);

        $bucket->initializeDefaultPostRoles();

        $this->assertDatabaseHas('buckets_roles', [
            'buckets_id' => $bucket->id,
            'role' => 'role_reporter',
            'post_flag' => 1,
            'approval_flag' => 0,
        ]);
        $this->assertDatabaseMissing('buckets_roles', [
            'buckets_id' => $bucket->id,
            'role' => 'role_article',
        ]);
    }

    /**
     * 両方ONなら、対象2ロールの初期権限をまとめて作成すること。
     */
    public function testInitializeDefaultPostRolesCreatesBothRolesWhenEnabled(): void
    {
        $bucket = $this->createBucket();
        $this->setDefaultPostRoleConfigs(1, 1);

        $bucket->initializeDefaultPostRoles();

        $this->assertDatabaseCount('buckets_roles', 2);
        $this->assertDatabaseHas('buckets_roles', [
            'buckets_id' => $bucket->id,
            'role' => 'role_article',
            'post_flag' => 1,
            'approval_flag' => 0,
        ]);
        $this->assertDatabaseHas('buckets_roles', [
            'buckets_id' => $bucket->id,
            'role' => 'role_reporter',
            'post_flag' => 1,
            'approval_flag' => 0,
        ]);
    }

    /**
     * 同じ初期化を繰り返しても、権限レコードが重複しないこと。
     */
    public function testInitializeDefaultPostRolesIsIdempotent(): void
    {
        $bucket = $this->createBucket();
        $this->setDefaultPostRoleConfigs(1, 1);

        $bucket->initializeDefaultPostRoles();
        $bucket->initializeDefaultPostRoles();

        $this->assertDatabaseCount('buckets_roles', 2);
    }

    /**
     * 既存のBucketsRolesがある場合は、その設定を勝手に上書きしないこと。
     */
    public function testInitializeDefaultPostRolesDoesNotOverwriteExistingRole(): void
    {
        $bucket = $this->createBucket();
        $this->setDefaultPostRoleConfigs(1, 0);

        BucketsRoles::create([
            'buckets_id' => $bucket->id,
            'role' => 'role_article',
            'post_flag' => 0,
            'approval_flag' => 1,
        ]);

        $bucket->initializeDefaultPostRoles();

        $this->assertDatabaseCount('buckets_roles', 1);
        $this->assertDatabaseHas('buckets_roles', [
            'buckets_id' => $bucket->id,
            'role' => 'role_article',
            'post_flag' => 0,
            'approval_flag' => 1,
        ]);
    }

    /**
     * 共通作成APIでは、投稿権限設定を持つプラグインだけ初期権限を作成すること。
     */
    public function testCreateWithDefaultPostRolesAppliesOnlyTargetPlugin(): void
    {
        $this->setDefaultPostRoleConfigs(1, 1);

        $target_bucket = Buckets::createWithDefaultPostRoles([
            'bucket_name' => '対象掲示板',
            'plugin_name' => 'bbses',
        ]);
        $non_target_bucket = Buckets::createWithDefaultPostRoles([
            'bucket_name' => '対象外リンクリスト',
            'plugin_name' => 'linklists',
        ]);

        $this->assertDatabaseHas('buckets_roles', [
            'buckets_id' => $target_bucket->id,
            'role' => 'role_article',
            'post_flag' => 1,
        ]);
        $this->assertDatabaseHas('buckets_roles', [
            'buckets_id' => $target_bucket->id,
            'role' => 'role_reporter',
            'post_flag' => 1,
        ]);
        $this->assertDatabaseMissing('buckets_roles', [
            'buckets_id' => $non_target_bucket->id,
        ]);
    }

    /**
     * 共通更新APIでは、新規作成時だけ初期権限を作成し、既存バケツ更新では作成しないこと。
     */
    public function testUpdateOrCreateWithDefaultPostRolesAppliesOnlyWhenCreated(): void
    {
        $this->setDefaultPostRoleConfigs(1, 0);

        $bucket = Buckets::updateOrCreateWithDefaultPostRoles(
            ['id' => null],
            ['bucket_name' => '新規ブログ', 'plugin_name' => 'blogs']
        );
        BucketsRoles::query()->delete();

        Buckets::updateOrCreateWithDefaultPostRoles(
            ['id' => $bucket->id],
            ['bucket_name' => '更新ブログ', 'plugin_name' => 'blogs']
        );

        $this->assertDatabaseCount('buckets_roles', 0);
    }

    /**
     * 対象プラグインの判定は、権限設定タブを持つ代表プラグインだけを対象にすること。
     */
    public function testIsDefaultPostRoleTargetPlugin(): void
    {
        $this->assertTrue(Buckets::isDefaultPostRoleTargetPlugin('bbses'));
        $this->assertTrue(Buckets::isDefaultPostRoleTargetPlugin('databases'));
        $this->assertFalse(Buckets::isDefaultPostRoleTargetPlugin('learningtasks'));
        $this->assertFalse(Buckets::isDefaultPostRoleTargetPlugin('linklists'));
        $this->assertFalse(Buckets::isDefaultPostRoleTargetPlugin('whatsnews'));
        $this->assertFalse(Buckets::isDefaultPostRoleTargetPlugin(null));
    }

    /**
     * Buckets削除時は関連するBucketsRolesも同時に片づけること。
     */
    public function testDestroyRemovesRelatedBucketsRoles(): void
    {
        $bucket = $this->createBucket();
        BucketsRoles::create([
            'buckets_id' => $bucket->id,
            'role' => 'role_article',
            'post_flag' => 1,
            'approval_flag' => 0,
        ]);
        BucketsRoles::create([
            'buckets_id' => $bucket->id,
            'role' => 'role_reporter',
            'post_flag' => 1,
            'approval_flag' => 0,
        ]);

        Buckets::destroy($bucket->id);

        $this->assertDatabaseMissing('buckets_roles', [
            'buckets_id' => $bucket->id,
            'role' => 'role_article',
        ]);
        $this->assertDatabaseMissing('buckets_roles', [
            'buckets_id' => $bucket->id,
            'role' => 'role_reporter',
        ]);
    }

    /**
     * テスト対象のバケツを1件作成する。
     */
    private function createBucket(): Buckets
    {
        return Buckets::factory()->create([
            'plugin_name' => 'contents',
        ]);
    }

    /**
     * 新規バケツ用の投稿権限Configを保存する。
     */
    private function setDefaultPostRoleConfigs(int $article_flag, int $reporter_flag): void
    {
        Configs::updateOrCreate(
            ['name' => 'new_bucket_role_article_post_flag'],
            ['category' => 'general', 'value' => $article_flag]
        );
        Configs::updateOrCreate(
            ['name' => 'new_bucket_role_reporter_post_flag'],
            ['category' => 'general', 'value' => $reporter_flag]
        );

        $this->clearSharedConfigsFromRequest();
    }

    /**
     * Request属性の共有Configを外し、DBフォールバックの条件をそろえる。
     */
    private function clearSharedConfigsFromRequest(): void
    {
        app(Request::class)->attributes->remove('configs');
    }
}
