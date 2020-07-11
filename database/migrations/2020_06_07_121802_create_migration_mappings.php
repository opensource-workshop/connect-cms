<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateMigrationMappings extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('migration_mappings', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('source_id');
            $table->integer('destination_id');
            $table->text('note')->nullable();
            $table->integer('created_id')->nullable();
            $table->string('created_name', 255)->nullable();
            $table->timestamp('created_at')->nullable();
            $table->integer('updated_id')->nullable();
            $table->string('updated_name', 255)->nullable();
            $table->timestamp('updated_at')->nullable();
            $table->integer('deleted_id')->nullable();
            $table->string('deleted_name', 255)->nullable();
            $table->timestamp('deleted_at')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('migration_mappings');
    }
}
