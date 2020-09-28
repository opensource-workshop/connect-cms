<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddRegistControlToForms extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('forms', function (Blueprint $table) {
            $table->integer('regist_control_flag')->default(0)->comment('登録期間の制御')->after('display_to');
            $table->dateTime('regist_from')->nullable()->comment('登録期間開始日時')->after('regist_control_flag');
            $table->dateTime('regist_to')->nullable()->comment('登録期間終了日時')->after('regist_from');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('forms', function (Blueprint $table) {
            $table->dropColumn('regist_control_flag');
            $table->dropColumn('regist_from');
            $table->dropColumn('regist_to');
        });
    }
}
