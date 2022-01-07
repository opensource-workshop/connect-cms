<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddSortTypeToDatabasesearchers extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('databasesearches', function (Blueprint $table) {
            $table->string('sort_type')->nullable()->comment('並び替え設定')->after('condition');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('databasesearches', function (Blueprint $table) {
            $table->dropColumn('sort_type');
        });
    }
}
