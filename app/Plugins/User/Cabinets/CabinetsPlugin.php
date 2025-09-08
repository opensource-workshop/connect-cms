<?php

namespace App\Plugins\User\Cabinets;

use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;

use App\Models\Common\Buckets;
use App\Models\Common\Frame;
use App\Models\Common\Page;
use App\Models\Common\PageRole;
use App\Models\Common\Uploads;
use App\Models\Core\FrameConfig;
use App\Models\User\Cabinets\Cabinet;
use App\Models\User\Cabinets\CabinetContent;

use App\Enums\UploadMaxSize;
use App\Enums\CabinetFrameConfig;
use App\Enums\CabinetSort;

use App\Plugins\User\UserPluginBase;
use App\Utilities\Zip\UnzipUtils;

// use function PHPUnit\Framework\isEmpty;

/**
 * キャビネット・プラグイン
 *
 * @author 石垣 佑樹 <ishigaki@opensource-workshop.jp>
 * @copyright OpenSource-WorkShop Co.,Ltd. All Rights Reserved
 * @category キャビネット・プラグイン
 * @package Controller
 * @plugin_title キャビネット
 * @plugin_desc キャビネットを作成できるプラグインです。ファイルの管理に使用します。
 */
class CabinetsPlugin extends UserPluginBase
{

    /* オブジェクト変数 */

    /**
     * POST チェックに使用する getPost() 関数を使うか
     * [TODO] 現在（2021/11/10）は、管理者がアップしたファイルも、編集者が削除できるため、getPost()は使わない設定にする。
     *        今後自分がアップしたファイルのみ削除できるように見直しする想定で、その時は getPost()を使うと予想。
     */
    public $use_getpost = false;

    // ファイルダウンロードURL
    private $download_url = '';

    /* コアから呼び出す関数 */

    /**
     * 関数定義（コアから呼び出す）
     */
    public function getPublicFunctions()
    {
        // 標準関数以外で画面などから呼ばれる関数の定義
        $functions = array();
        $functions['get']  = ['index', 'download', 'changeDirectory'];
        $functions['post'] = ['makeFolder', 'upload', 'deleteContents', 'rename'];
        return $functions;
    }

    /**
     *  権限定義
     */
    public function declareRole()
    {
        // 権限チェックテーブル
        $role_check_table = array();
        $role_check_table["upload"] = array('posts.create');
        $role_check_table["makeFolder"] = array('posts.create');
        $role_check_table["deleteContents"] = array('posts.delete');
        $role_check_table["rename"] = array('posts.update');
        return $role_check_table;
    }

    /**
     * プラグインのバケツ取得関数
     */
    private function getPluginBucket($bucket_id)
    {
        // プラグインのメインデータを取得する。
        return Cabinet::firstOrNew(['bucket_id' => $bucket_id]);
    }

    /* 画面アクション関数 */

    /**
     *  データ初期表示関数
     *  コアがページ表示の際に呼び出す関数
     *
     * @method_title ファイル一覧
     * @method_desc ファイルやフォルダを一覧表示します。
     * @method_detail ファイルをクリックすることでダウンロードできます。
     */
    public function index($request, $page_id, $frame_id, $parent_id = null)
    {
        // バケツ未設定の場合はバケツ空テンプレートを呼び出す
        if (!isset($this->frame) || !$this->frame->bucket_id) {
            // バケツ空テンプレートを呼び出す。
            return $this->view('empty_bucket');
        }

        $cabinet = $this->getPluginBucket($this->frame->bucket_id);

        $parent = $this->fetchCabinetContent($parent_id, $cabinet->id);

        // cabinet_idが配置バケツと違う場合、表示させない
        if (empty($parent) || $parent->cabinet_id != $cabinet->id) {
            return;
        }

        // 表示テンプレートを呼び出す。
        return $this->view('index', [
            'cabinet' => $cabinet,
            'cabinet_contents' => $parent->children()->get()->sort(function ($first, $second) {
                // フォルダ>ファイル

                if ($first['is_folder'] == $second['is_folder']) {
                    // フォルダ同士 or ファイル同士を比較

                    $sort = FrameConfig::getConfigValue($this->frame_configs, CabinetFrameConfig::sort);

                    if ($sort == '' || $sort == CabinetSort::name_asc) {
                        // 名前（昇順）
                        // return $first['displayName'] < $second['displayName'] ? -1 : 1;
                        return $this->sortAsc($first['displayName'], $second['displayName']);
                    } elseif ($sort == CabinetSort::name_desc) {
                        // 名前（降順）
                        return $this->sortDesc($first['displayName'], $second['displayName']);
                    // } elseif ($sort == CabinetSort::created_asc) {
                    //     // 登録日（昇順）
                    //     return $this->sortAsc($first['created_at'], $second['created_at']);
                    // } elseif ($sort == CabinetSort::created_desc) {
                    //     // 登録日（降順）
                    //     return $this->sortDesc($first['created_at'], $second['created_at']);
                    } elseif ($sort == CabinetSort::updated_asc) {
                        // 更新日（昇順）
                        return $this->sortAsc($first['updated_at'], $second['updated_at']);
                    } elseif ($sort == CabinetSort::updated_desc) {
                        // 更新日（降順）
                        return $this->sortDesc($first['updated_at'], $second['updated_at']);
                    }
                }
                // フォルダとファイルの比較
                // ファイル(is_folder=0)よりフォルダ(is_folder=1)を上（降順）にする
                // return $first['is_folder'] < $second['is_folder'] ? 1 : -1;
                return $this->sortDesc($first['is_folder'], $second['is_folder']);
            }),
            'breadcrumbs' => $this->fetchBreadCrumbs($cabinet->id, $parent->id),
            'parent_id' =>  $parent->id,
        ]);
    }

    /**
     * フォルダを移動する
     */
    public function changeDirectory($request, $page_id, $frame_id, $parent_id)
    {
        return $this->index($request, $page_id, $frame_id, $parent_id);
    }

    /**
     * キャビネットコンテンツを取得する
     *
     * @param int $cabinet_content_id キャビネットコンテンツID
     * @param int $cabinet_id キャビネットID
     * @return \App\Models\User\Cabinets\CabinetContent キャビネットコンテンツ
     */
    private function fetchCabinetContent($cabinet_content_id, $cabinet_id = null)
    {
        // cabinet_content_idがなければ、ルート要素を返す
        if (empty($cabinet_content_id)) {
            return CabinetContent::where('cabinet_id', $cabinet_id)->where('parent_id', null)->first();
        }
        return CabinetContent::find($cabinet_content_id);
    }

    /**
     * パンくずリスト（ファルダ階層）を取得する
     *
     * @param int $cabinet_content_id キャビネットコンテンツID
     * @param int $cabinet_id キャビネットID
     * @return \Illuminate\Support\Collection キャビネットコンテンツのコレクション
     */
    private function fetchBreadCrumbs($cabinet_id, $cabinet_content_id = null)
    {
        // 初期表示はルート要素のみ
        if (empty($cabinet_content_id)) {
            return CabinetContent::where('cabinet_id', $cabinet_id)
                ->where('parent_id', null)
                ->get();
        }
        return CabinetContent::ancestorsAndSelf($cabinet_content_id);
    }

    /**
     * コレクションのsortメソッドでコールバック使用時の昇順処理
     *
     * firstが小さい時(-1), firstが大きい時(1)  = 昇順
     * firstが小さい時(1),  firstが大きい時(-1) = 降順
     *
     * @param int $first
     * @param int $second
     * @return int
     * @see https://readouble.com/laravel/6.x/ja/collections.html#method-some
     * @see https://www.php.net/manual/ja/function.uasort.php
     */
    private function sortAsc($first, $second)
    {
        if ($first == $second) {
            return 0;
        }
        return $first < $second ? -1 : 1;
    }

    /**
     * コレクションのsortメソッドでコールバック使用時の降順処理
     *
     * firstが小さい時(-1), firstが大きい時(1)  = 昇順
     * firstが小さい時(1),  firstが大きい時(-1) = 降順
     *
     * @param int $first
     * @param int $second
     * @return int
     * @see https://readouble.com/laravel/6.x/ja/collections.html#method-some
     * @see https://www.php.net/manual/ja/function.uasort.php
     */
    private function sortDesc($first, $second)
    {
        if ($first == $second) {
            return 0;
        }
        return $first < $second ? 1 : -1;
    }

    /**
     * フォルダ作成処理
     *
     * @param \Illuminate\Http\Request $request リクエスト
     * @param int $page_id ページID
     * @param int $frame_id フレームID
     * @method_title フォルダ作成
     * @method_desc フォルダを作成できます。
     * @method_detail フォルダの中にもフォルダを作成できます。
     */
    public function makeFolder($request, $page_id, $frame_id)
    {
        $validator = $this->getMakeFoldertValidator($request);
        if ($validator->fails()) {
            return back()->withErrors($validator)->withInput();
        }

        $cabinet = $this->getPluginBucket($this->frame->bucket_id);
        $parent = $this->fetchCabinetContent($request->parent_id);

        $parent->children()->create([
            'cabinet_id' => $cabinet->id,
            'upload_id' => null,
            'name' => $request->folder_name[$frame_id],
            'is_folder' => CabinetContent::is_folder_on,
        ]);

        // 登録後はリダイレクトして初期表示。
        return new Collection(['redirect_path' => url('/') . "/plugin/cabinets/changeDirectory/" . $page_id . "/" . $frame_id  . "/" . $parent->id . "/#frame-" . $frame_id ]);
    }

    /**
     * ファイルアップロード処理
     *
     * @param \Illuminate\Http\Request $request リクエスト
     * @param int $page_id ページID
     * @param int $frame_id フレームID
     * @method_title アップロード
     * @method_desc ファイルをアップロードできます。
     * @method_detail ルート階層にも、フォルダの中にもファイルをアップロードできます。
     */
    public function upload($request, $page_id, $frame_id)
    {
        $cabinet = $this->getPluginBucket($this->frame->bucket_id);
        $validator = $this->getUploadValidator($request, $cabinet);
        if ($validator->fails()) {
            return back()->withErrors($validator)->withInput();
        }

        $parent = $this->fetchCabinetContent($request->parent_id);

        if ($request->has('zip_deploy') && $request->zip_deploy) {
            // Zip展開
            $zip_path = $request->file('upload_file')[$frame_id]->storeAs('tmp/cabinet', uniqid('', true) . '.zip');
            $unzip_path = 'tmp/cabinet/' . uniqid('', true);
            if (!UnzipUtils::unzip(storage_path('app/') . $zip_path, storage_path('app/') . $unzip_path)) {
                // Redirect時のエラー対応. リダイレクトせずエラー画面表示する。
                $request->merge(['return_mode' => 'asis']);
                return $this->viewError('500_inframe', null, 'unzip error');
            }
            $this->saveCabinetContentsRecursive($parent, $unzip_path);
            // 一時ファイルを削除する
            Storage::delete($zip_path);
            Storage::deleteDirectory($unzip_path);
        } else {
            // そのままアップロード
            $upload_file = $request->file('upload_file')[$frame_id];
            if ($this->shouldOverwriteFile($parent, $upload_file->getClientOriginalName())) {
                $this->overwriteUploadedFile($upload_file, $page_id, $parent);
            } else {
                $this->writeUploadedFile($upload_file, $page_id, $parent);
            }
        }

        // 登録後はリダイレクトして初期表示。
        return new Collection(['redirect_path' => url('/') . "/plugin/cabinets/changeDirectory/" . $page_id . "/" . $frame_id . "/" . $parent->id . "/#frame-" . $frame_id ]);
    }

    /**
     * 指定ディレクト配下の中身をキャビネットに登録する。
     *
     * @param CabinetContent $parent 親ディレクトリ
     * @param string $source_directory ディレクトリパス
     */
    private function saveCabinetContentsRecursive(CabinetContent $parent, string $source_directory)
    {
        // ディレクトリをキャビネットに登録する
        $directories = Storage::directories($source_directory);
        foreach ($directories as $directory) {
            // 同名フォルダがすでに登録されていれば新たに登録せず既存のデータを利用する
            $created_parent = CabinetContent::where('parent_id', $parent->id)
                ->where('name', basename($directory))
                ->where('is_folder', CabinetContent::is_folder_on)
                ->first();

            if ($created_parent === null) {
                $created_parent = $parent->children()->create([
                    'cabinet_id' => $parent->cabinet_id,
                    'upload_id' => null,
                    'name' => basename($directory),
                    'is_folder' => CabinetContent::is_folder_on,
                ]);
            }
            $this->saveCabinetContentsRecursive($created_parent, $directory);
        }

        // ファイルをキャビネットに登録する
        $files = Storage::files($source_directory);
        foreach ($files as $file) {
            if ($this->shouldOverwriteFile($parent, basename($file))) {
                $this->overwriteFile($file, $parent);
            } else {
                $this->writeFile($file, $parent);
            }
        }
    }

    /**
     * ファイルを上書きすべきか
     *
     * @param \App\Models\User\Cabinets\CabinetContent $parent 親要素
     * @param string $file_name アップロードするファイル名
     * @return bool
     */
    private function shouldOverwriteFile($parent, $file_name)
    {
        return CabinetContent::where('parent_id', $parent->id)
            ->where('name', $file_name)
            ->where('is_folder', CabinetContent::is_folder_off)
            ->exists();
    }

    /**
     * ファイル新規保存処理（UploadedFileを保存する）
     *
     * @param \Illuminate\Http\UploadedFile $file file
     * @param int $page_id ページID
     * @param int $frame_id フレームID
     */
    private function writeUploadedFile($file, $page_id, $parent)
    {
        // uploads テーブルに情報追加、ファイルのid を取得する
        $upload = Uploads::create([
            'client_original_name' => $file->getClientOriginalName(),
            'mimetype'             => $file->getClientMimeType(),
            'extension'            => $file->getClientOriginalExtension(),
            'size'                 => $file->getSize(),
            'plugin_name'          => 'cabinets',
            'page_id'              => $page_id,
            'temporary_flag'       => 0,
            'created_id'           => empty(Auth::user()) ? null : Auth::user()->id,
        ]);

        // ファイル保存
        $file->storeAs($this->getDirectory($upload->id), $this->getContentsFileName($upload));

        $parent->children()->create([
            'cabinet_id' => $upload->id,
            'upload_id' => $upload->id,
            'name' => $file->getClientOriginalName(),
            'is_folder' => CabinetContent::is_folder_off,
        ]);
    }

    /**
     * ファイル上書き保存処理（UploadedFileを保存する）
     *
     * @param \Illuminate\Http\UploadedFile $file file
     * @param int $page_id ページID
     * @param int $frame_id フレームID
     */
    private function overwriteUploadedFile($file, $page_id, $parent)
    {
        $content = CabinetContent::where('parent_id', $parent->id)
            ->where('name', $file->getClientOriginalName())
            ->where('is_folder', CabinetContent::is_folder_off)
            ->first();

        // uploads テーブルに情報追加、ファイルのid を取得する
        Uploads::find($content->upload_id)->update([
            'client_original_name' => $file->getClientOriginalName(),
            'mimetype'             => $file->getClientMimeType(),
            'extension'            => $file->getClientOriginalExtension(),
            'size'                 => $file->getSize(),
            'plugin_name'          => 'cabinets',
            'page_id'              => $page_id,
            'temporary_flag'       => 0,
            'updated_id'           => empty(Auth::user()) ? null : Auth::user()->id,
        ]);

        // ファイル保存
        $file->storeAs($this->getDirectory($content->upload_id), $this->getContentsFileName($content->upload));

        // 画面表示される更新日を更新する
        $content->touch();
    }

    /**
     * ファイル新規保存処理
     *
     * @param string $file ファイルパス
     * @param CabinetContent $parent 保存先のフォルダ
     */
    private function writeFile(string $file, CabinetContent $parent)
    {
        // uploads テーブルに情報追加、ファイルのid を取得する
        $upload = Uploads::create([
            'client_original_name' => basename($file),
            'mimetype'             => Storage::mimeType($file),
            'extension'            => pathinfo($file, PATHINFO_EXTENSION),
            'size'                 => Storage::size($file),
            'plugin_name'          => 'cabinets',
            'page_id'              => $this->page->id,
            'temporary_flag'       => 0,
            'created_id'           => empty(Auth::user()) ? null : Auth::user()->id,
        ]);

        // ファイル保存
        Storage::move($file, $this->getDirectory($upload->id) . '/' . $this->getContentsFileName($upload));

        $parent->children()->create([
            'cabinet_id' => $upload->id,
            'upload_id' => $upload->id,
            'name' => basename($file),
            'is_folder' => CabinetContent::is_folder_off,
        ]);
    }

    /**
     * ファイル上書き保存処理
     *
     * @param string $file ファイルパス
     * @param CabinetContent $parent 保存先のフォルダ
     */
    private function overwriteFile(string $file, CabinetContent $parent)
    {
        $content = CabinetContent::where('parent_id', $parent->id)
                    ->where('name', basename($file))
                    ->where('is_folder', CabinetContent::is_folder_off)
                    ->first();

        // uploads テーブルに情報追加、ファイルのid を取得する
        Uploads::find($content->upload_id)->update([
            'client_original_name' => basename($file),
            'mimetype'             => Storage::mimeType($file),
            'extension'            => pathinfo($file, PATHINFO_EXTENSION),
            'size'                 => Storage::size($file),
            'plugin_name'          => 'cabinets',
            'page_id'              => $this->page->id,
            'temporary_flag'       => 0,
            'updated_id'           => empty(Auth::user()) ? null : Auth::user()->id,
        ]);

        // ファイル保存
        $move_to = $this->getDirectory($content->upload_id) . '/' . $this->getContentsFileName($content->upload);
        if (Storage::exists($move_to)) {
            Storage::delete($move_to);
        }
        Storage::move($file, $move_to);

        // 画面表示される更新日を更新する
        $content->touch();
    }

    /**
     *  コンテンツ削除処理
     *
     * @param \Illuminate\Http\Request $request リクエスト
     * @param int $page_id ページID
     * @param int $frame_id フレームID
     * @method_title ファイル削除
     * @method_desc ファイルやフォルダを削除できます。
     * @method_detail
     */
    public function deleteContents($request, $page_id, $frame_id)
    {
        $validator = $this->getContentsControlValidator($request);
        if ($validator->fails()) {
            return back()->withErrors($validator)->withInput();
        }

        foreach ($request->cabinet_content_id as $cabinet_content_id) {
            $contents = CabinetContent::descendantsAndSelf($cabinet_content_id);
            if (!$this->canDelete($request, $contents)) {
                abort(403, '権限がありません。');
            };

            $this->deleteCabinetContents($cabinet_content_id, $contents);
        }

        // 登録後はリダイレクトして初期表示。
        return new Collection(['redirect_path' => url('/') . "/plugin/cabinets/changeDirectory/" . $page_id . "/" . $frame_id . "/" . $request->parent_id . "/#frame-" . $frame_id ]);
    }

    /**
     *  名前変更処理
     *
     * @param \Illuminate\Http\Request $request リクエスト
     * @param int $page_id ページID
     * @param int $frame_id フレームID
     * @method_title 名前変更
     * @method_desc ファイルやフォルダの名前を変更できます。
     * @method_detail
     */
    public function rename($request, $page_id, $frame_id)
    {
        $validator = $this->getRenameValidator($request);
        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $cabinet_content = CabinetContent::with('upload')->find($request->cabinet_content_id);
        if (!$cabinet_content) {
            return response()->json(['message' => 'ファイルまたはフォルダが見つかりません。'], 404);
        }

        // 同じ親の下で同じ名前のファイル/フォルダが存在しないかチェック
        $exists = CabinetContent::where('parent_id', $cabinet_content->parent_id)
            ->where('name', $request->new_name)
            ->where('id', '!=', $cabinet_content->id)
            ->exists();

        if ($exists) {
            return response()->json(['message' => '同じ名前のファイルまたはフォルダが既に存在します。'], 422);
        }

        // ファイルの場合は拡張子が変更されないことをチェック
        if ($cabinet_content->upload_id) {
            $original_extension = pathinfo($cabinet_content->name, PATHINFO_EXTENSION);
            $new_extension = pathinfo($request->new_name, PATHINFO_EXTENSION);

            if ($original_extension !== $new_extension) {
                return response()->json(['message' => 'ファイルの拡張子は変更できません。'], 422);
            }
        }

        // 名前変更
        $cabinet_content->name = $request->new_name;
        $cabinet_content->save();
        if ($cabinet_content->upload_id) {
            // アップロードファイルの名前も変更
            $upload = $cabinet_content->upload;
            $upload->client_original_name = $request->new_name;
            $upload->save();
        }

        return response()->json(['message' => '名前を変更しました。']);
    }

    /**
     * キャビネットコンテンツを再帰的に削除する
     *
     * @param int $cabinet_content_id キャビネットコンテンツID
     * @param \Illuminate\Support\Collection $cabinet_contents キャビネットコンテンツのコレクション
     */
    private function deleteCabinetContents($cabinet_content_id, $cabinet_contents)
    {
        // アップロードテーブル削除、実ファイルの削除
        foreach ($cabinet_contents->whereNotNull('upload_id') as $content) {
            Storage::delete($this->getContentsFilePath($content->upload));
            Uploads::destroy($content->upload->id);
        }

        // キャビネットコンテンツの削除（再帰）
        CabinetContent::find($cabinet_content_id)->delete();
    }

    /**
     * ダウンロード処理
     *
     * @param \Illuminate\Http\Request $request リクエスト
     * @param int $page_id ページID
     * @param int $frame_id フレームID
     */
    public function download($request, $page_id, $frame_id)
    {
        $validator = $this->getContentsControlValidator($request);
        if ($validator->fails()) {
            return back()->withErrors($validator)->withInput()->with('parent_id', $request->parent_id);
        }

        // ファイルの単数選択ならZIP化せずダウンロードレスポンスを返す
        if ($this->isSelectedSingleFile($request)) {
            return redirect($this->download_url);
        }

        $save_path = $this->getTmpDirectory() . uniqid('', true) . '.zip';
        $this->makeZip($save_path, $request);

        // 一時ファイルは削除して、ダウンロードレスポンスを返す. download()でAllowed memory sizeエラー時にtmpファイル削除対応
        $response = response()->download(
            $save_path,
            'Files.zip',
            ['Content-Disposition' => 'filename=Files.zip']
        );
        // )->deleteFileAfterSend(true);
        register_shutdown_function('unlink', $save_path);
        return $response;
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

        foreach ($request->cabinet_content_id as $cabinet_content_id) {
            $contents = CabinetContent::descendantsAndSelf($cabinet_content_id);
            if (!$this->canDownload($request, $contents)) {
                // zipファイル後始末
                $zip->close();
                if (file_exists($save_path)) {
                    unlink($save_path);
                }
                abort(403, 'ファイル参照権限がありません。');
            }
            // フォルダがないとzipファイルを作れない
            if (!is_dir($this->getTmpDirectory())) {
                mkdir($this->getTmpDirectory(), 0777, true);
            }

            $this->addContentsToZip($zip, $contents->toTree());
        }

        // 空のZIPファイルが出来たら404
        if ($zip->count() === 0) {
            // zipファイル後始末
            $zip->close();
            // Storage::delete() & xamppの場合、以下のパスになり、ファイルがあっても、ファイルなしとして扱われたため、unlink()を使う
            // $save_path = "C:\projects\connect-cms\htdocs\connect-cms\storage\app/tmp/cabinet/62da6ae9d8a013.24929254.zip"
            if (file_exists($save_path)) {
                unlink($save_path);
            }
            abort(404, 'ファイルがありません。');
        }
        $zip->close();
    }

    /**
     * ZIPファイルにフォルダ、ファイルを追加する。
     *
     * @param \ZipArchive $zip ZIPアーカイブ
     * @param \Illuminate\Support\Collection $contents キャビネットコンテンツのコレクション
     * @param string $parent_name 親キャビネットの名称
     */
    private function addContentsToZip(&$zip, $contents, $parent_name = '')
    {
        // 保存先のパス
        $save_path = $parent_name === '' ? $parent_name : $parent_name .'/';

        foreach ($contents as $content) {
            // ファイルが格納されていない空のフォルダだったら、空フォルダを追加
            if ($content->is_folder === CabinetContent::is_folder_on && $content->isLeaf()) {
                $zip->addEmptyDir($save_path . $content->name);

            // ファイル追加
            } elseif ($content->is_folder === CabinetContent::is_folder_off) {
                // データベースがない場合はスキップ
                if (empty($content->upload)) {
                    continue;
                }
                // ファイルの実体がない場合はスキップ
                if (!Storage::exists($this->getContentsFilePath($content->upload))) {
                    continue;
                }
                $zip->addFile(
                    storage_path('app/') . $this->getContentsFilePath($content->upload),
                    $save_path . $content->name
                );
                // ダウンロード回数をカウントアップ
                Uploads::find($content->upload->id)->increment('download_count');
            }
            $this->addContentsToZip($zip, $content->children, $save_path . $content->name);
        }
    }

    /**
     * 単数のファイルが選択されたか
     *
     * @param \Illuminate\Http\Request $request リクエスト
     * @return bool
     */
    private function isSelectedSingleFile($request)
    {
        // 複数選択された
        if (is_array($request->cabinet_content_id) && count($request->cabinet_content_id) !== 1) {
            return false;
        }

        // フォルダが選択された
        $cabinet_content = CabinetContent::find($request->cabinet_content_id[0]);
        if ($cabinet_content->is_folder === CabinetContent::is_folder_on) {
            return false;
        }

        // 単数ファイルダウンロード用パスを設定しておく
        $this->download_url = "/file/" . $cabinet_content->upload_id;

        return true;
    }

    /**
     * 一時フォルダのパスを取得する
     *
     * @return string 一時フォルダのパス
     */
    private function getTmpDirectory()
    {
        return storage_path('app/') . 'tmp/cabinet/';
    }

    /**
     * キャビネットに格納されている実ファイルのパスを取得する
     *
     * @return string ファイルのフルパス
     */
    private function getContentsFilePath($upload)
    {
        return $this->getDirectory($upload->id) . '/' . $this->getContentsFileName($upload);
    }

    /**
     * キャビネットに格納されている実ファイルの名称を取得する
     *
     * @param \App\Models\Common\Uploads $upload アップロード
     * @return string 物理ファイル名
     */
    private function getContentsFileName($upload)
    {
        return $upload->id . '.' . $upload->extension;
    }

    /**
     * キャビネットコンテンツを削除処理をできるか
     *
     * @param \Illuminate\Http\Request $request リクエスト
     * @param \Illuminate\Support\Collection $contents キャビネットコンテンツのコレクション
     * @return bool
     */
    private function canDelete($request, $cabinet_contents)
    {
        return $this->canTouch($request, $cabinet_contents);
    }

    /**
     * キャビネットコンテンツをダウンロード処理をできるか
     *
     * @param \Illuminate\Http\Request $request リクエスト
     * @param \Illuminate\Support\Collection $contents キャビネットコンテンツのコレクション
     * @return bool
     */
    private function canDownload($request, $cabinet_contents)
    {
        return $this->canTouch($request, $cabinet_contents);
    }

    /**
     * キャビネットコンテンツが触れる状態にあるか
     *
     * @param \Illuminate\Http\Request $request リクエスト
     * @param \Illuminate\Support\Collection $contents キャビネットコンテンツのコレクション
     * @return bool
     */
    private function canTouch($request, $cabinet_contents)
    {
        foreach ($cabinet_contents as $content) {
            $page_tree = Page::reversed()->ancestorsAndSelf($content->upload->page_id);
            // ファイルにページ情報がある場合
            if ($content->upload->page_id) {
                $page = Page::find($content->upload->page_id);
                $page_roles = PageRole::getPageRoles(array($page->id));

                // 認証されていなくてパスワードを要求する場合、パスワード要求画面を表示
                if ($page->isRequestPassword($request, $page_tree)) {
                    return false;
                }

                // ファイルに閲覧権限がない場合
                if (!$page->isView(Auth::user(), true, true, $page_roles)) {
                    return false;
                }
            }
        }
        return true;
    }

    /**
     * プラグインのバケツ選択表示関数
     *
     * @method_title 選択
     * @method_desc このフレームに表示するキャビネットを選択します。
     * @method_detail
     */
    public function listBuckets($request, $page_id, $frame_id, $id = null)
    {
        // 表示テンプレートを呼び出す。
        return $this->view('list_buckets', [
            'plugin_buckets' => Cabinet::orderBy('created_at', 'desc')->paginate(10),
        ]);
    }

    /**
     * バケツ新規作成画面
     *
     * @method_title 作成
     * @method_desc キャビネットを新しく作成します。
     * @method_detail キャビネット名を入力してキャビネットを作成できます。
     */
    public function createBuckets($request, $page_id, $frame_id)
    {
        // 処理的には編集画面を呼ぶ
        return $this->editBuckets($request, $page_id, $frame_id);
    }

    /**
     * バケツ設定変更画面の表示
     */
    public function editBuckets($request, $page_id, $frame_id)
    {
        // コアがbucket_id なしで呼び出してくるため、bucket_id は frame_id から探す。
        if ($this->action == 'createBuckets') {
            $bucket_id = null;
        } else {
            $bucket_id = $this->getBucketId();
        }

        // 表示テンプレートを呼び出す。
        return $this->view('bucket', [
            // 表示中のバケツデータ
            'cabinet' => $this->getPluginBucket($bucket_id),
        ]);
    }

    /**
     *  バケツ登録処理
     */
    public function saveBuckets($request, $page_id, $frame_id, $bucket_id = null)
    {
        // 入力エラーがあった場合は入力画面に戻る。
        $validator = $this->getBucketValidator($request);
        if ($validator->fails()) {
            return back()->withErrors($validator)->withInput();
        }

        $bucket_id = $this->saveCabinet($request, $frame_id, $bucket_id);

        // 登録後はリダイレクトして編集ページを開く。
        return new Collection(['redirect_path' => url('/') . "/plugin/cabinets/editBuckets/" . $page_id . "/" . $frame_id . "/" . $bucket_id . "#frame-" . $frame_id]);
    }

    /**
     * キャビネット登録/更新のバリデーターを取得する。
     *
     * @param \Illuminate\Http\Request $request リクエスト
     * @return \Illuminate\Contracts\Validation\Validator バリデーター
     */
    private function getBucketValidator($request)
    {
        // 項目のエラーチェック
        $validator = Validator::make($request->all(), [
            'name' => [
                'required',
                'max:255'
            ],
            'upload_max_size'=> [
                'required',
                Rule::in(UploadMaxSize::getMemberKeys()),
            ],
        ]);
        $validator->setAttributeNames([
            'name' => 'キャビネット名',
            'upload_max_size' => 'ファイル最大サイズ',
        ]);

        return $validator;
    }

    /**
     * フォルダ作成のバリデーターを取得する。
     *
     * @param \Illuminate\Http\Request $request リクエスト
     * @return \Illuminate\Contracts\Validation\Validator バリデーター
     */
    private function getMakeFoldertValidator($request)
    {
        // 項目のエラーチェック
        $validator = Validator::make($request->all(), [
            'folder_name.*' => [
                'required',
                'max:255',
                // 重複チェック（同じ階層で同じ名前はNG）
                Rule::unique('cabinet_contents', 'name')->where(function ($query) use ($request) {
                    return $query->where('parent_id', $request->parent_id);
                }),
            ],
        ]);
        $validator->setAttributeNames([
            'folder_name.*' => 'フォルダ名',
        ]);

        return $validator;
    }

    /**
     * ファイルアップロードのバリデーターを取得する。
     *
     * @param \Illuminate\Http\Request $request リクエスト
     * @param  App\Models\User\Cabinets\Cabinet キャビネット
     * @return \Illuminate\Contracts\Validation\Validator バリデーター
     */
    private function getUploadValidator($request, $cabinet)
    {
        // ファイルチェック
        $rules['upload_file.*'] = [
            'required',
        ];
        if ($cabinet->upload_max_size !== UploadMaxSize::infinity) {
            $rules['upload_file.*'][] = 'max:' . $cabinet->upload_max_size;
        }

        // 項目のエラーチェック
        $validator = Validator::make($request->all(), $rules);
        $validator->setAttributeNames([
            'upload_file.*' => 'ファイル',
        ]);

        // ZIPを展開する際はZIP形式のファイルのみ許可する
        $validator->sometimes('upload_file.*', 'required|mimes:zip', function ($input) {
            return !empty($input->zip_deploy);
        });

        return $validator;
    }

    /**
     * 一括のファイル削除、ダウンロードのバリデーターを取得する。
     *
     * @param \Illuminate\Http\Request $request リクエスト
     * @return \Illuminate\Contracts\Validation\Validator バリデーター
     */
    private function getContentsControlValidator($request)
    {
        $validator = Validator::make($request->all(), [
            'cabinet_content_id' => [
                'required',
            ],
        ]);

        // 項目のエラーチェック
        $validator->setAttributeNames([
            'cabinet_content_id' => 'ファイル選択',
        ]);

        return $validator;
    }

    /**
     * 名前変更のバリデーターを取得する。
     *
     * @param \Illuminate\Http\Request $request リクエスト
     * @return \Illuminate\Contracts\Validation\Validator バリデーター
     */
    private function getRenameValidator($request)
    {
        // 項目のエラーチェック
        $validator = Validator::make($request->all(), [
            'cabinet_content_id' => [
                'required',
                'exists:cabinet_contents,id',
            ],
            'new_name' => [
                'required',
                'max:255',
                // ファイル名として使用できない文字をチェック
                'regex:/^[^\\\\\/\:\*\?\"\<\>\|]+$/',
            ],
        ]);
        $validator->setAttributeNames([
            'cabinet_content_id' => 'コンテンツID',
            'new_name' => '新しい名前',
        ]);

        return $validator;
    }

    /**
     * キャビネットを登録する。
     *
     * @param \Illuminate\Http\Request $request リクエスト
     * @param int $frame_id フレームID
     * @param int $bucket_id バケツID
     * @return int バケツID
     */
    private function saveCabinet($request, $frame_id, $bucket_id)
    {
        // バケツの取得。なければ登録。
        $bucket = Buckets::updateOrCreate(
            ['id' => $bucket_id],
            ['bucket_name' => $request->name, 'plugin_name' => 'cabinets'],
        );

        // フレームにバケツの紐づけ
        $frame = Frame::find($frame_id)->update(['bucket_id' => $bucket->id]);

        // プラグインバケツを取得(なければ新規オブジェクト)
        // プラグインバケツにデータを設定して保存
        $cabinet = $this->getPluginBucket($bucket->id);
        $cabinet->name = $request->name;
        $cabinet->upload_max_size = $request->upload_max_size;
        $cabinet->save();

        $this->saveRootCabinetContent($cabinet);

        return $bucket->id;
    }

    /**
     * ルートディレクトリを登録する
     *
     * @param App\Models\User\Cabinets\Cabinet $cabinet キャビネット
     */
    private function saveRootCabinetContent($cabinet)
    {
        CabinetContent::updateOrCreate(
            ['cabinet_id' => $cabinet->id, 'parent_id' => null, 'is_folder' => CabinetContent::is_folder_on],
            ['name' => $cabinet->name]
        );
    }

    /**
     *  キャビネット削除処理
     *
     * @param \Illuminate\Http\Request $request リクエスト
     * @param int $page_id ページID
     * @param int $frame_id フレームID
     */
    public function destroyBuckets($request, $page_id, $frame_id, $cabinet_id)
    {
        // プラグインバケツの取得
        $cabinet = Cabinet::find($cabinet_id);
        if (empty($cabinet)) {
            return;
        }

        // FrameのバケツIDの更新
        Frame::where('id', $frame_id)->update(['bucket_id' => null]);

        // バケツ削除
        Buckets::find($cabinet->bucket_id)->delete();

        // キャビネットコンテンツ削除
        $cabinet_content = $this->fetchCabinetContent(null, $cabinet->id);
        $this->deleteCabinetContents(
            $cabinet_content->id,
            CabinetContent::descendantsAndSelf($cabinet_content->id)
        );

        // プラグインデータ削除
        $cabinet->delete();

        return;
    }

    /**
     * データ紐づけ変更関数
     *
     * @param \Illuminate\Http\Request $request リクエスト
     * @param int $page_id ページID
     * @param int $frame_id フレームID
     */
    public function changeBuckets($request, $page_id, $frame_id)
    {
        // FrameのバケツIDの更新
        Frame::where('id', $frame_id)->update(['bucket_id' => $request->select_bucket]);

        // Cabinets の特定
        $plugin_bucket = $this->getPluginBucket($request->select_bucket);
    }

    /**
     * 権限設定　変更画面を表示する
     *
     * @see UserPluginBase::editBucketsRoles()
     */
    public function editBucketsRoles($request, $page_id, $frame_id, $id = null, $use_approval = false)
    {
        // 承認機能は使わない
        return parent::editBucketsRoles($request, $page_id, $frame_id, $id, $use_approval);
    }

    /**
     * 権限設定を保存する
     *
     * @see UserPluginBase::saveBucketsRoles()
     */
    public function saveBucketsRoles($request, $page_id, $frame_id, $id = null, $use_approval = false)
    {
        // 承認機能は使わない
        return parent::saveBucketsRoles($request, $page_id, $frame_id, $id, $use_approval);
    }

    /**
     * フレーム表示設定画面の表示
     *
     * @method_title 表示設定
     * @method_desc このフレームに表示する際のキャビネットをカスタマイズできます。
     * @method_detail ファイルの並び順を指定できます。
     */
    public function editView($request, $page_id, $frame_id)
    {
        // 表示テンプレートを呼び出す。
        return $this->view('frame', [
            'cabinet' => $this->getPluginBucket($this->getBucketId()),
        ]);
    }

    /**
     * フレーム表示設定の保存
     */
    public function saveView($request, $page_id, $frame_id, $cabinet_id)
    {
        // フレーム設定保存
        FrameConfig::saveFrameConfigs($request, $frame_id, CabinetFrameConfig::getMemberKeys());
        // 更新したので、frame_configsを設定しなおす
        $this->refreshFrameConfigs();

        return;
    }
}
