<?php

namespace App\Plugins\User\Knowledges;

use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Gate;

use DB;

use App\Models\Common\Buckets;
use App\Models\Common\Frame;
use App\Knowledges;

use App\Plugins\User\UserPluginBase;

/**
 * ナレッジ・プラグイン
 *
 * サポート等のナレッジを蓄積するプラグイン。
 *
 * @author 永原　篤 <nagahara@opensource-workshop.jp>
 * @copyright OpenSource-WorkShop Co.,Ltd. All Rights Reserved
 * @category ナレッジ・プラグイン
 * @package Controller
 */
class KnowledgesPlugin extends UserPluginBase
{

    /**
     *  編集画面の最初のタブ
     *
     *  スーパークラスをオーバーライド
     */
/*
    public function getFirstFrameEditAction()
    {
        return "edit";
    }
*/

    /**
     *  関数定義（コアから呼び出す）
     */
    public function getPublicFunctions()
    {
        // 標準関数以外で画面などから呼ばれる関数の定義
        $functions = [];
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
        $role_check_table = [];
        return $role_check_table;
    }

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
                    ->first();

        return $contents;
    }

    /**
     *  データ初期表示関数
     *  コアがページ表示の際に呼び出す関数
     */
    public function index($request, $page_id, $frame_id)
    {
        // データ取得
//        $contents = $this->getContents($frame_id);

        // 表示テンプレートを呼び出す。
        return $this->view(
            'knowledges', [
            //            'contents' => $contents,
            ]
        );
    }

    /**
     *  データ詳細表示関数
     */
    public function detail($request, $page_id, $frame_id)
    {
        // 表示テンプレートを呼び出す。
        return $this->view(
            'knowledges_detail', [
            ]
        );
    }



















    /**
     *  データ詳細表示関数
     *  コアがページ表示の際に呼び出す関数
     */
    public function show($request, $page_id, $frame_id, $id = null)
    {
        // データ取得
        $contents = $this->getContents($frame_id);

        // データの存在確認をして、画面を切り替える
        if (empty($contents)) {
            // データなしの表示テンプレートを呼び出す。
            return $this->view(
                'contents_edit_nodata', [
                'contents' => null,
                ]
            );
        }

        // 表示テンプレートを呼び出す。
        return $this->view(
            'contents_edit_show', [
            'contents' => $contents,
            ]
        );
    }

    /**
     * データ初期表示関数
     * コアがページ表示の際に呼び出す関数
     */
    public function edit($request, $page_id, $frame_id, $id = null)
    {
        // データ取得
        $contents = $this->getContents($frame_id);

        // データがない場合は、新規登録用画面
        if (empty($contents)) {
            // 新規登録画面を呼び出す
            return $this->view(
                'contents_create', [
                ]
            );
        } else {
            // 編集画面テンプレートを呼び出す。
            return $this->view(
                'contents_edit', [
                'contents' => $contents,
                ]
            );
        }
    }

    /**
     * データ選択表示関数
     */
    public function listBuckets($request, $page_id, $frame_id, $id = null)
    {
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
        if (!empty($request->sort)) {
            $request_order_by = explode('|', $request->sort);
            if ($request_order_by[1] == "asc") {
                $sort_inits[$request_order_by[0]]=["asc", "desc"];
            } else {
                $sort_inits[$request_order_by[0]]=["desc", "asc"];
            }
        }

        // 画面でのリンク用ソート指示(ソート指定されている場合はソート指定を逆転したもの)
        $order_link = array();
        foreach ($sort_inits as $order_by_key => $order_by) {
            if ($request_order_by[0]==$order_by_key && $request_order_by[1]==$order_by[0]) {
                $order_link[$order_by_key] = array_reverse($order_by);
            } else {
                $order_link[$order_by_key] = $order_by;
            }
        }

        // データリストの場合の追加処理
        $buckets = DB::table('buckets')
                    ->select('buckets.*', 'contents.id as contents_id', 'contents.content_text', 'contents.updated_at as contents_updated_at', 'frames.id as frames_id', 'frames.frame_title', 'pages.page_name')
                    ->leftJoin('contents', 'buckets.id', '=', 'contents.bucket_id')
                    ->leftJoin('frames', 'buckets.id', '=', 'frames.bucket_id')
                    ->leftJoin('pages', 'pages.id', '=', 'frames.page_id')
                    ->where('buckets.plugin_name', 'contents')
                    ->orderBy($request_order_by[0], $request_order_by[1])
                    ->paginate(10, ["*"], "frame_{$frame_id}_page");

        return $this->view(
            'contents_edit_datalist', [
            'buckets'           => $buckets,
            'order_link'        => $order_link,
            'request_order_str' => implode('|', $request_order_by)
            ]
        );
    }

   /**
    * データ新規登録関数
    */
    public function store($request, $page_id = null, $frame_id = null, $id = null)
    {
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
        Frame::where('id', $frame_id)
               ->update(['bucket_id' => $request->select_bucket]);
        return;
    }

   /**
    * データ削除関数
    */
    public function destroy($request, $page_id = null, $frame_id = null, $id = null)
    {
        // id がある場合、コンテンツを削除
        if ($id) {
            // Contents データ
            $content = Contents::where('id', $id)->first();

            // フレームも同時に削除するがチェックされていたらフレームを削除する。
            if ($request->frame_delete_flag == "1") {
                Frame::destroy($frame_id);
            }

            // コンテンツデータとバケツデータを削除する。
            Contents::destroy($id);
            Buckets::destroy($content->bucket_id);
        }
        return;
    }
}
