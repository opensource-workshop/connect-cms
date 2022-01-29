<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddColumnsToDatabasesColumns extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('databases_columns', function (Blueprint $table) {
            $table->integer('body_flag')->default(0)->comment('新着等の本文指定')->after('title_flag');
            $table->integer('image_flag')->default(0)->comment('新着等のイメージ指定')->after('body_flag');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('databases_columns', function (Blueprint $table) {
            $table->dropColumn('body_flag');
            $table->dropColumn('image_flag');
        });
    }
}
