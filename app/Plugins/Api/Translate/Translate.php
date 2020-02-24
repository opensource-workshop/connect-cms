<?php

namespace App\Plugins\Api\Translate;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;

use DB;

use App\Models\Core\UsersRoles;
use App\User;
use App\Plugins\Api\ApiPluginBase;
use App\Traits\ConnectCommonTrait;

/**
 * 翻訳サービス管理クラス
 *
 * @author 永原　篤 <nagahara@opensource-workshop.jp>
 * @copyright OpenSource-WorkShop Co.,Ltd. All Rights Reserved
 * @category 翻訳サービス
 * @package Contoroller
 */
class Translate extends ApiPluginBase
{

    use ConnectCommonTrait;

    /**
     *  翻訳処理
     */
    public function post($request)
    {

        // 戻り値
        $msg_array = array();

        $url = "https://translate-api.opensource-workshop.jp";

        // cURLセッションを初期化する
        $ch = curl_init();

        // 送信データを指定
        $data = array('text' => $request->inline_text);

        // URLとオプションを指定する
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($data));
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

        // URLの情報を取得する
        $res = curl_exec($ch);

        // 結果を表示する
        //var_dump($res);
        //Log::debug($res);
        $msg_array['return_texts'][] = $res;

        // セッションを終了する
        curl_close($ch);

        // 値を戻す
        $msg_json = json_encode($msg_array);
        echo $msg_json;
    }
}
