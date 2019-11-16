<?php

namespace app\Plugins\Manage\IndexManage;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

use DB;

use App\Plugins\Manage\ManagePluginBase;

/**
 * 管理画面インデックスクラス
 *
 * @author 永原　篤 <nagahara@opensource-workshop.jp>
 * @copyright OpenSource-WorkShop Co.,Ltd. All Rights Reserved
 * @category 管理画面インデックス
 * @package Contoroller
 */
class IndexManage extends ManagePluginBase
{
    /**
     *  権限定義
     */
    public function declareRole()
    {
        // 権限チェックテーブル
        $role_ckeck_table = array();
        $role_ckeck_table["index"]  = array('admin_system', 'admin_page', 'admin_site', 'admin_user');
        return $role_ckeck_table;
    }

    /**
     *  ページ初期表示
     *
     * @return view
     */
    public function index($request)
    {
        $path = 'http://connect-cms.jp/connect-news';

        // XML取得
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL,$path);
        curl_setopt($ch, CURLOPT_FAILONERROR,1);
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION,1);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER,1);
        curl_setopt($ch, CURLOPT_TIMEOUT, 15);
        $ret_xml = curl_exec($ch);
        curl_close($ch);

        $rss_xml = null;
        if (!empty($ret_xml)) {
            $rss_xml = new \SimpleXMLElement($ret_xml);
        }

        // 管理画面プラグインの戻り値の返し方
        // view 関数の第一引数に画面ファイルのパス、第二引数に画面に渡したいデータを名前付き配列で渡し、その結果のHTML。
        return view('plugins.manage.index.index',[
            "plugin_name"  => "index",
            "rss_xml"      => $rss_xml,
        ]);
    }
}
