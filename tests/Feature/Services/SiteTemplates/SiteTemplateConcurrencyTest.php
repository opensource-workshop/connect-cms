<?php

namespace Tests\Feature\Services\SiteTemplates;

use App\Services\SiteTemplates\SiteTemplateService;
use App\User;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

/**
 * 別接続から適用処理を競合させ、未コミットの適用中も二重作成を拒否することを確認する。
 * @group concurrency
 */
class SiteTemplateConcurrencyTest extends TestCase
{
    /** 一方の適用が未確定の間は、別の確認済み計画を適用しても生成物を追加しない。 */
    public function testConcurrentApplication(): void
    {
        $database = getenv('SITE_TEMPLATE_CONCURRENCY_DATABASE');
        if (!$database) {
            $this->markTestSkipped('専用DB名をSITE_TEMPLATE_CONCURRENCY_DATABASEで明示してください。');
        }
        $this->assertSame($database, DB::getDatabaseName());
        $this->assertStringContainsString('test', $database);
        Artisan::call('migrate:fresh', ['--force' => true]);
        Artisan::call('db:seed', ['--force' => true]);

        $process = proc_open([PHP_BINARY, base_path('tests/Support/SiteTemplates/apply-template-worker.php')], [
            0 => ['pipe', 'r'], 1 => ['pipe', 'w'], 2 => ['pipe', 'w'],
        ], $pipes, base_path());
        $this->assertIsResource($process);
        stream_set_timeout($pipes[1], 15);
        $transaction_level = DB::transactionLevel();
        try {
            // 子の確認を先に完了させ、同じ初期状態に対する計画を持たせる。
            $this->assertSame("READY\n", fgets($pipes[1]));
            $service = app(SiteTemplateService::class);
            $actor = User::firstOrFail();
            $plan = $service->plan($actor, ['template_id' => 'school-basic', 'site_name' => '同時適用学校']);

            // 外側のトランザクションで適用記録のロックを保持する。
            // 子の終了までコミットしないので、スケジューリングに依存せず競合する。
            DB::beginTransaction();
            $service->apply($actor, $plan);
            fwrite($pipes[0], "APPLY\n");
            fclose($pipes[0]);

            $this->assertSame("CONFLICT\n", fgets($pipes[1]), '競合以外の失敗を適用拒否として扱わない');
            $errors = stream_get_contents($pipes[2]);
            $exit_code = proc_close($process);
            $process = null;
            $this->assertSame('', $errors);
            $this->assertSame(0, $exit_code);
            DB::commit();

            $this->assertDatabaseCount('site_template_applications', 1);
            $this->assertDatabaseCount('pages', 10);
            $this->assertDatabaseCount('frames', 17);
            $this->assertDatabaseCount('buckets', 12);
            $this->assertDatabaseCount('contents', 9);
        } finally {
            if (is_resource($process)) {
                proc_terminate($process);
                proc_close($process);
            }
            foreach ($pipes as $pipe) {
                if (is_resource($pipe)) {
                    fclose($pipe);
                }
            }
            while (DB::transactionLevel() > $transaction_level) {
                DB::rollBack();
            }
        }
    }
}
