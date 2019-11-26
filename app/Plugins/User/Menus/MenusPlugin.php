<?php

namespace App\Plugins\User\Menus;

use Illuminate\Support\Facades\Log;

use App\Models\Common\Page;

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
class MenusPlugin extends UserPluginBase
{

    /**
     *  ページデータ取得関数
     *
     *  ページデータを取得し、深さを追加して画面に。
     *
     * @return view
     */
    public function index($request, $page_id, $frame_id)
    {
        // ページデータ＆深さを全て取得
        // 表示順は入れ子集合モデルの順番
        $format = null;
        $pages = Page::defaultOrderWithDepth($format, $this->page);

        //Log::debug(json_encode( $multi_language_root_page, JSON_UNESCAPED_UNICODE));
        //Log::debug(json_encode( $pages, JSON_UNESCAPED_UNICODE));

        // パンくずリスト用に自分と上位階層のページを取得
        $ancestors = Page::ancestorsAndSelf($page_id);

        // 画面へ
        return view(
            $this->getViewPath('menus'), [
            'page_id'   => $page_id,
            'pages'     => $pages,
            'ancestors' => $ancestors,
            'page'      => $this->page,
        ]);
    }
}
