<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddLentSettingOpacs extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('opacs', function (Blueprint $table) {
            //
            $table->integer('lent_setting')->default('0')->after('moderator_mail_send_address');
            $table->integer('lent_limit')->default('0')->after('lent_setting');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('opacs', function (Blueprint $table) {
            //
            $table->dropColumn('lent_setting');
            $table->dropColumn('lent_limit');
        });
    }
}
