<?php

namespace App\Plugins\Manage\CodeManage;

//use Illuminate\Http\Request;
//use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;

//use File;
//use DB;

use App\Models\Common\Codes;
use App\Models\Core\Plugins;

use App\Plugins\Manage\ManagePluginBase;

// Connect-CMS 用設定データ
//use App\Traits\ConnectCommonTrait;

/**
 * コード管理クラス
 *
 * @author 牟田口 満 <mutaguchi@opensource-workshop.jp>
 * @copyright OpenSource-WorkShop Co.,Ltd. All Rights Reserved
 * @category コード管理
 * @package Contoroller
 */
class CodeManage extends ManagePluginBase
{
    // use ConnectCommonTrait;

    /**
     *  権限定義
     */
    public function declareRole()
    {
        // 権限チェックテーブル
        $role_ckeck_table = array();
        $role_ckeck_table["index"]              = array('admin_site');
        $role_ckeck_table["regist"]             = array('admin_site');
        $role_ckeck_table["store"]              = array('admin_site');
        $role_ckeck_table["edit"]               = array('admin_site');
        $role_ckeck_table["update"]             = array('admin_site');
        $role_ckeck_table["destroy"]            = array('admin_site');
        return $role_ckeck_table;
    }

    /**
     *  ページ初期表示
     *
     * @return view
     */
    public function index($request, $page_id = null, $errors = array())
    {
        // 現在の連番管理データの取得
        $codes = Codes::select('codes.*',
                                   'buckets.bucket_name',
                                   'plugins.plugin_name_full')
                          ->leftJoin('buckets', 'buckets.id', '=', 'codes.buckets_id')
                          ->leftJoin('plugins', 'plugins.plugin_name', '=', 'codes.plugin_name')
                          ->orderBy('plugin_name')
                          ->orderBy('buckets_id')
                          ->orderBy('prefix')
                          ->orderBy('type_code1')
                          ->orderBy('type_code2')
                          ->orderBy('type_code3')
                          ->orderBy('type_code4')
                          ->orderBy('type_code5')
                          ->orderBy('display_sequence')
                          //->orderBy('code')
                          ->paginate(10);

        // [TODO] ページネーションの表示ページ数を保持するための暫定対応
        $paginate_page = $request->get('page', 1);

        // 管理画面プラグインの戻り値の返し方
        // view 関数の第一引数に画面ファイルのパス、第二引数に画面に渡したいデータを名前付き配列で渡し、その結果のHTML。
        return view('plugins.manage.code.code',[
            "function"    => __FUNCTION__,
            "plugin_name" => "code",
            "codes"       => $codes,
            "paginate_page" => $paginate_page,
            // "page" => 1,
        ]);
    }

    /**
     *  コード登録画面表示
     *
     * @return view
     */
    public function regist($request, $id = null, $errors = array())
    {
        // ユーザデータの空枠
        $code = new Codes();

        // プラグイン一覧の取得
        // $plugins = $this->getPlugins();
        $plugins = Plugins::orderBy('display_sequence')->get();
        // var_dump($plugins);

        return view('plugins.manage.code.regist',[
            "function" => __FUNCTION__,
            "plugin_name" => "code",
            "plugins" => $plugins,
            "code" => $code,
            'errors' => $errors,
        ]);
    }

    /**
     *  コード登録処理
     */
    public function store($request)
    {
        // httpメソッド確認
        if (!$request->isMethod('post')) {
            abort(403, '権限がありません。');
        }

        // 項目のエラーチェック
        $validator = Validator::make($request->all(), [
            'code' => ['required'],
            'value' => ['required'],
        ]);
        $validator->setAttributeNames([
            'code' => 'コード',
            'value' => '値',
        ]);

        // エラーがあった場合は入力画面に戻る。
        if ($validator->fails()) {
            return ( $this->regist($request, null, $validator->errors()) );
        }

        // 登録
        $codes = new Codes();
        $codes->plugin_name          = $request->plugin_name;
        $codes->buckets_id           = $request->buckets_id;
        $codes->prefix               = $request->prefix;
        $codes->type_name            = $request->type_name;
        $codes->type_code1           = $request->type_code1;
        $codes->type_code2           = $request->type_code2;
        $codes->type_code3           = $request->type_code3;
        $codes->type_code4           = $request->type_code4;
        $codes->type_code5           = $request->type_code5;
        $codes->code                 = $request->code;
        $codes->value                = $request->value;
        $codes->additional1          = $request->additional1;
        $codes->additional2          = $request->additional2;
        $codes->additional3          = $request->additional3;
        $codes->additional4          = $request->additional4;
        $codes->additional5          = $request->additional5;
        $codes->display_sequence     = (isset($request->display_sequence) ? (int)$request->display_sequence : 0);
        $codes->save();

        // 一覧画面に戻る
        // return redirect("/manage/code");
        $page = $request->get('page', 1);
        return redirect("/manage/code?page=$page");
    }

    /**
     *  コード登録画面表示
     *
     * @return view
     */
    public function edit($request, $id = null, $errors = array())
    {
        // ID で1件取得
        $code = Codes::where('id', $id)->first();

        // プラグイン一覧の取得
        // $plugins = $this->getPlugins();
        $plugins = Plugins::orderBy('display_sequence')->get();
        // var_dump($plugins);

        // [TODO] ページネーションの表示ページ数を保持するための暫定対応
        $paginate_page = $request->get('page', 1);

        return view('plugins.manage.code.regist',[
            "function" => __FUNCTION__,
            "plugin_name" => "code",
            "plugins" => $plugins,
            "code" => $code,
            'errors' => $errors,
            "paginate_page" => $paginate_page,
        ]);
    }

    /**
     *  コード更新処理
     */
    public function update($request, $id)
    {
        // httpメソッド確認
        if (!$request->isMethod('post')) {
            abort(403, '権限がありません。');
        }

        // 項目のエラーチェック
        $validator = Validator::make($request->all(), [
            'code' => ['required'],
            'value' => ['required'],
        ]);
        $validator->setAttributeNames([
            'code' => 'コード',
            'value' => '値',
        ]);

        // エラーがあった場合は入力画面に戻る。
        if ($validator->fails()) {
            return ( $this->regist($request, $id, $validator->errors()) );
        }

        // 更新
        $codes = Codes::find($id);
        // Codes::where('id', $id)
        //     ->update(['name' => 'xxxx']);
        // $codes = new Codes();
        // $codes->id                   = $id;
        $codes->plugin_name          = $request->plugin_name;
        $codes->buckets_id           = $request->buckets_id;
        $codes->prefix               = $request->prefix;
        $codes->type_name            = $request->type_name;
        $codes->type_code1           = $request->type_code1;
        $codes->type_code2           = $request->type_code2;
        $codes->type_code3           = $request->type_code3;
        $codes->type_code4           = $request->type_code4;
        $codes->type_code5           = $request->type_code5;
        $codes->code                 = $request->code;
        $codes->value                = $request->value;
        $codes->additional1          = $request->additional1;
        $codes->additional2          = $request->additional2;
        $codes->additional3          = $request->additional3;
        $codes->additional4          = $request->additional4;
        $codes->additional5          = $request->additional5;
        $codes->display_sequence     = (isset($request->display_sequence) ? (int)$request->display_sequence : 0);
        $codes->save();

        // 一覧画面に戻る
        // return redirect("/manage/code");
        $page = $request->get('page', 1);
        return redirect("/manage/code?page=$page");
    }

    /**
     *  コード削除関数
     */
    public function destroy($request, $id)
    {
        Codes::destroy($id);

        // コード一覧画面に戻る
        // return redirect("/manage/code");
        $page = $request->get('page', 1);
        return redirect("/manage/code?page=$page");
    }
}
