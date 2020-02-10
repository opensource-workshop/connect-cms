<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

/**
 * コードテーブルに追加value項目 additional1～5 追加
 *
 * @author 牟田口 満 <mutaguchi@opensource-workshop.jp>
 * @copyright OpenSource-WorkShop Co.,Ltd. All Rights Reserved
 * @category コード管理
 * @package Migration
 */
class AddAdditional15CodesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('codes', function (Blueprint $table) {
            $table->string('additional1', 255)->nullable()->after('value');
            $table->string('additional2', 255)->nullable()->after('additional1');
            $table->string('additional3', 255)->nullable()->after('additional2');
            $table->string('additional4', 255)->nullable()->after('additional3');
            $table->string('additional5', 255)->nullable()->after('additional4');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('codes', function (Blueprint $table) {
            $table->dropColumn('additional1');
            $table->dropColumn('additional2');
            $table->dropColumn('additional3');
            $table->dropColumn('additional4');
            $table->dropColumn('additional5');
        });
    }
}
