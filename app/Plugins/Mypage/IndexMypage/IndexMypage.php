<?php

namespace app\Plugins\Mypage\IndexMypage;

use Illuminate\Support\Facades\Auth;
use App\Models\Core\UsersInputCols;
use App\Plugins\Mypage\MypagePluginBase;
use App\Plugins\Manage\UserManage\UsersTool;

/**
 * マイページ画面インデックスクラス
 *
 * @author 牟田口 満 <mutaguchi@opensource-workshop.jp>
 * @copyright OpenSource-WorkShop Co.,Ltd. All Rights Reserved
 * @category マイページ
 * @package Controller
 * @plugin_title マイページ
 * @plugin_desc マイページの初めに開く画面です。自分の情報を確認できます。
 */
class IndexMypage extends MypagePluginBase
{
    /**
     * 関数定義（コアから呼び出す）
     */
    public function getPublicFunctions()
    {
        // 標準関数以外で画面などから呼ばれる関数の定義
        $functions = [];
        $functions['get']  = [
            'passPdfDownload',
            'certPdfDownload',
        ];
        return $functions;
    }

    /**
     * ページ初期表示
     *
     * @return view
     * @method_title マイページ
     * @method_desc ログインIDやメールアドレスを確認できます。
     * @method_detail
     */
    public function index($request)
    {
        // ログインしているユーザー情報を取得
        $user = Auth::user();
        // カラムの登録データ
        $input_cols = UsersTool::getUsersInputCols([$user->id]);
        // ユーザーのカラム
        $users_columns = UsersTool::getUsersColumns($user->columns_set_id);

        // 管理画面プラグインの戻り値の返し方
        // view 関数の第一引数に画面ファイルのパス、第二引数に画面に渡したいデータを名前付き配列で渡し、その結果のHTML。
        return view('plugins.mypage.index.index', [
            'themes'          => $request->themes,
            "plugin_name"     => "index",
            "function"        => __FUNCTION__,
            "id"              => $user->id,
            "user"            => $user,
            "input_cols"      => $input_cols,
            "users_columns"   => $users_columns,
        ]);
    }
}
