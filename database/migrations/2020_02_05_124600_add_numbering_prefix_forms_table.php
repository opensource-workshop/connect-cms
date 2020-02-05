<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddNumberingPrefixFormsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('forms', function (Blueprint $table) {
            $table->integer('numbering_use_flag')->default('0')->comment('採番使用フラグ')->after('after_message');
            $table->string('numbering_prefix', 255)->nullable()->comment('採番プレフィックス')->after('numbering_use_flag');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('forms', function (Blueprint $table) {
            $table->dropColumn('numbering_use_flag');
            $table->dropColumn('numbering_prefix');
        });
    }
}
