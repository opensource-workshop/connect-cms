<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class ChangeDeliveryRequestOpacsBooksLents extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('opacs_books_lents', function (Blueprint $table) {
            $table->integer('delivery_request_flag')->default(0)->change();
        });    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('opacs_books_lents', function (Blueprint $table) {
            $table->integer('delivery_request_flag')->default(NULL)->change();
        });    }
}
