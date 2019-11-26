<?php

namespace App\Plugins\User\Whatsnews;

use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;

use Carbon\Carbon;

use DB;

use App\Models\Common\Buckets;
use App\Models\Common\Frame;
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

    /* private関数 */

    /**
     *  紐づく新着情報とフレームデータの取得
     */
    private function getWhatsnewsFrame($frame_id)
    {
        // Frame データ
        $frame = DB::table('frames')
                 ->select('frames.*',
                          'whatsnews.id as whatsnews_id',
                          'whatsnews.whatsnew_name',
                          'whatsnews.view_pattern',
                          'whatsnews.count',
                          'whatsnews.days',
                          'whatsnews.rss',
                          'whatsnews.view_created_name',
                          'whatsnews.view_created_at',
                          'whatsnews.target_plugin'
                         )
                 ->leftJoin('whatsnews', 'whatsnews.bucket_id', '=', 'frames.bucket_id')
                 ->where('frames.id', $frame_id)
                 ->first();
        return $frame;
    }

    /**
     * 新着記事の取得
     */
    private function getWhatsnews($frame_id)
    {
        // 新着記事
/*
        return $openingcalendar = DB::table('frames')
                 ->select('openingcalendars.*')
                 ->join('buckets', 'buckets.id', '=', 'frames.bucket_id')
                 ->join('openingcalendars', 'openingcalendars.bucket_id', '=', 'buckets.id')
                 ->where('frames.id', $frame_id)
                 ->first();]
*/
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

        // 新着情報がまだできていない場合
        if (!$whatsnews_frame || empty($whatsnews_frame->whatsnews_id)) {
            return $this->view(
                'whatsnews', [
                'whatsnews'   => null,
                'link_pattern' => null,
                'link_base' => null,
            ]);
        }

        // ターゲットプラグインをループ
        $target_plugins = explode(',', $whatsnews_frame->target_plugin);

        // union するSQL を各プラグインから取得。その際に使用するURL パターンとベースのURL も取得
        $union_sqls = array();
        foreach($target_plugins as $target_plugin) {
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
                          'posted_at',
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

        // 各プラグインのSQL をUNION
        foreach($union_sqls as $union_sql) {
            if ($where_date) {
                $whatsnews_sql->unionAll($union_sql->where('posted_at', '>=', $where_date));
            }
            else {
                $whatsnews_sql->unionAll($union_sql);
            }
        }

        // UNION 後をソート
        $whatsnews_sql->orderBy('posted_at', 'desc');

        // 件数制限
        if ($whatsnews_frame->view_pattern == 0) {
            $whatsnews_sql->limit($whatsnews_frame->count);
        }
        else {
            $whatsnews_sql->where('posted_at', '>=', '2019-10-29 00:00:00');
        }

        // 取得
        $whatsnews = $whatsnews_sql->get();

        // 表示テンプレートを呼び出す。
        return $this->view(
            'whatsnews', [
            'whatsnews'   => $whatsnews,
            'link_pattern' => $link_pattern,
            'link_base' => $link_base,
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

        // 表示テンプレートを呼び出す。
        return $this->view(
            'whatsnews_edit_whatsnew', [
            'whatsnew_frame' => $whatsnew_frame,
            'whatsnew'       => $whatsnew,
            'create_flag'    => $create_flag,
            'message'        => $message,
            'errors'         => $errors,
        ])->withInput($request->all);
    }

    /**
     *  新着情報設定の登録処理
     */
    public function saveBuckets($request, $page_id, $frame_id, $id = null)
    {
        // 項目のエラーチェック
        $validator = Validator::make($request->all(), [
            'whatsnew_name'     => ['required'],
            'target_plugin'     => ['required'],
        ]);
        $validator->setAttributeNames([
            'whatsnew_name'     => '新着情報設定名称',
            'target_plugin'     => '対象プラグイン',
        ]);

        // エラーがあった場合は入力画面に戻る。
        $message = null;
        if ($validator->fails()) {

            if (empty($id)) {
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

        // 画面から渡ってくるopeningcalendars_id が空ならバケツと開館カレンダーを新規登録
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
            $frame = Frame::where('id', $frame_id)->first();
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
        $whatsnews->view_created_name = $request->view_created_name;
        $whatsnews->view_created_at   = $request->view_created_at;
        $whatsnews->target_plugin     = $request->target_plugin;

        // データ保存
        $whatsnews->save();

        // 新規作成フラグを付けて新着情報設定変更画面を呼ぶ
        $create_flag = false;
        return $this->editBuckets($request, $page_id, $frame_id, $id, $create_flag, $message);
    }

    /**
     *  削除処理
     */
    public function destroyBuckets($request, $page_id, $frame_id, $blogs_id)
    {
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
}
