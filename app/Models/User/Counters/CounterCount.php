<?php

namespace App\Models\User\Counters;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

use App\UserableNohistory;

use Carbon\Carbon;

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

    // Carbonインスタンス（日付）に自動的に変換
    protected $dates = [
        'counted_at',
    ];

    /**
     * 累計・今日・昨日カウント取得
     */
    public static function getCount($counter_id, $counted_at = null)
    {
        $counter_count = CounterCount::select('counter_counts.*', 'yesterday_counts.day_count as yesterday_count')
                ->where('counter_counts.counter_id', $counter_id)
                ->where('counter_counts.counted_at', (new Carbon($counted_at))->format('Y-m-d'))
                ->leftJoin('counter_counts as yesterday_counts', function ($join) use ($counted_at) {
                    $join->on('yesterday_counts.counter_id', '=', 'counter_counts.counter_id')
                            ->where('yesterday_counts.counted_at', (new Carbon($counted_at))->subDay()->format('Y-m-d'));
                })
                ->first();

        return $counter_count;
    }

    /**
     * 累計・今日・昨日カウント取得. なければ作成
     */
    public static function getCountOrCreate($counter_id)
    {
        // 今日のカウント取得
        $today_count = CounterCount::getCount($counter_id);

        // 今日のカウントない
        if (is_null($today_count)) {
            // 昨日以前の最新日データを取得
            $before_counted_at = CounterCount::where('counter_id', $counter_id)->max('counted_at');

            $before_count = CounterCount::where('counter_id', $counter_id)
                    ->where('counted_at', $before_counted_at)
                    ->first();
            $before_count = $before_count ?? new CounterCount();

            // 今日カウント作成
            $today_count = CounterCount::create([
                'counter_id' => $counter_id,
                'counted_at' => now()->format('Y-m-d'),
                'day_count' => 0,
                'total_count' => $before_count->total_count,
            ]);

            // 今日のカウント再取得
            $today_count = CounterCount::getCount($counter_id, $before_counted_at);
        }

        return $today_count;
    }
}
