<?php

namespace App\Plugins\User\Sampleforms;

use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;

use DB;
use File;

use App\Frame;
use App\Page;
use App\Sampleforms;
use App\Uploads;

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

        // データ取得（1ページの表示件数指定）
        $sampleforms = Sampleforms::orderBy('created_at', 'desc')
                       ->paginate(2);

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
    public function create($request, $page_id, $frame_id, $errors = null)
    {
        // セッション初期化などのLaravel 処理。
        $request->flash();

        // Page データ
        $page = Page::where('id', $page_id)->first();

        // 空のデータ(画面で初期値設定で使用するため)
        $sampleform = new Sampleforms();

        // 表示テンプレートを呼び出す。(blade でold を使用するため、withInput 使用)
        return view('plugins.user.sampleforms.sampleforms_input', [
            'frame_id' => $frame_id,
            'page' => $page,
            'sampleform' => $sampleform,
            'errors' => $errors,
        ])->withInput($request->all);
    }

    /**
     * データ編集画面
     */
    public function edit($request, $page_id, $frame_id, $id = null, $errors = null)
    {
        // セッション初期化などのLaravel 処理。
        $request->flash();

        // Page データ
        $page = Page::where('id', $page_id)->first();

        // データ取得
        $sampleform = Sampleforms::where('id', $id)->first();

        // 変更画面を呼び出す。(blade でold を使用するため、withInput 使用)
        return view('plugins.user.sampleforms.sampleforms_input', [
            'frame_id' => $frame_id,
            'id' => $id,
            'page' => $page,
            'sampleform' => $sampleform,
            'errors' => $errors,
        ])->withInput($request->all);
    }

    /**
     *  確認画面
     */
    public function confirm($request, $page_id, $frame_id, $id = null)
    {
        // 項目のエラーチェック
        $validator = Validator::make($request->all(), [
            'column_text' => ['required'],
        ]);
        $validator->setAttributeNames([
            'column_text' => 'テキスト',
        ]);

        // エラーがあった場合は入力画面に戻る。
        if ($validator->fails()) {
            if (empty($id)) {
                return ( $this->create($request, $page_id, $frame_id, $validator->errors()) );
            }
            else {
                return ( $this->edit($request, $page_id, $frame_id, $id, $validator->errors()) );
            }
        }

        // セッション初期化などのLaravel 処理。
        $request->flash();

        // Page データ
        $page = Page::where('id', $page_id)->first();

        // データ取得
        $sampleform = Sampleforms::where('id', $id)->first();

        // アップロードファイルの定義
        $upload_files = array();

        // アップロードファイルが存在するかの確認
        if ($request->hasFile('column_file')) {

            // 確認中の一時ファイルとして保存
            $path = $request->file('column_file')->store('uploads/tmp');

            // オリジナルファイル名などのアップロードファイル情報を$upload_files 変数に保持
            $upload_files['column_file']['path'] = $path;
            $upload_files['column_file']['client_original_name'] = $request->file('column_file')->getClientOriginalName();
            $upload_files['column_file']['mimetype'] = $request->file('column_file')->getClientMimeType();
        }

        // 表示テンプレートを呼び出す。(blade でold を使用するため、withInput 使用)
        return view('plugins.user.sampleforms.sampleforms_confirm', [
            'frame_id'     => $frame_id,
            'id'           => $id,
            'page'         => $page,
            'sampleform'   => $sampleform,
            'upload_files' => $upload_files,
            'base_action'  => $request->base_action,
        ])->withInput($request->all);
    }

    /**
     *  保存画面
     */
    public function save($request, $page_id, $frame_id, $id = null)
    {
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
        $sampleforms->column_password = $request->column_password;
        $sampleforms->column_radio    = $request->column_radio;
        $sampleforms->column_hidden   = $request->column_hidden;
        $sampleforms->column_textarea = $request->column_textarea;
        $sampleforms->column_select   = $request->column_select;
        $sampleforms->column_checkbox = (empty($request->column_checkbox) ? null : "|".implode("|", $request->column_checkbox)."|" );

        // アップロードファイルの取得
        if (!empty($request->upload_files)) {
            $column_file = $request->upload_files['column_file'];
            if (!empty($column_file)) {

                // Uploads テーブルに登録
                $uploads_id = DB::table('Uploads')->insertGetId([
                      'client_original_name' => $request->upload_files['column_file']['client_original_name'],
                      // Storage ファサードで拡張子が取れなかったので、File を使用
                      'extension' => File::extension($request->upload_files['column_file']['path']),
                      'mimetype'  => $request->upload_files['column_file']['mimetype'],
                      'size'      => Storage::size($request->upload_files['column_file']['path']),
                ]);
                $sampleforms->column_file = $uploads_id;

                // ファイルの移動
                Storage::move($request->upload_files['column_file']['path'], 'uploads/' . $uploads_id . '.' . File::extension($request->upload_files['column_file']['path']));
            }
        }

        // データ保存
        $sampleforms->save();
    }

    /**
     *  更新処理
     */
    public function update($request, $page_id, $frame_id, $id)
    {
        // データ取得
        $sampleforms = Sampleforms::where('id', $id)->first();

        // 旧ファイル情報
        $old_file_id = null;

        // アップロードファイルの取得
        if (!empty($request->upload_files)) {
            $column_file = $request->upload_files['column_file'];
            if (!empty($column_file)) {

                // 先のファイルがあれば、後で削除するためにidを保持しておく。
                if (!empty($sampleforms->column_file)) {
                    $old_file_id = $sampleforms->column_file;
                }

                // Uploads テーブルに登録
                $uploads_id = DB::table('Uploads')->insertGetId([
                      'client_original_name' => $request->upload_files['column_file']['client_original_name'],
                      // Storage ファサードで拡張子が取れなかったので、File を使用
                      'extension' => File::extension($request->upload_files['column_file']['path']),
                      'mimetype'  => $request->upload_files['column_file']['mimetype'],
                      'size'      => Storage::size($request->upload_files['column_file']['path']),
                ]);
                $sampleforms->column_file = $uploads_id;

                // ファイルの移動
                Storage::move($request->upload_files['column_file']['path'], 'uploads/' . $uploads_id . '.' . File::extension($request->upload_files['column_file']['path']));
            }
        }

        // 各データを詰める
        $sampleforms->column_text     = $request->column_text;
        $sampleforms->column_radio    = $request->column_radio;
        $sampleforms->column_hidden   = $request->column_hidden;
        $sampleforms->column_textarea = $request->column_textarea;
        $sampleforms->column_select   = $request->column_select;
        $sampleforms->column_checkbox = (empty($request->column_checkbox) ? null : "|".implode("|", $request->column_checkbox)."|" );
        $sampleforms->save();

        // パスワードは入力があった場合のみ、更新する。
        if (!empty($request->column_password)) {
            $sampleforms->column_password = $request->column_password;
        }

        // 先のファイルがあれば、削除
        if (!empty($old_file_id)) {

            // Uploads データ
            $upload = Uploads::where('id', $old_file_id)->first();
            if ($upload) {
                // 実ファイル(存在確認してなければスルー)
                $file_exists = Storage::exists('uploads/' . $old_file_id . '.' . $upload->extension);
                if ($file_exists) {
                    Storage::delete('uploads/' . $old_file_id . '.' . $upload->extension);
                }
            }

            // データベースから削除
            Uploads::destroy($old_file_id);
        }
    }

    /**
     *  削除処理
     */
    public function destroy($request, $page_id, $frame_id, $id)
    {
        // id がある場合、データを削除
        if ( $id ) {

            // データ取得
            $sampleform = Sampleforms::where('id', $id)->first();

            // ファイルがあれば、削除
            if (!empty($sampleform->column_file)) {

                // Uploads データと実ファイルの削除
                $upload = Uploads::where('id', $sampleform->column_file)->first();
                if ($upload) {
                    // 実ファイル(存在確認してなければスルー)
                    $file_exists = Storage::exists('uploads/' . $sampleform->column_file . '.' . $upload->extension);
                    if ($file_exists) {
                        Storage::delete('uploads/' . $sampleform->column_file . '.' . $upload->extension);
                    }
                    Uploads::destroy($sampleform->column_file);
                }
            }

            // データを削除する。
            Sampleforms::destroy($id);
        }
        return;
    }
}
