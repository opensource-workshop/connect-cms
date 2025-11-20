<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddConditionalDisplayToUsersColumns extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('users_columns', function (Blueprint $table) {
            $table->integer('conditional_display_flag')->default(0)
                ->comment('条件付き表示フラグ 0:無効 1:有効')
                ->after('display_sequence');

            $table->integer('conditional_trigger_column_id')->nullable()
                ->comment('トリガー項目のID (users_columns.id)')
                ->after('conditional_display_flag');

            $table->string('conditional_operator')->nullable()
                ->comment('条件演算子 (トリガー項目の値を評価する演算子)')
                ->after('conditional_trigger_column_id');

            $table->string('conditional_value')->nullable()
                ->comment('条件の値')
                ->after('conditional_operator');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('users_columns', function (Blueprint $table) {
            $table->dropColumn('conditional_display_flag');
            $table->dropColumn('conditional_trigger_column_id');
            $table->dropColumn('conditional_operator');
            $table->dropColumn('conditional_value');
        });
    }
}
