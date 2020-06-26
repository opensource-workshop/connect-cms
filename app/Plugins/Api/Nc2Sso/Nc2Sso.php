<?php

namespace App\Plugins\Api\Nc2Sso;

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
 * NC2からのSSO管理クラス
 *
 * @author 永原　篤 <nagahara@opensource-workshop.jp>
 * @copyright OpenSource-WorkShop Co.,Ltd. All Rights Reserved
 * @category ページ管理
 * @package Contoroller
 */
class Nc2Sso extends ApiPluginBase
{

    use ConnectCommonTrait;

    /**
     *  ページ初期表示
     *
     * @return view
     */
    public function index($request, $site_key, $login_id, $token)
    {
        // 本来はDB管理
        $sites = config('cc_api_config.CC_API_CONFIGS');

        // パラメータ、設定チェック
        if (empty($sites)) {
            // トップページへ
            return redirect("/");
        }

        if (empty($site_key) || !array_key_exists($site_key, $sites) || !array_key_exists('salt', $sites[$site_key]) || !array_key_exists('url', $sites[$site_key])) {
            // トップページへ
            return redirect("/");
        }

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

        // 権限エラー
        if (!$check_result["check"]) {
            abort(403, "認証エラー。");
        }

        // SSO するユーザの存在を確認
        $user = User::where('userid', $login_id)->first();

        // ユーザが存在する
        if (!empty($user)) {
            // ユーザ権限データ取得
            //$roles = UsersRoles::getUsersRoles($user->id);
            $users_roles = new UsersRoles();

            // 権限が一般 or ゲストの場合は、自動ログイン
            // if ($user->role == config('cc_role.ROLE_PAGE_MANAGER') || $user->role == config('cc_role.ROLE_GUEST')) {

            // ユーザはあり、書き込み系の権限がない場合は、自動ログイン
            //if ($users_roles->notRole('role_reporter', $user->id)) {

            // // ユーザはあり、記事書き込み権限のみの場合は、自動ログイン
            // if ($users_roles->isOnlyRole('role_reporter', $user->id)) {
            //     // ログイン
            //     Auth::login($user, true);

            //     // トップページへ
            //     return redirect("/");
            // }

            // // 管理者権限の場合は、NC2 側でも管理者の場合、自動ログイン
            // //if ($user->role == config('cc_role.ROLE_SYSTEM_MANAGER') && $check_result['role_authority_id'] == 1) {
            // if ($users_roles->haveAdmin($user->id) && $check_result['role_authority_id'] == 1) {
            //     // ログイン
            //     Auth::login($user, true);

            //     // トップページへ
            //     return redirect("/");
            // }

            // // 権限エラー
            // abort(403, "SSO 権限エラー。<br />&nbsp;&nbsp;&nbsp;&nbsp;NetCommons2 の権限より高い権限でのログインはできません。");

            // 権限チェックしない
            // ログイン
            Auth::login($user, true);

            // トップページへ
            return redirect("/");
            
        } else {
            // ユーザが存在しない場合、一般権限でユーザを作成して、自動ログイン
            $user           = new User;
            $user->name     = $check_result['handle'];
            $user->userid   = $login_id;
            $user->password = 'sso-invalid-password';   // プレーンテキストのパスワードは設定しても、入力パスワードと一致する事はないため、無効になる
            //$user->role     = 0;
            $user->save();

            // ユーザ権限の登録
            UsersRoles::create([
                'users_id'   => $user->id,
                'target'     => 'base',
                'role_name'  => 'role_reporter',
                'role_value' => 1
            ]);

            // ログイン
            Auth::login($user, true);

            // トップページへ
            return redirect("/");
        }
    }
}
