<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddColumnToOpacsBooks extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('opacs_books', function (Blueprint $table) {
            //
            $table->string('barcode', 255)->nullable()->after('id');
            $table->string('title_read', 255)->nullable()->after('title');
            $table->string('subtitle', 255)->nullable()->after('title_read');
            $table->string('series', 255)->nullable()->after('subtitle');
            $table->string('publication_year', 255)->nullable()->after('publisher');
            $table->string('class', 255)->nullable()->after('publication_year');
            $table->string('size', 255)->nullable()->after('class');
            $table->string('page_number', 255)->nullable()->after('size');
            $table->string('marc', 255)->nullable()->after('isbn');
            $table->string('type', 255)->nullable()->after('page_number');
            $table->string('shelf', 255)->nullable()->after('type');
            $table->string('lend_flag', 255)->nullable()->after('shelf');
            $table->string('accept_flag', 255)->nullable()->after('lend_flag');
            $table->date('accept_date')->nullable()->after('accept_flag');
            $table->integer('accept_price')->nullable()->after('accept_date');
            $table->date('storage_life')->nullable()->after('accept_price');
            $table->string('remove_flag', 255)->nullable()->after('storage_life');
            $table->date('remove_date')->nullable()->after('remove_flag');
            $table->string('possession', 255)->nullable()->after('remove_date');
            $table->string('library', 255)->nullable()->after('possession');
            $table->date('last_lending_date')->nullable()->after('library');
            $table->integer('total_lends')->nullable()->after('last_lending_date');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('opacs_books', function (Blueprint $table) {
            //
            $table->dropColumn('barcode');
            $table->dropColumn('title_read');
            $table->dropColumn('subtitle');
            $table->dropColumn('series');
            $table->dropColumn('publication_year');
            $table->dropColumn('class');
            $table->dropColumn('size');
            $table->dropColumn('page');
            $table->dropColumn('marc');
            $table->dropColumn('type');
            $table->dropColumn('shelf');
            $table->dropColumn('lend_flag');
            $table->dropColumn('accept_flag');
            $table->dropColumn('accept_date');
            $table->dropColumn('accept_price');
            $table->dropColumn('storage_life');
            $table->dropColumn('remove_flag');
            $table->dropColumn('remove_date');
            $table->dropColumn('possession');
            $table->dropColumn('library');
            $table->dropColumn('last_lending_date');
            $table->dropColumn('total_lends');
        });
    }
}
