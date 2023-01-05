<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddSpecToDusksTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('dusks', function (Blueprint $table) {
            //
            $table->text('spec_class')->nullable()->comment('仕様(概要)')->after('method_detail');
            $table->text('spec_method')->nullable()->comment('仕様(詳細)')->after('spec_class');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('dusks', function (Blueprint $table) {
            //
            $table->dropColumn('spec_class');
            $table->dropColumn('spec_method');
        });
    }
}
