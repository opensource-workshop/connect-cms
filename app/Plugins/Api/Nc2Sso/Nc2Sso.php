<?php

namespace App\Plugins\Api\Nc2Sso;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;

use DB;

use App\User;
use App\Plugins\Api\ApiPluginBase;

/**
 * NC2からのSSO管理クラス
 *
 * @author 永原　篤 <nagahara@opensource-workshop.jp>
 * @copyright OpenSource-WorkShop Co.,Ltd. All Rights Reserved
 * @category ページ管理
 * @package Contoroller
 */
class Nc2Sso extends ApiPluginBase
{
    /**
     *  ページ初期表示
     *
     * @return view
     */
    public function index($request, $site_key, $login_id, $token)
    {
        // 本来はDB管理
        $sites = config('cc_api_config.CC_API_CONFIGS');

// NC2 側のワーニングを修正すること。
// webapp\config\define.inc.php の最初。HTTP_USER_AGENT のチェック

//        $request_url = 'http://nc2.localhost/?action=connectsso_view_main_check';
        $request_url = $sites[$site_key]['url'] . '?action=connectsso_view_main_check&login_id=' . $login_id . '&res_token=' . md5($sites[$site_key]['salt'] . $token);
        //Log::debug($request_url);

        // NC2 をCallBack
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $request_url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        $return_json = curl_exec($ch);
        //Log::debug(print_r($return_json, true));

        // JSON データを複合化
        $check_result = json_decode($return_json, true);
        //Log::debug(print_r($check_result, true));

        // SSO するユーザの存在を確認
        $user = User::where('userid', $login_id)->first();

        // ユーザが存在する
        if (!empty($user)) {

            // 権限が一般 or ゲストの場合は、自動ログイン
            if ($user->role == config('cc_role.ROLE_PAGE_MANAGER') || $user->role == config('cc_role.ROLE_GUEST')) {

                // ログイン
                Auth::login($user, true);

                // トップページへ
                return redirect("/");
            }

            // 管理者権限の場合は、NC2 側でも管理者の場合、自動ログイン
            if ($user->role == config('cc_role.ROLE_SYSTEM_MANAGER') && $check_result['role_authority_id'] == 1) {

                // ログイン
                Auth::login($user, true);

                // トップページへ
                return redirect("/");
            }

            // 権限エラー
            abort(403, "SSO 権限エラー。<br />&nbsp;&nbsp;&nbsp;&nbsp;NetCommons2 の権限より高い権限でのログインはできません。");
        }
        else {
            // ユーザが存在しない場合、一般権限でユーザを作成して、自動ログイン

            $user           = new User;
            $user->name     = $check_result['handle'];
            $user->userid   = $login_id;
            $user->password = 'password';
            $user->role     = 0;
            $user->save();

            // ログイン
            Auth::login($user, true);

            // トップページへ
            return redirect("/");
        }
    }
}
