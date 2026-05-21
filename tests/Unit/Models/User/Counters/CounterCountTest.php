<?php

namespace Tests\Unit\Models\User\Counters;

use App\Models\User\Counters\CounterCount;
use Carbon\Carbon;
use Illuminate\Database\QueryException;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * CounterCountモデルの日別カウント作成と取得を検証するテスト。
 * 公開メソッド経由で、同一日付の重複防止と昨日カウント取得の契約を守る。
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
}
