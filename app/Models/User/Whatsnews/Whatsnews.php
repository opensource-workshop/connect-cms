<?php

namespace App\Models\User\Whatsnews;

use Illuminate\Support\Facades\Log;

use Illuminate\Database\Eloquent\Model;

use App\Enums\WhatsnewsTargetPlugin;

class Whatsnews extends Model
{
    // 更新する項目の定義
    protected $fillable = [
        'bucket_id',
        'whatsnew_name',
        'view_pattern',
        'count',
        'days',
        'rss',
        'rss_count',
        'view_posted_name',
        'view_posted_at',
        'target_plugins',
        'frame_select',
        'read_more_use_flag',
        'read_more_name',
        'read_more_fetch_count',
        'read_more_btn_color_type',
        'read_more_btn_type',
        'read_more_btn_transparent_flag'
    ];

    /**
     *  表示するプラグインの配列を返却
     *
     */
    public function getTargetPlugins()
    {
        // 新着情報として対象としているプラグインの定義
        // $target_plugins = array(
        //     "blogs" => false,
        //     "databases" => false,
        // );
        $target_plugins = array();
        // $target_plugin_keys = WhatsnewsTargetPlugin::getMemberKeys();
        // foreach ($target_plugin_keys as $target_plugin_key) {
        //     $target_plugins[$target_plugin_key] = false;
        // }

        $enums_target_plugins = WhatsnewsTargetPlugin::getMembers();
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
