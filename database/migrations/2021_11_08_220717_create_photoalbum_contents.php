<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Kalnoy\Nestedset\NestedSet;

class CreatePhotoalbumContents extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('photoalbum_contents', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->integer('photoalbum_id')->comment('フォトアルバムID');
            $table->integer('upload_id')->nullable()->comment('アップロードID');
            $table->string('name', 255)->comment('名前');
            $table->integer('width')->nullable()->comment('画像の幅(px)');
            $table->integer('height')->nullable()->comment('画像の高さ(px)');
            $table->text('description')->nullable()->comment('説明');
            $table->tinyInteger('is_folder')->comment('フォルダである');
            $table->tinyInteger('is_cover')->default('0')->comment('アルバム表紙である');
            $table->nestedSet();
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
        Schema::dropIfExists('photoalbum_contents');
    }
}
