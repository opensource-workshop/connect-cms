<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

use Illuminate\Support\Facades\DB;

use App\Enums\PluginName;

class MoveLearningtasksCategoriesToPluginCategories extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        // 共通plugin_categoriesにFAQカテゴリが無ければ移し替え
        if (DB::table('plugin_categories')->where('target', PluginName::getPluginName(PluginName::learningtasks))->count() == 0) {

            $learningtasks_categories = DB::table('learningtasks_categories')->whereNull('learningtasks_categories.deleted_at')->get();
            $learningtasks_categories = $learningtasks_categories->toArray();
            // stdClassをArrayにキャスト. （モデルだとtoArray()で配列になるが、DBファサードの場合 stdClassのままのため、キャストする）
            $learningtasks_categories = json_decode(json_encode($learningtasks_categories), true);

            foreach ($learningtasks_categories as &$learningtasks_category) {
                unset($learningtasks_category['id']);

                $learningtasks_category['target'] = PluginName::getPluginName(PluginName::learningtasks);
                $learningtasks_category['target_id'] = $learningtasks_category['learningtasks_id'];
                unset($learningtasks_category['learningtasks_id']);
            }

            DB::table('plugin_categories')->insert($learningtasks_categories);
        }
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
    }
}
