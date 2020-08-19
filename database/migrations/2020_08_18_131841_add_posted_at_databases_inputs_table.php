<?php

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddPostedAtDatabasesInputsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('databases_inputs', function (Blueprint $table) {
            $table->dateTime('posted_at')->comment('公開日時')->after('status');
        });

        // 公開日時(投稿日時)のカラム追加時の初期値は、更新日時をセット
        DB::statement('UPDATE databases_inputs SET posted_at = updated_at');
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('databases_inputs', function (Blueprint $table) {
            $table->dropColumn('posted_at');
        });
    }
}
