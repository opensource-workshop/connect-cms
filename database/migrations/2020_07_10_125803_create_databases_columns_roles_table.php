<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateDatabasesColumnsRolesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('databases_columns_roles', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('databases_id');
            $table->integer('databases_columns_id');
            $table->string('role_name', 255);
            $table->integer('list_detail_display_flag')->comment('一覧・詳細で表示する指定')->nullable();
            $table->integer('regist_edit_display_flag')->comment('登録・編集で表示する指定')->nullable();

            $table->integer('created_id')->nullable();
            $table->string('created_name', 255)->nullable();
            $table->timestamp('created_at')->nullable();
            $table->integer('updated_id')->nullable();
            $table->string('updated_name', 255)->nullable();
            $table->timestamp('updated_at')->nullable();

            // index
            $table->index(['databases_columns_id'], 'databases_columns_id_index');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('databases_columns_roles');
    }
}
