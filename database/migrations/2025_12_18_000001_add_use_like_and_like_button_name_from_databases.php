<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddUseLikeAndLikeButtonNameFromDatabases extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('databases', function (Blueprint $table) {
            $table->integer('use_like')->default(0)->comment('いいねボタンの表示')->after('databases_name');
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
        Schema::table('databases', function (Blueprint $table) {
            $table->dropColumn('use_like');
            $table->dropColumn('like_button_name');
        });
    }
}

