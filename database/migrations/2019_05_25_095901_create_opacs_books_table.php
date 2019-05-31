<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateOpacsBooksTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('opacs_books', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('opacs_id');
            $table->string('isbn')->nullable();      /* ISBN等 */
            $table->string('title');                 /* タイトル */
            $table->string('ndc')->nullable();       /* 請求記号 */
            $table->string('creator')->nullable();   /* 著者 */
            $table->string('publisher')->nullable(); /* 出版者 */
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
        Schema::dropIfExists('opacs_books');
    }
}
