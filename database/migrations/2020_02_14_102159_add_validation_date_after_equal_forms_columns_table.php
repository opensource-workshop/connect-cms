<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddValidationDateAfterEqualFormsColumnsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('forms_columns', function (Blueprint $table) {
            $table->string('rule_date_after_equal')->nullable()->comment('～日以降を許容')->after('rule_word_count');
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
            $table->dropColumn('rule_date_after_equal');
        });
    }
}
