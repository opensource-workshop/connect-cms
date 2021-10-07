<?php

namespace App\Plugins\Api\Translate;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;

use App\Plugins\Api\ApiPluginBase;
use App\Traits\ConnectCommonTrait;

use App\Enums\UseType;

use App\Models\Core\Configs;

/**
 * 翻訳サービス管理クラス
 *
 * @author 永原　篤 <nagahara@opensource-workshop.jp>
 * @copyright OpenSource-WorkShop Co.,Ltd. All Rights Reserved
 * @category 翻訳サービス
 * @package Controller
 */
class Translate extends ApiPluginBase
{
    use ConnectCommonTrait;

    /**
     * 翻訳処理
     */
    public function post($request)
    {
        // 戻り値
        $msg_array = array();

        // API URL取得
        $api_url = config('connect.TRANSLATE_API_URL');
        if (empty($api_url)) {
            // API URLを設定しないとこの処理は通らないため、通常ここに入らない想定。そのためシステム的なメッセージを表示
            return ['return_texts' => ['error: 設定ファイル.envにTRANSLATE_API_URLが設定されていません。']];
        }

        if (Configs::getSharedConfigsValue('use_translate', UseType::not_use) == UseType::not_use) {
            Log::debug('入った');
            // 通常ここに入らない想定。（入る場合の例：誰かがウィジウィグで翻訳使用中に、管理者が翻訳を使用しないに設定変更して、翻訳が行われた場合等）
            return ['return_texts' => ['error: 翻訳の使用設定がONになっていません。']];
        }

        // cURLセッションを初期化する
        $ch = curl_init();

        // 送信データを指定
        $data = [
            'api_key' => config('connect.TRANSLATE_API_KEY'),
            'text' => $request->inline_text,
            'target_language' => $request->target_language,
        ];

        // URLとオプションを指定する
        curl_setopt($ch, CURLOPT_URL, $api_url);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($data));
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

        // URLの情報を取得する
        $res = curl_exec($ch);

        // 結果を表示する
        //Log::debug($res);
        $msg_array['return_texts'][] = $res;

        // セッションを終了する
        curl_close($ch);

        // 値を戻す
        // change: LaravelはArrayを返すだけで JSON形式になる
        // $msg_json = json_encode($msg_array);
        // echo $msg_json;
        return $msg_array;
    }
}
