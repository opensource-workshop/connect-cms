<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddAdditional610HelpMessageToCodesHelpMessages extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('codes_help_messages', function (Blueprint $table) {
            $table->string('additional6_help_message', 255)->nullable()->after('additional5_help_message');
            $table->string('additional7_help_message', 255)->nullable()->after('additional6_help_message');
            $table->string('additional8_help_message', 255)->nullable()->after('additional7_help_message');
            $table->string('additional9_help_message', 255)->nullable()->after('additional8_help_message');
            $table->string('additional10_help_message', 255)->nullable()->after('additional9_help_message');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('codes_help_messages', function (Blueprint $table) {
            $table->dropColumn('additional6_help_message');
            $table->dropColumn('additional7_help_message');
            $table->dropColumn('additional8_help_message');
            $table->dropColumn('additional9_help_message');
            $table->dropColumn('additional10_help_message');
        });
    }
}
