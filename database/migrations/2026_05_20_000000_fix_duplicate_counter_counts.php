<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class FixDuplicateCounterCounts extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        $recalculate_start_dates = [];
        $duplicates = DB::table('counter_counts')
            ->select('counter_id', 'counted_at')
            ->groupBy('counter_id', 'counted_at')
            ->havingRaw('COUNT(*) > 1')
            ->orderBy('counter_id')
            ->orderBy('counted_at')
            ->get();

        foreach ($duplicates as $duplicate) {
            $rows = DB::table('counter_counts')
                ->where('counter_id', $duplicate->counter_id)
                ->where('counted_at', $duplicate->counted_at)
                ->orderBy('id')
                ->get();

            $active_rows = $rows->filter(function ($row) {
                return is_null($row->deleted_at);
            });
            $source_rows = $active_rows->isNotEmpty() ? $active_rows : $rows;
            $keep_row = $source_rows->first();
            $day_count = $source_rows->sum('day_count');
            $total_count = $source_rows->max('total_count');

            if ($active_rows->isNotEmpty()) {
                $total_count = $this->getPreviousActiveTotalCount(
                    $duplicate->counter_id,
                    $duplicate->counted_at
                ) + $day_count;
                $recalculate_start_dates[$duplicate->counter_id] = min(
                    $recalculate_start_dates[$duplicate->counter_id] ?? $duplicate->counted_at,
                    $duplicate->counted_at
                );
            }

            // 1日1行へ正規化するため、日別カウントは合算し、累計は前日までの累計から再計算する。
            // 論理削除済み行だけが重複している場合も、一意制約を追加できるよう1行にまとめる。
            DB::table('counter_counts')
                ->where('id', $keep_row->id)
                ->update([
                    'day_count' => $day_count,
                    'total_count' => $total_count,
                    'updated_at' => $source_rows->max('updated_at'),
                    'deleted_at' => $keep_row->deleted_at,
                ]);

            DB::table('counter_counts')
                ->where('counter_id', $duplicate->counter_id)
                ->where('counted_at', $duplicate->counted_at)
                ->where('id', '<>', $keep_row->id)
                ->delete();
        }

        foreach ($recalculate_start_dates as $counter_id => $start_date) {
            $this->recalculateActiveTotalCounts($counter_id, $start_date);
        }

        // 以後はアプリケーション側で集計せず、DB制約で「同じカウンターの同じ日は1行」を保証する。
        Schema::table('counter_counts', function (Blueprint $table) {
            $table->unique(['counter_id', 'counted_at'], 'counter_counts_counter_id_counted_at_unique');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('counter_counts', function (Blueprint $table) {
            $table->dropUnique('counter_counts_counter_id_counted_at_unique');
        });
    }

    /**
     * 指定日より前の有効な最新累計を取得する。
     */
    private function getPreviousActiveTotalCount($counter_id, $counted_at)
    {
        return (int)(DB::table('counter_counts')
            ->where('counter_id', $counter_id)
            ->where('counted_at', '<', $counted_at)
            ->whereNull('deleted_at')
            ->orderBy('counted_at', 'desc')
            ->orderBy('id', 'desc')
            ->value('total_count') ?? 0);
    }

    /**
     * 重複統合で増減した日別カウントを、後続日の累計へ反映する。
     */
    private function recalculateActiveTotalCounts($counter_id, $start_date)
    {
        $total_count = $this->getPreviousActiveTotalCount($counter_id, $start_date);
        $rows = DB::table('counter_counts')
            ->where('counter_id', $counter_id)
            ->where('counted_at', '>=', $start_date)
            ->whereNull('deleted_at')
            ->orderBy('counted_at')
            ->orderBy('id')
            ->get();

        foreach ($rows as $row) {
            $total_count += (int)$row->day_count;

            DB::table('counter_counts')
                ->where('id', $row->id)
                ->update([
                    'total_count' => $total_count,
                ]);
        }
    }
}
