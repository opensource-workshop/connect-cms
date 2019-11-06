<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddCategoryBlogsPosts extends Migration
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
            $table->integer('categories_id')->nullable()->after('post_text2');
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
            $table->dropColumn('categories_id');
        });
    }
}
