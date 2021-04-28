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
}
