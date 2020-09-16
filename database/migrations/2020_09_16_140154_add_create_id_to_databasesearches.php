<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddCreateIdToDatabasesearches extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('databasesearches', function (Blueprint $table) {
            $table->integer('created_id')->nullable()->after('target_frame_ids');
            $table->string('created_name', 255)->nullable()->after('created_id');
            $table->integer('updated_id')->nullable()->after('created_at');
            $table->string('updated_name', 255)->nullable()->after('updated_id');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('databasesearches', function (Blueprint $table) {
            $table->dropColumn('created_id');
            $table->dropColumn('created_name');
            $table->dropColumn('updated_id');
            $table->dropColumn('updated_name');
        });
    }
}
