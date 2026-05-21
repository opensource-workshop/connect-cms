<?php

namespace App\Models\User\Counters;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

use App\UserableNohistory;

use Carbon\Carbon;
use Illuminate\Database\QueryException;

class CounterCount extends Model
{
    // 論理削除
    use SoftDeletes;

    // 保存時のユーザー関連データの保持（履歴なしUserable）
    use UserableNohistory;

    // 更新する項目の定義
    protected $fillable = [
        'counter_id',
        'counted_at',
        'day_count',
        'total_count',
    ];

    /**
     * キャストする必要のある属性
     */
    protected $casts = [
        'counted_at' => 'datetime',
    ];

    /**
     * 累計・今日・昨日カウント取得
     */
    public static function getCount($counter_id, $counted_at = null)
    {
        $counted_at = (new Carbon($counted_at))->format('Y-m-d');
        $yesterday_at = (new Carbon($counted_at))->subDay()->format('Y-m-d');

        $counter_count = CounterCount::select('counter_counts.*', 'yesterday_counts.day_count as yesterday_count')
                ->where('counter_counts.counter_id', $counter_id)
                ->where('counter_counts.counted_at', $counted_at)
                ->leftJoin('counter_counts as yesterday_counts', function ($join) use ($yesterday_at) {
                    $join->on('yesterday_counts.counter_id', '=', 'counter_counts.counter_id')
                            ->where('yesterday_counts.counted_at', $yesterday_at)
                            ->whereNull('yesterday_counts.deleted_at');
                })
                ->first();

        return $counter_count;
    }

    /**
     * 累計・今日・昨日カウント取得. なければ作成
     */
    public static function getCountOrCreate($counter_id)
    {
        $counted_at = now()->format('Y-m-d');

        // 今日のカウント取得
        $today_count = CounterCount::getCount($counter_id, $counted_at);

        // 今日のカウントない
        if (is_null($today_count)) {
            // 昨日以前の最新日データを取得
            $before_count = CounterCount::where('counter_id', $counter_id)
                    ->where('counted_at', '<', $counted_at)
                    ->orderBy('counted_at', 'desc')
                    ->first();

            // 今日カウント作成
            try {
                CounterCount::firstOrCreate(
                    [
                        'counter_id' => $counter_id,
                        'counted_at' => $counted_at,
                    ],
                    [
                        'day_count' => 0,
                        'total_count' => (int)($before_count->total_count ?? 0),
                    ]
                );
            } catch (QueryException $e) {
                // 並行アクセスで先に当日行が作られた場合は、作成済みの行を再取得する。
                if (is_null(CounterCount::getCount($counter_id, $counted_at))) {
                    throw $e;
                }
            }

            // 今日のカウント再取得
            $today_count = CounterCount::getCount($counter_id, $counted_at);
        }

        return $today_count;
    }
}
