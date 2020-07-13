<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class ChangeSourceMigrationMappings extends Migration
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
            $table->dropColumn('source_id');
            $table->dropColumn('destination_id');
            $table->string('source_key')->nullable()->after('target_source_table');
            $table->string('destination_key')->nullable()->after('source_key');
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
            $table->integer('source_id')->nullable()->after('target_source_table');
            $table->integer('destination_id')->nullable()->after('source_id');
            $table->dropColumn('source_key');
            $table->dropColumn('destination_key');
        });
    }
}
