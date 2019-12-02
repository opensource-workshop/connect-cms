<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class DropWhatsnewsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::dropIfExists('whatsnews');
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::create('whatsnews', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('bucket_id');
            $table->string('whatsnew_name');
            $table->integer('view_pattern')->default('0');
            $table->integer('count')->default('0');
            $table->integer('days')->default('0');
            $table->integer('rss')->default('0');
            $table->integer('view_created_name')->default('0');
            $table->integer('view_created_at')->default('0');
            $table->text('target_plugin');
            $table->timestamps();
        });
    }
}
