<?php

namespace App\Plugins\User\Photoalbums;

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
use App\Models\User\Photoalbums\Photoalbum;
use App\Models\User\Photoalbums\PhotoalbumContent;

use App\Enums\UploadMaxSize;
use App\Enums\PhotoalbumFrameConfig;
use App\Enums\PhotoalbumSort;

use App\Traits\ConnectCommonTrait;

use Intervention\Image\Facades\Image;

use App\Plugins\User\UserPluginBase;

/**
 * フォトアルバム・プラグイン
 *
 * @author 永原　篤 <nagahara@opensource-workshop.jp>
 * @copyright OpenSource-WorkShop Co.,Ltd. All Rights Reserved
 * @category フォトアルバム・プラグイン
 * @package Controller
 */
class PhotoalbumsPlugin extends UserPluginBase
{

    /*
        【メモ】
        zip ダウンロード ⇒ 閲覧回数アップ
    */

    /* オブジェクト変数 */
    // ファイルダウンロードURL
    private $download_url = '';

    /* コアから呼び出す関数 */

    /**
     * 関数定義（コアから呼び出す）
     *
     * @return array 関数名
     */
    public function getPublicFunctions()
    {
        // 標準関数以外で画面などから呼ばれる関数の定義
        $functions = array();
        $functions['get']  = ['index', 'download', 'changeDirectory', 'embed'];
        $functions['post'] = ['makeFolder', 'editFolder', 'upload', 'uploadVideo', 'editContents', 'editVideo', 'deleteContents'];
        return $functions;
    }

    /**
     *  権限定義
     *
     * @return array 関数名と権限
     */
    public function declareRole()
    {
        // 権限チェックテーブル
        $role_check_table = array();
        $role_check_table["makeFolder"] = array('posts.create');
        $role_check_table["editFolder"] = array('posts.create');
        $role_check_table["upload"] = array('posts.create');
        $role_check_table["uploadVideo"] = array('posts.create');
        $role_check_table["editContents"] = array('posts.update');
        $role_check_table["editVideo"] = array('posts.update');
        $role_check_table["deleteContents"] = array('posts.delete');
        return $role_check_table;
    }

    /**
     * 編集画面の最初のタブ（コアから呼び出す）
     * スーパークラスをオーバーライド
     *
     * @return string 関数名
     */
    public function getFirstFrameEditAction()
    {
        return "editBuckets";
    }

    /**
     * プラグインのバケツ取得関数
     *
     * @param int $bucket_id バケツID
     * @return App\Models\User\Photoalbums\Photoalbum $bucket_id 処理するフォトアルバム
     */
    private function getPluginBucket($bucket_id)
    {
        // プラグインのメインデータを取得する。
        return Photoalbum::firstOrNew(['bucket_id' => $bucket_id]);
    }

    /* 画面アクション関数 */

    /**
     * データ初期表示関数
     * コアがページ表示の際に呼び出す関数
     *
     * @param \Illuminate\Http\Request $request リクエスト
     * @param int $page_id ページID
     * @param int $frame_id フレームID
     * @param int $parent_id 表示する階層(ルートはnull)
     * @return mixed $value テンプレートに渡す内容
     */
    public function index($request, $page_id, $frame_id, $parent_id = null)
    {
        // バケツ未設定の場合はバケツ空テンプレートを呼び出す
        if (!isset($this->frame) || !$this->frame->bucket_id) {
            // バケツ空テンプレートを呼び出す。
            return $this->view('empty_bucket');
        }

        // バケツデータとフォトアルバムデータ取得、フォトアルバムのルート階層はphotoalbum->id == nullのもの。
        $photoalbum = $this->getPluginBucket($this->frame->bucket_id);
        $parent = $this->fetchPhotoalbumContent($parent_id, $photoalbum->id);

        // フォルダ、ファイルの比較条件の取得
        $sort_folder = FrameConfig::getConfigValue($this->frame_configs, PhotoalbumFrameConfig::sort_folder);
        $sort_file = FrameConfig::getConfigValue($this->frame_configs, PhotoalbumFrameConfig::sort_file);

        // データ取得してからソート(ページネートに対応するためにSQLソートに変更予定)
        $photoalbum_contents = $parent->children()->get()->sort(function ($first, $second) use($sort_folder, $sort_file) {
            // フォルダ>ファイル
            if ($first['is_folder'] == $second['is_folder']) {
                // フォルダ同士 or ファイル同士を比較

                if ($first['is_folder'] == PhotoalbumContent::is_folder_on) {
                    // フォルダを比較
                    $sort = $sort_folder;
                } else {
                    // ファイルを比較
                    $sort = $sort_file;
                }

                if ($sort == '' || $sort == PhotoalbumSort::name_asc) {
                    // 名前（昇順）
                    // return $first['displayName'] < $second['displayName'] ? -1 : 1;
                    return $this->sortAsc($first['displayName'], $second['displayName']);
                } elseif ($sort == PhotoalbumSort::name_desc) {
                    // 名前（降順）
                    return $this->sortDesc($first['displayName'], $second['displayName']);
                } elseif ($sort == PhotoalbumSort::created_asc) {
                    // 登録日（昇順）
                    return $this->sortAsc($first['created_at'], $second['created_at']);
                } elseif ($sort == PhotoalbumSort::created_desc) {
                    // 登録日（降順）
                    return $this->sortDesc($first['created_at'], $second['created_at']);
                }
            }
            // フォルダとファイルの比較
            // ファイル(is_folder=0)よりフォルダ(is_folder=1)を上（降順）にする
            // return $first['is_folder'] < $second['is_folder'] ? 1 : -1;
            return $this->sortDesc($first['is_folder'], $second['is_folder']);
        });

        // カバー写真に指定されている写真
        $covers = PhotoalbumContent::whereIn('parent_id', $photoalbum_contents->where('is_folder', PhotoalbumContent::is_folder_on)->pluck('id'))->where('is_cover', PhotoalbumContent::is_cover_on)->get();

        // 表示テンプレートを呼び出す。
        return $this->view('index', [
            'photoalbum' => $photoalbum,
            'photoalbum_contents' => $photoalbum_contents,
            'breadcrumbs' => $this->fetchBreadCrumbs($photoalbum->id, $parent->id),
            'parent_id' =>  $parent->id,
            'covers' => $covers,
        ]);
    }

    /**
     * 動画の埋め込み画面を表示する
     *
     * @param \Illuminate\Http\Request $request リクエスト
     * @param int $page_id ページID
     * @param int $frame_id フレームID
     * @param int $photoalbum_content_id コンテンツID
     * @return mixed $value テンプレートに渡す内容
     */
    public function embed($request, $page_id, $frame_id, $photoalbum_content_id)
    {
        // 対象のデータを取得して編集画面を表示する。
        $photoalbum_content = PhotoalbumContent::find($photoalbum_content_id);

        $photoalbum = $this->getPluginBucket($this->frame->bucket_id);

        return $this->view('embed', [
            'photoalbum' => $photoalbum,
            'photoalbum_content' => $photoalbum_content,
        ]);
    }

    /**
     * 編集画面を表示する
     * この関数はアルバム、画像、動画で共通使用
     *
     * @param \Illuminate\Http\Request $request リクエスト
     * @param int $page_id ページID
     * @param int $frame_id フレームID
     * @param int $photoalbum_content_id コンテンツID
     * @return mixed $value テンプレートに渡す内容
     */
    public function edit($request, $page_id, $frame_id, $photoalbum_content_id)
    {
        // 対象のデータを取得して編集画面を表示する。
        $photoalbum_content = PhotoalbumContent::find($photoalbum_content_id);

        $photoalbum = $this->getPluginBucket($this->frame->bucket_id);

        if ($photoalbum_content->is_folder) {
            $blade = 'edit_folder';
        } elseif (Uploads::isVideo($photoalbum_content->mimetype)) {
            $blade = 'edit_video';
        } else {
            $blade = 'edit_contents';
        }

        return $this->view($blade, [
            'photoalbum' => $photoalbum,
            'photoalbum_content' => $photoalbum_content,
        ]);
    }

    /**
     * アルバムを移動する
     *
     * @param \Illuminate\Http\Request $request リクエスト
     * @param int $page_id ページID
     * @param int $frame_id フレームID
     * @param int $parent_id 移動先の階層を示すid
     * @return mixed $value テンプレートに渡す内容
     */
    public function changeDirectory($request, $page_id, $frame_id, $parent_id) 
    {
        return $this->index($request, $page_id, $frame_id, $parent_id);
    }

    /**
     * フォトアルバムコンテンツを取得する
     *
     * @param int $photoalbum_content_id フォトアルバムコンテンツID
     * @param int $photoalbum_id フォトアルバムID
     * @return \App\Models\User\Photoalbums\PhotoalbumContent フォトアルバムコンテンツ
     */
    private function fetchPhotoalbumContent($photoalbum_content_id, $photoalbum_id = null)
    {
        // photoalbum_content_idがなければ、ルート要素を返す
        if (empty($photoalbum_content_id)) {
            return PhotoalbumContent::where('photoalbum_id', $photoalbum_id)->where('parent_id', null)->first();
        }
        return PhotoalbumContent::find($photoalbum_content_id);
    }

    /**
     * パンくずリスト（ファルダ階層）を取得する
     *
     * @param int $photoalbum_content_id フォトアルバムコンテンツID
     * @param int $photoalbum_id フォトアルバムID
     * @return \Illuminate\Support\Collection フォトアルバムコンテンツのコレクション
     */
    private function fetchBreadCrumbs($photoalbum_id, $photoalbum_content_id = null)
    {
        // 初期表示はルート要素のみ
        if (empty($photoalbum_content_id)) {
            return PhotoalbumContent::where('photoalbum_id', $photoalbum_id)
                ->where('parent_id', null)
                ->get();
        }
        return PhotoalbumContent::ancestorsAndSelf($photoalbum_content_id);
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
     * アルバム作成処理
     *
     * @param \Illuminate\Http\Request $request リクエスト
     * @param int $page_id ページID
     * @param int $frame_id フレームID
     * @return \Illuminate\Support\Collection リダイレクト先のパス
     */
    public function makeFolder($request, $page_id, $frame_id)
    {
        $validator = $this->getMakeFoldertValidator($request);
        if ($validator->fails()) {
            return back()->withErrors($validator)->withInput();
        }

        $photoalbum = $this->getPluginBucket($this->frame->bucket_id);
        $parent = $this->fetchPhotoalbumContent($request->parent_id);

        $parent->children()->create([
            'photoalbum_id' => $photoalbum->id,
            'upload_id' => null,
            'name' => $request->folder_name[$frame_id],
            'description' => $request->description[$frame_id],
            'is_folder' => PhotoalbumContent::is_folder_on,
            'is_cover' => PhotoalbumContent::is_cover_off,
        ]);

        // 登録後はリダイレクトして初期表示。
        return new Collection(['redirect_path' => url('/') . "/plugin/photoalbums/changeDirectory/" . $page_id . "/" . $frame_id  . "/" . $parent->id . "/#frame-" . $frame_id ]);
    }

    /**
     *  アルバム変更処理
     *
     * @param \Illuminate\Http\Request $request リクエスト
     * @param int $page_id ページID
     * @param int $frame_id フレームID
     * @param int $photoalbum_content_id 対象のアルバムID
     * @return \Illuminate\Support\Collection リダイレクト先のパス
     */
    public function editFolder($request, $page_id, $frame_id, $photoalbum_content_id)
    {
        // 対象のデータを取得して編集する。
        $photoalbum_content = PhotoalbumContent::find($photoalbum_content_id);
        $photoalbum_content->name = $request->name[$frame_id];
        $photoalbum_content->description = $request->description[$frame_id];
        $photoalbum_content->is_folder = PhotoalbumContent::is_folder_on;
        $photoalbum_content->is_cover = PhotoalbumContent::is_cover_off;
        $photoalbum_content->save();

        // 登録後はリダイレクトして初期表示。
        return new Collection(['redirect_path' => url('/') . "/plugin/photoalbums/changeDirectory/" . $page_id . "/" . $frame_id . "/" . $photoalbum_content->parent_id . "/#frame-" . $frame_id ]);
    }

    /**
     * 画像ファイルアップロード処理
     *
     * @param \Illuminate\Http\Request $request リクエスト
     * @param int $page_id ページID
     * @param int $frame_id フレームID
     * @return \Illuminate\Support\Collection リダイレクト先のパス
     */
    public function upload($request, $page_id, $frame_id)
    {
        $photoalbum = $this->getPluginBucket($this->frame->bucket_id);
        $validator = $this->getUploadValidator($request, $photoalbum); // バリデータ
        if ($validator->fails()) {
            return back()->withErrors($validator)->withInput();
        }

        $parent = $this->fetchPhotoalbumContent($request->parent_id);
        $this->writeFile($request, $page_id, $frame_id, $photoalbum, $parent);

        // 登録後はリダイレクトして初期表示。
        return new Collection(['redirect_path' => url('/') . "/plugin/photoalbums/changeDirectory/" . $page_id . "/" . $frame_id . "/" . $parent->id . "/#frame-" . $frame_id ]);
    }

    /**
     * 動画ファイルアップロード処理
     *
     * @param \Illuminate\Http\Request $request リクエスト
     * @param int $page_id ページID
     * @param int $frame_id フレームID
     * @return \Illuminate\Support\Collection リダイレクト先のパス
     */
    public function uploadVideo($request, $page_id, $frame_id)
    {
        $photoalbum = $this->getPluginBucket($this->frame->bucket_id);
        $validator = $this->getVideoUploadValidator($request, $photoalbum); // バリデータ
        if ($validator->fails()) {
            return back()->withErrors($validator)->withInput();
        }

        $parent = $this->fetchPhotoalbumContent($request->parent_id);
        $this->writeVideo($request, $page_id, $frame_id, $parent);

        // 登録後はリダイレクトして初期表示。
        return new Collection(['redirect_path' => url('/') . "/plugin/photoalbums/changeDirectory/" . $page_id . "/" . $frame_id . "/" . $parent->id . "/#frame-" . $frame_id ]);
    }

    /**
     * 対象ディレクトリの取得、なければ作成も。
     */
    private function makeDirectory($file_id)
    {
        $directory = $this->getDirectory($file_id);
        Storage::makeDirectory($directory);
        return $directory;
    }

    /**
     * ファイル新規保存処理
     *
     * @param \Illuminate\Http\Request $request リクエスト
     * @param int $page_id ページID
     * @param int $frame_id フレームID
     * @param \App\Models\User\Photoalbums\Photoalbum $photoalbum バケツレコード
     * @param \App\Models\User\Photoalbums\PhotoalbumContent $parent アルバムレコード
     */
    private function writeFile($request, $page_id, $frame_id, $photoalbum, $parent)
    {
        // 画像ファイル
        $file = $request->file('upload_file')[$frame_id];

        // 必要なら縮小して、\Intervention\Image\Image オブジェクトを受け取る。
        $image = Uploads::shrinkImage($file, $photoalbum->image_upload_max_px);

        // uploads テーブルに情報追加、ファイルのid を取得する
        $upload = Uploads::create([
            'client_original_name' => $file->getClientOriginalName(),
            'mimetype'             => $file->getClientMimeType(),
            'extension'            => $file->getClientOriginalExtension(),
            'size'                 => $file->getSize(),
            'plugin_name'          => 'photoalbums',
            'page_id'              => $page_id,
            'temporary_flag'       => 0,
            'created_id'           => empty(Auth::user()) ? null : Auth::user()->id,
        ]);

        // ファイル保存
        $directory = $this->makeDirectory($upload->id);
        $image->save(storage_path('app/') . $directory . '/' . $upload->id . '.' . $file->getClientOriginalExtension());

        $parent->children()->create([
            'photoalbum_id' => $parent->photoalbum_id,
            'upload_id' => $upload->id,
            'name' => empty($request->title[$frame_id]) ? $file->getClientOriginalName() : $request->title[$frame_id],
            'width' => $image->width(),
            'height' => $image->height(),
            'description' => $request->description[$frame_id],
            'is_folder' => PhotoalbumContent::is_folder_off,
            'is_cover' => ($request->has('is_cover') && $request->is_cover[$frame_id]) ? PhotoalbumContent::is_cover_on : PhotoalbumContent::is_cover_off,
            'mimetype' => $upload->mimetype,
        ]);
    }

    /**
     * 動画ファイル新規保存処理
     *
     * @param \Illuminate\Http\UploadedFile $file file
     * @param int $page_id ページID
     * @param int $frame_id フレームID
     * @param \App\Models\User\Photoalbums\PhotoalbumContent $parent アルバムレコード
     */
    private function writeVideo($request, $page_id, $frame_id, $parent)
    {
        // 動画ファイル
        $video = $request->file('upload_video')[$frame_id];

        // uploads テーブルに情報追加、ファイルのid を取得する
        $upload = Uploads::create([
            'client_original_name' => $video->getClientOriginalName(),
            'mimetype'             => $video->getClientMimeType(),
            'extension'            => $video->getClientOriginalExtension(),
            'size'                 => $video->getSize(),
            'plugin_name'          => 'photoalbums',
            'page_id'              => $page_id,
            'temporary_flag'       => 0,
            'created_id'           => empty(Auth::user()) ? null : Auth::user()->id,
        ]);

        // ファイル保存
        $video->storeAs($this->getDirectory($upload->id), $this->getContentsFileName($upload));

        // ポスター画像
        if ($request->hasFile('upload_poster.'.$frame_id)) {
            $poster = $request->file('upload_poster')[$frame_id];

            // 必要なら縮小して、\Intervention\Image\Image オブジェクトを受け取る。
            $image = Uploads::shrinkImage($poster, $photoalbum->image_upload_max_px);

            // uploads テーブルに情報追加、ファイルのid を取得する
            $upload_poster = Uploads::create([
                'client_original_name' => $poster->getClientOriginalName(),
                'mimetype'             => $poster->getClientMimeType(),
                'extension'            => $poster->getClientOriginalExtension(),
                'size'                 => $poster->getSize(),
                'plugin_name'          => 'photoalbums',
                'page_id'              => $page_id,
                'temporary_flag'       => 0,
                'created_id'           => empty(Auth::user()) ? null : Auth::user()->id,
            ]);

            // ファイル保存
            $directory = $this->makeDirectory($upload_poster->id);
            $image->save(storage_path('app/') . $directory . '/' . $upload_poster->id . '.' . $poster->getClientOriginalExtension());
        }

        // コンテンツレコードの保存
        $parent->children()->create([
            'photoalbum_id' => $parent->photoalbum_id,
            'upload_id' => $upload->id,
            'poster_upload_id' => isset($upload_poster) ? $upload_poster->id : null,
            'name' => empty($request->title[$frame_id]) ? $video->getClientOriginalName() : $request->title[$frame_id],
            'width' => null,
            'height' => null,
            'description' => $request->description[$frame_id],
            'is_folder' => PhotoalbumContent::is_folder_off,
            'is_cover' => ($request->has('is_cover') && $request->is_cover[$frame_id]) ? PhotoalbumContent::is_cover_on : PhotoalbumContent::is_cover_off,
            'mimetype' => $upload->mimetype,
        ]);
    }

    /**
     * ファイル上書き保存処理
     *
     * @param \Illuminate\Http\UploadedFile $file file
     * @param \App\Models\User\Photoalbums\PhotoalbumContent $photoalbum_content 更新対象レコード
     * @param int $page_id ページID
     * @param string $target_column 画像、動画は'upload_id'、ポスター画像の場合は'poster_upload_id'がくる。
     */
    private function overwriteFile($file, $photoalbum_content, $page_id, $target_column = 'upload_id')
    {
        // uploads テーブルの上書き更新
        Uploads::find($photoalbum_content->$target_column)->update([
            'client_original_name' => $file->getClientOriginalName(),
            'mimetype'             => $file->getClientMimeType(),
            'extension'            => $file->getClientOriginalExtension(),
            'size'                 => $file->getSize(),
            'plugin_name'          => 'photoalbums',
            'page_id'              => $page_id,
            'temporary_flag'       => 0,
            'updated_id'           => empty(Auth::user()) ? null : Auth::user()->id,
        ]);

        // 画面表示される更新日を更新する
        $photoalbum_content->touch();

        // ファイル保存
        if ($target_column == 'upload_id') {
            $upload = $photoalbum_content->upload;
        } else {
            $upload = $photoalbum_content->poster_upload;
        }
        $file->storeAs($this->getDirectory($photoalbum_content->$target_column), $this->getContentsFileName($upload));

        return $upload;
    }

    /**
     *  アルバム表紙のチェック処理
     *
     * @param \Illuminate\Http\Request $request リクエスト
     * @param int $frame_id フレームID
     * @param \App\Models\User\Photoalbums\PhotoalbumContent フォトアルバムコンテンツ
     */
    private function updateCover($request, $frame_id, $photoalbum_content)
    {
        // アルバム表紙がチェックされていた場合、同じアルバム内の他の写真からは、アルバム表紙のチェックを外す。
        // ここで、アルバム表紙のチェックを外したレーコードの更新日は変更されたくないため、モデル側で更新情報の自動更新をOFF にしている。
        if ($request->has('is_cover') && $request->is_cover[$frame_id] == PhotoalbumContent::is_cover_on) {
            PhotoalbumContent::where('parent_id', $photoalbum_content->parent_id)->where('id', '<>', $photoalbum_content->id)->update(['is_cover' => PhotoalbumContent::is_cover_off]);
        }
    }

    /**
     *  フォトアルバムコンテンツのプレフィックス設定
     *
     * @param \App\Models\User\Photoalbums\PhotoalbumContent フォトアルバムコンテンツ（参照）
     */
    private function setPrefixPhotoalbumContent(&$photoalbum_content)
    {
        // 表紙フラグの更新で複数レコードを更新する処理について、この処理では更新日時を変更したくないため、自動化をしていないので、自分で設定。
        $photoalbum_content->updated_id = Auth::user()->id;
        $photoalbum_content->updated_name = Auth::user()->name;
        $photoalbum_content->updated_at = now()->format('Y-m-d H:i:s');
    }

    /**
     *  カバー写真かどうかのフラグのセット
     *
     * @param \Illuminate\Http\Request $request リクエスト
     * @param int $frame_id フレームID
     * @param \App\Models\User\Photoalbums\PhotoalbumContent フォトアルバムコンテンツ（参照）
     */
    private function setIsCover($request, $frame_id, &$photoalbum_content)
    {
        if ($request->has('is_cover') && $request->is_cover[$frame_id] == PhotoalbumContent::is_cover_on) {
            $photoalbum_content->is_cover = PhotoalbumContent::is_cover_on;
        } else {
            $photoalbum_content->is_cover = PhotoalbumContent::is_cover_off;
        }
    }

    /**
     *  コンテンツ変更処理
     *
     * @param \Illuminate\Http\Request $request リクエスト
     * @param int $page_id ページID
     * @param int $frame_id フレームID
     * @param int $photoalbum_content_id 対象レコードID
     * @return \Illuminate\Support\Collection リダイレクト先のパス
     */
    public function editContents($request, $page_id, $frame_id, $photoalbum_content_id)
    {
        // 対象のデータを取得して編集する。
        $photoalbum_content = PhotoalbumContent::find($photoalbum_content_id);
        $photoalbum = $this->getPluginBucket($this->frame->bucket_id);

        // ファイルの入れ替えがあるか。
        if ($request->hasFile('upload_file.'.$frame_id)) {
            // ファイルのエラーチェック
            $validator = $this->getUploadValidator($request, $photoalbum);
            if ($validator->fails()) {
                return back()->withErrors($validator)->withInput();
            }

            // アップロードされたファイルの取得
            $file = $request->file('upload_file')[$frame_id];

            // ファイルの入れ替え
            $this->overwriteFile($file, $photoalbum_content, $page_id);

            // 写真レコードのタイトル（空ならファイル名）
            $photoalbum_content->name = empty($request->title[$frame_id]) ? $file->getClientOriginalName() : $request->title[$frame_id];

            // 写真の幅、高さ（幅、高さを取得するためにImage オブジェクトを生成しておく）
            $img = Image::make($file->path());
            $photoalbum_content->width = $img->width();
            $photoalbum_content->height = $img->height();
            $photoalbum_content->mimetype = $file->getClientMimeType();
        } else {
            // 写真レコードのタイトル（空ならもともと設定されていた内容＝ファイル名）
            $photoalbum_content->name = empty($request->title[$frame_id]) ? $photoalbum_content->name() : $request->title[$frame_id];
        }
        $this->setIsCover($request, $frame_id, $photoalbum_content); // カバー写真かどうかのフラグ
        $photoalbum_content->description = $request->description[$frame_id]; // 説明欄
        $this->setPrefixPhotoalbumContent($photoalbum_content); // フォトアルバムコンテンツのプレフィックス設定
        $photoalbum_content->save();

        // アルバム表紙がチェックされていた場合、同じアルバム内の他の写真からは、アルバム表紙のチェックを外す。
        $this->updateCover($request, $frame_id, $photoalbum_content);

        // 登録後はリダイレクトして初期表示。
        return new Collection(['redirect_path' => url('/') . "/plugin/photoalbums/changeDirectory/" . $page_id . "/" . $frame_id . "/" . $photoalbum_content->parent_id . "/#frame-" . $frame_id ]);
    }

    /**
     *  動画コンテンツ変更処理
     *
     * @param \Illuminate\Http\Request $request リクエスト
     * @param int $page_id ページID
     * @param int $frame_id フレームID
     * @param int $photoalbum_content_id 対象レコードID
     * @return \Illuminate\Support\Collection リダイレクト先のパス
     */
    public function editVideo($request, $page_id, $frame_id, $photoalbum_content_id)
    {
        // 対象のデータを取得して編集する。
        $photoalbum_content = PhotoalbumContent::find($photoalbum_content_id);
        $photoalbum = $this->getPluginBucket($this->frame->bucket_id);

        // ファイルのエラーチェック
        $validator = $this->getVideoUploadValidator($request, $photoalbum, false);
        if ($validator->fails()) {
            return back()->withErrors($validator)->withInput();
        }

        // 動画ファイルの入れ替えがあるか。
        if ($request->hasFile('upload_video.'.$frame_id)) {

            // アップロードされたファイルの取得
            $video = $request->file('upload_video')[$frame_id];

            // ファイルの入れ替え
            $this->overwriteFile($video, $photoalbum_content, $page_id);

            // 写真レコードのタイトル（空ならファイル名）
            $photoalbum_content->name = empty($request->title[$frame_id]) ? $file->getClientOriginalName() : $request->title[$frame_id];

            $photoalbum_content->width = null;
            $photoalbum_content->height = null;
            $photoalbum_content->mimetype = $video->getClientMimeType();
        } else {
            // 写真レコードのタイトル（空ならもともと設定されていた内容＝ファイル名）
            $photoalbum_content->name = empty($request->title[$frame_id]) ? $photoalbum_content->name() : $request->title[$frame_id];
        }

        // ポスター写真の入れ替えがあるか。
        if ($request->hasFile('upload_poster.'.$frame_id)) {
            // アップロードされたファイルの取得
            $poster = $request->file('upload_poster')[$frame_id];

            // ファイルの入れ替え
            $upload = $this->overwriteFile($poster, $photoalbum_content, $page_id, 'poster_upload_id');
            $photoalbum_content->poster_upload_id = $upload->id;
        }

        $this->setIsCover($request, $frame_id, $photoalbum_content); // カバー写真かどうかのフラグ
        $photoalbum_content->description = $request->description[$frame_id]; // 説明欄
        $this->setPrefixPhotoalbumContent($photoalbum_content); // フォトアルバムコンテンツのプレフィックス設定
        $photoalbum_content->save();

        // アルバム表紙がチェックされていた場合、同じアルバム内の他の写真からは、アルバム表紙のチェックを外す。
        $this->updateCover($request, $frame_id, $photoalbum_content);

        // 登録後はリダイレクトして初期表示。
        return new Collection(['redirect_path' => url('/') . "/plugin/photoalbums/changeDirectory/" . $page_id . "/" . $frame_id . "/" . $photoalbum_content->parent_id . "/#frame-" . $frame_id ]);
    }

    /**
     *  コンテンツ削除処理
     *
     * @param \Illuminate\Http\Request $request リクエスト
     * @param int $page_id ページID
     * @param int $frame_id フレームID
     * @return \Illuminate\Support\Collection リダイレクト先のパス
     */
    public function deleteContents($request, $page_id, $frame_id)
    {
        $validator = $this->getContentsControlValidator($request); // 選択チェック
        if ($validator->fails()) {
            return back()->withErrors($validator)->withInput();
        }

        foreach ($request->photoalbum_content_id as $photoalbum_content_id) {
            $contents = PhotoalbumContent::descendantsAndSelf($photoalbum_content_id);
            if (!$this->canDelete($request, $contents)) {
                abort(403, '権限がありません。');
            };

            $this->deletePhotoalbumContents($photoalbum_content_id, $contents);
        }

        // 登録後はリダイレクトして初期表示。
        return new Collection(['redirect_path' => url('/') . "/plugin/photoalbums/changeDirectory/" . $page_id . "/" . $frame_id . "/" . $request->parent_id . "/#frame-" . $frame_id ]);
    }

    /**
     * フォトアルバムコンテンツを再帰的に削除する
     *
     * @param int $photoalbum_content_id フォトアルバムコンテンツID
     * @param \Illuminate\Support\Collection $photoalbum_contents フォトアルバムコンテンツのコレクション
     */
    private function deletePhotoalbumContents($photoalbum_content_id, $photoalbum_contents)
    {
        // アップロードテーブル削除、実ファイルの削除
/*
        foreach ($photoalbum_contents->whereNotNull('upload_id') as $content) {
            Storage::delete($this->getContentsFilePath($content->upload));
            Uploads::destroy($content->upload->id);
        }]
*/
        // アップロードテーブル削除、実ファイルの削除（画像・動画は'upload_id'、ポスター画像は'poster_upload_id'）
        foreach ($photoalbum_contents as $content) {
            if (!empty($content->upload_id)) {
                Storage::delete($this->getContentsFilePath($content->upload));
                Uploads::destroy($content->upload->id);
            }
            if (!empty($content->poster_upload_id)) {
                Storage::delete($this->getContentsFilePath($content->poster_upload));
                Uploads::destroy($content->poster_upload->id);
            }
        }

        // フォトアルバムコンテンツの削除
        PhotoalbumContent::find($photoalbum_content_id)->delete();
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

        // 一時ファイルは削除して、ダウンロードレスポンスを返す
        return response()->download(
            $save_path,
            'Files.zip',
            ['Content-Disposition' => 'filename=Files.zip']
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

        foreach ($request->photoalbum_content_id as $photoalbum_content_id) {
            $contents = PhotoalbumContent::select('photoalbum_contents.*', 'uploads.client_original_name')
                        ->leftJoin('uploads', 'photoalbum_contents.upload_id', '=', 'uploads.id')
                        ->descendantsAndSelf($photoalbum_content_id);
            if (!$this->canDownload($request, $contents)) {
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
            abort(404, 'ファイルがありません。');
        }
        $zip->close();
    }

    /**
     * ZIPファイルにフォルダ、ファイルを追加する。
     *
     * @param \ZipArchive $zip ZIPアーカイブ
     * @param \Illuminate\Support\Collection $contents フォトアルバムコンテンツのコレクション
     * @param string $parent_name 親フォトアルバムの名称
     */
    private function addContentsToZip(&$zip, $contents, $parent_name = '')
    {
        // 保存先のパス
        $save_path = $parent_name === '' ? $parent_name : $parent_name .'/';

        foreach ($contents as $content) {
            // ファイルが格納されていない空のフォルダだったら、空フォルダを追加
            if ($content->is_folder === PhotoalbumContent::is_folder_on && $content->isLeaf()) {
                $zip->addEmptyDir($save_path . $content->name);

            // ファイル追加
            } elseif ($content->is_folder === PhotoalbumContent::is_folder_off) {
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
                    $save_path . $content->client_original_name
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
        if (is_array($request->photoalbum_content_id) && count($request->photoalbum_content_id) !== 1) {
            return false;
        }

        // フォルダが選択された
        $photoalbum_content = PhotoalbumContent::find($request->photoalbum_content_id[0]);
        if ($photoalbum_content->is_folder === PhotoalbumContent::is_folder_on) {
            return false;
        }

        // 単数ファイルダウンロード用パスを設定しておく
        $this->download_url = "/file/" . $photoalbum_content->upload_id . '?response=download';

        return true;
    }

    /**
     * 一時フォルダのパスを取得する
     *
     * @return string 一時フォルダのパス
     */
    private function getTmpDirectory()
    {
        return storage_path('app/') . 'tmp/photoalbum/';
    }

    /**
     * フォトアルバムに格納されている実ファイルのパスを取得する
     *
     * @param \App\Models\Common\Uploads $upload アップロード
     * @return string ファイルのフルパス
     */
    private function getContentsFilePath($upload)
    {
        return $this->getDirectory($upload->id) . '/' . $this->getContentsFileName($upload);
    }

    /**
     * フォトアルバムに格納されている実ファイルの名称を取得する
     *
     * @param \App\Models\Common\Uploads $upload アップロード
     * @return string 物理ファイル名
     */
    private function getContentsFileName($upload)
    {
        return $upload->id . '.' . $upload->extension;
    }

    /**
     * フォトアルバムコンテンツを削除処理をできるか
     *
     * @param \Illuminate\Http\Request $request リクエスト
     * @param \Illuminate\Support\Collection $contents フォトアルバムコンテンツのコレクション
     * @return bool
     */
    private function canDelete($request, $photoalbum_contents)
    {
        return $this->canTouch($request, $photoalbum_contents);
    }

    /**
     * フォトアルバムコンテンツをダウンロード処理をできるか
     *
     * @param \Illuminate\Http\Request $request リクエスト
     * @param \Illuminate\Support\Collection $contents フォトアルバムコンテンツのコレクション
     * @return bool
     */
    private function canDownload($request, $photoalbum_contents)
    {
        return $this->canTouch($request, $photoalbum_contents);
    }

    /**
     * フォトアルバムコンテンツが触れる状態にあるか
     *
     * @param \Illuminate\Http\Request $request リクエスト
     * @param \Illuminate\Support\Collection $contents フォトアルバムコンテンツのコレクション
     * @return bool
     */
    private function canTouch($request, $photoalbum_contents)
    {
        foreach ($photoalbum_contents as $content) {
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
     */
    public function listBuckets($request, $page_id, $frame_id, $id = null)
    {
        // 表示テンプレートを呼び出す。
        return $this->view('list_buckets', [
            'plugin_buckets' => Photoalbum::orderBy('created_at', 'desc')->paginate(10),
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
            'photoalbum' => $this->getPluginBucket($bucket_id),
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

        $bucket_id = $this->savePhotoalbum($request, $frame_id, $bucket_id);

        // 登録後はリダイレクトして編集ページを開く。
        return new Collection(['redirect_path' => url('/') . "/plugin/photoalbums/editBuckets/" . $page_id . "/" . $frame_id . "/" . $bucket_id . "#frame-" . $frame_id]);
    }

    /**
     * フォトアルバム登録/更新のバリデーターを取得する。
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
            'image_upload_max_size'=> [
                'required',
                Rule::in(UploadMaxSize::getMemberKeys()),
            ],
            'video_upload_max_size'=> [
                'required',
                Rule::in(UploadMaxSize::getMemberKeys()),
            ],
        ]);
        $validator->setAttributeNames([
            'name' => 'フォトアルバム名',
            'image_upload_max_size' => '画像の最大サイズ',
            'video_upload_max_size' => '動画の最大サイズ',
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
                Rule::unique('photoalbum_contents', 'name')->where(function ($query) use ($request) {
                    return $query->where('parent_id', $request->parent_id);
                }),
            ],
        ]);
        $validator->setAttributeNames([
            'folder_name.*' => 'フォトアルバム名',
        ]);

        return $validator;
    }

    /**
     * 画像ファイルアップロードのバリデーターを取得する。
     *
     * @param \Illuminate\Http\Request $request リクエスト
     * @param  App\Models\User\Photoalbums\Photoalbum フォトアルバム
     * @return \Illuminate\Contracts\Validation\Validator バリデーター
     */
    private function getUploadValidator($request, $photoalbum)
    {
        // ファイルの存在チェック
        $rules['upload_file.*'] = [
            'required',
        ];

        // ファイルサイズと形式チェック
        if ($photoalbum->image_upload_max_size !== UploadMaxSize::infinity) {
            $rules['upload_file.*'][] = 'max:' . $photoalbum->image_upload_max_size;
            $rules['upload_file.*'][] = 'mimes:jpg,jpe,jpeg,png,gif';
        }

        // 項目名設定
        $validator = Validator::make($request->all(), $rules);
        $validator->setAttributeNames([
            'upload_file.*' => 'ファイル',
        ]);

        return $validator;
    }

    /**
     * 動画ファイルアップロードのバリデーターを取得する。
     *
     * @param \Illuminate\Http\Request $request リクエスト
     * @param  App\Models\User\Photoalbums\Photoalbum フォトアルバム
     * @return \Illuminate\Contracts\Validation\Validator バリデーター
     */
    private function getVideoUploadValidator($request, $photoalbum, $video_require = true)
    {
        // ファイルの存在チェック
        $rules['upload_video.*'] = [];
        if ($video_require) {
            $rules['upload_video.*'][] = ['required'];
        } else {
            $rules['upload_video.*'][] = ['nullable'];
        }

        // ファイルサイズチェック(ポスターは画像サイズでチェック)
        if ($photoalbum->video_upload_max_size !== UploadMaxSize::infinity) {
            $rules['upload_video.*'][] = 'max:' . $photoalbum->video_upload_max_size;
            $rules['upload_video.*'][] = 'mimetypes:video/mp4';
            $rules['upload_poster.*'][] = 'nullable';
            $rules['upload_poster.*'][] = 'max:' . $photoalbum->image_upload_max_size;
            $rules['upload_poster.*'][] = 'mimetypes:image/jpeg,image/png,image/gif';
        }

        // オリジナルメッセージ（image/jpeg, image/png, image/gifのうちいずれかの形式のファイルを指定してください。では、わかりにくいので。）
        $error_message = [
            'upload_video.*.mimetypes' => '動画ファイルには、mp4形式のファイルを指定してください。',
            'upload_poster.*.mimetypes' => 'ポスター画像には、jpeg, png, gif のうちいずれかの形式のファイルを指定してください。',
        ];

        // 項目名設定
        $validator = Validator::make($request->all(), $rules, $error_message);
        $validator->setAttributeNames([
            'upload_video.*' => '動画ファイル',
            'upload_poster.*' => 'ポスター画像',
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
            'photoalbum_content_id' => [
                'required',
            ],
        ]);

        // 項目のエラーチェック
        $validator->setAttributeNames([
            'photoalbum_content_id' => 'ファイル選択',
        ]);

        return $validator;
    }


    /**
     * フォトアルバムを登録する。
     *
     * @param \Illuminate\Http\Request $request リクエスト
     * @param int $frame_id フレームID
     * @param int $bucket_id バケツID
     * @return int バケツID
     */
    private function savePhotoalbum($request, $frame_id, $bucket_id)
    {
        // バケツの取得。なければ登録。
        $bucket = Buckets::updateOrCreate(
            ['id' => $bucket_id],
            ['bucket_name' => $request->name, 'plugin_name' => 'photoalbums'],
        );

        // フレームにバケツの紐づけ
        $frame = Frame::find($frame_id)->update(['bucket_id' => $bucket->id]);

        // プラグインバケツを取得(なければ新規オブジェクト)
        // プラグインバケツにデータを設定して保存
        $photoalbum = $this->getPluginBucket($bucket->id);
        $photoalbum->name = $request->name;
        $photoalbum->image_upload_max_size = $request->image_upload_max_size;
        $photoalbum->image_upload_max_px = $request->image_upload_max_px;
        $photoalbum->video_upload_max_size = $request->video_upload_max_size;
        $photoalbum->save();

        $this->saveRootPhotoalbumContent($photoalbum);

        return $bucket->id;
    }

    /**
     * ルートディレクトリを登録する
     *
     * @param App\Models\User\Photoalbums\Photoalbum $photoalbum フォトアルバム
     */
    private function saveRootPhotoalbumContent($photoalbum)
    {
        PhotoalbumContent::updateOrCreate(
            ['photoalbum_id' => $photoalbum->id, 'parent_id' => null, 'is_folder' => PhotoalbumContent::is_folder_on],
            ['name' => $photoalbum->name]
        );
    }

    /**
     *  フォトアルバム削除処理
     *
     * @param \Illuminate\Http\Request $request リクエスト
     * @param int $page_id ページID
     * @param int $frame_id フレームID
     */
    public function destroyBuckets($request, $page_id, $frame_id, $photoalbum_id)
    {
        // プラグインバケツの取得
        $photoalbum = Photoalbum::find($photoalbum_id);
        if (empty($photoalbum)) {
            return;
        }

        // FrameのバケツIDの更新
        Frame::where('id', $frame_id)->update(['bucket_id' => null]);

        // バケツ削除
        Buckets::find($photoalbum->bucket_id)->delete();

        // フォトアルバムコンテンツ削除
        $photoalbum_content = $this->fetchPhotoalbumContent(null, $photoalbum->id);
        $this->deletePhotoalbumContents(
            $photoalbum_content->id,
            PhotoalbumContent::descendantsAndSelf($photoalbum_content->id)
        );

        // プラグインデータ削除
        $photoalbum->delete();

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

        // Photoalbums の特定
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
     */
    public function editView($request, $page_id, $frame_id)
    {
        // 表示テンプレートを呼び出す。
        return $this->view('frame', [
            'photoalbum' => $this->getPluginBucket($this->getBucketId()),
        ]);
    }

    /**
     * フレーム表示設定の保存
     */
    public function saveView($request, $page_id, $frame_id, $photoalbum_id)
    {
        // フレーム設定保存
        $this->saveFrameConfigs($request, $frame_id, PhotoalbumFrameConfig::getMemberKeys());
        // 更新したので、frame_configsを設定しなおす
        $this->refreshFrameConfigs();

        return;
    }

    /**
     * フレーム設定を保存する。
     *
     * @param Illuminate\Http\Request $request リクエスト
     * @param int $frame_id フレームID
     * @param array $frame_config_names フレーム設定のname配列
     */
    private function saveFrameConfigs(\Illuminate\Http\Request $request, int $frame_id, array $frame_config_names)
    {
        foreach ($frame_config_names as $key => $value) {

            if (!$request->$value == '0' && empty($request->$value)) {
                return;
            }

            FrameConfig::updateOrCreate(
                ['frame_id' => $frame_id, 'name' => $value],
                ['value' => $request->$value]
            );
        }
    }
}
