<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class DropColumnsCabinets extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('cabinets', function (Blueprint $table) {
            $table->dropColumn('comment');
            $table->dropColumn('approval_flag');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('cabinets', function (Blueprint $table) {
            $table->integer('approval_flag')->default(0)->comment('承認フラグ')->after('upload_max_size');
            $table->string('comment', 255)->nullable()->comment('コメント')->after('approval_flag');
        });
    }
}
