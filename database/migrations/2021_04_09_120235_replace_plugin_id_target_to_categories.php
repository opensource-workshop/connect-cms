<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

use App\Models\Core\Plugins;
use App\Models\Common\Categories;

class ReplacePluginIdTargetToCategories extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        // bugfix: サイト管理＞カテゴリ設定のプラグインIDが違うバグ修正

        $plugins = Plugins::get();

        // 課題管理
        $plugin_id = $plugins->where('plugin_name', \PluginName::learningtasks)->first()->id;
        // dd(\PluginName::learningtasks);

        // debug:確認したいSQLの前にこれを仕込んで
        // \DB::enableQueryLog();

        Categories::where('target', 'learningtasks')
                ->update([
                    'target' => \PluginName::learningtasks,
                    'plugin_id' => $plugin_id,
                ]);

        // debug: sql dumpする
        // \Log::debug(var_export(\DB::getQueryLog(), true));

        // FAQ
        $plugin_id = $plugins->where('plugin_name', \PluginName::faqs)->first()->id;

        Categories::where('target', 'faqs')
                ->update([
                    'target' => \PluginName::faqs,
                    'plugin_id' => $plugin_id,
                ]);

        // ブログ
        $plugin_id = $plugins->where('plugin_name', \PluginName::blogs)->first()->id;

        Categories::where('target', 'blogs')
                ->update([
                    'target' => \PluginName::blogs,
                    'plugin_id' => $plugin_id,
                ]);
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
