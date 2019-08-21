<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddStatusEtcToContents extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('contents', function (Blueprint $table) {
            //
            $table->text('content2_text')->nullable()->after('content_text');
            $table->string('view_more')->unique()->nullable()->after('content2_text');
            $table->string('hide_more')->unique()->nullable()->after('view_more');
            $table->integer('status')->default('0')->after('hide_more');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('contents', function (Blueprint $table) {
            //
            $table->dropColumn('content2_text');
            $table->dropColumn('view_more');
            $table->dropColumn('hide_more');
            $table->dropColumn('status');
        });
    }
}
