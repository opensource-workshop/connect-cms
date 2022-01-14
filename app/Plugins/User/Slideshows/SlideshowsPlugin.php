<?php

namespace App\Plugins\User\Slideshows;

use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Collection;

use App\Models\Common\Buckets;
use App\Models\Common\Frame;
use App\Models\Common\Uploads;
use App\Models\User\Slideshows\Slideshows;
use App\Models\User\Slideshows\SlideshowsItems;

use App\Rules\CustomValiUrlMax;

use App\Enums\ShowType;

use App\Plugins\User\UserPluginBase;

/**
 * スライドショー・プラグイン
 *
 * @author 井上 雅人 <inoue@opensource-workshop.jp / masamasamasato0216@gmail.com>, 永原　篤 <nagahara@opensource-workshop.jp>
 * @copyright OpenSource-WorkShop Co.,Ltd. All Rights Reserved
 * @category スライドショー・プラグイン
 * @package Controller
 */
class SlideshowsPlugin extends UserPluginBase
{
    /* オブジェクト変数 */

    /**
     * POST チェックに使用する getPost() 関数を使うか
     */
    public $use_getpost = false;

    /* コアから呼び出す関数 */

    /**
     *  関数定義（コアから呼び出す）
     */
    public function getPublicFunctions()
    {
        // 標準関数以外で画面などから呼ばれる関数の定義
        $functions = array();
        $functions['get']  = [
            'index',
            'editItem',
        ];
        $functions['post'] = [
            'index',
            'addItem',
            'updateItems',
            'deleteItem',
            'updateItemSequence',
        ];
        return $functions;
    }

    /**
     * 追加の権限定義（コアから呼び出す）
     */
    public function declareRole()
    {
        // 権限チェックテーブル
        $role_check_table = [];
        $role_check_table["addItem"]            = ['buckets.addColumn'];
        $role_check_table["editItem"]           = ['buckets.editColumn'];
        $role_check_table["updateItems"]        = ['buckets.saveColumn'];
        $role_check_table["deleteItem"]         = ['buckets.deleteColumn'];
        $role_check_table["updateItemSequence"] = ['buckets.upColumnSequence', 'buckets.downColumnSequence'];
        return $role_check_table;
    }

    /**
     *  編集画面の最初のタブ ※スーパークラスをオーバーライド
     */
    public function getFirstFrameEditAction()
    {
        // スライドショー未作成の場合、スライドショーの新規作成に遷移
        $slideshow = $this->getSlideshows($this->frame->id);
        return $slideshow ? 'editBuckets' : 'createBuckets';
    }

    /* private関数 */

    /**
     *  フレームIDに紐づくプラグインデータ取得
     */
    private function getSlideshows($frame_id)
    {
        $slideshow = Slideshows::query()
            ->select('slideshows.*')
            ->join('frames', 'frames.bucket_id', '=', 'slideshows.bucket_id')
            ->where('frames.id', '=', $frame_id)
            ->first();

        return $slideshow;
    }

    /**
     *  フレームに紐づくスライドショーID とフレームデータの取得
     */
    private function getSlideshowFrame($frame_id)
    {
        // Frame データ
        $frame = Frame::query()
            ->select(
                'frames.*',
                'slideshows.id as slideshows_id'
            )
            ->leftJoin('slideshows', 'slideshows.bucket_id', '=', 'frames.bucket_id')
            ->where('frames.id', $frame_id)
            ->first();
        return $frame;
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

        // Slideshows、Frame データ
        $slideshow = $this->getSlideshows($frame_id);

        $setting_error_messages = null;
        $slideshows_items = new Collection();
        if ($slideshow) {
            $slideshows_items = SlideshowsItems::query()
                ->where('slideshows_id', $slideshow->id)
                ->where('display_flag', ShowType::show)
                ->orderBy('display_sequence')
                ->get();
        } else {
            // フレームに紐づくスライドショー親データがない場合
            $setting_error_messages[] = 'フレームの設定画面から、使用するスライドショーを選択するか、作成してください。';
        }

        if ($slideshows_items->count() == 0) {
            // フレームに紐づくスライドショー子データがない場合
            $setting_error_messages[] = 'フレームの設定画面から、使用するスライドショーの項目を定義してください。';
        }

        if (empty($setting_error_messages)) {
            // 表示テンプレートを呼び出す。
            return $this->view('slideshows', [
                'request' => $request,
                'frame_id' => $frame_id,
                'slideshow' => $slideshow,
                'slideshows_items' => $slideshows_items,
                'errors' => $errors,
            ]);
        } else {
            // エラーあり
            return $this->view('slideshows_error_messages', [
                'error_messages' => $setting_error_messages,
            ]);
        }
    }

    /**
     * スライドショー新規作成画面
     */
    public function createBuckets($request, $page_id, $frame_id, $slideshows_id = null, $is_create = false, $message = null, $errors = null)
    {
        // 新規作成フラグを付けてスライドショー設定変更画面を呼ぶ
        $is_create = true;
        return $this->editBuckets($request, $page_id, $frame_id, $slideshows_id, $is_create, $message, $errors);
    }

    /**
     * スライドショー設定変更画面の表示
     */
    public function editBuckets($request, $page_id, $frame_id, $slideshows_id = null, $is_create = false, $message = null, $errors = null)
    {
        // 権限チェック
        if ($this->can('role_article_admin')) {
            return $this->view_error(403);
        }

        // セッション初期化などのLaravel 処理。
        $request->flash();

        // スライドショー＆フレームデータ
        $slideshow_frame = $this->getSlideshowFrame($frame_id);

        // スライドショーデータ
        $slideshow = new Slideshows();

        if (!empty($slideshows_id)) {
            // slideshows_id が渡ってくればslideshows_id が対象
            $slideshow = Slideshows::where('id', $slideshows_id)->first();
        } elseif (!empty($slideshow_frame->bucket_id) && $is_create == false) {
            // Frame のbucket_id があれば、bucket_id からスライドショーデータ取得、なければ、新規作成か選択へ誘導
            $slideshow = Slideshows::where('bucket_id', $slideshow_frame->bucket_id)->first();
        }

        // 表示テンプレートを呼び出す。
        return $this->view(
            'slideshows_edit_slideshow',
            [
                'slideshow_frame' => $slideshow_frame,
                'slideshow' => $slideshow,
                'is_create' => $is_create,
                'message' => $message,
                'errors' => $errors,
            ]
        )->withInput($request->all);
    }

    /**
     *  スライドショー登録処理
     */
    public function saveBuckets($request, $page_id, $frame_id, $slideshows_id = null)
    {
        // エラーチェック
        $validator_values['slideshows_name'] = ['required'];
        $validator_attributes['slideshows_name'] = 'スライドショー名';
        $validator_values['image_interval'] = ['required', 'numeric', 'integer', 'min:1', 'max:60000'];
        $validator_attributes['image_interval'] = '画像の静止時間';
        $validator_values['height'] = ['nullable', 'numeric', 'min:1', 'max:65535'];
        $validator_attributes['height'] = '高さ';

        // 項目のエラーチェック
        $validator = Validator::make($request->all(), $validator_values);
        $validator->setAttributeNames($validator_attributes);

        // エラーがあった場合は入力画面に戻る。
        $message = null;
        if ($validator->fails()) {
            $is_create = $slideshows_id ? false : true;
            return $this->editBuckets($request, $page_id, $frame_id, $slideshows_id, $is_create, $message, $validator->errors());
        }

        // 更新後のメッセージ
        $message = null;

        // 画面から渡ってくるslideshows_id が空ならバケツとスライドショーを新規登録
        if (empty($slideshows_id)) {
            /**
             * 新規登録用の処理
             */
            $bucket = new Buckets();
            $bucket->bucket_name = '無題';
            $bucket->plugin_name = 'slideshows';
            $bucket->save();

            // スライドショーデータ新規オブジェクト
            $slideshows = new Slideshows();
            $slideshows->bucket_id = $bucket->id;

            // Frame のBuckets を見て、Buckets が設定されていなければ、作成したものに紐づける。
            // Frame にBuckets が設定されていない ＞ 新規のフレーム＆スライドショー作成
            // Frame にBuckets が設定されている ＞ 既存のフレーム＆スライドショー更新
            // （表示スライドショー選択から遷移してきて、内容だけ更新して、フレームに紐づけないケースもあるため）
            $frame = Frame::where('id', $frame_id)->first();
            if (empty($frame->bucket_id)) {
                // FrameのバケツIDの更新
                $frame = Frame::where('id', $frame_id)->update(['bucket_id' => $bucket->id]);
            }

            $message = 'スライドショー設定を追加しました。<br />' .
                        '　 [ <a href="' . url('/') . '/plugin/slideshows/listBuckets/' . $page_id . '/' . $frame_id . '/#frame-' . $frame_id . '">スライドショー選択</a> ]から作成したスライドショーを選択後、［ 項目設定 ］で使用する項目を設定してください。';
        } else {
            /**
             * 更新用の処理
             */

            // スライドショーデータ取得
            $slideshows = Slideshows::where('id', $slideshows_id)->first();

            $message = 'スライドショー設定を変更しました。';
        }

        /**
         * 登録処理 ※新規、更新共通
         */
        $slideshows->slideshows_name = $request->slideshows_name;
        $slideshows->control_display_flag = $request->control_display_flag;
        $slideshows->indicators_display_flag = $request->indicators_display_flag;
        $slideshows->fade_use_flag = $request->fade_use_flag;
        $slideshows->image_interval = $request->image_interval;
        $slideshows->height = $request->height;
        $slideshows->save();

        // 新規作成フラグを更新モードにセットして設定変更画面へ遷移
        $is_create = false;

        return $this->editBuckets($request, $page_id, $frame_id, $slideshows->id, $is_create, $message);
    }

    /**
     * スライドショー削除処理
     */
    public function destroyBuckets($request, $page_id, $frame_id, $slideshows_id)
    {
        if ($slideshows_id) {

            // バケツに紐づく明細データを取得
            $slideshows_items = SlideshowsItems::where('slideshows_id', $slideshows_id)->get();

            /**
             * 紐づく画像ファイルの削除
             */
            $del_file_ids = $slideshows_items->pluck('uploads_id')->all();
            $delete_uploads = Uploads::whereIn('id', $del_file_ids)->get();
            foreach ($delete_uploads as $delete_upload) {
                // ファイルの削除
                $directory = $this->getDirectory($delete_upload->id);
                Storage::delete($directory . '/' . $delete_upload->id . '.' .$delete_upload->extension);

                // uploadの削除
                $delete_upload->delete();
            }

            /**
             * 明細データの削除
             */
            foreach ($slideshows_items as $slideshows_item) {
                SlideshowsItems::where('slideshows_items_id', $slideshows_item->id)->delete();
            }

            $slideshows = Slideshows::find($slideshows_id);

            // backetsの削除
            Buckets::where('id', $slideshows->bucket_id)->delete();

            // バケツIDの取得のためにFrame を取得(Frame を更新する前に取得しておく)
            $frame = Frame::where('id', $frame_id)->first();
            // フレームのbucket_idと削除するスライドショーのbucket_idが同じなら、FrameのバケツIDの更新する
            if ($frame->bucket_id == $slideshows->bucket_id) {
                // FrameのバケツIDの更新
                Frame::where('bucket_id', $frame->bucket_id)->update(['bucket_id' => null]);
            }

            // スライドショー設定を削除する。
            Slideshows::destroy($slideshows_id);
        }

        // リダイレクト設定はフォーム側で設定している為、return処理は省略
    }

    /**
     * データ選択表示関数
     */
    public function listBuckets($request, $page_id, $frame_id, $id = null)
    {
        // ソート設定に初期設定値をセット
        $sort_inits = [
            "slideshows_updated_at" => ["desc", "asc"],
            "page_name" => ["desc", "asc"],
            "frame_title" => ["asc", "desc"],
            "slideshows_name" => ["desc", "asc"],
        ];

        // 要求するソート指示。初期値として更新日の降順を設定
        $request_order_by = ["slideshows_updated_at", "desc"];

        // 画面からのソート指定があれば使用(ソート指定があった項目は、ソート設定の内容を入れ替える)
        if (!empty($request->sort)) {
            $request_order_by = explode('|', $request->sort);
            if ($request_order_by[1] == "asc") {
                $sort_inits[$request_order_by[0]]=["asc", "desc"];
            } else {
                $sort_inits[$request_order_by[0]]=["desc", "asc"];
            }
        }

        // 画面でのリンク用ソート指示(ソート指定されている場合はソート指定を逆転したもの)
        $order_link = array();
        foreach ($sort_inits as $order_by_key => $order_by) {
            if ($request_order_by[0]==$order_by_key && $request_order_by[1]==$order_by[0]) {
                $order_link[$order_by_key] = array_reverse($order_by);
            } else {
                $order_link[$order_by_key] = $order_by;
            }
        }

        // データリストの場合の追加処理
        // * status は 0 のもののみ表示（データリスト表示はそれで良いと思う）
        // * 現在のものを最初に表示する。orderByRaw('buckets.id = ' . $this->buckets->id . ' desc') ※ desc 指定が必要だった。
        $buckets_query = Buckets::
            select(
                'buckets.*',
                'slideshows.id as slideshows_id',
                'slideshows.slideshows_name',
                'slideshows.updated_at as slideshows_updated_at',
                'frames.id as frames_id',
                'frames.frame_title',
                'pages.page_name'
            )
            ->join('slideshows', function ($join) {
                $join->on('slideshows.bucket_id', '=', 'buckets.id');
                $join->whereNull('slideshows.deleted_at');
            })
            ->leftJoin('frames', 'buckets.id', '=', 'frames.bucket_id')
            ->leftJoin('pages', 'pages.id', '=', 'frames.page_id')
            ->where('buckets.plugin_name', 'slideshows');

        // buckets を作っていない状態で、設定の表示コンテンツ選択を開くこともあるので、バケツがあるかの判定
        if (!empty($this->buckets)) {
            // buckets がある場合は、該当buckets を一覧の最初に持ってくる。
            $buckets_query->orderByRaw('buckets.id = ' . $this->buckets->id . ' desc');
        }

        $buckets_list = $buckets_query
            ->orderBy($request_order_by[0], $request_order_by[1])
            ->paginate(10, ["*"], "frame_{$frame_id}_page");

        return $this->view(
            'slideshows_list_buckets',
            [
                'buckets_list'      => $buckets_list,
                'order_link'        => $order_link,
                'request_order_str' => implode('|', $request_order_by)
            ]
        );
    }

    /**
     * データ紐づけ変更関数
     */
    public function changeBuckets($request, $page_id = null, $frame_id = null, $id = null)
    {
        // FrameのバケツIDの更新
        Frame::where('id', $frame_id)->update(['bucket_id' => $request->select_bucket]);

        return;
    }

    /**
     * 項目の追加
     */
    public function addItem($request, $page_id, $frame_id, $id = null)
    {
        // エラーチェック
        $request->validate([
            'image_file'  => 'required|image',
            'link_url'    => [new CustomValiUrlMax()],
            'caption'     => 'max:9',
            'link_target' => 'max:9',
        ]);

        if ($request->hasFile('image_file')) {

            // UploadedFileオブジェクトを取得
            $image_file = $request->file('image_file');

            // uploads テーブルに情報追加、ファイルのid を取得する
            $upload = Uploads::create([
                'client_original_name' => $image_file->getClientOriginalName(),
                'mimetype'             => $image_file->getClientMimeType(),
                'extension'            => $image_file->getClientOriginalExtension(),
                'size'                 => $image_file->getClientSize(),
                'plugin_name'          => 'slideshows',
                'page_id'              => $page_id,
                'temporary_flag'       => 0,
                'created_id'           => Auth::user()->id,
            ]);

            // 保存先のディレクトリを取得
            $directory = $this->getDirectory($upload->id);

            // 保存先のディレクトリがなかったら作成
            if (!Storage::exists(storage_path('app/') . $directory . '/')) {
                Storage::makeDirectory(storage_path('app/') . $directory . '/', 0775, true);
            }

            // ファイル名は「(uploadsのid).(拡張子)」形式で保存する
            $upload_image_path = $image_file->storeAs($directory, $upload->id . '.' . $image_file->getClientOriginalExtension());
        }

        // 新規登録時の表示順を設定
        $max_display_sequence = SlideshowsItems::query()->where('slideshows_id', $request->slideshows_id)->max('display_sequence');
        $max_display_sequence = $max_display_sequence ? $max_display_sequence + 1 : 1;

        // 項目の登録処理
        $slideshows_item = new SlideshowsItems();
        $slideshows_item->slideshows_id = $request->slideshows_id;
        $slideshows_item->image_path = $upload_image_path;
        $slideshows_item->uploads_id = $upload->id;
        $slideshows_item->link_url = $request->link_url;
        $slideshows_item->link_target = $request->link_target;
        $slideshows_item->caption = $request->caption;
        $slideshows_item->display_flag = ShowType::show;
        $slideshows_item->display_sequence = $max_display_sequence;
        $slideshows_item->save();

        // フラッシュメッセージ設定
        $request->merge([
            'flash_message' => 'スライドショー項目を登録しました。'
        ]);

        // リダイレクト設定はフォーム側で設定している為、return処理は省略
    }

    /**
     * uploadsテーブルへInsert
     */
    private function insertUploads($image_file)
    {
        return Uploads::create([
            'client_original_name' => $image_file->getClientOriginalName(),
            'mimetype'             => $image_file->getClientMimeType(),
            'extension'            => $image_file->getClientOriginalExtension(),
            'size'                 => $image_file->getClientSize(),
            'plugin_name'          => 'slideshows',
            'page_id'              => $this->page->id,
            'temporary_flag'       => 0,
            'created_id'           => Auth::user()->id,
        ]);
    }

    /**
     * 項目の更新
     */
    public function updateItems($request, $page_id, $frame_id)
    {
        // エラーチェック
        $validator = Validator::make($request->all(), [
            'link_urls.*'    => [new CustomValiUrlMax()],
            'captions.*'     => ['max:255'],
            'link_targets.*' => ['max:255'],
        ]);
        $validator->setAttributeNames([
            'link_urls.*'    => "リンクURL",
            'captions.*'     => "キャプション",
            'link_targets.*' => "リンクターゲット	",
        ]);

        $errors = array();
        if ($validator->fails()) {
            $request->merge(['validator' => $validator]);
            return;
        }

        foreach (array_keys($request->link_urls) as $item_id) {

            $upload_image_path = null;
            $upload = null;

            // 画像のアップロードがあれば書き換える
            if ($request->hasFile('image_files') && isset($request->file('image_files')[$item_id])) {
                // UploadedFileオブジェクトを取得
                $image_file = $request->file('image_files')[$item_id];

                // uploadsテーブルへInsert
                $upload = $this->insertUploads($image_file);

                // 保存先のディレクトリを取得
                $directory = $this->getDirectory($upload->id);

                // 保存先のディレクトリがなかったら作成
                if (!Storage::exists(storage_path('app/') . $directory . '/')) {
                    Storage::makeDirectory(storage_path('app/') . $directory . '/', 0775, true);
                }

                // ファイル名は「(uploadsのid).(拡張子)」形式で保存する
                $upload_image_path = $image_file->storeAs($directory, $upload->id . '.' . $image_file->getClientOriginalExtension());
            }

            // 項目の更新処理
            $slideshows_item = SlideshowsItems::find($item_id);
            if ($upload_image_path) {
                $slideshows_item->image_path = $upload_image_path;
            }
            if ($upload) {
                $slideshows_item->uploads_id = $upload->id;
            }
            $slideshows_item->display_flag = isset($request->display_flags[$item_id]) ? ShowType::show : ShowType::not_show;
            $slideshows_item->link_url = $request->link_urls[$item_id];
            $slideshows_item->link_target = $request->link_targets[$item_id];
            $slideshows_item->caption = $request->captions[$item_id];
            $slideshows_item->save();
        }

        // フラッシュメッセージ設定
        $request->merge([
            'flash_message' => 'スライドショー項目を更新しました。'
        ]);

        // リダイレクト設定はフォーム側で設定している為、return処理は省略
    }

    /**
     * 項目編集画面の表示
     */
    public function editItem($request, $page_id, $frame_id, $id = null, $message = null, $errors = null)
    {
        // 権限チェック
        if ($this->can('role_article_admin')) {
            return $this->view_error(403);
        }

        // フレームに紐づくスライドショーを取得
        $slideshow = $this->getSlideshows($frame_id);

        // スライドショーのID。まだスライドショーがない場合は0
        $slideshows_id = !empty($slideshow) ? $slideshow->id : 0;

        // 項目データ取得
        $items = SlideshowsItems::query()
            ->select(
                'slideshows_items.*',
                'uploads.client_original_name'
            )
            ->join('uploads', 'uploads.id', '=', 'slideshows_items.uploads_id')
            ->where('slideshows_items.slideshows_id', $slideshows_id)
            ->orderby('slideshows_items.display_sequence')
            ->get();

        return $this->view(
            'slideshows_edit',
            [
                'slideshows_id'   => $slideshows_id,
                'items'    => $items,
                'message'    => $message,
            ]
        );
    }

    /**
     * 項目の削除
     */
    public function deleteItem($request, $page_id, $frame_id)
    {
        $slideshows_item = SlideshowsItems::find($request->item_id);

        /**
         * ※アップロード画像をPATH使い回しで他の箇所でも使用しているケースが想定される為、アップロードファイルの削除は一旦コメントアウト
         */
        // // 削除するファイルデータ
        // $delete_upload = Uploads::find($slideshows_item->uploads_id);

        // if ($delete_upload) {
        //     // ファイルの削除
        //     $directory = $this->getDirectory($delete_upload->id);
        //     Storage::delete($directory . '/' . $delete_upload->id . '.' .$delete_upload->extension);

        //     // uploadsデータの削除
        //     $delete_upload->delete();
        // }

        // 項目の削除
        $slideshows_item->delete();

        // フラッシュメッセージ設定
        $request->merge([
            // 'flash_message' => 'スライドショー項目を削除しました。（画像ファイル名：' . $delete_upload->client_original_name . '）'
            'flash_message' => 'スライドショー項目を削除しました。'
        ]);

        // リダイレクト設定はフォーム側で設定している為、return処理は省略
    }

    /**
     * 項目の表示順の更新
     */
    public function updateItemSequence($request, $page_id, $frame_id)
    {
        // ボタンが押された行の項目データ
        $target_item = SlideshowsItems::find($request->item_id);

        // ボタンが押された前（後）の項目データ
        $query = SlideshowsItems::query()
            ->where('slideshows_id', $request->slideshows_id);
        $pair_item = $request->display_sequence_operation == 'up' ?
            $query->where('display_sequence', '<', $request->display_sequence)->orderby('display_sequence', 'desc')->limit(1)->first() :
            $query->where('display_sequence', '>', $request->display_sequence)->orderby('display_sequence', 'asc')->limit(1)->first();

        // それぞれの表示順を退避
        $target_item_display_sequence = $target_item->display_sequence;
        $pair_item_display_sequence = $pair_item->display_sequence;

        // 入れ替えて更新
        $target_item->display_sequence = $pair_item_display_sequence;
        $target_item->save();
        $pair_item->display_sequence = $target_item_display_sequence;
        $pair_item->save();

        // フラッシュメッセージ設定
        $request->merge([
            'flash_message' => '項目の表示順を更新しました。'
        ]);

        // リダイレクト設定はフォーム側で設定している為、return処理は省略
    }
}
