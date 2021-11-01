<?php

namespace App\Plugins\User\Bbses;

use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;

use App\Enums\StatusType;
use App\Enums\UserStatus;

use App\Models\Common\Buckets;
use App\Models\Common\Frame;
use App\Models\Common\Like;
use App\Models\User\Bbses\Bbs;
use App\Models\User\Bbses\BbsFrame;
use App\Models\User\Bbses\BbsPost;

use App\Rules\CustomValiWysiwygMax;

use App\Plugins\User\UserPluginBase;

/**
 * 掲示板・プラグイン
 *
 * @author 永原　篤 <nagahara@opensource-workshop.jp>
 * @copyright OpenSource-WorkShop Co.,Ltd. All Rights Reserved
 * @category 掲示板・プラグイン
 * @package Controller
 */
class BbsesPlugin extends UserPluginBase
{
    /* DB migrate
       php artisan make:migration create_bbses --create=bbses
       php artisan make:migration create_bbs_posts --create=bbs_posts
       php artisan make:migration create_bbs_frames --create=bbs_frames
    */

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
        $functions['get']  = ['editView', 'saveLikeJson'];
        $functions['post'] = ['saveView', 'edit', 'reply'];
        return $functions;
    }

    /**
     * 権限定義
     */
    public function declareRole()
    {
        // 権限チェックテーブル
        $role_check_table = [];
        $role_check_table["editView"] = array('role_arrangement');
        $role_check_table["saveView"] = array('role_arrangement');
        $role_check_table["reply"]    = array('posts.create');
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

    /**
     * POST取得関数（コアから呼び出す）
     * コアがPOSTチェックの際に呼び出す関数
     */
    public function getPost($id, $action = null)
    {
        if (is_null($action)) {
            // プラグイン内からの呼び出しを想定。処理を通す。
        } elseif (in_array($action, ['edit', 'save', 'delete'])) {
            // コアから呼び出し。posts.update|posts.deleteの権限チェックを指定したアクションは、処理を通す。
        } else {
            // それ以外のアクションは null で返す。
            return null;
        }

        // 一度読んでいれば、そのPOSTを再利用する。
        if (!empty($this->post)) {
            return $this->post;
        }

        // 権限によって表示する記事を絞る
        // $this->post = BbsPost::select('bbs_posts.*')
        $bbses_query = BbsPost::
            select(
                'bbs_posts.*',
                'likes.id as like_id',
                'likes.count as like_count',
                'like_users.id as like_users_id'    // idあればいいね済み
            )
            ->where(function ($query) {
                $query = $this->appendAuthWhere($query, 'bbs_posts');
            });
            // ->firstOrNew(['id' => $id]);

        // いいねのleftJoin
        $bbses_query = Like::appendLikeLeftJoin($bbses_query, $this->frame->plugin_name, 'bbs_posts.id', 'bbs_posts.bbs_id');

        $this->post = $bbses_query->firstOrNew(['bbs_posts.id' => $id]);

        return $this->post;
    }

    /* private関数 */

    /**
     * プラグインのフレーム
     */
    private function getPluginFrame($frame_id)
    {
        return BbsFrame::firstOrNew(['frame_id' => $frame_id]);
    }

    /**
     * プラグインのバケツ取得関数
     */
    private function getPluginBucket($bucket_id)
    {
        // プラグインのメインデータを取得する。
        return Bbs::firstOrNew(['bucket_id' => $bucket_id]);
    }

    /**
     * データ取得時の権限条件の付与
     */
    protected function appendAuthWhere($query, $table_name)
    {
        return $this->appendAuthWhereBase($query, $table_name);
    }

    /**
     * Root の POST一覧取得
     */
    private function getRootPosts($bbs_frame, $frame_id)
    {
        // データ取得
        $posts_query = BbsPost::
            select(
                'bbs_posts.*',
                'likes.id as like_id',
                'likes.count as like_count',
                'like_users.id as like_users_id'    // idあればいいね済み
            )
            ->join('bbses', function ($join) {
                $join->on('bbses.id', '=', 'bbs_posts.bbs_id')
                    ->where('bbses.bucket_id', '=', $this->frame->bucket_id);
            })
            ->whereNull('bbs_posts.parent_id')
            ->whereNull('bbs_posts.deleted_at');

        // 権限によって表示する記事を絞る
        $posts_query = $this->appendAuthWhere($posts_query, 'bbs_posts');

        // いいねのleftJoin
        $posts_query = Like::appendLikeLeftJoin($posts_query, $this->frame->plugin_name, 'bbs_posts.id', 'bbs_posts.bbs_id');

        // 根記事の表示順
        if ($bbs_frame->thread_sort_flag == 1) {
            // 根記事の新しい日時順
            $posts_query->orderBy('created_at', 'desc');
        } else {
            // スレッド内の新しい更新日時順
            $posts_query->orderBy('thread_updated_at', 'desc');
        }

        // 取得
        return $posts_query->paginate($bbs_frame->getViewCount(), ["*"], "frame_{$frame_id}_page");
    }

    /**
     * 指定されたスレッドの記事一覧取得
     */
    private function getThreadPosts($bbs_frame, $thread_root_ids, $children_only = false)
    {
        // データ取得
        $posts_query = BbsPost::
            select(
                'bbs_posts.*',
                'likes.id as like_id',
                'likes.count as like_count',
                'like_users.id as like_users_id'    // idあればいいね済み
            )
            ->join('bbses', function ($join) {
                $join->on('bbses.id', '=', 'bbs_posts.bbs_id')
                    ->where('bbses.bucket_id', '=', $this->frame->bucket_id);
            });

        // ルートのポストは含まない場合
        if ($children_only) {
            $posts_query->whereColumn('bbs_posts.id', '<>', 'bbs_posts.thread_root_id');
        }

        // いいねのleftJoin
        $posts_query = Like::appendLikeLeftJoin($posts_query, $this->frame->plugin_name, 'bbs_posts.id', 'bbs_posts.bbs_id');

        // その他条件指定
        $posts_query->whereIn('bbs_posts.thread_root_id', $thread_root_ids)
                    ->whereNull('bbs_posts.deleted_at')
                    ->orderBy('created_at', 'asc');

        // 権限によって表示する記事を絞る
        $posts_query = $this->appendAuthWhere($posts_query, 'bbs_posts');

        // 取得（ここは対象スレッドの記事全てなので、get() で件数指定しない）
        return $posts_query->get();
    }

    /* スタティック関数 */

    /**
     * 新着情報用メソッド
     */
    public static function getWhatsnewArgs()
    {
        // 戻り値('sql_method'、'link_pattern'、'link_base')
        $return[] = BbsPost::
            select(
                'frames.page_id         as page_id',
                'frames.id              as frame_id',
                'bbs_posts.id           as post_id',
                'bbs_posts.title        as post_title',
                DB::raw("null           as important"),
                'bbs_posts.created_at   as posted_at',
                'bbs_posts.created_name as posted_name',
                DB::raw("null           as classname"),
                DB::raw("null           as category"),
                DB::raw('"bbses"        as plugin_name')
            )
            ->join('bbses', function ($join) {
                // 論理削除対応
                $join->on('bbses.id', '=', 'bbs_posts.bbs_id')
                    ->whereNull('bbses.deleted_at');
            })
            ->join('frames', 'frames.bucket_id', '=', 'bbses.bucket_id')
            ->where('bbs_posts.status', StatusType::active)
            ->where('frames.disable_whatsnews', 0);

        $return[] = 'show_page_frame_post';
        $return[] = '/plugin/bbses/show';

        return $return;
    }

    /**
     * 検索用メソッド
     */
    public static function getSearchArgs($search_keyword)
    {
        $return[] = BbsPost::
            select(
                'bbs_posts.id           as post_id',
                'frames.id                as frame_id',
                'frames.page_id           as page_id',
                'pages.permanent_link     as permanent_link',
                'bbs_posts.title        as post_title',
                DB::raw("null             as important"),
                'bbs_posts.created_at   as posted_at',
                'bbs_posts.created_name as posted_name',
                DB::raw("null             as classname"),
                DB::raw("null             as category_id"),
                DB::raw("null             as category"),
                DB::raw('"bbses"          as plugin_name')
            )
            ->join('bbses', function ($join) {
                // 論理削除対応
                $join->on('bbses.id', '=', 'bbs_posts.bbs_id')
                    ->whereNull('bbses.deleted_at');
            })
            ->join('frames', 'frames.bucket_id', '=', 'bbses.bucket_id')
            ->leftjoin('pages', 'pages.id', '=', 'frames.page_id')
            ->where(function ($plugin_query) {
                $plugin_query->where('bbs_posts.title', 'like', '?')
                    ->orWhere('bbs_posts.body', 'like', '?');
            });

        $bind = array('%' . $search_keyword . '%', '%' . $search_keyword . '%');
        $return[] = $bind;
        $return[] = 'show_page_frame_post';
        $return[] = '/plugin/bbses/show';

        return $return;
    }

    /* 画面アクション関数 */

    /**
     * データ初期表示関数
     * コアがページ表示の際に呼び出す関数
     */
    public function index($request, $page_id, $frame_id)
    {
        // プラグインのフレームデータ
        $plugin_frame = $this->getPluginFrame($frame_id);

        // 掲示板データ一覧の取得
        $posts = $this->getRootPosts($plugin_frame, $frame_id);

        // 表示対象のスレッドの記事一覧
        $thread_ids = $posts->pluck("id");
        $children_posts = $this->getThreadPosts($plugin_frame, $thread_ids, true);

        // 表示テンプレートを呼び出す。
        return $this->view('index', [
            'bbs'            => $this->getPluginBucket($this->getBucketId()),
            'posts'          => $posts,
            'children_posts' => $children_posts,
            'plugin_frame'   => $plugin_frame,
        ]);
    }

    /**
     * 関連投稿の取得
     */
    private function getThreadPost($plugin_frame, $post)
    {
        // 変数準備
        $thread_root_post = null;
        $children_posts = null;

        // 指定の記事がある場合
        if ($post) {
            // 根記事取得（getPost() はメインのPOST をシングルトンで保持するので、ここでは新たに取得する）
            // $thread_root_post = BbsPost::firstOrNew(['id' => $post->thread_root_id]);
            $thread_root_post_query = BbsPost::
                select(
                    'bbs_posts.*',
                    'likes.id as like_id',
                    'likes.count as like_count',
                    'like_users.id as like_users_id'    // idあればいいね済み
                );
            $thread_root_post_query = Like::appendLikeLeftJoin($thread_root_post_query, $this->frame->plugin_name, 'bbs_posts.id', 'bbs_posts.bbs_id');
            $thread_root_post = $thread_root_post_query->firstOrNew(['bbs_posts.id' => $post->thread_root_id]);

            // 表示対象のスレッドの記事一覧
            $children_posts = $this->getThreadPosts($plugin_frame, new Collection($post->thread_root_id), true);
        }

        // $thread_root_post, $children_posts を返す。
        return array($thread_root_post, $children_posts);
    }

    /**
     * 詳細表示関数
     */
    public function show($request, $page_id, $frame_id, $post_id)
    {
        // プラグインのフレームデータ
        $plugin_frame = $this->getPluginFrame($frame_id);

        // 記事取得
        $post = $this->getPost($post_id);

        // 関連投稿の取得
        list($thread_root_post, $children_posts) = $this->getThreadPost($plugin_frame, $post);

        // 詳細画面を呼び出す。
        return $this->view('show', [
            'bbs' => $this->getPluginBucket($this->getBucketId()),
            'post' => $post,
            'thread_root_post' => $thread_root_post,
            'children_posts'   => $children_posts,
            'plugin_frame'     => $plugin_frame,
        ]);
    }

    /**
     * 記事編集画面
     */
    public function edit($request, $page_id, $frame_id, $post_id = null)
    {
        // 記事取得
        $post = $this->getPost($post_id);

        // モデレータ以上の権限がなく、記事にすでに返信が付いている場合は、編集できない。


//        if (empty($faqs_post)) {
//            return $this->view_error("403_inframe", null, 'showのユーザー権限に応じたPOST ID チェック');
//        }

        // 変更画面を呼び出す。
        return $this->view('edit', [
            'post' => $post,
        ]);
    }

    /**
     * 記事返信画面
     */
    public function reply($request, $page_id, $frame_id, $post_id)
    {
        // プラグインのフレームデータ
        $plugin_frame = $this->getPluginFrame($frame_id);

        // 記事取得
        $post = $this->getPost($post_id);

        // 関連投稿の取得
        list($thread_root_post, $children_posts) = $this->getThreadPost($plugin_frame, $post);

        // 変更画面を呼び出す。
        return $this->view('edit', [
            'bbs' => $this->getPluginBucket($this->getBucketId()),
            'post'        => new BbsPost(),
            'parent_post' => $post,
            'reply'       => $request->get('reply'),
            'reply_flag'  => true,
            'thread_root_post' => $thread_root_post,
            'children_posts'   => $children_posts,
            'plugin_frame'     => $plugin_frame,
        ]);
    }

    /**
     * 記事登録処理
     */
    public function save($request, $page_id, $frame_id, $post_id = null)
    {
        // 項目のエラーチェック
        $validator = Validator::make($request->all(), [
            'title' => ['required', 'max:255'],
            'body'  => ['required', new CustomValiWysiwygMax()],
        ]);
        $validator->setAttributeNames([
            'title' => 'タイトル',
            'body'  => '本文',
        ]);

        // エラーがあった場合は入力画面に戻る。
        if ($validator->fails()) {
            return back()->withErrors($validator)->withInput();
        }

        // POSTデータのモデル取得
        $post = BbsPost::firstOrNew(['id' => $post_id]);

        // 新規登録の判定のために、保存する前のレコードを退避しておく。
        $before_post = clone $post;

        // モデレータ以上の権限を持たずに、記事にすでに返信が付いている場合は、保存できない。
        if (!$this->isCan('role_article') && $post->descendants->count() > 0) {
            $validator = Validator::make($request->all(), []);
            $validator->errors()->add('reply_role_error', '返信のある記事の編集はできません。');
            return back()->withErrors($validator)->withInput();
        }

        // フレームから bbs_id 取得
        $bbs_frame = $this->getPluginFrame($frame_id);

        // 値のセット
        $post->bbs_id            = $bbs_frame->bbs_id;
        $post->title             = $request->title;
        $post->body              = $this->clean($request->body);

        // 新規投稿、もしくは返信の場合は投稿者をセット
        if (empty($post_id) || $request->filled('parent_id')) {
            $post->created_id    = Auth::user()->id;
        }

        // 承認の要否確認とステータス処理
        if ($request->status == StatusType::temporary) {
            $post->status = StatusType::temporary;  // 一時保存
        // } elseif ($this->buckets->needApprovalUser(Auth::user())) {
        } elseif ($this->isApproval()) {
            $post->status = StatusType::approval_pending;  // 承認待ち
        } else {
            $post->status = StatusType::active;  // 公開
        }

        // 返信の場合
        if ($request->filled('parent_id')) {
            // 親のpost を取得
            $parent_post = BbsPost::find($request->parent_id);
            // 親のpost からthread_root_id をコピー。これで同じスレッドの記事を取得できるようにする。
            $post->thread_root_id = $parent_post->thread_root_id;
            // 親のノードに追加
            $post->prependToNode($parent_post)->save();
            // 根記事にスレッド更新日時をセット
            BbsPost::where('id', $post->thread_root_id)->update(['thread_updated_at' => date('Y-m-d H:i:s')]);
        } else {
            // 根記事 or 編集のため、スレッド更新日時をセット
            $post->thread_updated_at = date('Y-m-d H:i:s');
            // 保存
            $post->save();

            // 根記事の場合、保存後のid をthread_root_id にセットして更新（変更の場合はthread_root_id はそのまま）
            if (empty($post->thread_root_id)) {
                $post->thread_root_id = $post->id;
                $post->save();
            }
        }

        // 投稿通知メール
        $this->sendPostNotice($post, $before_post, 'show');

        // 関連記事通知メール
        // この post の thread_root_id と同じ post でかつ、この post 自身ではなく、データの status は公開のもの。
        $mail_users = BbsPost::select('users.name', 'users.email')
                             ->join('users', 'users.id', '=', 'bbs_posts.created_id')
                             ->where('users.status', UserStatus::active)
                             ->whereNotNull('users.email')
                             ->where('bbs_posts.thread_root_id', $post->thread_root_id)
                             ->where('bbs_posts.id', '!=', $post->id)
                             ->where('bbs_posts.status', StatusType::active)
                             ->distinct()
                             ->get();
        $this->sendRelateNotice($post, $before_post, $mail_users, 'show');

        // 登録後はリダイレクトして詳細画面を開く。(form のリダイレクト指定では post した id が渡せないため)
        return new Collection(['redirect_path' => url('/') . "/plugin/bbses/show/" . $page_id . "/" . $frame_id . "/" . $post->id . "#frame-" . $frame_id]);
    }

    /**
     * 承認処理
     */
    public function approval($request, $page_id, $frame_id, $post_id)
    {
        // 記事取得
        $post = $this->getPost($post_id);

        // 承認済みの判定のために、保存する前にpost を退避しておく。
        $before_post = clone $post;

        // データがあることを確認
        if (empty($post)) {
            return;
        }

        // 更新されたら、行レコードの updated_at を更新したいので、update()
        $post->updated_at = now();
        $post->status = StatusType::active;  // 公開
        $post->update();

        // メール送信 引数(レコードを表すモデルオブジェクト, 保存前のレコード, 詳細表示メソッド)
        $this->sendPostNotice($post, $before_post, 'show');

        // 登録後は画面側の指定により、リダイレクトして表示画面を開く。
        return;
    }

    /**
     * 削除処理
     */
    public function delete($request, $page_id, $frame_id, $post_id)
    {
        // id がある場合、データを削除
        if ($post_id) {
            // メール送信のために、削除する前にレコードを退避しておく。
            $delete_post = BbsPost::firstOrNew(['id' => $post_id]);

            $delete_comment = "";
            if ($delete_post) {
                $delete_comment  = "以下、削除されたデータのタイトルです。\n";
                $delete_comment .= "「" . $delete_post->title . "」を削除しました。";
            }

            // データを削除する。（論理削除で削除日、ID などを残すためにupdate）
            BbsPost::where('id', $post_id)->update([
                'deleted_at'   => date('Y-m-d H:i:s'),
                'deleted_id'   => Auth::user()->id,
                'deleted_name' => Auth::user()->name,
            ]);

            // メール送信 引数(削除した行, 詳細表示メソッド, 削除データを表すメッセージ)
            $this->sendDeleteNotice($delete_post, 'show', $delete_comment);
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
            'plugin_buckets' => Bbs::orderBy('created_at', 'desc')->paginate(10, ["*"], "frame_{$frame_id}_page"),
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
            'bbs'       => $this->getPluginBucket($this->getBucketId()),
            'bbs_frame' => $this->getPluginFrame($frame_id),
        ]);
    }

    /**
     * フレーム表示設定の保存
     */
    public function saveView($request, $page_id, $frame_id, $bbs_id)
    {
        // 項目のエラーチェック
        $validator = Validator::make($request->all(), [
            'view_format'      => ['nullable', 'numeric'],
            'thread_sort_flag' => ['nullable', 'numeric'],
            'view_count'       => ['nullable', 'numeric'],
            'list_format'      => ['nullable', 'numeric'],
            'thread_format'    => ['nullable', 'numeric'],
            'list_underline'   => ['nullable', 'numeric'],
        ]);
        $validator->setAttributeNames([
            'view_format'      => '表示形式',
            'thread_sort_flag' => '根記事の表示順',
            'view_count'       => '表示件数',
            'list_format'      => '一覧での展開方法',
            'thread_format'    => '詳細でのスレッド記事の展開方法',
            'list_underline'   => 'スレッド記事の下線',
        ]);

        // エラーがあった場合は入力画面に戻る。
        if ($validator->fails()) {
            return back()->withErrors($validator)->withInput();
        }

        // フレームごとの表示設定の更新
        $bbs_frame = BbsFrame::updateOrCreate(
            ['bbs_id' => $bbs_id, 'frame_id' => $frame_id],
            ['view_format'      => $request->view_format,
             'thread_sort_flag' => $request->thread_sort_flag,
             'view_count'       => $request->view_count,
             'list_format'      => $request->list_format,
             'thread_format'    => $request->thread_format,
             'list_underline'   => $request->list_underline,
             'thread_caption'   => $request->thread_caption],
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
            'bbs' => $this->getPluginBucket($bucket_id),
        ]);
    }

    /**
     * バケツ登録処理
     */
    public function saveBuckets($request, $page_id, $frame_id, $bucket_id = null)
    {
        // 項目のエラーチェック
        $validator = Validator::make($request->all(), [
            'name' => ['required'],
        ]);
        $validator->setAttributeNames([
            'name' => '掲示板名',
        ]);

        // エラーがあった場合は入力画面に戻る。
        if ($validator->fails()) {
            return back()->withErrors($validator)->withInput();
        }

        // バケツの取得。なければ登録。
        $bucket = Buckets::updateOrCreate(
            ['id' => $bucket_id],
            ['bucket_name' => $request->name, 'plugin_name' => 'bbses'],
        );

        // フレームにバケツの紐づけ
        $frame = Frame::find($frame_id)->update(['bucket_id' => $bucket->id]);

        // プラグインバケツを取得(なければ新規オブジェクト)
        // プラグインバケツにデータを設定して保存
        $bbs = $this->getPluginBucket($bucket->id);
        $bbs->name = $request->name;
        $bbs->use_like = $request->use_like;
        $bbs->like_button_name = $request->like_button_name;
        $bbs->save();

        // プラグインフレームを作成 or 更新
        $bbs_frame = BbsFrame::updateOrCreate(
            ['frame_id' => $frame_id],
            ['bbs_id' => $bbs->id, 'frame_id' => $frame_id],
        );

        // 登録後はリダイレクトして編集ページを開く。
        return new Collection(['redirect_path' => url('/') . "/plugin/bbses/editBuckets/" . $page_id . "/" . $frame_id . "/" . $bucket->id . "#frame-" . $frame_id]);
    }

    /**
     * 削除処理
     */
    public function destroyBuckets($request, $page_id, $frame_id, $bbs_id)
    {
        // プラグインバケツの取得
        $bbs = Bbs::find($bbs_id);
        if (empty($bbs)) {
            return;
        }

        // POSTデータ削除(一気にDelete なので、deleted_id は入らない)
        BbsPost::where('bbs_id', $bbs->id)->delete();

        // FrameのバケツIDの更新
        Frame::where('id', $frame_id)->update(['bucket_id' => null]);

        // プラグインフレームデータの削除(deleted_id を記録するために1回読んでから削除)
        $bbs_frame = BbsFrame::where('frame_id', $frame_id)->first();
        $bbs_frame->delete();

        // バケツ削除
        Buckets::find($bbs->bucket_id)->delete();

        // プラグインデータ削除
        $bbs->delete();

        return;
    }

    /**
     * データ紐づけ変更関数
     */
    public function changeBuckets($request, $page_id, $frame_id)
    {
        // FrameのバケツIDの更新
        Frame::where('id', $frame_id)->update(['bucket_id' => $request->select_bucket]);

        // Bbses の特定
        $plugin_bucket = $this->getPluginBucket($request->select_bucket);

        // フレームごとの表示設定の更新
        $bbs_frame = $this->getPluginFrame($frame_id);
        $bbs_frame->bbs_id = $plugin_bucket->id;
        $bbs_frame->frame_id = $frame_id;
        $bbs_frame->save();

        return;
    }

    /**
     * いいねをJSON形式で返す
     */
    public function saveLikeJson($request, $page_id, $frame_id, $id = null)
    {
        $bbs = $this->getPluginBucket($this->getBucketId());

        $count = Like::saveLike($this->frame->plugin_name, $bbs->id, $id);
        return $count;
    }
}
