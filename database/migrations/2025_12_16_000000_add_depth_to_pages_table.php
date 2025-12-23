<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use App\Models\Common\Page;

class AddDepthToPagesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        if (!Schema::hasColumn('pages', 'depth')) {
            Schema::table('pages', function (Blueprint $table) {
                $table->unsignedInteger('depth')->default(0)->after('parent_id');
            });
        }

        $this->recalculateDepths();
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('pages', function (Blueprint $table) {
            $table->dropColumn('depth');
        });
    }

    /**
     * ページの深さを_nested set_の左右値から再計算して保存する。
     *
     * @return void
     */
    private function recalculateDepths(): void
    {
        $stack = [];
        $updates = [];
        $chunkSize = 500;

        foreach (DB::table('pages')->select('id', '_lft', '_rgt')->orderBy('_lft')->cursor() as $page) {
            while (!empty($stack)) {
                $last = $stack[count($stack) - 1];
                if ($page->_lft > $last['_rgt']) {
                    array_pop($stack);
                    continue;
                }
                break;
            }

            $depth = count($stack);

            $updates[] = [
                'id'    => $page->id,
                'depth' => $depth,
            ];

            $stack[] = ['_rgt' => $page->_rgt];

            if (count($updates) >= $chunkSize) {
                $this->bulkUpdateDepths($updates);
                $updates = [];
            }
        }

        if (!empty($updates)) {
            $this->bulkUpdateDepths($updates);
        }
    }

    /**
     * CASE 文を使って pages の depth をまとめて更新する。
     *
     * @param array<int, array{id:int, depth:int}> $updates
     * @return void
     */
    private function bulkUpdateDepths(array $updates): void
    {
        if (empty($updates)) {
            return;
        }

        $ids = array_column($updates, 'id');
        $case = 'CASE id';
        foreach ($updates as $row) {
            $id = (int) $row['id'];
            $depth = (int) $row['depth'];
            $case .= " WHEN {$id} THEN {$depth}";
        }
        $case .= ' END';

        DB::table('pages')
            ->whereIn('id', $ids)
            ->update([
                'depth' => DB::raw($case),
                'updated_at' => now(),
            ]);
    }
}
