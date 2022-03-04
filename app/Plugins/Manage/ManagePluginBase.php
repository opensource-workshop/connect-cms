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
 * @package Controller
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

    /**
     * ページネートの表示ページを、セッションorリクエストから取得
     */
    protected function getPaginatePageFromRequestOrSession(\Illuminate\Http\Request $request, string $session_name, string $page_variable): int
    {
        // 表示ページ数。詳細で更新して戻ってきたら、元と同じページを表示したい。
        // セッションにあればページの指定があれば使用。
        // ただし、リクエストでページ指定があればそれが優先。(ページング操作)
        $page = 1;
        if ($request->session()->has($session_name)) {
            $page = $request->session()->get($session_name);
        }
        if ($request->filled($page_variable)) {
            $page = $request->$page_variable;
        }

        // ページがリクエストで指定されている場合は、セッションの検索条件配列のページ番号を更新しておく。
        // 詳細画面や更新処理から戻ってきた時用
        if ($request->filled($page_variable)) {
            session([$session_name => $request->$page_variable]);
        }

        return $page;
    }
}
