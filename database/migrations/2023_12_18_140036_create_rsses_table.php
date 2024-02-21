<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateRssesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('rsses', function (Blueprint $table) {

            // KEY
            $table->bigIncrements('id');

            // 固有項目
            $table->integer('bucket_id')->comment('バケツID');
            $table->string('rsses_name')->comment('RSS名');
            $table->integer('cache_interval')->default(0)->comment('RSSキャッシュタイム(分)');
            $table->integer('mergesort_flag')->default(1)->comment('各RSSを混ぜて表示するフラグ');
            $table->integer('mergesort_count')->default(10)->comment('混ぜて表示する場合の表示数');

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
        Schema::dropIfExists('rsses');
    }
}
