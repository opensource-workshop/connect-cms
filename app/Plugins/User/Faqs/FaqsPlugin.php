<?php

namespace App\Plugins\User\Faqs;

use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;

use DB;

use App\Models\Core\Configs;
use App\Models\Common\Buckets;
use App\Models\Common\Categories;
use App\Models\Common\Frame;
// use App\Models\Common\Page;
use App\Models\User\Faqs\Faqs;
use App\Models\User\Faqs\FaqsPosts;
use App\Models\User\Faqs\FaqsPostsTags;

use App\Plugins\User\UserPluginBase;

use App\Utilities\String\StringUtils;

/**
 * FAQプラグイン
 *
 * @author 永原　篤 <nagahara@opensource-workshop.jp>
 * @copyright OpenSource-WorkShop Co.,Ltd. All Rights Reserved
 * @category FAQプラグイン
 * @package Controller
 */
class FaqsPlugin extends UserPluginBase
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

        // deleteCategories の場合は、Faqs_posts のオブジェクトではないので、nullで返す。
        if ($action == 'deleteCategories') {
            return null;
        }

        // 一度読んでいれば、そのPOSTを再利用する。
        if (!empty($this->post)) {
            return $this->post;
        }

        // データのグループ（contents_id）が欲しいため、指定されたID のPOST を読む
        $arg_post = FaqsPosts::where('id', $id)->first();

        $plugin_name = $this->frame->plugin_name;

        // 指定されたPOST ID そのままではなく、権限に応じたPOST を取得する。
        $this->post = FaqsPosts::
            select(
                'faqs_posts.*',
                'categories.color as category_color',
                'categories.background_color as category_background_color',
                'categories.category as category',
                'plugin_categories.view_flag as category_view_flag'
            )
            ->leftJoin('categories', function ($join) {
                $join->on('categories.id', '=', 'faqs_posts.categories_id')
                    ->whereNull('categories.deleted_at');
            })
            ->leftJoin('plugin_categories', function ($join) use ($plugin_name) {
                $join->on('plugin_categories.categories_id', '=', 'categories.id')
                    ->where('plugin_categories.target', '=', $plugin_name)
                    ->whereColumn('plugin_categories.target_id', 'faqs_posts.faqs_id')
                    ->where('plugin_categories.view_flag', 1)   // 表示するカテゴリのみ
                    ->whereNull('plugin_categories.deleted_at');
            })
            ->where('faqs_posts.contents_id', $arg_post->contents_id)
            ->where(function ($query) {
                $query = $this->appendAuthWhere($query);
            })
            ->orderBy('faqs_posts.id', 'desc')
            ->first();

        return $this->post;
    }

    /* private関数 */

    /**
     *  紐づくFAQID とフレームデータの取得
     */
    private function getFaqFrame($frame_id)
    {
        // Frame データ
        $frame = Frame::select('frames.*', 'faqs.id as faqs_id', 'faqs.faq_name', 'faqs.view_count', 'faqs.rss', 'faqs.rss_count', 'faqs.sequence_conditions', 'faqs.display_posted_at_flag')
            ->leftJoin('faqs', 'faqs.bucket_id', '=', 'frames.bucket_id')
            ->where('frames.id', $frame_id)
            ->first();
        return $frame;
    }

    /**
     *  FAQ記事チェック設定
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
        if ($this->isCan('role_article') || $this->isCan('role_article_admin')) {
            // 記事修正権限、コンテンツ管理者の場合、全件取得のため、追加条件なしで戻る。
        } elseif ($this->isCan('role_approval')) {
            // 承認権限の場合、Active ＋ 承認待ちの取得
            $query->Where('status', '=', 0)
                  ->orWhere('status', '=', 2);
        } elseif ($this->isCan('role_reporter')) {
            // 編集者権限の場合、Active ＋ 自分の全ステータス記事の取得
            $query->Where('status', '=', 0)
                  ->orWhere('faqs_posts.created_id', '=', Auth::user()->id);
        } else {
            // その他（ゲスト）
            $query->where('status', 0);
            $query->where('faqs_posts.posted_at', '<=', Carbon::now());
        }

        return $query;
    }

    // delete: 使ってないprivateメソッド
    // /**
    //  *  表示条件に対するソート条件追加
    //  */
    // private function appendOrder($query, $faq_frame)
    // {
    //     if ($faq_frame->sequence_conditions == 0) {
    //         // 最新順
    //         $query->orderBy('posted_at', 'desc');
    //     } elseif ($faq_frame->sequence_conditions == 1) {
    //         // 投稿順
    //         $query->orderBy('posted_at', 'asc');
    //     } elseif ($faq_frame->sequence_conditions == 2) {
    //         // 指定順
    //         $query->orderBy('display_sequence', 'asc');
    //     }

    //     return $query;
    // }

    /**
     *  FAQ記事一覧取得
     */
    private function getPosts($faq_frame, $option_count = null)
    {
        //$faqs_posts = null;

        // 件数
        $count = $faq_frame->view_count;
        if ($option_count != null) {
            $count = $option_count;
        }

        $plugin_name = $this->frame->plugin_name;

        // 削除されていないデータでグルーピングして、最新のIDで全件
        $faqs_posts = FaqsPosts::
            select(
                'faqs_posts.*',
                'categories.color as category_color',
                'categories.background_color as category_background_color',
                'categories.category as category',
                'plugin_categories.view_flag as category_view_flag'
            )
            ->leftJoin('categories', function ($join) {
                $join->on('categories.id', '=', 'faqs_posts.categories_id')
                    ->whereNull('categories.deleted_at');
            })
            ->leftJoin('plugin_categories', function ($join) use ($plugin_name) {
                $join->on('plugin_categories.categories_id', '=', 'categories.id')
                    ->where('plugin_categories.target', '=', $plugin_name)
                    ->whereColumn('plugin_categories.target_id', 'faqs_posts.faqs_id')
                    ->where('plugin_categories.view_flag', 1)   // 表示するカテゴリのみ
                    ->whereNull('plugin_categories.deleted_at');
            })
            ->whereIn('faqs_posts.id', function ($query) use ($faq_frame) {
                $query->select(DB::raw('MAX(id) As id'))
                    ->from('faqs_posts')
                    ->where('faqs_id', $faq_frame->faqs_id)
                    ->where('deleted_at', null)
                    // 権限を見てWhere を付与する。
                    ->where(function ($query_auth) {
                        $query_auth = $this->appendAuthWhere($query_auth);
                    })
                    ->groupBy('contents_id');
            });
        // 表示条件に対するソート条件追加

        if ($faq_frame->sequence_conditions == 0) {
            // 最新順
            $faqs_posts->orderBy('posted_at', 'desc');
        } elseif ($faq_frame->sequence_conditions == 1) {
            // 投稿順
            $faqs_posts->orderBy('posted_at', 'asc');
        } elseif ($faq_frame->sequence_conditions == 2) {
            // 指定順
            $faqs_posts->orderBy('display_sequence', 'asc');
        }

       // 取得
        $faqs_posts_recored = $faqs_posts->orderBy('posted_at', 'desc')
            ->paginate($count, ["*"], "frame_{$faq_frame->id}_page");

        return $faqs_posts_recored;
    }

    // move: UserPluginBaseに移動
    // /**
    //  *  要承認の判断
    //  */
    // protected function isApproval($frame_id)
    // {
    //     return $this->buckets->needApprovalUser(Auth::user());
    //
    //     //        // 承認の要否確認とステータス処理
    //     //        $faq_frame = $this->getFaqFrame($frame_id);
    //     //        if ($faq_frame->approval_flag == 1) {
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
    private function saveTag($request, $faqs_post)
    {
        // タグの保存
        if ($request->tags) {
            $tags = explode(',', $request->tags);
            foreach ($tags as $tag) {
                // 新規オブジェクト生成
                $faqs_posts_tags = new FaqsPostsTags();

                // タグ登録
                $faqs_posts_tags->created_id     = $faqs_post->created_id;
                $faqs_posts_tags->faqs_posts_id = $faqs_post->id;
                $faqs_posts_tags->tags           = $tag;
                $faqs_posts_tags->save();
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
        $faqs_posts_tags = FaqsPostsTags::where('faqs_posts_id', $from_post->id)->orderBy('id', 'asc')->get();
        foreach ($faqs_posts_tags as $faqs_posts_tag) {
            $new_tag = $faqs_posts_tag->replicate();
            $new_tag->faqs_posts_id = $to_post->id;
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

        $return[] = DB::table('faqs_posts')
                      ->select(
                          'frames.page_id              as page_id',
                          'frames.id                   as frame_id',
                          'faqs_posts.id              as post_id',
                          'faqs_posts.post_title      as post_title',
                          'faqs_posts.important       as important',
                          'faqs_posts.posted_at       as posted_at',
                          'faqs_posts.created_name    as posted_name',
                          'categories.classname        as classname',
                          'categories.category         as category',
                          DB::raw('"faqs" as plugin_name')
                      )
                      ->join('faqs', 'faqs.id', '=', 'faqs_posts.faqs_id')
                      ->join('frames', 'frames.bucket_id', '=', 'faqs.bucket_id')
                      ->leftJoin('categories', 'categories.id', '=', 'faqs_posts.categories_id')
                      ->where('status', 0)
                      ->where('disable_whatsnews', 0)
                      ->whereNull('faqs_posts.deleted_at');

        $return[] = 'show_page_frame_post';
        $return[] = '/plugin/faqs/show';

        return $return;
    }

    /**
     *  検索用メソッド
     */
    public static function getSearchArgs($search_keyword)
    {
        $return[] = DB::table('faqs_posts')
                      ->select(
                          'faqs_posts.id              as post_id',
                          'frames.id                   as frame_id',
                          'frames.page_id              as page_id',
                          'pages.permanent_link        as permanent_link',
                          'faqs_posts.post_title      as post_title',
                          'faqs_posts.important       as important',
                          'faqs_posts.posted_at       as posted_at',
                          'faqs_posts.created_name    as posted_name',
                          'categories.classname        as classname',
                          'faqs_posts.categories_id   as categories_id',
                          'categories.category         as category',
                          DB::raw('"faqs" as plugin_name')
                      )
                      ->join('faqs', 'faqs.id', '=', 'faqs_posts.faqs_id')
                      ->join('frames', 'frames.bucket_id', '=', 'faqs.bucket_id')
                      ->leftJoin('categories', 'categories.id', '=', 'faqs_posts.categories_id')
                      ->leftjoin('pages', 'pages.id', '=', 'frames.page_id')
                      ->where('status', '?')
                      ->where(function ($plugin_query) use ($search_keyword) {
                          $plugin_query->where('faqs_posts.post_title', 'like', '?')
                                       ->orWhere('faqs_posts.post_text', 'like', '?');
                      })
                      ->whereNull('faqs_posts.deleted_at');


        $bind = array(0, '%'.$search_keyword.'%', '%'.$search_keyword.'%');
        $return[] = $bind;
        $return[] = 'show_page_frame_post';
        $return[] = '/plugin/faqs/show';

        return $return;
    }

    /* 画面アクション関数 */

    /**
     *  データ初期表示関数
     *  コアがページ表示の際に呼び出す関数
     */
    public function index($request, $page_id, $frame_id)
    {
        // FAQ＆フレームデータ
        $faq_frame = $this->getFaqFrame($frame_id);
        if (empty($faq_frame)) {
            return;
        }

        // Page データ
        // $page = Page::where('id', $page_id)->first();

        // 認証されているユーザの取得
        // $user = Auth::user();

        // FAQデータ一覧の取得
        $faqs_posts = $this->getPosts($faq_frame);

        // タグ：画面表示するデータのfaqs_posts_id を集める
        $posts_ids = array();
        foreach ($faqs_posts as $faqs_post) {
            $posts_ids[] = $faqs_post->id;
        }

        // タグ：タグデータ取得
        $faqs_posts_tags_row = FaqsPostsTags::whereIn('faqs_posts_id', $posts_ids)->get();

        // タグ：タグデータ詰めなおし（FAQデータの一覧にあてるための外配列）
        $faqs_posts_tags = array();
        foreach ($faqs_posts_tags_row as $record) {
            $faqs_posts_tags[$record->faqs_posts_id][] = $record->tags;
        }

        // タグ：タグデータをポストデータに紐づけ
        foreach ($faqs_posts as &$faqs_post) {
            if (array_key_exists($faqs_post->id, $faqs_posts_tags)) {
                $faqs_post->tags = $faqs_posts_tags[$faqs_post->id];
            }
        }

        // 表示テンプレートを呼び出す。
        return $this->view('faqs', [
            'faqs_posts' => $faqs_posts,
            'faq_frame'  => $faq_frame,
        ]);
    }

    /**
     *  新規記事画面
     */
    public function create($request, $page_id, $frame_id, $faqs_posts_id = null, $errors = null)
    {
        // セッション初期化などのLaravel 処理。
        $request->flash();

        // FAQ＆フレームデータ
        $faq_frame = $this->getFaqFrame($frame_id);

        // 空のデータ(画面で初期値設定で使用するため)
        $faqs_posts = new FaqsPosts();
        $faqs_posts->posted_at = date('Y-m-d H:i:00');

        // カテゴリ
        $faqs_categories = Categories::getInputCategories($this->frame->plugin_name, $faq_frame->faqs_id);

        // タグ
        $faqs_posts_tags = "";

        // 表示テンプレートを呼び出す。(blade でold を使用するため、withInput 使用)
        return $this->view(
            'faqs_input', [
            'faq_frame'       => $faq_frame,
            'faqs_posts'      => $faqs_posts,
            'faqs_categories' => $faqs_categories,
            'faqs_posts_tags' => $faqs_posts_tags,
            'errors'           => $errors,
            ]
        )->withInput($request->all);
    }

    /**
     *  詳細表示関数
     */
    public function show($request, $page_id, $frame_id, $faqs_posts_id = null)
    {
        // Frame データ
        $faq_frame = $this->getFaqFrame($frame_id);

        // 記事取得（指定されたPOST ID そのままではなく、権限に応じたPOST を取得する。）
        $faqs_post = $this->getPost($faqs_posts_id);
        if (empty($faqs_post)) {
            return $this->view_error("403_inframe", null, 'showのユーザー権限に応じたPOST ID チェック');
        }

        // タグ取得
        // タグ：タグデータ取得
        $faqs_post_tags = new FaqsPostsTags();
        if ($faqs_post) {
            $faqs_post_tags = FaqsPostsTags::where('faqs_posts_id', $faqs_post->id)->get();
        }

        // ひとつ前、ひとつ後の記事
        $before_post = null;
        $after_post = null;
        if ($faqs_post) {
            $before_post = FaqsPosts::where('faqs_id', $faqs_post->faqs_id)
                                     ->where('posted_at', '<', $faqs_post->posted_at)
                                     ->where(function ($query) {
                                         $query = $this->appendAuthWhere($query);
                                     })
                                     ->orderBy('posted_at', 'desc')
                                     ->first();
            $after_post = FaqsPosts::where('faqs_id', $faqs_post->faqs_id)
                                     ->where('posted_at', '>', $faqs_post->posted_at)
                                     ->where(function ($query) {
                                         $query = $this->appendAuthWhere($query);
                                     })
                                     ->orderBy('posted_at', 'asc')
                                     ->first();
        }

        // 詳細画面を呼び出す。
        return $this->view(
            'faqs_show', [
            'faq_frame'  => $faq_frame,
            'post'        => $faqs_post,
            'post_tags'   => $faqs_post_tags,
            'before_post' => $before_post,
            'after_post'  => $after_post,
            ]
        );
    }

    /**
     * 記事編集画面
     */
    public function edit($request, $page_id, $frame_id, $faqs_posts_id = null, $errors = null)
    {
        // セッション初期化などのLaravel 処理。
        $request->flash();

        // Frame データ
        $faq_frame = $this->getFaqFrame($frame_id);

        // 記事取得（指定されたPOST ID そのままではなく、権限に応じたPOST を取得する。）
        $faqs_post = $this->getPost($faqs_posts_id);
        if (empty($faqs_post)) {
            return $this->view_error("403_inframe", null, 'editのユーザー権限に応じたPOST ID チェック');
        }

        // カテゴリ
        $faqs_categories = Categories::getInputCategories($this->frame->plugin_name, $faq_frame->faqs_id);

        // タグ取得
        $faqs_posts_tags_array = FaqsPostsTags::where('faqs_posts_id', $faqs_post->id)->get();
        $faqs_posts_tags = "";
        foreach ($faqs_posts_tags_array as $faqs_posts_tags_item) {
            $faqs_posts_tags .= ',' . $faqs_posts_tags_item->tags;
        }
        $faqs_posts_tags = trim($faqs_posts_tags, ',');

        // 変更画面を呼び出す。(blade でold を使用するため、withInput 使用)
        return $this->view(
            'faqs_input', [
            'faq_frame'       => $faq_frame,
            'faqs_posts'      => $faqs_post,
            'faqs_categories' => $faqs_categories,
            'faqs_posts_tags' => $faqs_posts_tags,
            'errors'           => $errors,
            ]
        )->withInput($request->all);
    }

    /**
     * FAQ記事登録処理
     */
    public function save($request, $page_id, $frame_id, $faqs_posts_id = null)
    {
        $request->merge([
            // 表示順:  全角→半角変換
            "display_sequence" => StringUtils::convertNumericAndMinusZenkakuToHankaku($request->display_sequence),
        ]);

        // 項目のエラーチェック
        $validator = $this->makeValidator($request);

        // エラーがあった場合は入力画面に戻る。
        if ($validator->fails()) {
            return ( $this->create($request, $page_id, $frame_id, $faqs_posts_id, $validator->errors()) );
        }

        // id があれば旧データを取得＆権限を加味して更新可能データかどうかのチェック
        $old_faqs_post = null;
        if (!empty($faqs_posts_id)) {
            // 指定されたID のデータ
            $old_faqs_post = FaqsPosts::where('id', $faqs_posts_id)->first();

            // 記事取得（指定されたPOST ID そのままではなく、権限に応じたPOST を取得する。）
            $check_faqs_post = $this->getPost($faqs_posts_id);

            // 指定されたID と権限に応じたPOST のID が異なる場合は、キーを捏造したPOST と考えられるため、エラー
            if (empty($check_faqs_post) || $check_faqs_post->id != $old_faqs_post->id) {
                return $this->view_error("403_inframe", null, 'saveのユーザー権限に応じたPOST ID チェック');
            }
        }

        // 新規オブジェクト生成
        $faqs_post = new FaqsPosts();

        // 表示順が空なら、自分を省いた最後の番号+1 をセット
        $display_sequence = $this->getSaveDisplaySequence($request->display_sequence, $request->faqs_id, $faqs_posts_id);

        // FAQ記事設定
        $faqs_post->faqs_id          = $request->faqs_id;
        $faqs_post->post_title       = $request->post_title;
        $faqs_post->categories_id    = $request->categories_id;
        $faqs_post->important        = $request->important;
        $faqs_post->posted_at        = $request->posted_at . ':00';
        $faqs_post->post_text        = $this->clean($request->post_text);   // wysiwygのXSS対応のJavaScript等の制限
        $faqs_post->display_sequence = $display_sequence;

        // 承認の要否確認とステータス処理
        if ($this->isApproval()) {
            $faqs_post->status = 2;
        }

        if (empty($faqs_posts_id)) {
            // 新規
            // 登録ユーザ
            $faqs_post->created_id  = Auth::user()->id;

            // データ保存
            $faqs_post->save();

            // 新規登録の場合、contents_id を最初のレコードのid と同じにする。
            FaqsPosts::where('id', $faqs_post->id)->update(['contents_id' => $faqs_post->id]);
        } else {
            // 更新
            // 変更処理の場合、contents_id を旧レコードのcontents_id と同じにする。
            $faqs_post->contents_id = $old_faqs_post->contents_id;

            // 登録ユーザ
            $faqs_post->created_id  = $old_faqs_post->created_id;

            // 旧レコードのstatus 更新(Activeなもの(status:0)は、status:9 に更新。他はそのまま。)ただし、承認待ちレコード作成時は対象外
            if ($faqs_post->status != 2) {
                FaqsPosts::where('contents_id', $old_faqs_post->contents_id)->where('status', 0)->update(['status' => 9]);
            }

            // データ保存
            $faqs_post->save();
        }

        // タグの保存
        $this->saveTag($request, $faqs_post);

        // 登録後は表示用の初期処理を呼ぶ。
        return $this->index($request, $page_id, $frame_id);
    }

    /**
     * 登録する表示順を取得
     */
    private function getSaveDisplaySequence($display_sequence, $faqs_id, $id)
    {
        // 表示順が空なら、自分を省いた最後の番号+1 をセット
        if (!is_null($display_sequence)) {
            $display_sequence = intval($display_sequence);
        } else {
            $max_display_sequence = FaqsPosts::where('faqs_id', $faqs_id)->where('id', '<>', $id)->max('display_sequence');
            $display_sequence = empty($max_display_sequence) ? 1 : $max_display_sequence + 1;
        }
        return $display_sequence;
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
            $faqs_post = new FaqsPosts();

            // 登録ユーザ
            $faqs_post->created_id  = Auth::user()->id;
        } else {
            $faqs_post = FaqsPosts::find($id)->replicate();

            // チェック用に記事取得（指定されたPOST ID そのままではなく、権限に応じたPOST を取得する。）
            $check_faqs_post = $this->getPost($id);

            // 指定されたID と権限に応じたPOST のID が異なる場合は、キーを捏造したPOST と考えられるため、エラー
            if (empty($check_faqs_post) || $check_faqs_post->id != $id) {
                return $this->view_error("403_inframe", null, 'temporarysaveのユーザー権限に応じたPOST ID チェック');
            }
        }

        // FAQ記事設定
        $faqs_post->status = 1;
        $faqs_post->faqs_id          = $request->faqs_id;
        $faqs_post->post_title       = $request->post_title;
        $faqs_post->categories_id    = $request->categories_id;
        $faqs_post->important        = $request->important;
        $faqs_post->posted_at        = $request->posted_at . ':00';
        $faqs_post->post_text        = $request->post_text;
        $faqs_post->display_sequence = intval(empty($request->display_sequence) ? 0 : $request->display_sequence);

        $faqs_post->save();

        if (empty($id)) {
            // 新規登録の場合、contents_id を最初のレコードのid と同じにする。
            FaqsPosts::where('id', $faqs_post->id)->update(['contents_id' => $faqs_post->id]);
        }

        // タグの保存
        $this->saveTag($request, $faqs_post);

        // 登録後は表示用の初期処理を呼ぶ。
        return $this->index($request, $page_id, $frame_id);
    }

    /**
     *  削除処理
     */
    public function delete($request, $page_id, $frame_id, $faqs_posts_id)
    {
        // id がある場合、データを削除
        if ($faqs_posts_id) {
            // 同じcontents_id のデータを削除するため、一旦、対象データを取得
            $post = FaqsPosts::where('id', $faqs_posts_id)->first();

            // 削除ユーザ、削除日を設定する。（複数レコード更新のため、自動的には入らない）
            FaqsPosts::where('contents_id', $post->contents_id)->update(['deleted_id' => Auth::user()->id, 'deleted_name' => Auth::user()->name]);

            // データを削除する。
            FaqsPosts::where('contents_id', $post->contents_id)->delete();
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
        $faqs_post = FaqsPosts::find($id)->replicate();

        // チェック用に記事取得（指定されたPOST ID そのままではなく、権限に応じたPOST を取得する。）
        $check_faqs_post = $this->getPost($id);

        // 指定されたID と権限に応じたPOST のID が異なる場合は、キーを捏造したPOST と考えられるため、エラー
        if (empty($check_faqs_post) || $check_faqs_post->id != $id) {
            return $this->view_error("403_inframe", null, 'approvalのユーザー権限に応じたPOST ID チェック');
        }

        // 旧レコードのstatus 更新(Activeなもの(status:0)は、status:9 に更新。他はそのまま。)
        FaqsPosts::where('contents_id', $faqs_post->contents_id)->where('status', 0)->update(['status' => 9]);

        // FAQ記事設定
        $faqs_post->status = 0;
        $faqs_post->save();

        // タグもコピー
        $this->copyTag($check_faqs_post, $faqs_post);

        // 登録後は表示用の初期処理を呼ぶ。
        return $this->index($request, $page_id, $frame_id);
    }

    /**
     * データ選択表示関数
     */
    public function listBuckets($request, $page_id, $frame_id, $id = null)
    {
        // Frame データ
        $faq_frame = DB::table('frames')
                      ->select('frames.*', 'faqs.id as faqs_id', 'faqs.view_count')
                      ->leftJoin('faqs', 'faqs.bucket_id', '=', 'frames.bucket_id')
                      ->where('frames.id', $frame_id)->first();

        // データ取得（1ページの表示件数指定）
        $faqs = Faqs::orderBy('created_at', 'desc')
                       ->paginate(10, ["*"], "frame_{$frame_id}_page");

        // 表示テンプレートを呼び出す。
        return $this->view(
            'faqs_list_buckets', [
            'faq_frame' => $faq_frame,
            'faqs'      => $faqs,
            ]
        );
    }

    /**
     * FAQ新規作成画面
     */
    public function createBuckets($request, $page_id, $frame_id, $faqs_id = null, $create_flag = false, $message = null, $errors = null)
    {
        // 新規作成フラグを付けてFAQ設定変更画面を呼ぶ
        $create_flag = true;
        return $this->editBuckets($request, $page_id, $frame_id, $faqs_id, $create_flag, $message, $errors);
    }

    /**
     * FAQ設定変更画面の表示
     */
    public function editBuckets($request, $page_id, $frame_id, $faqs_id = null, $create_flag = false, $message = null, $errors = null)
    {
        // セッション初期化などのLaravel 処理。
        $request->flash();

        // FAQ＆フレームデータ
        $faq_frame = $this->getFaqFrame($frame_id);

        // FAQデータ
        $faq = new Faqs();

        if (!empty($faqs_id)) {
            // faqs_id が渡ってくればfaqs_id が対象
            $faq = Faqs::where('id', $faqs_id)->first();
        } elseif (!empty($faq_frame->bucket_id) && $create_flag == false) {
            // Frame のbucket_id があれば、bucket_id からFAQデータ取得、なければ、新規作成か選択へ誘導
            $faq = Faqs::where('bucket_id', $faq_frame->bucket_id)->first();
        }

        // 表示テンプレートを呼び出す。
        return $this->view(
            'faqs_edit_faq', [
            'faq_frame'  => $faq_frame,
            'faq'        => $faq,
            'create_flag' => $create_flag,
            'message'     => $message,
            'errors'      => $errors,
            ]
        )->withInput($request->all);
    }

    /**
     *  FAQ登録処理
     */
    public function saveBuckets($request, $page_id, $frame_id, $faqs_id = null)
    {
        // 項目のエラーチェック
        $validator = Validator::make($request->all(), [
            'faq_name'            => ['required'],
            'view_count'          => ['required', 'numeric'],
            'rss_count'           => ['nullable', 'numeric'],
            'sequence_conditions' => ['nullable', 'numeric'],
        ]);
        $validator->setAttributeNames([
            'faq_name'            => 'FAQ名',
            'view_count'          => '表示件数',
            'rss_count'           => 'RSS件数',
            'sequence_conditions' => '順序条件',
        ]);

        // エラーがあった場合は入力画面に戻る。
        $message = null;
        if ($validator->fails()) {
            if (empty($faqs_id)) {
                $create_flag = true;
                return $this->createBuckets($request, $page_id, $frame_id, $faqs_id, $create_flag, $message, $validator->errors());
            } else {
                $create_flag = false;
                return $this->editBuckets($request, $page_id, $frame_id, $faqs_id, $create_flag, $message, $validator->errors());
            }
        }

        // 更新後のメッセージ
        $message = null;

        // 画面から渡ってくるfaqs_id が空ならバケツとFAQを新規登録
        if (empty($request->faqs_id)) {
            // バケツの登録
            $bucket_id = DB::table('buckets')->insertGetId([
                'bucket_name' => $request->faq_name,
                'plugin_name' => 'faqs'
            ]);

            // FAQデータ新規オブジェクト
            $faqs = new Faqs();
            $faqs->bucket_id = $bucket_id;

            // Frame のBuckets を見て、Buckets が設定されていなければ、作成したものに紐づける。
            // Frame にBuckets が設定されていない ＞ 新規のフレーム＆FAQ作成
            // Frame にBuckets が設定されている ＞ 既存のフレーム＆FAQ更新
            // （表示FAQ選択から遷移してきて、内容だけ更新して、フレームに紐づけないケースもあるため）
            $frame = Frame::where('id', $frame_id)->first();
            if (empty($frame->bucket_id)) {
                // FrameのバケツIDの更新
                $frame = Frame::where('id', $frame_id)->update(['bucket_id' => $bucket_id]);
            }

            $message = 'FAQ設定を追加しました。';
        } else {
            // faqs_id があれば、FAQを更新
            // FAQデータ取得
            $faqs = Faqs::where('id', $request->faqs_id)->first();

            $message = 'FAQ設定を変更しました。';
        }

        // FAQ設定
        $faqs->faq_name            = $request->faq_name;
        $faqs->view_count          = $request->view_count;
        $faqs->rss                 = $request->rss;
        $faqs->rss_count           = $request->rss_count;
        $faqs->display_posted_at_flag = $request->display_posted_at_flag ?? 0;
        $faqs->sequence_conditions = intval($request->sequence_conditions);
        //$faqs->approval_flag = $request->approval_flag;

        // データ保存
        $faqs->save();

        // FAQ名で、Buckets名も更新する
        Buckets::where('id', $faqs->bucket_id)->update(['bucket_name' => $request->faq_name]);

        // FAQ名で、Buckets名も更新する
        //Log::debug($faqs->bucket_id);
        //Log::debug($request->faq_name);

        // 新規作成フラグを付けてFAQ設定変更画面を呼ぶ
        $create_flag = false;
        return $this->editBuckets($request, $page_id, $frame_id, $faqs_id, $create_flag, $message);
    }

    /**
     *  削除処理
     */
    public function destroyBuckets($request, $page_id, $frame_id, $faqs_id)
    {
        // faqs_id がある場合、データを削除
        if ($faqs_id) {
            // 記事データを削除する。
            FaqsPosts::where('faqs_id', $faqs_id)->delete();

            // カテゴリ削除
            Categories::destroyBucketsCategories($this->frame->plugin_name, $faqs_id);

            // FAQ設定を削除する。
            Faqs::destroy($faqs_id);

// Frame に紐づくFaq を削除した場合のみ、Frame の更新。（Frame に紐づかないFaq の削除もあるので、その場合はFrame は更新しない。）
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

        // 表示FAQ選択画面を呼ぶ
        return $this->listBuckets($request, $page_id, $frame_id, $id);
    }

    /**
     * カテゴリ表示関数
     */
    public function listCategories($request, $page_id, $frame_id, $id = null)
    {
        // FAQ
        $faq_frame = $this->getFaqFrame($frame_id);

        // 共通カテゴリ
        $general_categories = Categories::getGeneralCategories($this->frame->plugin_name, $faq_frame->faqs_id);

        // 個別カテゴリ（プラグイン）
        $plugin_categories = Categories::getPluginCategories($this->frame->plugin_name, $faq_frame->faqs_id);

        // 表示テンプレートを呼び出す。
        return $this->view('faqs_list_categories', [
            'general_categories' => $general_categories,
            'plugin_categories' => $plugin_categories,
            'faq_frame' => $faq_frame,
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

        // FAQ
        $faq_frame = $this->getFaqFrame($frame_id);

        Categories::savePluginCategories($request, $this->frame->plugin_name, $faq_frame->faqs_id);

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
        // FAQ＆フレームデータ
        $faq_frame = $this->getFaqFrame($frame_id);
        if (empty($faq_frame)) {
            return;
        }

        // サイト名
        $base_site_name = Configs::where('name', 'base_site_name')->first();

        // URL
        $url = url("/redirect/plugin/faqs/rss/" . $page_id . "/" . $frame_id);

        // HTTPヘッダー出力
        header('Content-Type: text/xml; charset=UTF-8');

        echo <<<EOD
<rss xmlns:content="http://purl.org/rss/1.0/modules/content/" version="2.0">
<channel>
<title>[{$base_site_name->value}]{$faq_frame->faq_name}</title>
<description></description>
<link>
{$url}
</link>
EOD;

        $faqs_posts = $this->getPosts($faq_frame, $faq_frame->rss_count);
        foreach ($faqs_posts as $faqs_post) {
            $title = $faqs_post->post_title;
            $link = url("/plugin/faqs/show/" . $page_id . "/" . $frame_id . "/" . $faqs_post->id);
            if (mb_strlen(strip_tags($faqs_post->post_text)) > 100) {
                $description = mb_substr(strip_tags($faqs_post->post_text), 0, 100) . "...";
                $replaceTarget = array('<br>', '&nbsp;', '&emsp;', '&ensp;');
                $description = str_replace($replaceTarget, '', $description);
            } else {
                $description = strip_tags($faqs_post->post_text);
                $replaceTarget = array('<br>', '&nbsp;', '&emsp;', '&ensp;');
                $description = str_replace($replaceTarget, '', $description);
            }
            $pub_date = date(DATE_RSS, strtotime($faqs_post->posted_at));
            $content = strip_tags(html_entity_decode($faqs_post->post_text));
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
}
