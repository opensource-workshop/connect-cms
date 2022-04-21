<?php

namespace App\Plugins\User\Searchs;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;

use App\Models\Common\Buckets;
use App\Models\Common\Frame;
use App\Models\Common\Page;
use App\Models\User\Searchs\Searchs;

use App\Plugins\User\UserPluginBase;
use App\Traits\ConnectCommonTrait;

use App\Enums\SearchsTargetPlugin;

/**
 * サイト内検索プラグイン
 *
 * サイト内の情報を検索するプラグイン。
 *
 * @author 永原　篤 <nagahara@opensource-workshop.jp>
 * @copyright OpenSource-WorkShop Co.,Ltd. All Rights Reserved
 * @category 検索プラグイン
 * @package Controller
 * @plugin_title サイト内検索
 * @plugin_desc サイト内のコンテンツを検索できるプラグインです。
 */
class SearchsPlugin extends UserPluginBase
{
    use ConnectCommonTrait;

    /* オブジェクト変数 */

    /**
     * POST チェックに使用する getPost() 関数を使うか
     */
    public $use_getpost = false;

    /* コアから呼び出す関数 */

    /**
     * 関数定義（コアから呼び出す）
     */
    public function getPublicFunctions()
    {
        // 標準関数以外で画面などから呼ばれる関数の定義
        $functions = array();
        $functions['get']  = ['search'];
        $functions['post'] = ['search'];
        return $functions;
    }

    /**
     * 追加の権限定義（コアから呼び出す）
     */
    public function declareRole()
    {
        // 標準権限以外で設定画面などから呼ばれる権限の定義
        // 標準権限は右記で定義 config/cc_role.php
        //
        // 権限チェックテーブル (追加チェックなし)
        $role_check_table = [];
        return $role_check_table;
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
     * 紐づく検索とフレームデータの取得
     */
    private function getSearchsFrame($frame_id)
    {
        // Frame データ
        $frame = Frame::
            select(
                'searchs.*',
                'frames.id as frames_id',
                'frames.bucket_id',
                'frames.disable_searchs'
            )
            ->leftJoin('searchs', 'frames.bucket_id', '=', 'searchs.bucket_id')
            ->where('frames.id', $frame_id)
            ->first();

        return $frame;
    }

    /**
     * 新着対象のプラグインがあるフレームデータの取得
     */
    private function getTargetPluginsFrames()
    {
        // Frame データ
        $frames = Frame::select('frames.*', 'pages._lft', 'pages.page_name', 'buckets.bucket_name')
            // ->whereIn('frames.plugin_name', array('blogs'))
            ->whereIn('frames.plugin_name', SearchsTargetPlugin::getKeysPluginsCanSpecifiedFrames())
            ->leftJoin('buckets', 'frames.bucket_id', '=', 'buckets.id')
            ->leftJoin('pages', 'frames.page_id', '=', 'pages.id')
            ->where('disable_searchs', 0)
            ->orderBy('pages._lft', 'asc')
            ->get();

        return $frames;
    }

    /**
     * 検索結果の取得
     */
    private function searchContents($request, $searchs_frame, $method = null)
    {
        // 検索がまだできていない場合
        if (!$searchs_frame || empty($searchs_frame->id)) {
            return array(null, null, null);
        }

        // 検索キーワードを取得
        $search_keyword = $request->search_keyword;

        // 現在の言語を取得
        $page_ids = Page::getPageIds($this->page);
//print_r($page_ids);

        // ターゲットプラグインをループ
        $target_plugins = explode(',', $searchs_frame->target_plugins);

        // union するSQL を各プラグインから取得。その際に使用するURL パターンとベースのURL も取得
        $union_sqls = array();
        foreach ($target_plugins as $target_plugin) {
            // クラスファイルの存在チェック。
            $file_path = base_path() . "/app/Plugins/User/" . ucfirst($target_plugin) . "/" . ucfirst($target_plugin) . "Plugin.php";

            // ファイルの存在確認
            if (!file_exists($file_path)) {
                return $this->viewError("500_inframe", null, 'ファイル Not found.<br />' . $file_path);
            }

            // 各プラグインのgetSearchArgs() 関数を呼び出し。
            $class_name = "App\Plugins\User\\" . ucfirst($target_plugin) . "\\" . ucfirst($target_plugin) . "Plugin";
            list($union_sqls[$target_plugin], $link_pattern[$target_plugin], $link_base[$target_plugin]) = $class_name::getSearchArgs($search_keyword, $page_ids);
        }

        // ベースの新着DUAL（ダミーテーブル）
        $searchs_sql = DB::table('searchs_dual')
                 ->select(
                     DB::raw("null as post_id"),
                     DB::raw("null as frame_id"),
                     DB::raw("null as page_id"),
                     DB::raw("null as permanent_link"),
                     DB::raw("null as post_title"),
                     DB::raw("null as important"),
                     DB::raw("null as posted_at"),
                     DB::raw("null as posted_name"),
                     DB::raw("null as classname"),
                     DB::raw("null as categories_id"),
                     DB::raw("null as category"),
                     DB::raw("null as plugin_name")
                 )
                 ->leftJoin('categories', 'categories.id', '=', 'searchs_dual.categories_id');

        // 各プラグインのSQL をUNION
        foreach ($union_sqls as $union_sql) {
            // フレームの選択が行われる場合
            if ($searchs_frame->frame_select == 1) {
                $union_sql->whereIn('frames.id', explode(',', $searchs_frame->target_frame_ids));
            }
            $searchs_sql->unionAll($union_sql);
        }

        // UNION 後をソート
        $searchs_sql->orderBy('posted_at', 'desc');

        // ページングしてデータ取得
        $searchs_results = $searchs_sql->paginate($searchs_frame->count, ["*"], "frame_{$searchs_frame->id}_page");

        return array($searchs_results, $link_pattern, $link_base);
    }


    /* 画面アクション関数 */

    /**
     * データ初期表示関数
     * コアがページ表示の際に呼び出す関数
     *
     * @method_title サイト内検索
     * @method_desc 指定したキーワードがあるコンテンツの一覧が表示されます。
     * @method_detail
     */
    public function index($request, $page_id, $frame_id, $errors = null)
    {
        // フレームから、検索の設定取得
        $searchs_frame = $this->getSearchsFrame($frame_id);

        // change: アクセシビリティ対応, getで検索空で受け取った場合、検索必須であるメッセージを表示する事（G83：入力が完了していない必須項目を特定するために、テキストの説明文を提供していることを確認して下さい）
        // 入力エラーが無く、検索キーワードが入ってきたら、検索メソッドへ
        // if (!empty($searchs_frame) && is_null($errors) && $searchs_frame->recieve_keyword == 1 && $request->search_keyword) {
        if (!empty($searchs_frame) && is_null($errors) && $searchs_frame->recieve_keyword == 1) {
            $get_query = strstr($request->fullUrl(), '?');
            // 'search_keyword'がクエリ文字列に含まれている場合
            if (strpos($get_query, 'search_keyword') !== false) {
                return $this->search($request, $page_id, $frame_id);
            }
        }

        // 表示テンプレートを呼び出す。
        return $this->view(
            'searchs', [
            'searchs_frame'   => $searchs_frame,
            'errors'          => $errors,
            ]
        );
    }

    /**
     * 検索アクション
     */
    public function search($request, $page_id, $frame_id)
    {
        // セッション初期化などのLaravel 処理。
        $request->flash();

        // 項目のエラーチェック
        $validator = Validator::make($request->all(), [
            'search_keyword'     => ['required'],
        ]);
        $validator->setAttributeNames([
            'search_keyword'     => '検索キーワード',
        ]);

        // エラーがあった場合は入力画面に戻る。
        $message = null;
        if ($validator->fails()) {
            return $this->index($request, $page_id, $frame_id, $validator->errors());
        }

        // フレームから、検索の設定取得
        $searchs_frame = $this->getSearchsFrame($frame_id);

        // 新着の一覧取得
        list($searchs_results, $link_pattern, $link_base) = $this->searchContents($request, $searchs_frame);

        // 表示テンプレートを呼び出す。
        return $this->view(
            'searchs_result', [
            'searchs_frame'   => $searchs_frame,
            'searchs_results' => $searchs_results,
            'link_pattern'    => $link_pattern,
            'link_base'       => $link_base,
            ]
        )->withInput($request->all);
    }

    /**
     * 設定データ選択表示関数
     *
     * @method_title 選択
     * @method_desc このフレームに表示するサイト内検索を選択します。
     * @method_detail
     */
    public function listBuckets($request, $page_id, $frame_id, $id = null)
    {
        // Frame データ
        $searchs_frame = $this->getSearchsFrame($frame_id);

        // データ取得（1ページの表示件数指定）
        $searchs = Searchs::orderBy('created_at', 'desc')
                          ->paginate(10, ["*"], "frame_{$frame_id}_page");

        // 表示テンプレートを呼び出す。
        return $this->view(
            'searchs_list_buckets', [
            'searchs_frame' => $searchs_frame,
            'searchs'       => $searchs,
            ]
        );
    }

    /**
     * 設定データの新規作成画面
     *
     * @method_title 作成
     * @method_desc サイト内検索を新しく作成します。
     * @method_detail サイト内検索の名称や検索条件などを入力してサイト内検索を作成できます。
     */
    public function createBuckets($request, $page_id, $frame_id, $id = null, $create_flag = false, $message = null, $errors = null)
    {
        // 新規作成フラグを付けて設定変更画面を呼ぶ
        $create_flag = true;
        return $this->editBuckets($request, $page_id, $frame_id, $id, $create_flag, $message, $errors);
    }

    /**
     * 設定データの変更画面の表示
     */
    public function editBuckets($request, $page_id, $frame_id, $id = null, $create_flag = false, $message = null, $errors = null)
    {
        // セッション初期化などのLaravel 処理。
        $request->flash();

        // 設定データ＆フレームデータ
        $searchs_frame = $this->getSearchsFrame($frame_id);

        // 設定データ
        $searchs = new Searchs();

        if (!empty($id)) {
            // id が渡ってくればid が対象
            $searchs = Searchs::where('id', $id)->first();
        } elseif (!empty($searchs_frame->bucket_id) && $create_flag == false) {
            // Frame のbucket_id があれば、bucket_id から設定データ取得、なければ、新規作成か選択へ誘導
            $searchs = Searchs::where('bucket_id', $searchs_frame->bucket_id)->first();
        }

        // 選択できるフレームの一覧
        $target_plugins_frames = $this->getTargetPluginsFrames();

        // 表示テンプレートを呼び出す。
        return $this->view(
            'searchs_edit_search', [
            'searchs_frame'         => $searchs_frame,
            'searchs'               => $searchs,
            'target_plugins_frames' => $target_plugins_frames,
            'create_flag'           => $create_flag,
            'message'               => $message,
            'errors'                => $errors,
            ]
        )->withInput($request->all);
    }

    /**
     * 設定の登録処理
     */
    public function saveBuckets($request, $page_id, $frame_id, $id = null)
    {
        // フレームから、新着の設定取得
        $searchs_frame = $this->getSearchsFrame($frame_id);

        // 項目のエラーチェック
        $validator = Validator::make($request->all(), [
            'search_name'     => ['required'],
            'count'           => ['required', 'numeric'],
            'target_plugin'   => ['required'],
        ]);
        $validator->setAttributeNames([
            'search_name'     => '検索設定名',
            'count'           => '1ページの表示件数',
            'target_plugin'   => '対象プラグイン',
        ]);

        // エラーがあった場合は入力画面に戻る。
        $message = null;
        if ($validator->fails()) {
            if (empty($request->searchs_id)) {
                $create_flag = true;
                return $this->createBuckets($request, $page_id, $frame_id, $request->searchs_id, $create_flag, $message, $validator->errors());
            } else {
                $create_flag = false;
                return $this->editBuckets($request, $page_id, $frame_id, $request->searchs_id, $create_flag, $message, $validator->errors());
            }
        }

        // 更新後のメッセージ
        $message = null;

        if (empty($request->searchs_id)) {
            // 画面から渡ってくるsearchs_id が空ならバケツと設定を新規登録
            // バケツの登録
            $bucket = Buckets::create([
                'bucket_name' => $request->search_name,
                'plugin_name' => 'searchs'
            ]);

            // 設定データ新規オブジェクト
            $searchs = new Searchs();
            $searchs->bucket_id = $bucket->id;

            // Frame のBuckets を見て、Buckets が設定されていなければ、作成したものに紐づける。
            // Frame にBuckets が設定されていない ＞ 新規のフレーム＆新着情報設定作成
            // Frame にBuckets が設定されている ＞ 既存のフレーム＆新着情報設定更新
            // （新着情報設定選択から遷移してきて、内容だけ更新して、フレームに紐づけないケースもあるため）
            if (empty($searchs_frame->bucket_id)) {
                // FrameのバケツIDの更新
                $frame = Frame::where('id', $frame_id)->update(['bucket_id' => $bucket->id]);
            }

            $message = '設定を追加しました。';
        } else {
            // whatsnews_id があれば、新着情報設定を更新
            // 新着情報設定の取得
            $searchs = Searchs::where('id', $request->searchs_id)->first();

            Buckets::where('id', $searchs->bucket_id)
                ->update(['bucket_name' => $request->search_name, 'plugin_name' => 'searchs']);

            $message = '設定を変更しました。';
        }

        // 設定データ
        $searchs->search_name       = $request->search_name;
        $searchs->count             = $request->count;
        $searchs->view_posted_name  = intval($request->view_posted_name);
        $searchs->view_posted_at    = intval($request->view_posted_at);
        $searchs->target_plugins    = implode(',', $request->target_plugin);
        $searchs->frame_select      = intval($request->frame_select);
        $searchs->target_frame_ids  = empty($request->target_frame_ids) ? "": implode(',', $request->target_frame_ids);
        $searchs->recieve_keyword   = intval($request->recieve_keyword);

        // データ保存
        $searchs->save();

        // 新規作成フラグを付けて新着情報設定変更画面を呼ぶ
        $create_flag = false;
        return $this->editBuckets($request, $page_id, $frame_id, $request->searchs_id, $create_flag, $message);
    }

    /**
     * 削除処理
     */
    public function destroyBuckets($request, $page_id, $frame_id, $id)
    {
        // id がある場合、データを削除
        if ($id) {
            // フレームから、新着の設定取得
            $searchs_frame = $this->getSearchsFrame($frame_id);

            // 新着設定を削除する。
            Searchs::where('id', $id)->delete();

            // backetsの削除
            Buckets::where('id', $searchs_frame->bucket_id)->delete();

            // FrameのバケツIDの更新
            Frame::where('id', $frame_id)->update(['bucket_id' => null]);
        }
        // 削除処理はredirect 付のルートで呼ばれて、処理後はページの再表示が行われるため、ここでは何もしない。
    }

    /**
     * データ紐づけ変更関数
     */
    public function changeBuckets($request, $page_id = null, $frame_id = null, $id = null)
    {
        // FrameのバケツIDの更新
        Frame::where('id', $frame_id)
               ->update(['bucket_id' => $request->select_bucket]);

        // 新着情報設定選択画面を呼ぶ
        return $this->listBuckets($request, $page_id, $frame_id, $id);
    }
}
