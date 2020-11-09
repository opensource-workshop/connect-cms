<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class UpdatePluginNameTypoDatabasessToUploads extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        // bugfix: データベースで uploadsのplugin_name が一部typo(databasess)があり databases に修正するパッチ
        \DB::statement('UPDATE uploads SET plugin_name = "databases" WHERE plugin_name = "databasess"');
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        //
    }
}
