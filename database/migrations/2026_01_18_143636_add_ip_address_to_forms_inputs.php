<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddIpAddressToFormsInputs extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('forms_inputs', function (Blueprint $table) {
            $table->string('ip_address', 255)->nullable()->comment('投稿者のIPアドレス');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('forms_inputs', function (Blueprint $table) {
            $table->dropColumn('ip_address');
        });
    }
}
