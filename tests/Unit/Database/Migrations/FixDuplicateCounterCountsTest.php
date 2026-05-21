<?php

namespace Tests\Unit\Database\Migrations;

use App\Models\User\Counters\CounterCount;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

/**
 * counter_countsの重複補正マイグレーションを検証するテスト。
 * 既存データの補正処理として、同一日付の統合と後続日の累計再計算を守る。
 */
class FixDuplicateCounterCountsTest extends TestCase
{
    use RefreshDatabase;

    /**
     * 重複行を1日1行へ統合したとき、合算した日別カウントが当日と後続日の累計へ反映されることを守る。
     */
    public function testRecalculatesTotalCountsAfterMergedDate(): void
    {
        $this->loadMigration();
        $this->dropCounterCountUniqueIndex();
        DB::table('counter_counts')->delete();

        DB::table('counter_counts')->insert([
            [
                'counter_id' => 1,
                'counted_at' => '2026-05-19',
                'day_count' => 100,
                'total_count' => 100,
            ],
            [
                'counter_id' => 1,
                'counted_at' => '2026-05-20',
                'day_count' => 1,
                'total_count' => 101,
            ],
            [
                'counter_id' => 1,
                'counted_at' => '2026-05-20',
                'day_count' => 1,
                'total_count' => 101,
            ],
            [
                'counter_id' => 1,
                'counted_at' => '2026-05-21',
                'day_count' => 5,
                'total_count' => 106,
            ],
        ]);

        (new \FixDuplicateCounterCounts())->up();

        $merged_count = CounterCount::where('counter_id', 1)->where('counted_at', '2026-05-20')->first();
        $next_count = CounterCount::where('counter_id', 1)->where('counted_at', '2026-05-21')->first();

        $this->assertSame(1, CounterCount::where('counter_id', 1)->where('counted_at', '2026-05-20')->count());
        $this->assertSame(2, (int)$merged_count->day_count);
        $this->assertSame(102, (int)$merged_count->total_count);
        $this->assertSame(107, (int)$next_count->total_count);
    }

    /**
     * マイグレーション単体を直接実行するため、未ロード時だけ定義ファイルを読み込む。
     */
    private function loadMigration(): void
    {
        if (! class_exists(\FixDuplicateCounterCounts::class)) {
            require_once database_path('migrations/2026_05_20_000000_fix_duplicate_counter_counts.php');
        }
    }

    /**
     * 重複した既存データを投入できるよう、一時的に日別一意制約を外す。
     */
    private function dropCounterCountUniqueIndex(): void
    {
        Schema::table('counter_counts', function ($table) {
            $table->dropUnique('counter_counts_counter_id_counted_at_unique');
        });
    }
}
