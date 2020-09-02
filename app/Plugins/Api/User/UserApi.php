<?php

namespace App\Plugins\Api\User;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;

use App\User;
use App\Models\Core\ApiSecret;
use App\Models\Core\UsersRoles;

use App\Traits\ConnectCommonTrait;
use App\Plugins\Api\ApiPluginBase;

/**
 * ユーザ関係APIクラス
 *
 * @author 永原　篤 <nagahara@opensource-workshop.jp>
 * @copyright OpenSource-WorkShop Co.,Ltd. All Rights Reserved
 * @category ユーザ関係API
 * @package Contoroller
 */
class UserApi extends ApiPluginBase
{
    use ConnectCommonTrait;

    /**
     *  ページ初期表示
     */
    public function info($request, $userid)
    {
        // 秘密コードがない場合はエラー
        if ($request->filled('secret_code')) {
            $secret_code = $request->secret_code;
        } else {
            $ret = array('code' => 403, 'message' => '秘密コードが必要です。');
            return $this->encodeJson($ret, $request);
        }

        // 秘密コードのチェック(IPアドレス指定などで、複数のレコードがある可能性あり)
        $api_secrets = ApiSecret::where('secret_code', $secret_code)->where('apis', 'like', '%User%')->get();
        if ($api_secrets->isEmpty()) {
            $ret = array('code' => 403, 'message' => '閲覧条件に合致しません。');
            return $this->encodeJson($ret, $request);
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
            $ret = array('code' => 403, 'message' => 'ネットワーク条件に合致しません。');
            return $this->encodeJson($ret, $request);
        }

        // 返すデータ取得
        $user = User::where('userid', $userid)->first();
        if (empty($user)) {
            $ret = array('code' => 404, 'message' => '指定されたユーザが存在しません。');
            return $this->encodeJson($ret, $request);
        }

        $ret = array('code' => 200, 'message' => '', 'userid' => $user->userid, 'name' => $user->name);
        return $this->encodeJson($ret, $request);
    }

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
}
