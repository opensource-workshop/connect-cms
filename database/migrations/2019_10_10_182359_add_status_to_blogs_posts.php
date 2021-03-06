<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddStatusToBlogsPosts extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('blogs_posts', function (Blueprint $table) {
            //
            $table->integer('status')->default('0')->after('post_text2');
            DB::statement('ALTER TABLE `blogs_posts` ADD COLUMN `contents_id` int(11) DEFAULT NULL AFTER `id`');
            DB::statement('UPDATE `blogs_posts` set `contents_id` = `id`');
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
            //
            $table->dropColumn('status');
            $table->dropColumn('contents_id');
        });
    }
}
