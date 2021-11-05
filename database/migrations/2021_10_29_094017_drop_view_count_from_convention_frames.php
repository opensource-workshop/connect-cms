<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class DropViewCountFromConventionFrames extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('convention_frames', function (Blueprint $table) {
            $table->dropColumn('view_count');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('convention_frames', function (Blueprint $table) {
            $table->integer('view_count')->nullable()->comment('1ページの表示件数')->after('frame_id');
        });
    }
}
