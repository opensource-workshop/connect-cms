<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class UpdateCreatedIdToCodestudies extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('codestudies', function (Blueprint $table) {
            //
            DB::statement('UPDATE `codestudies` set `created_id` = `user_id`');
            $table->dropColumn('user_id');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('codestudies', function (Blueprint $table) {
            //
            $table->integer('user_id')->nullable()->after('id');
        });
    }
}
