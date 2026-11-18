<?php

use App\Services\SiteTemplates\SiteTemplateService;
use App\User;
use Illuminate\Contracts\Console\Kernel;
use Illuminate\Support\Facades\DB;

/**
 * 親が適用中のDBへ別接続で適用し、競合による拒否だけをCONFLICTで通知する。
 * 終了コード0は期待した競合を確認した場合だけ。その他は診断を標準エラーへ出す。
 */
require dirname(__DIR__, 3) . '/vendor/autoload.php';
$app = require dirname(__DIR__, 3) . '/bootstrap/app.php';
$app->make(Kernel::class)->bootstrap();

try {
    $database = getenv('SITE_TEMPLATE_CONCURRENCY_DATABASE');
    if (!$database || strpos($database, 'test') === false || DB::getDatabaseName() !== $database) {
        throw new RuntimeException('接続先が指定された専用テストDBと一致しません。');
    }
    // 親がロックを保持したまま結果を待つため、この接続だけ待ち時間を短くする。
    DB::statement('SET SESSION innodb_lock_wait_timeout = 2');
    $actor = User::firstOrFail();
    $service = $app->make(SiteTemplateService::class);
    $plan = $service->plan($actor, ['template_id' => 'school-basic', 'site_name' => '同時適用学校']);

    fwrite(STDOUT, "READY\n");
    fflush(STDOUT);
    $command = fgets(STDIN);
    if ($command === false || trim($command) !== 'APPLY') {
        throw new RuntimeException('親テストから適用開始の指示を受け取れませんでした。');
    }

    try {
        $service->apply($actor, $plan);
    } catch (InvalidArgumentException $exception) {
        if ($exception->getMessage() !== '別の適用処理と競合しました。一覧で適用結果を確認してください。') {
            throw $exception;
        }
        fwrite(STDOUT, "CONFLICT\n");
        exit(0);
    }
    throw new RuntimeException('別接続で適用中にもかかわらず二重適用に成功しました。');
} catch (Throwable $exception) {
    fwrite(STDERR, get_class($exception) . ': ' . $exception->getMessage() . "\n");
    exit(1);
}
