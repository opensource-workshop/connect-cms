<?php

namespace App\Plugins\Api\Receives;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

use DB;

use App\Models\User\Receives\Receive;
use App\Models\User\Receives\ReceiveData;
use App\Models\User\Receives\ReceiveRecord;

use App\Plugins\Api\ApiPluginBase;
use App\Traits\ConnectCommonTrait;

/**
 * 測定機器からのデータ収集クラス
 *
 * @author 永原　篤 <nagahara@opensource-workshop.jp>
 * @copyright OpenSource-WorkShop Co.,Ltd. All Rights Reserved
 * @category データ収集プラグインAPI
 * @package Contoroller
 */
class Receives extends ApiPluginBase
{

    use ConnectCommonTrait;

    /**
     *  データ受け取り
     *
     * @return view
     */
    public function index($request, $site_key, $login_id, $token)
    {
        /*
            想定URL
            http://domain/api/Receives/index?key=xxx&token=xxx&temperature=xxx&humidity=xxx
        */

        // 必要な値のチェック
        if (!$request->has(['key', 'token'])) {
            return "Error. undefined needs column.";
        }

        // key でデータベース確認
        $receive = Receive::where('key', $request->key)->first();
        if (empty($receive)) {
            return "Error. database not found.";
        }

        // token で認証チェック
        if ($receive->token != $request->token) {
            return "Error. token error.";
        }

        // columns から配列生成
        $columns = explode(',', $receive->columns);

        // 受け取った値を保持する配列
        $values = array();

        // Request からcolumns のキーで値を取得
        foreach($columns as $column) {
            $values[$column] = $request->query($column);
        }

        // receive_record に値の保持
        $receive_record = ReceiveRecord::create([
            'receive_id' => $receive->id,
        ]);

        // receives_datas に値の保持
        foreach($values as $column => $value) {
            $receive_data = ReceiveData::create([
                'record_id'  => $receive_record->id,
                'column_key' => $column,
                'value'      => $value,
            ]);
        }

        // 正常終了のコードの返却
        return "OK";
    }
}
