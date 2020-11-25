<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class UpdateCheckboxSeparatorToFormsInputCols extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        // bugfix: カンマを含むチェックボックス選択肢をインポートできないため、チェックボックスの区切り文字を,→|に見直しするパッチ
        \DB::statement('UPDATE forms_input_cols cols, forms_columns colums ' .
                ' SET cols.value = REPLACE(cols.value, ",", "|") ' .
                ' WHERE cols.value like "%,%" AND cols.forms_columns_id = colums.id  AND colums.column_type = "checkbox"');
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        //
    }
}
