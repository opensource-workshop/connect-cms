<?php

namespace App\Plugins\Manage\MessageManage;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;

use File;
use DB;

use App\Models\Core\Configs;

use App\Plugins\Manage\ManagePluginBase;

/**
 * メッセージ管理クラス
 *
 * @author 井上 雅人 <inoue@opensource-workshop.jp / masamasamasato0216@gmail.com>
 * @copyright OpenSource-WorkShop Co.,Ltd. All Rights Reserved
 * @category メッセージ管理
 * @package Contoroller
 */
class MessageManage extends ManagePluginBase
{
    /**
     *  権限定義
     */
    public function declareRole()
    {
        // 権限チェックテーブル
        $role_ckeck_table = array();
        $role_ckeck_table["index"]  = array('admin_system');
        $role_ckeck_table["update"] = array('admin_system');
        return $role_ckeck_table;
    }

    /**
     *  ページ初期表示
     *
     * @return view
     */
    public function index($request)
    {
        // Config データの取得
        $configs = Configs::get();

        // Config データの変換
        $configs_array = array();
        foreach ($configs as $config) {
            $configs_array[$config->name] = $config->value;
        }

        // 管理画面プラグインの戻り値の返し方
        // view 関数の第一引数に画面ファイルのパス、第二引数に画面に渡したいデータを名前付き配列で渡し、その結果のHTML。
        return view('plugins.manage.message.message', [
            "function" => __FUNCTION__,
            "plugin_name" => "message",
            "configs" => $configs_array,
        ]);
    }

    /**
     *  更新
     */
    public function update($request)
    {
        // httpメソッド確認
        if (!$request->isMethod('post')) {
            abort(403, '権限がありません。');
        }

        // --- 更新
        // 初回確認メッセージ（利用有無）
        $configs = Configs::updateOrCreate(
            ['name'     => 'message_first_show_type'],
            ['category' => 'message',
             'value'    => $request->message_first_show_type]
        );

        // 初回確認メッセージ（枠外クリック等の離脱許可）
        $configs = Configs::updateOrCreate(
            ['name'     => 'message_first_permission_type'],
            ['category' => 'message',
             'value'    => $request->message_first_permission_type]
        );

        // 初回確認メッセージ（メッセージ内容）
        $search = array('<script>','</script>');
        $replace = array('','');
        $configs = Configs::updateOrCreate(
            ['name'     => 'message_first_content'],
            ['category' => 'message',
             'value'    => str_replace($search, $replace, $request->message_first_content)]
        );

        // 初回確認メッセージ（ボタン名）
        $configs = Configs::updateOrCreate(
            ['name'     => 'message_first_button_name'],
            ['category' => 'message',
             'value'    => $request->message_first_button_name]
        );

        // 初回確認メッセージ（除外URL）
        $configs = Configs::updateOrCreate(
            ['name'     => 'message_first_exclued_url'],
            ['category' => 'message',
             'value'    => $request->message_first_exclued_url]
        );

        // ページ管理画面に戻る
        return redirect("/manage/message");
    }
}
