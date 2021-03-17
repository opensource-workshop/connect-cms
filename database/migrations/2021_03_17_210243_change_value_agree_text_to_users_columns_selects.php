<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class ChangeValueAgreeTextToUsersColumnsSelects extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('users_columns_selects', function (Blueprint $table) {
            $table->string('value', 255)->change();
            $table->text('agree_description')->nullable()->comment('同意の説明文')->after('value');
        });
    }

    /**ｃ
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('users_columns_selects', function (Blueprint $table) {
            $table->text('value')->change();
            $table->dropColumn('agree_description');
        });
    }
}
