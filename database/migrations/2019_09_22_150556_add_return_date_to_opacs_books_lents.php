<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddReturnDateToOpacsBooksLents extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('opacs_books_lents', function (Blueprint $table) {
            //
            $table->date('return_date')->nullable()->after('return_scheduled');
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
            //
            $table->dropColumn('return_date');
        });
    }
}
