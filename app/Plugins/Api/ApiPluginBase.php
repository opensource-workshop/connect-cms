<?php

namespace App\Plugins\Api;

use Illuminate\Http\Request;

use App\Models\Core\ApiSecret;

use App\Plugins\PluginBase;

use App\Traits\ConnectCommonTrait;

/**
 * APIプラグイン
 *
 * APIプラグインの基底クラス
 *
 * @author 永原　篤 <nagahara@opensource-workshop.jp>
 * @copyright OpenSource-WorkShop Co.,Ltd. All Rights Reserved
 * @category APIプラグイン
 * @package Controller
 */
class ApiPluginBase extends PluginBase
{
    use ConnectCommonTrait;

    /**
     * json encode
     *
     * @todo app\Http\Controllers\Core\ApiController.php で重複
     */
    public function encodeJson($value, $request = null)
    {
        // UNICODE エスケープ指定
        if (!empty($request) && $request->filled('escape') && $request->escape == 'json_unescaped_unicode') {
            return json_encode($value, JSON_UNESCAPED_UNICODE);
        }
        return json_encode($value);
    }

    /**
     *  json encode
     */
    public function apiCallCheck($request, $plugin_name)
    {
        // 秘密コードがない場合はエラー
        if ($request->filled('secret_code')) {
            $secret_code = $request->secret_code;
        } else {
            return array('code' => 403, 'message' => '秘密コードが必要です。');
        }

        // 秘密コードのチェック(IPアドレス指定などで、複数のレコードがある可能性あり)
        //$api_secrets = ApiSecret::where('secret_code', $secret_code)->where('apis', 'like', '%User%')->get();
        $api_secrets = ApiSecret::where('secret_code', $secret_code)->where('apis', 'like', '%' . $plugin_name . '%')->get();
        if ($api_secrets->isEmpty()) {
            return array('code' => 403, 'message' => '閲覧条件に合致しません。');
        }

        // IPアドレスのチェック(どれかOK ならOK)
        $ip_check_flag = false;
        foreach ($api_secrets as $api_secret) {
            // IP アドレス指定なし。OK
            if (empty($api_secret->ip_address)) {
                $ip_check_flag = true;
                continue;
            }

            // IP アドレス指定あり
            if ($this->isRangeIp($request->ip(), $api_secret->ip_address)) {
                // IP アドレス合致 OK
                $ip_check_flag = true;
                continue;
            }
        }
        if ($ip_check_flag == false) {
            return array('code' => 403, 'message' => 'ネットワーク条件に合致しません。');
        }

        // 制限クリア」。
        return array('code' => '', 'message' => '');
    }

    /**
     * requestに紐づけられたSlackのハッシュ値（A）と、request内容から生成したハッシュ値（B）を比較して、一致すればtrueを返す
     *
     * @param Request $request
     * @return boolean
     */
    public function compareSlackHashes(Request $request) : bool
    {
        // Slackの投稿に紐付けられたハッシュ値（A）を取得
        $slack_signature = $request->header('x-slack-signature');
        list($version, $signature_hash) = explode("=", $slack_signature);

        // request内容からハッシュ値生成用の基本文字列を生成
        $timestamp = $request->header('x-slack-request-timestamp');
        $body = $request->all();
        $query = "";
        foreach ($body as $key => $value) {
            $query .= $key . '=' . urlencode($value). '&';
        }
        $query = rtrim($query,'&');
        $base_string = $version . ':' . $timestamp . ':' . $query;

        // 基本文字列からハッシュ値（B）を作る
        $hash = hash_hmac('sha256', $base_string, config('connect.SLACK_SIGNING_SECRET'));

        // ハッシュ（A，B）を比較する
        return hash_equals($signature_hash , $hash);
    }
}
