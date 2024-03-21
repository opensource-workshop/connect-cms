<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateUsersColumnsSetsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     * @see database\migrations\2021_11_16_175426_create_reservations_columns_sets_table.php is copy
     */
    public function up()
    {
        Schema::create('users_columns_sets', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->integer('display_sequence')->default('0');
            $table->integer('created_id')->nullable();
            $table->string('created_name')->nullable();
            $table->timestamp('created_at')->nullable();
            $table->integer('updated_id')->nullable();
            $table->string('updated_name')->nullable();
            $table->timestamp('updated_at')->nullable();
            $table->integer('deleted_id')->nullable();
            $table->string('deleted_name')->nullable();
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
        Schema::dropIfExists('users_columns_sets');
    }
}
