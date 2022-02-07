<?php

namespace App\Plugins\Manage\NumberManage;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;

use File;
use DB;

use App\Models\Common\Numbers;

use App\Plugins\Manage\ManagePluginBase;

/**
 * 連番管理クラス
 *
 * @author 永原　篤 <nagahara@opensource-workshop.jp>
 * @copyright OpenSource-WorkShop Co.,Ltd. All Rights Reserved
 * @category 連番管理
 * @package Contoroller
 * @plugin_title 連番管理
 * @plugin_desc 連番管理に関する機能が集まった管理機能です。
 */
class NumberManage extends ManagePluginBase
{
    /**
     *  権限定義
     */
    public function declareRole()
    {
        // 権限チェックテーブル
        $role_ckeck_table = array();
        $role_ckeck_table["index"]             = array('admin_site');
        $role_ckeck_table["update"]            = array('admin_site');
        $role_ckeck_table["clearSerialNumber"] = array('admin_site');
        return $role_ckeck_table;
    }

    /**
     *  ページ初期表示
     *
     * @return view
     * @method_title 連番一覧
     * @method_desc システム上で採番されている連番を一覧で確認できます。
     * @method_detail 連番をクリアすることもできます。
     */
    public function index($request, $page_id = null, $errors = array())
    {
        // 現在の連番管理データの取得
        $numbers = Numbers::select(
            'numbers.*',
            'buckets.bucket_name',
            'plugins.plugin_name_full'
        )
                          ->leftJoin('buckets', 'buckets.id', '=', 'numbers.buckets_id')
                          ->leftJoin('plugins', 'plugins.plugin_name', '=', 'numbers.plugin_name')
                          ->orderBy('plugin_name')
                          ->orderBy('buckets_id')
                          ->orderBy('prefix')
                          ->get();

        // 管理画面プラグインの戻り値の返し方
        // view 関数の第一引数に画面ファイルのパス、第二引数に画面に渡したいデータを名前付き配列で渡し、その結果のHTML。
        return view('plugins.manage.number.number', [
            "function"    => __FUNCTION__,
            "plugin_name" => "number",
            "numbers"     => $numbers,
        ]);
    }

    /**
     *  更新
     */
    public function clearSerialNumber($request, $id)
    {
        // httpメソッド確認
        if (!$request->isMethod('post')) {
            abort(403, '権限がありません。');
        }

        // 連番クリア
        Numbers::where('id', $id)->update(['serial_number' => 0]);

        return redirect("/manage/number");
    }
}
