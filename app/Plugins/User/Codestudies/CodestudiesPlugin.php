<?php

namespace App\Plugins\User\Codestudies;

use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;

use DB;

use App\Models\Common\Buckets;
use App\Models\Common\Frame;
use App\Models\Common\Page;
use App\Models\User\Codestudies\Codestudies;

use App\Enums\CsvCharacterCode;

use App\Plugins\User\UserPluginBase;

/**
 * ブログプラグイン
 *
 * @author 永原　篤 <nagahara@opensource-workshop.jp>
 * @copyright OpenSource-WorkShop Co.,Ltd. All Rights Reserved
 * @category コンテンツプラグイン
 * @package Contoroller
 */
class CodestudiesPlugin extends UserPluginBase
{

    /* オブジェクト変数 */

    /**
     * 変更時のPOSTデータ
     */
    public $post = null;

    /**
     *  実行関数チェック
     */
    private $run_check_msgs = null;

    /* コアから呼び出す関数 */

    /**
     *  関数定義（コアから呼び出す）
     */
    public function getPublicFunctions()
    {
        // 標準関数以外で画面などから呼ばれる関数の定義
        $functions = array();
        $functions['get']  = ['editcode', 'viewDownload', 'download'];
        $functions['post'] = ['savecode', 'run', 'deletecode'];
        return $functions;
    }

    /**
     * 追加の権限定義（コアから呼び出す）
     */
    public function declareRole()
    {
        // 標準権限以外で設定画面などから呼ばれる権限の定義
        // 標準権限は右記で定義 config/cc_role.php
        //
        // 権限チェックテーブル
        // [TODO] 【各プラグイン】declareRoleファンクションで適切な追加の権限定義を設定する https://github.com/opensource-workshop/connect-cms/issues/658
        $role_ckeck_table = array();
        $role_ckeck_table["editcode"]   = array('role_reporter');
        $role_ckeck_table["savecode"]   = array('role_reporter');
        $role_ckeck_table["run"]        = array('role_reporter');
        $role_ckeck_table["deletecode"] = array('role_reporter');
        $role_ckeck_table["download"]     = array('role_arrangement');
        $role_ckeck_table["viewDownload"] = array('role_arrangement');
        return $role_ckeck_table;
    }

    /**
     *  使用言語のバージョン取得
     */
    private function getLangVersion()
    {
        $versions = array();
        // PHP
        $versions['PHP'] = phpversion();
        // Java
        $cmd = 'javac -encoding UTF-8 -version';
        exec("$cmd 2>&1", $result);
        // バージョン取得ががうまくいった場合($result が空)
        if (strtoupper(substr(PHP_OS, 0, 3)) === 'WIN') {
            $versions['Java'] = mb_convert_encoding(implode('<br />', $result), "UTF-8", "sjis-win");
        } else {
            $versions['Java'] = implode('<br />', $result);
        }

        return $versions;
    }

    /**
     *  POST取得関数（コアから呼び出す）
     *  コアがPOSTチェックの際に呼び出す関数
     */
    public function getPost($id)
    {

        // 一度読んでいれば、そのPOSTを再利用する。
        if (!empty($this->post)) {
            return $this->post;
        }

        // コード取得
        $this->post = Codestudies::where('id', $id)->first();

        return $this->post;
    }

    /* 画面アクション関数 */

    /**
     *  データ初期表示関数
     *  コアがページ表示の際に呼び出す関数
     */
    public function index($request, $page_id, $frame_id, $errors = null)
    {
        // セッション初期化などのLaravel 処理。
        $request->flash();

        // 認証されているユーザの取得
        $user = Auth::user();

        // ログイン
        if (empty($user) || empty($user->id)) {
            // 認証エラーテンプレートを呼び出す。
            return $this->view(
                'codestudies_forbidden', [
                ]
            );
        }

        // 自分の保存したプログラムを取得
        $codestudies = Codestudies::where('created_id', $user->id)->get();

        // 画面で空を表示させるために、空のオブジェクトを生成
        $codestudy = new Codestudies();

        // 表示テンプレートを呼び出す。
        return $this->view(
            'codestudies', [
            'codestudies' => $codestudies,
            'codestudy'   => $codestudy,
            'errors'      => $errors,
            'versions'    => $this->getLangVersion(),
            ]
        )->withInput($request->all);
    }

    /**
     * 編集画面
     */
    public function editcode($request, $page_id, $frame_id, $codestudy_id = null, $result = null, $error_flag = null, $errors = null)
    {
        // セッション初期化などのLaravel 処理。
        $request->flash();

        // 認証されているユーザの取得
        $user = Auth::user();

        // ログイン
        if (empty($user) || empty($user->id)) {
            // 認証エラーテンプレートを呼び出す。
            return $this->view(
                'codestudies_forbidden', [
                ]
            );
        }

        // 自分のコード全て
        $codestudies = Codestudies::where('created_id', $user->id)->get();

        // コード取得
        $codestudy = $this->getPost($codestudy_id);

        // if (empty($codestudy)) {
        //     $codestudy = new Codestudies();
        // }


        // 変更画面を呼び出す。(blade でold を使用するため、withInput 使用)
        return $this->view(
            'codestudies', [
            'codestudies'    => $codestudies,
            'codestudy'      => $codestudy,
            'result'         => $result,
            'error_flag'     => $error_flag,
            'errors'         => $errors,
            'run_check_msgs' => $this->run_check_msgs,
            'versions'       => $this->getLangVersion(),
            ]
        )->withInput($request->all);
    }

    /**
     *  保存処理
     */
    private function saveImpl($request, $page_id, $frame_id, $codestudy_id)
    {
        // id があれば更新、なければ登録
        if (empty($codestudy_id)) {
            $codestudies = new Codestudies();
        } else {
            $codestudies = Codestudies::where('id', $codestudy_id)->first();
        }

        // コード設定
        $codestudies->title      = $request->title;
        $codestudies->study_lang = $request->study_lang;
        $codestudies->code_text  = $request->code_text;
        $codestudies->created_id = Auth::user()->id;

        // データ保存
        $codestudies->save();

        // id を返却
        return $codestudies->id;
    }

    /**
     *  保存画面処理
     */
    public function savecode($request, $page_id, $frame_id, $codestudy_id)
    {

        // 項目のエラーチェック
        $validator = Validator::make($request->all(), [
            'code_text' => ['required'],
            'study_lang' => ['required'],
        ]);
        $validator->setAttributeNames([
            'code_text'  => 'コード',
            'study_lang' => '言語',
        ]);

        // エラーがあった場合は入力画面に戻る。
        if ($validator->fails()) {
            if ($codestudy_id) {
                return $this->editcode($request, $page_id, $frame_id, $codestudy_id, null, null, $validator->errors());
            } else {
                return $this->index($request, $page_id, $frame_id, $validator->errors());
            }
        }

        // データ保存
        $codestudy_id = $this->saveImpl($request, $page_id, $frame_id, $codestudy_id);

        // 登録後は表示用の初期処理を呼ぶ。
        return $this->editcode($request, $page_id, $frame_id, $codestudy_id);
    }

    /**
     *  実行可否の判定
     */
    private function runCheck($codestudy)
    {
        // 禁止関数
        $deny_method = array();
        $deny_method['php'][] = array('method'=>'dl',                   'check_str'=>'dl(');
        $deny_method['php'][] = array('method'=>'exec',                 'check_str'=>'exec(');
        $deny_method['php'][] = array('method'=>'fsockopen',            'check_str'=>'fsockopen(');
        $deny_method['php'][] = array('method'=>'passthru',             'check_str'=>'passthru(');
        $deny_method['php'][] = array('method'=>'pcntl_exec',           'check_str'=>'pcntl_exec(');
        $deny_method['php'][] = array('method'=>'pfsockopen',           'check_str'=>'pfsockopen(');
        $deny_method['php'][] = array('method'=>'phpinfo',              'check_str'=>'phpinfo(');
        $deny_method['php'][] = array('method'=>'popen',                'check_str'=>'popen(');
        $deny_method['php'][] = array('method'=>'proc_open',            'check_str'=>'proc_open(');
        $deny_method['php'][] = array('method'=>'shell_exec',           'check_str'=>'shell_exec(');
        $deny_method['php'][] = array('method'=>'stream_socket_client', 'check_str'=>'stream_socket_client(');
        $deny_method['php'][] = array('method'=>'system',               'check_str'=>'system(');

        // 戻り値
        $return = array();

        // コードからスペースを取り除く
        $code_text_trim_space = str_replace(' ', '', $codestudy->code_text);

        // エラーチェック
        if (array_key_exists($codestudy->study_lang, $deny_method)) {
            foreach ($deny_method[$codestudy->study_lang] as $check_method) {
                if (stripos($code_text_trim_space, $check_method['check_str']) !== false) {
                    $return[] = "「" . $check_method['method'] . "」が実行される可能性のあるプログラムは実行できません。";
                }
            }
        }
        //return $return;

        $this->run_check_msgs = $return;

        return;
    }

    /**
     *  Java クラス名抜き出し
     */
    private function getClassName($codestudy)
    {
        $tmp_code = trim($codestudy->code_text, "\n");
        $tmp_code = trim($tmp_code, "\r");

        $tmp_code = substr($tmp_code, strpos($tmp_code, 'class') + 5, strpos($tmp_code, '{') - strpos($tmp_code, 'class') - 5);

        $tmp_code = trim(mb_convert_kana($tmp_code, 'as', 'UTF-8'));
        $tmp_code = preg_replace('/[^0-9a-zA-Z]/', '', $tmp_code);

        return $tmp_code;
    }

    /**
     *  実行処理
     */
    public function run($request, $page_id, $frame_id, $codestudy_id)
    {
        // 権限チェック（run 関数は標準チェックにないので、独自チェック）
        //if ($this->can('posts.update', $this->getPost($codestudy_id))) {
        //    return $this->view_error(403);
        //}

        // 項目のエラーチェック
        $validator = Validator::make($request->all(), [
            'code_text'  => ['required'],
            'study_lang' => ['required'],
        ]);
        $validator->setAttributeNames([
            'code_text'  => 'コード',
            'study_lang' => '言語',
        ]);

        // エラーがあった場合は入力画面に戻る。
        if ($validator->fails()) {
            if ($codestudy_id) {
                return $this->editcode($request, $page_id, $frame_id, $codestudy_id, null, null, $validator->errors());
            } else {
                return $this->index($request, $page_id, $frame_id, $validator->errors());
            }
        }

        // データ保存
        $codestudy_id = $this->saveImpl($request, $page_id, $frame_id, $codestudy_id);

        // コード取得
        $codestudy = Codestudies::where('id', $codestudy_id)->first();

        // ファイルに出力
        //Storage::put('codestudy/' . $codestudy_id . '.php', $codestudy->code_text);
        Storage::makeDirectory('codestudy/' . $codestudy->created_id . '/' . $codestudy_id);

        $class_name = "";

        // 言語判定
        if ($codestudy->study_lang == 'php') {
            Storage::put('codestudy/' . $codestudy->created_id . '/' . $codestudy_id . '/' . $codestudy_id . '.php', $codestudy->code_text);
        } elseif ($codestudy->study_lang == 'java') {
            $class_name = $this->getClassName($codestudy);
            Storage::put('codestudy/' . $codestudy->created_id . '/' . $codestudy_id . '/' . $class_name . '.java', $codestudy->code_text);
        }

        // 実行可否の判定
        $error_flag = null;
        //$error_msg = $this->runCheck($codestudy);
        $error_msg = array();

        $this->runCheck($codestudy);

        //if ($error_msg) {
        if ($this->run_check_msgs) {
            // エラーメッセージを返す。
            $error_flag = 1;
            $result = $error_msg;
        } else {
            $cmd = '';

            if ($codestudy->study_lang == 'php') {
                // PHP 実行
                $cmd = 'php ' . storage_path('app/codestudy/' . $codestudy->created_id . '/' . $codestudy_id . '/' . $codestudy_id . '.php');
                //$cmd = 'php -l ' . storage_path('app/codestudy/' . $codestudy_id . '.php');

                if (!empty($cmd)) {
                    exec("$cmd 2>&1", $result);
                }
            } elseif ($codestudy->study_lang == 'javascript') {
                    $result = $codestudy;
            } elseif ($codestudy->study_lang == 'java') {
                // コンパイル
                $cmd = 'javac -encoding UTF-8 ' . storage_path('app/codestudy/' . $codestudy->created_id . '/' . $codestudy_id . '/' . $class_name . '.java');
                if (!empty($cmd)) {
                    exec("$cmd 2>&1", $result);
                    //Log::debug($cmd);
                    //Log::debug($result);
                }

                // コンパイルがうまくいった場合($result が空)
                if (empty($result)) {
                    // 実行
                    $cmd  = 'java -Dfile.encoding=UTF-8 -classpath ' . storage_path('app/codestudy/' . $codestudy->created_id . '/' . $codestudy_id);
                    $cmd .= ' ' . $class_name;
                    exec("$cmd 2>&1", $result);
                    //Log::debug($cmd);
                    //Log::debug($result);
                }
            }
        }

        // $result 内のプログラム・ファイルパスを編集する。
        $rep_str = storage_path('app\\codestudy\\' . $codestudy->created_id . '\\' . $codestudy_id . '\\');
        foreach ($result as &$result_item) {
            $result_item = str_replace($rep_str, '', $result_item);
        }

        // 登録後は表示用の初期処理を呼ぶ。
        return $this->editcode($request, $page_id, $frame_id, $codestudy_id, $result, $error_flag);
    }

    /**
     *  削除処理
     */
    public function deletecode($request, $page_id, $frame_id, $codestudy_id)
    {
        // id がある場合、データを削除
        if ($codestudy_id) {
            // データを削除する。
            Codestudies::where('id', $codestudy_id)->delete();
        }
        // 削除後は表示用の初期処理を呼ぶ。
        return $this->index($request, $page_id, $frame_id);
    }

    /**
     *  成績ダウンロード指示画面
     */
    public function viewDownload($request, $page_id, $frame_id)
    {
        // 表示テンプレートを呼び出す。
        return $this->view(
            'download', []
        )->withInput($request->all);
    }

    /**
     *  成績ダウンロード実行
     */
    public function download($request, $page_id, $frame_id)
    {

        $save_path = $this->getTmpDirectory() . uniqid('', true) . '.zip';
        $this->makeZip($save_path, $request);

        // 一時ファイルは削除して、ダウンロードレスポンスを返す
        return response()->download(
            $save_path,
            'StudyCodes.zip',
            ['Content-Disposition' => 'filename=StudyCodes.zip']
        )->deleteFileAfterSend(true);
    }

    /**
     * ダウンロードするZIPファイルを作成する。
     *
     * @param string $save_path 保存先パス
     * @param \Illuminate\Http\Request $request リクエスト
     */
    private function makeZip($save_path, $request)
    {
        $zip = new \ZipArchive();
        $zip->open($save_path, \ZipArchive::CREATE);

        // フォルダがないとzipファイルを作れない
        if (!is_dir($this->getTmpDirectory())) {
            mkdir($this->getTmpDirectory(), 0777, true);
        }

        // 学生のコードを取得する。
        // ユーザは削除された場合のことも想定しておく。
        $codestudies = Codestudies::select(
            'codestudies.*',
            'users.userid',
            'users.name',
        )
        ->leftJoin('users', 'users.id', '=', 'codestudies.created_id')
        ->orderBy('codestudies.created_id', 'asc')
        ->orderBy('codestudies.id', 'asc')
        ->get();

        // 学生ループ
        $tmp_dir_name = '';
        foreach ($codestudies as $codestudy) {
            // 学生用フォルダ。ログインID（学籍番号を想定）で作成
            // もしユーザデータがなかったら、_{$created_id}
            $dir = $codestudy->userid;
            if (empty($dir)) {
                $dir = '_' . $codestudy->created_id;
            }
            // 学生用フォルダ作成
            if ($tmp_dir_name != $dir) {
                $zip->addEmptyDir($dir);
                $tmp_dir_name = $dir;
            }
            // 拡張子
            $ext = "";
            if ($codestudy->study_lang == 'javascript') {
                $ext = ".js";
            } elseif ($codestudy->study_lang == 'java') {
                $ext = ".java";
            } elseif ($codestudy->study_lang == 'php') {
                $ext = ".php";
            }
            // コードの保存
            $zip->addFromString($dir . "/" . mb_convert_encoding($codestudy->title, CsvCharacterCode::sjis_win) . $ext, $codestudy->code_text . "\n");
        }

        // 空のZIPファイルが出来たら404
        if ($zip->count() === 0) {
            abort(404, 'ファイルがありません。');
        }
        $zip->close();
    }

    /**
     * 一時フォルダのパスを取得する
     *
     * @return string 一時フォルダのパス
     */
    private function getTmpDirectory()
    {
        return storage_path('app/') . 'tmp/codestudies/';
    }
}
