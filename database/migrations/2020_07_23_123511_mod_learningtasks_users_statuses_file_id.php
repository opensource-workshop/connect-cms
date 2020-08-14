<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class ModLearningtasksUsersStatusesFileId extends Migration
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
            $table->renameColumn('canvas_answer_file_id', 'upload_id');
            $table->renameColumn('memo', 'comment');
            $table->renameColumn('contents_id', 'post_id');
            $table->integer('examination_id')->nullable()->default(0)->after('canvas_answer_file_id')->comment('試験ID');
            $table->string('grade', 255)->nullable()->after('examination_id')->comment('評価');
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
            $table->renameColumn('upload_id', 'canvas_answer_file_id');
            $table->renameColumn('comment', 'memo');
            $table->renameColumn('post_id', 'contents_id');
            $table->dropColumn('examination_id');
            $table->dropColumn('grade');
        });
    }
}
