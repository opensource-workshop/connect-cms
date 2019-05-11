<?php

namespace App\Plugins\User\Codestudies;

use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;

use DB;

use App\Buckets;
use App\Codestudies;

use App\Frame;
use App\Page;

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
    /**
     *  データ初期表示関数
     *  コアがページ表示の際に呼び出す関数
     */
    public function index($request, $page_id, $frame_id, $result = null)
    {
        // ブログ＆フレームデータ
        $codestudies = Codestudies::get();

        // 記事取得
        $codestudy = new Codestudies();

        // 表示テンプレートを呼び出す。
        return $this->view(
            'codestudies', [
            'codestudies' => $codestudies,
            'codestudy'   => $codestudy,
            'result'      => $result,
        ]);
    }

    /**
     * 編集画面
     */
    public function edit($request, $page_id, $frame_id, $codestudy_id = null)
    {
        // セッション初期化などのLaravel 処理。
        $request->flash();

        // ブログ＆フレームデータ
        $codestudies = Codestudies::get();

        // コード取得
        $codestudy = Codestudies::where('id', $codestudy_id)->first();
//highlight_string($codestudy->code_text);

        // 変更画面を呼び出す。(blade でold を使用するため、withInput 使用)
        return $this->view(
            'codestudies_edit', [
            'codestudies' => $codestudies,
            'codestudy'   => $codestudy,
        ])->withInput($request->all);
    }

    /**
     *  保存処理
     */
    public function save_impl($request, $page_id, $frame_id, $codestudy_id)
    {
        // id があれば更新、なければ登録
        if (empty($codestudy_id)) {
            $codestudies = new Codestudies();
        }
        else {
            $codestudies = Codestudies::where('id', $codestudy_id)->first();
        }

        // コード設定
        $codestudies->user_id    = 1;
        $codestudies->study_lang = $request->study_lang;
        $codestudies->code_text  = $request->code_text;

        // データ保存
        $codestudies->save();
    }

    /**
     *  保存画面処理
     */
    public function save($request, $page_id, $frame_id, $codestudy_id)
    {
        // データ保存
        $this->save_impl($request, $page_id, $frame_id, $codestudy_id);

        // 登録後は表示用の初期処理を呼ぶ。
        return $this->index($request, $page_id, $frame_id);
    }

    /**
     *  実行処理
     */
    public function run($request, $page_id, $frame_id, $codestudy_id)
    {
        // データ保存
        $this->save_impl($request, $page_id, $frame_id, $codestudy_id);

        // コード取得
        $codestudy = Codestudies::where('id', $codestudy_id)->first();

        // ファイルに出力
        Storage::put('codestudy/' . $codestudy_id . '.php', $codestudy->code_text);

        // PHP 実行

//$cmd = 'dir';
$cmd = 'php ' . storage_path('app/codestudy/' . $codestudy_id . '.php');
exec($cmd, $opt);
//Log::debug($opt);

        // 登録後は表示用の初期処理を呼ぶ。
        return $this->index($request, $page_id, $frame_id, print_r($opt,true));
    }
}
