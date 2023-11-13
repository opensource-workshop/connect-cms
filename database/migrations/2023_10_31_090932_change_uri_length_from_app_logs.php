<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class ChangeUriLengthFromAppLogs extends Migration
{
    /**
     * Run the migrations.
     * - URL 項目の文字数を255 => 8190 へ変更
     * - Apache のLimitRequestLine ディレクティブのデフォルト値に合わせた。
     * - https://httpd.apache.org/docs/2.4/mod/core.html#limitrequestline
     * @see 2021_10_05_111600_change_url_length.php
     *
     * @return void
     */
    public function up()
    {
        Schema::table('app_logs', function (Blueprint $table) {
            $table->text('uri')->nullable()->comment('URI')->change();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('app_logs', function (Blueprint $table) {
            $table->string('uri')->nullable()->comment('URI')->change();
        });
    }
}
