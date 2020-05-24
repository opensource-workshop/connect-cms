<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddCanvasLearningtasksUsersStatuses extends Migration
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
            $table->integer('canvas_answer_file_id')->nullable()->after('memo');
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
            $table->dropColumn('canvas_answer_file_id');
        });
    }
}
