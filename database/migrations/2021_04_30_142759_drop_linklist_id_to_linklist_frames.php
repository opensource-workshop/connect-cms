<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class DropLinklistIdToLinklistFrames extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('linklist_frames', function (Blueprint $table) {
            $table->dropColumn('linklist_id');
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
            $table->integer('linklist_id')->comment('リンクリストID')->after('id');
        });
    }
}
