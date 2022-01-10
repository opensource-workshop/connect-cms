<?php

namespace App\Plugins\Api\User;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;

use App\User;
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
        // API 共通チェック
        $ret = $this->apiCallCheck($request, 'User');
        if (!empty($ret['code'])) {
            return $this->encodeJson($ret, $request);
        }

        // 返すデータ取得
        $user = User::where('userid', $userid)->first();
        if (empty($user)) {
            // ユーザがいない場合は、外部認証ユーザを探しに行く。
            $user_info = $this->getOtherAuthUser($request, $userid);
            if ($user_info['code'] == 200) {
                $ret = array('code' => 200, 'message' => '', 'userid' => $userid, 'name' => $user_info['name']);
                return $this->encodeJson($ret, $request);
            }
            if ($user_info['code'] == 100) {
                // 続き
            } else {
                $ret = array('code' => $user_info['code'], 'message' => $user_info['message']);
                return $this->encodeJson($ret, $request);
            }

            $ret = array('code' => 404, 'message' => '指定されたユーザが存在しません。');
            return $this->encodeJson($ret, $request);
        }

        $ret = array('code' => 200, 'message' => '', 'userid' => $user->userid, 'name' => $user->name);
        return $this->encodeJson($ret, $request);
    }
}
