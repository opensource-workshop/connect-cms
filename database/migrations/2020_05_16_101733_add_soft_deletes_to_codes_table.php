<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

/**
 * コード管理 論理削除項目 追加
 *
 * @author 牟田口 満 <mutaguchi@opensource-workshop.jp>
 * @copyright OpenSource-WorkShop Co.,Ltd. All Rights Reserved
 * @category コード管理
 * @package Migration
 */
class AddSoftDeletesToCodesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('codes', function (Blueprint $table) {
            $table->integer('deleted_id')->nullable()->after('updated_at');
            $table->string('deleted_name', 255)->nullable()->after('deleted_id');
            $table->timestamp('deleted_at')->nullable()->after('deleted_name');
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
            $table->dropColumn('deleted_id');
            $table->dropColumn('deleted_name');
            $table->dropColumn('deleted_at');
        });
    }
}
