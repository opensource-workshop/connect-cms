<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddAddTokenAddTokenCreatedAtToFormsInputs extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('forms_inputs', function (Blueprint $table) {
            $table->integer('status')->default(0)->after('forms_id');
            $table->string('add_token', 255)->nullable()->comment('本登録トークン')->after('status');
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
        Schema::table('forms_inputs', function (Blueprint $table) {
            $table->dropColumn('status');
            $table->dropColumn('add_token');
            $table->dropColumn('add_token_created_at');
        });
    }
}
