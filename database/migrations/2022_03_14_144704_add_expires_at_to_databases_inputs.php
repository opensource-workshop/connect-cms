<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddExpiresAtToDatabasesInputs extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('databases_inputs', function (Blueprint $table) {
            $table->dateTime('expires_at')->nullable()->comment('公開終了日時')->after('posted_at');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('databases_inputs', function (Blueprint $table) {
            $table->dropColumn('expires_at');
        });
    }
}
