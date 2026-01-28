<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

class AddBaseLayoutToConfigs extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        if (DB::table('configs')->where('name', 'base_layout')->exists()) {
            return;
        }

        $layout_default = config('connect.BASE_LAYOUT_DEFAULT');
        $top_page_layout = DB::table('pages')->orderBy('_lft', 'asc')->value('layout');
        $layout = $top_page_layout ?: $layout_default;

        DB::table('configs')->insert([
            'name' => 'base_layout',
            'value' => $layout,
            'category' => 'general',
            'additional1' => null,
            'additional2' => null,
            'additional3' => null,
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        DB::table('configs')->where('name', 'base_layout')->delete();
    }
}
