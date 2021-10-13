<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class ChangeUrlLength extends Migration
{
    /**
     * Run the migrations.
     *     - URL 項目の文字数を255 => 8190 へ変更
     *     - Apache のLimitRequestLine ディレクティブのデフォルト値に合わせた。
     *     - http://httpd.apache.org/docs/2.4/mod/core.html#limitrequestline
     *
     * @return void
     */
    public function up()
    {
        /**
         * データベースへのマイグレーション
         */
        // ページの外部サイトURLと固定リンク
        Schema::table('pages', function (Blueprint $table) {
            $table->text('othersite_url')->nullable()->comment('URL')->change();
            $table->text('permanent_link')->nullable()->comment('固定リンク')->change();
        });
        // リンクリストのURL
        Schema::table('linklist_posts', function (Blueprint $table) {
            $table->text('url')->nullable()->comment('URL')->change();
        });
        // スライドショーのlink_url
        Schema::table('slideshows_items', function (Blueprint $table) {
            $table->text('link_url')->nullable()->comment('リンクURL')->change();
        });
    }

    /**
     * Reverse the migrations.
     *     - 1. データベース文字セット変更（utf8mb4 => utf8）
     *     - 2. テーブル文字セット変更（utf8mb4 => utf8）
     *     - 3. INDEX対象の一部カラムのデータ長さ変更（191 => 255）
     *
     * @return void
     */
    public function down()
    {
        /**
         * データベースへのマイグレーション
         */
        // ページの外部サイトURL
        Schema::table('pages', function (Blueprint $table) {
            $table->string('othersite_url', 255)->nullable()->comment('URL')->change();
            $table->string('permanent_link', 255)->nullable()->comment('固定リンク')->change();
        });
        // リンクリストのURL
        Schema::table('linklist_posts', function (Blueprint $table) {
            $table->string('url', 255)->nullable()->comment('URL')->change();
        });
        // スライドショーのlink_url
        Schema::table('slideshows_items', function (Blueprint $table) {
            $table->string('link_url', 255)->nullable()->comment('リンクURL')->change();
        });
    }
}
