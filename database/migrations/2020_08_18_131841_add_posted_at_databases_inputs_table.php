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
            // bugfix: MySQL5.7以降では、strictモードがデフォルトで有効になったため、0000-00-00 00:00:00が有効な日付として取り扱われなくなりました。
            //         そのため、Not NullでdateTimeを定義すると、初期値に0000-00-00 00:00:00がセットされエラーになるので、->default(DB::raw('CURRENT_TIMESTAMP'))を使って、
            //         日時をセットしました。
            //$table->dateTime('posted_at')->comment('公開日時')->after('status');
            $table->dateTime('posted_at')->default(DB::raw('CURRENT_TIMESTAMP'))->comment('公開日時')->after('status');
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
