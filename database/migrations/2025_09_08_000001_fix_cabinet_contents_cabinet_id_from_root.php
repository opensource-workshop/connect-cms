<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

class FixCabinetContentsCabinetIdFromRoot extends Migration
{
    /**
     * Run the migrations.
     * ルート要素の cabinet_id を基準に、同一ツリー配下の cabinet_id を修正します。
     *
     * @return void
     */
    public function up()
    {
        // ルート（parent_id が NULL）のノード毎に、そのツリー配下の cabinet_id をルートの cabinet_id に揃える
        $roots = DB::table('cabinet_contents')
            ->select('id', 'cabinet_id', '_lft', '_rgt')
            ->whereNull('parent_id')
            ->get();

        foreach ($roots as $root) {
            // ルートの cabinet_id が NULL/0 の場合はスキップ（想定外だが安全側）
            if (empty($root->cabinet_id)) {
                continue;
            }

            DB::table('cabinet_contents')
                ->whereBetween('_lft', [$root->_lft, $root->_rgt])
                ->update(['cabinet_id' => $root->cabinet_id]);
        }
    }

    /**
     * Reverse the migrations.
     * 差し戻しは困難なため、何もしません。
     *
     * @return void
     */
    public function down()
    {
        // no-op
    }
}

