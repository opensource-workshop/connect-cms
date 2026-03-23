<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

class RemoveCodestudiesFromPlugins extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        DB::table('plugins')
            ->whereRaw('LOWER(plugin_name) = ?', ['codestudies'])
            ->delete();
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        // 削除済みプラグインの再表示を防ぐため、復元はしない。
    }
}
