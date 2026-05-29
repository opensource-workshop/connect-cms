<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddRepeatGroupIdToCalendarPosts extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('calendar_posts', function (Blueprint $table) {
            $table->string('repeat_group_id', 36)->nullable()->after('contact')->comment('繰り返し予定グループID');
            $table->index('repeat_group_id');
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
            $table->dropIndex(['repeat_group_id']);
            $table->dropColumn('repeat_group_id');
        });
    }
}
