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
            $table->integer('view_flag')->nullable()->after('categories_id');
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
            $table->dropColumn('view_flag');
        });
    }
}
