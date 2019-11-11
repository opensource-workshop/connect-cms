<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddPageIdWhatsnewsDual extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('whatsnews_dual', function (Blueprint $table) {
            //
            $table->integer('page_id')->nullable()->after('id');
            $table->integer('frame_id')->nullable()->after('page_id');
            $table->integer('post_id')->nullable()->after('frame_id');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('whatsnews_dual', function (Blueprint $table) {
            //
            $table->dropColumn('page_id');
            $table->dropColumn('frame_id');
            $table->dropColumn('post_id');
        });
    }
}
