<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddIpApiSecrets extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('api_secrets', function (Blueprint $table) {
            //
            $table->string('ip_address', 255)->nullable()->after('apis')->comment('制限IPアドレス');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('api_secrets', function (Blueprint $table) {
            //
            $table->dropColumn('ip_address');
        });
    }
}
