<?php

namespace App\Plugins\User\Blogs;

use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;

use DB;
use Session;

use App\Models\Core\Configs;
use App\Models\Common\Buckets;
use App\Models\Common\Categories;
use App\Models\Common\Frame;
use App\Models\Common\Page;
use App\Models\User\Blogs\Blogs;
use App\Models\User\Blogs\BlogsCategories;
use App\Models\User\Blogs\BlogsFrames;
use App\Models\User\Blogs\BlogsPosts;
use App\Models\User\Blogs\BlogsPostsTags;

use App\Plugins\User\UserPluginBase;

/**
 * ブログプラグイン
 *
 * @author 永原　篤 <nagahara@opensource-workshop.jp>
 * @copyright OpenSource-WorkShop Co.,Ltd. All Rights Reserved
 * @category ブログプラグイン
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
        $functions['get']  = ['listCategories', 'rss', 'editBucketsRoles', 'settingBlogFrame'];
        $functions['post'] = ['saveCategories', 'deleteCategories', 'saveBucketsRoles', 'saveBlogFrame'];
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
    public function getPost($id, $action = null) {

        // deleteCategories の場合は、Blogs_posts のオブジェクトではないので、nullで返す。
        if ($action == 'deleteCategories') {
            return null;
        }

        // 一度読んでいれば、そのPOSTを再利用する。
        if (!empty($this->post)) {
            return $this->post;
        }

        // データのグループ（contents_id）が欲しいため、指定されたID のPOST を読む
        $arg_post = BlogsPosts::where('id', $id)->first();

        // 指定されたPOST ID そのままではなく、権限に応じたPOST を取得する。
        $this->post = BlogsPosts::select('blogs_posts.*',
                                          'categories.color as category_color',
                                          'categories.background_color as category_background_color',
                                          'categories.category as category')
                                ->leftJoin('categories', 'categories.id', '=', 'blogs_posts.categories_id')
                                ->where('contents_id', $arg_post->contents_id)
                                ->where(function($query){
                                      $query = $this->appendAuthWhere($query);
                                })
                                ->orderBy('id', 'desc')
                                ->first();
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
                 ->select('frames.*', 'blogs.id as blogs_id', 'blogs.blog_name', 'blogs.view_count', 'blogs.rss', 'blogs.rss_count', 'blogs_frames.scope', 'blogs_frames.scope_value')
                 ->leftJoin('blogs', 'blogs.bucket_id', '=', 'frames.bucket_id')
                 ->leftJoin('blogs_frames', 'blogs_frames.frames_id', '=', 'frames.id')
                 ->where('frames.id', $frame_id)
                 ->first();
        return $frame;
    }

    /**
     *  カテゴリデータの取得
     */
    private function getBlogsCategories($blogs_id)
    {
        $blogs_categories = Categories::select('categories.*')
                          ->join('blogs_categories', function ($join) use($blogs_id) {
                              $join->on('blogs_categories.categories_id', '=', 'categories.id')
                                   ->where('blogs_categories.blogs_id', '=', $blogs_id)
                                   ->where('blogs_categories.view_flag', 1);
                          })
                          ->whereNull('plugin_id')
                          ->orWhere('plugin_id', $blogs_id)
                          ->orderBy('target', 'asc')
                          ->orderBy('display_sequence', 'asc')
                          ->get();
        return $blogs_categories;
    }

    /**
     *  ブログ記事チェック設定
     */
    private function makeValidator($request)
    {
        // 項目のエラーチェック
        $validator = Validator::make($request->all(), [
            'post_title' => ['required'],
            'posted_at'  => ['required', 'date_format:Y-m-d H:i'],
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
                  ->orWhere('blogs_posts.created_id', '=', Auth::user()->id);
        }
        // その他（ゲスト）
        else {
            $query->where('status', 0);
            $query->where('blogs_posts.posted_at', '<=', Carbon::now());
        }

        return $query;
    }

    /**
     *  表示条件に対する条件追加
     */
    private function appendSettingWhere($query, $blog_frame)
    {
        // 全件表示
        if (empty($blog_frame->scope)) {
            // 全件取得のため、追加条件なしで戻る。
        }
        // 年
        elseif ($blog_frame->scope == 'year') {
            $query->Where('posted_at',   '>=', $blog_frame->scope_value . '-01-01')
                  ->Where('posted_at',   '<=', $blog_frame->scope_value . '-12-31 23:59:59');
        }
        // 年度
        elseif ($blog_frame->scope == 'fiscal') {
            $fiscal_next = intval($blog_frame->scope_value) + 1;
            $query->Where('posted_at',   '>=', $blog_frame->scope_value . '-04-01')
                  ->Where('posted_at',   '<=', $fiscal_next . '-03-31 23:59:59');
        }

        return $query;
    }

    /**
     *  ブログ記事一覧取得
     */
    private function getPosts($blog_frame, $option_count = null)
    {
        //$blogs_posts = null;

        // 件数
        $count = $blog_frame->view_count;
        if ($option_count != null) {
            $count = $option_count;
        }

        // 削除されていないデータでグルーピングして、最新のIDで全件
        $blogs_posts = BlogsPosts::select('blogs_posts.*',
                                          'categories.color as category_color',
                                          'categories.background_color as category_background_color',
                                          'categories.category as category')
                                 ->leftJoin('categories', 'categories.id', '=', 'blogs_posts.categories_id')
                                 ->whereIn('blogs_posts.id', function($query) use($blog_frame) {
                                     $query->select(DB::raw('MAX(id) As id'))
                                           ->from('blogs_posts')
                                           ->where('blogs_id', $blog_frame->blogs_id)

                                           ->where('deleted_at', null)
                                           // 権限を見てWhere を付与する。
                                           ->where(function($query_auth){
                                               $query_auth = $this->appendAuthWhere($query_auth);
                                           })
                                           ->groupBy('contents_id');
                                     })

                                           // 設定を見てWhere を付与する。
                                           ->where(function($query_setting) use($blog_frame) {
                                               $query_setting = $this->appendSettingWhere($query_setting, $blog_frame);
                                           })
                                 ->orderBy('posted_at', 'desc')
                                 ->orderBy('contents_id', 'desc')
                                 ->paginate($count);
        return $blogs_posts;
    }

    /**
     *  要承認の判断
     */
    private function isApproval($frame_id)
    {
        return $this->buckets->needApprovalUser(Auth::user());

//        // 承認の要否確認とステータス処理
//        $blog_frame = $this->getBlogFrame($frame_id);
//        if ($blog_frame->approval_flag == 1) {
//
//            // 記事修正、記事管理者権限がない場合は要承認
//            if (!$this->isCan('role_article') && !$this->isCan('role_article_admin')) {
//                return true;
//            }
//        }
//        return false;
    }

    /**
     *  タグの保存
     */
    private function saveTag($request, $blogs_post)
    {
        // タグの保存
        if ($request->tags) {
            $tags = explode(',', $request->tags);
            foreach($tags as $tag) {

                // 新規オブジェクト生成
                $blogs_posts_tags = new BlogsPostsTags();

                // タグ登録
                $blogs_posts_tags->created_id     = $blogs_post->created_id;
                $blogs_posts_tags->blogs_posts_id = $blogs_post->id;
                $blogs_posts_tags->tags           = $tag;
                $blogs_posts_tags->save();
            }
        }
        return;
    }

    /**
     *  タグのコピー
     */
    private function copyTag($from_post, $to_post)
    {
        // タグの保存
        $blogs_posts_tags = BlogsPostsTags::where('blogs_posts_id', $from_post->id)->orderBy('id', 'asc')->get();
        foreach($blogs_posts_tags as $blogs_posts_tag) {
            $new_tag = $blogs_posts_tag->replicate();
            $new_tag->blogs_posts_id = $to_post->id;
            $new_tag->save();
        }

        return;
    }

    /* スタティック関数 */

    /**
     *  新着情報用メソッド
     */
    public static function getWhatsnewArgs()
    {
        // 戻り値('sql_method'、'link_pattern'、'link_base')

        $return[] = DB::table('blogs_posts')
                      ->select('frames.page_id              as page_id',
                               'frames.id                   as frame_id',
                               'blogs_posts.id              as post_id',
                               'blogs_posts.post_title      as post_title',
                               'blogs_posts.important       as important',
                               'blogs_posts.posted_at       as posted_at',
                               'blogs_posts.created_name    as posted_name',
                               'categories.classname        as classname',
                               'categories.category         as category',
                               DB::raw('"blogs" as plugin_name')
                              )
                      ->join('blogs', 'blogs.id', '=', 'blogs_posts.blogs_id')
                      ->join('frames', 'frames.bucket_id', '=', 'blogs.bucket_id')

                      ->leftJoin('blogs_frames', function ($join) {
                          $join->on('blogs_frames.blogs_id', '=', 'blogs.id')
                          // frames.id がDB::Raw しなければ、バインドの値としてSQL が生成されて、"frames.id" というframes.id は存在せず、left join がレコードが取れなかった。
                          ->where('blogs_frames.frames_id', '=', DB::Raw("frames.id"));
                      })
                      ->leftJoin('categories', 'categories.id', '=', 'blogs_posts.categories_id')
                      ->where('status', 0)
                      ->where('posted_at', '<=', Carbon::now())
                      ->where('disable_whatsnews', 0)

/* if で書いたもの。CASE を疑っていた際のテスト用
                      ->whereRaw('(
                                  (blogs_frames.scope IS NULL) OR
                                  (blogs_frames.scope = "year" AND blogs_frames.scope_value IS NOT NULL AND 
                                      posted_at >= CONCAT(blogs_frames.scope_value, "-01-01") AND posted_at <= CONCAT(blogs_frames.scope_value, "-12-31 23:59:59")) OR
                                  (blogs_frames.scope = "fiscal" AND blogs_frames.scope_value IS NOT NULL AND 
                                      posted_at >= CONCAT(blogs_frames.scope_value, "-04-01") AND posted_at <= CONCAT((blogs_frames.scope_value + 1), "-03-31 23:59:59"))
                                  )')
*/

                      ->whereRaw('CASE
                                  WHEN blogs_frames.scope IS NULL
                                      THEN blogs_frames.scope IS NULL
                                  WHEN blogs_frames.scope = "year" AND blogs_frames.scope_value IS NOT NULL
                                      THEN posted_at >= CONCAT(blogs_frames.scope_value, "-01-01") AND posted_at <= CONCAT(blogs_frames.scope_value, "-12-31 23:59:59")
                                  WHEN blogs_frames.scope = "fiscal" AND blogs_frames.scope_value IS NOT NULL
                                      THEN posted_at >= CONCAT(blogs_frames.scope_value, "-04-01") AND posted_at <= CONCAT((blogs_frames.scope_value + 1), "-03-31 23:59:59")
                                  END')
                      ->whereNull('blogs_posts.deleted_at');
/*
SELECT blogs_frames.scope, blogs_frames.scope_value, blogs_posts.*
FROM blogs_posts
    INNER JOIN blogs ON blogs.id = blogs_posts.blogs_id
    INNER JOIN frames ON frames.bucket_id = blogs.bucket_id
    LEFT JOIN blogs_frames ON blogs_frames.frames_id AND blogs_frames.blogs_id = blogs.id
WHERE status = 0
    AND disable_whatsnews = 0
    AND
        CASE
        WHEN blogs_frames.scope IS NULL
            THEN blogs_frames.scope IS NULL
        WHEN blogs_frames.scope = 'year' AND blogs_frames.scope_value IS NOT NULL
            THEN posted_at >= CONCAT(blogs_frames.scope_value, '-01-01') AND posted_at <= CONCAT(blogs_frames.scope_value, '-12-31 23:59:59')
        WHEN blogs_frames.scope = 'fiscal' AND blogs_frames.scope_value IS NOT NULL
            THEN posted_at >= CONCAT(blogs_frames.scope_value, '-04-01') AND posted_at <= CONCAT((blogs_frames.scope_value + 1), '-03-31 23:59:59')
        END
*/
        $return[] = 'show_page_frame_post';
        $return[] = '/plugin/blogs/show';

        return $return;
    }

    /**
     *  検索用メソッド
     */
    public static function getSearchArgs($search_keyword, $page_ids = null)
    {
        $return[] = DB::table('blogs_posts')
                      ->select('blogs_posts.id              as post_id',
                               'frames.id                   as frame_id',
                               'frames.page_id              as page_id',
                               'pages.permanent_link        as permanent_link',
                               'blogs_posts.post_title      as post_title',
                               'blogs_posts.important       as important',
                               'blogs_posts.posted_at       as posted_at',
                               'blogs_posts.created_name    as posted_name',
                               'categories.classname        as classname',
                               'blogs_posts.categories_id   as categories_id',
                               'categories.category         as category',
                               DB::raw('"blogs" as plugin_name')
                              )
                      ->join('blogs', 'blogs.id', '=', 'blogs_posts.blogs_id')
                      ->join('frames', 'frames.bucket_id', '=', 'blogs.bucket_id')
                      ->leftJoin('categories', 'categories.id', '=', 'blogs_posts.categories_id')
                      ->join('pages', 'pages.id', '=', 'frames.page_id')
                      ->whereIn('pages.id', $page_ids)
                      ->where('status', '?')
                      ->where(function($plugin_query) use($search_keyword) {
                          $plugin_query->where('blogs_posts.post_title', 'like', '?')
                                       ->orWhere('blogs_posts.post_text', 'like', '?');
                      })
                      ->whereNull('blogs_posts.deleted_at');


        $bind = array($page_ids, 0, '%'.$search_keyword.'%', '%'.$search_keyword.'%');
        $return[] = $bind;
        $return[] = 'show_page_frame_post';
        $return[] = '/plugin/blogs/show';

        return $return;
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
                $blogs_post->tags = $blogs_posts_tags[$blogs_post->id];
            }
        }

        // 表示テンプレートを呼び出す。
        return $this->view(
            'blogs', [
            'blogs_posts' => $blogs_posts,
            'blog_frame'  => $blog_frame,
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
        $blogs_posts->posted_at = date('Y-m-d H:i:00');

        // カテゴリ
        $blogs_categories = $this->getBlogsCategories($blog_frame->blogs_id);

        // タグ
        $blogs_posts_tags = "";

        // 表示テンプレートを呼び出す。(blade でold を使用するため、withInput 使用)
        return $this->view(
            'blogs_input', [
            'blog_frame'       => $blog_frame,
            'blogs_posts'      => $blogs_posts,
            'blogs_categories' => $blogs_categories,
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

        // 記事取得（指定されたPOST ID そのままではなく、権限に応じたPOST を取得する。）
        $blogs_post = $this->getPost($blogs_posts_id);
        if (empty($blogs_post)) {
            return $this->view_error("403_inframe", null, 'showのユーザー権限に応じたPOST ID チェック');
        }

        // タグ取得
        // タグ：タグデータ取得
        $blogs_post_tags = new BlogsPostsTags();
        if ($blogs_post) {
            $blogs_post_tags = BlogsPostsTags::where('blogs_posts_id', $blogs_post->id)->get();
        }

        // ひとつ前、ひとつ後の記事
        $before_post = null;
        $after_post = null;
        if ($blogs_post) {

            // 1件前
            $before_post = BlogsPosts::whereIn('id', function($query1) use($blogs_post) {
                                           // 権限の条件で絞って、contents_id でグループ化した最後のid（権限を加味した記事のID 一覧）
                                           $query1->select(DB::raw('MAX(id) as id'))
                                                  ->from('blogs_posts')
                                                  ->where('blogs_id', $blogs_post->blogs_id)
                                                  ->where(function($query2){
                                                      $query2 = $this->appendAuthWhere($query2);
                                                  })
                                                  ->groupBy('contents_id');
                                           })
                                       // 同じ日付の記事があるので、(日付が小さい OR (日付が同じ＆contents_id が小さい)で1件目)
                                       // 一覧は 日付(desc), contents_id(desc) で表示するため
                                       ->where(function($query3) use($blogs_post) {
                                           $query3->where('posted_at', '<', $blogs_post->posted_at)
                                                  ->orWhere(function($query4) use($blogs_post) {
                                                      $query4->where('posted_at', '=', $blogs_post->posted_at)
                                                             ->where('contents_id', '<', $blogs_post->contents_id);
                                                  });
                                       })
                                       ->orderBy('posted_at', 'desc')
                                       ->orderBy('contents_id', 'desc')
                                       ->first();

            // 1件後
            $after_post = BlogsPosts::whereIn('id', function($query1) use($blogs_post) {
                                           // 権限の条件で絞って、contents_id でグループ化した最後のid（権限を加味した記事のID 一覧）
                                           $query1->select(DB::raw('MAX(id) as id'))
                                                  ->from('blogs_posts')
                                                  ->where('blogs_id', $blogs_post->blogs_id)
                                                  ->where(function($query2){
                                                      $query2 = $this->appendAuthWhere($query2);
                                                  })
                                                  ->groupBy('contents_id');
                                           })
                                       // 同じ日付の記事があるので、(日付が小さい OR (日付が同じ＆contents_id が大きい)で1件目)
                                       // 一覧は 日付(desc), contents_id(desc) で表示するため
                                       ->where(function($query3) use($blogs_post) {
                                           $query3->where('posted_at', '>', $blogs_post->posted_at)
                                                  ->orWhere(function($query4) use($blogs_post) {
                                                      $query4->where('posted_at', '=', $blogs_post->posted_at)
                                                             ->where('contents_id', '>', $blogs_post->contents_id);
                                                  });
                                       })
                                       ->orderBy('posted_at', 'asc')
                                       ->orderBy('contents_id', 'asc')
                                       ->first();
        }

        // 詳細画面を呼び出す。
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

        // 記事取得（指定されたPOST ID そのままではなく、権限に応じたPOST を取得する。）
        $blogs_post = $this->getPost($blogs_posts_id);
        if (empty($blogs_post)) {
            return $this->view_error("403_inframe", null, 'editのユーザー権限に応じたPOST ID チェック');
        }

        // カテゴリ
        $blogs_categories = $this->getBlogsCategories($blog_frame->blogs_id);

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
            'blogs_categories' => $blogs_categories,
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

        // id があれば旧データを取得＆権限を加味して更新可能データかどうかのチェック
        $old_blogs_post = null;
        if (!empty($blogs_posts_id)) {

            // 指定されたID のデータ
            $old_blogs_post = BlogsPosts::where('id', $blogs_posts_id)->first();

            // 記事取得（指定されたPOST ID そのままではなく、権限に応じたPOST を取得する。）
            $check_blogs_post = $this->getPost($blogs_posts_id);

            // 指定されたID と権限に応じたPOST のID が異なる場合は、キーを捏造したPOST と考えられるため、エラー
            if (empty($check_blogs_post) || $check_blogs_post->id != $old_blogs_post->id) {
                return $this->view_error("403_inframe", null, 'saveのユーザー権限に応じたPOST ID チェック');
            }
        }

        // 新規オブジェクト生成
        $blogs_post = new BlogsPosts();

        // ブログ記事設定
        $blogs_post->blogs_id      = $request->blogs_id;
        $blogs_post->post_title    = $request->post_title;
        $blogs_post->categories_id = $request->categories_id;
        $blogs_post->important     = $request->important;
        $blogs_post->posted_at     = $request->posted_at . ':00';
        $blogs_post->post_text     = $request->post_text;

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
        $this->saveTag($request, $blogs_post);

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
 
            // チェック用に記事取得（指定されたPOST ID そのままではなく、権限に応じたPOST を取得する。）
            $check_blogs_post = $this->getPost($id);

            // 指定されたID と権限に応じたPOST のID が異なる場合は、キーを捏造したPOST と考えられるため、エラー
            if (empty($check_blogs_post) || $check_blogs_post->id != $id) {
                return $this->view_error("403_inframe", null, 'temporarysaveのユーザー権限に応じたPOST ID チェック');
            }
       }

        // ブログ記事設定
        $blogs_post->status = 1;
        $blogs_post->blogs_id   = $request->blogs_id;
        $blogs_post->post_title = $request->post_title;
        $blogs_post->important  = $request->important;
        $blogs_post->posted_at  = $request->posted_at . ':00';
        $blogs_post->post_text  = $request->post_text;

        $blogs_post->save();

        if (empty($id)) {

            // 新規登録の場合、contents_id を最初のレコードのid と同じにする。
            BlogsPosts::where('id', $blogs_post->id)->update(['contents_id' => $blogs_post->id]);
        }

        // タグの保存
        $this->saveTag($request, $blogs_post);

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

        // チェック用に記事取得（指定されたPOST ID そのままではなく、権限に応じたPOST を取得する。）
        $check_blogs_post = $this->getPost($id);

        // 指定されたID と権限に応じたPOST のID が異なる場合は、キーを捏造したPOST と考えられるため、エラー
        if (empty($check_blogs_post) || $check_blogs_post->id != $id) {
            return $this->view_error("403_inframe", null, 'approvalのユーザー権限に応じたPOST ID チェック');
        }

        // 旧レコードのstatus 更新(Activeなもの(status:0)は、status:9 に更新。他はそのまま。)
        BlogsPosts::where('contents_id', $blogs_post->contents_id)->where('status', 0)->update(['status' => 9]);

        // ブログ記事設定
        $blogs_post->status = 0;
        $blogs_post->save();

        // タグもコピー
        $this->copyTag($check_blogs_post, $blogs_post);

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
        // デフォルトでチェック
        $validator_values['blog_name'] = ['required'];
        $validator_values['view_count'] = ['required', 'numeric'];
        $validator_values['rss_count'] = ['required', 'numeric'];

        $validator_attributes['blog_name'] = 'ブログ名';
        $validator_attributes['view_count'] = '表示件数';
        $validator_attributes['rss_count'] = 'RSS件数';

        // 項目のエラーチェック
        $validator = Validator::make($request->all(), $validator_values);
        $validator->setAttributeNames($validator_attributes);

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
                  'bucket_name' => $request->blog_name,
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
        $blogs->rss           = $request->rss;
        $blogs->rss_count     = $request->rss_count;
        //$blogs->approval_flag = $request->approval_flag;

        // データ保存
        $blogs->save();

        // ブログ名で、Buckets名も更新する
        Buckets::where('id', $blogs->bucket_id)->update(['bucket_name' => $request->blog_name]);

        // ブログ名で、Buckets名も更新する
        //Log::debug($blogs->bucket_id);
        //Log::debug($request->blog_name);

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

    /**
     * カテゴリ表示関数
     */
    public function listCategories($request, $page_id, $frame_id, $id = null, $errors = null, $create_flag = false)
    {
        // セッション初期化などのLaravel 処理。
        $request->flash();

        // 権限チェック（listCategories 関数は標準チェックにないので、独自チェック）
        if ($this->can('role_arrangement')) {
            return $this->view_error("403_inframe", null, '関数実行権限がありません。');
        }

        // ブログ
        $blog_frame = $this->getBlogFrame($frame_id);

        // カテゴリ（全体）
        $general_categories = Categories::select('categories.*', 'blogs_categories.id as blogs_categories_id', 'blogs_categories.categories_id', 'blogs_categories.view_flag')
                                        ->leftJoin('blogs_categories', function ($join) use($blog_frame) {
                                            $join->on('blogs_categories.categories_id', '=', 'categories.id')
                                                 ->where('blogs_categories.blogs_id', '=', $blog_frame->blogs_id);
                                        })
                                        ->where('target', null)
                                        ->orderBy('display_sequence', 'asc')
                                        ->get();
        // カテゴリ（このブログ）
        $plugin_categories = null;
        if ($blog_frame->blogs_id) {
            $plugin_categories = Categories::select('categories.*', 'blogs_categories.id as blogs_categories_id', 'blogs_categories.categories_id', 'blogs_categories.view_flag')
                                           ->leftJoin('blogs_categories', 'blogs_categories.categories_id', '=', 'categories.id')
                                           ->where('target', 'blogs')
                                           ->where('plugin_id', $blog_frame->blogs_id)
                                           ->orderBy('display_sequence', 'asc')
                                           ->get();
        }

        // 表示テンプレートを呼び出す。
        return $this->view(
            'blogs_list_categories', [
            'general_categories' => $general_categories,
            'plugin_categories'  => $plugin_categories,
            'blog_frame'         => $blog_frame,
            'errors'             => $errors,
            'create_flag'        => $create_flag,
        ])->withInput($request->all);
    }

    /**
     *  カテゴリ登録処理
     */
    public function saveCategories($request, $page_id, $frame_id, $id = null)
    {
        // 権限チェック（saveCategories 関数は標準チェックにないので、独自チェック）
        if ($this->can('role_arrangement')) {
            return $this->view_error("403_inframe", null, '関数実行権限がありません。');
        }

        /* エラーチェック
        ------------------------------------ */

        // 追加項目のどれかに値が入っていたら、行の他の項目も必須
        if (!empty($request->add_display_sequence) || !empty($request->add_category) || !empty($request->add_color)) {

            // 項目のエラーチェック
            $validator = Validator::make($request->all(), [
                'add_display_sequence' => ['required'],
                'add_category'         => ['required'],
                'add_color'            => ['required'],
                'add_background_color' => ['required'],
            ]);
            $validator->setAttributeNames([
                'add_display_sequence' => '追加行の表示順',
                'add_category'         => '追加行のカテゴリ',
                'add_color'            => '追加行の文字色',
                'add_background_color' => '追加行の背景色',
            ]);

            if ($validator->fails()) {
                return $this->listCategories($request, $page_id, $frame_id, $id, $validator->errors());
            }
        }

        // 既存項目のidに値が入っていたら、行の他の項目も必須
        if (!empty($request->blogs_categories_id)) {
            foreach($request->blogs_categories_id as $category_id) {

                // 項目のエラーチェック
                $validator = Validator::make($request->all(), [
                    'plugin_display_sequence.'.$category_id => ['required'],
                    'plugin_category.'.$category_id         => ['required'],
                    'plugin_color.'.$category_id            => ['required'],
                    'plugin_background_color.'.$category_id => ['required'],
                ]);
                $validator->setAttributeNames([
                    'plugin_display_sequence.'.$category_id => '表示順',
                    'plugin_category.'.$category_id         => 'カテゴリ',
                    'plugin_color.'.$category_id            => '文字色',
                    'plugin_background_color.'.$category_id => '背景色',
                ]);

                if ($validator->fails()) {
                    return $this->listCategories($request, $page_id, $frame_id, $id, $validator->errors());
                }
            }
        }

        /* カテゴリ追加
        ------------------------------------ */

        // ブログ
        $blog_frame = $this->getBlogFrame($frame_id);

        // 追加項目アリ
        if (!empty($request->add_display_sequence)) {
            $add_category = Categories::create([
                                'classname'        => $request->add_classname,
                                'category'         => $request->add_category,
                                'color'            => $request->add_color,
                                'background_color' => $request->add_background_color,
                                'target'           => 'blogs',
                                'plugin_id'        => $blog_frame->blogs_id,
                                'display_sequence' => intval($request->add_display_sequence),
                             ]);
            BlogsCategories::create([
                                'blogs_id'         => $blog_frame->blogs_id,
                                'categories_id'    => $add_category->id,
                                'view_flag'        => (isset($request->add_view_flag) && $request->add_view_flag == '1') ? 1 : 0,
                                'display_sequence' => intval($request->add_display_sequence),
                             ]);
        }

        // 既存項目アリ
        if (!empty($request->plugin_categories_id)) {

            foreach($request->plugin_categories_id as $plugin_categories_id) {

                // モデルオブジェクト取得
                $category = Categories::where('id', $plugin_categories_id)->first();

                // データのセット
                $category->classname        = $request->plugin_classname[$plugin_categories_id];
                $category->category         = $request->plugin_category[$plugin_categories_id];
                $category->color            = $request->plugin_color[$plugin_categories_id];
                $category->background_color = $request->plugin_background_color[$plugin_categories_id];
                $category->target           = 'blogs';
                $category->plugin_id        = $blog_frame->blogs_id;
                $category->display_sequence = $request->plugin_display_sequence[$plugin_categories_id];

                // 保存
                $category->save();
            }
        }

        /* 表示フラグ更新(共通カテゴリ)
        ------------------------------------ */
        if (!empty($request->general_categories_id)) {
            foreach($request->general_categories_id as $general_categories_id) {

                // ブログプラグインのカテゴリー使用テーブルになければ追加、あれば更新
                BlogsCategories::updateOrCreate(
                    ['categories_id' => $general_categories_id, 'blogs_id' => $blog_frame->blogs_id],
                    [
                     'blogs_id' => $blog_frame->blogs_id,
                     'categories_id' => $general_categories_id,
                     'view_flag' => (isset($request->general_view_flag[$general_categories_id]) && $request->general_view_flag[$general_categories_id] == '1') ? 1 : 0,
                     'display_sequence' => $request->general_display_sequence[$general_categories_id],
                    ]
                );
            }
        }

        /* 表示フラグ更新(自ブログのカテゴリ)
        ------------------------------------ */
        if (!empty($request->plugin_categories_id)) {
            foreach($request->plugin_categories_id as $plugin_categories_id) {

                // ブログプラグインのカテゴリー使用テーブルになければ追加、あれば更新
                BlogsCategories::updateOrCreate(
                    ['categories_id' => $plugin_categories_id, 'blogs_id' => $blog_frame->blogs_id],
                    [
                     'blogs_id' => $blog_frame->blogs_id,
                     'categories_id' => $plugin_categories_id,
                     'view_flag' => (isset($request->plugin_view_flag[$plugin_categories_id]) && $request->plugin_view_flag[$plugin_categories_id] == '1') ? 1 : 0,
                     'display_sequence' => $request->plugin_display_sequence[$plugin_categories_id],
                    ]
                );
            }
        }

        return $this->listCategories($request, $page_id, $frame_id, $id, null, true);
    }

    /**
     *  カテゴリ削除処理
     */
    public function deleteCategories($request, $page_id, $frame_id, $id = null)
    {
        // 権限チェック（deleteCategories 関数は標準チェックにないので、独自チェック）
        if ($this->can('role_arrangement')) {
            return $this->view_error("403_inframe", null, '関数実行権限がありません。');
        }

        // 削除(ブログプラグインのカテゴリ表示データ)
        BlogsCategories::where('categories_id', $id)->delete();

        // 削除(カテゴリ)
        Categories::where('id', $id)->delete();

        return $this->listCategories($request, $page_id, $frame_id, $id, null, true);
    }

    /**
     *  RSS配信
     */
    public function rss($request, $page_id, $frame_id, $id = null)
    {
        // ブログ＆フレームデータ
        $blog_frame = $this->getBlogFrame($frame_id);
        if (empty($blog_frame)) {
            return;
        }

        // サイト名
        $base_site_name = Configs::where('name', 'base_site_name')->first();

        // URL
        $url = url("/redirect/plugin/blogs/rss/" . $page_id . "/" . $frame_id);

        // HTTPヘッダー出力
        header('Content-Type: text/xml; charset=UTF-8');

echo <<<EOD
<rss xmlns:content="http://purl.org/rss/1.0/modules/content/" version="2.0">
<channel>
<title>[{$base_site_name->value}]{$blog_frame->blog_name}</title>
<description></description>
<link>
{$url}
</link>
EOD;

        $blogs_posts = $this->getPosts($blog_frame, $blog_frame->rss_count);
        foreach ($blogs_posts as $blogs_post) {

            $title = $blogs_post->post_title;
            $link = url("/plugin/blogs/show/" . $page_id . "/" . $frame_id . "/" . $blogs_post->id);
            if (mb_strlen(strip_tags($blogs_post->post_text)) > 100) {
                $description = mb_substr(strip_tags($blogs_post->post_text), 0, 100) . "...";
                $replaceTarget = array('<br>', '&nbsp;', '&emsp;', '&ensp;');
                $description = str_replace($replaceTarget, '', $description);
            }
            else {
                $description = strip_tags($blogs_post->post_text);
                $replaceTarget = array('<br>', '&nbsp;', '&emsp;', '&ensp;');
                $description = str_replace($replaceTarget, '', $description);
            }
            $pub_date = date(DATE_RSS, strtotime($blogs_post->posted_at));
            $content = strip_tags(html_entity_decode($blogs_post->post_text));
echo <<<EOD

<item>
<title>{$title}</title>
<link>{$link}</link>
<description>{$description}</description>
<pubDate>{$pub_date}</pubDate>
<content:encoded>{$content}</content:encoded>
</item>
EOD;
        }

/*
<title>{$title}</title>
<link>{$link}</link>
<description>{$description}</description>
<pubDate>{$pub_date}</pubDate>
<content:encoded>{$content}</content:encoded>
*/
//echo $rss_text;

echo <<<EOD
</channel>
</rss>
EOD;

exit;
    }

    /**
     *  Blogフレーム設定表示画面
     */
    public function settingBlogFrame($request, $page_id, $frame_id)
    {
        // セッション初期化などのLaravel 処理。
        $request->flash();

        // Blog設定取得
        $blog_frame = $this->getBlogFrame($frame_id);
        if (empty($blog_frame)) {
            return;
        }

        // Blogフレーム設定
        $blog_frame_setting = BlogsFrames::where('frames_id', $frame_id)->first();
        if (empty($blog_frame_setting)) {
            $blog_frame_setting = new BlogsFrames();
        }

        // Blogフレーム設定画面を呼び出す。
        return $this->view(
            'blogs_setting_frame', [
            'blog_frame'         => $blog_frame,
            'blog_frame_setting' => $blog_frame_setting,
        ]);
    }

    /**
     *  Blogフレーム設定保存処理
     */
    public function saveBlogFrame($request, $page_id, $frame_id)
    {
        // Blog設定取得
        $blog_frame = $this->getBlogFrame($frame_id);

        // 項目のエラーチェック
        $validator_values['scope_value'] = ['nullable', 'digits:4'];
        if($request->scope == 'year' || $request->scope == 'fiscal'){
            $validator_values['scope_value'][] = ['required'];
        }
        $validator_attributes['scope_value'] = '指定年';

        $validator = Validator::make($request->all(), $validator_values);
        $validator->setAttributeNames($validator_attributes);

        // エラーがあった場合は入力画面に戻る。
        if ($validator->fails()) {
            Session::flash('flash_errors', $validator->errors());
            return $this->settingBlogFrame($request, $page_id, $frame_id);
        }

        // プラグインのフレームやBlogのID が設定されていない場合は空振りさせる。
        if (empty($blog_frame) || empty($blog_frame->blogs_id)) {
            return;
        }

        BlogsFrames::updateOrCreate(
            ['frames_id' => $frame_id],
            ['blogs_id' => $blog_frame->blogs_id, 'frames_id' => $frame_id, 'scope' => $request->scope, 'scope_value' => $request->scope_value ]
        );

        return $this->settingBlogFrame($request, $page_id, $frame_id);
    }
}
