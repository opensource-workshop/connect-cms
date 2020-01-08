<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateFaqsPosts extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('faqs_posts', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('contents_id')->nullable();
            $table->integer('faqs_id');
            $table->text('post_title');
            $table->text('post_text')->nullable();
            $table->text('post_text2')->nullable();
            $table->integer('categories_id')->nullable();
            $table->integer('important')->nullable();
            $table->integer('display_sequence')->default('0');
            $table->integer('status')->default('0');
            $table->dateTime('posted_at');
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
        Schema::dropIfExists('faqs_posts');
    }
}
