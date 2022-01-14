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
     *  マニュアル定義
     */
    public static function declareManual($dusk)
    {
        $dusk->plugin_title = "お知らせ";
        $dusk->plugin_desc  = "管理画面の初めに開く画面です。<br />Connect-CMS の公式サイトより、最新情報を取得して表示します。";
        $dusk->setMethodManual(
            ["index" => [
                "title"  => "お知らせ",
                "desc"   => "最新機能やバージョンアップ情報が表示されます。",
                "detail" => ""],
            ]
        );
        return $dusk;
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
        curl_setopt($ch, CURLOPT_URL, $path);
        curl_setopt($ch, CURLOPT_FAILONERROR, 1);
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 1);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_TIMEOUT, 15);
        $ret_xml = curl_exec($ch);
        curl_close($ch);

        // 取得するXML とエラー用配列
        $rss_xml = null;
        $errors = array();

        // XML チェック
        if (empty($ret_xml)) {
            $errors[] = "Connect-CMS 更新情報が空で返ってきました。";
        } else {
            // libxmlエラーを無効にし、エラーを制御します。
            libxml_use_internal_errors(true);
            // XML ロード
            $rss_xml = simplexml_load_string($ret_xml);
            if ($rss_xml === false) {
                $errors[] = "Connect-CMS 更新情報が解析できませんでした。";
            }
        }

        // 管理画面プラグインの戻り値の返し方
        // view 関数の第一引数に画面ファイルのパス、第二引数に画面に渡したいデータを名前付き配列で渡し、その結果のHTML。
        return view('plugins.manage.index.index', [
            "plugin_name"  => "index",
            "rss_xml"      => $rss_xml,
            "errors"       => $errors,
        ]);
    }
}
