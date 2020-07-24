<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class ModLearningtasksPostsFilesUpload extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('learningtasks_posts_files', function (Blueprint $table) {
            //
            $table->renameColumn('learningtasks_posts_id', 'post_id');
            $table->renameColumn('task_file_uploads_id', 'upload_id');
            $table->integer('task_flag')->nullable()->default(0)->after('task_file_uploads_id');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('learningtasks_posts_files', function (Blueprint $table) {
            //
            $table->renameColumn('post_id', 'learningtasks_posts_id');
            $table->renameColumn('upload_id', 'task_file_uploads_id');
            $table->dropColumn('task_flag');
        });
    }
}
