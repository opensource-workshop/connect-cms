<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddLearningtasksNeedAuth extends Migration
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
            $table->integer('use_evaluate')->nullable()->default(0)->after('use_examination')->comment('総合評価有無');
            $table->integer('need_auth')->nullable()->default(0)->after('use_evaluate')->comment('ログイン要否');
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
            $table->dropColumn('use_evaluate');
            $table->dropColumn('need_auth');
        });
    }
}
