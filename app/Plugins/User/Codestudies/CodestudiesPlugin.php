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
    var $run_check_msgs = null;

    /* コアから呼び出す関数 */

    /**
     *  関数定義（コアから呼び出す）
     */
    public function getPublicFunctions()
    {
        // 標準関数以外で画面などから呼ばれる関数の定義
        $functions = array();
        $functions['get']  = [];
        $functions['post'] = ['run'];
        return $functions;
    }

    /**
     *  POST取得関数（コアから呼び出す）
     *  コアがPOSTチェックの際に呼び出す関数
     */
    public function getPost($id) {

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
            ]);
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
        ])->withInput($request->all);
    }

    /**
     * 編集画面
     */
    public function edit($request, $page_id, $frame_id, $codestudy_id = null, $result = null, $error_flag = null, $errors = null)
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
            ]);
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
        ])->withInput($request->all);
    }

    /**
     *  保存処理
     */
    private function save_impl($request, $page_id, $frame_id, $codestudy_id)
    {
        // id があれば更新、なければ登録
        if (empty($codestudy_id)) {
            $codestudies = new Codestudies();
        }
        else {
            $codestudies = Codestudies::where('id', $codestudy_id)->first();
        }

        // コード設定
        $codestudies->title      = $request->title;
        $codestudies->study_lang = $request->study_lang;
        $codestudies->code_text  = $request->code_text;

        // データ保存
        $codestudies->save();

        // id を返却
        return $codestudies->id;
    }

    /**
     *  保存画面処理
     */
    public function save($request, $page_id, $frame_id, $codestudy_id)
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
                return $this->edit($request, $page_id, $frame_id, $codestudy_id, null, null, $validator->errors());
            }
            else {
                return $this->index($request, $page_id, $frame_id, $validator->errors());
            }
        }

        // データ保存
        $codestudy_id = $this->save_impl($request, $page_id, $frame_id, $codestudy_id);

        // 登録後は表示用の初期処理を呼ぶ。
        return $this->edit($request, $page_id, $frame_id, $codestudy_id);
    }

    /**
     *  実行可否の判定
     */
    private function run_check($codestudy)
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
            foreach($deny_method[$codestudy->study_lang] as $check_method) {

                if (stripos($code_text_trim_space,$check_method['check_str']) !== false) {
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
        if ($this->can('posts.update', $this->getPost($codestudy_id))) {
            return $this->view_error(403);
        }

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
                return $this->edit($request, $page_id, $frame_id, $codestudy_id, null, null, $validator->errors());
            }
            else {
                return $this->index($request, $page_id, $frame_id, $validator->errors());
            }
        }

        // データ保存
        $codestudy_id = $this->save_impl($request, $page_id, $frame_id, $codestudy_id);

        // コード取得
        $codestudy = Codestudies::where('id', $codestudy_id)->first();

        // ファイルに出力
        //Storage::put('codestudy/' . $codestudy_id . '.php', $codestudy->code_text);
        Storage::makeDirectory('codestudy/' . $codestudy->created_id . '/' . $codestudy_id);

        $class_name = "";

        // 言語判定
        if ($codestudy->study_lang == 'php') {
            Storage::put('codestudy/' . $codestudy->created_id . '/' . $codestudy_id . '/' . $codestudy_id . '.php', $codestudy->code_text);
        }
        else if ($codestudy->study_lang == 'java') {
            $class_name = $this->getClassName($codestudy);
            Storage::put('codestudy/' . $codestudy->created_id . '/' . $codestudy_id . '/' . $class_name . '.java', $codestudy->code_text);
        }

        // 実行可否の判定
        $error_flag = null;
        //$error_msg = $this->run_check($codestudy);
        $error_msg = array();

        $this->run_check($codestudy);

        //if ($error_msg) {
        if ($this->run_check_msgs) {
            // エラーメッセージを返す。
            $error_flag = 1;
            $result = $error_msg;
        }
        else {
            $cmd = '';

            if ($codestudy->study_lang == 'php') {
                // PHP 実行
                $cmd = 'php ' . storage_path('app/codestudy/' . $codestudy->created_id . '/' . $codestudy_id . '/' . $codestudy_id . '.php');
                //$cmd = 'php -l ' . storage_path('app/codestudy/' . $codestudy_id . '.php');

                if (!empty($cmd)) {
                    exec("$cmd 2>&1", $result);
                }
            }
            else if ($codestudy->study_lang == 'java') {

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
                    $cmd  = 'java -classpath ' . storage_path('app/codestudy/' . $codestudy->created_id . '/' . $codestudy_id);
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
        return $this->edit($request, $page_id, $frame_id, $codestudy_id, $result, $error_flag);
    }

    /**
     *  削除処理
     */
    public function delete($request, $page_id, $frame_id, $codestudy_id)
    {
        // id がある場合、データを削除
        if ( $codestudy_id ) {

            // データを削除する。
            Codestudies::delete($codestudy_id);
        }
        // 削除後は表示用の初期処理を呼ぶ。
        return $this->index($request, $page_id, $frame_id);
    }

}
