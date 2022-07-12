<?php

namespace App\Utilities\User;

use App\Enums\AuthMethodType;
use App\Models\Core\Configs;

class UserUtils
{
    /**
     * 外部ユーザ情報取得
     */
    public static function getOtherAuthUser($request, $userid)
    {
        // 使用する外部認証 取得
        $auth_method_event = Configs::getAuthMethodEvent();

        // 外部認証ではない場合は戻る
        if (empty($auth_method_event->value)) {
            // 外部認証の対象外
            return array('code' => 100, 'message' => '', 'userid' => $userid, 'name' => '');
        }

        // NetCommons2 認証
        if ($auth_method_event->value == AuthMethodType::netcommons2) {
            // 外部認証設定 取得
            $auth_method = Configs::where('name', 'auth_method')->where('value', AuthMethodType::netcommons2)->first();

            // リクエストURLの組み立て
            $request_url = $auth_method['additional1'] . '?action=connectauthapi_view_main_getuser&userid=' . $userid . '&site_key=' . $auth_method['additional2'] . '&check_value=' . md5($auth_method['additional5'] . $auth_method['additional3']);
            // Log::debug($request['password']);
            // Log::debug($auth_method['additional3']);
            // Log::debug(md5($request['password']));
            // Log::debug(md5(md5($request['password']) . $auth_method['additional3']));
            // Log::debug($request_url);

            // NC2 をCall
            $ch = curl_init();
            curl_setopt($ch, CURLOPT_URL, $request_url);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            $return_json = curl_exec($ch);

            // JSON データを複合化
            $check_result = json_decode($return_json, true);

            // 戻り値のチェック
            if (is_array($check_result) &&
                array_key_exists('check', $check_result) &&
                array_key_exists('handle', $check_result)) {
                if ($check_result['check'] == true) {
                    // ユーザ情報が取得できた
                    return array('code' => 200, 'message' => '', 'userid' => $userid, 'name' => $check_result['handle']);
                } else {
                    // ユーザ情報が取得できなかった
                    return array('code' => 404, 'message' => $check_result['message'], 'userid' => $userid, 'name' => '');
                }
            } else {
                // システム的なエラー
                return array('code' => 500, 'message' => '戻り値の異常', 'userid' => $userid, 'name' => '');
            }
        }
        // 外部認証の対象外
        return array('code' => 100, 'message' => '', 'userid' => $userid, 'name' => '');
    }
}
