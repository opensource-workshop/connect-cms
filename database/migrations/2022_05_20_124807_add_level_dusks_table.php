<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddLevelDusksTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('dusks', function (Blueprint $table) {
            //
            $table->string('level')->nullable()->after('img_args');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('dusks', function (Blueprint $table) {
            //
            $table->dropColumn('level');
        });
    }
}
