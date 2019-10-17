<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddFormsIdToFormsColumns extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('forms_columns', function (Blueprint $table) {
            //
            DB::statement('ALTER TABLE `forms_columns` ADD COLUMN `forms_id` int(11) DEFAULT NULL AFTER `id`');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        //
            $table->dropColumn('forms_id');
    }
}
