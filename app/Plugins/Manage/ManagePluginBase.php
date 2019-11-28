<?php

namespace App\Plugins\Manage;

use App\Models\Core\Configs;

use App\Plugins\PluginBase;

/**
 * 管理プラグイン
 *
 * 管理ページ用プラグインの基底クラス
 *
 * @author 永原　篤 <nagahara@opensource-workshop.jp>
 * @copyright OpenSource-WorkShop Co.,Ltd. All Rights Reserved
 * @category 管理プラグイン
 * @package Contoroller
 */
class ManagePluginBase extends PluginBase
{

    /**
     *  設定されているConfig の取得
     */
    protected function getConfigs($name = null, $category = null)
    {
        $return_configs = array();

        if ($name) {
            $configs = Configs::where('name', $name)->get();
        }
        elseif ($category) {
            $configs = Configs::where('category', $category)->get();
        }
        else {
            $configs = Configs::get();
        }

        foreach($configs as $config) {
            $return_configs[$config->name] = $config;
        }

        return $return_configs;
    }

}
