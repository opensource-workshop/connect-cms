<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddAccessLimitTypeFormPasswordFromForms extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('forms', function (Blueprint $table) {
            $table->integer('access_limit_type')->default(0)->comment('閲覧制限タイプ')->after('form_mode');
            $table->string('form_password')->nullable()->comment('閲覧パスワード')->after('access_limit_type');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('forms', function (Blueprint $table) {
            $table->dropColumn('access_limit_type');
            $table->dropColumn('form_password');
        });
    }
}
