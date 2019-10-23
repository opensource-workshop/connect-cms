<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddDisplayFlagPlugins extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('plugins', function (Blueprint $table) {
            //
            $table->integer('display_flag')->default('0')->after('plugin_name_full');
            $table->integer('display_sequence')->default('0')->after('display_flag');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('plugins', function (Blueprint $table) {
            //
            $table->dropColumn('display_flag');
            $table->dropColumn('display_sequence');
        });
    }
}
