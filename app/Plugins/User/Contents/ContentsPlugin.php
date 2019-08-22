<?php

namespace App\Plugins\User\Contents;

use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Gate;

use DB;

use App\Buckets;
use App\Models\User\Contents\Contents;
use App\Frame;

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
class ContentsPlugin extends UserPluginBase
{

    /**
     *  編集画面の最初のタブ
     *
     *  スーパークラスをオーバーライド
     */
    public function getFirstFrameEditAction()
    {
        return "edit";
    }

    /**
     *  データ取得
     */
    public function getContents($frame_id)
    {

        // 認証されているユーザの取得
        $user = Auth::user();

        // 管理者権限の場合は、一時保存も対象
        if (!empty($user) && $user->role == config('cc_role.ROLE_SYSTEM_MANAGER')) {

            // フレームID が渡されるので、そのフレームに応じたデータを返す。
            // 表示するデータ、バケツ、フレームをJOIN して取得
            $contents = DB::table('contents')
                        ->select('contents.*', 'buckets.id as bucket_id', 'frames.page_id as page_id')
                        ->join('buckets', 'buckets.id', '=', 'contents.bucket_id')
                        ->join('frames', function ($join) {
                            $join->on('frames.bucket_id', '=', 'buckets.id');
                        })
                        ->where('frames.id', $frame_id)
                        // 権限があるときは、アクティブ、一時保存、承認待ちを or で取得
                        ->where(function($query){ $query->where('contents.status', 0)->orWhere('contents.status', 1)->orWhere('contents.status', 2); })
                        ->orderBy('id', 'desc')
                        ->first();
        }
        else {

            // フレームID が渡されるので、そのフレームに応じたデータを返す。
            // 表示するデータ、バケツ、フレームをJOIN して取得
            $contents = DB::table('contents')
                        ->select('contents.*', 'buckets.id as bucket_id', 'frames.page_id as page_id')
                        ->join('buckets', 'buckets.id', '=', 'contents.bucket_id')
                        ->join('frames', function ($join) {
                            $join->on('frames.bucket_id', '=', 'buckets.id');
                        })
                        ->where('frames.id', $frame_id)
                        ->where('contents.status', 0)
                        ->first();
        }
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

        // 表示テンプレートを呼び出す。
        return $this->view(
            'contents', [
            'contents' => $contents,
        ]);
    }

    /**
     *  データ詳細表示関数
     *  コアがデータ削除の確認用に呼び出す関数
     */
    public function edit_show($request, $page_id, $frame_id, $id = null)
    {
        // データ取得
        $contents = $this->getContents($frame_id);

        // データの存在確認をして、画面を切り替える
        if (empty($contents)) {

            // データなしの表示テンプレートを呼び出す。
            return $this->view(
                'contents_edit_nodata', [
                'contents' => null,
            ]);
        }

        // 表示テンプレートを呼び出す。
        return $this->view(
            'contents_edit_show', [
            'contents' => $contents,
        ]);
    }

    /**
     * データ編集用表示関数
     * コアが編集画面表示の際に呼び出す関数
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
            ]);

        }
        else {
            // 編集画面テンプレートを呼び出す。
            return $this->view(
                'contents_edit', [
                'contents' => $contents,
            ]);
        }
    }

    /**
     * データ選択表示関数
     */
    public function datalist($request, $page_id, $frame_id, $id = null)
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
        // * status は 0 のもののみ表示（データリスト表示はそれで良いと思う）
        $buckets = DB::table('buckets')
                    ->select('buckets.*', 'contents.id as contents_id', 'contents.content_text', 'contents.updated_at as contents_updated_at', 'frames.id as frames_id',  'frames.frame_title', 'pages.page_name')
                    ->leftJoin('contents', function ($join) {
                        $join->on('contents.bucket_id', '=', 'buckets.id');
                        $join->where('contents.status', '=', 0);
                    })
                    ->leftJoin('frames', 'buckets.id', '=', 'frames.bucket_id')
                    ->leftJoin('pages', 'pages.id', '=', 'frames.page_id')
                    ->where('buckets.plugin_name', 'contents')
                    ->orderBy($request_order_by[0],        $request_order_by[1])
                    ->paginate(10);

        return $this->view(
            'contents_edit_datalist', [
            'buckets'           => $buckets,
            'order_link'        => $order_link,
            'request_order_str' => implode( '|', $request_order_by )
        ]);
    }

   /**
    * データ新規登録関数
    */
    public function store($request, $page_id = null, $frame_id = null, $id = null, $status = 0)
    {
        // バケツの登録
        $bucket_id = DB::table('buckets')->insertGetId([
              'bucket_name' => '無題',
              'plugin_name' => 'contents'
        ]);

        // コンテンツデータの登録
        $id = DB::table('contents')->insertGetId([
            'bucket_id'    => $bucket_id,
            'content_text' => $request->contents,
            'status'       => $status
        ]);

        // FrameのバケツIDの更新
        Frame::where('id', $frame_id)
                  ->update(['bucket_id' => $bucket_id]);

        return;
    }

   /**
    * データ更新（確定）関数
    */
    public function update($request, $page_id = null, $frame_id = null, $id = null)
    {
        // 新しいレコードの登録（旧レコードのコピー＆内容の入れ替え）
        $oldrow = Contents::find($id);

        // 旧レコードのstatus 更新(同じbackets のものは、最新を除いてstatus:9 に更新)
        Contents::where('bucket_id', $oldrow->bucket_id)->update(['status' => 9]);

        // 新しいレコードの登録（旧レコードのコピー＆内容の入れ替え）
        $newrow = $oldrow->replicate();
        $newrow->content_text = $request->contents;
        $newrow->status       = 0;
        $newrow->save();

        return;
    }

   /**
    * データ一時保存関数
    */
    public function temporarysave($request, $page_id = null, $frame_id = null, $id = null)
    {

        // 新規で一時保存しようとしたときは id、レコードがまだない。
        if (empty($id)) {
            $status = 1;
            $this->store($request, $page_id, $frame_id, $id, $status);
        }
        else {
            // 新しいレコードの登録（旧レコードのコピー＆内容の入れ替え）
            $oldrow = Contents::find($id);
            $newrow = $oldrow->replicate();
            $newrow->content_text = $request->contents;
            $newrow->status = 1; //（一時保存）
            $newrow->save();
        }
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
    public function delete($request, $page_id = null, $frame_id = null, $id = null)
    {
        // id がある場合、コンテンツを削除
        if ( $id ) {

            // Contents データ
            $content = Contents::where('id', $id)->first();

            // フレームも同時に削除するがチェックされていたらフレームを削除する。
            if ( $request->frame_delete_flag == "1" ) {
                Frame::destroy($frame_id);
            }

            // 論理削除のため、コンテンツデータを status:9 に変更する。バケツデータは削除しない。
            Contents::where('id', $id)->update(['status' => 9]);
        }
        return;
    }

//   /**
//    * データ削除関数
//    */
//    public function destroy($request, $page_id = null, $frame_id = null, $id = null)
//    {
//        // id がある場合、コンテンツを削除
//        if ( $id ) {
//
//            // Contents データ
//            $content = Contents::where('id', $id)->first();
//
//            // フレームも同時に削除するがチェックされていたらフレームを削除する。
//            if ( $request->frame_delete_flag == "1" ) {
//                Frame::destroy($frame_id);
//            }
//
//            // コンテンツデータとバケツデータを削除する。
//            Contents::destroy($id);
//            Buckets::destroy($content->bucket_id);
//        }
//        return;
//    }
}
