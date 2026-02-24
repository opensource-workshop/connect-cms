<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddFileUploadRulesToFormsColumnsTable extends Migration
{
    /**
     * Run the migrations.
     */
    public function up()
    {
        Schema::table('forms_columns', function (Blueprint $table) {
            $table->text('rule_file_extensions')
                ->nullable()
                ->comment('ファイル型: 許可拡張子(CSV)')
                ->after('rule_date_after_equal');
            $table->unsignedInteger('rule_file_max_kb')
                ->nullable()
                ->comment('ファイル型: 最大アップロードサイズ(KB)')
                ->after('rule_file_extensions');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down()
    {
        Schema::table('forms_columns', function (Blueprint $table) {
            $table->dropColumn('rule_file_extensions');
            $table->dropColumn('rule_file_max_kb');
        });
    }
}

