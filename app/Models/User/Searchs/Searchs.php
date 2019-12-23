<?php

namespace App\Models\User\Searchs;

use Illuminate\Database\Eloquent\Model;

class Searchs extends Model
{
    /**
     * create()やupdate()で入力を受け付ける ホワイトリスト
     */
    protected $fillable = [];

    /**
     *  表示するプラグインの配列を返却
     *
     */
    public function getTargetPlugins()
    {
        // 検索として対象としているプラグインの定義
        $target_plugins = array("contents" => false, "blogs" => false);

        // 表示ON になっているプラグインの情報を付与して返却
        if (!empty($this->target_plugins)) {
            foreach(explode(',', $this->target_plugins) as $target_plugin) {
                $target_plugins[$target_plugin] = true;
            }
        }

        return $target_plugins;
    }

    /**
     *  指定したFrame が表示対象か判定
     *
     */
    public function isTargetFrame($frame_id)
    {
        if (in_array($frame_id, explode(',', $this->target_frame_ids))) {
            return true;
        }
        return false;
    }
}
