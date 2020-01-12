<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class ModPostIdLearningtasksUsersStatuses extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('learningtasks_users_statuses', function (Blueprint $table) {
            //
            $table->dropColumn('learningtasks_posts_id');
            $table->integer('contents_id')->default(0)->after('id');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('learningtasks_users_statuses', function (Blueprint $table) {
            //
            $table->integer('learningtasks_posts_id')->default(0)->after('id');
            $table->dropColumn('contents_id');
        });
    }
}
