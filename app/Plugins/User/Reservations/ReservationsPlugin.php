<?php

namespace App\Plugins\User\Reservations;

use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;

use App\Models\Core\Configs;
use App\Models\Common\Buckets;
use App\Models\Common\Frame;
use App\Models\Common\Page;

use DB;

use App\Models\User\Reservations\Reservations;
//use App\Models\User\Reservations\ReservationsNos;
//use App\Models\User\Reservations\ReservationsPosts;
//use App\Models\User\Reservations\ReservationsViews;

use App\Plugins\User\UserPluginBase;

/**
 * 施設予約プラグイン
 *
 * @author 井上 雅人 <inoue@opensource-workshop.jp / masamasamasato0216@gmail.com>
 * @copyright OpenSource-WorkShop Co.,Ltd. All Rights Reserved
 * @category 施設予約プラグイン
 * @package Contoroller
 */
class ReservationsPlugin extends UserPluginBase
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
        $functions['get']  = ['week', 'month', 'editBucketsRoles', 'editFacilities'];
        $functions['post'] = ['saveBucketsRoles'];
        return $functions;
    }

    /**
     *  編集画面の最初のタブ（コアから呼び出す）
     *
     *  スーパークラスをオーバーライド
     */
    public function getFirstFrameEditAction()
    {
        return "editFacilities";
    }

    /**
     *  POST取得関数（コアから呼び出す）
     *  コアがPOSTチェックの際に呼び出す関数
     */
    public function getPost($id) {

        // 一度読んでいれば、そのPOSTを再利用する。
        if (!empty($this->post)) {
            return $this->post;
        }

        // POST を取得する。
        $this->post = null;
        // DB read

        return $this->post;
    }

    /* private関数 */

    /**
     *  紐づく施設予約ID とフレームデータの取得
     */
    private function getReservationsFrame($frame_id)
    {
        // Frame と紐づく施設データを取得
        $frame = DB::table('frames')
                 ->select('frames.*', 'reservations.id as reservation_id', 'reservations.*')
                 ->leftJoin('reservations', 'reservations.bucket_id', '=', 'frames.bucket_id')
                 ->where('frames.id', $frame_id)
                 ->first();

        return $frame;
    }

    /**
     *  施設予約登録チェック設定
     */
    private function makeValidator($request)
    {
        // 項目のエラーチェック
        $validator = Validator::make($request->all(), [
            //'post_title' => ['required'],
            //'posted_at'  => ['required', 'date_format:Y-m-d H:i:s'],
            //'post_text'  => ['required'],
        ]);
        $validator->setAttributeNames([
            //'post_title' => 'タイトル',
            //'posted_at'  => '投稿日時',
            //'post_text'  => '本文',
        ]);
        return $validator;
    }

    /**
     *  施設予約 登録一覧取得
     */
    private function getPosts($blog_frame)
    {
        $reservations = null;

        // 削除されていないデータでグルーピングして、最新のIDで全件
        $reservations = null;
//        $reservations = Reservations::where('年月 or 週')->get();

        return $blogs_posts;
    }

    /**
     *  要承認の判断
     */
    private function isApproval($frame_id)
    {
        return $this->buckets->needApprovalUser(Auth::user());
    }

    /* スタティック関数 */

    /**
     *  新着情報用メソッド
     */
    public static function getWhatsnewArgs()
    {

        // 戻り値('sql_method'、'link_pattern'、'link_base')

        $return[] = array();
        //$return[] = Reservations::where('日付など')
        //                        ->get(;
        //$return[] = 'show_page_frame_post';
        //$return[] = '/plugin/reservations/show';

        return $return;
    }

    /* 画面アクション関数 */

    /**
     *  データ初期表示関数
     *  コアがページ表示の際に呼び出す関数
     */
    public function index($request, $page_id, $frame_id, $view_format = null)
    {
        // 施設予約＆フレームデータ
        $reservations_frame = $this->getReservationsFrame($frame_id);
        if (empty($reservations_frame)) {
            return;
        }

        // 施設予約表示設定
        //$reservations_views = ReservationsViews::where('frame_id', $reservations_frame->id);

        // 予約データ
        // デフォルトはシステム日付から予約データを取得
        $reservations = null;

        // 表示テンプレートを呼び出す。
        if ($view_format == 'week') {
            return $this->view(
                'reservations_week', [
                'reservations' => $reservations,
            ]);
        }
        return $this->view(
            'reservations_month', [
            'reservations' => $reservations,
        ]);
    }

    /**
     *  週表示関数
     */
    public function week($request, $page_id, $frame_id)
    {
        return $this->index($request, $page_id, $frame_id, 'week');
    }

    /**
     *  月表示関数
     */
    public function month($request, $page_id, $frame_id)
    {
        return $this->index($request, $page_id, $frame_id, 'month');
    }

    /**
     *  新規予約画面
     */
    public function create($request, $page_id, $frame_id, $id = null, $errors = null)
    {
        // セッション初期化などのLaravel 処理。
        $request->flash();

        // 施設予約＆フレームデータ
        $reservations_frame = $this->getReservationsFrame($frame_id);

        // 空のデータ(画面で初期値設定で使用するため)
        //$reservations = new Reservations();
        //$reservations->posted_at = date('Y-m-d H:i:s');

        // 表示テンプレートを呼び出す。(blade でold を使用するため、withInput 使用)
        return $this->view(
            'reservations_input', [
            'reservation_frame' => $reservation_frame,
            'errors'            => $errors,
        ])->withInput($request->all);
    }

    /**
     *  詳細表示関数
     */
    public function show($request, $page_id, $frame_id, $id = null)
    {
        // Frame データ
        $reservations_frame = $this->getReservationsFrame($frame_id);

        // 予約取得
        $reservation = $this->getPost($id);
        if (empty($reservation)) {
            return $this->view_error("403_inframe", null, 'show データなし');
        }

        // 詳細画面を呼び出す。
        return $this->view(
            'reservations_show', [
            'reservations_frame'  => $reservations_frame,
            'reservation'         => $reservation,
        ]);
    }

    /**
     * 予約編集画面
     */
    public function edit($request, $page_id, $frame_id, $id = null, $errors = null)
    {
        // セッション初期化などのLaravel 処理。
        $request->flash();

        // Frame データ
        $reservations_frame = $this->getReservationsFrame($frame_id);

        // 予約取得
        $reservation = $this->getPost($id);
        if (empty($reservation)) {
            return $this->view_error("403_inframe", null, 'edit データなし');
        }

        // 変更画面を呼び出す。(blade でold を使用するため、withInput 使用)
        return $this->view(
            'reservations_edit', [
            'reservations_frame'  => $reservations_frame,
            'reservation'         => $reservation,
        ])->withInput($request->all);
    }

    /**
     *  予約登録処理
     */
    public function save($request, $page_id, $frame_id, $id = null)
    {
        // 項目のエラーチェック
        $validator = $this->makeValidator($request);

        // エラーがあった場合は入力画面に戻る。
        if ($validator->fails()) {
            return ( $this->create($request, $page_id, $frame_id, $id, $validator->errors()) );
        }

        // id があれば更新、なければ新規
        $reservations = null;

        if (!empty($id)) {
            //$reservations = Reservations::where('id', $id)->first();
        }
        if (empty($reservations)) {
            //$reservations = new Reservations();

            // 登録ユーザ
            //$reservations->created_id  = Auth::user()->id;
        }

        // 予約内容
        //$reservations->施設ID   = $request->施設ID;
        //$reservations->開始日時 = $request->開始日時;
        //$reservations->終了日時 = $request->終了日時;

        // 承認の要否確認とステータス処理
        if ($this->isApproval($frame_id)) {
            //$reservations->status = 2;
        }

        // 予約データ保存
        //$reservations->save();

        // 登録後は表示用の初期処理を呼ぶ。
        return $this->index($request, $page_id, $frame_id);
    }

    /**
     *  削除処理
     */
    public function delete($request, $page_id, $frame_id, $id)
    {
        // id がある場合、データを削除
        if ( $id ) {

            // データを削除する。
            //Reservations::where('id', $id)->delete();
        }
        // 削除後は表示用の初期処理を呼ぶ。
        return $this->index($request, $page_id, $frame_id);
    }

   /**
    * 承認
    */
    public function approval($request, $page_id = null, $frame_id = null, $id = null)
    {
        // id で予約データ取得、status を0 に更新

        // 承認後は表示用の初期処理を呼ぶ。
        return $this->index($request, $page_id, $frame_id);
    }

    /**
     * データ選択表示関数
     */
    public function listBuckets($request, $page_id, $frame_id, $id = null)
    {
        // Frame データ
        $reservation_frame = null;

        $reservation_frame = Frame::select('frames.*', 'reservations.id as reservations_id', 'reservations.*')
                           ->leftJoin('reservations', 'reservations.bucket_id', '=', 'frames.bucket_id')
                           ->where('frames.id', $frame_id)->first();

        // 施設取得（1ページの表示件数指定）
        $reservations = array();
        $reservations = Reservations::orderBy('created_at', 'desc')
                      ->paginate(10);

        // 表示テンプレートを呼び出す。
        return $this->view(
            'reservations_list_buckets', [
            'reservation_frame' => $reservation_frame,
            'reservations'      => $reservations,
        ]);
    }

    /**
     * 施設新規作成画面
     */
    public function createBuckets($request, $page_id, $frame_id, $id = null, $create_flag = false, $message = null, $errors = null)
    {
        // 新規作成フラグを付けて施設設定変更画面を呼ぶ
        $create_flag = true;
        return $this->editBuckets($request, $page_id, $frame_id, $id, $create_flag, $message, $errors);
    }

    /**
     * 施設設定変更画面の表示
     */
    public function editBuckets($request, $page_id, $frame_id, $reservation_id = null, $create_flag = false, $message = null, $errors = null)
    {
        // セッション初期化などのLaravel 処理。
        $request->flash();

        // 施設予約＆フレームデータ
        $reservation_frame = $this->getReservationsFrame($frame_id);

        // 施設データ
        $reservation = new Reservations();

        // id が渡ってくればid が対象
        if (!empty($reservation_id)) {
            $reservation = Reservations::where('id', $reservation_id)->first();
        }
        // Frame のbucket_id があれば、bucket_id から施設データ取得、なければ、新規作成か選択へ誘導
        else if (!empty($reservation_frame->bucket_id) && $create_flag == false) {
            $reservation = Reservations::where('bucket_id', $reservation_frame->bucket_id)->first();
        }

        // 表示テンプレートを呼び出す。
        return $this->view(
            'reservations_edit', [
            'reservation_frame'  => $reservation_frame,
            'reservation'        => $reservation,
            'create_flag'        => $create_flag,
            'message'            => $message,
            'errors'             => $errors,
        ])->withInput($request->all);
    }

    /**
     *  施設登録処理
     */
    public function saveBuckets($request, $page_id, $frame_id, $reservation_id = null)
    {
        // 項目のエラーチェック
        $validator = Validator::make($request->all(), [
            'reservation_name'  => ['required'],
            'initial_display_setting'  => ['required'],
        ]);
        $validator->setAttributeNames([
            'reservation_name'  => '施設予約名',
            'initial_display_setting'  => '初期表示設定',
        ]);

        // エラーがあった場合は入力画面に戻る。
        $message = null;
        if ($validator->fails()) {

            if (empty($reservation_id)) {
                $create_flag = true;
                return $this->createBuckets($request, $page_id, $frame_id, $reservation_id, $create_flag, $message, $validator->errors());
            }
            else  {
                $create_flag = false;
                return $this->editBuckets($request, $page_id, $frame_id, $reservation_id, $create_flag, $message, $validator->errors());
            }
        }

        // 更新後のメッセージ
        $message = null;

        // 画面から渡ってくるid が空ならバケツと施設を新規登録
        if (empty($request->id)) {

            // バケツの登録
            $bucket_id = DB::table('buckets')->insertGetId([
                  'bucket_name' => $request->reservation_name,
                  'plugin_name' => 'reservations'
            ]);

            // 施設予約データ新規オブジェクト
            $reservations = new Reservations();
            $reservations->bucket_id = $bucket_id;

            // Frame のBuckets を見て、Buckets が設定されていなければ、作成したものに紐づける。
            // Frame にBuckets が設定されていない ＞ 新規のフレーム＆施設予約作成
            // Frame にBuckets が設定されている ＞ 既存のフレーム＆施設予約更新
            // （表示施設予約選択から遷移してきて、内容だけ更新して、フレームに紐づけないケースもあるため）
            $frame = Frame::where('id', $frame_id)->first();
            if (empty($frame->bucket_id)) {

                // FrameのバケツIDの更新
                $frame = Frame::where('id', $frame_id)->update(['bucket_id' => $bucket_id]);
            }

            $message = '施設予約の設定を追加しました。';
        }
        // id があれば、施設予約を更新
        else {

            // 施設予約データ取得
            $reservations = Reservations::where('id', $reservation_id)->first();

            $message = '施設予約の設定を変更しました。';
        }

        // 施設設定
        $reservations->reservation_name = $request->reservation_name;
        $reservations->initial_display_setting = $request->initial_display_setting;

        // データ保存
        $reservations->save();

        // 新規作成フラグを付けて施設設定変更画面を呼ぶ
        $create_flag = false;
        return $this->editBuckets($request, $page_id, $frame_id, $reservation_id, $create_flag, $message);
    }

    /**
     *  削除処理
     */
    public function destroyBuckets($request, $page_id, $frame_id, $id)
    {
        // id がある場合、データを削除
        if ($id) {

            // 予約データを削除する。
            //ReservationsPost::where('reservations_id', $id)->delete();

            // 施設設定を削除する。
            //Reservations::delete($id);

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

        // 表示施設予約選択画面を呼ぶ
        return $this->listBuckets($request, $page_id, $frame_id, $id);
    }

    /**
     * 施設登録・変更画面の表示
     */
    public function editFacilities($request, $page_id, $frame_id, $id = null, $create_flag = false, $message = null, $errors = null)
    {
        // セッション初期化などのLaravel 処理。
        $request->flash();

        // 施設予約＆フレームデータ
        $reservation_frame = $this->getReservationsFrame($frame_id);

        // 施設データ
        $reservation = new Reservations();

        // id が渡ってくればid が対象
        if (!empty($id)) {
            $reservation = Reservations::where('id', $id)->first();
        }
        // Frame のbucket_id があれば、bucket_id から施設データ取得、なければ、新規作成か選択へ誘導
        else if (!empty($reservation_frame->bucket_id) && $create_flag == false) {
            $reservation = Reservations::where('bucket_id', $reservation_frame->bucket_id)->first();
        }

        // 表示テンプレートを呼び出す。
        return $this->view(
            'reservations_edit', [
            'reservation_frame'  => $reservation_frame,
            'reservation'        => $reservation,
            'create_flag'        => $create_flag,
            'message'            => $message,
            'errors'             => $errors,
        ])->withInput($request->all);
    }
}
