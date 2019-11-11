<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class ModBlogsCategories extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('blogs_categories', function (Blueprint $table) {
            //
            $table->dropColumn('color');
            $table->dropColumn('background_color');
            $table->dropColumn('category');
            $table->integer('categories_id')->nullable()->after('blogs_id');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('blogs_categories', function (Blueprint $table) {
            //
            $table->string('color')->after('blogs_id');
            $table->string('background_color')->after('color');
            $table->string('category')->after('background_color');
            $table->dropColumn('categories_id');
        });
    }
}
