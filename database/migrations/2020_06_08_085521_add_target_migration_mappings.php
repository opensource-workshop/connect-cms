<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddTargetMigrationMappings extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('migration_mappings', function (Blueprint $table) {
            //
            $table->string('target_source_table', 255)->nullable()->after('id');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('migration_mappings', function (Blueprint $table) {
            //
            $table->dropColumn('target_source_table');
        });
    }
}
