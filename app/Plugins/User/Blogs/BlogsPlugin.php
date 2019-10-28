<?php

namespace App\Plugins\User\Blogs;

use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;

use DB;

use App\Models\Common\Buckets;
use App\Models\Common\Frame;
use App\Models\Common\Page;
use App\Models\User\Blogs\Blogs;
use App\Models\User\Blogs\BlogsPosts;
use App\Models\User\Blogs\BlogsPostsTags;

use App\Plugins\User\UserPluginBase;

/**
 * ブログプラグイン
 *
 * @author 永原　篤 <nagahara@opensource-workshop.jp>
 * @copyright OpenSource-WorkShop Co.,Ltd. All Rights Reserved
 * @category コンテンツプラグイン
 * @package Contoroller
 */
class BlogsPlugin extends UserPluginBase
{

    /* オブジェクト変数 */

    /**
     * 変更時のPOSTデータ
     */
    public $post = null;

    /* コアから呼び出す関数 */

    /**
     *  関数定義（コアから呼び出す）
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
     *  編集画面の最初のタブ（コアから呼び出す）
     *
     *  スーパークラスをオーバーライド
     */
    public function getFirstFrameEditAction()
    {
        return "editBuckets";
    }

    /**
     *  POST取得関数（コアから呼び出す）
     *  コアがPOSTチェックの際に呼び出す関数
     */
    public function getPost($id) {

        // 一度読んでいれば、そのPOSTを再利用する。
        if (!empty($this->post)) {
            return $this->post;
        }

        $this->post = BlogsPosts::where('id', $id)->first();
        return $this->post;
    }

    /* private関数 */

    /**
     *  紐づくブログID とフレームデータの取得
     */
    private function getBlogFrame($frame_id)
    {
        // Frame データ
        $frame = DB::table('frames')
                 ->select('frames.*', 'blogs.id as blogs_id', 'blogs.view_count', 'blogs.approval_flag')
                 ->leftJoin('blogs', 'blogs.bucket_id', '=', 'frames.bucket_id')
                 ->where('frames.id', $frame_id)
                 ->first();
        return $frame;
    }

    /**
     *  ブログ記事チェック設定
     */
    private function makeValidator($request)
    {
        // 項目のエラーチェック
        $validator = Validator::make($request->all(), [
            'post_title' => ['required'],
            'posted_at'  => ['required', 'date_format:Y-m-d H:i:s'],
            'post_text'  => ['required'],
        ]);
        $validator->setAttributeNames([
            'post_title' => 'タイトル',
            'posted_at'  => '投稿日時',
            'post_text'  => '本文',
        ]);
        return $validator;
    }

    /**
     *  記事の取得権限に対する条件追加
     */
    private function appendAuthWhere($query)
    {
        // 記事修正権限、記事管理者の場合、全記事の取得
        if ($this->isCan('role_article') || $this->isCan('role_article_admin')) {
            // 全件取得のため、追加条件なしで戻る。
        }
        // 承認権限の場合、Active ＋ 承認待ちの取得
        elseif ($this->isCan('role_approval')) {
            $query->Where('status',   '=', 0)
                  ->orWhere('status', '=', 2);
        }
        // 記事追加権限の場合、Active ＋ 自分の全ステータス記事の取得
        elseif ($this->isCan('role_reporter')) {
            $query->Where('status', '=', 0)
                  ->orWhere('created_id', '=', Auth::user()->id);
        }
        // その他（ゲスト）
        else {
            $query->where('status', 0);
        }

        return $query;
    }

    /**
     *  ブログ記事一覧取得
     */
    private function getPosts($blog_frame)
    {
        $blogs_posts = null;

        // 削除されていないデータでグルーピングして、最新のIDで全件
        $blogs_posts = BlogsPosts::whereIn('id', function($query) use($blog_frame) {
            $query->select(DB::raw('MAX(id) As id'))
                    ->from('blogs_posts')
                    ->where('blogs_id', $blog_frame->blogs_id)
                    ->where('deleted_at', null)
                    // 権限を見てWhere を付与する。
                    ->where(function($query2){
                        $query2 = $this->appendAuthWhere($query2);
                    })
                    ->groupBy('contents_id');
        })->orderBy('posted_at', 'desc')
          ->paginate($blog_frame->view_count);

        return $blogs_posts;
    }

    /**
     *  ブログ記事一覧取得(全件)
     */
    private function getPostsAll($blog_frame)
    {
        $blogs_posts = null;

        // 記事修正権限、記事管理者の場合、全記事の取得
        if ($this->isCan('role_article') || $this->isCan('role_article_admin')) {

            // 削除されていないデータでグルーピングして、最新のIDで全件
            $blogs_posts = BlogsPosts::whereIn('id', function($query) use($blog_frame) {
                $query->select(DB::raw('MAX(id) As id'))
                        ->from('blogs_posts')
                        ->where('blogs_id', $blog_frame->blogs_id)
                        ->where('deleted_at', null)
                        ->groupBy('contents_id');
            })->orderBy('posted_at', 'desc')
              ->get();
        }
        // 承認権限の場合、Active ＋ 承認待ちの取得
        elseif ($this->isCan('role_approval')) {

            // 削除されていないデータでグルーピングして、最新のIDを取ったのち、アクティブと承認待ち。
            $blogs_posts = BlogsPosts::whereIn('id', function($query) use($blog_frame) {
                $query->select(DB::raw('MAX(id) As id'))
                        ->from('blogs_posts')
                        ->where('blogs_id', $blog_frame->blogs_id)
                        ->where('deleted_at', null)
                        ->groupBy('contents_id');
                })->where(function($query2){
                    $query2->Where('status', '=', 0)
                           ->orWhere('status', '=', 2);
                })->orderBy('posted_at', 'desc')
                  ->get();
        }
        // 記事追加権限の場合、Active ＋ 自分の全ステータス記事の取得
        elseif ($this->isCan('role_reporter')) {

            // 削除されていないデータでグルーピングして、最新のIDを取ったのち、アクティブと自分のデータ。
            $blogs_posts = BlogsPosts::whereIn('id', function($query) use($blog_frame) {
                $query->select(DB::raw('MAX(id) As id'))
                        ->from('blogs_posts')
                        ->where('blogs_id', $blog_frame->blogs_id)
                        ->where('deleted_at', null)
                        ->groupBy('contents_id');
            })->where(function($query2){
                $query2->Where('status', '=', 0)
                       ->orWhere('created_id', '=', Auth::user()->id);
            })->orderBy('posted_at', 'desc')
              ->get();
        }
        // その他（ゲスト）
        else {

            // データ取得
            $blogs_posts = BlogsPosts::whereIn('id', function($query) use($blog_frame) {
                $query->select(DB::raw('MAX(id) As id'))
                        ->from('blogs_posts')
                        ->where('blogs_id', $blog_frame->blogs_id)
                        ->where('deleted_at', null)
                        ->where('status', 0)
                        ->groupBy('contents_id');
            })->orderBy('posted_at', 'desc')
              ->get();
        }

        return $blogs_posts;
    }

    /**
     *  要承認の判断
     */
    private function isApproval($frame_id)
    {
        // 承認の要否確認とステータス処理
        $blog_frame = $this->getBlogFrame($frame_id);
        if ($blog_frame->approval_flag == 1) {

            // 記事修正、記事管理者権限がない場合は要承認
            if (!$this->isCan('role_article') && !$this->isCan('role_article_admin')) {
                return true;
            }
        }
        return false;
    }

    /* 画面アクション関数 */

    /**
     *  データ初期表示関数
     *  コアがページ表示の際に呼び出す関数
     */
    public function index($request, $page_id, $frame_id)
    {
        // ブログ＆フレームデータ
        $blog_frame = $this->getBlogFrame($frame_id);
        if (empty($blog_frame)) {
            return;
        }

        // Page データ
        $page = Page::where('id', $page_id)->first();

        // 認証されているユーザの取得
        $user = Auth::user();

        // ブログデータ一覧の取得
        $blogs_posts = $this->getPosts($blog_frame);

        // タグ：画面表示するデータのblogs_posts_id を集める
        $posts_ids = array();
        foreach($blogs_posts as $blogs_post) {
            $posts_ids[] = $blogs_post->id;
        }

        // タグ：タグデータ取得
        $blogs_posts_tags_row = BlogsPostsTags::whereIn('blogs_posts_id', $posts_ids)->get();

        // タグ：タグデータ詰めなおし（ブログデータの一覧にあてるための外配列）
        $blogs_posts_tags = array();
        foreach($blogs_posts_tags_row as $record) {
            $blogs_posts_tags[$record->blogs_posts_id][] = $record->tags;
        }

        // タグ：タグデータをポストデータに紐づけ
        foreach($blogs_posts as &$blogs_post) {
            if (array_key_exists($blogs_post->id, $blogs_posts_tags)) {
                $blogs_post['tags'] = $blogs_posts_tags[$blogs_post->id];
            }
        }

        // 表示テンプレートを呼び出す。
        return $this->view(
            'blogs', [
            'blogs_posts' => $blogs_posts,
        ]);
    }

    /**
     *  新規記事画面
     */
    public function create($request, $page_id, $frame_id, $blogs_posts_id = null, $errors = null)
    {
        // セッション初期化などのLaravel 処理。
        $request->flash();

        // ブログ＆フレームデータ
        $blog_frame = $this->getBlogFrame($frame_id);

        // 空のデータ(画面で初期値設定で使用するため)
        $blogs_posts = new BlogsPosts();
        $blogs_posts->posted_at = date('Y-m-d H:i:s');

        // タグ
        $blogs_posts_tags = "";

        // 表示テンプレートを呼び出す。(blade でold を使用するため、withInput 使用)
        return $this->view(
            'blogs_input', [
            'blog_frame'       => $blog_frame,
            'blogs_posts'      => $blogs_posts,
            'blogs_posts_tags' => $blogs_posts_tags,
            'errors'           => $errors,
        ])->withInput($request->all);
    }

    /**
     *  詳細表示関数
     */
    public function show($request, $page_id, $frame_id, $blogs_posts_id = null)
    {
        // Frame データ
        $blog_frame = $this->getBlogFrame($frame_id);

        // 記事取得
        $blogs_post = $this->getPost($blogs_posts_id);

        // タグ取得
        // タグ：タグデータ取得
        $blogs_post_tags = BlogsPostsTags::where('blogs_posts_id', $blogs_post->id)->get();

        // ひとつ前、ひとつ後の記事
        //$before_post = BlogsPosts::where('blogs_id', $blogs_post->blogs_id)->where('posted_at', '<', $blogs_post->posted_at)->orderBy('posted_at', 'desc')->first();
        //$after_post = BlogsPosts::where('blogs_id', $blogs_post->blogs_id)->where('posted_at', '>', $blogs_post->posted_at)->orderBy('posted_at', 'asc')->first();

        // ブログデータ一覧の取得
        $blogs_posts = $this->getPostsAll($blog_frame);

        $before_post = null;
        $after_post = null;

        // ひとつ後
        foreach($blogs_posts as $blogs_item) {
            if ($blogs_post->posted_at < $blogs_item->posted_at) {
                $after_post = $blogs_item;
                //break;
            }
        }

        // ひとつ前
        foreach($blogs_posts as $blogs_item) {
            if ($blogs_post->posted_at > $blogs_item->posted_at) {
                $before_post = $blogs_item;
                break;
            }
        }

        // 変更画面を呼び出す。(blade でold を使用するため、withInput 使用)
        return $this->view(
            'blogs_show', [
            'blog_frame'  => $blog_frame,
            'post'        => $blogs_post,
            'post_tags'   => $blogs_post_tags,
            'before_post' => $before_post,
            'after_post'  => $after_post,
        ]);
    }

    /**
     * 記事編集画面
     */
    public function edit($request, $page_id, $frame_id, $blogs_posts_id = null, $errors = null)
    {
        // セッション初期化などのLaravel 処理。
        $request->flash();

        // Frame データ
        $blog_frame = $this->getBlogFrame($frame_id);

        // 記事取得
        //$blogs_post = BlogsPosts::where('id', $blogs_posts_id)->first();
        $blogs_post = $this->getPost($blogs_posts_id);

        // タグ取得
        $blogs_posts_tags_array = BlogsPostsTags::where('blogs_posts_id', $blogs_post->id)->get();
        $blogs_posts_tags = "";
        foreach($blogs_posts_tags_array as $blogs_posts_tags_item) {
            $blogs_posts_tags .= ',' . $blogs_posts_tags_item->tags;
        }
        $blogs_posts_tags = trim($blogs_posts_tags, ',');

        // 変更画面を呼び出す。(blade でold を使用するため、withInput 使用)
        return $this->view(
            'blogs_input', [
            'blog_frame'       => $blog_frame,
            'blogs_posts'      => $blogs_post,
            'blogs_posts_tags' => $blogs_posts_tags,
            'errors'           => $errors,
        ])->withInput($request->all);
    }

    /**
     *  ブログ記事登録処理
     */
    public function save($request, $page_id, $frame_id, $blogs_posts_id = null)
    {
        // 項目のエラーチェック
        $validator = $this->makeValidator($request);

        // エラーがあった場合は入力画面に戻る。
        if ($validator->fails()) {
            return ( $this->create($request, $page_id, $frame_id, $blogs_posts_id, $validator->errors()) );
        }

        // id があれば旧データを取得
        $old_blogs_post = null;
        if (!empty($blogs_posts_id)) {
            $old_blogs_post = BlogsPosts::where('id', $blogs_posts_id)->first();
        }

        // 新規オブジェクト生成
        $blogs_post = new BlogsPosts();

        // ブログ記事設定
        $blogs_post->blogs_id   = $request->blogs_id;
        $blogs_post->post_title = $request->post_title;
        $blogs_post->posted_at  = $request->posted_at;
        $blogs_post->post_text  = $request->post_text;

        // 承認の要否確認とステータス処理
        if ($this->isApproval($frame_id)) {
            $blogs_post->status = 2;
        }

        // 新規
        if (empty($blogs_posts_id)) {

            // 登録ユーザ
            $blogs_post->created_id  = Auth::user()->id;

            // データ保存
            $blogs_post->save();

            // 新規登録の場合、contents_id を最初のレコードのid と同じにする。
            BlogsPosts::where('id', $blogs_post->id)->update(['contents_id' => $blogs_post->id]);
        }
        // 更新
        else {

            // 変更処理の場合、contents_id を旧レコードのcontents_id と同じにする。
            $blogs_post->contents_id = $old_blogs_post->contents_id;

            // 登録ユーザ
            $blogs_post->created_id  = $old_blogs_post->created_id;

            // 旧レコードのstatus 更新(Activeなもの(status:0)は、status:9 に更新。他はそのまま。)ただし、承認待ちレコード作成時は対象外
            if ($blogs_post->status != 2) {
                BlogsPosts::where('contents_id', $old_blogs_post->contents_id)->where('status', 0)->update(['status' => 9]);
            }

            // データ保存
            $blogs_post->save();

        }

        // タグの保存
        if ($request->tags) {
            $tags = explode(',', $request->tags);
            foreach($tags as $tag) {

                // 新規オブジェクト生成
                $blogs_posts_tags = new BlogsPostsTags();

                // タグ登録
                $blogs_posts_tags->blogs_posts_id = $blogs_post->id;
                $blogs_posts_tags->tags           = $tag;
                $blogs_posts_tags->save();
            }

        }

        // 登録後は表示用の初期処理を呼ぶ。
        return $this->index($request, $page_id, $frame_id);
    }

   /**
    * データ一時保存関数
    */
    public function temporarysave($request, $page_id = null, $frame_id = null, $id = null)
    {
        // 項目のエラーチェック
        $validator = $this->makeValidator($request);

        // エラーがあった場合は入力画面に戻る。
        if ($validator->fails()) {
            return ( $this->create($request, $page_id, $frame_id, $id, $validator->errors()) );
        }

        // 新規オブジェクト生成
        if (empty($id)) {
            $blogs_post = new BlogsPosts();

            // 登録ユーザ
            $blogs_post->created_id  = Auth::user()->id;
        }
        else {
            $blogs_post = BlogsPosts::find($id)->replicate();
        }

        // ブログ記事設定
        $blogs_post->status = 1;
        $blogs_post->blogs_id   = $request->blogs_id;
        $blogs_post->post_title = $request->post_title;
        $blogs_post->posted_at  = $request->posted_at;
        $blogs_post->post_text  = $request->post_text;

        $blogs_post->save();

        if (empty($id)) {

            // 新規登録の場合、contents_id を最初のレコードのid と同じにする。
            BlogsPosts::where('id', $blogs_post->id)->update(['contents_id' => $blogs_post->id]);
        }

        // 登録後は表示用の初期処理を呼ぶ。
        return $this->index($request, $page_id, $frame_id);
    }

    /**
     *  削除処理
     */
    public function delete($request, $page_id, $frame_id, $blogs_posts_id)
    {
        // id がある場合、データを削除
        if ( $blogs_posts_id ) {

            // 同じcontents_id のデータを削除するため、一旦、対象データを取得
            $post = BlogsPosts::where('id', $blogs_posts_id)->first();

            // 削除ユーザ、削除日を設定する。（複数レコード更新のため、自動的には入らない）
            BlogsPosts::where('contents_id', $post->contents_id)->update(['deleted_id' => Auth::user()->id, 'deleted_name' => Auth::user()->name]);

            // データを削除する。
            BlogsPosts::where('contents_id', $post->contents_id)->delete();
        }
        // 削除後は表示用の初期処理を呼ぶ。
        return $this->index($request, $page_id, $frame_id);
    }

   /**
    * 承認
    */
    public function approval($request, $page_id = null, $frame_id = null, $id = null)
    {
        // 新規オブジェクト生成
        $blogs_post = BlogsPosts::find($id)->replicate();

        // 旧レコードのstatus 更新(Activeなもの(status:0)は、status:9 に更新。他はそのまま。)
        BlogsPosts::where('contents_id', $blogs_post->contents_id)->where('status', 0)->update(['status' => 9]);

        // ブログ記事設定
        $blogs_post->status = 0;
        $blogs_post->save();

        // 登録後は表示用の初期処理を呼ぶ。
        return $this->index($request, $page_id, $frame_id);
    }

    /**
     * データ選択表示関数
     */
    public function listBuckets($request, $page_id, $frame_id, $id = null)
    {
        // Frame データ
        $blog_frame = DB::table('frames')
                      ->select('frames.*', 'blogs.id as blogs_id', 'blogs.view_count')
                      ->leftJoin('blogs', 'blogs.bucket_id', '=', 'frames.bucket_id')
                      ->where('frames.id', $frame_id)->first();

        // データ取得（1ページの表示件数指定）
        $blogs = Blogs::orderBy('created_at', 'desc')
                       ->paginate(10);

        // 表示テンプレートを呼び出す。
        return $this->view(
            'blogs_list_buckets', [
            'blog_frame' => $blog_frame,
            'blogs'      => $blogs,
        ]);
    }

    /**
     * ブログ新規作成画面
     */
    public function createBuckets($request, $page_id, $frame_id, $blogs_id = null, $create_flag = false, $message = null, $errors = null)
    {
        // 新規作成フラグを付けてブログ設定変更画面を呼ぶ
        $create_flag = true;
        return $this->editBuckets($request, $page_id, $frame_id, $blogs_id, $create_flag, $message, $errors);
    }

    /**
     * ブログ設定変更画面の表示
     */
    public function editBuckets($request, $page_id, $frame_id, $blogs_id = null, $create_flag = false, $message = null, $errors = null)
    {
        // セッション初期化などのLaravel 処理。
        $request->flash();

        // ブログ＆フレームデータ
        $blog_frame = $this->getBlogFrame($frame_id);

        // ブログデータ
        $blog = new Blogs();

        // blogs_id が渡ってくればblogs_id が対象
        if (!empty($blogs_id)) {
            $blog = Blogs::where('id', $blogs_id)->first();
        }
        // Frame のbucket_id があれば、bucket_id からブログデータ取得、なければ、新規作成か選択へ誘導
        else if (!empty($blog_frame->bucket_id) && $create_flag == false) {
            $blog = Blogs::where('bucket_id', $blog_frame->bucket_id)->first();
        }

        // 表示テンプレートを呼び出す。
        return $this->view(
            'blogs_edit_blog', [
            'blog_frame'  => $blog_frame,
            'blog'        => $blog,
            'create_flag' => $create_flag,
            'message'     => $message,
            'errors'      => $errors,
        ])->withInput($request->all);
    }

    /**
     *  ブログ登録処理
     */
    public function saveBuckets($request, $page_id, $frame_id, $blogs_id = null)
    {
        // 項目のエラーチェック
        $validator = Validator::make($request->all(), [
            'blog_name'  => ['required'],
            'view_count' => ['required'],
        ]);
        $validator->setAttributeNames([
            'blog_name'  => 'ブログ名',
            'view_count' => '表示件数',
        ]);

        // エラーがあった場合は入力画面に戻る。
        $message = null;
        if ($validator->fails()) {

            if (empty($blogs_id)) {
                $create_flag = true;
                return $this->createBuckets($request, $page_id, $frame_id, $blogs_id, $create_flag, $message, $validator->errors());
            }
            else  {
                $create_flag = false;
                return $this->editBuckets($request, $page_id, $frame_id, $blogs_id, $create_flag, $message, $validator->errors());
            }
        }

        // 更新後のメッセージ
        $message = null;

        // 画面から渡ってくるblogs_id が空ならバケツとブログを新規登録
        if (empty($request->blogs_id)) {

            // バケツの登録
            $bucket_id = DB::table('buckets')->insertGetId([
                  'bucket_name' => '無題',
                  'plugin_name' => 'blogs'
            ]);

            // ブログデータ新規オブジェクト
            $blogs = new Blogs();
            $blogs->bucket_id = $bucket_id;

            // Frame のBuckets を見て、Buckets が設定されていなければ、作成したものに紐づける。
            // Frame にBuckets が設定されていない ＞ 新規のフレーム＆ブログ作成
            // Frame にBuckets が設定されている ＞ 既存のフレーム＆ブログ更新
            // （表示ブログ選択から遷移してきて、内容だけ更新して、フレームに紐づけないケースもあるため）
            $frame = Frame::where('id', $frame_id)->first();
            if (empty($frame->bucket_id)) {

                // FrameのバケツIDの更新
                $frame = Frame::where('id', $frame_id)->update(['bucket_id' => $bucket_id]);
            }

            $message = 'ブログ設定を追加しました。';
        }
        // blogs_id があれば、ブログを更新
        else {

            // ブログデータ取得
            $blogs = Blogs::where('id', $request->blogs_id)->first();

            $message = 'ブログ設定を変更しました。';
        }

        // ブログ設定
        $blogs->blog_name     = $request->blog_name;
        $blogs->view_count    = $request->view_count;
        $blogs->approval_flag = $request->approval_flag;

        // データ保存
        $blogs->save();

        // 新規作成フラグを付けてブログ設定変更画面を呼ぶ
        $create_flag = false;
        return $this->editBuckets($request, $page_id, $frame_id, $blogs_id, $create_flag, $message);
    }

    /**
     *  削除処理
     */
    public function destroyBuckets($request, $page_id, $frame_id, $blogs_id)
    {
        // blogs_id がある場合、データを削除
        if ( $blogs_id ) {

            // 記事データを削除する。
            BlogsPosts::where('blogs_id', $blogs_id)->delete();

            // ブログ設定を削除する。
            Blogs::destroy($blogs_id);

// Frame に紐づくBlog を削除した場合のみ、Frame の更新。（Frame に紐づかないBlog の削除もあるので、その場合はFrame は更新しない。）
// 実装は後で。

            // バケツIDの取得のためにFrame を取得(Frame を更新する前に取得しておく)
            $frame = Frame::where('id', $frame_id)->first();

            // FrameのバケツIDの更新
            Frame::where('id', $frame_id)->update(['bucket_id' => null]);

            // backetsの削除
            Buckets::where('id', $frame->bucket_id)->delete();
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

        // 表示ブログ選択画面を呼ぶ
        return $this->listBuckets($request, $page_id, $frame_id, $id);
    }
}
