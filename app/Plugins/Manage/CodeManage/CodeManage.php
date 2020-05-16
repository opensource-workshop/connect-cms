<?php

namespace App\Plugins\Manage\CodeManage;

use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;

use App\Models\Common\Codes;
use App\Models\Common\CodesSearches;
use App\Models\Common\CodesHelpMessages;
use App\Models\Core\Configs;
use App\Models\Core\Plugins;

use App\Plugins\Manage\ManagePluginBase;

use Log;

//use DB;

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
     * 権限定義
     */
    public function declareRole()
    {
        // 権限チェックテーブル
        $role_ckeck_table = array();
        // コード一覧
        $role_ckeck_table["index"]              = array('admin_site');
        $role_ckeck_table["regist"]             = array('admin_site');
        $role_ckeck_table["store"]              = array('admin_site');
        $role_ckeck_table["edit"]               = array('admin_site');
        $role_ckeck_table["update"]             = array('admin_site');
        $role_ckeck_table["destroy"]            = array('admin_site');

        // (コード一覧)表示設定
        $role_ckeck_table["display"]            = array('admin_site');
        $role_ckeck_table["displayStore"]       = array('admin_site');
        $role_ckeck_table["displayUpdate"]      = array('admin_site');

        // 検索条件
        $role_ckeck_table["searches"]           = array('admin_site');
        $role_ckeck_table["searchRegist"]       = array('admin_site');
        $role_ckeck_table["searchStore"]        = array('admin_site');
        $role_ckeck_table["searchEdit"]         = array('admin_site');
        $role_ckeck_table["searchUpdate"]       = array('admin_site');
        $role_ckeck_table["searchDestroy"]      = array('admin_site');

        // 注釈設定
        $role_ckeck_table["helpMessages"]       = array('admin_site');
        $role_ckeck_table["helpMessageRegist"]  = array('admin_site');
        $role_ckeck_table["helpMessageStore"]   = array('admin_site');
        $role_ckeck_table["helpMessageEdit"]    = array('admin_site');
        $role_ckeck_table["helpMessageUpdate"]  = array('admin_site');
        $role_ckeck_table["helpMessageDestroy"] = array('admin_site');

        return $role_ckeck_table;
    }

    /**
     * ページ初期表示
     *
     * @return view
     */
    public function index($request, $page_id = null)
    {
        // コード管理データの取得
        $codes_query = Codes::query();
        $codes_query->select(
            'codes.*',
            'buckets.bucket_name',
            'plugins.plugin_name_full',
            'codes_help_messages.name as codes_help_messages_name'
        );
        $codes_query->leftJoin('buckets', 'buckets.id', '=', 'codes.buckets_id')
                    ->leftJoin('plugins', 'plugins.plugin_name', '=', 'codes.plugin_name')
                    ->leftJoin('codes_help_messages', 'codes_help_messages.alias_key', '=', 'codes.codes_help_messages_alias_key');
        $codes_query->orderBy('plugin_name')
                    ->orderBy('buckets_id')
                    ->orderBy('prefix')
                    ->orderBy('type_code1')
                    ->orderBy('type_code2')
                    ->orderBy('type_code3')
                    ->orderBy('type_code4')
                    ->orderBy('type_code5')
                    ->orderBy('display_sequence');

        // $search_words = $request->input('search_words', '入退室');
        // 入力された検索ワード
        $search_words = $request->input('search_words');
        // 検索ワードをパースする
        $search_words_array = $this->parseSearchWords($search_words);
        // var_dump($search_words, $search_words_array);

        // 検索するdb項目
        $search_db_colums = [
            'plugin_name' => 'codes.plugin_name',
            'plugin_name_full' => 'plugins.plugin_name_full',
            'bucket_name' => 'buckets.bucket_name',
            'buckets_id' => 'codes.buckets_id',
            'prefix' => 'codes.prefix',
            'type_name' => 'codes.type_name',
            'type_code1' => 'codes.type_code1',
            'type_code2' => 'codes.type_code2',
            'type_code3' => 'codes.type_code3',
            'type_code4' => 'codes.type_code4',
            'type_code5' => 'codes.type_code5',
            'code' => 'codes.code',
            'value' => 'codes.value',
            'additional1' => 'codes.additional1',
            'additional2' => 'codes.additional2',
            'additional3' => 'codes.additional3',
            'additional4' => 'codes.additional4',
            'additional5' => 'codes.additional5',
            'display_sequence' => 'codes.display_sequence',
        ];

        // $codes_query->orWhere('codes.plugin_name', 'like', '%入退室%');
        // $codes_query->orWhere('plugins.plugin_name_full', 'like', '%入退室%');

        // 入力の検索条件ありのみ ループしてくれる
        foreach ($search_words_array as $search_word) {
            if (strpos($search_word, '=') === false) {
                // search_wordのなかに'='が含まれていない場合

                // 複数単語の検索に対応
                // 部分一致検索 ex) and (`codes.plugin_name` like "%aaa%" or `codes.plugin_name` like "%bbb%" or `codes.plugin_name` like "%ccc%")
                $codes_query->where(function ($query) use ($search_word, $search_db_colums) {
                    foreach ($search_db_colums as $search_db_colum) {
                        $query->orwhere($search_db_colum, 'like', '%' . $search_word . '%');
                    }
                });
            } else {
                // = 含む場合、 <dbカラム名>=<value>形式
                //$search_word = 'plugin_name_full=111';
                //$search_word = 'plugin_name_full=';
                $search_word_including_equals = explode('=', $search_word);
                $search_word_db_colum = $search_word_including_equals[0];
                $search_word_value = $search_word_including_equals[1];

                // 入力されたdbカラム名は、検索対象のものだけ含める。それ以外は検索に含めない
                // 完全一致検索 ex) and `codes.plugin_name` = "aaa"
                // DB項目配列のkeyに、入力したDBカラムがあるか
                if (array_key_exists($search_word_db_colum, $search_db_colums)) {
                    // DB項目配列のkeyから、<DB.カラム名>取得して検索条件追加
                    $codes_query->where($search_db_colums[$search_word_db_colum], $search_word_value);
                }
            }
        }

        // 確認したいSQLの前にこれを仕込んで
        //DB::enableQueryLog();

        $codes = $codes_query->paginate(10);

        // dumpする
        //Log::debug(var_export(DB::getQueryLog(), true));


        // Configsから一覧表示設定の取得
        $config = $this->getConfigCodeListDisplayColums();
        //var_dump($config);

        // 記録した検索条件取得
        $codes_searches = CodesSearches::orderBy('display_sequence')->get();

        // [TODO] ページネーションの表示ページ数を保持するための暫定対応
        $paginate_page = $request->get('page', 1);

        // 管理画面プラグインの戻り値の返し方
        // view 関数の第一引数に画面ファイルのパス、第二引数に画面に渡したいデータを名前付き配列で渡し、その結果のHTML。
        return view('plugins.manage.code.code', [
            "function"      => __FUNCTION__,
            "plugin_name"   => "code",
            "codes"         => $codes,
            "codes_searches"  => $codes_searches,
            "config"        => $config,
            "search_words"  => $search_words,
            "paginate_page" => $paginate_page,
            // "page" => 1,
        ]);
    }

    /**
     * 検索ワードのパース
     */
    private function parseSearchWords($search_words)
    {
        // --- debug
        // $search_words = " apple,apple bear \"Tom, Cruise\" or 'Mickey Mouse' another  word";
        // $search_words = " あああ いいい ううう　ううう \"えええ えええ\" or 'おおお おおお' かか  きき　";
        // $search_words = " code=1 type_name=学校 'type_code1=sch ool'";
        // $search_words = "";
        // $search_words = null;

        // 正規表現の図) https://regexper.com/#%2F%5B%5Cs%2C%5D*%5C%5C%5C%22%28%5B%5E%5C%5C%5C%22%5D%2B%29%5C%5C%5C%22%5B%5Cs%2C%5D*%7C%22%20.%20%22%5B%5Cs%2C%5D*'%28%5B%5E'%5D%2B%29'%5B%5Cs%2C%5D*%7C%22%20.%20%22%5B%5Cs%2C%5D%2B%2F
        // preg_split) https://www.php.net/manual/ja/function.preg-split.php
        //   PREG_SPLIT_NO_EMPTY: このフラグを設定すると、空文字列でないものだけが preg_split() により返されます。
        //   PREG_SPLIT_DELIM_CAPTURE: このフラグを設定すると、文字列分割用のパターン中の カッコ'()'によるサブパターンでキャプチャされた値も同時に返されます。
        //                             -> 正規表現にカッコ'()'でサブ抽出ができるようになる
        //
        //   ・半角空白orカンマ(' ' or ,)でパースして配列を戻す
        //   ・半角空白oとカンマ有りでも、''か""で囲めば単語として抽出する
        //     ・前後の空白あり, 空白重複は取り除かれる
        //     ・重複ワードはそのまま, 全角空白はそのまま, %はそのまま
        //     ・null, "" でもarray空が戻ってきてくれる
        //     ・日本語OK
        $search_words_array = preg_split(
            "/[\s,]*\\\"([^\\\"]+)\\\"[\s,]*|" . "[\s,]*'([^']+)'[\s,]*|" . "[\s,]+/",
            $search_words,
            0,     // -1|0=無制限(-1=default)
            PREG_SPLIT_NO_EMPTY | PREG_SPLIT_DELIM_CAPTURE
        );
        // print_r($search_words_array);

        if (preg_last_error() != PREG_NO_ERROR) {
            // エラーならdebug log出力
            // copy) https://www.php.net/manual/ja/function.preg-last-error.php#114105
            //   In PHP 5.5 and above, getting the error message is as simple as:
            $error_message = array_flip(get_defined_constants(true)['pcre'])[preg_last_error()];
            Log::debug('[' . __METHOD__ . '] ' . __FILE__ . ' (line ' . __LINE__ . '):' . $error_message);
        }

        return $search_words_array;
    }

    /**
     * コード登録画面表示
     *
     * @return view
     */
    public function regist($request, $id = null, $errors = array())
    {
        return $this->edit($request, $id, 'regist', $errors);
    }

    /**
     * コード登録処理
     */
    public function store($request)
    {
        return $this->update($request, null, 'regist');
    }

    /**
     * コード変更画面表示
     *
     * @return view
     */
    public function edit($request, $id = null, $function = null, $errors = array())
    {
        // セッション初期化などのLaravel 処理。これを書かないとold()が機能しなかった。
        $request->flash();

        if ($id) {
            // ID で1件取得, leftjoinするとcode.idをセットしてくれないので、個別にget
            $code = Codes::where('id', $id)->first();
        } else {
            // ユーザデータの空枠
            $code = new Codes();
        }

        // 入力の注釈設定キー、なければDBの注釈設定キー（更新時の初期表示）から取得
        $codes_help_messages_alias_key = $request->input('codes_help_messages_alias_key', $code->codes_help_messages_alias_key);
        if ($codes_help_messages_alias_key) {
            // 注釈設定取得
            $codes_help_message = CodesHelpMessages::where('alias_key', $codes_help_messages_alias_key)->first();
            // var_dump($codes_help_messages_alias_key, $codes_help_message);
        } else {
            // 注釈設定の空枠
            $codes_help_message = new CodesHelpMessages();
        }

        // プラグイン一覧の取得
        $plugins = Plugins::orderBy('display_sequence')->get();
        // var_dump($plugins);

        // 注釈設定一覧の取得
        $codes_help_messages_all = CodesHelpMessages::orderBy('display_sequence')->get();

        // [TODO] ページネーションの表示ページ数を保持するための暫定対応
        $paginate_page = $request->get('page', 1);

        // 入力された検索ワード
        $search_words = $request->input('search_words');

        if (is_null($function)) {
            $function = 'edit';
        }

        return view('plugins.manage.code.edit', [
            // "function" => __FUNCTION__,
            "function" => $function,
            "plugin_name" => "code",
            "plugins" => $plugins,
            "code" => $code,
            "codes_help_message" => $codes_help_message,
            "codes_help_messages_all" => $codes_help_messages_all,
            'errors' => $errors,
            'search_words' => $search_words,
            "paginate_page" => $paginate_page,
        ]);
    }

    /**
     * コード更新処理
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
        $codes->codes_help_messages_alias_key = $request->codes_help_messages_alias_key;
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

        // 入力された検索条件
        $search_words = $request->input('search_words');

        // 一覧画面に戻る
        // return redirect("/manage/code");
        return redirect("/manage/code?page=$page&search_words=$search_words");
    }

    /**
     * コード削除処理
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
     * (コード一覧)表示設定 画面表示
     *
     * @return view
     */
    public function display($request)
    {
        // Configsから一覧表示設定の取得
        $config = $this->getConfigCodeListDisplayColums();

        // 管理画面プラグインの戻り値の返し方
        // view 関数の第一引数に画面ファイルのパス、第二引数に画面に渡したいデータを名前付き配列で渡し、その結果のHTML。
        return view('plugins.manage.code.display', [
            "function"    => __FUNCTION__,
            "plugin_name" => "code",
            "config"      => $config,
        ]);
    }

    /**
     * Configsから一覧表示設定の取得
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
     * (コード一覧)表示設定 登録処理
     */
    public function displayStore($request)
    {
        return $this->displayUpdate($request, null);
    }

    /**
     * (コード一覧)表示設定 更新処理
     */
    public function displayUpdate($request, $id)
    {
        if ($id) {
            // 更新

            $configs = Configs::find($id);

            if (! $configs) {
                // 更新時にデータなしは、基本ありえない
                Log::debug('[' . __METHOD__ . '] ' . __FILE__ . ' (line ' . __LINE__ . '):更新時にデータなしは、基本ありえない。id=' . $id);
                // 一覧画面に戻る
                return redirect("/manage/code?page=1");
            } elseif ($configs->name != 'code_list_display_colums') {
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

    /**
     * 検索条件一覧 初期表示
     *
     * @return view
     */
    public function searches($request, $page_id = null)
    {
        // コード検索条件取得
        $codes_searches = CodesSearches::orderBy('display_sequence')->paginate(10);

        // [TODO] ページネーションの表示ページ数を保持するための暫定対応
        $paginate_page = $request->get('page', 1);

        // 管理画面プラグインの戻り値の返し方
        // view 関数の第一引数に画面ファイルのパス、第二引数に画面に渡したいデータを名前付き配列で渡し、その結果のHTML。
        return view('plugins.manage.code.code_search', [
            "function"      => __FUNCTION__,
            "plugin_name"   => "code",
            "codes_searches"  => $codes_searches,
            "paginate_page" => $paginate_page,
            // "page" => 1,
        ]);
    }

    /**
     * 検索条件 登録画面表示
     *
     * @return view
     */
    public function searchRegist($request, $id = null, $errors = array())
    {
        return $this->searchEdit($request, $id, 'searchRegist', $errors);
    }

    /**
     * 検索条件 登録処理
     */
    public function searchStore($request)
    {
        return $this->searchUpdate($request, null, 'searchRegist');
    }

    /**
     * 検索条件 変更画面表示
     *
     * @return view
     */
    public function searchEdit($request, $id = null, $function = null, $errors = array())
    {
        // セッション初期化などのLaravel 処理。これを書かないとold()が機能しなかった。
        $request->flash();

        if ($id) {
            // ID で1件取得
            $codes_search = CodesSearches::where('id', $id)->first();
        } else {
            // ユーザデータの空枠
            $codes_search = new CodesSearches();
        }

        // [TODO] ページネーションの表示ページ数を保持するための暫定対応
        $paginate_page = $request->get('page', 1);

        if (is_null($function)) {
            $function = 'searchEdit';
        }

        return view('plugins.manage.code.code_search_edit', [
            // "function" => __FUNCTION__,
            "function" => $function,
            "plugin_name" => "code",
            "codes_search" => $codes_search,
            'errors' => $errors,
            "paginate_page" => $paginate_page,
        ]);
    }

    /**
     * 検索条件 更新処理
     */
    public function searchUpdate($request, $id, $function = 'searchEdit')
    {
        // 項目のエラーチェック
        $validator = Validator::make($request->all(), [
            'name' => ['required'],
            'search_words' => ['required'],
        ]);
        $validator->setAttributeNames([
            'name' => '検索ラベル名',
            'search_words' => '検索条件',
        ]);

        // エラーがあった場合は入力画面に戻る。
        if ($validator->fails()) {
            return $this->searchEdit($request, $id, $function, $validator->errors());
        }

        if ($id) {
            // 更新
            $codes_searches = CodesSearches::find($id);
        } else {
            // 登録
            $codes_searches = new CodesSearches();
        }
        $codes_searches->name                 = $request->name;
        $codes_searches->search_words         = $request->search_words;
        $codes_searches->display_sequence     = (isset($request->display_sequence) ? (int)$request->display_sequence : 0);
        $codes_searches->save();


        $page = $request->get('page', 1);

        // 一覧画面に戻る
        // return redirect("/manage/code");
        return redirect("/manage/code/searches?page=$page");
    }

    /**
     * 検索条件 削除処理
     */
    public function searchDestroy($request, $id)
    {
        CodesSearches::destroy($id);

        // コード一覧画面に戻る
        // return redirect("/manage/code");
        $page = $request->get('page', 1);
        return redirect("/manage/code/searches?page=$page");
    }

    /**
     * 注釈一覧 初期表示
     *
     * @return view
     */
    public function helpMessages($request, $page_id = null)
    {
        // コード注釈取得
        $codes_help_messages = CodesHelpMessages::orderBy('display_sequence')->paginate(10);

        // [TODO] ページネーションの表示ページ数を保持するための暫定対応
        $paginate_page = $request->get('page', 1);

        // 管理画面プラグインの戻り値の返し方
        // view 関数の第一引数に画面ファイルのパス、第二引数に画面に渡したいデータを名前付き配列で渡し、その結果のHTML。
        return view('plugins.manage.code.help_message', [
            "function"      => __FUNCTION__,
            "plugin_name"   => "code",
            "codes_help_messages"  => $codes_help_messages,
            "paginate_page" => $paginate_page,
            // "page" => 1,
        ]);
    }

    /**
     * 注釈 登録画面表示
     *
     * @return view
     */
    public function helpMessageRegist($request, $id = null, $errors = array())
    {
        return $this->helpMessageEdit($request, $id, 'helpMessageRegist', $errors);
    }

    /**
     * 注釈 登録処理
     */
    public function helpMessageStore($request)
    {
        return $this->helpMessageUpdate($request, null, 'helpMessageRegist');
    }

    /**
     * 注釈 変更画面表示
     *
     * @return view
     */
    public function helpMessageEdit($request, $id = null, $function = null, $errors = array())
    {
        // セッション初期化などのLaravel 処理。これを書かないとold()が機能しなかった。
        $request->flash();

        if ($id) {
            // ID で1件取得
            $codes_help_message = CodesHelpMessages::where('id', $id)->first();
        } else {
            // ユーザデータの空枠
            $codes_help_message = new CodesHelpMessages();
        }

        // [TODO] ページネーションの表示ページ数を保持するための暫定対応
        $paginate_page = $request->get('page', 1);

        if (is_null($function)) {
            $function = 'helpMessageEdit';
        }

        return view('plugins.manage.code.help_message_edit', [
            // "function" => __FUNCTION__,
            "function" => $function,
            "plugin_name" => "code",
            "codes_help_message" => $codes_help_message,
            'errors' => $errors,
            "paginate_page" => $paginate_page,
        ]);
    }

    /**
     * 注釈 更新処理
     */
    public function helpMessageUpdate($request, $id, $function = 'helpMessageEdit')
    {
        // 項目のエラーチェック
        $validator = Validator::make($request->all(), [
            'alias_key' => [
                'required',
                Rule::unique('codes_help_messages')->ignore($id),
            ],
            'name' => ['required'],
        ]);
        $validator->setAttributeNames([
            'alias_key' => '注釈キー',
            'name' => '注釈名',
        ]);

        // エラーがあった場合は入力画面に戻る。
        if ($validator->fails()) {
            return $this->helpMessageEdit($request, $id, $function, $validator->errors());
        }

        if ($id) {
            // 更新
            $codes_help_messages = CodesHelpMessages::find($id);
        } else {
            // 登録
            $codes_help_messages = new CodesHelpMessages();
        }
        $codes_help_messages->alias_key                         = $request->alias_key;
        $codes_help_messages->name                              = $request->name;

        $codes_help_messages->codes_help_messages_alias_key_help_message = $request->codes_help_messages_alias_key_help_message;
        $codes_help_messages->plugin_name_help_message          = $request->plugin_name_help_message;
        $codes_help_messages->buckets_id_help_message           = $request->buckets_id_help_message;
        $codes_help_messages->prefix_help_message               = $request->prefix_help_message;

        $codes_help_messages->type_name_help_message            = $request->type_name_help_message;
        $codes_help_messages->type_code1_help_message           = $request->type_code1_help_message;
        $codes_help_messages->type_code2_help_message           = $request->type_code2_help_message;
        $codes_help_messages->type_code3_help_message           = $request->type_code3_help_message;
        $codes_help_messages->type_code4_help_message           = $request->type_code4_help_message;
        $codes_help_messages->type_code5_help_message           = $request->type_code5_help_message;
        $codes_help_messages->code_help_message                 = $request->code_help_message;
        $codes_help_messages->value_help_message                = $request->value_help_message;
        $codes_help_messages->additional1_help_message          = $request->additional1_help_message;
        $codes_help_messages->additional2_help_message          = $request->additional2_help_message;
        $codes_help_messages->additional3_help_message          = $request->additional3_help_message;
        $codes_help_messages->additional4_help_message          = $request->additional4_help_message;
        $codes_help_messages->additional5_help_message          = $request->additional5_help_message;
        $codes_help_messages->display_sequence_help_message     = $request->display_sequence_help_message;
        $codes_help_messages->display_sequence                  = (isset($request->display_sequence) ? (int)$request->display_sequence : 0);
        $codes_help_messages->save();


        $page = $request->get('page', 1);

        // 一覧画面に戻る
        // return redirect("/manage/code");
        return redirect("/manage/code/helpMessages?page=$page");
    }

    /**
     * 注釈 削除処理
     */
    public function helpMessageDestroy($request, $id)
    {
        CodesHelpMessages::destroy($id);

        // コード一覧画面に戻る
        // return redirect("/manage/code");
        $page = $request->get('page', 1);
        return redirect("/manage/code/helpMessages?page=$page");
    }
}
