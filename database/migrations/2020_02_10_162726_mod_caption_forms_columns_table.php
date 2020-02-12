<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class ModCaptionFormsColumnsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('forms_columns', function (Blueprint $table) {
            DB::statement('ALTER TABLE `forms_columns` MODIFY COLUMN caption text');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('forms_columns', function (Blueprint $table) {
            DB::statement('ALTER TABLE `forms_columns` MODIFY COLUMN caption varchar(255)');
        });
    }
}
