<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class RenameFormsIdToFormsInputCols extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('forms_input_cols', function (Blueprint $table) {
            DB::statement('ALTER TABLE `forms_input_cols` CHANGE COLUMN `forms_id` `forms_inputs_id` int(11)');
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
            DB::statement('ALTER TABLE `forms_input_cols` CHANGE COLUMN `forms_inputs_id` `forms_id` int(11)');
        });
    }
}
