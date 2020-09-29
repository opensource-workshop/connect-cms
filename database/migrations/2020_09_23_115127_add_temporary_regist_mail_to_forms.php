<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddTemporaryRegistMailToForms extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('forms', function (Blueprint $table) {
            $table->integer('use_temporary_regist_mail_flag')->default(0)->comment('仮登録メールを使うフラグ')->after('user_mail_send_flag');
            $table->string('temporary_regist_mail_subject', 255)->nullable()->comment('仮登録メール件名')->after('use_temporary_regist_mail_flag');
            $table->text('temporary_regist_mail_format')->nullable()->comment('仮登録メールフォーマット')->after('temporary_regist_mail_subject');
            $table->text('temporary_regist_after_message')->nullable()->comment('仮登録後のメッセージ')->after('temporary_regist_mail_format');
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
            $table->dropColumn('use_temporary_regist_mail_flag');
            $table->dropColumn('temporary_regist_mail_subject');
            $table->dropColumn('temporary_regist_mail_format');
            $table->dropColumn('temporary_regist_after_message');
        });
    }
}
