<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddContainerPageIdFromBuckets extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('buckets', function (Blueprint $table) {
            $table->integer('container_page_id')->unsigned()->nullable()->after('plugin_name');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('buckets', function (Blueprint $table) {
            $table->dropColumn('container_page_id');
        });
    }
}
