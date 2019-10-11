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
            $table->integer('contents_id')->nullable()->after('id');
            // run please
            // UPDATE `blogs_posts` set `contents_id` = `id`
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
