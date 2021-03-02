<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddAdditional610ToCodes extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('codes', function (Blueprint $table) {
            $table->string('additional6', 255)->nullable()->after('additional5');
            $table->string('additional7', 255)->nullable()->after('additional6');
            $table->string('additional8', 255)->nullable()->after('additional7');
            $table->string('additional9', 255)->nullable()->after('additional8');
            $table->string('additional10', 255)->nullable()->after('additional9');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('codes', function (Blueprint $table) {
            $table->dropColumn('additional6');
            $table->dropColumn('additional7');
            $table->dropColumn('additional8');
            $table->dropColumn('additional9');
            $table->dropColumn('additional10');
        });
    }
}
