<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddLastColUpdatedAtToDatabasesInputsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('databases_inputs', function (Blueprint $table) {
            $table->timestamp('last_col_updated_at')->nullable()->after('full_text');
        });

        // 既存データの更新
        \DB::statement('
            UPDATE databases_inputs di
            SET di.last_col_updated_at = (
                SELECT updated_at
                FROM databases_input_cols dic
                WHERE dic.databases_inputs_id = di.id
                ORDER BY updated_at DESC
                LIMIT 1
            )
        ');
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('databases_inputs', function (Blueprint $table) {
            $table->dropColumn('last_col_updated_at');
        });
    }
}
