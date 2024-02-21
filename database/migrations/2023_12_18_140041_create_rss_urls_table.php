<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateRssUrlsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('rss_urls', function (Blueprint $table) {

            // KEY
            $table->bigIncrements('id');

            // 固有項目
            $table->integer('rsses_id')->comment('RSSID');
            $table->string('url', 255)->comment('URL');
            $table->string('title')->nullable()->comment('タイトル');
            $table->string('caption')->nullable()->comment('キャプション');
            $table->integer('item_count')->default(10)->comment('RSS取得数');
            $table->integer('display_flag')->default(0)->comment('表示フラグ');
            $table->integer('display_sequence')->comment('表示順');
            $table->longText('xml')->nullable()->comment('キャッシュ用');
            $table->timestamp('xml_updated_at')->nullable()->comment('XML取得時間');

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
        Schema::dropIfExists('rss_urls');
    }
}
