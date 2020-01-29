<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddIconMenus extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('menus', function (Blueprint $table) {
            //
            $table->integer('folder_close_font')->default(0)->after('page_ids');
            $table->integer('folder_open_font')->default(0)->after('folder_close_font');
            $table->integer('indent_font')->default(0)->after('folder_open_font');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('menus', function (Blueprint $table) {
            //
            $table->dropColumn('folder_close_font');
            $table->dropColumn('folder_open_font');
            $table->dropColumn('indent_font');
        });
    }
}
