<?php

namespace Tests\Feature\Services\SiteTemplates;

use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

/**
 * トランザクションで包まない専用DBを別プロセスから更新し、管理画面の適用処理同士の排他を確認する。
 * @group concurrency
 */
class SiteTemplateConcurrencyTest extends TestCase
{
    /** 同じ初期サイトに同時実行しても成功は一回で、半端な生成物を残さない。 */
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
        \Illuminate\Support\Facades\Schema::rename('site_template_applications', 'site_template_applications_missing');
        try {
            $definition = app(\App\Services\SiteTemplates\SiteTemplateDefinition::class)->load('school-basic');
            $state = app(\App\Services\SiteTemplates\SiteTemplatePreflight::class)->inspect($definition);
            $this->assertNotEmpty($state['site_reasons']);
        } finally {
            \Illuminate\Support\Facades\Schema::rename('site_template_applications_missing', 'site_template_applications');
        }
        $service = app(\App\Services\SiteTemplates\SiteTemplateService::class);
        $actor = \App\User::first();
        $plan = $service->plan($actor, ['template_id' => 'school-basic', 'site_name' => '同時適用学校']);
        DB::statement('ALTER TABLE site_template_applications ENGINE=MyISAM');
        try {
            $service->apply($actor, $plan);
            $this->fail('非対応エンジンでは履歴予約前に拒否する必要があります');
        } catch (\InvalidArgumentException $exception) {
            $this->assertInstanceOf(\InvalidArgumentException::class, $exception);
            $this->assertDatabaseCount('site_template_applications', 0);
        } finally {
            DB::statement('ALTER TABLE site_template_applications ENGINE=InnoDB');
        }
        $processes = [];
        $pipes = [];
        try {
            for ($index = 0; $index < 2; $index++) {
                $processes[$index] = proc_open([PHP_BINARY, '-r', $this->concurrentApplicationScript()], [
                    0 => ['pipe', 'r'], 1 => ['pipe', 'w'], 2 => ['pipe', 'w'],
                ], $pipes[$index], base_path());
                $this->assertIsResource($processes[$index]);
                stream_set_timeout($pipes[$index][1], 30);
            }
            foreach ($pipes as $pipe) {
                $this->assertSame("READY\n", fgets($pipe[1]));
            }
            foreach ($pipes as $pipe) {
                fwrite($pipe[0], "APPLY\n");
                fclose($pipe[0]);
            }
            $codes = [];
            foreach ($processes as $index => $process) {
                $output = stream_get_contents($pipes[$index][1]);
                $errors = stream_get_contents($pipes[$index][2]);
                fclose($pipes[$index][1]);
                fclose($pipes[$index][2]);
                $codes[] = proc_close($process);
                unset($processes[$index]);
                $this->assertSame('', $errors, $output);
            }
            sort($codes);
            $this->assertSame([0, 2], $codes);
            $this->assertDatabaseCount('site_template_applications', 1);
            $this->assertDatabaseCount('pages', 10);
            $this->assertDatabaseCount('frames', 17);
            $this->assertDatabaseCount('buckets', 12);
            $this->assertDatabaseCount('contents', 9);
        } finally {
            foreach ($processes as $process) {
                proc_terminate($process);
                proc_close($process);
            }
        }
    }

    /** 独立したDB接続と開始待ち合わせが必要なため、子プロセス用の実行コードを返す。 */
    private function concurrentApplicationScript(): string
    {
        return <<<'PHP'
// 別プロセスから同じ確認済み状態を同時適用するテスト専用ワーカー。
require getcwd() . '/vendor/autoload.php';
$app = require getcwd() . '/bootstrap/app.php';
$app->make(\Illuminate\Contracts\Console\Kernel::class)->bootstrap();
if ($app['config']->get('database.connections.mysql.database') !== getenv('SITE_TEMPLATE_CONCURRENCY_DATABASE')) {
    exit(3);
}
$service = $app->make(\App\Services\SiteTemplates\SiteTemplateService::class);
$actor = \App\User::first();
$input = ['template_id' => 'school-basic', 'site_name' => '同時適用学校'];
$plan = $service->plan($actor, $input);
fwrite(STDOUT, "READY\n");
fflush(STDOUT);
if (trim(fgets(STDIN)) !== 'APPLY') {
    exit(3);
}
try {
    // Webの確認済みセッションから実行する公開アクションを通す。
    $app['auth']->login($actor);
    $request = \Illuminate\Http\Request::create('/manage/site/applySiteTemplate', 'POST', ['confirmation_token' => 'test-token', 'confirmed' => '1']);
    $request->setUserResolver(function () use ($actor) {
        return $actor;
    });
    $session = $app['session']->driver();
    $request->setLaravelSession($session);
    unset($plan['state']);
    $session->put('site_template_confirmation', ['token' => 'test-token', 'expires_at' => time() + 900, 'plan' => $plan]);
    $response = (new \App\Plugins\Manage\SiteManage\SiteManage())->applySiteTemplate($request);
    exit(strpos($response->getTargetUrl(), '/siteTemplateResult/') !== false ? 0 : 2);
} catch (\InvalidArgumentException $exception) {
    exit(2);
}
PHP;
    }

}
