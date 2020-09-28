<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddDisplayControlToForms extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('forms', function (Blueprint $table) {
            $table->integer('display_control_flag')->default(0)->comment('表示期間の制御')->after('entry_limit_over_message');
            $table->dateTime('display_from')->nullable()->comment('表示期間開始日時')->after('display_control_flag');
            $table->dateTime('display_to')->nullable()->comment('表示期間終了日時')->after('display_from');
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
            $table->dropColumn('display_control_flag');
            $table->dropColumn('display_from');
            $table->dropColumn('display_to');
        });
    }
}
