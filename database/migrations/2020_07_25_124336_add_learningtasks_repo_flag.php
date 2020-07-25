<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddLearningtasksRepoFlag extends Migration
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
            $table->integer('use_report')->nullable()->default(0)->after('learningtasks_name')->comment('レポート使用有無');
            $table->integer('use_examination')->nullable()->default(0)->after('use_report')->comment('試験使用有無');
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
            $table->dropColumn('use_report');
            $table->dropColumn('use_examination');
        });
    }
}
