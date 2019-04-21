<?php

namespace App\Plugins\User\Contents;

use Illuminate\Support\Facades\Log;

use DB;

use App\Buckets;
use App\Contents;
use App\Frame;
use App\Page;
use App\Plugins\User\UserPluginBase;

/**
 * コンテンツプラグイン
 *
 * 固定エリアのデータ登録ができるプラグイン。
 *
 * @author 永原　篤 <nagahara@opensource-workshop.jp>
 * @copyright OpenSource-WorkShop Co.,Ltd. All Rights Reserved
 * @category コンテンツプラグイン
 * @package Contoroller
 */
//class ContentsPlugin extends Authenticatable
class ContentsPlugin extends UserPluginBase
{

    /**
     *  データ取得
     */
    public function getContents($frame_id)
    {
        // フレームID が渡されるので、そのフレームに応じたデータを返す。
        // 表示するデータ、バケツ、フレームをJOIN して取得
        $contents = DB::table('contents')
                    ->select('contents.*', 'buckets.id as bucket_id', 'frames.page_id as page_id')
                    ->join('buckets', 'buckets.id', '=', 'contents.bucket_id')
                    ->join('frames', function ($join) {
                        $join->on('frames.bucket_id', '=', 'buckets.id');
                    })
                    ->where('frames.id', $frame_id)
                    ->get();

        return $contents;
    }

    /**
     *  データ初期表示関数
     *  コアがページ表示の際に呼び出す関数
     */
    public function index($request, $page_id, $frame_id)
    {
        // データ取得
        $contents = $this->getContents($frame_id);

        // Page データ
        $page = Page::where('id', $page_id)->first();

        // コンテンツ1件分の変数
        $content = null;

        // DB::table では、コレクションで返されるので、データがない場合は[0]件目とすると例外がでるので、空を返す。
        if (!$contents->isEmpty()) {
            $content = $contents[0];
        }

        // 表示テンプレートを呼び出す。
        return view(
            $this->getViewPath('contents'), [
            'frame_id' => $frame_id,
            'contents' => $content,
            'page' => $page,
        ]);
    }

    /**
     *  データ詳細表示関数
     *  コアがページ表示の際に呼び出す関数
     */
    public function edit_show($request, $page_id, $frame_id, $id = null)
    {
        // データ取得
        $contents = $this->getContents($frame_id);

        // Page データ
        $page = Page::where('id', $page_id)->first();

        // コンテンツ1件分の変数
        $content = null;

        // DB::table では、コレクションで返されるので、データがない場合は[0]件目とすると例外がでるので、空を返す。
        if ($contents->isEmpty()) {

            // データなしの表示テンプレートを呼び出す。
            return view(
                $this->getViewPath('contents_edit_nodata'), [
                'frame_id' => $frame_id,
                'contents' => null,
                'page' => $page,
            ]);
        }

        // 表示テンプレートを呼び出す。
        return view(
            $this->getViewPath('contents_edit_show'), [
            'frame_id' => $frame_id,
            'contents' => $contents[0],
            'page' => $page,
        ]);
    }

    /**
     * データ初期表示関数
     * コアがページ表示の際に呼び出す関数
     */
    public function edit($request, $page_id, $frame_id, $id = null)
    {
        // データ取得
        $contents = $this->getContents($frame_id);

        // Page データ
        $page = Page::where('id', $page_id)->first();

        // DB::table では、コレクションで返されるので、データがない場合は[0]件目に空のクラスを用意しておく。
        if ($contents->isEmpty()) {
            // 新規登録画面を呼び出す
            return view(
                $this->getViewPath('contents_create'), [
                'frame_id' => $frame_id,
                'page' => $page
            ]);

        }
        else {
            // [0]件目を返し、編集画面テンプレートを呼び出す。
            return view(
                $this->getViewPath('contents_edit'), [
                'frame_id' => $frame_id,
                'contents' => $contents[0],
                'page' => $page
            ]);
        }
    }

    /**
     * データ選択表示関数
     */
    public function datalist($request, $page_id, $frame_id, $id = null)
    {
        // Page データ
        $page = Page::where('id', $page_id)->first();

        // ソート設定に初期設定値をセット
        $sort_inits = [
            "contents_updated_at" => ["desc", "asc"],
            "page_name" => ["desc", "asc"],
            "bucket_name" => ["asc", "desc"],
            "frame_title" => ["asc", "desc"],
            "content_text" => ["asc", "desc"],
        ];

        // 要求するソート指示。初期値として更新日の降順を設定
        $request_order_by = ["contents_updated_at", "desc"];

        // 画面からのソート指定があれば使用(ソート指定があった項目は、ソート設定の内容を入れ替える)
        if ( !empty( $request->sort ) ) {
            $request_order_by = explode('|', $request->sort);
            if ($request_order_by[1] == "asc") {
                $sort_inits[$request_order_by[0]]=["asc", "desc"];
            }
            else {
                $sort_inits[$request_order_by[0]]=["desc", "asc"];
            }
        }

        // 画面でのリンク用ソート指示(ソート指定されている場合はソート指定を逆転したもの)
        $order_link = array();
        foreach ( $sort_inits as $order_by_key => $order_by ) {
            if ( $request_order_by[0]==$order_by_key && $request_order_by[1]==$order_by[0]) {
                $order_link[$order_by_key] = array_reverse($order_by);
            }
            else {
                $order_link[$order_by_key] = $order_by;
            }
        }

        // データリストの場合の追加処理
        $buckets = DB::table('buckets')
                    ->select('buckets.*', 'contents.id as contents_id', 'contents.content_text', 'contents.updated_at as contents_updated_at', 'frames.id as frames_id',  'frames.frame_title', 'pages.page_name')
                    ->leftJoin('contents', 'buckets.id', '=', 'contents.bucket_id')
                    ->leftJoin('frames', 'buckets.id', '=', 'frames.bucket_id')
                    ->leftJoin('pages', 'pages.id', '=', 'frames.page_id')
                    ->where('buckets.plugin_name', 'contents')
                    ->orderBy($request_order_by[0],        $request_order_by[1])
                    ->paginate(10);

        return view(
            $this->getViewPath('contents_edit_datalist'), [
            'frame_id' => $frame_id,
            'page' => $page,
            'buckets' => $buckets,
            'order_link' => $order_link,
            'request_order_str' => implode( '|', $request_order_by )
        ]);
    }

   /**
    * データ新規登録関数
    */
    public function store($request, $page_id = null, $frame_id = null, $id = null)
    {
//Log::debug("store");
        // バケツの登録
        $bucket_id = DB::table('buckets')->insertGetId([
              'bucket_name' => '無題',
              'plugin_name' => 'contents'
        ]);

        // コンテンツデータの登録
        $id = DB::table('contents')->insertGetId(
            ['bucket_id' => $bucket_id, 'content_text' => $request->contents]
        );

        // FrameのバケツIDの更新
        Frame::where('id', $frame_id)
                  ->update(['bucket_id' => $bucket_id]);

        return;
    }

   /**
    * データ保存関数
    */
    public function update($request, $page_id = null, $frame_id = null, $id = null)
    {
//Log::debug("update");
        Contents::where('id', $id)
                  ->update(['content_text' => $request->contents]);
        return;
    }

   /**
    * データ紐づけ変更関数
    */
    public function change($request, $page_id = null, $frame_id = null, $id = null)
    {
        // FrameのバケツIDの更新
        \App\Frame::where('id', $frame_id)
                  ->update(['bucket_id' => $request->select_bucket]);
        return;
    }

   /**
    * データ削除関数
    */
    public function destroy($request, $page_id = null, $frame_id = null, $id = null)
    {
        // id がある場合、コンテンツを削除
        if ( $id ) {

            // Contents データ
            $content = Contents::where('id', $id)->first();

            // フレームも同時に削除するがチェックされていたらフレームを削除する。
            if ( $request->frame_delete_flag == "1" ) {
                Frame::destroy($frame_id);
            }

            // コンテンツデータとバケツデータを削除する。
            Contents::destroy($id);
            Buckets::destroy($content->bucket_id);
        }
        return;
    }
}
