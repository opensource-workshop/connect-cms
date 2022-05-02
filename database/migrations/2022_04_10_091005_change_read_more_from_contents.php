<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class ChangeReadMoreFromContents extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('contents', function (Blueprint $table) {
            $table->integer('read_more_flag')->default(0)->comment('続きを表示するフラグ')->after('content2_text');
            $table->string('read_more_button')->nullable()->comment('続きを読むボタン')->after('read_more_flag');
            $table->string('close_more_button')->nullable()->comment('続きを閉じるボタン')->after('read_more_button');
            $table->dropColumn('view_more');
            $table->dropColumn('hide_more');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('contents', function (Blueprint $table) {
            $table->string('view_more', 191)->unique()->nullable()->after('content2_text');
            $table->string('hide_more', 191)->unique()->nullable()->after('view_more');
            $table->dropColumn('read_more_flag');
            $table->dropColumn('read_more_button');
            $table->dropColumn('close_more_button');
        });
    }
}
