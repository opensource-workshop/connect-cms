<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateOpacsBooksLentsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('opacs_books_lents', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('opacs_books_id');
            $table->integer('lent_flag');
            $table->string('student_no')->nullable();
            $table->date('return_scheduled')->nullable();
            $table->string('phone_no')->nullable();
            $table->string('email')->nullable();
            $table->dateTime('lent_at')->nullable();
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
        Schema::dropIfExists('opacs_books_lents');
    }
}
