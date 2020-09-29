<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddStyleLinklistFrames extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('linklist_frames', function (Blueprint $table) {
            //
            $table->integer('type')->nullable()->after('view_count');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('linklist_frames', function (Blueprint $table) {
            //
            $table->dropColumn('type');
        });
    }
}
