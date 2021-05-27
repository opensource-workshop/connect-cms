<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateSlideshowsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('slideshows', function (Blueprint $table) {

            // KEY
            $table->bigIncrements('id');

            // 固有項目
            $table->integer('bucket_id')->comment('バケツID');
            $table->string('slideshows_name')->comment('スライドショー名');
            $table->integer('control_display_flag')->default(0)->comment('コントロール表示フラグ');
            $table->integer('indicators_display_flag')->default(0)->comment('インジケータ表示フラグ');
            $table->integer('fade_use_flag')->default(0)->comment('フェード使用フラグ');

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
        Schema::dropIfExists('slideshows');
    }
}
