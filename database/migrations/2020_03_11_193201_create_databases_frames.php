<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateDatabasesFrames extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('databases_frames', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('databases_id');
            $table->integer('frames_id');
            $table->integer('use_search_flag')->comment('検索機能使用の有無')->default(1);
            $table->integer('use_select_flag')->comment('絞り込み機能使用の有無')->default(1);
            $table->integer('use_sort_flag')->comment('並べ替え使用の有無')->default(1);
            $table->integer('view_count')->comment('1ページの表示件数')->default(1);
            $table->integer('default_hide')->comment('初期表示で一覧表示しない')->default(0);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('databases_frames');
    }
}
