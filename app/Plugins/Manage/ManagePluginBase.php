<?php

namespace App\Plugins\Manage;

use File;

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

    /**
     *  テーマ名取得
     */
    private function getThemeName($dir, $theme_inis = null, $parent_dir = null)
    {
        // テーマ設定がない場合はディレクトリ名
        if (empty($theme_inis)) {
            return array('name' => basename($dir), 'dir' => ($parent_dir) ? $parent_dir . '/' . basename($dir) : basename($dir));
        }

        // テーマ設定からテーマ名
        if (array_key_exists('theme_name', $theme_inis)) {
            return array('name' => $theme_inis['theme_name'], 'dir' => ($parent_dir) ? $parent_dir . '/' . basename($dir) : basename($dir));
        }
        else {
            return array('name' => basename($dir), 'dir' => ($parent_dir) ? $parent_dir . '/' . basename($dir) : basename($dir));
        }
    }

    /**
     *  テーマ取得
     */
    protected function getThemes()
    {
        // テーマディレクトリ
        $dirs = File::directories(public_path() . '/themes/');
        //print_r($dirs);

        $themes = array();  // 画面に渡すテーマ配列
        foreach($dirs as $dir) {
            if (File::exists($dir."/themes.ini")) {

                // テーマ設定ファイルのパース
                $theme_inis = parse_ini_file($dir."/themes.ini");

                // ディレクトリがテーマ・グループ用のものなら、その下のディレクトリを探す。
                if (array_key_exists('theme_dir', $theme_inis) && $theme_inis['theme_dir'] == 'group') {

                    $sub_themes = array();  // ディレクトリ管理のサブテーマ配列

                    // テーマの第2階層ディレクトリ
                    $group_dirs = File::directories(public_path() . '/' . '/themes/' . basename($dir));
                    foreach($group_dirs as $group_dir) {

                        if (File::exists($group_dir."/themes.ini")) {

                            // テーマ設定ファイルのパース
                            $group_theme_inis = parse_ini_file($group_dir."/themes.ini");

                            // テーマ設定ファイルからテーマ名を探す。設定がなければディレクトリ名をテーマ名とする。
                            $sub_themes[] = $this->getThemeName($group_dir, $group_theme_inis, basename($dir));
                        }
                        else {
                            $sub_themes[] = $this->getThemeName($group_dir, null, basename($dir));
                        }
                    }
                    $themes[] = array('name' => basename($dir), 'dir' => basename($dir), 'themes' => $sub_themes);
                }
                else {
                    // テーマ設定ファイルからテーマ名を探す。設定がなければディレクトリ名をテーマ名とする。
                    $themes[] = $this->getThemeName($dir, $theme_inis);
                }
            }
            else {
                $themes[] = $this->getThemeName($dir);
            }
        }
        return $themes;
    }
}
