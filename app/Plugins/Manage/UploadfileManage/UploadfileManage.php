<?php

namespace App\Plugins\Manage\UploadfileManage;

use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Log;

use App\Models\Common\Uploads;
use App\Models\Core\Configs;
use App\Utilities\Storage\StorageUsageCalculator;

use App\Plugins\Manage\ManagePluginBase;
use App\Traits\ConnectCommonTrait;

use Symfony\Component\Finder\Exception\DirectoryNotFoundException;

/**
 * アップロードファイル管理クラス
 *
 * @author 永原　篤 <nagahara@opensource-workshop.jp>
 * @author 牟田口 満 <mutaguchi@opensource-workshop.jp>
 * @copyright OpenSource-WorkShop Co.,Ltd. All Rights Reserved
 * @category アップロードファイル管理
 * @package Controller
 * @plugin_title アップロードファイル管理
 * @plugin_desc アップロードファイルに関する機能が集まった管理機能です。
 */
class UploadfileManage extends ManagePluginBase
{
    use ConnectCommonTrait;

    /**
     * 表示件数の許可された値
     */
    private $allowed_per_page = [10, 50, 100];

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
        $role_ckeck_table["delete"]      = array('admin_site');
        $role_ckeck_table["userdir"]     = array('admin_site');
        $role_ckeck_table["saveUserdir"] = array('admin_site');
        $role_ckeck_table["userdirPublic"] = array('admin_site');
        $role_ckeck_table["deleteUserdirPublic"] = array('admin_site');
        $role_ckeck_table["bulkDelete"] = array('admin_site');
        return $role_ckeck_table;
    }

    /**
     *  ページ初期表示
     *
     * @return view
     * @method_title アップロードファイル一覧
     * @method_desc アップロードファイルを一覧で確認できます。
     * @method_detail アップロードファイルのサイズなどの情報が確認できます。
     */
    public function index($request)
    {
        /* ページの処理（セッション）
        ----------------------------------------------*/

        // 表示ページ数。詳細で更新して戻ってきたら、元と同じページを表示したい。
        // セッションにあればページの指定があれば使用。
        // ただし、リクエストでページ指定があればそれが優先。(ページング操作)
        $page = $this->getPaginatePageFromRequestOrSession($request, 'search_condition.page', 'page');

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

        // 表示件数の取得 ※デフォルトは10件
        $per_page = $this->allowed_per_page[0];
        if ($request->session()->has('uploadfile_per_page')) {
            $per_page = (int)$request->session()->get('uploadfile_per_page');
            // 許可された値のみを使用
            if (!in_array($per_page, $this->allowed_per_page)) {
                $per_page = $this->allowed_per_page[0];
            }
        }

        // データ取得
        $uploads = $uploads_query->paginate($per_page, null, 'page', $page);

        // データ使用量の計算
        $storage_usage = StorageUsageCalculator::getDataUsage();

        // 入力値をsessionへ保存（検索用）
        $request->flash();

        // 画面呼び出し
        return view('plugins.manage.uploadfile.index', [
            "function"    => __FUNCTION__,
            "plugin_name" => "uploadfile",
            "uploads"     => $uploads,
            "allowed_per_page" => $this->allowed_per_page,
            "storage_usage" => $storage_usage,
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

        // 表示件数は独立して保存（条件設定中バッジの対象外）
        if ($request->has('uploadfile_per_page')) {
            session(['uploadfile_per_page' => $request->input('uploadfile_per_page')]);
        }

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
     *
     * @method_title アップロードファイル編集
     * @method_desc アップロードファイルのファイル名の編集と削除ができます。
     * @method_detail アップロードファイルの詳細情報を確認することができます。
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
        $upload->temporary_flag       = $request->temporary_flag;
        $upload->save();

        return redirect("/manage/uploadfile/edit/" . $uploads_id)->with('info_message', '更新しました。');
    }

    /**
     *  削除
     */
    public function delete($request, $uploads_id)
    {
        $upload = Uploads::findOrFail($uploads_id);

        // ファイル削除
        Storage::delete($this->getDirectory($upload->id) . '/' . $upload->id . '.' .$upload->extension);

        // レコード削除
        $upload->delete();

        return redirect('/manage/uploadfile/')->with('flash_message', '削除しました。(ID:' . $upload->id . ', ファイル名:' . $upload->client_original_name . ')');
    }

    /**
     *  ユーザファイルの設定画面
     *
     * @method_title ユーザファイルの設定
     * @method_desc SCP等でアップロードしたファイルをConnect-CMSで制御する設定です。
     * @method_detail
     */
    public function userdir($request)
    {
        // storage/user の下のディレクトリ参照
        // この時点で、storage/user がなければ、作成される。
        $user_directories = Storage::disk('user')->directories();

        // Config のユーザディレクトリ許可設定
        $userdir_allows = Configs::where('category', 'userdir_allow')->get();

        // 画面呼び出し
        return view('plugins.manage.uploadfile.userdir', [
            "function"    => __FUNCTION__,
            "plugin_name" => "uploadfile",
            "user_directories" => $user_directories,
            "userdir_allows" => $userdir_allows,
        ]);
    }

    /**
     *  ユーザファイルの設定を保存
     */
    public function saveUserdir($request)
    {
        // 入力内容が存在するかのチェック
        if (!$request->has('userdir') || !is_array($request->userdir)) {
            return $this->userdir($request);
        }

        // storage/user の下のディレクトリ参照。リクエストされて、ここにあるもののみ、保存対象
        // リクエストの不正防止
        $user_directories = Storage::disk('user')->directories();

        // リクエストのuserdir をループして、閲覧制限値を設定する。
        foreach ($request->userdir as $userdir => $value) {
            if (in_array($userdir, $user_directories)) {
                // ユーザファイルの設定を保存(念のため、ディレクトリはbasename関数で安全性の確保)
                $config = Configs::updateOrCreate(
                    ['category' => 'userdir_allow', 'name' => basename($userdir)],
                    ['value' => $value]
                );
            }
        }

        return redirect("/manage/uploadfile/userdir")->with('info_message', '更新しました。');
    }

    /**
     * ユーザパブリックファイルの一覧画面
     */
    public function userdirPublic($request)
    {
        $manage_userdir_public_target = config('connect.MANAGE_USERDIR_PUBLIC_TARGET');
        $manage_userdir_public_target = basename($manage_userdir_public_target);
        if (empty($manage_userdir_public_target)) {
            session()->flash('flash_error_message', '設定値が空です');

            // 空の場合はファイル表示しない。
            return view('plugins.manage.uploadfile.userdir_public', [
                "function"    => __FUNCTION__,
                "plugin_name" => "uploadfile",
                "manage_userdir_public_target" => $manage_userdir_public_target,
                "files" => [],
            ]);
        }

        $path = public_path($manage_userdir_public_target);
        $files = [];
        try {
            $files = File::allFiles($path);
        } catch (DirectoryNotFoundException $e) {
            // 指定ディレクトリなし
            session()->flash('flash_error_message', '指定されたディレクトリがありません。' . $path);
        }

        return view('plugins.manage.uploadfile.userdir_public', [
            "function"    => __FUNCTION__,
            "plugin_name" => "uploadfile",
            "manage_userdir_public_target" => $manage_userdir_public_target,
            "files" => $files,
        ]);
    }

    /**
     * ユーザパブリックファイルの削除削除
     */
    public function deleteUserdirPublic($request)
    {
        // 入力内容が存在するかのチェック
        if (!$request->has('delete_files') || !is_array($request->delete_files)) {
            return $this->userdirPublic($request);
        }

        $manage_userdir_public_target = config('connect.MANAGE_USERDIR_PUBLIC_TARGET');
        $manage_userdir_public_target = basename($manage_userdir_public_target);
        if (empty($manage_userdir_public_target)) {
            return $this->userdirPublic($request);
        }

        // public/uploads の下のディレクトリ参照。リクエストされて、ここにあるもののみ、削除対象
        // リクエストの不正防止
        $path = public_path($manage_userdir_public_target);
        $files = File::allFiles($path);
        $files_all = [];
        foreach ($files as $file) {
            $files_all[] = $file->getPathname();
        }

        foreach ($request->delete_files as $delete_file) {
            if (in_array($delete_file, $files_all)) {
                File::delete($delete_file);
            }
        }

        return redirect("/manage/uploadfile/userdirPublic")->with('flash_message', '削除しました。');
    }

    /**
     * アップロードファイルの一括削除処理
     *
     * @param $request
     */
    public function bulkDelete($request)
    {
        // 選択されたファイルIDの確認
        if (!$request->has('selected_files') || !is_array($request->selected_files)) {
            return redirect('/manage/uploadfile/')->with('flash_error_message', '削除するファイルが選択されていません。');
        }

        $deleted_files = [];
        $error_files = [];

        foreach ($request->selected_files as $upload_id) {
            try {
                $upload = Uploads::findOrFail($upload_id);
                
                // ファイル削除
                Storage::delete($this->getDirectory($upload->id) . '/' . $upload->id . '.' . $upload->extension);
                
                // レコード削除
                $deleted_files[] = $upload->client_original_name;
                $upload->delete();
                
            } catch (\Exception $e) {
                Log::error('UploadfileManage: Bulk delete failed for file ID: ' . $upload_id, ['error' => $e->getMessage()]);
                $error_files[] = "ID:" . $upload_id;
            }
        }

        $message = count($deleted_files) . "件のファイルを削除しました。";
        if (!empty($error_files)) {
            $message .= " ※削除に失敗したファイル: " . implode(', ', $error_files);
            Log::error('UploadfileManage: Bulk delete failed for files: ' . implode(', ', $error_files));
        }

        return redirect('/manage/uploadfile/')->with('flash_message', $message);
    }
}
