<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddSettingColumnBlogs extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('blogs', function (Blueprint $table) {
            //
            $table->integer('rss')->default(0)->after('view_count');
            $table->integer('rss_count')->default(0)->after('rss');
            $table->string('scope', 255)->nullable()->after('rss_count');
            $table->string('scope_value', 255)->nullable()->after('scope');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('blogs', function (Blueprint $table) {
            //
            $table->dropColumn('rss');
            $table->dropColumn('rss_count');
            $table->dropColumn('scope');
            $table->dropColumn('scope_value');
        });
    }
}
