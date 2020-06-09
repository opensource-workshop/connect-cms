<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddPageMethodWhatsnewsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('whatsnews', function (Blueprint $table) {
            $table->integer('page_method')->default('0')->after('rss_count');
            $table->integer('page_count')->default('0')->after('page_method');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('whatsnews', function (Blueprint $table) {
            $table->dropColumn('page_method');
            $table->dropColumn('page_count');
        });
    }
}
