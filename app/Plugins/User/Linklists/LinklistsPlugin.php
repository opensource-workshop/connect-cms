<?php

namespace App\Plugins\User\Linklists;

use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;

use App\Models\Common\Buckets;
use App\Models\Common\Frame;
use App\Models\Common\Categories;
use App\Models\User\Linklists\Linklist;
use App\Models\User\Linklists\LinklistFrame;
use App\Models\User\Linklists\LinklistPost;

use App\Rules\CustomValiTextMax;
use App\Rules\CustomValiUrlMax;

use App\Plugins\User\UserPluginBase;

/**
 * リンクリスト・プラグイン
 *
 * @author 永原　篤 <nagahara@opensource-workshop.jp>
 * @copyright OpenSource-WorkShop Co.,Ltd. All Rights Reserved
 * @category リンクリスト・プラグイン
 * @package Controller
 */
class LinklistsPlugin extends UserPluginBase
{
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
        $functions['get']  = [];
        $functions['post'] = [];
        return $functions;
    }

    /**
     * 権限定義
     */
    public function declareRole()
    {
        // 権限チェックテーブル
        $role_check_table = [];
        $role_check_table["edit"]        = ['frames.edit'];
        $role_check_table["save"]        = ['frames.create'];
        $role_check_table["delete"]      = ['frames.delete'];
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
     * リンクデータ取得
     */
    private function getLinklistPost($id)
    {
        return LinklistPost::firstOrNew(['id' => $id]);
    }

    /**
     * プラグインのフレーム
     */
    private function getPluginFrame($frame_id)
    {
        return LinklistFrame::firstOrNew(['frame_id' => $frame_id]);
    }

    /**
     * プラグインのバケツ取得関数
     */
    private function getPluginBucket($bucket_id)
    {
        // プラグインのメインデータを取得する。
        return Linklist::firstOrNew(['bucket_id' => $bucket_id]);
    }

    /**
     *  POST一覧取得
     */
    private function getPosts($linklist_frame)
    {
        // データ取得
        $posts_query = LinklistPost::
            select(
                'linklist_posts.*',
                'categories.color as category_color',
                'categories.background_color as category_background_color',
                'categories.category as category',
                'plugin_categories.view_flag as category_view_flag',
                'plugin_categories.categories_id as plugin_categories_categories_id'
            )
            ->join('linklists', function ($join) {
                $join->on('linklists.id', '=', 'linklist_posts.linklist_id')
                    ->where('linklists.bucket_id', $this->frame->bucket_id)
                    ->whereNull('linklists.deleted_at');
            });

        // カテゴリのleftJoin
        $posts_query = Categories::appendCategoriesLeftJoin($posts_query, $this->frame->plugin_name, 'linklist_posts.categories_id', 'linklists.id');

        $posts_query->orderBy('plugin_categories.display_sequence', 'asc')
            ->orderBy('linklist_posts.display_sequence', 'asc')
            ->orderBy('linklist_posts.created_at', 'asc');

        // 取得
        return $posts_query->paginate($linklist_frame->view_count, ["*"], "frame_{$linklist_frame->id}_page");
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
                      ->join('linklists', 'linklists.id', '=', 'linklists_posts.linklists_id')
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

        // バケツから linklist_id 取得
        $linklist = $this->getPluginBucket($this->getBucketId());

        // 表示テンプレートを呼び出す。
        return $this->view('index', [
            'posts' => $posts,
            'plugin_frame' => $plugin_frame,
            'linklist' => $linklist,
        ]);
    }

    /**
     *  詳細表示関数
     */
    /*
    新着と検索で呼ばれたときの詳細画面のイメージ
    public function show($request, $page_id, $frame_id, $post_id)
    {
        // 記事取得
        $post = $this->getPost($post_id);

        // 詳細画面を呼び出す。
        return $this->view('show', [
            'post'         => $post,
        ]);
    }
    */

    /**
     * 記事編集画面
     */
    public function edit($request, $page_id, $frame_id, $post_id = null)
    {
        // 記事取得
        $post = $this->getLinklistPost($post_id);

        // 更新時
        if ($post_id) {
            if (empty($post->id)) {
                return $this->view_error("403_inframe", null, 'データ存在チェック');
            }
        }

        // バケツから linklist_id 取得
        $linklist = $this->getPluginBucket($this->getBucketId());

        // カテゴリ
        $categories = Categories::getInputCategories($this->frame->plugin_name, $linklist->id);

        // 変更画面を呼び出す。
        return $this->view('edit', [
            'post' => $post,
            'categories' => $categories,
        ]);
    }

    /**
     *  記事登録処理
     */
    public function save($request, $page_id, $frame_id, $post_id = null)
    {
        // POSTデータのモデル取得
        $post = $this->getLinklistPost($post_id);

        // 更新時
        if ($post_id) {
            if (empty($post->id)) {
                return $this->view_error("403_inframe", null, 'データ存在チェック');
            }
        }

        // 項目のエラーチェック
        $validator = Validator::make($request->all(), [
            'title'            => ['required', 'max:255'],
            'url'              => ['required', new CustomValiUrlMax()],
            'description'      => [new CustomValiTextMax()],
            'display_sequence' => ['nullable', 'numeric'],
        ]);
        $validator->setAttributeNames([
            'title'            => 'タイトル',
            'url'              => 'URL',
            'description'      => '説明',
            'display_sequence' => '表示順',
        ]);

        // エラーがあった場合は入力画面に戻る。
        if ($validator->fails()) {
            return back()->withErrors($validator)->withInput();
        }

        // バケツから linklist_id 取得
        // bugfix: linklist_idはフレームではなく、表示しているバケツから取得する
        // $linklist_frame = $this->getPluginFrame($frame_id);
        $linklist = $this->getPluginBucket($this->getBucketId());

        // 表示順が空なら、自分を省いた最後の番号+1 をセット
        if ($request->filled('display_sequence')) {
            $display_sequence = intval($request->display_sequence);
        } else {
            $max_display_sequence = LinklistPost::where('linklist_id', $linklist->id)->where('id', '<>', $post_id)->max('display_sequence');
            $display_sequence = empty($max_display_sequence) ? 1 : $max_display_sequence + 1;
        }

        // 値のセット
        $post->linklist_id       = $linklist->id;
        $post->title             = $request->title;
        $post->url               = $request->url;
        $post->target_blank_flag = $request->target_blank_flag;
        $post->description       = $request->description;
        $post->categories_id     = $request->categories_id;
        $post->display_sequence  = $display_sequence;

        // データ保存
        $post->save();

        // 登録後はリダイレクトして編集画面を開く。
        return new Collection(['redirect_path' => url('/') . "/plugin/linklists/edit/" . $page_id . "/" . $frame_id . "/" . $post->id . "#frame-" . $frame_id]);
    }

    /**
     *  削除処理
     */
    public function delete($request, $page_id, $frame_id, $post_id)
    {
        // id がある場合、データを削除
        if ($post_id) {

            $post = $this->getLinklistPost($post_id);
            if (empty($post->id)) {
                return $this->view_error("403_inframe", null, 'データ存在チェック');
            }

            // データを削除する。
            LinklistPost::where('id', $post_id)->delete();
        }
        return;
    }

    /**
     * プラグインのバケツ選択表示関数
     */
    public function listBuckets($request, $page_id, $frame_id, $id = null)
    {
        // 表示テンプレートを呼び出す。
        return $this->view('list_buckets', [
            'plugin_buckets' => Linklist::orderBy('created_at', 'desc')->paginate(10),
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
        ]);
    }

    /**
     * フレーム表示設定の保存
     */
    public function saveView($request, $page_id, $frame_id, $linklist_id)
    {
        // 項目のエラーチェック
        $validator = Validator::make($request->all(), [
            'view_count' => ['nullable', 'numeric'],
            'type'       => ['nullable', 'numeric'],
        ]);
        $validator->setAttributeNames([
            'view_count' => '表示件数',
            'type'       => '表示形式',
        ]);

        // エラーがあった場合は入力画面に戻る。
        if ($validator->fails()) {
            return back()->withErrors($validator)->withInput();
        }

        // フレームごとの表示設定の更新
        $linklist_frame = LinklistFrame::updateOrCreate(
            // bugfix: LinklistFrameは frame_id のみfirst()取得しているため、frame_idのみで登録・更新する
            // ['linklist_id' => $linklist_id, 'frame_id' => $frame_id],
            ['frame_id' => $frame_id],
            [
                'view_count'  => $request->view_count,
                'type'        => $request->type
            ],
        );

        return;
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
            'linklist' => $this->getPluginBucket($bucket_id),
        ]);
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
            return back()->withErrors($validator)->withInput();
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

        // プラグインフレームが存在しなければ作成
        $linklist_frame = LinklistFrame::firstOrCreate(['frame_id' => $frame_id]);

        // 登録後はリダイレクトして編集ページを開く。
        return new Collection(['redirect_path' => url('/') . "/plugin/linklists/editBuckets/" . $page_id . "/" . $frame_id . "/" . $bucket->id . "#frame-" . $frame_id]);
    }

    /**
     *  削除処理
     */
    public function destroyBuckets($request, $page_id, $frame_id, $linklist_id)
    {
        // deleted_id, deleted_nameを自動セットするため、複数件削除する時はdestroy()を利用する。

        // プラグインバケツの取得
        $linklist = Linklist::find($linklist_id);
        if (empty($linklist)) {
            return;
        }

        // POSTデータ削除
        // see) https://readouble.com/laravel/5.5/ja/collections.html#method-pluck
        $linklist_post_ids = LinklistPost::where('linklist_id', $linklist->id)->pluck('id');
        LinklistPost::destroy($linklist_post_ids);

        // カテゴリ削除
        Categories::destroyBucketsCategories($this->frame->plugin_name, $linklist->id);

        // FrameのバケツIDの更新
        Frame::where('bucket_id', $linklist->bucket_id)->update(['bucket_id' => null]);

        // delete: バケツ削除時に表示設定は消さない. 今後フレーム削除時にプラグイン側で追加処理ができるようになったら linklist_frame を削除する
        // プラグインフレームデータの削除(deleted_id を記録するために1回読んでから削除)
        // $linklist_frame = LinklistFrame::where('frame_id', $frame_id)->first();
        // $linklist_frame->delete();

        // バケツ削除
        Buckets::destroy($linklist->bucket_id);

        // プラグインデータ削除
        $linklist->delete();

        return;
    }

    /**
     * データ紐づけ変更関数
     */
    public function changeBuckets($request, $page_id, $frame_id)
    {
        // FrameのバケツIDの更新
        Frame::where('id', $frame_id)->update(['bucket_id' => $request->select_bucket]);

        // delete: バケツ切替時、表示設定のフレームIDは同じため、更新不要
        // // Linklists の特定
        // $plugin_bucket = $this->getPluginBucket($request->select_bucket);

        // // フレームごとの表示設定の更新
        // $linklist_frame = $this->getPluginFrame($frame_id);
        // $linklist_frame->linklist_id = $plugin_bucket->id;
        // $linklist_frame->frame_id = $frame_id;
        // $linklist_frame->save();

        return;
    }

    /**
     * カテゴリ表示関数
     */
    public function listCategories($request, $page_id, $frame_id, $id = null)
    {
        // バケツから linklist_id 取得
        $linklist = $this->getPluginBucket($this->getBucketId());

        // 共通カテゴリ
        $general_categories = Categories::getGeneralCategories($this->frame->plugin_name, $linklist->id);

        // 個別カテゴリ（プラグイン）
        $plugin_categories = Categories::getPluginCategories($this->frame->plugin_name, $linklist->id);

        // 表示テンプレートを呼び出す。
        return $this->view('list_categories', [
            'general_categories' => $general_categories,
            'plugin_categories' => $plugin_categories,
            'linklist' => $linklist,
        ]);
    }

    /**
     * カテゴリ登録処理
     */
    public function saveCategories($request, $page_id, $frame_id, $id = null)
    {
        /* エラーチェック
        ------------------------------------ */

        $validator = Categories::validatePluginCategories($request);

        if ($validator->fails()) {
            return redirect()->back()->withErrors($validator)->withInput();
        }

        /* カテゴリ追加
        ------------------------------------ */

        // バケツから linklist_id 取得
        $linklist = $this->getPluginBucket($this->getBucketId());

        Categories::savePluginCategories($request, $this->frame->plugin_name, $linklist->id);

        // このメソッドはredirect 付のルートで呼ばれて、処理後はページの再表示が行われるため、ここでは何もしない。
    }

    /**
     * カテゴリ削除処理
     */
    public function deleteCategories($request, $page_id, $frame_id, $id = null)
    {
        Categories::deleteCategories($this->frame->plugin_name, $id);
    }
}
