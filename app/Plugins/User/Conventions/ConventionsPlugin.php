<?php

namespace App\Plugins\User\Conventions;

use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;

use App\Models\Common\Buckets;
use App\Models\Common\Frame;
use App\Models\User\Conventions\Convention;
use App\Models\User\Conventions\ConventionFrame;
use App\Models\User\Conventions\ConventionJoin;
use App\Models\User\Conventions\ConventionPost;

use App\Plugins\User\Conventions\ConventionsTool;

use App\Plugins\User\UserPluginBase;

/**
 * イベント管理・プラグイン
 *
 * @author 永原　篤 <nagahara@opensource-workshop.jp>
 * @copyright OpenSource-WorkShop Co.,Ltd. All Rights Reserved
 * @category イベント管理・プラグイン
 * @package Controller
 */
class ConventionsPlugin extends UserPluginBase
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
        $functions['get']  = [];
        $functions['post'] = ['join', 'joinOff'];
        return $functions;
    }

    /**
     *  権限定義
     */
    public function declareRole()
    {
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
        $this->post = ConventionPost::firstOrNew(['id' => $id]);
        return $this->post;
    }

    /* private関数 */

    /**
     * プラグインのフレーム
     */
    private function getPluginFrame($frame_id)
    {
        return ConventionFrame::firstOrNew(['frame_id' => $frame_id]);
    }

    /**
     * プラグインのバケツ取得関数
     */
    private function getPluginBucket($bucket_id)
    {
        // プラグインのメインデータを取得する。
        return Convention::firstOrNew(['bucket_id' => $bucket_id]);
    }

    /**
     *  POST一覧取得
     */
    private function getPosts($convention_frame)
    {
        // データ取得
        $posts_query = ConventionPost::select('convention_posts.*')
                                   ->join('conventions', function ($join) {
                                       $join->on('conventions.id', '=', 'convention_posts.convention_id')
                                          ->where('conventions.bucket_id', '=', $this->frame->bucket_id);
                                   })
                                   ->whereNull('convention_posts.deleted_at')
                                   ->orderBy('display_sequence', 'asc')
                                   ->orderBy('created_at', 'asc');

        // 取得
        return $posts_query->get();
    }

    /**
     *  参加者取得
     */
    private function getJoins($convention_frame, $post_id, $user_id)
    {
        // データ取得
        $joins_query = ConventionJoin::select('convention_joins.*', 'users.name')
                                      ->join('convention_posts', function ($join) use ($convention_frame) {
                                          $join->on('convention_posts.id', '=', 'convention_joins.post_id')
                                               ->where('convention_posts.convention_id', '=', $convention_frame->convention_id);
                                      })
                                      ->leftJoin('users', 'users.id', '=', 'convention_joins.user_id');

        if (!empty($user_id)) {
            $joins_query->where('convention_joins.user_id', $user_id);
        }

        if (!empty($post_id)) {
            $joins_query->where('convention_joins.post_id', $post_id);
        }

        $joins_query->whereNull('convention_joins.deleted_at')
            ->whereNull('convention_posts.deleted_at')
            ->orderBy('created_at', 'asc');

        // 取得
        return $joins_query->get();
    }

    /* スタティック関数 */

    /**
     *  新着情報用メソッド
     */
    public static function getWhatsnewArgs()
    {
        // 戻り値('sql_method'、'link_pattern'、'link_base')
        $return[] = DB::table('conventions_posts')
                      ->select(
                          'frames.page_id               as page_id',
                          'frames.id                    as frame_id',
                          'conventions_posts.id              as post_id',
                          'conventions_posts.title           as post_title',
                          DB::raw("null                 as important"),
                          'conventions_posts.created_at      as posted_at',
                          'conventions_posts.created_name    as posted_name',
                          DB::raw("null                 as classname"),
                          DB::raw("null                 as category"),
                          DB::raw('"conventions"             as plugin_name')
                      )
                      ->join('conventions', 'conventions.id', '=', 'conventions_posts.conventions_id')
                      ->join('frames', 'frames.bucket_id', '=', 'conventions.bucket_id')
                      ->where('frames.disable_whatsnews', 0)
                      ->whereNull('conventions_posts.deleted_at');

        $return[] = 'show_page_frame_post';
        $return[] = '/plugin/conventions/show';

        return $return;
    }

    /**
     *  検索用メソッド
     */
/*
    public static function getSearchArgs($search_keyword)
    {
        $return[] = DB::table('conventions_posts')
                      ->select(
                          'conventions_posts.id           as post_id',
                          'frames.id                 as frame_id',
                          'frames.page_id            as page_id',
                          'pages.permanent_link      as permanent_link',
                          'conventions_posts.title        as post_title',
                          DB::raw("null              as important"),
                          'conventions_posts.created_at   as posted_at',
                          'conventions_posts.created_name as posted_name',
                          DB::raw("null              as classname"),
                          DB::raw("null              as category_id"),
                          DB::raw("null              as category"),
                          DB::raw('"conventions"          as plugin_name')
                      )
                      ->join('conventions', 'conventions.id',  '=', 'conventions_posts.conventions_id')
                      ->join('frames', 'frames.bucket_id', '=', 'conventions.bucket_id')
                      ->leftjoin('pages', 'pages.id',      '=', 'frames.page_id')
                      ->where(function ($plugin_query) use ($search_keyword) {
                          $plugin_query->where('conventions_posts.title', 'like', '?')
                                       ->orWhere('conventions_posts.url', 'like', '?');
                      })
                      ->whereNull('conventions_posts.deleted_at');


        $bind = array('%'.$search_keyword.'%', '%'.$search_keyword.'%');
        $return[] = $bind;
        $return[] = 'show_page_frame_post';
        $return[] = '/plugin/conventions/show';

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
        // プラグインの主データ
        $convention = $this->getPluginBucket($this->frame->bucket_id);

        // プラグインのフレームデータ
        $plugin_frame = $this->getPluginFrame($frame_id);

        // イベントデータ一覧の取得
        $posts = $this->getPosts($plugin_frame);

        // ログインしているユーザ
        $user = Auth::user();
        $user_id = empty($user) ? null : $user->id;

        // 参加者一覧の取得
        $joins = $this->getJoins($plugin_frame, null, $user_id);

        // 表示テンプレートを呼び出す。
        return $this->view('index', [
            'convention'   => $convention,
            'posts'        => $posts,
            'plugin_frame' => $plugin_frame,
            'tool'         => new ConventionsTool($request, $page_id, $frame_id, $convention, $posts, $joins),
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
        $post = $this->getPost($post_id);

        // プラグインのフレームデータ
        $plugin_frame = $this->getPluginFrame($frame_id);

        // 参加者一覧の取得
        $joins = $this->getJoins($plugin_frame, $post_id, null);

        // 変更画面を呼び出す。
        return $this->view('edit', [
            'post'   => $post,
            'track'  => !empty($request->track) ? $request->track : $post->track,
            'period' => !empty($request->period) ? $request->period : $post->period,
            'joins'  => $joins,
        ]);
    }

    /**
     *  記事登録処理
     */
    public function save($request, $page_id, $frame_id, $post_id = null)
    {
        // 項目のエラーチェック
        $validator = Validator::make($request->all(), [
            'track'            => ['required', 'numeric'],
            'period'           => ['required', 'numeric'],
            'title'            => ['required'],
            'display_sequence' => ['nullable', 'numeric'],
        ]);
        $validator->setAttributeNames([
            'track'            => 'トラックNo',
            'period'           => 'コマNo',
            'title'            => 'タイトル',
            'display_sequence' => '表示順',
        ]);

        // エラーがあった場合は入力画面に戻る。
        if ($validator->fails()) {
            return back()->withErrors($validator)->withInput();
        }

        // POSTデータのモデル取得
        $post = ConventionPost::firstOrNew(['id' => $post_id]);

        // フレームから convention_id 取得
        $convention_frame = $this->getPluginFrame($frame_id);

        // 表示順が空なら、自分を省いた最後の番号+1 をセット
        if ($request->filled('display_sequence')) {
            $display_sequence = intval($request->display_sequence);
        } else {
            $max_display_sequence = ConventionPost::where('convention_id', $convention_frame->convention_id)->where('id', '<>', $post_id)->max('display_sequence');
            $display_sequence = empty($max_display_sequence) ? 1 : $max_display_sequence + 1;
        }

        // 値のセット
        $post->convention_id     = $convention_frame->convention_id;
        $post->track             = $request->track;
        $post->period            = $request->period;
        $post->title             = $request->title;
        $post->url               = $request->url;
        $post->description       = $request->description;
        $post->display_sequence  = $display_sequence;

        // データ保存
        $post->save();

        // 登録後はリダイレクトして編集画面を開く。
        return new Collection(['redirect_path' => url('/') . "/plugin/conventions/edit/" . $page_id . "/" . $frame_id . "/" . $post->id . "#frame-" . $frame_id]);
    }

    /**
     *  削除処理
     */
    public function delete($request, $page_id, $frame_id, $post_id)
    {
        // id がある場合、データを削除
        if ($post_id) {
            // データを削除する。
            ConventionPost::where('id', $post_id)->delete();
        }
        return;
    }

    /**
     *  参加処理
     */
    public function join($request, $page_id, $frame_id, $post_id)
    {
        // ログインしているユーザ
        $user = Auth::user();

        $convention_join = ConventionJoin::updateOrCreate(
            ['post_id'   => $post_id,
             'user_id'   => $user->id],
            ['post_id'   => $post_id,
             'user_id'   => $user->id,
             'join_flag' => $request->join_flag],
        );
    }

    /**
     *  参加取り消し処理
     */
    public function joinOff($request, $page_id, $frame_id, $post_id)
    {
        // ログインしているユーザ
        $user = Auth::user();
        ConventionJoin::where('post_id', $post_id)->where('user_id', $user->id)->delete();
    }

    /**
     * プラグインのバケツ選択表示関数
     */
    public function listBuckets($request, $page_id, $frame_id, $id = null)
    {
        // 表示テンプレートを呼び出す。
        return $this->view('list_buckets', [
            'plugin_buckets' => Convention::orderBy('created_at', 'desc')->paginate(10, ["*"], "frame_{$frame_id}_page"),
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

    // delete: イベントの総コマ数は設定の トラック数 x コマ数 で決まるため、表示件数を使って絞るとまずいため、削除
    /**
     * フレーム表示設定画面の表示
     */
    // public function editView($request, $page_id, $frame_id)
    // {
    //     // 表示テンプレートを呼び出す。
    //     return $this->view('frame', [
    //         // 表示中のバケツデータ
    //         'convention'       => $this->getPluginBucket($this->getBucketId()),
    //         'convention_frame' => $this->getPluginFrame($frame_id),
    //     ]);
    // }
    //
    /**
     * フレーム表示設定の保存
     */
    // public function saveView($request, $page_id, $frame_id, $convention_id)
    // {
    //     // 項目のエラーチェック
    //     $validator = Validator::make($request->all(), [
    //         'view_count' => ['nullable', 'numeric'],
    //     ]);
    //     $validator->setAttributeNames([
    //         'view_count' => '表示件数',
    //     ]);

    //     // エラーがあった場合は入力画面に戻る。
    //     if ($validator->fails()) {
    //         return back()->withErrors($validator)->withInput();
    //     }

    //     // フレームごとの表示設定の更新
    //     $convention_frame = ConventionFrame::updateOrCreate(
    //         ['convention_id'   => $convention_id, 'frame_id' => $frame_id],
    //         ['view_count'      => $request->view_count],
    //     );

    //     return;
    // }

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
            'convention' => $this->getPluginBucket($bucket_id),
        ]);
    }

    /**
     *  バケツ登録処理
     */
    public function saveBuckets($request, $page_id, $frame_id, $bucket_id = null)
    {
        // 項目のエラーチェック
        $validator = Validator::make($request->all(), [
            'name'         => ['required'],
            'track_count'  => ['required', 'numeric'],
            'period_count' => ['required', 'numeric'],
        ]);
        $validator->setAttributeNames([
            'name'         => 'イベント名',
            'track_count'  => 'トラック数',
            'period_count' => 'コマ数',
        ]);

        // エラーがあった場合は入力画面に戻る。
        if ($validator->fails()) {
            return back()->withErrors($validator)->withInput();
        }

        // バケツの取得。なければ登録。
        $bucket = Buckets::updateOrCreate(
            ['id' => $bucket_id],
            ['bucket_name' => $request->name, 'plugin_name' => 'conventions'],
        );

        // フレームにバケツの紐づけ
        $frame = Frame::find($frame_id)->update(['bucket_id' => $bucket->id]);

        // プラグインバケツを取得(なければ新規オブジェクト)
        // プラグインバケツにデータを設定して保存
        $convention = $this->getPluginBucket($bucket->id);
        $convention->name         = $request->name;
        $convention->track_count  = $request->track_count;
        $convention->period_count = $request->period_count;
        $convention->period_label = $request->period_label;
        $convention->save();

        // プラグインフレームを作成 or 更新
        $convention_frame = ConventionFrame::updateOrCreate(
            ['frame_id' => $frame_id],
            ['convention_id' => $convention->id, 'frame_id' => $frame_id],
        );

        // 登録後はリダイレクトして編集ページを開く。
        return new Collection(['redirect_path' => url('/') . "/plugin/conventions/editBuckets/" . $page_id . "/" . $frame_id . "/" . $bucket->id . "#frame-" . $frame_id]);
    }

    /**
     *  削除処理
     */
    public function destroyBuckets($request, $page_id, $frame_id, $convention_id)
    {
        // プラグインバケツの取得
        $convention = Convention::find($convention_id);
        if (empty($convention)) {
            return;
        }

        // POSTデータ削除(一気にDelete なので、deleted_id は入らない)
        ConventionPost::where('convention_id', $convention->id)->delete();

        // FrameのバケツIDの更新
        Frame::where('id', $frame_id)->update(['bucket_id' => null]);

        // プラグインフレームデータの削除(deleted_id を記録するために1回読んでから削除)
        $convention_frame = ConventionFrame::where('frame_id', $frame_id)->first();
        $convention_frame->delete();

        // バケツ削除
        Buckets::find($convention->bucket_id)->delete();

        // プラグインデータ削除
        $convention->delete();

        return;
    }

   /**
    * データ紐づけ変更関数
    */
    public function changeBuckets($request, $page_id, $frame_id)
    {
        // FrameのバケツIDの更新
        Frame::where('id', $frame_id)->update(['bucket_id' => $request->select_bucket]);

        // Conventions の特定
        $plugin_bucket = $this->getPluginBucket($request->select_bucket);

        // フレームごとの表示設定の更新
        $convention_frame = $this->getPluginFrame($frame_id);
        $convention_frame->convention_id = $plugin_bucket->id;
        $convention_frame->frame_id = $frame_id;
        $convention_frame->save();

        return;
    }
}
