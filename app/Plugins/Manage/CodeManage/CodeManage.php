<?php

namespace App\Plugins\Manage\CodeManage;

use Illuminate\Support\Facades\Validator;

use App\Models\Common\Codes;
use App\Models\Core\Configs;
use App\Models\Core\Plugins;

use App\Plugins\Manage\ManagePluginBase;

use Log;

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
        $role_ckeck_table["display"]            = array('admin_site');
        $role_ckeck_table["displayUpdate"]      = array('admin_site');
        return $role_ckeck_table;
    }

    /**
     *  ページ初期表示
     *
     * @return view
     */
    public function index($request, $page_id = null, $errors = array())
    {
        // コード管理データの取得
        $codes_query = Codes::query();
        $codes_query->select(
            'codes.*',
            'buckets.bucket_name',
            'plugins.plugin_name_full'
        );
        $codes_query->leftJoin('buckets', 'buckets.id', '=', 'codes.buckets_id')
                    ->leftJoin('plugins', 'plugins.plugin_name', '=', 'codes.plugin_name');
        $codes_query->orderBy('plugin_name')
                    ->orderBy('buckets_id')
                    ->orderBy('prefix')
                    ->orderBy('type_code1')
                    ->orderBy('type_code2')
                    ->orderBy('type_code3')
                    ->orderBy('type_code4')
                    ->orderBy('type_code5')
                    ->orderBy('display_sequence');

        // $q = $request->input('q', '入退室');
        // q = 入力された検索条件
        $q = $request->input('q');
        if ($q) {
            // $codes_query->where('codes.plugin_name', 'like', '%入退室%');
            // $codes_query->where('plugins.plugin_name_full', 'like', '%入退室%');
            $codes_query->orWhere('codes.plugin_name', 'like', '%' . $q . '%');
            $codes_query->orWhere('plugins.plugin_name_full', 'like', '%' . $q . '%');
            $codes_query->orWhere('buckets.bucket_name', 'like', '%' . $q . '%');
            $codes_query->orWhere('codes.buckets_id', 'like', '%' . $q . '%');
            $codes_query->orWhere('codes.prefix', 'like', '%' . $q . '%');
            $codes_query->orWhere('codes.type_name', 'like', '%' . $q . '%');
            $codes_query->orWhere('codes.type_code1', 'like', '%' . $q . '%');
            $codes_query->orWhere('codes.type_code2', 'like', '%' . $q . '%');
            $codes_query->orWhere('codes.type_code3', 'like', '%' . $q . '%');
            $codes_query->orWhere('codes.type_code4', 'like', '%' . $q . '%');
            $codes_query->orWhere('codes.type_code5', 'like', '%' . $q . '%');
            $codes_query->orWhere('codes.code', 'like', '%' . $q . '%');
            $codes_query->orWhere('codes.value', 'like', '%' . $q . '%');
            $codes_query->orWhere('codes.additional1', 'like', '%' . $q . '%');
            $codes_query->orWhere('codes.additional2', 'like', '%' . $q . '%');
            $codes_query->orWhere('codes.additional3', 'like', '%' . $q . '%');
            $codes_query->orWhere('codes.additional4', 'like', '%' . $q . '%');
            $codes_query->orWhere('codes.additional5', 'like', '%' . $q . '%');
            $codes_query->orWhere('codes.display_sequence', 'like', '%' . $q . '%');
        }

        $codes = $codes_query->paginate(10);

        // Configsから一覧表示設定の取得
        $config = $this->getConfigCodeListDisplayColums();

        // [TODO] ページネーションの表示ページ数を保持するための暫定対応
        $paginate_page = $request->get('page', 1);

        // 管理画面プラグインの戻り値の返し方
        // view 関数の第一引数に画面ファイルのパス、第二引数に画面に渡したいデータを名前付き配列で渡し、その結果のHTML。
        return view('plugins.manage.code.code',[
            "function"    => __FUNCTION__,
            "plugin_name" => "code",
            "codes"       => $codes,
            "config"      => $config,
            "q"           => $q,
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
        return $this->edit($request, $id, 'regist', $errors);
    }

    /**
     *  コード登録処理
     */
    public function store($request)
    {
        return $this->update($request, null, 'regist');
    }

    /**
     *  コード変更画面表示
     *
     * @return view
     */
    public function edit($request, $id = null, $function = 'edit', $errors = array())
    {
        // セッション初期化などのLaravel 処理。これを書かないとold()が機能しなかった。
        $request->flash();

        if ($id) {
            // ID で1件取得
            $code = Codes::where('id', $id)->first();
        } else {
            // ユーザデータの空枠
            $code = new Codes();
        }

        // プラグイン一覧の取得
        $plugins = Plugins::orderBy('display_sequence')->get();
        // var_dump($plugins);

        // [TODO] ページネーションの表示ページ数を保持するための暫定対応
        $paginate_page = $request->get('page', 1);

        // q = 入力された検索条件
        $q = $request->input('q');

        return view('plugins.manage.code.regist',[
            // "function" => __FUNCTION__,
            "function" => $function,
            "plugin_name" => "code",
            "plugins" => $plugins,
            "code" => $code,
            'errors' => $errors,
            'q' => $q,
            "paginate_page" => $paginate_page,
        ]);
    }

    /**
     *  コード更新処理
     */
    public function update($request, $id, $function = 'edit')
    {
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
            return $this->edit($request, $id, $function, $validator->errors());
        }

        if ($id) {
            // 更新
            $codes = Codes::find($id);
        } else {
            // 登録
            $codes = new Codes();
        }
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


        $page = $request->get('page', 1);

        // q = 入力された検索条件
        $q = $request->input('q');

        // 一覧画面に戻る
        // return redirect("/manage/code");
        return redirect("/manage/code?page=$page&q=$q");
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

    /**
     *  一覧表示設定画面表示
     *
     * @return view
     */
    public function display($request)
    {
        // Configsから一覧表示設定の取得
        $config = $this->getConfigCodeListDisplayColums();

        // 管理画面プラグインの戻り値の返し方
        // view 関数の第一引数に画面ファイルのパス、第二引数に画面に渡したいデータを名前付き配列で渡し、その結果のHTML。
        return view('plugins.manage.code.display',[
            "function"    => __FUNCTION__,
            "plugin_name" => "code",
            "config"      => $config,
        ]);
    }

    /**
     *  Configsから一覧表示設定の取得
     */
    private function getConfigCodeListDisplayColums()
    {
        // 一覧表示設定の取得
        $config = Configs::where('category', 'code_manage')
                        ->where('name', 'code_list_display_colums')
                        ->first();

        if ($config) {
            // 基本、マイグレーションで初期値を設定するため、データは必ずある想定
            $config->value_array = explode('|', $config->value);
        }

        return $config;
    }

    /**
     *  一覧表示設定 更新処理
     */
    public function displayUpdate($request, $id = null)
    {
        if ($id) {
            // 更新

            $configs = Configs::find($id);

            if (! $configs) {
                // 更新時にデータなしは、基本ありえない
                Log::debug('[' . __METHOD__ . '] ' . __FILE__ . ' (line ' . __LINE__ . '):更新時にデータなしは、基本ありえない。id=' . $id);
                // 一覧画面に戻る
                return redirect("/manage/code?page=1");

            } elseif ($configs->name != 'code_list_display_colums')  {
                // code_list_display_colums以外のデータは、基本ありえない
                Log::debug('[' . __METHOD__ . '] ' . __FILE__ . ' (line ' . __LINE__ . '):更新時に取得したデータがcode_list_display_colums以外は、基本ありえない。id=' . $configs->id . ' name=' . $configs->name);
                // 一覧画面に戻る
                return redirect("/manage/code?page=1");
            }
        } else {
            // 登録（初回登録時のみ）

            // Configsから一覧表示設定の取得
            $config = $this->getConfigCodeListDisplayColums();

            if ($config) {
                // 登録時にデータありは、基本ありえない
                Log::debug('[' . __METHOD__ . '] ' . __FILE__ . ' (line ' . __LINE__ . '):登録時にデータありは、基本ありえない');
                // 一覧画面に戻る
                return redirect("/manage/code?page=1");
            }

            $configs = new Configs();
            $configs->category = 'code_manage';
            $configs->name = 'code_list_display_colums';
        }

        // 値があれば配列に直してセット
        if ($request->code_list_display_colums) {
            // 必ずplugin_nameをセットするため、ここを通る
            $value = implode('|', $request->code_list_display_colums);
        } else {
            $value = null;
        }
        $configs->value = $value;
        $configs->save();

        // Log::debug('[' . __METHOD__ . '] ' . __FILE__ . ' (line ' . __LINE__ . ')');
        // Log::debug(var_export($request->code_list_display_colums, true));
        // Log::debug(var_export($_POST, true));

        // 一覧画面に戻る
        // return redirect("/manage/code");
        return redirect("/manage/code?page=1");
    }

}
