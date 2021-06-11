<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddOpacsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('opacs', function (Blueprint $table) {
            $table->integer('request_mail_send_flag')->default(0)->comment('リクエスト元にもメールを送るかフラグ')->after('moderator_mail_send_address');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('opacs', function (Blueprint $table) {
            $table->dropColumn('request_mail_send_flag');
        });
    }
}
