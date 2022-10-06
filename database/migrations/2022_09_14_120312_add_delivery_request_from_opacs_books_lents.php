<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddDeliveryRequestFromOpacsBooksLents extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('opacs_books_lents', function (Blueprint $table) {
            $table->integer('delivery_request_flag')->comment('配送希望')->after('mailing_name');
            $table->date('delivery_request_date')->nullable()->comment('配送希望日')->after('delivery_request_flag');
            $table->string('delivery_request_time')->nullable()->comment('配送希望時間')->after('delivery_request_date');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('opacs_books_lents', function (Blueprint $table) {
            $table->dropColumn('delivery_request_flag');
            $table->dropColumn('delivery_request_date');
            $table->dropColumn('delivery_request_time');
        });
    }
}
