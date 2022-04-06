<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddNc2BlockIdFromPermalinks extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('permalinks', function (Blueprint $table) {
            $table->integer('nc2_block_id')->nullable()->after('unique_id');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('permalinks', function (Blueprint $table) {
            $table->dropColumn('nc2_block_id');
        });
    }
}
