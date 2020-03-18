<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddImportantBlogsFrames extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('blogs_frames', function (Blueprint $table) {
            //
            $table->string('important_view')->nullable()->after('scope_value');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('blogs_frames', function (Blueprint $table) {
            //
            $table->dropColumn('important_view');
        });
    }
}
