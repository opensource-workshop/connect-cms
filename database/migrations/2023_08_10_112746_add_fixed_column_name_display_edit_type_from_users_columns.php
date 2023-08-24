<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddFixedColumnNameDisplayEditTypeFromUsersColumns extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('users_columns', function (Blueprint $table) {
            $table->integer('is_fixed_column')->default(0)->comment('固定項目か')->after('column_name');
            $table->integer('is_show_auto_regist')->default(1)->comment('自動登録で登録可')->after('is_fixed_column');
            $table->integer('is_show_my_page')->default(1)->comment('マイページで表示する')->after('is_show_auto_regist');
            $table->integer('is_edit_my_page')->default(0)->comment('マイページで変更可')->after('is_show_my_page');
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
            $table->dropColumn('is_fixed_column');
            $table->dropColumn('is_show_auto_regist');
            $table->dropColumn('is_show_my_page');
            $table->dropColumn('is_edit_my_page');
        });
    }
}
