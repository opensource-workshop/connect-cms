<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

/**
 * 新着情報に係るインデックスをブログとフレーム関係のテーブルに追加
 */
class AddWhatsnewsIndexToBlogsAndFrames extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('blogs_posts', function (Blueprint $table) {
            $table->index(['blogs_id'], 'blogs_id_index');
        });

        Schema::table('frames', function (Blueprint $table) {
            $table->index(['bucket_id'], 'bucket_id_index');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('blogs_posts', function (Blueprint $table) {
            $table->dropIndex('blogs_id_index');
        });

        Schema::table('frames', function (Blueprint $table) {
            $table->dropIndex('bucket_id_index');
        });
    }
}
