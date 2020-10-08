<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddLabelHideFlagToDatabasesColumns extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('databases_columns', function (Blueprint $table) {
            $table->integer('label_hide_flag')->default(0)->comment('項目名を非表示にする指定')->after('detail_hide_flag');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('databases_columns', function (Blueprint $table) {
            $table->dropColumn('label_hide_flag');
        });
    }
}
