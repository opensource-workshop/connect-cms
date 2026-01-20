<?php

namespace App\Plugins\User\Photoalbums;

use Illuminate\Filesystem\Filesystem;
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

use App\Utilities\Zip\UnzipUtils;

use App\Traits\ConnectCommonTrait;

use Intervention\Image\Facades\Image;

use App\Plugins\User\UserPluginBase;
use Illuminate\Support\Facades\Session;

/**
 * フォトアルバム・プラグイン
 *
 * @author 永原　篤 <nagahara@opensource-workshop.jp>
 * @copyright OpenSource-WorkShop Co.,Ltd. All Rights Reserved
 * @category フォトアルバム・プラグイン
 * @package Controller
 * @plugin_title フォトアルバム
 * @plugin_desc 写真などの画像や動画を管理できる、メディアアルバムです。
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

    // サポートされている拡張子のリスト
    private $supported_extensions = ['jpg', 'jpe', 'jpeg', 'png', 'gif'];

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
        $functions['get']  = ['index', 'download', 'changeDirectory', 'embed', 'detail'];
        $functions['post'] = ['makeFolder', 'editFolder', 'upload', 'uploadVideo', 'editContents', 'editVideo', 'deleteContents', 'updateViewSequence', 'updateHiddenFolders'];
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
        $role_check_table["updateViewSequence"] = array('frames.edit');
        $role_check_table["updateHiddenFolders"] = array('frames.edit');
        return $role_check_table;
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
     * @method_title アルバム表示
     * @method_desc アルバムの一覧や表紙に追加した写真・動画の一覧が表示されます。
     * @method_detail
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

        // photoalbum_idが配置バケツと違う場合、表示させない
        if (empty($parent) || $parent->photoalbum_id != $photoalbum->id) {
            return;
        }

        $hidden_folder_ids = $this->getHiddenFolderIds($this->frame_configs);
        if ($this->isHiddenPhotoalbumContent($parent, $hidden_folder_ids)) {
            return;
        }

        // フォルダ、ファイルの比較条件の取得
        $sort_folder = FrameConfig::getConfigValue($this->frame_configs, PhotoalbumFrameConfig::sort_folder);
        $sort_file = FrameConfig::getConfigValue($this->frame_configs, PhotoalbumFrameConfig::sort_file);

        $photoalbum_contents = $this->getSortedChildren($parent, $sort_folder, $sort_file);
        if (!empty($hidden_folder_ids)) {
            $photoalbum_contents = $photoalbum_contents->reject(function ($content) use ($hidden_folder_ids) {
                return $content->is_folder == PhotoalbumContent::is_folder_on
                    && in_array($content->id, $hidden_folder_ids, true);
            });
        }

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
        $photoalbum = $this->getPluginBucket($this->frame->bucket_id);
        $photoalbum_content = PhotoalbumContent::where('id', $photoalbum_content_id)
            ->where('photoalbum_id', $photoalbum->id)
            ->first();

        if (empty($photoalbum_content)) {
            abort(404, 'コンテンツがありません。');
        }

        $hidden_folder_ids = $this->getHiddenFolderIds($this->frame_configs);
        if (!empty($hidden_folder_ids)) {
            $ancestors = PhotoalbumContent::ancestorsAndSelf($photoalbum_content->id);
            if ($this->isHiddenPhotoalbumContent($photoalbum_content, $hidden_folder_ids, $ancestors->keyBy('id'))) {
                abort(404, 'コンテンツがありません。');
            }
        }

        return $this->view('embed', [
            'photoalbum' => $photoalbum,
            'photoalbum_content' => $photoalbum_content,
        ]);
    }

    /**
     * 詳細画面を表示する
     * この関数は動画用（画像でも使えるかなとは思いつつ作ってますが）
     *
     * @param \Illuminate\Http\Request $request リクエスト
     * @param int $page_id ページID
     * @param int $frame_id フレームID
     * @param int $photoalbum_content_id コンテンツID
     * @return mixed $value テンプレートに渡す内容
     */
    public function detail($request, $page_id, $frame_id, $photoalbum_content_id)
    {
        // バケツデータとフォトアルバムデータ取得、フォトアルバムのルート階層はphotoalbum->id == nullのもの。
        $photoalbum = $this->getPluginBucket($this->frame->bucket_id);
        $photoalbum_content = PhotoalbumContent::where('id', $photoalbum_content_id)
            ->where('photoalbum_id', $photoalbum->id)
            ->first();

        if (empty($photoalbum_content)) {
            abort(404, 'コンテンツがありません。');
        }

        $hidden_folder_ids = $this->getHiddenFolderIds($this->frame_configs);
        if (!empty($hidden_folder_ids)) {
            $ancestors = PhotoalbumContent::ancestorsAndSelf($photoalbum_content->id);
            if ($this->isHiddenPhotoalbumContent($photoalbum_content, $hidden_folder_ids, $ancestors->keyBy('id'))) {
                abort(404, 'コンテンツがありません。');
            }
        }

        return $this->view('detail', [
            'photoalbum' => $photoalbum,
            'photoalbum_content' => $photoalbum_content,
            'breadcrumbs' => $this->fetchBreadCrumbs($photoalbum->id, $photoalbum_content->id),
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
        if (UnzipUtils::useZipArchive()) {

        }

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
     * 非表示対象のフォルダIDを取得する
     */
    private function getHiddenFolderIds(Collection $frame_configs): array
    {
        $hidden_folder_value = FrameConfig::getConfigValue($frame_configs, PhotoalbumFrameConfig::hidden_folder_ids);
        if (empty($hidden_folder_value)) {
            return [];
        }

        $hidden_folder_ids = is_array($hidden_folder_value)
            ? $hidden_folder_value
            : explode(FrameConfig::CHECKBOX_SEPARATOR, (string) $hidden_folder_value);

        $hidden_folder_ids = array_map('intval', $hidden_folder_ids);
        $hidden_folder_ids = array_filter($hidden_folder_ids, static function ($id) {
            return $id > 0;
        });

        return array_values(array_unique($hidden_folder_ids));
    }

    /**
     * 非表示設定に該当するフォトアルバムコンテンツか判定する
     */
    private function isHiddenPhotoalbumContent(PhotoalbumContent $content, array $hidden_folder_ids, ?Collection $contents_by_id = null): bool
    {
        if (empty($hidden_folder_ids)) {
            return false;
        }

        $current = $content;
        while (!empty($current)) {
            if ($current->is_folder == PhotoalbumContent::is_folder_on
                && in_array($current->id, $hidden_folder_ids, true)) {
                return true;
            }

            if (is_null($current->parent_id)) {
                break;
            }

            $current = $contents_by_id ? $contents_by_id->get($current->parent_id) : PhotoalbumContent::find($current->parent_id);
        }

        return false;
    }

    /**
     * 指定した親の子要素をフレーム設定に合わせて並び替えて取得する
     */
    private function getSortedChildren(PhotoalbumContent $parent, ?string $sort_folder, ?string $sort_file, ?Collection $preloaded_children = null)
    {
        $children = is_null($preloaded_children)
            ? $parent->children()->get()
            : $preloaded_children->get($parent->id, collect());

        // 設定画面などで事前に読み込んだ子要素一覧を再利用し、追加クエリを避ける
        if (!is_null($preloaded_children) && $children->isEmpty()) {
            return collect();
        }

        return $children->sort(function ($first, $second) use ($sort_folder, $sort_file) {
            return $this->comparePhotoalbumContents($first, $second, $sort_folder, $sort_file);
        })->values();
    }

    /**
     * 並び替え比較処理
     */
    private function comparePhotoalbumContents(PhotoalbumContent $first, PhotoalbumContent $second, ?string $sort_folder, ?string $sort_file)
    {
        if ($first->is_folder == $second->is_folder) {
            $sort_key = $first->is_folder == PhotoalbumContent::is_folder_on ? $sort_folder : $sort_file;

            switch ($sort_key) {
                case PhotoalbumSort::name_desc:
                    return strnatcasecmp($second->displayName, $first->displayName);
                case PhotoalbumSort::created_asc:
                    return $this->compareDates($first->created_at, $second->created_at);
                case PhotoalbumSort::created_desc:
                    return $this->compareDates($second->created_at, $first->created_at);
                case PhotoalbumSort::manual_order:
                    $sequence = $first->display_sequence <=> $second->display_sequence;
                    return $sequence !== 0 ? $sequence : $first->id <=> $second->id;
                default:
                    return strnatcasecmp($first->displayName, $second->displayName);
            }
        }

        return $second->is_folder <=> $first->is_folder;
    }

    /**
     * 日付比較
     */
    private function compareDates($first, $second)
    {
        $first_timestamp = $this->convertToTimestamp($first);
        $second_timestamp = $this->convertToTimestamp($second);

        return $first_timestamp <=> $second_timestamp;
    }

    /**
     * 日付をタイムスタンプへ変換する
     */
    private function convertToTimestamp($value)
    {
        if (empty($value)) {
            return 0;
        }

        if ($value instanceof \Carbon\Carbon) {
            return $value->timestamp;
        }

        return strtotime((string) $value) ?: 0;
    }

    /**
     * 並び替え済みの子要素マップを作成する
     */
    private function buildSortedChildrenMap(PhotoalbumContent $root, ?string $sort_folder, ?string $sort_file, ?Collection $preloaded_children = null)
    {
        $map = [];
        $this->appendSortedChildrenToMap($root, $sort_folder, $sort_file, $map, $preloaded_children);
        return $map;
    }

    /**
     * 事前取得済みデータを元に、各親IDの並び済み子リストをマップへ格納する
     */
    private function appendSortedChildrenToMap(PhotoalbumContent $node, ?string $sort_folder, ?string $sort_file, array &$map, ?Collection $preloaded_children = null)
    {
        if (isset($map[$node->id])) {
            return;
        }

        $children = $this->getSortedChildren($node, $sort_folder, $sort_file, $preloaded_children);
        $map[$node->id] = $children;

        foreach ($children as $child) {
            if ($child->is_folder == PhotoalbumContent::is_folder_off) {
                continue;
            }

            $this->appendSortedChildrenToMap($child, $sort_folder, $sort_file, $map, $preloaded_children);
        }
    }

    /**
     * 次に採番する表示順を取得する
     */
    private function getNextDisplaySequence($parent_id)
    {
        $max = PhotoalbumContent::where('parent_id', $parent_id)->max('display_sequence');
        if (is_null($max)) {
            return 1;
        }
        return $max + 1;
    }

    /**
     * 表示順を付与して子要素を作成する
     */
    private function createChildContent($parent, $attributes)
    {
        $attributes['display_sequence'] = $this->getNextDisplaySequence($parent->id);
        return $parent->children()->create($attributes);
    }

    /**
     * 指定した親直下の表示順を詰め直す
     */
    private function normalizeDisplaySequence($parent_id)
    {
        $siblings = PhotoalbumContent::where('parent_id', $parent_id)
            ->orderBy('is_folder', 'desc')
            ->orderBy('display_sequence')
            ->orderBy('id')
            ->get();

        $sequence = 1;
        foreach ($siblings as $sibling) {
            if ($sibling->display_sequence != $sequence) {
                $sibling->display_sequence = $sequence;
                $sibling->save();
            }
            $sequence++;
        }
    }

    /**
     * アルバム作成処理
     *
     * @param \Illuminate\Http\Request $request リクエスト
     * @param int $page_id ページID
     * @param int $frame_id フレームID
     * @return \Illuminate\Support\Collection リダイレクト先のパス
     * @method_title アルバム作成
     * @method_desc アルバムを作成できます。
     * @method_detail アルバムの中にもアルバムを作成できます。
     */
    public function makeFolder($request, $page_id, $frame_id)
    {
        $validator = $this->getMakeFoldertValidator($request);
        if ($validator->fails()) {
            return back()->withErrors($validator)->withInput();
        }

        $photoalbum = $this->getPluginBucket($this->frame->bucket_id);
        $parent = $this->fetchPhotoalbumContent($request->parent_id);

        $this->createChildContent($parent, [
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
     * @method_title アップロード
     * @method_desc 画像や動画ファイルをアップロードできます。
     * @method_detail ルート階層にも、フォルダの中にもファイルをアップロードできます。
     */
    public function upload($request, $page_id, $frame_id)
    {
        $photoalbum = $this->getPluginBucket($this->frame->bucket_id);
        $validator = $this->getUploadValidator($request, $photoalbum, ',zip'); // バリデータ
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
        $this->writeVideo($request, $page_id, $frame_id, $photoalbum, $parent);

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
        // zip ファイルの判定
        $file_extension = $request->file('upload_file')[$frame_id]->extension();

        if ($file_extension == 'zip') {
            // 展開、連番で保存、キャビネットに格納するときに名前を復元の順で処理。
            // UTF-8変換＆展開方式では、「写真テスト」フォルダと「写真テスト/ロゴ」フォルダに格納した画像で、「写真テスト」フォルダの画像がルート階層になる現象があったため。

            // zip ファイルの展開
            $path = $request->file('upload_file')[$frame_id]->store('tmp/photoalbum');

            // zip ファイルを連番で展開して、配列で返す。
            list($album_paths, $tmp_dirs) = UnzipUtils::unzipSerialNumber($path, 'photoalbum');

            // フォトアルバム・プラグインにファイル追加
            foreach ($album_paths as $album_path) {
                // フォトアルバム内のアルバムレコードがあるか探す必要があるが、SQL発行回数を減らすため、バケツのレコードをここで読み込んでおく。
                // ここで読む意味は、データを追加するたびに、新しい状態で親を探す必要があるため。
                $photoalbum_contents = PhotoalbumContent::where('photoalbum_id', $photoalbum->id)->get();

                // ループしながら、フォルダの検索。起点になるフォルダidをここで初期化（画面でアップロードしたフォルダに）する。
                $parent_id = $parent->id;
                $create_parent = clone $parent; // フォルダを作成するときのための、ループ内で変更されていく親

                // アルバム有無の確認
                $album_dir_paths = explode('/', dirname($album_path['album_path']));
                foreach ($album_dir_paths as $album_dir_path) {
                    $folder = $photoalbum_contents->where('is_folder', 1)->where('parent_id', $parent_id)->where('name', $album_dir_path)->first();
                    if (empty($folder)) {
                        // アルバムがないので作成する。（作成したら、それをルーム内の親に設定する）
                        $create_parent = $this->createChildContent($create_parent, [
                            'photoalbum_id' => $photoalbum->id,
                            'upload_id' => null,
                            'name' => $album_dir_path,
                            'description' => null,
                            'is_folder' => PhotoalbumContent::is_folder_on,
                            'is_cover' => PhotoalbumContent::is_cover_off,
                        ]);
                    } else {
                        $create_parent = $folder;
                    }
                }

                // ファイル追加
                $filesystem = new Filesystem();
                $file_params = [
                    'path' => storage_path('app/') . $album_path['src_path'],
                    'client_original_name' => basename($album_path['album_path']),
                    'mimetype' => $filesystem->mimeType(storage_path('app/') . $album_path['src_path']),
                    'extension' => $filesystem->extension(storage_path('app/') . $album_path['src_path']),
                    'size' => $filesystem->size(storage_path('app/') . $album_path['src_path']),
                    'name' => basename($album_path['album_path']),
                    'description' => null,
                    'is_cover' => PhotoalbumContent::is_cover_off,
                ];
                $new_content = $this->writeFileImpl($file_params, $page_id, $frame_id, $photoalbum, $create_parent);
            }

            // 一時フォルダ、ファイルの削除
            UnzipUtils::deleteUnzipTmp($tmp_dirs, $path);

        } else {
            // zip 以外の処理
            $file = $request->file('upload_file')[$frame_id];
            $file_params = [
                'path' => $file->path(),
                'client_original_name' => $file->getClientOriginalName(),
                'mimetype' => $file->getClientMimeType(),
                'extension' => $file->getClientOriginalExtension(),
                'size' => $file->getSize(),
                'name' => empty($request->title[$frame_id]) ? $file->getClientOriginalName() : $request->title[$frame_id],
                'description' => $request->description[$frame_id],
                'is_cover' => ($request->has('is_cover') && $request->is_cover[$frame_id]) ? PhotoalbumContent::is_cover_on : PhotoalbumContent::is_cover_off,
            ];
            $this->writeFileImpl($file_params, $page_id, $frame_id, $photoalbum, $parent);
        }
    }

    /**
     * ファイル新規保存処理
     *
     * @param array $file_params リクエストから必要なものを詰めたオブジェクト
     * @param int $page_id ページID
     * @param int $frame_id フレームID
     * @param \App\Models\User\Photoalbums\Photoalbum $photoalbum バケツレコード
     * @param \App\Models\User\Photoalbums\PhotoalbumContent $parent アルバムレコード
     */
    private function writeFileImpl($file_params, $page_id, $frame_id, $photoalbum, $parent)
    {
        // 画像ファイル
//        $file = $file_params['upload_file'];

        // 必要なら縮小して、\Intervention\Image\Image オブジェクトを受け取る。
//        $image = Uploads::shrinkImage($file, $photoalbum->image_upload_max_px);
        $image = Uploads::shrinkImage($file_params['path'], $photoalbum->image_upload_max_px);

        // リサイズ後のバイナリデータのサイズを取得
        $extension = strtolower($file_params['extension']);
        $resized_image_size = strlen((string) $image->encode($extension));

        // uploads テーブルに情報追加、ファイルのid を取得する
        $upload = Uploads::create([
/*
            'client_original_name' => $file->getClientOriginalName(),
            'mimetype'             => $file->getClientMimeType(),
            'extension'            => $file->getClientOriginalExtension(),
            'size'                 => $file->getSize(),
*/
            'client_original_name' => $file_params['client_original_name'],
            'mimetype'             => $file_params['mimetype'],
            'extension'            => $file_params['extension'],
            'size'                 => $resized_image_size,

            'plugin_name'          => 'photoalbums',
            'page_id'              => $page_id,
            'temporary_flag'       => 0,
            'created_id'           => empty(Auth::user()) ? null : Auth::user()->id,
        ]);

        // ファイル保存
        $directory = $this->makeDirectory($upload->id);
        $image->save(storage_path('app/') . $directory . '/' . $upload->id . '.' . $file_params['extension']);

        return $this->createChildContent($parent, [
            'photoalbum_id' => $parent->photoalbum_id,
            'upload_id' => $upload->id,
            'name' => $file_params['name'],
            'width' => $image->width(),
            'height' => $image->height(),
            'description' => $file_params['description'],
            'is_folder' => PhotoalbumContent::is_folder_off,
            'is_cover' => $file_params['is_cover'],
            'mimetype' => $upload->mimetype,
        ]);
    }

    /**
     * 動画ファイル新規保存処理
     *
     * @param \Illuminate\Http\UploadedFile $file file
     * @param int $page_id ページID
     * @param int $frame_id フレームID
     * @param \App\Models\User\Photoalbums\Photoalbum $photoalbum バケツレコード
     * @param \App\Models\User\Photoalbums\PhotoalbumContent $parent アルバムレコード
     */
    private function writeVideo($request, $page_id, $frame_id, $photoalbum, $parent)
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
        $this->createChildContent($parent, [
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
        $upload = Uploads::updateOrCreate(['id' => $photoalbum_content->$target_column], [
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
        $file->storeAs($this->getDirectory($upload->id), $this->getContentsFileName($upload));

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

            // 写真レコードのタイトル
            $photoalbum_content->name = empty($request->title[$frame_id]) ? '' : $request->title[$frame_id];

            // 写真の幅、高さ（幅、高さを取得するためにImage オブジェクトを生成しておく）
            $img = Image::make($file->path());
            $photoalbum_content->width = $img->width();
            $photoalbum_content->height = $img->height();
            $photoalbum_content->mimetype = $file->getClientMimeType();
        } else {
            // 写真レコードのタイトル
            $photoalbum_content->name = empty($request->title[$frame_id]) ? '' : $request->title[$frame_id];
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
     * @method_title ファイル削除
     * @method_desc ファイルやフォルダを削除できます。
     * @method_detail
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
        // アップロードテーブル削除、実ファイルの削除（画像・動画は'upload_id'、ポスター画像は'poster_upload_id'）
        foreach ($photoalbum_contents as $content) {
            if (!empty($content->upload_id)) {
                Storage::delete($this->getContentsFilePath($content->upload));
                Uploads::destroy($content->upload->id);
            }
            if (!empty($content->poster_upload_id)) {
                Storage::delete($this->getContentsFilePath($content->posterUpload));
                Uploads::destroy($content->posterUpload->id);
            }
        }

        // フォトアルバムコンテンツの削除
        $target = PhotoalbumContent::find($photoalbum_content_id);
        if (empty($target)) {
            return;
        }

        $parent_id = $target->parent_id;
        $target->delete();

        $this->normalizeDisplaySequence($parent_id);
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

        $hidden_folder_ids = $this->getHiddenFolderIds($this->frame_configs);
        if (!empty($hidden_folder_ids)) {
            $photoalbum = $this->getPluginBucket($this->frame->bucket_id);
            $visible_ids = $this->filterVisibleDownloadContentIds($photoalbum, $request->photoalbum_content_id, $hidden_folder_ids);
            $request->merge(['photoalbum_content_id' => $visible_ids]);
            if (empty($visible_ids)) {
                return back()
                    ->withErrors(['photoalbum_content_id' => 'ダウンロードできるコンテンツがありません。'])
                    ->withInput()
                    ->with('parent_id', $request->parent_id);
            }
        }

        // ファイルの単数選択ならZIP化せずダウンロードレスポンスを返す
        if ($this->isSelectedSingleFile($request)) {
            return redirect($this->download_url);
        }

        $save_path = $this->getTmpDirectory() . uniqid('', true) . '.zip';
        $this->makeZip($save_path, $request, $hidden_folder_ids ?? []);

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
     * @param array $hidden_folder_ids 非表示フォルダID
     */
    private function makeZip($save_path, $request, array $hidden_folder_ids = [])
    {
        $zip = new \ZipArchive();
        $zip->open($save_path, \ZipArchive::CREATE);

        foreach ($request->photoalbum_content_id as $photoalbum_content_id) {
            $contents = PhotoalbumContent::select('photoalbum_contents.*', 'uploads.client_original_name')
                        ->leftJoin('uploads', 'photoalbum_contents.upload_id', '=', 'uploads.id')
                        ->descendantsAndSelf($photoalbum_content_id);
            $contents_by_id = $contents->keyBy('id');
            $download_contents = $contents;
            if (!empty($hidden_folder_ids)) {
                $download_contents = $contents->reject(function ($content) use ($hidden_folder_ids, $contents_by_id) {
                    return $this->isHiddenPhotoalbumContent($content, $hidden_folder_ids, $contents_by_id);
                });
            }

            if (!$this->canDownload($request, $download_contents)) {
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

            $this->addContentsToZip($zip, $contents->toTree(), '', $hidden_folder_ids, $contents_by_id);
        }

        // 空のZIPファイルが出来たら404
        if ($zip->count() === 0) {
            // zipファイル後始末
            $zip->close();
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
     * @param \Illuminate\Support\Collection $contents フォトアルバムコンテンツのコレクション
     * @param string $parent_name 親フォトアルバムの名称
     * @param array $hidden_folder_ids 非表示フォルダID
     * @param \Illuminate\Support\Collection|null $contents_by_id コンテンツIDマップ
     */
    private function addContentsToZip(&$zip, $contents, $parent_name = '', array $hidden_folder_ids = [], ?Collection $contents_by_id = null)
    {
        // 保存先のパス
        $save_path = $parent_name === '' ? $parent_name : $parent_name .'/';

        foreach ($contents as $content) {
            if (!empty($hidden_folder_ids) && $this->isHiddenPhotoalbumContent($content, $hidden_folder_ids, $contents_by_id)) {
                continue;
            }

            $children = $content->children;

            // 非表示を除外した後に子要素がない場合は空フォルダを追加
            if ($content->is_folder === PhotoalbumContent::is_folder_on) {
                if (!empty($hidden_folder_ids)) {
                    $children = $children->reject(function ($child) use ($hidden_folder_ids, $contents_by_id) {
                        return $this->isHiddenPhotoalbumContent($child, $hidden_folder_ids, $contents_by_id);
                    });
                }
                if ($children->isEmpty()) {
                    $zip->addEmptyDir($save_path . $content->name);
                }

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
            $this->addContentsToZip($zip, $children, $save_path . $content->name, $hidden_folder_ids, $contents_by_id);
        }
    }

    /**
     * 非表示対象を除外したダウンロード対象IDを取得する
     */
    private function filterVisibleDownloadContentIds(Photoalbum $photoalbum, $content_ids, array $hidden_folder_ids): array
    {
        $content_ids = is_array($content_ids) ? $content_ids : [$content_ids];
        $content_ids = array_values(array_filter(array_map('intval', $content_ids), static function ($id) {
            return $id > 0;
        }));

        if (empty($content_ids) || empty($photoalbum->id)) {
            return [];
        }

        $contents = PhotoalbumContent::where('photoalbum_id', $photoalbum->id)
            ->whereIn('id', $content_ids)
            ->get();

        if (empty($hidden_folder_ids)) {
            return $contents->pluck('id')->all();
        }

        $visible_ids = [];
        foreach ($contents as $content) {
            $ancestors = PhotoalbumContent::ancestorsAndSelf($content->id);
            if ($this->isHiddenPhotoalbumContent($content, $hidden_folder_ids, $ancestors->keyBy('id'))) {
                continue;
            }
            $visible_ids[] = $content->id;
        }

        return $visible_ids;
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
     *
     * @method_title 選択
     * @method_desc このフレームに表示するフォトアルバムを選択します。
     * @method_detail
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
     *
     * @method_title 作成
     * @method_desc フォトアルバムを新しく作成します。
     * @method_detail フォトアルバム名やアップロード最大サイズ等を入力してフォトアルバムを作成できます。
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
    private function getUploadValidator($request, $photoalbum, $add_mimes = '')
    {
        // ファイルの存在チェック
        $rules['upload_file.*'] = [
            'required',
        ];

        // ファイルサイズと形式チェック
        if ($photoalbum->image_upload_max_size !== UploadMaxSize::infinity) {
            $rules['upload_file.*'][] = 'max:' . $photoalbum->image_upload_max_size;
            $rules['upload_file.*'][] = 'mimes:' . implode(',', $this->supported_extensions) . $add_mimes;
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
            ['name' => $photoalbum->name, 'display_sequence' => 1]
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
     *
     * @method_title 表示設定
     * @method_desc このフレームに表示する際のフォトアルバムをカスタマイズできます。
     * @method_detail ファイルの並び順を指定できます。
     */
    public function editView($request, $page_id, $frame_id)
    {
        $photoalbum = $this->getPluginBucket($this->getBucketId());

        $sort_folder = FrameConfig::getConfigValue($this->frame_configs, PhotoalbumFrameConfig::sort_folder);
        $sort_file = FrameConfig::getConfigValue($this->frame_configs, PhotoalbumFrameConfig::sort_file);

        $preview_root = null;
        $sorted_children_map = [];
        $focus_open_ids = [];
        if (!empty($photoalbum->id)) {
            $all_contents = PhotoalbumContent::with(['upload', 'posterUpload'])
                ->where('photoalbum_id', $photoalbum->id)
                ->get();

            $preview_root = $all_contents->firstWhere('parent_id', null);

            if (!empty($preview_root)) {
                // プレビュー／編集の双方で使えるよう、親IDごとの並び済みリストを構築
                $grouped_children = $all_contents->groupBy('parent_id');
                $sorted_children_map = $this->buildSortedChildrenMap($preview_root, $sort_folder, $sort_file, $grouped_children);
            }

            $focus_open_ids = $this->buildFocusOpenIds($all_contents, session('photoalbum_sort_focus'));
        }
        // 表示テンプレートを呼び出す。
        return $this->view('frame', [
            'photoalbum' => $photoalbum,
            'manual_sort_root' => $preview_root,
            'sort_folder' => $sort_folder,
            'sort_file' => $sort_file,
            'sorted_children_map' => $sorted_children_map,
            'focus_open_ids' => $focus_open_ids,
        ]);
    }

    /**
     * 並び替え直後に開くべきフォルダIDを取得する。
     *
     * @param \Illuminate\Support\Collection $contents
     * @param int|null $focus_content_id
     * @return array
     */
    private function buildFocusOpenIds($contents, $focus_content_id)
    {
        if (empty($focus_content_id)) {
            return [];
        }

        $contents_by_id = $contents->keyBy('id');
        $current = $contents_by_id->get($focus_content_id);
        if (empty($current)) {
            return [];
        }

        $focus_open_ids = [];
        while (!empty($current) && !is_null($current->parent_id)) {
            $focus_open_ids[] = $current->parent_id;
            $current = $contents_by_id->get($current->parent_id);
        }

        return array_values(array_unique($focus_open_ids));
    }

    /**
     * フレーム表示設定の保存
     */
    public function saveView($request, $page_id, $frame_id, $photoalbum_id)
    {
        // 項目のエラーチェック
        $validator = Validator::make($request->all(), [
            'description_list_length' => ['nullable', 'integer', 'min:1'],
        ]);
        $validator->setAttributeNames([
            'description_list_length' => PhotoalbumFrameConfig::enum['description_list_length'],
        ]);

        // エラーがあった場合は入力画面に戻る。
        if ($validator->fails()) {
            return redirect()->back()->withErrors($validator)->withInput();
        }

        // フレーム設定保存
        $this->saveFrameConfigs($request, $frame_id, PhotoalbumFrameConfig::getMemberKeys());
        // 更新したので、frame_configsを設定しなおす
        $this->refreshFrameConfigs();

        return;
    }

    /**
     * 表示設定のカスタム順序を更新する
     */
    public function updateViewSequence($request, $page_id, $frame_id)
    {
        $content = PhotoalbumContent::find($request->photoalbum_content_id);
        if (empty($content)) {
            return $this->respondViewSequenceError($request, 'コンテンツが見つかりません。', 404);
        }

        $this->refreshFrameConfigs();
        $sort_folder = FrameConfig::getConfigValue($this->frame_configs, PhotoalbumFrameConfig::sort_folder);
        $sort_file = FrameConfig::getConfigValue($this->frame_configs, PhotoalbumFrameConfig::sort_file);

        $is_folder = $content->is_folder == PhotoalbumContent::is_folder_on;
        if ($is_folder && $sort_folder != PhotoalbumSort::manual_order) {
            return $this->respondViewSequenceError($request, 'フォルダの並び順がカスタム順ではありません。');
        }
        if (!$is_folder && $sort_file != PhotoalbumSort::manual_order) {
            return $this->respondViewSequenceError($request, 'ファイルの並び順がカスタム順ではありません。');
        }

        $siblings = PhotoalbumContent::where('parent_id', $content->parent_id)
            ->orderBy('is_folder', 'desc')
            ->orderBy('display_sequence')
            ->orderBy('id')
            ->get();

        $target_index = $siblings->search(function ($item) use ($content) {
            return $item->id == $content->id;
        });

        if ($target_index === false) {
            return $this->respondViewSequenceError($request, '並び替え対象が見つかりません。');
        }

        $pair = null;
        if ($request->display_sequence_operation === 'up') {
            for ($i = $target_index - 1; $i >= 0; $i--) {
                if ($siblings[$i]->is_folder == $content->is_folder) {
                    $pair = $siblings[$i];
                    break;
                }
            }
        } elseif ($request->display_sequence_operation === 'down') {
            for ($i = $target_index + 1; $i < $siblings->count(); $i++) {
                if ($siblings[$i]->is_folder == $content->is_folder) {
                    $pair = $siblings[$i];
                    break;
                }
            }
        } else {
            return $this->respondViewSequenceError($request, '並び替えの指定が正しくありません。');
        }

        if (!$pair) {
            return $this->respondViewSequenceError($request, '並び順の変更ができません。');
        }

        $current_sequence = $content->display_sequence;
        $content->display_sequence = $pair->display_sequence;
        $content->save();

        $pair->display_sequence = $current_sequence;
        $pair->save();

        $this->normalizeDisplaySequence($content->parent_id);

        $request->merge(['flash_message' => '並び順を更新しました。']);
        // ハイライト対象をセッションに保持し、次回描画で視覚的に示す
        Session::flash('photoalbum_sort_focus', $content->id);

        if ($request->expectsJson()) {
            return $this->respondViewSequenceJson($content);
        }

        if (!empty($request->redirect_path)) {
            $redirect_path = $request->redirect_path;
            if ($request->filled('anchor_target')) {
                $redirect_path = preg_replace('/#.*$/', '', $redirect_path);
                $redirect_path .= '#'.$request->anchor_target;
            }

            return new Collection(['redirect_path' => $redirect_path]);
        }
    }

    /**
     * 表示設定の並び替え失敗レスポンスを返す
     */
    private function respondViewSequenceError($request, string $message, int $status = 422)
    {
        if ($request->expectsJson()) {
            return response()->json(['message' => $message], $status);
        }

        return;
    }

    /**
     * 表示設定の並び替え成功レスポンスを返す（JSON）
     */
    private function respondViewSequenceJson(PhotoalbumContent $content)
    {
        $sort_folder = FrameConfig::getConfigValue($this->frame_configs, PhotoalbumFrameConfig::sort_folder);
        $sort_file = FrameConfig::getConfigValue($this->frame_configs, PhotoalbumFrameConfig::sort_file);

        $siblings = PhotoalbumContent::where('parent_id', $content->parent_id)
            ->with('upload')
            ->get(['id', 'is_folder', 'display_sequence', 'created_at', 'name', 'upload_id'])
            ->sort(function ($first, $second) use ($sort_folder, $sort_file) {
                return $this->comparePhotoalbumContents($first, $second, $sort_folder, $sort_file);
            })
            ->values();

        return response()->json([
            'message' => '並び順を更新しました。',
            'parent_id' => $content->parent_id,
            'moved_id' => $content->id,
            'siblings' => $siblings->map(function ($sibling) {
                return [
                    'id' => $sibling->id,
                    'is_folder' => $sibling->is_folder,
                ];
            })->values(),
        ]);
    }

    /**
     * 表示設定の非表示フォルダを更新する（JSON）
     */
    public function updateHiddenFolders($request, $page_id, $frame_id)
    {
        $photoalbum = $this->getPluginBucket($this->frame->bucket_id);
        if (empty($photoalbum->id)) {
            return response()->json(['message' => 'フォトアルバムが見つかりません。'], 404);
        }

        $hidden_folder_ids = $request->input('hidden_folder_ids', []);
        if (!is_array($hidden_folder_ids)) {
            $hidden_folder_ids = [$hidden_folder_ids];
        }

        $hidden_folder_ids = array_values(array_filter(array_map('intval', $hidden_folder_ids), static function ($id) {
            return $id > 0;
        }));

        if (!empty($hidden_folder_ids)) {
            $valid_folder_ids = PhotoalbumContent::where('photoalbum_id', $photoalbum->id)
                ->where('is_folder', PhotoalbumContent::is_folder_on)
                ->whereIn('id', $hidden_folder_ids)
                ->pluck('id')
                ->all();

            if (count($valid_folder_ids) !== count($hidden_folder_ids)) {
                return response()->json(['message' => '指定されたフォルダが存在しません。'], 422);
            }
        }

        if (empty($hidden_folder_ids)) {
            FrameConfig::where('frame_id', $frame_id)
                ->where('name', PhotoalbumFrameConfig::hidden_folder_ids)
                ->forceDelete();
        } else {
            FrameConfig::updateOrCreate(
                ['frame_id' => $frame_id, 'name' => PhotoalbumFrameConfig::hidden_folder_ids],
                ['value' => implode(FrameConfig::CHECKBOX_SEPARATOR, $hidden_folder_ids)]
            );
        }

        $this->refreshFrameConfigs();

        return response()->json([
            'message' => '表示設定を更新しました。',
            'hidden_folder_ids' => $hidden_folder_ids,
        ]);
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
            $request_value = $request->$value;
            if (is_array($request_value)) {
                $request_value = array_values(array_filter($request_value, static function ($item) {
                    return $item !== '' && $item !== null;
                }));
            }

            // 空の場合はレコード削除
            if ($request_value === null || $request_value === '' || (is_array($request_value) && empty($request_value))) {
                FrameConfig::where('frame_id', $frame_id)->where('name', $value)->forceDelete();
                continue;
            }

            FrameConfig::updateOrCreate(
                ['frame_id' => $frame_id, 'name' => $value],
                ['value' => is_array($request_value) ? implode(FrameConfig::CHECKBOX_SEPARATOR, $request_value) : $request_value]
            );
        }
    }
}
