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
 * @package Controller
 * @plugin_title 管理画面
 * @plugin_desc 管理画面の初めに開く画面です。Connect-CMS の公式サイトより、最新情報を取得して表示します。
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
     * @method_title お知らせ
     * @method_desc 最新機能やバージョンアップ情報が表示されます。
     * @method_detail Connect-CMSのバージョンアップ情報は公式サイトでも確認できます。
     */
    public function index($request)
    {
        $path = 'http://connect-cms.jp/connect-news';

        $options = array(
            CURLOPT_URL => $path,
            CURLOPT_FAILONERROR => 1,
            CURLOPT_FOLLOWLOCATION => 1,
            CURLOPT_RETURNTRANSFER => 1,
            CURLOPT_TIMEOUT => 15,
        );

        // Proxy設定
        if (config('connect.HTTPPROXYTUNNEL')) {
            $options = $this->addProxyOptions($options);
        }

        // XML取得
        $ch = curl_init();
        curl_setopt_array($ch, $options);
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

    /**
     * プロキシ設定を追加する。
     *
     * @param array $options cURLオプション
     * @return array プロキシ設定追加済みのcURLオプション
     */
    private function addProxyOptions(array $options)
    {
        $options[CURLOPT_HTTPPROXYTUNNEL] = config('connect.HTTPPROXYTUNNEL');
        $options[CURLOPT_PROXYPORT] = config('connect.PROXYPORT');
        $options[CURLOPT_PROXY] = config('connect.PROXY');
        $options[CURLOPT_PROXYUSERPWD] = config('connect.PROXYUSERPWD');

        return $options;
    }
}
