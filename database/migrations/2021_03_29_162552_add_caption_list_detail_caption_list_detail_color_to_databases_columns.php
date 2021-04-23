<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddCaptionListDetailCaptionListDetailColorToDatabasesColumns extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('databases_columns', function (Blueprint $table) {
            $table->string('caption_list_detail', 255)->nullable()->comment('一覧・詳細のキャプション')->after('caption_color');
            $table->string('caption_list_detail_color', 255)->default('text-dark')->comment('一覧・詳細のキャプション文字色')->after('caption_list_detail');
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
            $table->dropColumn('caption_list_detail');
            $table->dropColumn('caption_list_detail_color');
        });
    }
}
