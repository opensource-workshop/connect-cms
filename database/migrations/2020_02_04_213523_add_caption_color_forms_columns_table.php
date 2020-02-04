<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddCaptionColorFormsColumnsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('forms_columns', function (Blueprint $table) {
            $table->string('caption_color', 255)->default('text-dark')->comment('キャプション文字色')->after('caption');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('forms_columns', function (Blueprint $table) {
            $table->dropColumn('caption_color');
        });
    }
}
