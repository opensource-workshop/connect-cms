<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateSlideshowsItemsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('slideshows_items', function (Blueprint $table) {

            // KEY
            $table->bigIncrements('id');

            // 固有項目
            $table->integer('slideshows_id')->comment('スライドショーID');
            $table->string('image_path')->comment('画像PATH');
            $table->integer('image_interval')->default(5000)->comment('画像の静止時間（ms）');
            $table->string('link_url')->nullable()->comment('リンクURL');
            $table->string('link_target')->nullable()->comment('リンクターゲット');
            $table->string('caption')->nullable()->comment('キャプション');
            $table->integer('display_flag')->default(0)->comment('表示フラグ');
            $table->integer('display_sequence')->comment('表示順');

            // 共通項目
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
        Schema::dropIfExists('slideshows_items');
    }
}
