<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddAddTokenApprovalTokenAddTokenCreatedAtToUsers extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('users', function (Blueprint $table) {
            $table->string('add_token', 255)->nullable()->comment('本登録トークン')->after('remember_token');
            $table->timestamp('add_token_created_at')->nullable()->comment('本登録トークン登録日時')->after('add_token');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn('add_token');
            $table->dropColumn('add_token_created_at');
        });
    }
}
