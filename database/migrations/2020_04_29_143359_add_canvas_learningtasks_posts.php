<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddCanvasLearningtasksPosts extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('learningtasks_posts', function (Blueprint $table) {
            //
            $table->integer('use_canvas')->default(0)->after('important');
            $table->integer('required_canvas_answer')->default(0)->after('use_canvas');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('learningtasks_posts', function (Blueprint $table) {
            //
            $table->dropColumn('use_canvas');
            $table->dropColumn('required_canvas_answer');
        });
    }
}
