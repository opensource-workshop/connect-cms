<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddDeletedToBlogsPosts extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('blogs_posts', function (Blueprint $table) {
            //
            $table->integer('deleted_id')->nullable()->after('updated_at');
            $table->string('deleted_name', 255)->nullable()->after('deleted_id');
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('blogs_posts', function (Blueprint $table) {
            //
            $table->dropColumn('deleted_id');
            $table->dropColumn('deleted_name');
            $table->dropSoftDeletes();
        });
    }
}
