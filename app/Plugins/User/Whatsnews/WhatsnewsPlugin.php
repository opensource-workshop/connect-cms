<?php

namespace App\Plugins\User\Whatsnews;

use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;

use Carbon\Carbon;

use DB;

use App\Models\Common\Buckets;
use App\Models\Common\Frame;
use App\Models\Core\Configs;
use App\Models\User\Whatsnews\Whatsnews;

use App\Plugins\User\UserPluginBase;
use App\Traits\ConnectCommonTrait;

/**
 * 新着情報・プラグイン
 *
 * サイト内の新着情報を表示するプラグイン。
 *
 * @author 永原　篤 <nagahara@opensource-workshop.jp>
 * @copyright OpenSource-WorkShop Co.,Ltd. All Rights Reserved
 * @category 新着情報プラグイン
 * @package Contoroller
 */
class WhatsnewsPlugin extends UserPluginBase
{
    use ConnectCommonTrait;

    /* オブジェクト変数 */

    /**
     *  新着の検索結果
     */
    public $whatsnews_results = null;

    /**
     *  新着のフレーム情報
     */
    public $whatsnews_frame = null;


    /* コアから呼び出す関数 */

    /**
     *  関数定義（コアから呼び出す）
     */
    public function getPublicFunctions()
    {
        // 標準関数以外で画面などから呼ばれる関数の定義
        $functions = array();
        $functions['get']  = ['rss'];
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

    /* private関数 */

    /**
     *  紐づく新着情報とフレームデータの取得
     */
    private function getWhatsnewsFrame($frame_id)
    {
        // 1回呼ばれている場合
        if ($this->whatsnews_frame) {
            return $this->whatsnews_frame;
        }

        // Frame データ
        $this->whatsnews_frame = DB::table('frames')
                 ->select('frames.*',
                          'whatsnews.id as whatsnews_id',
                          'whatsnews.whatsnew_name',
                          'whatsnews.view_pattern',
                          'whatsnews.count',
                          'whatsnews.days',
                          'whatsnews.rss',
                          'whatsnews.rss_count',
                          'whatsnews.view_posted_name',
                          'whatsnews.view_posted_at',
                          'whatsnews.important',
                          'whatsnews.target_plugins',
                          'whatsnews.frame_select',
                          'whatsnews.target_frame_ids'
                         )
                 ->leftJoin('whatsnews', 'whatsnews.bucket_id', '=', 'frames.bucket_id')
                 ->where('frames.id', $frame_id)
                 ->first();
        return $this->whatsnews_frame;
    }

    /**
     *  新着対象のプラグインがあるフレームデータの取得
     */
    private function getTargetPluginsFrames()
    {
        // Frame データ
        $frames = Frame::select('frames.*', 'pages._lft', 'pages.page_name', 'buckets.bucket_name')
                       ->whereIn('frames.plugin_name', array('blogs'))
                       ->leftJoin('buckets', 'frames.bucket_id', '=', 'buckets.id')
                       ->leftJoin('pages', 'frames.page_id', '=', 'pages.id')
                       ->where('disable_whatsnews', 0)
                       ->orderBy('pages._lft', 'asc')
                       ->get();
        return $frames;
    }

    /**
     * 表示記事の件数取得
     */
    public function getContentsCount($frame_id)
    {
        // フレームから、新着の設定取得
        $whatsnews_frame = $this->getWhatsnewsFrame($frame_id);

        // 新着の一覧取得
        list($whatsnews, $link_pattern, $link_base) = $this->getWhatsnews($whatsnews_frame);

        // 件数を返却
        return $whatsnews ? count($whatsnews) : 0;
    }

    /**
     * 新着記事の取得
     */
    private function getWhatsnews($whatsnews_frame, $method = null)
    {
//DB::enableQueryLog();
        // 新着情報がまだできていない場合
        if (!$whatsnews_frame || empty($whatsnews_frame->whatsnews_id)) {
            return array(null, null, null);
        }

        // 1回呼ばれている場合
        if ($this->whatsnews_results) {
            return $this->whatsnews_results;
        }

        // ターゲットプラグインをループ
        $target_plugins = explode(',', $whatsnews_frame->target_plugins);

        // union するSQL を各プラグインから取得。その際に使用するURL パターンとベースのURL も取得
        $union_sqls = array();
        foreach($target_plugins as $target_plugin) {

            // クラスファイルの存在チェック。
            $file_path = base_path() . "/app/Plugins/User/" . ucfirst($target_plugin) . "/" . ucfirst($target_plugin) . "Plugin.php";

            // ファイルの存在確認
            if (!file_exists($file_path)) {
                return $this->view_error("500_inframe", null, 'ファイル Not found.<br />' . $file_path);
            }

            // 各プラグインのgetWhatsnewArgs() 関数を呼び出し。
            $class_name = "App\Plugins\User\\" . ucfirst($target_plugin) . "\\" . ucfirst($target_plugin) . "Plugin";

            list($union_sqls[$target_plugin], $link_pattern[$target_plugin], $link_base[$target_plugin]) = $class_name::getWhatsnewArgs();
        }

        // blog
/*
        $blogs = DB::table('blogs_posts')
                 ->select('frames.page_id              as page_id',
                          'frames.id                   as frame_id',
                          'blogs_posts.id              as post_id',
                          'blogs_posts.post_title      as post_title',
                          'blogs_posts.posted_at       as posted_at',
                          'categories.classname        as classname',
                          'categories.category         as category',
                          DB::raw("'blogs' as plugin_name")
                         )
                 ->join('blogs', 'blogs.id', '=', 'blogs_posts.blogs_id')
                 ->join('frames', 'frames.bucket_id', '=', 'blogs.bucket_id')
                 ->leftJoin('categories', 'categories.id', '=', 'blogs_posts.categories_id')
                 ->where('status', 0)
                 ->whereNull('deleted_at');
*/
        // union
/*
        $whatsnews = DB::table('whatsnews_dual')
                 ->select('page_id',
                          'frame_id',
                          'post_id',
                          'post_title',
                          'posted_at',
                          'categories.classname        as classname',
                          'categories.category         as category',
                          DB::raw("null as plugin_name")
                         )
                 ->leftJoin('categories', 'categories.id', '=', 'whatsnews_dual.categories_id')
                 ->unionAll($blogs)
                 ->orderBy('posted_at', 'desc')
                 ->limit(5)
                 ->get(5);
*/
        // ベースの新着DUAL（ダミーテーブル）
        $whatsnews_sql = DB::table('whatsnews_dual')
                 ->select('page_id',
                          'frame_id',
                          'post_id',
                          'post_title',
                          DB::raw("null as important"),
                          'posted_at',
                          DB::raw("null as posted_name"),
                          'categories.classname        as classname',
                          'categories.category         as category',
                          DB::raw("null as plugin_name")
                         )
                 ->leftJoin('categories', 'categories.id', '=', 'whatsnews_dual.categories_id');

        // 何日前の指定がある場合は、各プラグインのSQL で日付で絞る
        $where_date = null;
        if ($whatsnews_frame->view_pattern == 1) {
            $where_date = date("Y-m-d",strtotime("-" . $whatsnews_frame->days ." day"));
        }

        // 各プラグインのSQL に追加条件を加えてUNION
        foreach($union_sqls as $union_sql) {
            if ($where_date) {
                $union_sql->where('posted_at', '>=', $where_date);
            }
            // 重要なもののみ
            if ($whatsnews_frame->important == 'important_only') {
                $union_sql->where('important', 1);
            }
            // 重要なものを除外
            if ($whatsnews_frame->important == 'not_important') {
                $union_sql->whereNull('important');
            }
            // フレームの選択が行われる場合
            if ($whatsnews_frame->frame_select == 1) {
                $union_sql->whereIn('frames.id', explode(',', $whatsnews_frame->target_frame_ids));
            }

            $whatsnews_sql->unionAll($union_sql);
        }

/*
        foreach($union_sqls as $union_sql) {
            if ($where_date) {
                $whatsnews_sql->unionAll($union_sql->where('posted_at', '>=', $where_date));
            }
            else {
                $whatsnews_sql->unionAll($union_sql);
            }
        }
*/
        // UNION 後をソート
        if ($whatsnews_frame->important == 'top') {
            $whatsnews_sql->orderBy('important', 'desc');
        }
        $whatsnews_sql->orderBy('posted_at', 'desc');

        // 件数制限
        if ($method == 'rss') {
            $whatsnews_sql->limit($whatsnews_frame->rss_count);
        }
        else if ($whatsnews_frame->view_pattern == 0) {
            $whatsnews_sql->limit($whatsnews_frame->count);
        }
        else {
            $whatsnews_sql->where('posted_at', '>=', date('Y-m-d H:i:s', strtotime("- " . $whatsnews_frame->days . " day")));
        }

        // 取得
        $whatsnews = $whatsnews_sql->get();
//Log::debug(DB::getQueryLog());

//Log::debug($whatsnews);

        // 一旦オブジェクト変数へ。（Singleton のため。フレーム表示確認でコアが使用する）
        $this->whatsnews_results = array($whatsnews, $link_pattern, $link_base);

        return $this->whatsnews_results;
    }


    /* 画面アクション関数 */

    /**
     *  データ初期表示関数
     *  コアがページ表示の際に呼び出す関数
     */
    public function index($request, $page_id, $frame_id)
    {
        // フレームから、新着の設定取得
        $whatsnews_frame = $this->getWhatsnewsFrame($frame_id);

        // 新着の一覧取得
        list($whatsnews, $link_pattern, $link_base) = $this->getWhatsnews($whatsnews_frame);

        // 表示テンプレートを呼び出す。
        return $this->view(
            'whatsnews', [
            'whatsnews'       => $whatsnews,
            'whatsnews_frame' => $whatsnews_frame,
            'link_pattern'    => $link_pattern,
            'link_base'       => $link_base,
        ]);
    }

    /**
     * データ選択表示関数
     */
    public function listBuckets($request, $page_id, $frame_id, $id = null)
    {
        // Frame データ
        $whatsnew_frame = $this->getWhatsnewsFrame($frame_id);

        // データ取得（1ページの表示件数指定）
        $whatsnews = Whatsnews::orderBy('created_at', 'desc')
                              ->paginate(10);

        // 表示テンプレートを呼び出す。
        return $this->view(
            'whatsnews_list_buckets', [
            'whatsnew_frame' => $whatsnew_frame,
            'whatsnews'      => $whatsnews,
        ]);
    }

    /**
     * 新着情報設定の新規作成画面
     */
    public function createBuckets($request, $page_id, $frame_id, $id = null, $create_flag = false, $message = null, $errors = null)
    {
        // 新規作成フラグを付けて設定変更画面を呼ぶ
        $create_flag = true;
        return $this->editBuckets($request, $page_id, $frame_id, $id, $create_flag, $message, $errors);
    }

    /**
     * 新着情報設定変更画面の表示
     */
    public function editBuckets($request, $page_id, $frame_id, $id = null, $create_flag = false, $message = null, $errors = null)
    {
        // セッション初期化などのLaravel 処理。
        $request->flash();

        // 新着情報＆フレームデータ
        $whatsnew_frame = $this->getWhatsnewsFrame($frame_id);

        // 新着情報設定データ
        $whatsnew = new Whatsnews();

        // id が渡ってくればid が対象
        if (!empty($id)) {
            $whatsnew = Whatsnews::where('id', $id)->first();
        }
        // Frame のbucket_id があれば、bucket_id から新着情報設定データ取得、なければ、新規作成か選択へ誘導
        else if (!empty($whatsnew_frame->bucket_id) && $create_flag == false) {
            $whatsnew = Whatsnews::where('bucket_id', $whatsnew_frame->bucket_id)->first();
        }

        // 選択できるフレームの一覧
        $target_plugins_frames = $this->getTargetPluginsFrames();

        // 表示テンプレートを呼び出す。
        return $this->view(
            'whatsnews_edit_whatsnew', [
            'whatsnew_frame'        => $whatsnew_frame,
            'whatsnew'              => $whatsnew,
            'target_plugins_frames' => $target_plugins_frames,
            'create_flag'           => $create_flag,
            'message'               => $message,
            'errors'                => $errors,
        ])->withInput($request->all);
    }

    /**
     *  新着情報設定の登録処理
     */
    public function saveBuckets($request, $page_id, $frame_id, $id = null)
    {
        // フレームから、新着の設定取得
        $whatsnews_frame = $this->getWhatsnewsFrame($frame_id);

        // 項目のエラーチェック
        $validator = Validator::make($request->all(), [
            'whatsnew_name'     => ['required'],
            'target_plugin'     => ['required'],
            'count'             => ['nullable', 'numeric'],
            'days'              => ['nullable', 'numeric'],
            'rss_count'         => ['nullable', 'numeric'],
        ]);
        $validator->setAttributeNames([
            'whatsnew_name'     => '新着情報設定名称',
            'target_plugin'     => '対象プラグイン',
            'count'             => '表示件数',
            'days'              => '表示日数',
            'rss_count'         => '対象RSS件数',
        ]);

        // エラーがあった場合は入力画面に戻る。
        $message = null;
        if ($validator->fails()) {

            if (empty($whatsnews_frame->whatsnews_id)) {
                $create_flag = true;
                return $this->createBuckets($request, $page_id, $frame_id, $id, $create_flag, $message, $validator->errors());
            }
            else  {
                $create_flag = false;
                return $this->editBuckets($request, $page_id, $frame_id, $id, $create_flag, $message, $validator->errors());
            }
        }

        // 更新後のメッセージ
        $message = null;

        // 画面から渡ってくるwhatsnews_id が空ならバケツと設定データを新規登録
        if (empty($request->whatsnews_id)) {

            // バケツの登録
            $bucket_id = DB::table('buckets')->insertGetId([
                  'bucket_name' => '無題',
                  'plugin_name' => 'whatsnews'
            ]);

            // 新着情報設定データ新規オブジェクト
            $whatsnews = new Whatsnews();
            $whatsnews->bucket_id = $bucket_id;

            // Frame のBuckets を見て、Buckets が設定されていなければ、作成したものに紐づける。
            // Frame にBuckets が設定されていない ＞ 新規のフレーム＆新着情報設定作成
            // Frame にBuckets が設定されている ＞ 既存のフレーム＆新着情報設定更新
            // （新着情報設定選択から遷移してきて、内容だけ更新して、フレームに紐づけないケースもあるため）
            if (empty($frame->bucket_id)) {

                // FrameのバケツIDの更新
                $frame = Frame::where('id', $frame_id)->update(['bucket_id' => $bucket_id]);
            }

            $message = '新着情報設定を追加しました。';
        }
        // whatsnews_id があれば、新着情報設定を更新
        else {

            // 新着情報設定の取得
            $whatsnews = Whatsnews::where('id', $request->whatsnews_id)->first();

            $message = '新着情報設定を変更しました。';
        }

        // 新着情報設定
        $whatsnews->whatsnew_name     = $request->whatsnew_name;
        $whatsnews->view_pattern      = $request->view_pattern;
        $whatsnews->count             = intval($request->count);
        $whatsnews->days              = intval($request->days);
        $whatsnews->rss               = $request->rss;
        $whatsnews->rss_count         = intval($request->rss_count);
        $whatsnews->view_posted_name  = $request->view_posted_name;
        $whatsnews->view_posted_at    = $request->view_posted_at;
        $whatsnews->important         = $request->important;
        $whatsnews->target_plugins    = implode(',', $request->target_plugin);
        $whatsnews->frame_select      = intval($request->frame_select);
//Log::debug($request->target_frame_ids);
        $whatsnews->target_frame_ids  = empty($request->target_frame_ids) ? "": implode(',', $request->target_frame_ids);

        // データ保存
        $whatsnews->save();

        // 新規作成フラグを付けて新着情報設定変更画面を呼ぶ
        $create_flag = false;
        return $this->editBuckets($request, $page_id, $frame_id, $id, $create_flag, $message);
    }

    /**
     *  削除処理
     */
    public function destroyBuckets($request, $page_id, $frame_id, $id)
    {
        // id がある場合、データを削除
        if ( $id ) {

            // フレームから、新着の設定取得
            $whatsnews_frame = $this->getWhatsnewsFrame($frame_id);

            // 新着設定を削除する。
            Whatsnews::where('id', $id)->delete();

            // backetsの削除
            Buckets::where('id', $whatsnews_frame->bucket_id)->delete();

            // FrameのバケツIDの更新
            Frame::where('id', $frame_id)->update(['bucket_id' => null]);

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

        // 新着情報設定選択画面を呼ぶ
        return $this->listBuckets($request, $page_id, $frame_id, $id);
    }

    /**
     *  RSS配信
     */
    public function rss($request, $page_id, $frame_id, $id = null)
    {
        // フレームから、新着の設定取得
        $whatsnews_frame = $this->getWhatsnewsFrame($frame_id);
        if (empty($whatsnews_frame)) {
            return;
        }

        // サイト名
        $base_site_name = Configs::where('name', 'base_site_name')->first();

        // URL
        $url = url("/redirect/plugin/wahtsnews/rss/" . $page_id . "/" . $frame_id);

        // HTTPヘッダー出力
        header('Content-Type: text/xml; charset=UTF-8');

echo <<<EOD
<rss xmlns:content="http://purl.org/rss/1.0/modules/content/" version="2.0">
<channel>
<title>[{$base_site_name->value}]{$whatsnews_frame->whatsnew_name}</title>
<description></description>
<link>
{$url}
</link>
EOD;

        // 新着の一覧取得
        list($whatsnews, $link_pattern, $link_base) = $this->getWhatsnews($whatsnews_frame, 'rss');

        foreach ($whatsnews as $whatsnew) {

            $title = $whatsnew->post_title;
            $link = url($link_base[$whatsnew->plugin_name] . '/' . $whatsnew->page_id . '/' . $whatsnew->frame_id . '/' . $whatsnew->post_id);
//            $description = strip_tags(mb_substr($blogs_post->post_text, 0, 20));
            $pub_date = date(DATE_RSS, strtotime($whatsnew->posted_at));
            $content = strip_tags(html_entity_decode($whatsnew->post_title));
echo <<<EOD

<item>
<title>{$title}</title>
<link>{$link}</link>
<pubDate>{$pub_date}</pubDate>
<content:encoded>{$content}</content:encoded>
</item>
EOD;
        }

echo <<<EOD
</channel>
</rss>
EOD;

exit;
    }
}
