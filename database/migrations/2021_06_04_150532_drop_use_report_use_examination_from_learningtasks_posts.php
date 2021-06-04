<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class DropUseReportUseExaminationFromLearningtasksPosts extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('learningtasks_posts', function (Blueprint $table) {
            $table->dropColumn('use_report');
            $table->dropColumn('use_examination');
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
            $table->integer('use_report')->nullable()->default(null)->after('required_canvas_answer')->comment('レポート使用有無');
            $table->integer('use_examination')->nullable()->default(null)->after('use_report')->comment('試験使用有無');
        });
    }
}
