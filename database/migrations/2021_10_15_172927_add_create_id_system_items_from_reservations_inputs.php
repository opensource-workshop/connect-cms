<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddCreateIdSystemItemsFromReservationsInputs extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('reservations_inputs', function (Blueprint $table) {
            // nullableに変更とコメント削除
            $table->string('input_user_id')->nullable()->change();
            $table->string('update_user_id')->nullable()->change();

            // commit前のため、リネーム前の項目でafterをセット
            // $table->integer('created_id')->nullable()->after('updated_name');
            $table->integer('created_id')->nullable()->after('update_user_id');

            // $table->string('created_name', 255)->nullable()->after('created_id');
            $table->integer('updated_id')->nullable()->after('created_at');
            // $table->string('updated_name', 255)->nullable()->after('updated_id');

            // 既に created_name, updated_nameに該当するカラムがあるためリネーム。
            // 後のマイグレーション（2021_10_15_181302_change_created_name_updated_name_position_from_reservations_inputs）でカラムの順番も直す。
            $table->renameColumn('input_user_id', 'created_name');
            $table->renameColumn('update_user_id', 'updated_name');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('reservations_inputs', function (Blueprint $table) {
            $table->dropColumn('created_id');
            // $table->dropColumn('created_name');
            $table->dropColumn('updated_id');
            // $table->dropColumn('updated_name');

            // nullableを外す
            // commit前のため、リネーム前の項目でセット
            // $table->string('input_user_id')->comment('登録者ID')->change();
            // $table->string('update_user_id')->comment('更新者ID')->change();
            $table->string('created_name')->comment('登録者ID')->change();
            $table->string('updated_name')->comment('更新者ID')->change();

            $table->renameColumn('created_name', 'input_user_id');
            $table->renameColumn('updated_name', 'update_user_id');
        });
    }
}
