<?php

namespace app\Plugins\Mypage\LoginHistoryMypage;

use Illuminate\Support\Facades\Auth;

use App\Models\Core\UsersLoginHistories;

use App\Plugins\Mypage\MypagePluginBase;

/**
 * ログイン履歴マイページクラス
 *
 * @plugin_title ログイン履歴
 * @plugin_desc 自分のログイン履歴を確認できます。
 */
class LoginHistoryMypage extends MypagePluginBase
{
    /**
     * ページ初期表示(ログイン履歴画面表示)
     *
     * @method_title ログイン履歴
     * @method_desc 自分のログイン履歴を確認できます。
     * @method_detail ログイン日時、IPアドレス、ユーザエージェントを確認できます。
     */
    public function index($request, $id = null)
    {
        // ログインしているユーザー情報を取得
        $user = Auth::user();

        // ログイン履歴取得
        $users_login_histories = UsersLoginHistories::where('users_id', $user->id)
                ->orderBy('logged_in_at', 'desc')
                ->paginate(10, ["*"]);

        // 画面呼び出し
        return view('plugins.mypage.loginhistory.list', [
            'themes'          => $request->themes,
            "function" => __FUNCTION__,
            "plugin_name" => "loginhistory",
            "users_login_histories" => $users_login_histories,
        ]);
    }
}
