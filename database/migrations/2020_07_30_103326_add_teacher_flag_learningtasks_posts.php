<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddTeacherFlagLearningtasksPosts extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('learningtasks_posts', function (Blueprint $table) {
            //
            $table->renameColumn('join_flag', 'student_join_flag');
            $table->integer('teacher_join_flag')->nullable()->default(0)->after('join_flag')->comment('教員の参加方法');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('learningtasks_posts', function (Blueprint $table) {
            //
            $table->renameColumn('student_join_flag', 'join_flag');
            $table->dropColumn('teacher_join_flag');
        });
    }
}
