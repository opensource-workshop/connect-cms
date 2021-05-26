<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddDateValueToLearningtasksUseSettings extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('learningtasks_use_settings', function (Blueprint $table) {
            // レポート提出終了日時で利用
            $table->datetime('datetime_value')->nullable()->comment('日時')->after('value');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('learningtasks_use_settings', function (Blueprint $table) {
            $table->dropColumn('datetime_value');
        });
    }
}
