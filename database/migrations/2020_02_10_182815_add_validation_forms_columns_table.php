<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddValidationFormsColumnsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('forms_columns', function (Blueprint $table) {
            $table->string('rule_allowed_numeric')->nullable()->comment('数値のみ許容')->after('minutes_increments');
            $table->string('rule_allowed_alpha_numeric')->nullable()->comment('英数値のみ許容')->after('rule_allowed_numeric');
            $table->string('rule_digits_or_less')->nullable()->comment('指定桁数以下を許容')->after('rule_allowed_alpha_numeric');
            $table->string('rule_max')->nullable()->comment('最大値設定')->after('rule_digits_or_less');
            $table->string('rule_min')->nullable()->comment('最小値設定')->after('rule_max');
            $table->string('rule_word_count')->nullable()->comment('最大文字数（半角換算）')->after('rule_min');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('forms_columns', function (Blueprint $table) {
            $table->dropColumn('rule_allowed_numeric');
            $table->dropColumn('rule_allowed_alpha_numeric');
            $table->dropColumn('rule_digits_or_less');
            $table->dropColumn('rule_max');
            $table->dropColumn('rule_min');
            $table->dropColumn('rule_word_count');
        });
    }
}
