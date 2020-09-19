<?php

namespace app\Plugins\Mypage\IndexMypage;

use App\Plugins\Mypage\MypagePluginBase;

/**
 * マイページ画面インデックスクラス
 */
class IndexMypage extends MypagePluginBase
{
    /**
     *  ページ初期表示
     *
     * @return view
     */
    public function index($request)
    {
        // 管理画面プラグインの戻り値の返し方
        // view 関数の第一引数に画面ファイルのパス、第二引数に画面に渡したいデータを名前付き配列で渡し、その結果のHTML。
        return view('plugins.mypage.index.index', [
            "plugin_name"  => "index",
        ]);
    }
}
