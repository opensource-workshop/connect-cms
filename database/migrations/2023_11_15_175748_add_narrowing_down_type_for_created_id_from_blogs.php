<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddNarrowingDownTypeForCreatedIdFromBlogs extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('blogs', function (Blueprint $table) {
            $table->string('narrowing_down_type')->nullable()->comment('カテゴリ絞り込み機能')->change();
            $table->string('narrowing_down_type_for_created_id')->nullable()->comment('投稿者絞り込み機能')->after('narrowing_down_type');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('blogs', function (Blueprint $table) {
            $table->string('narrowing_down_type')->nullable()->comment('絞り込み機能')->change();
            $table->dropColumn('narrowing_down_type_for_created_id');
        });
    }
}
