<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddMailToForms extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('forms', function (Blueprint $table) {
            //
            $table->integer('mail_send_flag')->default('0')->after('forms_name');
            $table->text('mail_send_address')->nullable()->after('mail_send_flag');
            $table->integer('user_mail_send_flag')->default('0')->after('mail_send_address');
            $table->string('from_mail_name', 255)->nullable()->after('user_mail_send_flag');
            $table->string('mail_subject', 255)->nullable()->after('from_mail_name');
            $table->text('mail_format')->nullable()->after('mail_subject');
            $table->integer('data_save_flag')->default('0')->after('mail_format');
            $table->text('after_message')->nullable()->after('data_save_flag');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('forms', function (Blueprint $table) {
            //
            $table->dropColumn('mail_send_flag');
            $table->dropColumn('mail_send_address');
            $table->dropColumn('user_mail_send_flag');
            $table->dropColumn('from_mail_name');
            $table->dropColumn('mail_subject');
            $table->dropColumn('mail_format');
            $table->dropColumn('data_save_flag');
            $table->dropColumn('after_message');
        });
    }
}
