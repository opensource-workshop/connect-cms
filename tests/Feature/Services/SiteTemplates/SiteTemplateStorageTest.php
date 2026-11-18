<?php

namespace Tests\Feature\Services\SiteTemplates;

use App\Services\SiteTemplates\SiteTemplateDefinition;
use App\Services\SiteTemplates\SiteTemplatePreflight;
use App\Services\SiteTemplates\SiteTemplateService;
use App\User;
use Illuminate\Foundation\Testing\RefreshDatabaseState;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use InvalidArgumentException;
use Tests\TestCase;

/**
 * DBの準備不足では適用を開始しないことを確認する。
 * DDLはMySQLで暗黙コミットされるため、RefreshDatabaseのトランザクションを使わない。
 */
class SiteTemplateStorageTest extends TestCase
{
    /** 通常の専用テストDBに初期データを用意する。 */
    protected function setUp(): void
    {
        parent::setUp();
        // 既存migrationのdown()に依存せず初期化し、後続のRefreshDatabaseにも再構築を求める。
        RefreshDatabaseState::$migrated = false;
        Artisan::call('migrate:fresh', ['--force' => true]);
        $this->seed();
    }

    /** 適用記録のテーブルがなければ、事前検査で必要なテーブルを案内する。 */
    public function testMissingApplicationTableIsReported(): void
    {
        Schema::rename('site_template_applications', 'site_template_applications_missing');
        try {
            $definition = app(SiteTemplateDefinition::class)->load('school-basic');
            $state = app(SiteTemplatePreflight::class)->inspect($definition);
            $this->assertContains('必要なテーブルがありません：site_template_applications', $state['site_reasons']);
        } finally {
            Schema::rename('site_template_applications_missing', 'site_template_applications');
        }
    }

    /** ロールバックできないエンジンでは、適用記録もサイトデータも作成しない。 */
    public function testNonTransactionalStorageIsRejectedBeforeWriting(): void
    {
        $service = app(SiteTemplateService::class);
        $actor = User::firstOrFail();
        $plan = $service->plan($actor, ['template_id' => 'school-basic', 'site_name' => 'ストレージ検証学校']);
        DB::statement('ALTER TABLE site_template_applications ENGINE=MyISAM');
        try {
            try {
                $service->apply($actor, $plan);
                $this->fail('非対応エンジンでは書込み前に拒否する必要があります');
            } catch (InvalidArgumentException $exception) {
                $this->assertSame('必要なテーブルがないか、トランザクションに対応していません。', $exception->getMessage());
            }
            $this->assertDatabaseCount('site_template_applications', 0);
            $this->assertDatabaseCount('pages', 1);
            $this->assertDatabaseCount('frames', 0);
            $this->assertDatabaseCount('buckets', 0);
            $this->assertDatabaseCount('contents', 0);
        } finally {
            DB::statement('ALTER TABLE site_template_applications ENGINE=InnoDB');
        }
    }
}
