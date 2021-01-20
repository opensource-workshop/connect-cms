<?php

namespace App\Plugins\User\Cabinets;

use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;

use App\Models\Common\Buckets;
use App\Models\Common\Frame;
//use App\Models\User\Cabinets\Cabinet;
use App\Models\User\Cabinets\CabinetFolder;
//use App\Models\User\Cabinets\CabinetFrame;
//use App\Models\User\Cabinets\CabinetPost;

use App\Plugins\User\UserPluginBase;

/**
 * キャビネット・プラグイン
 *
 * @author 永原　篤 <nagahara@opensource-workshop.jp>
 * @copyright OpenSource-WorkShop Co.,Ltd. All Rights Reserved
 * @category キャビネット・プラグイン
 * @package Contoroller
 */
class CabinetsPlugin extends UserPluginBase
{
    /* DB migrate
       php artisan make:migration create_cabinets --create=cabinets
       php artisan make:migration create_cabinet_folders --create=cabinet_folders
       php artisan make:migration create_cabinet_posts --create=cabinet_posts
       php artisan make:migration create_cabinet_frames --create=cabinet_frames
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
        $functions['get']  = ['index', 'editView'];
        $functions['post'] = ['edit', 'saveView'];
        return $functions;
    }

    /**
     *  権限定義
     */
    public function declareRole()
    {
        // 権限チェックテーブル
        $role_ckeck_table = array();
        $role_ckeck_table["editView"] = array('role_arrangement');
        $role_ckeck_table["saveView"] = array('role_arrangement');
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
        $this->post = CabinetPost::firstOrNew(['id' => $id]);
        return $this->post;
    }

    /* private関数 */

    /**
     * プラグインのフレーム
     */
    private function getPluginFrame($frame_id)
    {
        return CabinetFrame::firstOrNew(['frame_id' => $frame_id]);
    }

    /**
     * プラグインのバケツ取得関数
     */
    public function getPluginBucket($bucket_id)
    {
        // プラグインのメインデータを取得する。
        return Cabinet::firstOrNew(['bucket_id' => $bucket_id]);
    }

    /**
     *  データ取得時の権限条件の付与
     */
    protected function appendAuthWhere($query, $table_name)
    {
        // 各条件でSQL を or 追記する場合は、クロージャで記載することで、元のSQL とAND 条件でつながる。
        // クロージャなしで追記した場合、or は元の whereNull('cabinet_posts.parent_id') を打ち消したりするので注意。

        if (empty($query)) {
            // 空なら何もしない
            return $query;
        }

        // モデレータ(記事修正, role_article)権限以上（role_article, role_article_admin）
        if ($this->isCan('role_article')) {
            // 全件取得のため、追加条件なしで戻る。
            return $query;
        }

        // 認証状況により、絞り込み条件を変える。
        if (!Auth::check()) {
            //
            // 共通条件（Active）
            // 権限なし（コンテンツ管理者・モデレータ・承認者・編集者以外）
            // 未ログイン
            //
            $query->where($table_name . '.status', '=', StatusType::active);
        } elseif ($this->isCan('role_approval')) {
            //
            // 承認者(role_approval)権限 = Active ＋ 承認待ちの取得
            //
            $query->where(function ($auth_query) use ($table_name) {
                $auth_query->orWhere($table_name . '.status', '=', StatusType::active);
                $auth_query->orWhere($table_name . '.status', '=', StatusType::approval_pending);
            });
        } elseif ($this->isCan('role_reporter')) {
            //
            // 編集者(role_reporter)権限 = Active ＋ 自分の全ステータス記事の取得
            // 一時保存の記事も、自分の記事を取得することで含まれる。
            // 承認待ちの記事であっても、自分の記事なので、修正可能。
            //
            $query->where(function ($auth_query) use ($table_name) {
                $auth_query->orWhere($table_name . '.status', '=', StatusType::active);
                $auth_query->orWhere($table_name . '.created_id', '=', Auth::user()->id);
            });
        }

        return $query;
    }

    /**
     *  POST一覧取得
     */
    private function getPosts($folder_id)
    {
        // データ取得
        $posts_query = CabinetPost::select('cabinet_posts.*')
                                   ->join('cabinets', function ($join) {
                                       $join->on('cabinets.id', '=', 'cabinet_posts.cabinet_id')
                                          ->where('cabinets.bucket_id', '=', $this->frame->bucket_id);
                                   })
// 対象のフォルダを指定する。
                                   ->whereNull('cabinet_posts.deleted_at');

        // 権限によって表示する記事を絞る
        $posts_query = $this->appendAuthWhere($posts_query, 'cabinet_posts');

        // 取得
        return $posts_query->get();
    }

    /* スタティック関数 */

    /**
     *  新着情報用メソッド
     */
    public static function getWhatsnewArgs()
    {
        // 戻り値('sql_method'、'link_pattern'、'link_base')
        $return[] = DB::table('cabinet_posts')
                      ->select(
                          'frames.page_id           as page_id',
                          'frames.id                as frame_id',
                          'cabinet_posts.id         as post_id',
                          'cabinet_posts.title      as post_title',
                          DB::raw("null             as important"),
                          'cabinet_posts.created_at   as posted_at',
                          'cabinet_posts.created_name as posted_name',
                          DB::raw("null             as classname"),
                          DB::raw("null             as category"),
                          DB::raw('"cabinets"       as plugin_name')
                      )
                      ->join('cabinets', 'cabinets.id', '=', 'cabinet_posts.cabinets_id')
                      ->join('frames', 'frames.bucket_id', '=', 'cabinets.bucket_id')
                      ->where('frames.disable_whatsnews', 0)
                      ->whereNull('cabinets_posts.deleted_at');

        $return[] = 'show_page_frame_post';
        $return[] = '/plugin/cabinets/show';

        return $return;
    }

    /**
     *  検索用メソッド
     */
    /*
    public static function getSearchArgs($search_keyword)
    {
        $return[] = DB::table('cabinet_posts')
                      ->select(
                          'cabinet_posts.id         as post_id',
                          'frames.id                as frame_id',
                          'frames.page_id           as page_id',
                          'pages.permanent_link     as permanent_link',
                          'cabinet_posts.title      as post_title',
                          DB::raw("null             as important"),
                          'cabinet_posts.created_at   as posted_at',
                          'cabinet_posts.created_name as posted_name',
                          DB::raw("null             as classname"),
                          DB::raw("null             as category_id"),
                          DB::raw("null             as category"),
                          DB::raw('"cabinets"       as plugin_name')
                      )
                      ->join('cabinets', 'cabinets.id',    '=', 'cabinet_posts.calendars_id')
                      ->join('frames', 'frames.bucket_id', '=', 'cabinets.bucket_id')
                      ->leftjoin('pages', 'pages.id',      '=', 'frames.page_id')
                      ->where(function ($plugin_query) use ($search_keyword) {
                          $plugin_query->where('cabinet_posts.title', 'like', '?')
                                       ->orWhere('cabinet_posts.body', 'like', '?');
                      })
                      ->whereNull('cabinet_posts.deleted_at');

        $bind = array('%'.$search_keyword.'%', '%'.$search_keyword.'%');
        $return[] = $bind;
        $return[] = 'show_page_frame_post';
        $return[] = '/plugin/cabinets/show';

        return $return;
    }
    */

    public function test()
    {
        CabinetFolder::truncate();
        $folder_1     = CabinetFolder::create(['cabinet_id' => 1, 'title' => 'Title_1']);
        $folder_1_1   = CabinetFolder::create(['cabinet_id' => 1, 'title' => 'Title_1-1'], $folder_1);
        $folder_1_1_1 = CabinetFolder::create(['cabinet_id' => 1, 'title' => 'Title_1-1-1'], $folder_1_1);
        $folder_1_2   = CabinetFolder::create(['cabinet_id' => 1, 'title' => 'Title_1-2'], $folder_1);
        $folder_1_2_1 = CabinetFolder::create(['cabinet_id' => 1, 'title' => 'Title_1-2-1'], $folder_1_2);
        $folder_2     = CabinetFolder::create(['cabinet_id' => 1, 'title' => 'Title_2']);

        //$tree = CabinetFolder::withDepth()->orderBy('title')->get()->toFlatTree();
        $tree = CabinetFolder::withDepth()->get()->toFlatTree();
        //$tree = CabinetFolder::withDepth()->get();
        foreach ($tree as $folder) {
            echo $folder->title . "(" . $folder->depth . ")<br />";
        }

        foreach($folder_1_1_1->ancestors as $ancestor) {
            echo " &gt; " . $ancestor->title;
        }

//Log::debug($tree);
    }

    /* 画面アクション関数 */

    /**
     *  データ初期表示関数
     *  コアがページ表示の際に呼び出す関数
     */
    public function index($request, $page_id, $frame_id)
    {
// $this->test();

        // プラグインのフレームデータ
//        $plugin_frame = $this->getPluginFrame($frame_id);

        // 該当フォルダのデータの取得
$folder_id = 0;
//        $posts = $this->getPosts($folder_id);

        // 表示テンプレートを呼び出す。
        return $this->view('index', [
//            'posts'            => $posts,
//            'plugin_frame'     => $plugin_frame,
        ]);
    }

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
            'post' => $post,
        ]);
    }

    /**
     * 記事編集画面
     */
    public function edit($request, $page_id, $frame_id, $post_id = null)
    {
        // 記事取得
        $post = $this->getPost($post_id);

        // 変更画面を呼び出す。
        return $this->view('edit', [
            'post' => $post,
        ]);
    }

    /**
     *  記事登録処理
     */
    public function save($request, $page_id, $frame_id, $post_id = null)
    {
        // 項目のエラーチェック
        $validator = Validator::make($request->all(), [
            'title'      => ['required'],
            'body'       => ['required'],
        ]);
        $validator->setAttributeNames([
            'title'      => 'タイトル',
            'body'       => '本文',
        ]);
        // エラーがあった場合は入力画面に戻る。
        if ($validator->fails()) {
            return back()->withErrors($validator)->withInput();
        }

        // POSTデータのモデル取得
        $post = CabinetPost::firstOrNew(['id' => $post_id]);

        // フレームから cabinet_id 取得
        $cabinet_frame = $this->getPluginFrame($frame_id);

        // 値のセット
        $post->cabinet_id  = $cabinet_frame->cabinet_id;
        $post->title       = $request->title;
        $post->body        = $request->body;

        // 投稿者をセット
        if (Auth::check()) {
            $post->created_id = Auth::user()->id;
        }

        // 承認の要否確認とステータス処理
        if ($request->status == "1") {
            $post->status = 1;  // 一時保存
        } elseif ($this->buckets->needApprovalUser(Auth::user())) {
            $post->status = 2;  // 承認待ち
        } else {
            $post->status = 0;  // 公開
        }

        // 保存
        $post->save();

        // 登録後はリダイレクトして編集画面を開く。(form のリダイレクト指定では post した id が渡せないため)
        return new Collection(['redirect_path' => url('/') . "/plugin/cabinets/edit/" . $page_id . "/" . $frame_id . "/" . $post->id . "#frame-" . $frame_id]);
    }

    /**
     *  承認処理
     */
    public function approval($request, $page_id, $frame_id, $post_id)
    {
        // 記事取得
        $post = $this->getPost($post_id);

        // データがあることを確認
        if (empty($post)) {
            return;
        }

        // 更新されたら、行レコードの updated_at を更新したいので、update()
        $post->updated_at = now();
        $post->status = 0;  // 公開
        $post->update();

        // 登録後は画面側の指定により、リダイレクトして表示画面を開く。
        return;
    }

    /**
     *  削除処理
     */
    public function delete($request, $page_id, $frame_id, $post_id)
    {
        // id がある場合、データを削除
        if ($post_id) {
            // データを削除する。（論理削除で削除日、ID などを残すためにupdate）
            CabinetPost::where('id', $post_id)->update([
                'deleted_at'   => date('Y-m-d H:i:s'),
                'deleted_id'   => Auth::user()->id,
                'deleted_name' => Auth::user()->name,
            ]);
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
            'plugin_buckets' => Cabinet::orderBy('created_at', 'desc')->paginate(10),
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
            'cabinet'       => $this->getPluginBucket($this->getBucketId()),
            'cabinet_frame' => $this->getPluginFrame($frame_id),
        ]);
    }

    /**
     * フレーム表示設定の保存
     */
    public function saveView($request, $page_id, $frame_id, $cabinet_id)
    {
        // 項目のエラーチェック
        $validator = Validator::make($request->all(), [
            'view_count'       => ['nullable', 'numeric'],
            'view_format'      => ['nullable', 'numeric'],
        ]);
        $validator->setAttributeNames([
            'view_count'       => '表示件数',
            'view_format'      => '表示形式',
        ]);

        // エラーがあった場合は入力画面に戻る。
        if ($validator->fails()) {
            return back()->withErrors($validator)->withInput();
        }

        // フレームごとの表示設定の更新
        $cabinet_frame = CabinetFrame::updateOrCreate(
            ['cabinet_id'  => $cabinet_id, 'frame_id' => $frame_id],
            ['view_count'  => $request->view_count,
             'view_format' => $request->view_format],
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
            'cabinet' => $this->getPluginBucket($bucket_id),
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
            'name' => 'カレンダー名',
        ]);

        // エラーがあった場合は入力画面に戻る。
        if ($validator->fails()) {
            return back()->withErrors($validator)->withInput();
        }

        // バケツの取得。なければ登録。
        $bucket = Buckets::updateOrCreate(
            ['id' => $bucket_id],
            ['bucket_name' => $request->name, 'plugin_name' => 'cabinets'],
        );

        // フレームにバケツの紐づけ
        $frame = Frame::find($frame_id)->update(['bucket_id' => $bucket->id]);

        // プラグインバケツを取得(なければ新規オブジェクト)
        // プラグインバケツにデータを設定して保存
        $cabinet = $this->getPluginBucket($bucket->id);
        $cabinet->name = $request->name;
        $cabinet->save();

        // プラグインフレームを作成 or 更新
        $cabinet_frame = CabinetFrame::updateOrCreate(
            ['frame_id' => $frame_id],
            ['cabinet_id' => $cabinet->id, 'frame_id' => $frame_id],
        );

        // 登録後はリダイレクトして編集ページを開く。
        return new Collection(['redirect_path' => url('/') . "/plugin/cabinets/editBuckets/" . $page_id . "/" . $frame_id . "/" . $bucket->id . "#frame-" . $frame_id]);
    }

    /**
     *  削除処理
     */
    public function destroyBuckets($request, $page_id, $frame_id, $cabinet_id)
    {
        // プラグインバケツの取得
        $cabinet = Cabinet::find($cabinet_id);
        if (empty($cabinet)) {
            return;
        }

        // POSTデータ削除(一気にDelete なので、deleted_id は入らない)
        CabinetPost::where('cabinet_id', $cabinet->id)->delete();

        // FrameのバケツIDの更新
        Frame::where('id', $frame_id)->update(['bucket_id' => null]);

        // プラグインフレームデータの削除(deleted_id を記録するために1回読んでから削除)
        $cabinet_frame = CabinetFrame::where('frame_id', $frame_id)->first();
        $cabinet_frame->delete();

        // バケツ削除
        Buckets::find($cabinet->bucket_id)->delete();

        // プラグインデータ削除
        $cabinet->delete();

        return;
    }

   /**
    * データ紐づけ変更関数
    */
    public function changeBuckets($request, $page_id, $frame_id)
    {
        // FrameのバケツIDの更新
        Frame::where('id', $frame_id)->update(['bucket_id' => $request->select_bucket]);

        // Cabinets の特定
        $plugin_bucket = $this->getPluginBucket($request->select_bucket);

        // フレームごとの表示設定の更新
        $cabinet_frame = $this->getPluginFrame($frame_id);
        $cabinet_frame->cabinet_id = $plugin_bucket->id;
        $cabinet_frame->frame_id = $frame_id;
        $cabinet_frame->save();

        return;
    }
}
