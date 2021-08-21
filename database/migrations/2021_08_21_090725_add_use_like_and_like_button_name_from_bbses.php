<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddUseLikeAndLikeButtonNameFromBbses extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('bbses', function (Blueprint $table) {
            $table->integer('use_like')->default(0)->comment('いいねボタンの表示')->after('name');
            $table->string('like_button_name', 191)->nullable()->comment('いいねボタン名')->after('use_like');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('bbses', function (Blueprint $table) {
            $table->dropColumn('use_like');
            $table->dropColumn('like_button_name');
        });
    }
}
