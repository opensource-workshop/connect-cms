<?php

namespace App\Plugins\Mypage;

use App\Models\Core\Configs;

use App\Plugins\PluginBase;

/**
 * マイページ用プラグイン
 *
 * マイページ用プラグインの基底クラス
 */
class MypagePluginBase extends PluginBase
{
    /**
     * 設定されているConfig の取得
     */
    protected function getConfigs($name = null, $category = null)
    {
        $return_configs = array();

        if ($name) {
            $configs = Configs::where('name', $name)->get();
        } elseif ($category) {
            $configs = Configs::where('category', $category)->get();
        } else {
            $configs = Configs::get();
        }

        foreach ($configs as $config) {
            $return_configs[$config->name] = $config;
        }

        return $return_configs;
    }
}
