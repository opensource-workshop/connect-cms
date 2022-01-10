<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

use Illuminate\Support\Facades\DB;

use App\Enums\PluginName;

class MoveBlogsCategoriesToPluginCategories extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        // 共通plugin_categoriesにFAQカテゴリが無ければ移し替え
        if (DB::table('plugin_categories')->where('target', PluginName::getPluginName(PluginName::blogs))->count() == 0) {

            $blogs_categories = DB::table('blogs_categories')->whereNull('blogs_categories.deleted_at')->get();
            $blogs_categories = $blogs_categories->toArray();
            // stdClassをArrayにキャスト. （モデルだとtoArray()で配列になるが、DBファサードの場合 stdClassのままのため、キャストする）
            $blogs_categories = json_decode(json_encode($blogs_categories), true);

            foreach ($blogs_categories as &$blogs_category) {
                unset($blogs_category['id']);

                $blogs_category['target'] = PluginName::getPluginName(PluginName::blogs);
                $blogs_category['target_id'] = $blogs_category['blogs_id'];
                unset($blogs_category['blogs_id']);
            }

            DB::table('plugin_categories')->insert($blogs_categories);
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
