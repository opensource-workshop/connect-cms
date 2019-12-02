<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateWhatsnewsTable2 extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('whatsnews', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('bucket_id');
            $table->string('whatsnew_name');
            $table->integer('view_pattern')->default('0');
            $table->integer('count')->default('0');
            $table->integer('days')->default('0');
            $table->integer('rss')->default('0');
            $table->integer('rss_count')->default('0');
            $table->integer('view_posted_name')->default('0');
            $table->integer('view_posted_at')->default('0');
            $table->string('important')->nullable();
            $table->text('target_plugins');
            $table->integer('frame_select')->default('0');
            $table->text('target_frame_ids')->nullable();
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
        Schema::dropIfExists('whatsnews');
    }
}
