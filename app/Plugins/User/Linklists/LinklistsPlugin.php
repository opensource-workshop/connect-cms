<?php

namespace App\Plugins\User\Linklists;

use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;

use DB;

use App\Models\Core\Configs;
use App\Models\Common\Buckets;
use App\Models\Common\Frame;
use App\Models\Common\Page;
use App\Models\User\Linklists\Linklist;
use App\Models\User\Linklists\LinklistFrame;
use App\Models\User\Linklists\LinklistPost;

use App\Plugins\User\UserPluginBase;

/**
 * リンクリスト・プラグイン
 *
 * @author 永原　篤 <nagahara@opensource-workshop.jp>
 * @copyright OpenSource-WorkShop Co.,Ltd. All Rights Reserved
 * @category リンクリスト・プラグイン
 * @package Contoroller
 */
class LinklistsPlugin extends UserPluginBase
{
    /* オブジェクト変数 */

    /**
     * 変更時のPOSTデータ
     */
    public $post = null;

    /* コアから呼び出す関数 */

    /**
     * 関数定義（コアから呼び出す）
     */
    public function getPublicFunctions()
    {
        // 標準関数以外で画面などから呼ばれる関数の定義
        $functions = array();
        $functions['get']  = ['editView'];
        $functions['post'] = ['saveView'];
        return $functions;
    }

    /**
     *  権限定義
     */
    public function declareRole()
    {
        // 権限チェックテーブル
        $role_ckeck_table = array();
        $role_ckeck_table["editView"] = array('role_article');
        $role_ckeck_table["saveView"] = array('role_article');
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

    /**
     * POST取得関数（コアから呼び出す）
     * コアがPOSTチェックの際に呼び出す関数
     */
    public function getPost($id)
    {
        // 一度読んでいれば、そのPOSTを再利用する。
        if (!empty($this->post)) {
            return $this->post;
        }

        // POST を取得する。
        $this->post = LinklistPost::firstOrNew(['id' => $id]);
        return $this->post;
    }

    /* private関数 */

    /**
     * プラグインのフレームにコアのフレーム情報を付加して取得
     */
    private function getPluginFrame($frame_id)
    {
/*
        return LinklistFrame::firstOrNew(['frame_id' => $frame_id]);
*/
        $linklist_frame = LinklistFrame::select('linklist_frames.*', 'frames.bucket_id')
                                       ->join('frames', 'frames.id', '=', 'linklist_frames.frame_id')
                                       ->where('linklist_frames.frame_id', $frame_id)
                                       ->whereNull('linklist_frames.deleted_at')
                                       ->first();
        if (empty($linklist_frame)) {
            $linklist_frame = new LinklistFrame();
        }
        return $linklist_frame;
    }

    /**
     * フレームに紐づけられたリンクリストの取得
     */
    private function getLinklist($frame_id)
    {
        // linklists データ
        return Linklist::where('frame_id', $frame_id)->first();
    }

    /**
     * プラグインのバケツ取得関数
     */
    public function getPluginBuckets()
    {
        // プラグインのメインデータを取得する。
        return Linklist::orderBy('created_at', 'desc')->paginate(10);
    }

    /**
     * プラグインのバケツ取得関数
     */
    public function getPluginBucket($bucket_id)
    {
        // プラグインのメインデータを取得する。
        return Linklist::firstOrNew(['bucket_id' => $bucket_id]);
    }

    /**
     *  POST チェック設定
     */
    private function makeValidator($request)
    {
        // 項目のエラーチェック
        $validator = Validator::make($request->all(), [
            'title'            => ['required'],
            'display_sequence' => ['nullable', 'numeric'],
        ]);
        $validator->setAttributeNames([
            'title'            => 'タイトル',
            'display_sequence' => '表示順',
        ]);
        return $validator;
    }

    /**
     *  表示条件に対するソート条件追加
     */
    private function appendOrder($query, $linklists_frame)
    {
        if ($linklists_frame->sequence_conditions == 0) {
            // 最新順
            $query->orderBy('created_at', 'desc');
        } elseif ($linklists_frame->sequence_conditions == 1) {
            // 投稿順
            $query->orderBy('created_at', 'asc');
        } elseif ($linklists_frame->sequence_conditions == 2) {
            // 指定順
            $query->orderBy('display_sequence', 'asc');
        }

        return $query;
    }

    /**
     *  POST一覧取得
     */
    private function getPosts($linklist_frame, $option_count = null)
    {
        // 件数
        $count = $linklist_frame->view_count;
        if ($option_count != null) {
            $count = $option_count;
        }

        // データ取得
        $posts_query = LinklistPost::select('linklist_posts.*')
                                   ->join('linklists', function ($join) use ($linklist_frame) {
                                       $join->on('linklists.id', '=', 'linklist_posts.linklist_id')
                                          ->where('linklists.bucket_id', '=', $linklist_frame->bucket_id);
                                   })
                                   ->whereNull('linklist_posts.deleted_at')
                                   ->orderBy('created_at', 'desc');

        // 表示条件に対するソート条件追加
        $posts_query = $this->appendOrder($posts_query, $linklist_frame);

        // 取得
        return $posts_query->paginate($linklist_frame->view_count);
    }

    /* スタティック関数 */

    /**
     *  新着情報用メソッド
     */
    public static function getWhatsnewArgs()
    {
        // 戻り値('sql_method'、'link_pattern'、'link_base')
        $return[] = DB::table('linklists_posts')
                      ->select(
                          'frames.page_id               as page_id',
                          'frames.id                    as frame_id',
                          'linklists_posts.id           as post_id',
                          'linklists_posts.title        as post_title',
                          DB::raw("null                 as important"),
                          'linklists_posts.created_at   as posted_at',
                          'linklists_posts.created_name as posted_name',
                          DB::raw("null                 as classname"),
                          DB::raw("null                 as category"),
                          DB::raw('"linklists"          as plugin_name')
                      )
                      ->join('linklists', 'linklists.id',  '=', 'linklists_posts.linklists_id')
                      ->join('frames', 'frames.bucket_id', '=', 'linklists.bucket_id')
                      ->where('frames.disable_whatsnews', 0)
                      ->whereNull('linklists_posts.deleted_at');

        $return[] = 'show_page_frame_post';
        $return[] = '/plugin/linklists/show';

        return $return;
    }

    /**
     *  検索用メソッド
     */
/*
    public static function getSearchArgs($search_keyword)
    {
        $return[] = DB::table('linklists_posts')
                      ->select(
                          'linklists_posts.id           as post_id',
                          'frames.id                    as frame_id',
                          'frames.page_id               as page_id',
                          'pages.permanent_link         as permanent_link',
                          'linklists_posts.title        as post_title',
                          DB::raw("null                 as important"),
                          'linklists_posts.created_at   as posted_at',
                          'linklists_posts.created_name as posted_name',
                          DB::raw("null                 as classname"),
                          DB::raw("null                 as category_id"),
                          DB::raw("null                 as category"),
                          DB::raw('"linklists"          as plugin_name')
                      )
                      ->join('linklists', 'linklists.id',  '=', 'linklists_posts.linklists_id')
                      ->join('frames', 'frames.bucket_id', '=', 'linklists.bucket_id')
                      ->leftjoin('pages', 'pages.id',      '=', 'frames.page_id')
                      ->where(function ($plugin_query) use ($search_keyword) {
                          $plugin_query->where('linklists_posts.title', 'like', '?')
                                       ->orWhere('linklists_posts.url', 'like', '?');
                      })
                      ->whereNull('linklists_posts.deleted_at');


        $bind = array('%'.$search_keyword.'%', '%'.$search_keyword.'%');
        $return[] = $bind;
        $return[] = 'show_page_frame_post';
        $return[] = '/plugin/linklists/show';

        return $return;
    }
*/
    /* 画面アクション関数 */

    /**
     *  データ初期表示関数
     *  コアがページ表示の際に呼び出す関数
     */
    public function index($request, $page_id, $frame_id)
    {
        // プラグインのフレームデータ
        $plugin_frame = $this->getPluginFrame($frame_id);

        // リンクリストデータ一覧の取得
        $posts = $this->getPosts($plugin_frame);

        // 表示テンプレートを呼び出す。
        return $this->view('index', [
            'plugin_frame' => $plugin_frame,
            'posts'        => $posts,
        ]);
    }

    /**
     *  新規記事画面
     */
/*
    public function create($request, $page_id, $frame_id)
    {
        // プラグインのフレームデータ
        $plugin_frame = $this->getPluginFrame($frame_id);

        // 空のデータ(画面で初期値設定で使用するため)
        $post = new LinklistsPosts();

        // 表示テンプレートを呼び出す。(blade でold を使用するため、withInput 使用)
        return $this->view('input', [
            'plugin_frame' => $plugin_frame,
            'post'         => $post,
        ])->withInput($request->all);
    }
*/

    /**
     *  詳細表示関数
     */
    public function show($request, $page_id, $frame_id, $post_id)
    {
        // プラグインのフレームデータ
        $plugin_frame = $this->getPluginFrame($frame_id);

        // 記事取得
        $post = $this->getPost($post_id);

        // 詳細画面を呼び出す。
        return $this->view('show', [
            'plugin_frame' => $plugin_frame,
            'post'         => $post,
        ]);
    }

    /**
     * 記事編集画面
     */
    public function edit($request, $page_id, $frame_id, $post_id = null)
    {
        // プラグインのフレームデータ
        $plugin_frame = $this->getPluginFrame($frame_id);

        // 記事取得
        $post = $this->getPost($post_id);

        // 変更画面を呼び出す。(blade でold を使用するため、withInput 使用)
        return $this->view('edit', [
            'plugin_frame' => $plugin_frame,
            'post'         => $post,
        ])->withInput($request->all);
    }

    /**
     *  記事登録処理
     */
    public function save($request, $page_id, $frame_id, $post_id = null)
    {
        // 項目のエラーチェック
        $validator = $this->makeValidator($request);

        // エラーがあった場合は入力画面に戻る。
        if ($validator->fails()) {
            return redirect()->back()->withErrors($validator)->withInput();
        }

        // POSTデータのモデル取得
        $post = LinklistPost::firstOrNew(['id' => $post_id]);

        // フレームから linklist_id 取得
        $linklist_frame = $this->getPluginFrame($frame_id);

        // 値のセット
        $post->linklist_id      = $linklist_frame->linklist_id;
        $post->title            = $request->title;
        $post->url              = $request->url;
        $post->description      = $request->description;
        $post->display_sequence = intval(empty($request->display_sequence) ? 0 : $request->display_sequence);

        // データ保存
        $post->save();

        // 登録後はリダイレクトされて編集画面が開く。
        return;
    }

    /**
     *  削除処理
     */
    public function delete($request, $page_id, $frame_id, $post_id)
    {
        // id がある場合、データを削除
        if ($post_id) {
            // データを削除する。
            LinklistsPosts::where('linklists_id', $post_id)->delete();
        }
        // 削除後はリダイレクトされて表示ページが開く。
        return;
    }

    /**
     * プラグインのバケツ選択表示関数
     */
    public function listBuckets($request, $page_id, $frame_id, $id = NULL)
    {
        // 表示テンプレートを呼び出す。
        return $this->view('list_buckets', [
            'plugin_buckets' => $this->getPluginBuckets(),
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
     * フレーム表示設定画面の表示
     */
    public function editView($request, $page_id, $frame_id)
    {
        // 表示テンプレートを呼び出す。
        return $this->view('frame', [
            // 表示中のバケツデータ
            'linklist'       => $this->getPluginBucket($this->getBucketId()),
            'linklist_frame' => $this->getPluginFrame($frame_id),
        ])->withInput($request->all);
    }

    /**
     * フレーム表示設定の保存
     */
    public function saveView($request, $page_id, $frame_id, $linklist_id)
    {
        // フレームごとの表示設定の更新
        $linklist_frame = $this->LinklistFrame::updateOrCreate(
           ['linklist_id' => $linklist_id, 'frame_id' => $frame_id],
           ['view_count' => $request->view_count],
        );

        // 削除後はリダイレクトされてバケツ一覧ページが開く。
        return;
    }

    /**
     * バケツ設定変更画面の表示
     */
    public function editBuckets($request, $page_id, $frame_id)
    {
        // コアがbucket_id なしで呼び出してくるため、bucket_id は frame_id から探す。
        $bucket_id = $this->getBucketId();

        // 表示テンプレートを呼び出す。
        return $this->view('bucket', [
            // 表示中のバケツデータ
            'linklist' => $this->getPluginBucket($bucket_id),
        ])->withInput($request->all);
    }

    /**
     *  バケツ登録処理
     */
    public function saveBuckets($request, $page_id, $frame_id, $bucket_id = null)
    {
        // 項目のエラーチェック
        $validator = Validator::make($request->all(), [
            'name' => ['required'],
        ]);
        $validator->setAttributeNames([
            'name' => 'リンクリスト名',
        ]);

        // エラーがあった場合は入力画面に戻る。
        if ($validator->fails()) {
            return redirect()->back()->withErrors($validator)->withInput();
        }

        // バケツの取得。なければ登録。
        $bucket = Buckets::updateOrCreate(
           ['id' => $bucket_id],
           ['bucket_name' => $request->name, 'plugin_name' => 'linklists'],
        );

        // フレームにバケツの紐づけ
        $frame = Frame::find($frame_id)->update(['bucket_id' => $bucket->id]);

        // プラグインバケツを取得(なければ新規オブジェクト)
        // プラグインバケツにデータを設定して保存
        $linklist = $this->getPluginBucket($bucket->id);
        $linklist->name = $request->name;
        $linklist->save();

        // プラグインフレームを作成 or 更新
        $linklist_frame = LinklistFrame::updateOrCreate(
           ['frame_id' => $frame_id],
           ['linklist_id' => $linklist->id, 'frame_id' => $frame_id],
        );

        // 登録後はリダイレクトされて編集ページが開く。
        return;
    }

    /**
     *  削除処理
     */
    public function destroyBuckets($request, $page_id, $frame_id, $linklist_id)
    {
        // プラグインバケツの取得
        $linklist = Linklist::find($linklist_id);
        if (empty($linklist)) {
            return;
        }

        // POSTデータ削除
        LinklistPost::where('linklist_id', $linklist->id)->delete();

        // FrameのバケツIDの更新
        Frame::where('id', $frame_id)->update(['bucket_id' => null]);

        // バケツ削除
        Buckets::find($linklist->bucket_id)->delete();

        // プラグインデータ削除
        $linklist->delete();

        // 削除後はリダイレクトされてバケツ一覧ページが開く。
        return;
    }

   /**
    * データ紐づけ変更関数
    */
    public function changeBuckets($request, $page_id, $frame_id)
    {
        // FrameのバケツIDの更新
        Frame::where('id', $frame_id)
               ->update(['bucket_id' => $request->select_bucket]);

        // Linklists の特定
        $plugin_bucket = $this->getPluginBucket($request->select_bucket);

        // フレームごとの表示設定の更新
        $linklist_frame = $this->getPluginFrame($frame_id);
        $linklist_frame->linklist_id = $plugin_bucket->id;
        $linklist_frame->frame_id = $frame_id;
        $linklist_frame->save();

        // 削除後はリダイレクトされてバケツ一覧ページが開く。
        return;
    }
}
