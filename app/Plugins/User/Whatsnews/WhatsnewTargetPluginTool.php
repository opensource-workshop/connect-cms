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
            // クラスファイルの存在チェック。
            list($class_name, $file_path) = Plugins::getPluginClassNameAndFilePath($plugin->plugin_name);
            // ファイルの存在確認
            if (!file_exists($file_path)) {
                continue;
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
