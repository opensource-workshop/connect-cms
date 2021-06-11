<?php

namespace App\Plugins\User\Receives;

use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;

use DB;

use App\Models\Common\Buckets;
use App\Models\Common\Frame;
use App\Models\User\Receives\Receive;
use App\Models\User\Receives\ReceiveData;
use App\Models\User\Receives\ReceiveRecord;

use App\Plugins\User\UserPluginBase;
use App\Traits\ConnectCommonTrait;

/**
 * データ収集プラグイン
 *
 * APIでデータ収集するためのプラグイン。
 *
 * @author 永原　篤 <nagahara@opensource-workshop.jp>
 * @copyright OpenSource-WorkShop Co.,Ltd. All Rights Reserved
 * @category データ収集プラグイン
 * @package Contoroller
 */
class ReceivesPlugin extends UserPluginBase
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
     *  フレームデータの取得
     */
    private function getReceiveFrame($frame_id)
    {
        // Frame データ
        $frame = DB::table('frames')
                 ->select('frames.*',
                          'receives.id as receive_id',
                          'receives.dataset_name',
                          'receives.columns',
                         )
                 ->leftJoin('receives', 'receives.bucket_id', '=', 'frames.bucket_id')
                 ->where('frames.id', $frame_id)
                 ->first();
        return $frame;
    }

    /* 画面アクション関数 */

    /**
     *  データ初期表示関数
     *  コアがページ表示の際に呼び出す関数
     */
    public function index($request, $page_id, $frame_id)
    {

        // Frame データ
        $receive_frame = $this->getReceiveFrame($frame_id);

        // データ件数
        $receive_count = DB::table('receive_records')
                           ->join('receives', 'receive_records.receive_id', '=', 'receives.id')
                           ->join('frames', function ($join) use ($frame_id) {
                               $join->on('frames.bucket_id', '=', 'receives.bucket_id')
                                    ->where('frames.id', $frame_id);
                           })
                           ->count();

        // 最終登録日時
        $receive_last = ReceiveRecord::select('receive_records.*')
                            ->join('receives', 'receive_records.receive_id', '=', 'receives.id')
                            ->join('frames', function ($join) use ($frame_id) {
                                $join->on('frames.bucket_id', '=', 'receives.bucket_id')
                                    ->where('frames.id', $frame_id);
                            })
                            ->orderBy('receive_records.created_at', 'desc')
                            ->first();

        // 表示テンプレートを呼び出す。
        return $this->view(
            'receives', [
            'receive_frame'  => $receive_frame,
            'receives_count' => $receive_count,
            'receives_last'  => $receive_last,
        ]);
    }

    /**
     * 収集データダウンロード
     */
    public function downloadCsv($request, $page_id, $frame_id, $id)
    {

        // id で対象のデータの取得

        // Frame データ
        $receive_frame = $this->getReceiveFrame($frame_id);

        // 収集データ取得
        $receive_datas = DB::table('receive_datas')
                             ->select('receive_datas.*')
                             ->join('receive_records', 'receive_records.id', '=', 'receive_datas.record_id')
                             ->join('receives', 'receives.id', '=', 'receive_records.receive_id')
                             ->where('receives.id', $receive_frame->receive_id)
                             ->orderBy('receive_datas.id', 'asc')
                             ->get();

        /*
            ダウンロード前の配列イメージ。
            0行目をreceives.columns から生成。
            1行目以降はデータ

            0 [
                0 => 'temperature'
                1 => 'humidity'
                2 => 'created_at'
            ]
            1 [
                'temperature' => 20.5
                'humidity'    => 65.1
                'created_at'  => 2019-11-04 23:19:13
            ]
            2 [
                'temperature' => 21.5
                'humidity'    => 67.5
                'created_at'  => 2019-11-04 23:19:57
            ]
        */

        // 返却用配列
        $csv_array = array();

        // データ行用の空配列
        $copy_base = array();

        // columns から配列生成
        $columns = explode(',', $receive_frame->columns);

        // 見出し行
        foreach($columns as $column) {
            $csv_array[0][] = $column;
            $copy_base[$column] = '';
        }
        $csv_array[0][] = 'created_at';
        $copy_base['created_at'] = '';

        // データ
        foreach($receive_datas as $data) {
            if (!array_key_exists($data->record_id, $csv_array)) {
                $csv_array[$data->record_id] = $copy_base;
                $csv_array[$data->record_id]['created_at'] = $data->created_at;
            }
            $csv_array[$data->record_id][$data->column_key] = $data->value;
        }

        // レスポンス出力
        $filename = $receive_frame->dataset_name . '.csv';
        $headers = [
            'Content-Type' => 'text/csv',
            'content-Disposition' => 'attachment; filename="'.$filename.'"',
        ];
 
        // データ
        $csv_data = '';
        foreach($csv_array as $csv_line) {
            foreach($csv_line as $csv_col) {
                $csv_data .= '"' . $csv_col . '",';
            }
            $csv_data .= "\n";
        }

        // 文字コード変換
        $csv_data = mb_convert_encoding($csv_data, "SJIS-win");

        return response()->make($csv_data, 200, $headers);
    }

    /**
     * データ選択表示関数
     */
    public function listBuckets($request, $page_id, $frame_id, $id = null)
    {
        // Frame データ
        $receive_frame = $this->getReceiveFrame($frame_id);

        // データ取得（1ページの表示件数指定）
        $receives = Receives::orderBy('created_at', 'desc')
                            ->paginate(10);

        // 表示テンプレートを呼び出す。
        return $this->view(
            'receives_list_buckets', [
            'receive_frame' => $receive_frame,
            'receives'      => $receives,
        ]);
    }

    /**
     * 収集データ新規作成画面
     */
    public function createBuckets($request, $page_id, $frame_id, $id = null, $create_flag = false, $message = null, $errors = null)
    {
        // 新規作成フラグを付けて収集データ設定変更画面を呼ぶ
        $create_flag = true;
        return $this->editBuckets($request, $page_id, $frame_id, $id, $create_flag, $message, $errors);
    }

    /**
     * 収集データ設定変更画面の表示
     */
    public function editBuckets($request, $page_id, $frame_id, $id = null, $create_flag = false, $message = null, $errors = null)
    {
        // セッション初期化などのLaravel 処理。
        $request->flash();

        // 収集データ＆フレームデータ
        $receive_frame = $this->getReceiveFrame($frame_id);

        // データ収集設定データ
        $receive = new Receive();

        // id が渡ってくればid が対象
        if (!empty($id)) {
            $receive = Receive::where('id', $id)->first();
        }
        // Frame のbucket_id があれば、bucket_id からデータ収集設定取得、なければ、新規作成か選択へ誘導
        else if (!empty($receive_frame->bucket_id) && $create_flag == false) {
            $receive = Receive::where('bucket_id', $receive_frame->bucket_id)->first();
        }
        // 表示テンプレートを呼び出す。
        return $this->view(
            'receives_edit_receives', [
            'receive_frame' => $receive_frame,
            'receive'       => $receive,
            'create_flag'   => $create_flag,
            'message'       => $message,
            'errors'        => $errors,
        ])->withInput($request->all);
    }

    /**
     *  収集データ設定登録処理
     */
    public function saveBuckets($request, $page_id, $frame_id, $id = null)
    {
        // 項目のエラーチェック
        $validator = Validator::make($request->all(), [
            'key'          => ['required'],
            'token'        => ['required'],
            'dataset_name' => ['required'],
            'columns'      => ['required'],
        ]);
        $validator->setAttributeNames([
            'key'          => 'APIキー',
            'token'        => 'APIトークン',
            'dataset_name' => 'データセット名',
            'columns'      => 'カラム',
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
        if (empty($request->receives_id)) {

            // バケツの登録
            $bucket_id = DB::table('buckets')->insertGetId([
                  'bucket_name' => '無題',
                  'plugin_name' => 'receives'
            ]);

            // データ収集新規オブジェクト
            $receives = new Receive();
            $receives->bucket_id = $bucket_id;

            // Frame のBuckets を見て、Buckets が設定されていなければ、作成したものに紐づける。
            // Frame にBuckets が設定されていない ＞ 新規のフレーム＆データ収集作成
            // Frame にBuckets が設定されている ＞ 既存のフレーム＆データ収集更新
            // （データ収集選択から遷移してきて、内容だけ更新して、フレームに紐づけないケースもあるため）
            $frame = Frame::where('id', $frame_id)->first();
            if (empty($frame->bucket_id)) {

                // FrameのバケツIDの更新
                $frame = Frame::where('id', $frame_id)->update(['bucket_id' => $bucket_id]);
            }

            $message = 'データ収集設定を追加しました。';
        }
        // receive_id があれば、データ収集設定を更新
        else {

            // データ収集設定取得
            $receives = Receive::where('id', $request->receives_id)->first();

            $message = 'データ収集設定を変更しました。';
        }

        // データ収集設定
        $receives->key          = $request->key;
        $receives->token        = $request->token;
        $receives->dataset_name = $request->dataset_name;
        $receives->columns      = $request->columns;

        // データ保存
        $receives->save();

        // 新規作成フラグを付けてデータ収集設定変更画面を呼ぶ
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

        // データ収集選択画面を呼ぶ
        return $this->listBuckets($request, $page_id, $frame_id, $id);
    }
}
