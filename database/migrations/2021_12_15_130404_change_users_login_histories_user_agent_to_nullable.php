<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class ChangeUsersLoginHistoriesUserAgentToNullable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('users_login_histories', function (Blueprint $table) {
            // bugfix: api処理でログインした場合、ユーザエージェントが空で500エラーになるため対応
            $table->string('user_agent', 255)->nullable()->comment('ユーザーエージェント')->change();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('users_login_histories', function (Blueprint $table) {
            $table->string('user_agent', 255)->comment('ユーザーエージェント')->change();
        });
    }
}
