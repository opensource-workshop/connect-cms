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
use App\Models\Common\Uploads;
use App\Models\User\Cabinets\Cabinet;

use App\Enums\UploadMaxSize;
use App\Models\User\Cabinets\CabinetContent;
use App\Plugins\User\UserPluginBase;

use function PHPUnit\Framework\isEmpty;

/**
 * キャビネット・プラグイン
 *
 * @author 永原　篤 <nagahara@opensource-workshop.jp>
 * @copyright OpenSource-WorkShop Co.,Ltd. All Rights Reserved
 * @category キャビネット・プラグイン
 * @package Contoroller
 */
class CabinetsPlugin extends UserPluginBase
{

    /* オブジェクト変数 */

    /* コアから呼び出す関数 */

    /**
     * 関数定義（コアから呼び出す）
     */
    public function getPublicFunctions()
    {
        // 標準関数以外で画面などから呼ばれる関数の定義
        $functions = array();
        $functions['get']  = ['index', 'download'];
        $functions['post'] = ['makeFolder', 'upload', 'deleteContents'];
        return $functions;
    }

    /**
     *  権限定義
     */
    public function declareRole()
    {
        // 権限チェックテーブル
        $role_ckeck_table = array();
        $role_ckeck_table["upload"] = array('posts.create');
        $role_ckeck_table["makeFolder"] = array('posts.create');
        $role_ckeck_table["deleteContents"] = array('posts.delete');
        return $role_ckeck_table;
    }

    /**
     * 編集画面の最初のタブ（コアから呼び出す）
     *
     * スーパークラスをオーバーライド
     */
    public function getFirstFrameEditAction()
    {
        return "editBuckets";
    }

    /* private関数 */

    /**
     * プラグインのバケツ取得関数
     */
    public function getPluginBucket($bucket_id)
    {
        // プラグインのメインデータを取得する。
        return Cabinet::firstOrNew(['bucket_id' => $bucket_id]);
    }

    /* 画面アクション関数 */

    /**
     *  データ初期表示関数
     *  コアがページ表示の際に呼び出す関数
     */
    public function index($request, $page_id, $frame_id)
    {
        // バケツ未設定の場合はバケツ空テンプレートを呼び出す
        if (!isset($this->frame) || !$this->frame->bucket_id) {
            // バケツ空テンプレートを呼び出す。
            return $this->view('empty_bucket');
        }

        $cabinet = $this->getPluginBucket($this->frame->bucket_id);

        $parent = $this->fetchCabinetContent($this->getParentId($request), $cabinet->id);

        // 表示テンプレートを呼び出す。
        return $this->view('index', [
           'cabinet_contents' => $parent->children()->orderBy('is_folder', 'desc')->orderBy('name', 'asc')->get(),
           'breadcrumbs' => $this->fetchBreadCrumbs($cabinet->id, $parent->id),
           'parent_id' =>  $parent->id,
        ]);
    }

    private function getParentId($request)
    {
        $parent_id = '';
        // エラーのとき、セッションからparent_idを取得
        if (!empty(session('parent_id'))) {
            $parent_id = session('parent_id');
        } else {
            $parent_id = $request->parent_id;
        }

        return $parent_id;
    }

    private function fetchCabinetContent($cabinet_content_id, $cabinet_id = null)
    {
        // cabinet_content_idがなければ、ルート要素を返す
        if (empty($cabinet_content_id)) {
            return CabinetContent::where('cabinet_id', $cabinet_id)->where('parent_id' , null)->first();
        }
        return CabinetContent::find($cabinet_content_id);
    }

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

    public function makeFolder($request, $page_id, $frame_id)
    {
        $validator = $this->getMakeFoldertValidator($request);
        if ($validator->fails()) {
            return back()->withErrors($validator)->withInput()->with('parent_id', $request->parent_id);
        }

        $cabinet = $this->getPluginBucket($this->frame->bucket_id);
        $parent = $this->fetchCabinetContent($request->parent_id);

        $parent->children()->create([
            'cabinet_id' => $cabinet->id,
            'upload_id' => null,
            'name' => $request->folder_name,
            'is_folder' => CabinetContent::is_folder_on,
        ]);

        // 登録後はリダイレクトして初期表示。
        return new Collection(['redirect_path' => url('/') . "/plugin/cabinets/index/" . $page_id . "/" . $frame_id . "/" . $this->frame->bucket_id . '?parent_id=' . $parent->id . "#frame-" . $frame_id ]);
    }

    public function upload($request, $page_id, $frame_id)
    {
        $cabinet = $this->getPluginBucket($this->frame->bucket_id);
        $validator = $this->getUploadValidator($request, $cabinet);
        if ($validator->fails()) {
            return back()->withErrors($validator)->withInput()->with('parent_id', $request->parent_id);
        }

        $parent = $this->fetchCabinetContent($request->parent_id);

        if ($this->shouldOverwriteFile($parent, $request->file('upload_file')->getClientOriginalName())) {
            $this->overwriteFile($request, $page_id, $parent);
        } else {
            $this->writeFile($request, $page_id, $parent);
        }

        // 登録後はリダイレクトして初期表示。
        return new Collection(['redirect_path' => url('/') . "/plugin/cabinets/index/" . $page_id . "/" . $frame_id . "/" . $this->frame->bucket_id . '?parent_id=' . $parent->id . "#frame-" . $frame_id ]);
    }

    private function shouldOverwriteFile($parent, $file_name)
    {
        return CabinetContent::where('parent_id', $parent->id)
            ->where('name', $file_name)
            ->where('is_folder', CabinetContent::is_folder_off)
            ->exists();
    }

    private function writeFile($request, $page_id, $parent)
    {
        // uploads テーブルに情報追加、ファイルのid を取得する
        $upload = Uploads::create([
            'client_original_name' => $request->file('upload_file')->getClientOriginalName(),
            'mimetype'             => $request->file('upload_file')->getClientMimeType(),
            'extension'            => $request->file('upload_file')->getClientOriginalExtension(),
            'size'                 => $request->file('upload_file')->getClientSize(),
            'plugin_name'          => 'cabinets',
            'page_id'              => $page_id,
            'temporary_flag'       => 0,
            'created_id'           => empty(Auth::user()) ? null : Auth::user()->id,
        ]);

        // ファイル保存
        $request->file('upload_file')->storeAs(
            $this->getDirectory($upload->id), $this->getContentsFileName($upload));

        $parent->children()->create([
            'cabinet_id' => $upload->id,
            'upload_id' => $upload->id,
            'name' => $request->file('upload_file')->getClientOriginalName(),
            'is_folder' => CabinetContent::is_folder_off,
        ]);
    } 

    private function overwriteFile($request, $page_id, $parent)
    {
        $content = CabinetContent::where('parent_id', $parent->id)
            ->where('name', $request->file('upload_file')->getClientOriginalName())
            ->where('is_folder', CabinetContent::is_folder_off)
            ->first();
            
        // uploads テーブルに情報追加、ファイルのid を取得する
        Uploads::find($content->upload_id)->update([
            'client_original_name' => $request->file('upload_file')->getClientOriginalName(),
            'mimetype'             => $request->file('upload_file')->getClientMimeType(),
            'extension'            => $request->file('upload_file')->getClientOriginalExtension(),
            'size'                 => $request->file('upload_file')->getClientSize(),
            'plugin_name'          => 'cabinets',
            'page_id'              => $page_id,
            'temporary_flag'       => 0,
            'updated_id'           => empty(Auth::user()) ? null : Auth::user()->id,
        ]);

        // ファイル保存
        $request->file('upload_file')->storeAs(
            $this->getDirectory($content->upload_id), $this->getContentsFileName($content->upload));

        // 画面表示される更新日を更新する
        $content->touch();
    }

    /**
     *  コンテンツ削除処理
     */
    public function deleteContents($request, $page_id, $frame_id)
    {
        $validator = $this->getContentsControlValidator($request);
        if ($validator->fails()) {
            return back()->withErrors($validator)->withInput()->with('parent_id', $request->parent_id);
        }

        foreach ($request->cabinet_content_id as $cabinet_content_id) {
            $contents = CabinetContent::descendantsAndSelf($cabinet_content_id);
            if (!$this->canDelete($request, $contents)){
                abort(403, '権限がありません。');
            };

            $this->deleteCabinetContents($cabinet_content_id, $contents);
        }

        // 登録後はリダイレクトして初期表示。
        return new Collection(['redirect_path' => url('/') . "/plugin/cabinets/index/" . $page_id . "/" . $frame_id . "/" . $this->frame->bucket_id . '?parent_id=' . $request->parent_id . "#frame-" . $frame_id ]);
    }

    private function deleteCabinetContents($cabinet_content_id, $cabinet_contents)
    {
        $delete_upload_ids = [];
        foreach ($cabinet_contents as $content) {
            if (!empty($content->upload_id)) {
                $delete_upload_ids[] = $content->upload_id;
            }
        }

        CabinetContent::find($cabinet_content_id)->delete();

        // アップロードテーブル削除、実ファイルの削除
        Uploads::destroy($delete_upload_ids);
        foreach ($cabinet_contents->whereNotNull('upload_id') as $content) {
            Storage::delete($this->getContentsFilePath($content->upload));
        }
    }


    public function download($request, $page_id, $frame_id)
    {
        $validator = $this->getContentsControlValidator($request);
        if ($validator->fails()) {
            return back()->withErrors($validator)->withInput()->with('parent_id', $request->parent_id);
        }

        $traverse = function ($contents,  $parent_name = '') use (&$traverse, &$zip) {
            foreach ($contents as $content) {
                if ($content->is_folder === CabinetContent::is_folder_on && $content->isLeaf()) {
                    $zip->addEmptyDir($parent_name .'/' . $content->name);
                } elseif ($content->is_folder === CabinetContent::is_folder_off) {
                    // データベースがない場合はスキップ
                    if (empty($content->upload)) {
                        continue;
                    }
                    // ファイルの実体がない場合はスキップ
                    if (!Storage::exists($this->getContentsFilePath($content->upload))) {
                        continue;
                    }
                    $zip->addFile(storage_path('app/') . $this->getContentsFilePath($content->upload), $parent_name .'/'. $content->name);
                    Uploads::find($content->upload->id)->increment('download_count');
                }
                $traverse($content->children, $parent_name .'/' . $content->name);
            }
        };

        $save_path = $this->getTmpDirectory() . uniqid('', true) . '.zip';
        $zip = new \ZipArchive();
        $zip->open($save_path, \ZipArchive::CREATE);

        foreach ($request->cabinet_content_id as $cabinet_content_id) {
            $contents = CabinetContent::descendantsAndSelf($cabinet_content_id)->toTree();
            if (!$this->canDownload($request, $contents)) {
                abort(403, 'ファイル参照権限がありません。');
            }

            // フォルダがないとzipファイルを作れない
            if (!is_dir($this->getTmpDirectory())) {
                mkdir($this->getTmpDirectory(), 0777, true);
            }

            $traverse($contents);
        }

        if ($zip->count() === 0) {
            abort(404, 'ファイルがありません。');
        }
        $zip->close();

        return response()->download(
            $save_path,
            'Files.zip',
            ['Content-Disposition' => 'filename=Files.zip']
        );
    }

    private function getTmpDirectory()
    {
        return storage_path('app/') . 'tmp/cabinet/';
    }

    private function getContentsFilePath($upload)
    {
        return $this->getDirectory($upload->id) . '/' . $this->getContentsFileName($upload);
    }

    private function getContentsFileName($upload)
    {
        return $upload->id . '.' . $upload->extension;
    }

    private function canDelete($request, $cabinet_contents)
    {
        // TODO:権限チェック
        return $this->canTouch($request, $cabinet_contents);
    }

    private function canDownload($request, $cabinet_contents)
    {
        return $this->canTouch($request, $cabinet_contents);
    }

    private function canTouch($request, $cabinet_contents)
    {
        foreach($cabinet_contents as $content) {
            $page_tree = Page::reversed()->ancestorsAndSelf($content->upload->page_id);
            // ファイルにページ情報がある場合
            if ($content->upload->page_id) {
                $page = Page::find($content->upload->page_id);
                $page_roles = $this->getPageRoles(array($page->id));

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
    private function getBucketValidator($request) {
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
    private function getMakeFoldertValidator($request) {
        // 項目のエラーチェック
        $validator = Validator::make($request->all(), [
            'folder_name' => [
                'required',
                'max:255',
                // 重複チェック（同じ階層で同じ名前はNG）
                Rule::unique('cabinet_contents', 'name')->where(function ($query) use ($request) {
                    return $query->where('parent_id', $request->parent_id);
                }),
            ],
        ]);
        $validator->setAttributeNames([
            'folder_name' => 'フォルダ名',
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
        $rules['upload_file'] = [
            'required',
        ];
        if ($cabinet->upload_max_size !== UploadMaxSize::infinity) {
            $rules['upload_file'][] = 'max:' . $cabinet->upload_max_size;
        }

        // 項目のエラーチェック
        $validator = Validator::make($request->all(), $rules);
        $validator->setAttributeNames([
            'upload_file' => 'ファイル',
        ]);

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
     * キャビネットを登録する。
     * 
     * @param \Illuminate\Http\Request $request リクエスト
     * @param string $frame_id フレームID
     * @param string $bucket_id バケツID
     * @return string バケツID
     */
    private function saveCabinet($request, $frame_id, $bucket_id) {
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
     *  削除処理
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

        // $this->deleteCabinetContents();

        // プラグインデータ削除
        $cabinet->delete();

        return;
    }

   /**
    * データ紐づけ変更関数
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
    public function editBucketsRoles($request, $page_id, $frame_id, $id = null, $use_approval = false) {
        // 承認機能は使わない
        return parent::editBucketsRoles($request, $page_id, $frame_id, $id, $use_approval);
    }

    /**
     * 権限設定を保存する
     * 
     * @see UserPluginBase::saveBucketsRoles()
     */
    public function saveBucketsRoles($request, $page_id, $frame_id, $id = null, $use_approval = false) {
        // 承認機能は使わない
        return parent::saveBucketsRoles($request, $page_id, $frame_id, $id, $use_approval);
    }
}
