<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddOpacsBooksLentsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('opacs_books_lents', function (Blueprint $table) {
            $table->string('postal_code', 255)->nullable()->comment('郵送先郵便番号')->after('email');
            $table->string('address', 255)->nullable()->comment('郵送先住所')->after('postal_code');
            $table->string('mailing_name', 255)->nullable()->comment('郵送先宛名')->after('address');
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
            $table->dropColumn('postal_code');
            $table->dropColumn('address');
            $table->dropColumn('mailing_name');
        });
    }
}
