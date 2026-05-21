<?php

namespace Tests\Unit\Models\User\Counters;

use App\Models\User\Counters\CounterCount;
use Carbon\Carbon;
use Illuminate\Database\QueryException;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

/**
 * CounterCountモデルと重複補正マイグレーションの日別カウント作成・取得を検証するテスト。
 * 公開メソッドとマイグレーション経由で、同一日付の重複防止と累計補正の契約を守る。
 */
class CounterCountTest extends TestCase
{
    use RefreshDatabase;

    /**
     * テスト用に固定した現在時刻を次のテストへ持ち越さない。
     */
    protected function tearDown(): void
    {
        Carbon::setTestNow();

        parent::tearDown();
    }

    /**
     * 同じカウンターの同じ日付に複数行を作れないことで、日別カウントが一覧で重複表示される回帰を防ぐ。
     */
    public function testCounterCountIsUniquePerCounterAndDate(): void
    {
        CounterCount::create([
            'counter_id' => 1,
            'counted_at' => '2026-05-20',
            'day_count' => 5,
            'total_count' => 15,
        ]);

        $this->expectException(QueryException::class);

        CounterCount::create([
            'counter_id' => 1,
            'counted_at' => '2026-05-20',
            'day_count' => 0,
            'total_count' => 15,
        ]);
    }

    /**
     * 当日行の作成を複数回要求しても1日1行に保たれ、昨日のカウントが表示用に引き継がれることを守る。
     */
    public function testGetCountOrCreateKeepsOneDailyRowAndReturnsYesterdayCount(): void
    {
        Carbon::setTestNow(Carbon::parse('2026-05-20 10:00:00'));

        CounterCount::create([
            'counter_id' => 1,
            'counted_at' => '2026-05-19',
            'day_count' => 7,
            'total_count' => 17,
        ]);

        $first_count = CounterCount::getCountOrCreate(1);
        $second_count = CounterCount::getCountOrCreate(1);

        $this->assertSame(1, CounterCount::where('counter_id', 1)->where('counted_at', '2026-05-20')->count());
        $this->assertSame(0, (int)$first_count->day_count);
        $this->assertSame(17, (int)$first_count->total_count);
        $this->assertSame(7, (int)$second_count->yesterday_count);
    }

    /**
     * 重複行を1日1行へ統合したとき、合算した日別カウントが当日と後続日の累計へ反映されることを守る。
     */
    public function testDuplicateFixMigrationRecalculatesTotalCountsAfterMergedDate(): void
    {
        $this->loadDuplicateFixMigration();
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
    private function loadDuplicateFixMigration(): void
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
