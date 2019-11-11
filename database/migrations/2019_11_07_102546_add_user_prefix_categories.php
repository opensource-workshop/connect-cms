<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddUserPrefixCategories extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('categories', function (Blueprint $table) {
            //
            $table->integer('created_id')->nullable()->after('display_sequence');
            $table->string('created_name', 255)->nullable()->after('created_id');
            $table->integer('updated_id')->nullable()->after('created_at');
            $table->string('updated_name', 255)->nullable()->after('updated_id');
            $table->integer('deleted_id')->nullable()->after('updated_at');
            $table->string('deleted_name', 255)->nullable()->after('deleted_id');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('categories', function (Blueprint $table) {
            //
            $table->dropColumn('created_id');
            $table->dropColumn('created_name');
            $table->dropColumn('updated_id');
            $table->dropColumn('updated_name');
            $table->dropColumn('deleted_id');
            $table->dropColumn('deleted_name');
        });
    }
}
