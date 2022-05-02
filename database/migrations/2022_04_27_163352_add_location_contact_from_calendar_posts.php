<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddLocationContactFromCalendarPosts extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('calendar_posts', function (Blueprint $table) {
            $table->string('location')->nullable()->comment('場所')->after('body');
            $table->string('contact')->nullable()->comment('連絡先')->after('location');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('calendar_posts', function (Blueprint $table) {
            $table->dropColumn('location');
            $table->dropColumn('contact');
        });
    }
}
