<?php

namespace App\Plugins\User\Blogs;

use Carbon\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\DB;

use App\Models\Core\Configs;
use App\Models\Core\FrameConfig;
use App\Models\Common\Buckets;
use App\Models\Common\BucketsRoles;
use App\Models\Common\Categories;
use App\Models\Common\Frame;
use App\Models\Common\Like;
use App\Models\User\Blogs\Blogs;
use App\Models\User\Blogs\BlogsFrames;
use App\Models\User\Blogs\BlogsPosts;
use App\Models\User\Blogs\BlogsPostsTags;

use App\Plugins\User\UserPluginBase;

use App\Enums\StatusType;
use App\Enums\BlogFrameConfig;

// use App\Rules\CustomValiWysiwygMax;

/**
 * ブログプラグイン
 *
 * @author 永原　篤 <nagahara@opensource-workshop.jp>
 * @author 牟田口 満 <mutaguchi@opensource-workshop.jp>
 * @copyright OpenSource-WorkShop Co.,Ltd. All Rights Reserved
 * @category ブログプラグイン
 * @package Controller
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
        $functions['get']  = ['settingBlogFrame', 'saveLikeJson'];
        $functions['post'] = ['saveBlogFrame'];
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
        // 権限チェックテーブル
        // [TODO] 【各プラグイン】declareRoleファンクションで適切な追加の権限定義を設定する https://github.com/opensource-workshop/connect-cms/issues/658
        $role_ckeck_table = array();
        return $role_ckeck_table;
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
    public function getPost($id, $action = null)
    {
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

        // 指定されたPOST がない場合は、不正な処理として空で返す。
        if (empty($arg_post)) {
            return null;
        }

        // 指定されたPOST ID そのままではなく、権限に応じたPOST を取得する。
        // $this->post = BlogsPosts::
        $blogs_query = BlogsPosts::
            select(
                'blogs_posts.*',
                'categories.color as category_color',
                'categories.background_color as category_background_color',
                'categories.category as category',
                'plugin_categories.view_flag as category_view_flag',
                'likes.id as like_id',
                'likes.count as like_count',
                'like_users.id as like_users_id'    // idあればいいね済み
            )
            // ->leftJoin('categories', function ($join) {
            //     $join->on('categories.id', '=', 'blogs_posts.categories_id')
            //         ->whereNull('categories.deleted_at');
            // })
            // ->leftJoin('plugin_categories', function ($join) use ($plugin_name) {
            //     $join->on('plugin_categories.categories_id', '=', 'categories.id')
            //         ->where('plugin_categories.target', $plugin_name)
            //         ->whereColumn('plugin_categories.target_id', 'blogs_posts.blogs_id')
            //         ->where('plugin_categories.view_flag', 1)   // 表示するカテゴリのみ
            //         ->whereNull('plugin_categories.deleted_at');
            // })
            ->where('contents_id', $arg_post->contents_id)
            ->where(function ($query) {
                $query = $this->appendAuthWhere($query);
            });
            // ->orderBy('id', 'desc')
            // ->first();

        // カテゴリのleftJoin
        $blogs_query =  Categories::appendCategoriesLeftJoin($blogs_query, $this->frame->plugin_name, 'blogs_posts.categories_id', 'blogs_posts.blogs_id');

        // いいねのleftJoin
        $blogs_query = Like::appendLikeLeftJoin($blogs_query, $this->frame->plugin_name, 'blogs_posts.contents_id', 'blogs_posts.blogs_id');

        $this->post = $blogs_query->orderBy('id', 'desc')     // 履歴最新を取得するために、idをdesc指定
            ->first();

        // 続きを読むボタン名・続きを閉じるボタン名が空なら、初期値セットする
        if (empty($this->post->read_more_button)) {
            $this->post->read_more_button = BlogsPosts::read_more_button_default;
        }
        if (empty($this->post->close_more_button)) {
            $this->post->close_more_button = BlogsPosts::close_more_button_default;
        }

        return $this->post;
    }

    /* private関数 */

    /**
     *  紐づくブログID とフレームデータの取得
     */
    private function getBlogFrame($frame_id)
    {
        // Frame データ
        $frame = Frame::
            select(
                'frames.*',
                'blogs.id as blogs_id',
                'blogs.blog_name',
                'blogs.view_count',
                'blogs.rss',
                'blogs.rss_count',
                'blogs.use_like',
                'blogs.like_button_name',
                'blogs_frames.scope',
                'blogs_frames.scope_value',
                'blogs_frames.important_view',
            )
            ->leftJoin('blogs', 'blogs.bucket_id', '=', 'frames.bucket_id')
            ->leftJoin('blogs_frames', 'blogs_frames.frames_id', '=', 'frames.id')
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
            'post_title' => ['required', 'max:255'],
            'posted_at'  => ['required', 'date_format:Y-m-d H:i'],
            'post_text'  => ['required'],
            // 'post_text'  => ['required', new CustomValiWysiwygMax()],
            // 'post_text2'  => ['nullable', new CustomValiWysiwygMax()],
            'read_more_button' => ['nullable', 'max:255'],
            'close_more_button' => ['nullable', 'max:255'],
            'tags' => ['nullable', 'max:255'],
        ]);
        $validator->setAttributeNames([
            'post_title' => 'タイトル',
            'posted_at'  => '投稿日時',
            'post_text'  => '本文',
            'post_text2'  => '続き本文',
            'read_more_button'  => '続きを読むボタン名',
            'close_more_button'  => '続きを閉じるボタン名',
            'tags'  => 'タグ',
        ]);
        return $validator;
    }

    /**
     *  記事の取得権限に対する条件追加
     */
    private function appendAuthWhere($query)
    {
        // 記事修正権限、コンテンツ管理者の場合、全記事の取得
        if ($this->isCan('role_article') || $this->isCan('role_article_admin')) {
            // 全件取得のため、追加条件なしで戻る。
        } elseif ($this->isCan('role_approval')) {
            // 承認権限の場合、Active ＋ 承認待ちの取得
            $query->Where('status', '=', 0)
                  ->orWhere('status', '=', 2);
        } elseif ($this->isCan('role_reporter')) {
            // 編集者権限の場合、Active ＋ 自分の全ステータス記事の取得
            $query->Where('status', '=', 0)
                  ->orWhere('blogs_posts.created_id', '=', Auth::user()->id);
        } else {
            // その他（ゲスト）
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
        } elseif ($blog_frame->scope == 'year') {
            // 年
            $query->Where('posted_at', '>=', $blog_frame->scope_value . '-01-01')
                  ->Where('posted_at', '<=', $blog_frame->scope_value . '-12-31 23:59:59');
        } elseif ($blog_frame->scope == 'fiscal') {
            // 年度
            $fiscal_next = intval($blog_frame->scope_value) + 1;
            $query->Where('posted_at', '>=', $blog_frame->scope_value . '-04-01')
                  ->Where('posted_at', '<=', $fiscal_next . '-03-31 23:59:59');
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
        if ($count < 0) {
            $count = 0;
        }

        $plugin_name = $this->frame->plugin_name;

        // 削除されていないデータでグルーピングして、最新のIDで全件
        $blogs_query = BlogsPosts::
            select(
                'blogs_posts.*',
                'categories.color as category_color',
                'categories.background_color as category_background_color',
                'categories.category as category',
                'plugin_categories.view_flag as category_view_flag',
                'likes.id as like_id',
                'likes.count as like_count',
                'like_users.id as like_users_id'    // idあればいいね済み
            )
            ->whereIn('blogs_posts.id', function ($query) use ($blog_frame) {
                $query->select(DB::raw('MAX(id) As id'))
                    ->from('blogs_posts')
                    ->where('blogs_id', $blog_frame->blogs_id)

                    ->where('deleted_at', null)
                    // 権限を見てWhere を付与する。
                    ->where(function ($query_auth) {
                        $query_auth = $this->appendAuthWhere($query_auth);
                    })
                    ->groupBy('contents_id');
            })
            // 設定を見てWhere を付与する。
            ->where(function ($query_setting) use ($blog_frame) {
                $query_setting = $this->appendSettingWhere($query_setting, $blog_frame);
            });

        // カテゴリのleftJoin
        $blogs_query = Categories::appendCategoriesLeftJoin($blogs_query, $this->frame->plugin_name, 'blogs_posts.categories_id', 'blogs_posts.blogs_id');

        // いいねのleftJoin
        $blogs_query = Like::appendLikeLeftJoin($blogs_query, $plugin_name, 'blogs_posts.contents_id', 'blogs_posts.blogs_id');

        // フレームの重要記事の条件参照
        if ($blog_frame->important_view == 'important_only') {
            $blogs_query->where('blogs_posts.important', 1);
        } elseif ($blog_frame->important_view == 'not_important') {
            $blogs_query->whereNull('blogs_posts.important');
        }

        // フレームの重要記事のソート条件参照
        if ($blog_frame->important_view == 'top') {
            $blogs_query->orderBy('important', 'desc');
        }

        // 続き
        $blogs_posts = $blogs_query->orderBy('posted_at', 'desc')
            ->orderBy('contents_id', 'desc')
            ->paginate($count, ["*"], "frame_{$blog_frame->id}_page");

        foreach ($blogs_posts as &$blogs_post) {
            // 続きを読むボタン名・続きを閉じるボタン名が空なら、初期値セットする
            if (empty($blogs_post->read_more_button)) {
                $blogs_post->read_more_button = BlogsPosts::read_more_button_default;
            }
            if (empty($blogs_post->close_more_button)) {
                $blogs_post->close_more_button = BlogsPosts::close_more_button_default;
            }
        }

        return $blogs_posts;
    }

    // move: UserPluginBaseに移動
    // /**
    //  *  要承認の判断
    //  */
    // protected function isApproval($frame_id)
    // {
    //     return $this->buckets->needApprovalUser(Auth::user(), $this->frame);

    //     //        // 承認の要否確認とステータス処理
    //     //        $blog_frame = $this->getBlogFrame($frame_id);
    //     //        if ($blog_frame->approval_flag == 1) {
    //     //
    //     //            // 記事修正、コンテンツ管理者権限がない場合は要承認
    //     //            if (!$this->isCan('role_article') && !$this->isCan('role_article_admin')) {
    //     //                return true;
    //     //            }
    //     //        }
    //     //        return false;
    // }

    /**
     *  タグの保存
     */
    private function saveTag($request, $blogs_post)
    {
        // タグの保存
        if ($request->tags) {
            $tags = explode(',', $request->tags);
            foreach ($tags as $tag) {
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
        foreach ($blogs_posts_tags as $blogs_posts_tag) {
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
                      ->select(
                          'frames.page_id              as page_id',
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

                      // blogs_frames テーブルがない(null)場合は全て or blogs_frames で重要
                      ->where(function ($important_query) {
                          $important_query->whereNull('blogs_frames.important_view')
                                          ->orWhere('blogs_frames.important_view', '')
                                          ->orWhere('blogs_frames.important_view', 'top')
                                          ->orWhere(function ($important_query2) {
                                               $important_query2->where('blogs_frames.important_view', 'not_important')
                                                                ->whereNull('blogs_posts.important');
                                          })
                                          ->orWhere(function ($important_query3) {
                                               $important_query3->where('blogs_frames.important_view', 'important_only')
                                                                ->where('blogs_posts.important', 1);
                                          });
                      })

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
                      ->select(
                          'blogs_posts.id              as post_id',
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
                      ->leftJoin('blogs_frames', function ($join) {
                          $join->on('blogs_frames.blogs_id', '=', 'blogs.id')
                          // frames.id がDB::Raw しなければ、バインドの値としてSQL が生成されて、"frames.id" というframes.id は存在せず、left join がレコードが取れなかった。
                          ->where('blogs_frames.frames_id', '=', DB::Raw("frames.id"));
                      })
                      ->leftJoin('categories', 'categories.id', '=', 'blogs_posts.categories_id')
                      ->join('pages', 'pages.id', '=', 'frames.page_id')
                      ->whereIn('pages.id', $page_ids)
                      ->where('status', '?')
                      ->where('posted_at', '<=', Carbon::now())
                      ->where(function ($plugin_query) use ($search_keyword) {
                          $plugin_query->where('blogs_posts.post_title', 'like', '?')
                                       ->orWhere('blogs_posts.post_text', 'like', '?')
                                       ->orWhere('blogs_posts.post_text2', 'like', '?');
                      })
                      ->whereRaw('CASE
                                  WHEN blogs_frames.scope IS NULL
                                      THEN blogs_frames.scope IS NULL
                                  WHEN blogs_frames.scope = "year" AND blogs_frames.scope_value IS NOT NULL
                                      THEN posted_at >= CONCAT(blogs_frames.scope_value, "-01-01") AND posted_at <= CONCAT(blogs_frames.scope_value, "-12-31 23:59:59")
                                  WHEN blogs_frames.scope = "fiscal" AND blogs_frames.scope_value IS NOT NULL
                                      THEN posted_at >= CONCAT(blogs_frames.scope_value, "-04-01") AND posted_at <= CONCAT((blogs_frames.scope_value + 1), "-03-31 23:59:59")
                                  END')

                      // blogs_frames テーブルがない(null)場合は全て or blogs_frames で重要
                      ->where(function ($important_query) {
                          $important_query->whereNull('blogs_frames.important_view')
                                          ->orWhere('blogs_frames.important_view', '')
                                          ->orWhere('blogs_frames.important_view', 'top')
                                          ->orWhere(function ($important_query2) {
                                               $important_query2->where('blogs_frames.important_view', 'not_important')
                                                                ->whereNull('blogs_posts.important');
                                          })
                                          ->orWhere(function ($important_query3) {
                                               $important_query3->where('blogs_frames.important_view', 'important_only')
                                                                ->where('blogs_posts.important', 1);
                                          });
                      })

                      ->whereNull('blogs_posts.deleted_at');

        //$bind = array($page_ids, 0, '%'.$search_keyword.'%', '%'.$search_keyword.'%');
        $bind = array($page_ids, 0, Carbon::now(), '%'.$search_keyword.'%', '%'.$search_keyword.'%', '%'.$search_keyword.'%', '', 'top', 'not_important', 'important_only', 1);
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

        // ブログデータ一覧の取得
        $blogs_posts = $this->getPosts($blog_frame);

        // タグ：画面表示するデータのblogs_posts_id を集める
        $posts_ids = array();
        foreach ($blogs_posts as $blogs_post) {
            $posts_ids[] = $blogs_post->id;
        }

        // タグ：タグデータ取得
        $blogs_posts_tags_row = BlogsPostsTags::whereIn('blogs_posts_id', $posts_ids)->get();

        // タグ：タグデータ詰めなおし（ブログデータの一覧にあてるための外配列）
        $blogs_posts_tags = array();
        foreach ($blogs_posts_tags_row as $record) {
            $blogs_posts_tags[$record->blogs_posts_id][] = $record->tags;
        }

        // タグ：タグデータをポストデータに紐づけ
        foreach ($blogs_posts as &$blogs_post) {
            if (array_key_exists($blogs_post->id, $blogs_posts_tags)) {
                $blogs_post->tags = $blogs_posts_tags[$blogs_post->id];
            }
        }

        // 表示テンプレートを呼び出す。
        return $this->view(
            'blogs', [
            'blogs_posts' => $blogs_posts,
            'blog_frame'  => $blog_frame,
            ]
        );
    }

    /**
     * 新規記事画面
     */
    public function create($request, $page_id, $frame_id, $blogs_posts_id = null)
    {
        // セッション初期化などのLaravel 処理。
        // $request->flash();

        // ブログ＆フレームデータ
        $blog_frame = $this->getBlogFrame($frame_id);

        // 空のデータ(画面で初期値設定で使用するため)
        $blogs_posts = new BlogsPosts();
        $blogs_posts->posted_at = date('Y-m-d H:i:00');

        // カテゴリ
        $blogs_categories = Categories::getInputCategories($this->frame->plugin_name, $blog_frame->blogs_id);

        // タグ
        $blogs_posts_tags = "";

        // 表示テンプレートを呼び出す。(blade でold を使用するため、withInput 使用)
        return $this->view('blogs_input', [
            'blog_frame'       => $blog_frame,
            'blogs_posts'      => $blogs_posts,
            'blogs_categories' => $blogs_categories,
            'blogs_posts_tags' => $blogs_posts_tags,
        ]);
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
            $before_post = BlogsPosts::
                whereIn('id', function ($query1) use ($blogs_post) {
                    // 権限の条件で絞って、contents_id でグループ化した最後のid（権限を加味した記事のID 一覧）
                    $query1->select(DB::raw('MAX(id) as id'))
                            ->from('blogs_posts')
                            ->where('blogs_id', $blogs_post->blogs_id)
                            ->where(function ($query2) {
                                $query2 = $this->appendAuthWhere($query2);
                            })
                            ->groupBy('contents_id');
                })
                // 同じ日付の記事があるので、(日付が小さい OR (日付が同じ＆contents_id が小さい)で1件目)
                // 一覧は 日付(desc), contents_id(desc) で表示するため
                ->where(function ($query3) use ($blogs_post) {
                    $query3->where('posted_at', '<', $blogs_post->posted_at)
                        ->orWhere(function ($query4) use ($blogs_post) {
                            $query4->where('posted_at', '=', $blogs_post->posted_at)
                                ->where('contents_id', '<', $blogs_post->contents_id);
                        });
                })
                // 表示設定を見て抽出範囲のwhereを追加する
                ->where(function ($query_setting) use ($blog_frame) {
                    $query_setting = $this->appendSettingWhere($query_setting, $blog_frame);
                })
                ->orderBy('posted_at', 'desc')
                ->orderBy('contents_id', 'desc')
                ->first();

            // 1件後
            $after_post = BlogsPosts::
                whereIn('id', function ($query1) use ($blogs_post) {
                    // 権限の条件で絞って、contents_id でグループ化した最後のid（権限を加味した記事のID 一覧）
                    $query1->select(DB::raw('MAX(id) as id'))
                        ->from('blogs_posts')
                        ->where('blogs_id', $blogs_post->blogs_id)
                        ->where(function ($query2) {
                            $query2 = $this->appendAuthWhere($query2);
                        })
                        ->groupBy('contents_id');
                })
                // 同じ日付の記事があるので、(日付が小さい OR (日付が同じ＆contents_id が大きい)で1件目)
                // 一覧は 日付(desc), contents_id(desc) で表示するため
                ->where(function ($query3) use ($blogs_post) {
                    $query3->where('posted_at', '>', $blogs_post->posted_at)
                        ->orWhere(function ($query4) use ($blogs_post) {
                            $query4->where('posted_at', '=', $blogs_post->posted_at)
                                ->where('contents_id', '>', $blogs_post->contents_id);
                        });
                })
                // 表示設定を見て抽出範囲のwhereを追加する
                ->where(function ($query_setting) use ($blog_frame) {
                    $query_setting = $this->appendSettingWhere($query_setting, $blog_frame);
                })
                ->orderBy('posted_at', 'asc')
                ->orderBy('contents_id', 'asc')
                ->first();
        }

        // 詳細画面を呼び出す。
        return $this->view('blogs_show', [
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
    public function edit($request, $page_id, $frame_id, $blogs_posts_id = null)
    {
        // セッション初期化などのLaravel 処理。
        // $request->flash();

        // Frame データ
        $blog_frame = $this->getBlogFrame($frame_id);

        // 記事取得（指定されたPOST ID そのままではなく、権限に応じたPOST を取得する。）
        $blogs_post = $this->getPost($blogs_posts_id);
        if (empty($blogs_post)) {
            return $this->view_error("403_inframe", null, 'editのユーザー権限に応じたPOST ID チェック');
        }

        // カテゴリ
        $blogs_categories = Categories::getInputCategories($this->frame->plugin_name, $blog_frame->blogs_id);

        // タグ取得
        $blogs_posts_tags_array = BlogsPostsTags::where('blogs_posts_id', $blogs_post->id)->get();
        $blogs_posts_tags = "";
        foreach ($blogs_posts_tags_array as $blogs_posts_tags_item) {
            $blogs_posts_tags .= ',' . $blogs_posts_tags_item->tags;
        }
        $blogs_posts_tags = trim($blogs_posts_tags, ',');

        // 変更画面を呼び出す。(blade でold を使用するため、withInput 使用)
        return $this->view('blogs_input', [
            'blog_frame'       => $blog_frame,
            'blogs_posts'      => $blogs_post,
            'blogs_categories' => $blogs_categories,
            'blogs_posts_tags' => $blogs_posts_tags,
        ]);
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
            // return ( $this->create($request, $page_id, $frame_id, $blogs_posts_id, $validator->errors()) );
            return back()->withErrors($validator)->withInput();
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
        $blogs_post->post_text     = $this->clean($request->post_text);
        $blogs_post->post_text2    = $this->clean($request->post_text2);
        $blogs_post->read_more_flag = $request->read_more_flag ?? 0;
        $blogs_post->read_more_button = $request->read_more_button;
        $blogs_post->close_more_button = $request->close_more_button;

        // 承認の要否確認とステータス処理
        if ($this->isApproval()) {
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
        } else {
            // 更新
            // 変更処理の場合、contents_id を旧レコードのcontents_id と同じにする。
            $blogs_post->contents_id = $old_blogs_post->contents_id;

            // 登録ユーザ
            $blogs_post->created_id   = $old_blogs_post->created_id;
            $blogs_post->created_name = $old_blogs_post->created_name;
            $blogs_post->created_at   = $old_blogs_post->created_at;

            // 旧レコードのstatus 更新(Activeなもの(status:0)は、status:9 に更新。他はそのまま。)ただし、承認待ちレコード作成時は対象外
            if ($blogs_post->status != 2) {
                BlogsPosts::where('contents_id', $old_blogs_post->contents_id)->where('status', 0)->update(['status' => 9]);
            }

            // データ保存
            $blogs_post->save();
        }

        // タグの保存
        $this->saveTag($request, $blogs_post);

        // 登録後はリダイレクトして表示用の初期処理を呼ぶ。
        // return $this->index($request, $page_id, $frame_id);
        return new Collection(['redirect_path' => url($this->page->permanent_link)]);
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
        } else {
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
        $blogs_post->categories_id = $request->categories_id;
        $blogs_post->important  = $request->important;
        $blogs_post->posted_at  = $request->posted_at . ':00';
        $blogs_post->post_text  = $this->clean($request->post_text);
        $blogs_post->post_text2 = $this->clean($request->post_text2);

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
        if ($blogs_posts_id) {
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
        $blog_frame = Frame::select('frames.*', 'blogs.id as blogs_id', 'blogs.view_count')
            ->leftJoin('blogs', 'blogs.bucket_id', '=', 'frames.bucket_id')
            ->where('frames.id', $frame_id)->first();

        // データ取得（1ページの表示件数指定）
        // $blogs = Blogs::orderBy('created_at', 'desc')
        //                ->paginate(10, ["*"], "frame_{$frame_id}_page");
        $blogs = Blogs::
            select(
                'blogs.id',
                'blogs.bucket_id',
                'blogs.created_at',
                'blogs.blog_name',
                DB::raw('count(blogs_posts.blogs_id) as entry_count')
            )
            ->leftJoin('blogs_posts', function ($leftJoin) {
                // 履歴あり & 論理削除対応
                $leftJoin->on('blogs.id', '=', 'blogs_posts.blogs_id')
                        ->where('blogs_posts.status', StatusType::active)
                        ->whereNull('blogs_posts.deleted_at');
            })
            ->groupBy(
                'blogs.id',
                'blogs.bucket_id',
                'blogs.created_at',
                'blogs.blog_name',
                'blogs_posts.blogs_id'
            )
            ->orderBy('blogs.created_at', 'desc')
            ->paginate(10, ["*"], "frame_{$frame_id}_page");

        // 表示テンプレートを呼び出す。
        return $this->view('blogs_list_buckets', [
            'blog_frame' => $blog_frame,
            'blogs'      => $blogs,
        ]);
    }

    /**
     * ブログ新規作成画面
     */
    public function createBuckets($request, $page_id, $frame_id, $blogs_id = null, $create_flag = false, $message = null)
    {
        // 新規作成フラグを付けてブログ設定変更画面を呼ぶ
        $create_flag = true;
        return $this->editBuckets($request, $page_id, $frame_id, $blogs_id, $create_flag, $message);
    }

    /**
     * ブログ設定変更画面の表示
     */
    public function editBuckets($request, $page_id, $frame_id, $blogs_id = null, $create_flag = false, $message = null)
    {
        // セッション初期化などのLaravel 処理。
        // $request->flash();

        // ブログ＆フレームデータ
        $blog_frame = $this->getBlogFrame($frame_id);

        // ブログデータ
        $blog = new Blogs();

        // blogs_id が渡ってくればblogs_id が対象
        if (!empty($blogs_id)) {
            $blog = Blogs::where('id', $blogs_id)->first();
        } elseif (!empty($blog_frame->bucket_id) && $create_flag == false) {
            // Frame のbucket_id があれば、bucket_id からブログデータ取得、なければ、新規作成か選択へ誘導
            $blog = Blogs::where('bucket_id', $blog_frame->bucket_id)->first();
        }

        // 表示テンプレートを呼び出す。
        return $this->view('blogs_edit_blog', [
            'blog_frame'  => $blog_frame,
            'blog'        => $blog,
            'create_flag' => $create_flag,
            'message'     => $message,
        ]);
    }

    /**
     * ブログ登録処理
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
            // if (empty($blogs_id)) {
            //     $create_flag = true;
            //     return $this->createBuckets($request, $page_id, $frame_id, $blogs_id, $create_flag, $message, $validator->errors());
            // } else {
            //     $create_flag = false;
            //     return $this->editBuckets($request, $page_id, $frame_id, $blogs_id, $create_flag, $message, $validator->errors());
            // }
            return back()->withErrors($validator)->withInput();
        }

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

            $request->flash_message = 'ブログ設定を追加しました。';

        } else {
            // blogs_id があれば、ブログを更新
            // ブログデータ取得
            $blogs = Blogs::where('id', $request->blogs_id)->first();

            $request->flash_message = 'ブログ設定を変更しました。';
        }

        // ブログ設定
        $blogs->blog_name     = $request->blog_name;
        $blogs->view_count    = (intval($request->view_count) < 0) ? 0 : intval($request->view_count);
        $blogs->rss           = $request->rss;
        $blogs->rss_count     = $request->rss_count;
        $blogs->use_like      = $request->use_like;
        $blogs->like_button_name = $request->like_button_name;
        //$blogs->approval_flag = $request->approval_flag;

        // データ保存
        $blogs->save();

        // ブログ名で、Buckets名も更新する
        Buckets::where('id', $blogs->bucket_id)->update(['bucket_name' => $request->blog_name]);

        // ブログ名で、Buckets名も更新する
        //Log::debug($blogs->bucket_id);
        //Log::debug($request->blog_name);

        // 新規作成フラグを付けてブログ設定変更画面を呼ぶ.
        // $create_flag = false;
        // return $this->editBuckets($request, $page_id, $frame_id, $blogs_id, $create_flag, $message);
        return new Collection(['redirect_path' => url('/') . '/plugin/blogs/editBuckets/' . $page_id . '/' . $frame_id . '/' . $blogs->id . '#frame-' . $frame_id]);
    }

    /**
     *  削除処理
     */
    public function destroyBuckets($request, $page_id, $frame_id, $blogs_id)
    {
        // blogs_id がある場合、データを削除
        if ($blogs_id) {
            // deleted_id, deleted_nameを自動セットするため、複数件削除する時はdestroy()を利用する。
            // see) https://readouble.com/laravel/5.5/ja/collections.html#method-pluck
            //
            // BlogsPosts::where('blogs_id', $blogs_id)->delete();
            $blogs_posts_ids = BlogsPosts::where('blogs_id', $blogs_id)->pluck('id');
            $blogs_posts_tags_ids = BlogsPostsTags::whereIn('blogs_posts_id', $blogs_posts_ids)->pluck('id');

            // タグ削除
            BlogsPostsTags::destroy($blogs_posts_tags_ids);

            // 記事データを削除する。
            BlogsPosts::destroy($blogs_posts_ids);

            // カテゴリ削除
            Categories::destroyBucketsCategories($this->frame->plugin_name, $blogs_id);

// Frame に紐づくBlog を削除した場合のみ、Frame の更新。（Frame に紐づかないBlog の削除もあるので、その場合はFrame は更新しない。）
// 実装は後で。

            // change: backets, buckets_rolesは $frame->bucket_id で消さない。選択したblogのbucket_idで消す
            $blogs = Blogs::find($blogs_id);
            // // バケツIDの取得のためにFrame を取得(Frame を更新する前に取得しておく)
            // $frame = Frame::where('id', $frame_id)->first();

            // FrameのバケツIDの更新. このバケツを表示している全ページのフレームのバケツIDを消す（もし、このフレームでこのバケツを表示していたとしても、$blogs->bucket_idで消えるため問題なし）
            // Frame::where('bucket_id', $frame->bucket_id)->update(['bucket_id' => null]);
            Frame::where('bucket_id', $blogs->bucket_id)->update(['bucket_id' => null]);

            // blogs_frames. バケツ削除時に表示設定は消さない. 今後フレーム削除時にプラグイン側で追加処理ができるようになったら削除する

            // 権限設定消す buckets_roles（消す。バケツに紐づき）
            $buckets_roles_ids = BucketsRoles::where('buckets_id', $blogs->bucket_id)->pluck('id');
            // dd($buckets_roles_ids, $frame->bucket_id);
            BucketsRoles::destroy($buckets_roles_ids);

            // backetsの削除
            // Buckets::where('id', $frame->bucket_id)->delete();
            Buckets::destroy($blogs->bucket_id);

            // ブログ設定を削除する。
            Blogs::destroy($blogs_id);
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

        // 表示ブログ選択画面を呼ぶ. リダイレクトで遷移するため、ここでは何もしない
        // return $this->listBuckets($request, $page_id, $frame_id, $id);
    }

    /**
     * カテゴリ表示関数
     */
    public function listCategories($request, $page_id, $frame_id, $id = null)
    {
        // セッション初期化などのLaravel 処理。
        // $request->flash();

        // ブログ
        $blog_frame = $this->getBlogFrame($frame_id);

        // 共通カテゴリ
        $general_categories = Categories::getGeneralCategories($this->frame->plugin_name, $blog_frame->blogs_id);

        // 個別カテゴリ（プラグイン）
        $plugin_categories = Categories::getPluginCategories($this->frame->plugin_name, $blog_frame->blogs_id);

        // 表示テンプレートを呼び出す。
        return $this->view('blogs_list_categories', [
            'general_categories' => $general_categories,
            'plugin_categories' => $plugin_categories,
            'blog_frame' => $blog_frame,
        ]);
    }

    /**
     *  カテゴリ登録処理
     */
    public function saveCategories($request, $page_id, $frame_id, $id = null)
    {
        /* エラーチェック
        ------------------------------------ */

        $validator = Categories::validatePluginCategories($request);

        if ($validator->fails()) {
            // return $this->listCategories($request, $page_id, $frame_id, $id, $validator->errors());
            return redirect()->back()->withErrors($validator)->withInput();
        }

        /* カテゴリ追加
        ------------------------------------ */

        // ブログ
        $blog_frame = $this->getBlogFrame($frame_id);

        Categories::savePluginCategories($request, $this->frame->plugin_name, $blog_frame->blogs_id);

        // return $this->listCategories($request, $page_id, $frame_id, $id, null, true);
        // このメソッドはredirect 付のルートで呼ばれて、処理後はページの再表示が行われるため、ここでは何もしない。
    }

    /**
     *  カテゴリ削除処理
     */
    public function deleteCategories($request, $page_id, $frame_id, $id = null)
    {
        Categories::deleteCategories($this->frame->plugin_name, $id);

        // return $this->listCategories($request, $page_id, $frame_id, $id, null, true);
        // このメソッドはredirect 付のルートで呼ばれて、処理後はページの再表示が行われるため、ここでは何もしない。
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
            } else {
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
        // $request->flash();

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
        return $this->view('blogs_setting_frame', [
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
        if ($request->scope == 'year' || $request->scope == 'fiscal') {
            $validator_values['scope_value'][] = ['required'];
        }
        $validator_attributes['scope_value'] = '指定年';

        $validator = Validator::make($request->all(), $validator_values);
        $validator->setAttributeNames($validator_attributes);

        // エラーがあった場合は入力画面に戻る。
        if ($validator->fails()) {
            // Session::flash('flash_errors', $validator->errors());
            // return $this->settingBlogFrame($request, $page_id, $frame_id);
            return back()->withErrors($validator)->withInput();
        }

        // プラグインのフレームやBlogのID が設定されていない場合は空振りさせる。
        if (empty($blog_frame) || empty($blog_frame->blogs_id)) {
            return;
        }

        BlogsFrames::updateOrCreate(
            ['frames_id' => $frame_id],
            ['blogs_id' => $blog_frame->blogs_id, 'frames_id' => $frame_id, 'scope' => $request->scope, 'scope_value' => $request->scope_value, 'important_view' => $request->important_view ]
        );

        // フレーム設定保存
        $this->saveFrameConfigs($request, $frame_id);
        // 更新したので、frame_configsを設定しなおす
        $this->refreshFrameConfigs();

        // redirect_pathで遷移するため、ここでは何もしない
        // return $this->settingBlogFrame($request, $page_id, $frame_id);
    }

    /**
     * フレーム設定を保存する。
     *
     * @param Illuminate\Http\Request $request リクエスト
     * @param int $frame_id フレームID
     */
    private function saveFrameConfigs($request, $frame_id)
    {
        $configs = BlogFrameConfig::getMemberKeys();
        foreach ($configs as $key => $value) {

            if (empty($request->$value)) {
                return;
            }

            FrameConfig::updateOrCreate(
                ['frame_id' => $frame_id, 'name' => $value],
                ['value' => $request->$value]
            );
        }
    }

    /**
     * いいねをJSON形式で返す
     */
    public function saveLikeJson($request, $page_id, $frame_id, $id = null)
    {
        $blog_frame = $this->getBlogFrame($frame_id);
        if (empty($blog_frame)) {
            return;
        }

        $count = Like::saveLike($this->frame->plugin_name, $blog_frame->blogs_id, $id);
        return $count;
    }
}
