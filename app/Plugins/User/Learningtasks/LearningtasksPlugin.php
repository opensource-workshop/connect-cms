<?php

namespace App\Plugins\User\Learningtasks;

use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;

use DB;

use App\Models\Core\Configs;
use App\Models\Common\Buckets;
use App\Models\Common\Categories;
use App\Models\Common\Frame;
use App\Models\Common\Page;
use App\Models\Common\Uploads;
use App\Models\User\Learningtasks\Learningtasks;
use App\Models\User\Learningtasks\LearningtasksCategories;
use App\Models\User\Learningtasks\LearningtasksPosts;
use App\Models\User\Learningtasks\LearningtasksPostsTags;
use App\Models\User\Learningtasks\LearningtasksPostsFiles;
use App\Models\User\Learningtasks\LearningtasksUsersStatuses;

use App\Plugins\User\UserPluginBase;

/**
 * 課題管理プラグイン
 *
 * @author 永原　篤 <nagahara@opensource-workshop.jp>
 * @copyright OpenSource-WorkShop Co.,Ltd. All Rights Reserved
 * @category 課題管理プラグイン
 * @package Contoroller
 */
class LearningtasksPlugin extends UserPluginBase
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
        $functions['get']  = ['listCategories', 'rss', 'editBucketsRoles'];
        $functions['post'] = ['saveCategories', 'deleteCategories', 'saveBucketsRoles', 'changeStatus'];
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

        // deleteCategories の場合は、Learningtasks_posts のオブジェクトではないので、nullで返す。
        if ($action == 'deleteCategories') {
            return null;
        }

        // 一度読んでいれば、そのPOSTを再利用する。
        if (!empty($this->post)) {
            return $this->post;
        }

        // データのグループ（contents_id）が欲しいため、指定されたID のPOST を読む
        $arg_post = LearningtasksPosts::where('id', $id)->first();

        // 指定されたPOST ID そのままではなく、権限に応じたPOST を取得する。
        $this->post = LearningtasksPosts::select('learningtasks_posts.*',
                                          'categories.color as category_color',
                                          'categories.background_color as category_background_color',
                                          'categories.category as category')
                                ->leftJoin('categories', 'categories.id', '=', 'learningtasks_posts.categories_id')
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
     *  紐づく課題管理ID とフレームデータの取得
     */
    private function getLearningTaskFrame($frame_id)
    {
        // Frame データ
        $frame = DB::table('frames')
                 ->select('frames.*', 'learningtasks.id as learningtasks_id', 'learningtasks.learningtasks_name', 'learningtasks.view_count', 'learningtasks.rss', 'learningtasks.rss_count', 'learningtasks.sequence_conditions')
                 ->leftJoin('learningtasks', 'learningtasks.bucket_id', '=', 'frames.bucket_id')
                 ->where('frames.id', $frame_id)
                 ->first();
        return $frame;
    }

    /**
     *  カテゴリデータの取得
     */
    private function getLearningtasksCategories($learningtasks_id)
    {
        $learningtasks_categories = Categories::select('categories.*')
                          ->join('learningtasks_categories', function ($join) use($learningtasks_id) {
                              $join->on('learningtasks_categories.categories_id', '=', 'categories.id')
                                   ->where('learningtasks_categories.learningtasks_id', '=', $learningtasks_id)
                                   ->where('learningtasks_categories.view_flag', 1);
                          })
                          ->whereNull('plugin_id')
                          ->orWhere('plugin_id', $learningtasks_id)
                          ->orderBy('target', 'asc')
                          ->orderBy('display_sequence', 'asc')
                          ->get();
        return $learningtasks_categories;
    }

    /**
     *  課題管理記事チェック設定
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
                  ->orWhere('learningtasks_posts.created_id', '=', Auth::user()->id);
        }
        // その他（ゲスト）
        else {
            $query->where('status', 0);
            $query->where('learningtasks_posts.posted_at', '<=', Carbon::now());
        }

        return $query;
    }

    /**
     *  表示条件に対するソート条件追加
     */
    private function appendOrder($query, $learningtasks_frame)
    {
        // 最新順
        if ($learningtasks_frame->sequence_conditions == 0) {
            $query->orderBy('posted_at', 'desc');
        }
        // 投稿順
        elseif ($learningtasks_frame->sequence_conditions == 1) {
            $query->orderBy('posted_at', 'asc');
        }
        // 指定順
        elseif ($learningtasks_frame->sequence_conditions == 2) {
            $query->orderBy('display_sequence', 'asc');
        }

        return $query;
    }

    /**
     *  課題管理記事一覧取得
     */
    private function getPosts($learningtasks_frame, $option_count = null)
    {
        //$learningtasks_posts = null;

        // 件数
        $count = $learningtasks_frame->view_count;
        if ($option_count != null) {
            $count = $option_count;
        }

        // 削除されていないデータでグルーピングして、最新のIDで全件
        $learningtasks_posts = LearningtasksPosts::select('learningtasks_posts.*',
                                          'categories.id as category_id',
                                          'categories.color as category_color',
                                          'categories.background_color as category_background_color',
                                          'categories.category as category')
                                 ->leftJoin('categories', 'categories.id', '=', 'learningtasks_posts.categories_id')
                                 ->whereIn('learningtasks_posts.id', function($query) use($learningtasks_frame) {
                                     $query->select(DB::raw('MAX(id) As id'))
                                           ->from('learningtasks_posts')
                                           ->where('learningtasks_id', $learningtasks_frame->learningtasks_id)
                                           ->where('deleted_at', null)
                                           // 権限を見てWhere を付与する。
                                           ->where(function($query_auth){
                                               $query_auth = $this->appendAuthWhere($query_auth);
                                           })
                                           ->groupBy('categories.display_sequence')
                                           ->groupBy('contents_id');
                                   });
        // カテゴリソート条件追加
        $learningtasks_posts->orderBy('categories.display_sequence', 'asc');

        // 表示条件に対するソート条件追加

        // 最新順
        if ($learningtasks_frame->sequence_conditions == 0) {
            $learningtasks_posts->orderBy('posted_at', 'desc');
        }
        // 投稿順
        elseif ($learningtasks_frame->sequence_conditions == 1) {
            $learningtasks_posts->orderBy('posted_at', 'asc');
        }
        // 指定順
        elseif ($learningtasks_frame->sequence_conditions == 2) {
            $learningtasks_posts->orderBy('display_sequence', 'asc');
        }

       // 取得
       $learningtasks_posts_recored = $learningtasks_posts->orderBy('posted_at', 'desc')
                           ->paginate($count);

        return $learningtasks_posts_recored;
    }

    /**
     *  要承認の判断
     */
    private function isApproval($frame_id)
    {
        return $this->buckets->needApprovalUser(Auth::user());

//        // 承認の要否確認とステータス処理
//        $learningtasks_frame = $this->getLearningTaskFrame($frame_id);
//        if ($learningtasks_frame->approval_flag == 1) {
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
    private function saveTag($request, $learningtasks_post, $old_learningtasks_post)
    {
        // タグの保存
        if ($request->tags) {
            $tags = explode(',', $request->tags);
            foreach($tags as $tag) {

                // 新規オブジェクト生成
                $learningtasks_posts_tags = new LearningtasksPostsTags();

                // タグ登録
                $learningtasks_posts_tags->created_id     = $learningtasks_post->created_id;
                $learningtasks_posts_tags->learningtasks_posts_id = $learningtasks_post->id;
                $learningtasks_posts_tags->tags           = $tag;
                $learningtasks_posts_tags->save();
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
        $learningtasks_posts_tags = LearningtasksPostsTags::where('learningtasks_posts_id', $from_post->id)->orderBy('id', 'asc')->get();
        foreach($learningtasks_posts_tags as $learningtasks_posts_tag) {
            $new_tag = $learningtasks_posts_tag->replicate();
            $new_tag->learningtasks_posts_id = $to_post->id;
            $new_tag->save();
        }

        return;
    }

    /**
     *  課題ファイルの保存
     */
    private function saveTaskFile($request, $learningtasks_post, $old_learningtasks_post)
    {
        // 旧データがある場合は、履歴のためにコピーする。
        if (!empty($old_learningtasks_post) && !empty($old_learningtasks_post->id)) {
            $this->copyTaskFile($request, $old_learningtasks_post, $learningtasks_post);
        }

        // 課題ファイルがアップロードされた。
        if ($request->hasFile('add_task_file')) {

            // ファイルチェック
            $validator = Validator::make($request->all(), [
                'add_task_file' => 'required|mimes:pdf,doc,docx',
            ]);
            $validator->setAttributeNames([
                'add_task_file' => '課題ファイル',
            ]);
            if ($validator->fails()) {
                return ( $this->create($request, $page_id, $frame_id, $learningtasks_posts_id, $validator->errors()) );
            }

            // uploads テーブルに情報追加、ファイルのid を取得する
            $upload = Uploads::create([
                'client_original_name' => $request->file('add_task_file')->getClientOriginalName(),
                'mimetype'             => $request->file('add_task_file')->getClientMimeType(),
                'extension'            => $request->file('add_task_file')->getClientOriginalExtension(),
                'size'                 => $request->file('add_task_file')->getClientSize(),
                'plugin_name'          => 'learningtasks',
             ]);

            // learningtasks_posts_files テーブルに情報追加
            $learningtasks_posts_files = LearningtasksPostsFiles::create([
                'learningtasks_posts_id' => $learningtasks_post->id,
                'task_file_uploads_id'   => $upload->id,
             ]);

            // 課題ファイル保存
            $directory = $this->getDirectory($upload->id);
            $upload_path = $request->file('add_task_file')->storeAs($directory, $upload->id . '.' . $request->file('add_task_file')->getClientOriginalExtension());
        }
        return;
    }

    /**
     *  課題ファイル情報のコピー
     */
    private function copyTaskFile($request, $from_post, $to_post)
    {
        // 課題ファイル情報の保存
        $learningtasks_posts_files = LearningtasksPostsFiles::where('learningtasks_posts_id', $from_post->id)->orderBy('id', 'asc')->get();
        foreach($learningtasks_posts_files as $learningtasks_posts_file) {

            // 削除対象のファイルはデータをコピーしない
            if ($request->del_task_file) {
                if (array_key_exists($learningtasks_posts_file->id, $request->del_task_file)) {
                    continue;
                }
            }

            // レコードコピー
            $new_file = $learningtasks_posts_file->replicate();
            $new_file->learningtasks_posts_id = $to_post->id;
            $new_file->save();
        }

        return;
    }

    /**
     *  紐づく課題ファイルの取得
     */
    private function getTaskFile($posts_ids)
    {
        // 課題ファイルテーブル
        $posts_files_db
            = LearningtasksPostsFiles::select('learningtasks_posts_files.*',
                                              'uploads.id as uploads_id', 'uploads.client_original_name')
                 ->leftJoin('uploads', 'uploads.id', '=', 'learningtasks_posts_files.task_file_uploads_id')
                 ->whereIn('learningtasks_posts_files.learningtasks_posts_id', $posts_ids)
                 ->get();

        // 課題ファイル詰めなおし（課題管理データの一覧にあてるための外配列）
        $learningtasks_posts_files = array();
        foreach($posts_files_db as $record) {
            $learningtasks_posts_files[$record->learningtasks_posts_id][] = $record;
        }

        return $learningtasks_posts_files;
    }

    /**
     *  紐づくユーザーstatusの取得
     */
    private function getUserStatus($contents_ids)
    {
        // ユーザ
        $user = Auth::user();
        if (empty($user)) {
            return null;
        }

        // ユーザーstatusテーブル
        $users_statuses
            = LearningtasksUsersStatuses::whereIn('learningtasks_users_statuses.contents_id', $contents_ids)
                                        ->where('user_id', '=', $user->id)
                                        ->get();

        // ユーザーstatusテーブル詰めなおし（課題管理データの一覧にあてるための配列）
        $learningtasks_users_statuses = array();
        foreach($users_statuses as $record) {
            $learningtasks_users_statuses[$record->contents_id] = $record;
        }

        return $learningtasks_users_statuses;
    }

    /* スタティック関数 */

    /**
     *  新着情報用メソッド
     */
    public static function getWhatsnewArgs()
    {

        // 戻り値('sql_method'、'link_pattern'、'link_base')

        $return[] = DB::table('learningtasks_posts')
                      ->select('frames.page_id              as page_id',
                               'frames.id                   as frame_id',
                               'learningtasks_posts.id              as post_id',
                               'learningtasks_posts.post_title      as post_title',
                               'learningtasks_posts.important       as important',
                               'learningtasks_posts.posted_at       as posted_at',
                               'learningtasks_posts.created_name    as posted_name',
                               'categories.classname        as classname',
                               'categories.category         as category',
                               DB::raw('"learningtasks" as plugin_name')
                              )
                      ->join('learningtasks', 'learningtasks.id', '=', 'learningtasks_posts.learningtasks_id')
                      ->join('frames', 'frames.bucket_id', '=', 'learningtasks.bucket_id')
                      ->leftJoin('categories', 'categories.id', '=', 'learningtasks_posts.categories_id')
                      ->where('status', 0)
                      ->where('disable_whatsnews', 0)
                      ->whereNull('learningtasks_posts.deleted_at');

        $return[] = 'show_page_frame_post';
        $return[] = '/plugin/learningtasks/show';

        return $return;
    }

    /**
     *  検索用メソッド
     */
    public static function getSearchArgs($search_keyword)
    {
        $return[] = DB::table('learningtasks_posts')
                      ->select('learningtasks_posts.id              as post_id',
                               'frames.id                   as frame_id',
                               'frames.page_id              as page_id',
                               'pages.permanent_link        as permanent_link',
                               'learningtasks_posts.post_title      as post_title',
                               'learningtasks_posts.important       as important',
                               'learningtasks_posts.posted_at       as posted_at',
                               'learningtasks_posts.created_name    as posted_name',
                               'categories.classname        as classname',
                               'learningtasks_posts.categories_id   as categories_id',
                               'categories.category         as category',
                               DB::raw('"learningtasks" as plugin_name')
                              )
                      ->join('learningtasks', 'learningtasks.id', '=', 'learningtasks_posts.learningtasks_id')
                      ->join('frames', 'frames.bucket_id', '=', 'learningtasks.bucket_id')
                      ->leftJoin('categories', 'categories.id', '=', 'learningtasks_posts.categories_id')
                      ->leftjoin('pages', 'pages.id', '=', 'frames.page_id')
                      ->where('status', '?')
                      ->where(function($plugin_query) use($search_keyword) {
                          $plugin_query->where('learningtasks_posts.post_title', 'like', '?')
                                       ->orWhere('learningtasks_posts.post_text', 'like', '?');
                      })
                      ->whereNull('learningtasks_posts.deleted_at');


        $bind = array(0, '%'.$search_keyword.'%', '%'.$search_keyword.'%');
        $return[] = $bind;
        $return[] = 'show_page_frame_post';
        $return[] = '/plugin/learningtasks/show';

        return $return;
    }

    /* 画面アクション関数 */

    /**
     *  データ初期表示関数
     *  コアがページ表示の際に呼び出す関数
     */
    public function index($request, $page_id, $frame_id)
    {
        // 課題管理＆フレームデータ
        $learningtasks_frame = $this->getLearningTaskFrame($frame_id);
        if (empty($learningtasks_frame)) {
            return;
        }

        // Page データ
        $page = Page::where('id', $page_id)->first();

        // 認証されているユーザの取得
        $user = Auth::user();

        // 課題管理データ一覧の取得
        $learningtasks_posts = $this->getPosts($learningtasks_frame);

        // タグ：画面表示するデータのlearningtasks_posts_id を集める
        $posts_ids = array();
        foreach($learningtasks_posts as $learningtasks_post) {
            $posts_ids[] = $learningtasks_post->id;
        }

        // タグ：タグデータ取得
        $learningtasks_posts_tags_row = LearningtasksPostsTags::whereIn('learningtasks_posts_id', $posts_ids)->get();

        // タグ：タグデータ詰めなおし（課題管理データの一覧にあてるための外配列）
        $learningtasks_posts_tags = array();
        foreach($learningtasks_posts_tags_row as $record) {
            $learningtasks_posts_tags[$record->learningtasks_posts_id][] = $record->tags;
        }

        // タグ：タグデータをポストデータに紐づけ
        foreach($learningtasks_posts as &$learningtasks_post) {
            if (array_key_exists($learningtasks_post->id, $learningtasks_posts_tags)) {
                $learningtasks_post->tags = $learningtasks_posts_tags[$learningtasks_post->id];
            }
        }

        // 課題管理データを取得
        $learningtasks_posts_files = $this->getTaskFile($posts_ids);

        // 課題管理データをポストデータに紐づけ
        foreach($learningtasks_posts as &$learningtasks_post) {
            if (array_key_exists($learningtasks_post->id, $learningtasks_posts_files)) {
                $learningtasks_post->task_files = $learningtasks_posts_files[$learningtasks_post->id];
            }
        }

        // ユーザーstatus：画面表示するデータのcontents_id を集める
        $contents_ids = array();
        foreach($learningtasks_posts as $learningtasks_post) {
            $contents_ids[] = $learningtasks_post->contents_id;
        }

        // ユーザーstatusテーブルを取得
        $learningtasks_users_statuses = $this->getUserStatus($contents_ids);

        // ユーザーstatusテーブルをポストデータに紐づけ
        foreach($learningtasks_posts as &$learningtasks_post) {
            if ($learningtasks_users_statuses && array_key_exists($learningtasks_post->contents_id, $learningtasks_users_statuses)) {
                $learningtasks_post->user_task_status = $learningtasks_users_statuses[$learningtasks_post->contents_id]->task_status;
            }
        }

        // カテゴリごとにまとめる＆カテゴリの配列も作る
        $categories_and_posts = array();
        $categories = array();
        foreach($learningtasks_posts as $learningtasks_post) {
            $categories_and_posts[$learningtasks_post->categories_id][] = $learningtasks_post;
            $categories[$learningtasks_post->categories_id] = $learningtasks_post;
        }

        // 表示テンプレートを呼び出す。
        return $this->view(
            'learningtasks', [
            'learningtasks_posts'  => $learningtasks_posts,
            'categories_and_posts' => $categories_and_posts,
            'categories'           => $categories,
            'learningtasks_frame'  => $learningtasks_frame,
        ]);
    }

    /**
     *  新規記事画面
     */
    public function create($request, $page_id, $frame_id, $learningtasks_posts_id = null, $errors = null)
    {
        // セッション初期化などのLaravel 処理。
        $request->flash();

        // 課題管理＆フレームデータ
        $learningtasks_frame = $this->getLearningTaskFrame($frame_id);

        // 空のデータ(画面で初期値設定で使用するため)
        $learningtasks_posts = new LearningtasksPosts();
        $learningtasks_posts->posted_at = date('Y-m-d H:i:00');

        // カテゴリ
        $learningtasks_categories = $this->getLearningtasksCategories($learningtasks_frame->learningtasks_id);

        // タグ
        $learningtasks_posts_tags = "";

        // 表示テンプレートを呼び出す。(blade でold を使用するため、withInput 使用)
        return $this->view(
            'learningtasks_input', [
            'learningtasks_frame'       => $learningtasks_frame,
            'learningtasks_posts'      => $learningtasks_posts,
            'learningtasks_categories' => $learningtasks_categories,
            'learningtasks_posts_tags' => $learningtasks_posts_tags,
            'errors'           => $errors,
        ])->withInput($request->all);
    }

    /**
     *  詳細表示関数
     */
    public function show($request, $page_id, $frame_id, $learningtasks_posts_id = null)
    {
        // Frame データ
        $learningtasks_frame = $this->getLearningTaskFrame($frame_id);

        // 記事取得（指定されたPOST ID そのままではなく、権限に応じたPOST を取得する。）
        $learningtasks_post = $this->getPost($learningtasks_posts_id);
        if (empty($learningtasks_post)) {
            return $this->view_error("403_inframe", null, 'showのユーザー権限に応じたPOST ID チェック');
        }

        // タグ取得
        // タグ：タグデータ取得
        $learningtasks_post_tags = new LearningtasksPostsTags();
        if ($learningtasks_post) {
            $learningtasks_post_tags = LearningtasksPostsTags::where('learningtasks_posts_id', $learningtasks_post->id)->get();
        }

        // 課題管理データを取得
        $learningtasks_posts_files
            = LearningtasksPostsFiles::select('learningtasks_posts_files.*',
                                              'uploads.id as uploads_id', 'uploads.client_original_name')
                 ->leftJoin('uploads', 'uploads.id', '=', 'learningtasks_posts_files.task_file_uploads_id')
                 ->where('learningtasks_posts_id', $learningtasks_post->id)
                 ->get();

        // ひとつ前、ひとつ後の記事
        $before_post = null;
        $after_post = null;
        if ($learningtasks_post) {
            $before_post = LearningtasksPosts::where('learningtasks_id', $learningtasks_post->learningtasks_id)
                                     ->where('posted_at', '<', $learningtasks_post->posted_at)
                                     ->where(function($query){
                                         $query = $this->appendAuthWhere($query);
                                     })
                                     ->orderBy('posted_at', 'desc')
                                     ->first();
            $after_post = LearningtasksPosts::where('learningtasks_id', $learningtasks_post->learningtasks_id)
                                     ->where('posted_at', '>', $learningtasks_post->posted_at)
                                     ->where(function($query){
                                         $query = $this->appendAuthWhere($query);
                                     })
                                     ->orderBy('posted_at', 'asc')
                                     ->first();
        }

        // 詳細画面を呼び出す。
        return $this->view(
            'learningtasks_show', [
            'learningtasks_frame'  => $learningtasks_frame,
            'post'        => $learningtasks_post,
            'post_tags'   => $learningtasks_post_tags,
            'post_files'  => $learningtasks_posts_files,
            'before_post' => $before_post,
            'after_post'  => $after_post,
        ]);
    }

    /**
     * 記事編集画面
     */
    public function edit($request, $page_id, $frame_id, $learningtasks_posts_id = null, $errors = null)
    {
        // セッション初期化などのLaravel 処理。
        $request->flash();

        // Frame データ
        $learningtasks_frame = $this->getLearningTaskFrame($frame_id);

        // 記事取得（指定されたPOST ID そのままではなく、権限に応じたPOST を取得する。）
        $learningtasks_post = $this->getPost($learningtasks_posts_id);
        if (empty($learningtasks_post)) {
            return $this->view_error("403_inframe", null, 'editのユーザー権限に応じたPOST ID チェック');
        }

        // カテゴリ
        $learningtasks_categories = $this->getLearningtasksCategories($learningtasks_frame->learningtasks_id);

        // タグ取得
        $learningtasks_posts_tags_array = LearningtasksPostsTags::where('learningtasks_posts_id', $learningtasks_post->id)->get();
        $learningtasks_posts_tags = "";
        foreach($learningtasks_posts_tags_array as $learningtasks_posts_tags_item) {
            $learningtasks_posts_tags .= ',' . $learningtasks_posts_tags_item->tags;
        }
        $learningtasks_posts_tags = trim($learningtasks_posts_tags, ',');

        // 課題管理データを取得
        $learningtasks_posts_files = $this->getTaskFile([$learningtasks_post->id]);

        // 変更画面を呼び出す。(blade でold を使用するため、withInput 使用)
        return $this->view(
            'learningtasks_input', [
            'learningtasks_frame'       => $learningtasks_frame,
            'learningtasks_posts'       => $learningtasks_post,
            'learningtasks_categories'  => $learningtasks_categories,
            'learningtasks_posts_tags'  => $learningtasks_posts_tags,
            'learningtasks_posts_files' => (array_key_exists($learningtasks_post->id, $learningtasks_posts_files)) ? $learningtasks_posts_files[$learningtasks_post->id] : null,
            'errors'           => $errors,
        ])->withInput($request->all);
    }

    /**
     *  課題管理記事登録処理
     */
    public function save($request, $page_id, $frame_id, $learningtasks_posts_id = null)
    {
        // 項目のエラーチェック
        $validator = $this->makeValidator($request);

        // エラーがあった場合は入力画面に戻る。
        if ($validator->fails()) {
            if ($learningtasks_posts_id) {
                return ( $this->edit($request, $page_id, $frame_id, $learningtasks_posts_id, $validator->errors()) );
            }
            else {
                return ( $this->create($request, $page_id, $frame_id, $learningtasks_posts_id, $validator->errors()) );
            }
        }

        // id があれば旧データを取得＆権限を加味して更新可能データかどうかのチェック
        $old_learningtasks_post = null;
        if (!empty($learningtasks_posts_id)) {

            // 指定されたID のデータ
            $old_learningtasks_post = LearningtasksPosts::where('id', $learningtasks_posts_id)->first();

            // 記事取得（指定されたPOST ID そのままではなく、権限に応じたPOST を取得する。）
            $check_learningtasks_post = $this->getPost($learningtasks_posts_id);

            // 指定されたID と権限に応じたPOST のID が異なる場合は、キーを捏造したPOST と考えられるため、エラー
            if (empty($check_learningtasks_post) || $check_learningtasks_post->id != $old_learningtasks_post->id) {
                return $this->view_error("403_inframe", null, 'saveのユーザー権限に応じたPOST ID チェック');
            }
        }

        // 新規オブジェクト生成
        $learningtasks_post = new LearningtasksPosts();

        // 課題管理記事設定
        $learningtasks_post->learningtasks_id          = $request->learningtasks_id;
        $learningtasks_post->post_title       = $request->post_title;
        $learningtasks_post->categories_id    = $request->categories_id;
        $learningtasks_post->important        = $request->important;
        $learningtasks_post->posted_at        = $request->posted_at . ':00';
        $learningtasks_post->post_text        = $request->post_text;
        $learningtasks_post->display_sequence = intval(empty($request->display_sequence) ? 0 : $request->display_sequence);

        // 承認の要否確認とステータス処理
        if ($this->isApproval($frame_id)) {
            $learningtasks_post->status = 2;
        }

        // 新規
        if (empty($learningtasks_posts_id)) {

            // 登録ユーザ
            $learningtasks_post->created_id  = Auth::user()->id;

            // データ保存
            $learningtasks_post->save();

            // 新規登録の場合、contents_id を最初のレコードのid と同じにする。
            LearningtasksPosts::where('id', $learningtasks_post->id)->update(['contents_id' => $learningtasks_post->id]);
        }
        // 更新
        else {

            // 変更処理の場合、contents_id を旧レコードのcontents_id と同じにする。
            $learningtasks_post->contents_id = $old_learningtasks_post->contents_id;

            // 登録ユーザ
            $learningtasks_post->created_id  = $old_learningtasks_post->created_id;

            // 旧レコードのstatus 更新(Activeなもの(status:0)は、status:9 に更新。他はそのまま。)ただし、承認待ちレコード作成時は対象外
            if ($learningtasks_post->status != 2) {
                LearningtasksPosts::where('contents_id', $old_learningtasks_post->contents_id)->where('status', 0)->update(['status' => 9]);
            }

            // データ保存
            $learningtasks_post->save();

        }

        // タグの保存
        $this->saveTag($request, $learningtasks_post, $old_learningtasks_post);

        // 課題ファイルの保存
        $this->saveTaskFile($request, $learningtasks_post, $old_learningtasks_post);

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
            $learningtasks_post = new LearningtasksPosts();

            // 登録ユーザ
            $learningtasks_post->created_id  = Auth::user()->id;
        }
        else {
            $learningtasks_post = LearningtasksPosts::find($id)->replicate();
 
            // チェック用に記事取得（指定されたPOST ID そのままではなく、権限に応じたPOST を取得する。）
            $check_learningtasks_post = $this->getPost($id);

            // 指定されたID と権限に応じたPOST のID が異なる場合は、キーを捏造したPOST と考えられるため、エラー
            if (empty($check_learningtasks_post) || $check_learningtasks_post->id != $id) {
                return $this->view_error("403_inframe", null, 'temporarysaveのユーザー権限に応じたPOST ID チェック');
            }
       }

        // 課題管理記事設定
        $learningtasks_post->status = 1;
        $learningtasks_post->learningtasks_id          = $request->learningtasks_id;
        $learningtasks_post->post_title       = $request->post_title;
        $learningtasks_post->important        = $request->important;
        $learningtasks_post->posted_at        = $request->posted_at . ':00';
        $learningtasks_post->post_text        = $request->post_text;
        $learningtasks_post->display_sequence = intval(empty($request->display_sequence) ? 0 : $request->display_sequence);

        $learningtasks_post->save();

        if (empty($id)) {

            // 新規登録の場合、contents_id を最初のレコードのid と同じにする。
            LearningtasksPosts::where('id', $learningtasks_post->id)->update(['contents_id' => $learningtasks_post->id]);
        }

        // タグの保存
        $this->saveTag($request, $learningtasks_post, $old_learningtasks_post);

        // 課題ファイルの保存
        $this->saveTaskFile($request, $learningtasks_post, $old_learningtasks_post);

        // 登録後は表示用の初期処理を呼ぶ。
        return $this->index($request, $page_id, $frame_id);
    }

    /**
     *  削除処理
     */
    public function delete($request, $page_id, $frame_id, $learningtasks_posts_id)
    {
        // id がある場合、データを削除
        if ( $learningtasks_posts_id ) {

            // 同じcontents_id のデータを削除するため、一旦、対象データを取得
            $post = LearningtasksPosts::where('id', $learningtasks_posts_id)->first();

            // 削除ユーザ、削除日を設定する。（複数レコード更新のため、自動的には入らない）
            LearningtasksPosts::where('contents_id', $post->contents_id)->update(['deleted_id' => Auth::user()->id, 'deleted_name' => Auth::user()->name]);

            // データを削除する。
            LearningtasksPosts::where('contents_id', $post->contents_id)->delete();
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
        $learningtasks_post = LearningtasksPosts::find($id)->replicate();

        // チェック用に記事取得（指定されたPOST ID そのままではなく、権限に応じたPOST を取得する。）
        $check_learningtasks_post = $this->getPost($id);

        // 指定されたID と権限に応じたPOST のID が異なる場合は、キーを捏造したPOST と考えられるため、エラー
        if (empty($check_learningtasks_post) || $check_learningtasks_post->id != $id) {
            return $this->view_error("403_inframe", null, 'approvalのユーザー権限に応じたPOST ID チェック');
        }

        // 旧レコードのstatus 更新(Activeなもの(status:0)は、status:9 に更新。他はそのまま。)
        LearningtasksPosts::where('contents_id', $learningtasks_post->contents_id)->where('status', 0)->update(['status' => 9]);

        // 課題管理記事設定
        $learningtasks_post->status = 0;
        $learningtasks_post->save();

        // タグもコピー
        $this->copyTag($check_learningtasks_post, $learningtasks_post);

        // 課題ファイル情報もコピー
        $this->copyTaskFile($request, $check_learningtasks_post, $learningtasks_post);

        // 登録後は表示用の初期処理を呼ぶ。
        return $this->index($request, $page_id, $frame_id);
    }

    /**
     * データ選択表示関数
     */
    public function listBuckets($request, $page_id, $frame_id, $id = null)
    {
        // Frame データ
        $learningtasks_frame = DB::table('frames')
                      ->select('frames.*', 'learningtasks.id as learningtasks_id', 'learningtasks.view_count')
                      ->leftJoin('learningtasks', 'learningtasks.bucket_id', '=', 'frames.bucket_id')
                      ->where('frames.id', $frame_id)->first();

        // データ取得（1ページの表示件数指定）
        $learningtasks = Learningtasks::orderBy('created_at', 'desc')
                       ->paginate(10);

        // 表示テンプレートを呼び出す。
        return $this->view(
            'learningtasks_list_buckets', [
            'learningtasks_frame' => $learningtasks_frame,
            'learningtasks'      => $learningtasks,
        ]);
    }

    /**
     * 課題管理新規作成画面
     */
    public function createBuckets($request, $page_id, $frame_id, $learningtasks_id = null, $create_flag = false, $message = null, $errors = null)
    {
        // 新規作成フラグを付けて課題管理設定変更画面を呼ぶ
        $create_flag = true;
        return $this->editBuckets($request, $page_id, $frame_id, $learningtasks_id, $create_flag, $message, $errors);
    }

    /**
     * 課題管理設定変更画面の表示
     */
    public function editBuckets($request, $page_id, $frame_id, $learningtasks_id = null, $create_flag = false, $message = null, $errors = null)
    {
        // セッション初期化などのLaravel 処理。
        $request->flash();

        // 課題管理＆フレームデータ
        $learningtasks_frame = $this->getLearningTaskFrame($frame_id);

        // 課題管理データ
        $learningtasks = new Learningtasks();

        // learningtasks_id が渡ってくればlearningtasks_id が対象
        if (!empty($learningtasks_id)) {
            $learningtasks = Learningtasks::where('id', $learningtasks_id)->first();
        }
        // Frame のbucket_id があれば、bucket_id から課題管理データ取得、なければ、新規作成か選択へ誘導
        else if (!empty($learningtasks_frame->bucket_id) && $create_flag == false) {
            $learningtasks = Learningtasks::where('bucket_id', $learningtasks_frame->bucket_id)->first();
        }

        // 表示テンプレートを呼び出す。
        return $this->view(
            'learningtasks_edit_learningtasks', [
            'learningtasks_frame'  => $learningtasks_frame,
            'learningtasks'        => $learningtasks,
            'create_flag' => $create_flag,
            'message'     => $message,
            'errors'      => $errors,
        ])->withInput($request->all);
    }

    /**
     *  課題管理登録処理
     */
    public function saveBuckets($request, $page_id, $frame_id, $learningtasks_id = null)
    {
        // 項目のエラーチェック
        $validator = Validator::make($request->all(), [
            'learningtasks_name'            => ['required'],
            'view_count'          => ['required'],
            'view_count'          => ['numeric'],
            'rss_count'           => ['nullable', 'numeric'],
            'sequence_conditions' => ['nullable', 'numeric'],
        ]);
        $validator->setAttributeNames([
            'learningtasks_name'            => '課題管理名',
            'view_count'          => '表示件数',
            'rss_count'           => 'RSS件数',
            'sequence_conditions' => '順序条件',
        ]);

        // エラーがあった場合は入力画面に戻る。
        $message = null;
        if ($validator->fails()) {

            if (empty($learningtasks_id)) {
                $create_flag = true;
                return $this->createBuckets($request, $page_id, $frame_id, $learningtasks_id, $create_flag, $message, $validator->errors());
            }
            else  {
                $create_flag = false;
                return $this->editBuckets($request, $page_id, $frame_id, $learningtasks_id, $create_flag, $message, $validator->errors());
            }
        }

        // 更新後のメッセージ
        $message = null;

        // 画面から渡ってくるlearningtasks_id が空ならバケツと課題管理を新規登録
        if (empty($request->learningtasks_id)) {

            // バケツの登録
            $bucket_id = DB::table('buckets')->insertGetId([
                  'bucket_name' => $request->learningtasks_name,
                  'plugin_name' => 'learningtasks'
            ]);

            // 課題管理データ新規オブジェクト
            $learningtasks = new Learningtasks();
            $learningtasks->bucket_id = $bucket_id;

            // Frame のBuckets を見て、Buckets が設定されていなければ、作成したものに紐づける。
            // Frame にBuckets が設定されていない ＞ 新規のフレーム＆課題管理作成
            // Frame にBuckets が設定されている ＞ 既存のフレーム＆課題管理更新
            // （表示課題管理選択から遷移してきて、内容だけ更新して、フレームに紐づけないケースもあるため）
            $frame = Frame::where('id', $frame_id)->first();
            if (empty($frame->bucket_id)) {

                // FrameのバケツIDの更新
                $frame = Frame::where('id', $frame_id)->update(['bucket_id' => $bucket_id]);
            }

            $message = '課題管理設定を追加しました。';
        }
        // learningtasks_id があれば、課題管理を更新
        else {

            // 課題管理データ取得
            $learningtasks = Learningtasks::where('id', $request->learningtasks_id)->first();

            $message = '課題管理設定を変更しました。';
        }

        // 課題管理設定
        $learningtasks->learningtasks_name            = $request->learningtasks_name;
        $learningtasks->view_count          = $request->view_count;
        $learningtasks->rss                 = $request->rss;
        $learningtasks->rss_count           = $request->rss_count;
        $learningtasks->sequence_conditions = intval($request->sequence_conditions);
        //$learningtasks->approval_flag = $request->approval_flag;

        // データ保存
        $learningtasks->save();

        // 課題管理名で、Buckets名も更新する
        Buckets::where('id', $learningtasks->bucket_id)->update(['bucket_name' => $request->learningtasks_name]);

        // 課題管理名で、Buckets名も更新する
        //Log::debug($learningtasks->bucket_id);
        //Log::debug($request->learningtasks_name);

        // 新規作成フラグを付けて課題管理設定変更画面を呼ぶ
        $create_flag = false;
        return $this->editBuckets($request, $page_id, $frame_id, $learningtasks_id, $create_flag, $message);
    }

    /**
     *  削除処理
     */
    public function destroyBuckets($request, $page_id, $frame_id, $learningtasks_id)
    {
        // learningtasks_id がある場合、データを削除
        if ( $learningtasks_id ) {

            // 記事データを削除する。
            LearningtasksPosts::where('learningtasks_id', $learningtasks_id)->delete();

            // 課題管理設定を削除する。
            Learningtasks::destroy($learningtasks_id);

// Frame に紐づくLearningTask を削除した場合のみ、Frame の更新。（Frame に紐づかないLearningTask の削除もあるので、その場合はFrame は更新しない。）
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

        // 表示課題管理選択画面を呼ぶ
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

        // 課題管理
        $learningtasks_frame = $this->getLearningTaskFrame($frame_id);

        // カテゴリ（全体）
        $general_categories = Categories::select('categories.*', 'learningtasks_categories.id as learningtasks_categories_id', 'learningtasks_categories.categories_id', 'learningtasks_categories.view_flag')
                                        ->leftJoin('learningtasks_categories', function ($join) use($learningtasks_frame) {
                                            $join->on('learningtasks_categories.categories_id', '=', 'categories.id')
                                                 ->where('learningtasks_categories.learningtasks_id', '=', $learningtasks_frame->learningtasks_id);
                                        })
                                        ->where('target', null)
                                        ->orderBy('display_sequence', 'asc')
                                        ->get();
        // カテゴリ（この課題管理）
        $plugin_categories = null;
        if ($learningtasks_frame->learningtasks_id) {
            $plugin_categories = Categories::select('categories.*', 'learningtasks_categories.id as learningtasks_categories_id', 'learningtasks_categories.categories_id', 'learningtasks_categories.view_flag')
                                           ->leftJoin('learningtasks_categories', 'learningtasks_categories.categories_id', '=', 'categories.id')
                                           ->where('target', 'learningtasks')
                                           ->where('plugin_id', $learningtasks_frame->learningtasks_id)
                                           ->orderBy('display_sequence', 'asc')
                                           ->get();
        }

        // 表示テンプレートを呼び出す。
        return $this->view(
            'learningtasks_list_categories', [
            'general_categories' => $general_categories,
            'plugin_categories'  => $plugin_categories,
            'learningtasks_frame'         => $learningtasks_frame,
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
        if (!empty($request->learningtasks_categories_id)) {
            foreach($request->learningtasks_categories_id as $category_id) {

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

        // 課題管理
        $learningtasks_frame = $this->getLearningTaskFrame($frame_id);

        // 追加項目アリ
        if (!empty($request->add_display_sequence)) {
            $add_category = Categories::create([
                                'classname'        => $request->add_classname,
                                'category'         => $request->add_category,
                                'color'            => $request->add_color,
                                'background_color' => $request->add_background_color,
                                'target'           => 'learningtasks',
                                'plugin_id'        => $learningtasks_frame->learningtasks_id,
                                'display_sequence' => intval($request->add_display_sequence),
                             ]);
            LearningtasksCategories::create([
                                'learningtasks_id'         => $learningtasks_frame->learningtasks_id,
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
                $category->target           = 'learningtasks';
                $category->plugin_id        = $learningtasks_frame->learningtasks_id;
                $category->display_sequence = $request->plugin_display_sequence[$plugin_categories_id];

                // 保存
                $category->save();
            }
        }

        /* 表示フラグ更新(共通カテゴリ)
        ------------------------------------ */
        if (!empty($request->general_categories_id)) {
            foreach($request->general_categories_id as $general_categories_id) {

                // 課題管理プラグインのカテゴリー使用テーブルになければ追加、あれば更新
                LearningtasksCategories::updateOrCreate(
                    ['categories_id' => $general_categories_id, 'learningtasks_id' => $learningtasks_frame->learningtasks_id],
                    [
                     'learningtasks_id' => $learningtasks_frame->learningtasks_id,
                     'categories_id' => $general_categories_id,
                     'view_flag' => (isset($request->general_view_flag[$general_categories_id]) && $request->general_view_flag[$general_categories_id] == '1') ? 1 : 0,
                     'display_sequence' => $request->general_display_sequence[$general_categories_id],
                    ]
                );
            }
        }

        /* 表示フラグ更新(自課題管理のカテゴリ)
        ------------------------------------ */
        if (!empty($request->plugin_categories_id)) {
            foreach($request->plugin_categories_id as $plugin_categories_id) {

                // 課題管理プラグインのカテゴリー使用テーブルになければ追加、あれば更新
                LearningtasksCategories::updateOrCreate(
                    ['categories_id' => $plugin_categories_id, 'learningtasks_id' => $learningtasks_frame->learningtasks_id],
                    [
                     'learningtasks_id' => $learningtasks_frame->learningtasks_id,
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

        // 削除(課題管理プラグインのカテゴリ表示データ)
        LearningtasksCategories::where('categories_id', $id)->delete();

        // 削除(カテゴリ)
        Categories::where('id', $id)->delete();

        return $this->listCategories($request, $page_id, $frame_id, $id, null, true);
    }

    /**
     *  進捗ステータス更新
     */
    public function changeStatus($request, $page_id, $frame_id, $id = null)
    {
        // 権限チェック（deleteCategories 関数は標準チェックにないので、独自チェック）
        $user = Auth::user();
        if (empty($user)) {
            return $this->view_error("403_inframe", null, "ログインしないとできない処理です。");
        }

        // ユーザーの進捗ステータス
        LearningtasksUsersStatuses::updateOrCreate(
            ['contents_id' => $id, 'user_id' => $user->id],
            [
             'contents_id' => $id,
             'user_id' => $user->id,
             'task_status' => $request->task_status,
            ]
        );

        return $this->index($request, $page_id, $frame_id);
    }

    /**
     *  RSS配信
     */
    public function rss($request, $page_id, $frame_id, $id = null)
    {
        // 課題管理＆フレームデータ
        $learningtasks_frame = $this->getLearningTaskFrame($frame_id);
        if (empty($learningtasks_frame)) {
            return;
        }

        // サイト名
        $base_site_name = Configs::where('name', 'base_site_name')->first();

        // URL
        $url = url("/redirect/plugin/learningtasks/rss/" . $page_id . "/" . $frame_id);

        // HTTPヘッダー出力
        header('Content-Type: text/xml; charset=UTF-8');

echo <<<EOD
<rss xmlns:content="http://purl.org/rss/1.0/modules/content/" version="2.0">
<channel>
<title>[{$base_site_name->value}]{$learningtasks_frame->learningtasks_name}</title>
<description></description>
<link>
{$url}
</link>
EOD;

        $learningtasks_posts = $this->getPosts($learningtasks_frame, $learningtasks_frame->rss_count);
        foreach ($learningtasks_posts as $learningtasks_post) {

            $title = $learningtasks_post->post_title;
            $link = url("/plugin/learningtasks/show/" . $page_id . "/" . $frame_id . "/" . $learningtasks_post->id);
            if (mb_strlen(strip_tags($learningtasks_post->post_text)) > 100) {
                $description = mb_substr(strip_tags($learningtasks_post->post_text), 0, 100) . "...";
                $replaceTarget = array('<br>', '&nbsp;', '&emsp;', '&ensp;');
                $description = str_replace($replaceTarget, '', $description);
            }
            else {
                $description = strip_tags($learningtasks_post->post_text);
                $replaceTarget = array('<br>', '&nbsp;', '&emsp;', '&ensp;');
                $description = str_replace($replaceTarget, '', $description);
            }
            $pub_date = date(DATE_RSS, strtotime($learningtasks_post->posted_at));
            $content = strip_tags(html_entity_decode($learningtasks_post->post_text));
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
