<?php

namespace App\Models\User\Searchs;

use Illuminate\Database\Eloquent\Model;

use App\Enums\SearchsTargetPlugin;

class Searchs extends Model
{
    /**
     * create()やupdate()で入力を受け付ける ホワイトリスト
     */
    protected $fillable = [];

    /**
     * 表示するプラグインの配列を返却
     */
    public function getTargetPlugins()
    {
        // 検索として対象としているプラグインの定義
        // $target_plugins = array("contents" => false, "blogs" => false);
        $target_plugins = array();
        $enums_target_plugins = SearchsTargetPlugin::getMembers();
        foreach ($enums_target_plugins as $target_plugin_key => $enums_target_plugin) {
            $target_plugins[$target_plugin_key]['use_flag'] = false;
            $target_plugins[$target_plugin_key]['plugin_name_full'] = $enums_target_plugin;
        }

        // 表示ON になっているプラグインの情報を付与して返却
        if (!empty($this->target_plugins)) {
            foreach (explode(',', $this->target_plugins) as $target_plugin) {
                // $target_plugins[$target_plugin] = true;
                $target_plugins[$target_plugin]['use_flag'] = true;
            }
        }

        return $target_plugins;
    }

    /**
     * 指定したFrame が表示対象か判定
     */
    public function isTargetFrame($frame_id)
    {
        if (in_array($frame_id, explode(',', $this->target_frame_ids))) {
            return true;
        }
        return false;
    }
}
