<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateDatabasesSearchedWords extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('databases_searched_words', function (Blueprint $table) {
            $table->id();
            $table->unsignedInteger('databases_id');
            $table->string('word');

            $table->integer('created_id')->nullable();
            $table->string('created_name')->nullable();
            $table->timestamp('created_at')->nullable();
            $table->integer('updated_id')->nullable();
            $table->string('updated_name')->nullable();
            $table->timestamp('updated_at')->nullable();

            // 外部キー
            $table->foreign('databases_id')->references('id')->on('databases')->cascadeOnDelete();
            // インデックス
            $table->index(['databases_id', 'word', 'created_at']);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('databases_searched_words');
    }
}
