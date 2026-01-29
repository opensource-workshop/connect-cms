<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class MakeBlockValueNullableInSpamLists extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('spam_lists', function (Blueprint $table) {
            // ハニーポット対応: block_value をnullable に変更
            // ハニーポットは値を必要としないため
            $table->string('block_value', 255)->nullable()->comment('ブロック対象の値（ハニーポットの場合はnull）')->change();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('spam_lists', function (Blueprint $table) {
            $table->string('block_value', 255)->nullable(false)->comment('ブロック対象の値')->change();
        });
    }
}
