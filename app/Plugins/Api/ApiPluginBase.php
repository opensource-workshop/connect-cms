<?php

namespace App\Plugins\Api;

use App\Models\Core\ApiSecret;

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
class ApiPluginBase extends PluginBase
{
    /**
     *  json encode
     */
    public function encodeJson($value, $request = null)
    {
        // UNOCIDE エスケープ指定
        if (!empty($request) && $request->filled('escape') && $request->escape == 'json_unescaped_unicode') {
            return json_encode($value, JSON_UNESCAPED_UNICODE);
        }
        return json_encode($value);
    }

    /**
     *  json encode
     */
    public function apiCallCheck($request)
    {
        // 秘密コードがない場合はエラー
        if ($request->filled('secret_code')) {
            $secret_code = $request->secret_code;
        } else {
            return array('code' => 403, 'message' => '秘密コードが必要です。');
        }

        // 秘密コードのチェック(IPアドレス指定などで、複数のレコードがある可能性あり)
        $api_secrets = ApiSecret::where('secret_code', $secret_code)->where('apis', 'like', '%User%')->get();
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
}
