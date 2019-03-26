<?php

namespace App\Plugins\User\Menu;

use Illuminate\Support\Facades\Log;

use App\Page;
use App\Plugins\User\UserPluginBase;

/**
 * メニュープラグイン
 *
 * ページデータの表示を行う。
 * ページデータは入れ子集合モデルで表されている。
 * 入れ子集合モデルの処理には lazychaser/laravel-nestedset を使用
 * (https://github.com/lazychaser/laravel-nestedset)
 *
 * @author 永原　篤 <nagahara@opensource-workshop.jp>
 * @copyright OpenSource-WorkShop Co.,Ltd. All Rights Reserved
 * @category ページプラグイン
 * @package Contoroller
 */
class Menu extends UserPluginBase
{

    /**
     *  ページデータ取得関数
     *
     *  ページデータを取得し、深さを追加して画面に。
     *
     * @param int $frame_id
     * @return view
     */
    public function viewInit($frame_id = null)
    {
        // ページデータ＆深さを全て取得
        // 表示順は入れ子集合モデルの順番
        $pages = Page::defaultOrderWithDepth();

        // 画面へ
        return view('plugins.user.menu.menu', [
            'pages' => $pages
        ]);
    }
}
