<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddReadMoreToBlogsPosts extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('blogs_posts', function (Blueprint $table) {
            $table->integer('read_more_flag')->default(0)->comment('続きを表示するフラグ')->after('post_text2');
            $table->string('read_more_button', 255)->nullable()->comment('続きを読むボタン')->after('read_more_flag');
            $table->string('close_more_button', 255)->nullable()->comment('続きを閉じるボタン')->after('read_more_button');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('blogs_posts', function (Blueprint $table) {
            $table->dropColumn('read_more_flag');
            $table->dropColumn('read_more_button');
            $table->dropColumn('close_more_button');
        });
    }
}
