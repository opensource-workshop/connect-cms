<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddTargetSourceTableAndSourceKeyIndexFromMigrationMappings extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('migration_mappings', function (Blueprint $table) {
            $table->index(['target_source_table', 'source_key'], 'target_source_table_and_source_key_index');
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
            $table->dropIndex('target_source_table_and_source_key_index');
        });
    }
}
