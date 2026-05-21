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
        $duplicates = DB::table('counter_counts')
            ->select('counter_id', 'counted_at')
            ->groupBy('counter_id', 'counted_at')
            ->havingRaw('COUNT(*) > 1')
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

            // 1日1行へ正規化するため、日別カウントは合算し、累計は同日内の最大値を残す。
            // 論理削除済み行だけが重複している場合も、一意制約を追加できるよう1行にまとめる。
            DB::table('counter_counts')
                ->where('id', $keep_row->id)
                ->update([
                    'day_count' => $source_rows->sum('day_count'),
                    'total_count' => $source_rows->max('total_count'),
                    'updated_at' => $source_rows->max('updated_at'),
                    'deleted_at' => $keep_row->deleted_at,
                ]);

            DB::table('counter_counts')
                ->where('counter_id', $duplicate->counter_id)
                ->where('counted_at', $duplicate->counted_at)
                ->where('id', '<>', $keep_row->id)
                ->delete();
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
}
