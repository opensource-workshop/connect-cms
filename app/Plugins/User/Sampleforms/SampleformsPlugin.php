<?php

namespace App\Plugins\User\Sampleforms;

use Illuminate\Support\Facades\Log;

use DB;

use App\Buckets;
use App\Contents;
use App\Frame;
use App\Page;
use App\Sampleforms;

use App\Plugins\User\UserPluginBase;

/**
 * フォームのサンプルプラグイン
 *
 * @author 永原　篤 <nagahara@opensource-workshop.jp>
 * @copyright OpenSource-WorkShop Co.,Ltd. All Rights Reserved
 * @category コンテンツプラグイン
 * @package Contoroller
 */
class SampleformsPlugin extends UserPluginBase
{

    /**
     *  データ初期表示関数
     *  コアがページ表示の際に呼び出す関数
     */
    public function index($request, $page_id, $frame_id)
    {
        // Page データ
        $page = Page::where('id', $page_id)->first();

        // コンテンツ
        $sampleforms = Sampleforms::orderBy('created_at', 'desc')->get();

        // 表示テンプレートを呼び出す。
        return view('plugins.user.sampleforms.sampleforms', [
            'page'        => $page,
            'frame_id'    => $frame_id,
            'sampleforms' => $sampleforms,
        ]);
    }

    /**
     *  新規登録画面
     */
    public function create($request, $page_id, $frame_id)
    {
        // セッション初期化などのLaravel 処理。
        $request->flash();

        // Page データ
        $page = Page::where('id', $page_id)->first();

        // 空のデータ(画面で初期値設定で使用するため)
        $sampleform = new Sampleforms();

        // 表示テンプレートを呼び出す。
        return view('plugins.user.sampleforms.sampleforms_input', [
            'frame_id' => $frame_id,
            'page' => $page,
            'sampleform' => $sampleform,
        ])->withInput($request->all);
    }

    /**
     * データ編集画面
     */
    public function edit($request, $page_id, $frame_id, $id = null)
    {
        // Page データ
        $page = Page::where('id', $page_id)->first();

        // データ取得
        $sampleform = Sampleforms::where('id', $id)->first();

        // 変更画面を呼び出す
        return view('plugins.user.sampleforms.sampleforms_input', [
            'frame_id' => $frame_id,
            'id' => $id,
            'page' => $page,
            'sampleform' => $sampleform,
        ]);
    }

    /**
     *  チェック
     */
//    public function validator($request, $page_id, $frame_id, $id = null)
//    {
//        return redirect('/?action=confirm&frame_id=' . $frame_id)->withInput();
//    }

    /**
     *  確認画面
     */
    public function confirm($request, $page_id, $frame_id, $id = null)
    {
        // セッション初期化などのLaravel 処理。
        $request->flash();

        // Page データ
        $page = Page::where('id', $page_id)->first();

        // アップロードファイルの定義
        $tmp_filename = null;
        $upload_filename = null;

        // アップロードファイルの一時保存
        if ($request->hasFile('column_file')) {

            // アップロードファイルがある場合
            $tmp_filename = uniqid() . "." . $request->file('column_file')->guessExtension();
            $request->file('column_file')->move(public_path() . "/uploads/tmp", $tmp_filename);
            $upload_filename = $request->file('column_file')->getClientOriginalName();
        }

        // 表示テンプレートを呼び出す。
        return view('plugins.user.sampleforms.sampleforms_confirm', [
            'frame_id' => $frame_id,
            'id' => $id,
            'page' => $page,
            'tmp_filename' => $tmp_filename,
            'upload_filename' => $upload_filename,
        ])->withInput($request->all);
    }

    /**
     *  保存画面
     */
    public function save($request, $page_id, $frame_id, $id = null)
    {
        // Log::debug($request);

        // Frame データ
        $frame = Frame::where('id', $frame_id)->first();

        // bucket の有無を確認して、なければ作成
        if (empty($frame->bucket_id)) {

            // バケツの登録
            $bucket_id = DB::table('buckets')->insertGetId([
                  'bucket_name' => '無題',
                  'plugin_name' => 'sampleforms'
            ]);

            // FrameのバケツIDの更新
            Frame::where('id', $frame_id)->update(['bucket_id' => $bucket_id]);

        }
        else {
            $bucket_id = $frame->bucket_id;
        }

        // データ登録
        $sampleforms = new Sampleforms();
        $sampleforms->bucket_id       = $bucket_id;
        $sampleforms->form_name       = $request->form_name;
        $sampleforms->column_text     = $request->column_text;
        if (!empty($request->column_password)) {
            $sampleforms->column_password = $request->column_password;
        }
        $sampleforms->column_radio    = $request->column_radio;
        $sampleforms->column_file     = $request->column_file;
        $sampleforms->column_hidden   = $request->column_hidden;
        $sampleforms->column_textarea = $request->column_textarea;
        $sampleforms->column_select   = $request->column_select;
        $sampleforms->column_checkbox = (empty($request->column_checkbox) ? null : "|".implode("|", $request->column_checkbox)."|" );
        $sampleforms->save();

        // Log::debug($request);

    }

    /**
     *  更新処理
     */
    public function update($request, $page_id, $frame_id, $id)
    {
        Log::debug($request);

        // データ取得
        $sampleforms = Sampleforms::where('id', $id)->first();

        $sampleforms->column_text     = $request->column_text;
        if (!empty($request->column_password)) {
            $sampleforms->column_password = $request->column_password;
        }
        $sampleforms->column_radio    = $request->column_radio;
        if (!empty($request->column_file)) {
            $sampleforms->column_file     = $request->column_file;
        }
        $sampleforms->column_hidden   = $request->column_hidden;
        $sampleforms->column_textarea = $request->column_textarea;
        $sampleforms->column_select   = $request->column_select;
        $sampleforms->column_checkbox = (empty($request->column_checkbox) ? null : "|".implode("|", $request->column_checkbox)."|" );
        $sampleforms->save();

        // Log::debug($request);

    }
}
