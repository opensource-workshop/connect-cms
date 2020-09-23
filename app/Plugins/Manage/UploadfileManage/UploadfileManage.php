<?php

namespace App\Plugins\Manage\UploadfileManage;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;

use File;

use App\Models\Common\Uploads;

use App\Plugins\Manage\ManagePluginBase;

/**
 * アップロードファイル管理クラス
 *
 * @author 永原　篤 <nagahara@opensource-workshop.jp>
 * @copyright OpenSource-WorkShop Co.,Ltd. All Rights Reserved
 * @category アップロードファイル管理
 * @package Contoroller
 */
class UploadfileManage extends ManagePluginBase
{
    /**
     *  権限定義
     */
    public function declareRole()
    {
        // 権限チェックテーブル
        $role_ckeck_table = array();
        $role_ckeck_table["index"]       = array('admin_site');
        $role_ckeck_table["search"]      = array('admin_site');
        $role_ckeck_table["clearSearch"] = array('admin_site');
        $role_ckeck_table["edit"]        = array('admin_site');
        $role_ckeck_table["save"]        = array('admin_site');
        $role_ckeck_table["uploadImage"] = array('admin_site');
        $role_ckeck_table["deleteImage"] = array('admin_site');
        return $role_ckeck_table;
    }

    /**
     *  ページ初期表示
     *
     * @return view
     */
    public function index($request)
    {
        /* ページの処理（セッション）
        ----------------------------------------------*/

        // 表示ページ数。詳細で更新して戻ってきたら、元と同じページを表示したい。
        // セッションにあればページの指定があれば使用。
        // ただし、リクエストでページ指定があればそれが優先。(ページング操作)
        $page = 1;
        if ($request->session()->has('search_condition.page')) {
            $page = $request->session()->get('search_condition.page');
        }
        if ($request->filled('page')) {
            $page = $request->page;
        }

        // ページがリクエストで指定されている場合は、セッションの検索条件配列のページ番号を更新しておく。
        // 詳細画面や更新処理から戻ってきた時用
        if ($request->filled('page')) {
            session(["search_condition.page" => $request->page]);
        }

        /* データの取得（検索）
        ----------------------------------------------*/

        // アップロードファイルを検索
        $uploads_query = Uploads::select('uploads.*', 'plugins.plugin_name_full', 'pages.page_name', 'pages.permanent_link')
                                ->leftJoin('plugins', 'plugins.plugin_name', '=', 'uploads.plugin_name')
                                ->leftJoin('pages', 'pages.id', '=', 'uploads.page_id');

        if ($request->session()->has('search_condition.client_original_name')) {
            $uploads_query->where('client_original_name', 'like', '%' . $request->session()->get('search_condition.client_original_name') . '%');
        }

        // 表示順
        $sort = 'id_desc';
        if ($request->session()->has('search_condition.sort')) {
            $sort = session('search_condition.sort');
        }
        if ($sort == 'id_asc') {
            $uploads_query->orderBy('id', 'asc');
        } elseif ($sort == 'id_desc') {
            $uploads_query->orderBy('id', 'desc');
        } elseif ($sort == 'client_original_name_asc') {
            $uploads_query->orderBy('client_original_name', 'asc');
        } elseif ($sort == 'client_original_name_desc') {
            $uploads_query->orderBy('client_original_name', 'desc');
        } elseif ($sort == 'size_asc') {
            $uploads_query->orderBy('size', 'asc');
        } elseif ($sort == 'size_desc') {
            $uploads_query->orderBy('size', 'desc');
        } elseif ($sort == 'created_at_asc') {
            $uploads_query->orderBy('created_at', 'asc');
        } elseif ($sort == 'created_at_desc') {
            $uploads_query->orderBy('created_at', 'desc');
        } elseif ($sort == 'download_count_desc') {
            $uploads_query->orderBy('download_count', 'desc');
        }

        // データ取得
        $uploads = $uploads_query->paginate(10, null, 'page', $page);

        // 入力値をsessionへ保存（検索用）
        $request->flash();

        // 画面呼び出し
        return view('plugins.manage.uploadfile.index', [
            "function"    => __FUNCTION__,
            "plugin_name" => "uploadfile",
            "uploads"     => $uploads,
        ]);
    }

    /**
     *  検索条件設定処理
     */
    public function search($request)
    {
        // 検索ボタンが押されたときはここが実行される。検索条件を設定してindex を呼ぶ。
        $search_condition = [
            "client_original_name" => $request->input('search_condition.client_original_name'),
            "sort"                 => $request->input('search_condition.sort'),
        ];

        session(["search_condition" => $search_condition]);

        return redirect("/manage/uploadfile");
    }

    /**
     *  検索条件クリア処理
     */
    public function clearSearch($request)
    {
        // 検索条件をクリアし、index 処理を呼ぶ。
        $request->session()->forget('search_condition');
        return $this->index($request);
    }

    /**
     *  編集画面
     */
    public function edit($request, $uploads_id)
    {
        // アップロードファイルを検索
        $upload = Uploads::select('uploads.*', 'plugins.plugin_name_full', 'pages.page_name', 'pages.permanent_link')
                         ->leftJoin('plugins', 'plugins.plugin_name', '=', 'uploads.plugin_name')
                         ->leftJoin('pages', 'pages.id', '=', 'uploads.page_id')
                         ->where('uploads.id', $uploads_id)
                         ->first();

        // 画面呼び出し
        return view('plugins.manage.uploadfile.edit', [
            "function"    => __FUNCTION__,
            "plugin_name" => "uploadfile",
            "upload"      => $upload,
        ]);
    }

    /**
     *  保存
     */
    public function save($request, $uploads_id)
    {
        // 項目のエラーチェック
        $validator = Validator::make($request->all(), [
            'client_original_name' => ['required'],
        ]);
        $validator->setAttributeNames([
            'client_original_name' => 'ファイル名',
        ]);

        // エラーがあった場合は入力画面に戻る。
        if ($validator->fails()) {
            return redirect()->back()->withErrors($validator)->withInput();
        }

        // アップロードファイルを検索
        $upload = Uploads::find($uploads_id);

        // 入力値の設定
        $filename = pathinfo(basename($request->client_original_name), PATHINFO_FILENAME);
        if (empty($filename) || $filename == '.') {
            $validator->errors()->add('client_original_name', 'ファイル名にエラーがあります。');
            return redirect()->back()->withErrors($validator)->withInput();
        }

        $upload->client_original_name = $filename . '.' . $upload->extension;
        $upload->save();

        return redirect("/manage/uploadfile/edit/" . $uploads_id)->with('info_message', '更新しました。');
    }
}
