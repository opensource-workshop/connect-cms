<?php

namespace App\Plugins\User\Whatsnews;

use App\Models\Core\Plugins;

/**
 * 新着対象プラグイン
 *
 * @author 牟田口 満 <mutaguchi@opensource-workshop.jp>
 * @copyright OpenSource-WorkShop Co.,Ltd. All Rights Reserved
 * @category 新着情報プラグイン
 * @package Utility
 */
class WhatsnewTargetPluginTool
{
    /**
     * key/valueの連想配列を返す
     *
     * １．DBのプラグインインストール一覧
     * ２．各プラグインのオプション含む、で新着対象か？
     * ３．array作成してそれ返す
     */
    public static function getMembers(): array
    {
        $target_plugins = [];

        // プラグイン一覧の取得
        $plugins = Plugins::where('display_flag', 1)->orderBy('display_sequence')->get();

        foreach ($plugins as $plugin) {
            // 通常のクラスファイルの存在チェック。
            $file_path = base_path() . "/app/Plugins/User/" . ucfirst($plugin->plugin_name) . "/" . ucfirst($plugin->plugin_name) . "Plugin.php";
            // 各プラグインのgetWhatsnewArgs() 関数を呼び出し。
            $class_name = "App\Plugins\User\\" . ucfirst($plugin->plugin_name) . "\\" . ucfirst($plugin->plugin_name) . "Plugin";

            // ない場合はオプションプラグインを探す
            if (!file_exists($file_path)) {
                $file_path = base_path() . "/app/PluginsOption/User/" . ucfirst($plugin->plugin_name) . "/" . ucfirst($plugin->plugin_name) . "Plugin.php";
                $class_name = "App\PluginsOption\User\\" . ucfirst($plugin->plugin_name) . "\\" . ucfirst($plugin->plugin_name) . "Plugin";

                // ファイルの存在確認
                if (!file_exists($file_path)) {
                    continue;
                }
            }

            $class = new $class_name;

            // 新着対象か
            if ($class->use_whatsnew) {
                $target_plugins[self::getPluginName($plugin->plugin_name)] = $plugin->plugin_name_full;
            };
        }

        return $target_plugins;
    }

    /**
     * DBに登録される plugin_name を取得
     *
     * @see resources\views\layouts\add_plugin.blade.php
     */
    private static function getPluginName($plugin_name): string
    {
        return strtolower($plugin_name);
    }

    /**
     * key配列を返す
     */
    public static function getMemberKeys(): array
    {
        return array_keys(self::getMembers());
    }
}
