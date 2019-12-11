<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddMailOpacs extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('opacs', function (Blueprint $table) {
            //
            $table->integer('moderator_mail_send_flag')->default('0')->after('view_count');
            $table->text('moderator_mail_send_address')->nullable()->after('moderator_mail_send_flag');
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
            //
            $table->dropColumn('moderator_mail_send_flag');
            $table->dropColumn('moderator_mail_send_address');
        });
    }
}
