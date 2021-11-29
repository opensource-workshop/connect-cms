<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreatePhotoalbums extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('photoalbums', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->integer('bucket_id')->comment('バケツID');
            $table->string('name', 255)->comment('フォトアルバム名');
            $table->string('image_upload_max_size', 255)->comment('画像ファイル最大サイズ');
            $table->string('image_upload_max_px', 255)->comment('画像アップロード時の最大変換サイズ');
            $table->string('video_upload_max_size', 255)->comment('動画ファイル最大サイズ');
            $table->string('comment', 255)->nullable()->comment('コメント');
            $table->integer('approval_flag')->default(0)->comment('承認フラグ');
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
        Schema::dropIfExists('photoalbums');
    }
}
