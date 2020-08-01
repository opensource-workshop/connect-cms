<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddLearningtasksFlags extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('learningtasks', function (Blueprint $table) {
            //
            $table->integer('use_report_comment')->nullable()->default(0)->after('use_report')->comment('教員からのコメント');
            $table->integer('use_report_status_collapse')->nullable()->default(0)->after('use_report_comment')->comment('レポート履歴の開閉');
            $table->integer('use_examination_correction')->nullable()->default(0)->after('use_examination')->comment('添削アップロード');
            $table->integer('use_examination_comment')->nullable()->default(0)->after('use_examination_correction')->comment('試験コメント');
            $table->integer('use_report_status_collapse')->nullable()->default(0)->after('use_report_comment')->comment('履歴の開閉');
            $table->integer('use_examination_status_collapse')->nullable()->default(0)->after('use_report_status_collapse')->comment('試験履歴の開閉');
            $table->integer('use_evaluate_comment')->nullable()->default(0)->after('use_evaluate')->comment('総合評価コメント');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('learningtasks', function (Blueprint $table) {
            //
        });
    }
}
