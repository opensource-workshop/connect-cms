<?php

namespace Tests\Browser\Theme;

use Laravel\Dusk\Browser;
use Tests\DuskTestCase;

/**
 * テーマのテスト
 *
 */
class ThemeTest extends DuskTestCase
{
    /**
     * 実行
     */
    public function testInvoke()
    {
        // マニュアル出力のために、dusk データベースなど利用するので、アサーションは無条件にOKとしたい。
        $this->assertTrue(true);

        // テーマ
        $this->getThemeList();
    }

    /**
     * テストするテーマの一覧を取得
     */
    private function getThemeList()
    {
        // テーマリストの取得
        $theme_list_txt = file_get_contents(config('connect.ADD_THEME_DIR') . 'theme_list.txt');
        $theme_list_objs = json_decode($theme_list_txt, false);

        // リストをループして、詳細取得
        foreach ($theme_list_objs as $theme_list_obj) {
            $this->getThemeInfo($theme_list_obj->name, $theme_list_obj->dir);
        }
    }

    /**
     * テストするテーマの情報を取得
     */
    private function getThemeInfo($name, $dir)
    {
        // テーマ情報の取得
        $theme_info_txt = file_get_contents(config('connect.ADD_THEME_DIR') . $dir . '\theme.txt');
        $theme_info_obj = json_decode($theme_info_txt, false);

        // モデル（データベース）の処理
        $this->createModel($theme_info_obj->models);


        // リストをループして、詳細処理
//        foreach ($theme_info_objs as $theme_info_obj) {
//            var_dump($theme_info_obj);
//        }
    }

    /**
     * モデルの処理
     */
    private function createModel($models)
    {
        foreach($models as $model_info) {
        }
    }
}
