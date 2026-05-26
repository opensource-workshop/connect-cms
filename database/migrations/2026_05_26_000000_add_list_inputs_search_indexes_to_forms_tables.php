<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddListInputsSearchIndexesToFormsTables extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('forms_inputs', function (Blueprint $table) {
            $table->index(['forms_id', 'created_at'], 'forms_inputs_forms_id_created_at_index');
        });

        Schema::table('forms_input_cols', function (Blueprint $table) {
            $table->index('forms_inputs_id', 'forms_input_cols_forms_inputs_id_index');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('forms_input_cols', function (Blueprint $table) {
            $table->dropIndex('forms_input_cols_forms_inputs_id_index');
        });

        Schema::table('forms_inputs', function (Blueprint $table) {
            $table->dropIndex('forms_inputs_forms_id_created_at_index');
        });
    }
}
