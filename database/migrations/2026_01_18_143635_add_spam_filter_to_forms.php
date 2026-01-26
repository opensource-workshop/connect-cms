<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddSpamFilterToForms extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('forms', function (Blueprint $table) {
            $table->integer('use_spam_filter_flag')->default(0)->comment('スパムフィルタリング使用フラグ');
            $table->text('spam_filter_message')->nullable()->comment('スパムブロック時のメッセージ');
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
            $table->dropColumn('use_spam_filter_flag');
            $table->dropColumn('spam_filter_message');
        });
    }
}
