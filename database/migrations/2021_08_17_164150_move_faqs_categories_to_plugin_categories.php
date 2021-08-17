<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

use Illuminate\Support\Facades\DB;

use App\Enums\PluginName;

class MoveFaqsCategoriesToPluginCategories extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        // 共通plugin_categoriesにFAQカテゴリが無ければ移し替え
        if (DB::table('plugin_categories')->where('target', PluginName::getPluginName(PluginName::faqs))->count() == 0) {

            $faqs_categories = DB::table('faqs_categories')->whereNull('faqs_categories.deleted_at')->get();
            $faqs_categories = $faqs_categories->toArray();
            // stdClassをArrayにキャスト. （モデルだとtoArray()で配列になるが、DBファサードの場合 stdClassのままのため、キャストする）
            $faqs_categories = json_decode(json_encode($faqs_categories), true);

            foreach ($faqs_categories as &$faqs_category) {
                unset($faqs_category['id']);

                $faqs_category['target'] = PluginName::getPluginName(PluginName::faqs);
                $faqs_category['target_id'] = $faqs_category['faqs_id'];
                unset($faqs_category['faqs_id']);
            }

            DB::table('plugin_categories')->insert($faqs_categories);
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
