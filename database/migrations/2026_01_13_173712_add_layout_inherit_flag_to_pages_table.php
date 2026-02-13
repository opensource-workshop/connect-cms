<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class AddLayoutInheritFlagToPagesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('pages', function (Blueprint $table) {
            $table->tinyInteger('layout_inherit_flag')->default(1)->after('layout');
        });

        DB::table('pages')
            ->whereNull('layout_inherit_flag')
            ->update(['layout_inherit_flag' => 1]);

        $top_page_id = DB::table('pages')->orderBy('_lft', 'asc')->value('id');
        if ($top_page_id) {
            DB::table('pages')
                ->where('id', $top_page_id)
                ->update(['layout_inherit_flag' => 0]);
        }
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('pages', function (Blueprint $table) {
            $table->dropColumn('layout_inherit_flag');
        });
    }
}
