<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class ChangeStudentJoinFlagTeacherJoinFlagToLearningtasksPosts extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('learningtasks_posts', function (Blueprint $table) {
            // bugfix: 課題作成時に、参加ユーザ設定の「受講者の参加方式」「教員の参加方法」がdefault=0に画面から選べ無い値になってるバグ修正.
            //         誰も参加させない「default=3（配置ページのメンバーシップ受講者から選ぶ）,参加者を誰も選ばない」に修正
            $table->integer('student_join_flag')->default(\LearningtaskUserJoinFlag::select)->change();
            $table->integer('teacher_join_flag')->default(\LearningtaskUserJoinFlag::select)->change();
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
            $table->integer('student_join_flag')->default(0)->change();
            $table->integer('teacher_join_flag')->default(0)->change();
        });
    }
}
