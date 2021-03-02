<?php

namespace App\Plugins\Manage\CodeManage;

use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;

use App\Models\Common\Codes;
use App\Models\Common\CodesSearches;
use App\Models\Common\CodesHelpMessages;
use App\Models\Core\Configs;
use App\Models\Core\Plugins;

use App\Plugins\Manage\ManagePluginBase;

use Log;

use App\Utilities\Csv\CsvUtils;
use App\Utilities\String\StringUtils;

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

        // インポート
        $role_ckeck_table["import"]             = array('admin_site');
        $role_ckeck_table["uploadCsv"]          = array('admin_site');

        // ダウンロード（エクスポート）
        $role_ckeck_table["download"]           = array('admin_site');
        $role_ckeck_table["downloadCsv"]        = array('admin_site');
        $role_ckeck_table["downloadCsvFormat"]  = array('admin_site');

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
            'additional6' => 'codes.additional6',
            'additional7' => 'codes.additional7',
            'additional8' => 'codes.additional8',
            'additional9' => 'codes.additional9',
            'additional10' => 'codes.additional10',
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
            'buckets_id' => ['nullable', 'numeric'],
            'code' => ['required'],
            'value' => ['required'],
        ]);
        $validator->setAttributeNames([
            'buckets_id' => 'buckets_id',
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
        $codes->additional6          = $request->additional6;
        $codes->additional7          = $request->additional7;
        $codes->additional8          = $request->additional8;
        $codes->additional9          = $request->additional9;
        $codes->additional10         = $request->additional10;
        $codes->display_sequence     = isset($request->display_sequence) ? (int)$request->display_sequence : 0;
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
        $codes_help_messages->additional6_help_message          = $request->additional6_help_message;
        $codes_help_messages->additional7_help_message          = $request->additional7_help_message;
        $codes_help_messages->additional8_help_message          = $request->additional8_help_message;
        $codes_help_messages->additional9_help_message          = $request->additional9_help_message;
        $codes_help_messages->additional10_help_message          = $request->additional10_help_message;
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

    /**
     * インポート画面表示
     */
    public function import($request, $page_id = null)
    {
        // 管理画面プラグインの戻り値の返し方
        return view('plugins.manage.code.code_import', [
            "function"      => __FUNCTION__,
            "plugin_name"   => "code",
        ]);
    }

    /**
     * インポート
     */
    public function uploadCsv($request, $page_id = null)
    {
        // csv
        $rules = [
            'codes_csv'  => [
                'required',
                'file',
                'mimes:csv,txt', // mimesの都合上text/csvなのでtxtも許可が必要
                'mimetypes:text/plain',
            ],
        ];

        // 画面エラーチェック
        $validator = Validator::make($request->all(), $rules);
        $validator->setAttributeNames([
            'codes_csv'  => 'CSVファイル',
        ]);

        if ($validator->fails()) {
            // Log::debug(var_export($validator->errors(), true));
            // エラーと共に編集画面を呼び出す
            return redirect()->back()->withErrors($validator)->withInput();
        }


        // CSVファイル一時保存
        $path = $request->file('codes_csv')->store('tmp');
        // Log::debug(var_export(storage_path('app/') . $path, true));
        $csv_full_path = storage_path('app/') . $path;

        // ファイル拡張子取得
        $file_extension = $request->file('codes_csv')->getClientOriginalExtension();
        // 小文字に変換
        $file_extension = strtolower($file_extension);
        // Log::debug(var_export($file_extension, true));

        // 文字コード
        $character_code = $request->character_code;

        // 文字コード自動検出
        if ($character_code == \CsvCharacterCode::auto) {
            // 文字コードの自動検出(文字エンコーディングをsjis-win, UTF-8の順番で自動検出. 対象文字コード外の場合、false戻る)
            $character_code = CsvUtils::getCharacterCodeAuto($csv_full_path);
            if (!$character_code) {
                // 一時ファイルの削除
                $this->rmImportTmpFile($path);

                $error_msgs = "文字コードを自動検出できませんでした。CSVファイルの文字コードを " . \CsvCharacterCode::getSelectMembersDescription(\CsvCharacterCode::sjis_win) .
                            ", " . \CsvCharacterCode::getSelectMembersDescription(\CsvCharacterCode::utf_8) . " のいずれかに変更してください。";

                return redirect()->back()->withErrors(['codes_csv' => $error_msgs])->withInput();
            }
        }

        // 読み込み
        $fp = fopen($csv_full_path, 'r');
        // CSVファイル：Shift-JIS -> UTF-8変換時のみ
        if ($character_code == \CsvCharacterCode::sjis_win) {
            // ストリームフィルタ内で、Shift-JIS -> UTF-8変換
            $fp = CsvUtils::setStreamFilterRegisterSjisToUtf8($fp);
        }

        // 一行目（ヘッダ）
        $header_columns = fgetcsv($fp, 0, ',');
        // CSVファイル：UTF-8のみ
        if ($character_code == \CsvCharacterCode::utf_8) {
            // UTF-8のみBOMコードを取り除く
            $header_columns = CsvUtils::removeUtf8Bom($header_columns);
        }
        // dd($csv_full_path);
        // \Log::debug('$header_columns:'. var_export($header_columns, true));

        // カラムの取得
        $code_columns = \CodeColumn::getImportColumn();

        // ヘッダー項目のエラーチェック
        $error_msgs = $this->checkCsvHeader($header_columns, $code_columns);
        if (!empty($error_msgs)) {
            // 一時ファイルの削除
            fclose($fp);
            $this->rmImportTmpFile($path);

            return redirect()->back()->withErrors(['codes_csv' => $error_msgs])->withInput();
        }

        // データ項目のエラーチェック
        $error_msgs = $this->checkCvslines($fp, $code_columns);
        if (!empty($error_msgs)) {
            // 一時ファイルの削除
            fclose($fp);
            $this->rmImportTmpFile($path);

            return redirect()->back()->withErrors(['codes_csv' => $error_msgs])->withInput();
        }

        // // 一時ファイルの削除
        // fclose($fp);
        // $this->rmImportTmpFile($path);
        // dd('ここまで');

        // ファイルポインタの位置を先頭に戻す
        rewind($fp);

        // ヘッダー
        $header_columns = fgetcsv($fp, 0, ',');
        // CSVファイル：UTF-8のみ
        if ($character_code == \CsvCharacterCode::utf_8) {
            // UTF-8のみBOMコードを取り除く
            $header_columns = CsvUtils::removeUtf8Bom($header_columns);
        }

        // データ
        while (($csv_columns = fgetcsv($fp, 0, ',')) !== false) {
            // --- 入力値変換
            // Log::debug(var_export($csv_columns, true));

            // 入力値をトリム(preg_replace(/u)で置換. /u = UTF-8 として処理)
            $csv_columns = StringUtils::trimInput($csv_columns);

            // 配列の頭から要素(id)を取り除いて取得
            // CSVのデータ行の頭は、必ず固定項目のidの想定
            $codes_id = array_shift($csv_columns);
            // 空文字をnullに変換
            $codes_id = StringUtils::convertEmptyStringsToNull($codes_id);

            foreach ($csv_columns as $col => &$csv_column) {
                // 空文字をnullに変換
                $csv_column = StringUtils::convertEmptyStringsToNull($csv_column);
            }
            // Log::debug('$csv_columns:'. var_export($csv_columns, true));

            // 一時ファイルの削除
            // fclose($fp);
            // Storage::delete($path);
            // dd('ここまで' . $posted_at);

            if (empty($codes_id)) {
                // 登録
                $codes = new Codes();
            } else {
                // 更新
                // codes_idはバリデートでCodes存在チェック済みなので、必ずデータある想定
                $codes = Codes::where('id', $codes_id)->first();
            }

            $codes->plugin_name          = $csv_columns[0];
            $codes->codes_help_messages_alias_key = $csv_columns[1];
            $codes->buckets_id           = $csv_columns[2];
            $codes->prefix               = $csv_columns[3];
            $codes->type_name            = $csv_columns[4];
            $codes->type_code1           = $csv_columns[5];
            $codes->type_code2           = $csv_columns[6];
            $codes->type_code3           = $csv_columns[7];
            $codes->type_code4           = $csv_columns[8];
            $codes->type_code5           = $csv_columns[9];
            $codes->code                 = $csv_columns[10];
            $codes->value                = $csv_columns[11];
            $codes->additional1          = $csv_columns[12];
            $codes->additional2          = $csv_columns[13];
            $codes->additional3          = $csv_columns[14];
            $codes->additional4          = $csv_columns[15];
            $codes->additional5          = $csv_columns[16];
            $codes->additional6          = $csv_columns[17];
            $codes->additional7          = $csv_columns[18];
            $codes->additional8          = $csv_columns[19];
            $codes->additional9          = $csv_columns[20];
            $codes->additional10         = $csv_columns[21];
            $codes->display_sequence     = isset($csv_columns[22]) ? (int)$csv_columns[22] : 0;
            $codes->save();
        }

        // 一時ファイルの削除
        fclose($fp);
        $this->rmImportTmpFile($path);

        // インポート画面に戻る
        return redirect("/manage/code/import")->with('flash_message', 'インポートしました。');
    }

    /**
     * CSVヘッダーチェック
     */
    private function checkCsvHeader($header_columns, $header_column_format)
    {
        if (empty($header_columns)) {
            return array("CSVファイルが空です。");
        }

        // 項目の不足チェック
        $shortness = array_diff($header_column_format, $header_columns);
        if (!empty($shortness)) {
            // Log::debug(var_export($header_column_format, true));
            // Log::debug(var_export($header_columns, true));
            return array("1行目に " . implode(",", $shortness) . " が不足しています。");
        }
        // 項目の不要チェック
        $excess = array_diff($header_columns, $header_column_format);
        if (!empty($excess)) {
            return array("1行目に " . implode(",", $excess) . " は不要です。");
        }

        return array();
    }

    /**
     * インポート時の一時ファイル削除
     */
    private function rmImportTmpFile($path)
    {
        // 一時ファイルの削除
        Storage::delete($path);
    }

    /**
     * CSVデータ行チェック
     */
    private function checkCvslines($fp, $code_columns)
    {
        $rules = [
            0 => ['nullable', 'numeric', 'exists:codes,id,deleted_at,NULL'],    // id
            1 => ['nullable', 'exists:plugins,plugin_name'],                    // プラグイン(英語)
            2 => ['nullable', 'exists:codes_help_messages,alias_key,deleted_at,NULL'],    // 注釈キー
            3 => ['nullable', 'numeric'],    // buckets_id
            4 => [],
            5 => [],
            6 => [],
            7 => [],
            8 => [],
            9 => [],
            10 => [],
            11 => ['required'],     // コード
            12 => ['required'],     // 値
        ];

        // ヘッダー行が1行目なので、2行目からデータ始まる
        $line_count = 2;
        $errors = [];

        while (($csv_columns = fgetcsv($fp, 0, ',')) !== false) {
            // 入力値をトリム (preg_replace(/u)で置換. /u = UTF-8 として処理)
            $csv_columns = StringUtils::trimInput($csv_columns);

            // バリデーション
            $validator = Validator::make($csv_columns, $rules);
            // Log::debug($line_count . '行目の$csv_columns:' . var_export($csv_columns, true));
            // Log::debug(var_export($rules, true));

            $attribute_names = [];

            $col = 0;
            foreach ($code_columns as $code_column) {
                // 行数＋項目名
                $attribute_names[$col] = $line_count . '行目の' . $code_column;
                $col++;
            }

            $validator->setAttributeNames($attribute_names);
            // Log::debug(var_export($attribute_names, true));

            if ($validator->fails()) {
                $errors = array_merge($errors, $validator->errors()->all());
            }

            $line_count++;
        }

        return $errors;
    }

    /**
     * ダウンロード画面表示
     */
    public function download($request, $page_id = null)
    {
        // 管理画面プラグインの戻り値の返し方
        return view('plugins.manage.code.code_download', [
            "function" => __FUNCTION__,
            "plugin_name" => "code",
        ]);
    }

    /**
     * CSVインポートのフォーマットダウンロード
     */
    public function downloadCsvFormat($request, $page_id = null)
    {
        // データ出力しない（フォーマットのみ出力）
        $data_output_flag = false;
        return $this->downloadCsv($request, $page_id, $sub_id = null, $data_output_flag);
    }

    /**
     * データベースデータダウンロード
     */
    public function downloadCsv($request, $page_id = null, $sub_id = null, $data_output_flag = true)
    {
        // カラムの取得
        $columns = \CodeColumn::getImportColumn();

        // 返却用配列
        $csv_array = array();

        // 見出し行
        foreach ($columns as $columnKey => $column) {
            $csv_array[0][$columnKey] = $column;
        }

        // $data_output_flag = falseは、CSVフォーマットダウンロード処理
        if ($data_output_flag) {
            // 登録データの取得
            $codes = Codes::orderBy('plugin_name')
                    ->orderBy('buckets_id')
                    ->orderBy('prefix')
                    ->orderBy('type_code1')
                    ->orderBy('type_code2')
                    ->orderBy('type_code3')
                    ->orderBy('type_code4')
                    ->orderBy('type_code5')
                    ->orderBy('display_sequence')
                    ->get();

            // 行数
            $csv_line_no = 1;

            // データ
            foreach ($codes as $code) {
                $csv_line = [];
                foreach ($columns as $columnKey => $column) {
                    $csv_line[$columnKey] = $code->$columnKey;
                }

                $csv_array[$csv_line_no] = $csv_line;
                $csv_line_no++;
            }
        }

        // レスポンス版
        $filename = 'codes.csv';
        $headers = [
            'Content-Type' => 'text/csv',
            'content-Disposition' => 'attachment; filename="'.$filename.'"',
        ];

        // データ
        $csv_data = '';
        foreach ($csv_array as $csv_line) {
            foreach ($csv_line as $csv_col) {
                $csv_data .= '"' . $csv_col . '",';
            }
            // 末尾カンマを削除
            $csv_data = substr($csv_data, 0, -1);
            $csv_data .= "\n";
        }

        // Log::debug(var_export($request->character_code, true));

        // 文字コード変換
        if ($request->character_code == \CsvCharacterCode::utf_8) {
            $csv_data = mb_convert_encoding($csv_data, \CsvCharacterCode::utf_8);
            // UTF-8のBOMコードを追加する(UTF-8 BOM付きにするとExcelで文字化けしない)
            $csv_data = CsvUtils::addUtf8Bom($csv_data);
        } else {
            $csv_data = mb_convert_encoding($csv_data, \CsvCharacterCode::sjis_win);
        }

        return response()->make($csv_data, 200, $headers);
    }

}
