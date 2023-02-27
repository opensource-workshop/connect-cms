<?php

use App\Enums\FormMode;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddFormModeAndCanViewInputsModeratorFromForms extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('forms', function (Blueprint $table) {
            $table->string('form_mode')->default(FormMode::form)->comment('フォームモード')->after('forms_name');
            $table->integer('can_view_inputs_moderator')->nullable()->comment('モデレータは集計結果を表示できる')->after('regist_to');
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
            $table->dropColumn('form_mode');
            $table->dropColumn('can_view_inputs_moderator');
        });
    }
}
