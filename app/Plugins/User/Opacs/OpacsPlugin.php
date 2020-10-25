<?php

namespace App\Plugins\User\Opacs;

use SimpleXMLElement;

use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\MessageBag;

use DB;

use App\User;
use App\Models\Common\Buckets;
use App\Models\Common\Frame;
use App\Models\Common\Page;
use App\Models\Core\Configs;
use App\Models\Core\UsersRoles;
use App\Models\User\Opacs\Opacs;
use App\Models\User\Opacs\OpacsBooks;
use App\Models\User\Opacs\OpacsBooksLents;
use App\Models\User\Opacs\OpacsFrames;
use App\Models\User\Opacs\OpacsConfigs;

use App\Mail\ConnectMail;
use App\Plugins\User\UserPluginBase;

/**
 * Opacプラグイン
 *
 * @author 永原　篤 <nagahara@opensource-workshop.jp>
 * @copyright OpenSource-WorkShop Co.,Ltd. All Rights Reserved
 * @category Opacプラグイン
 * @package Contoroller
 */
class OpacsPlugin extends UserPluginBase
{

    /* オブジェクト変数 */

    /* コアから呼び出す関数 */

    /**
     *  関数定義（コアから呼び出す）
     */
    public function getPublicFunctions()
    {
        // 標準関数以外で画面などから呼ばれる関数の定義
        $functions = array();
        $functions['get']  = ['settingOpacFrame', 'rentlist'];
        $functions['post'] = ['lent', 'requestLent', 'returnLent', 'search', 'saveOpacFrame'];
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
        // [TODO] 【各プラグイン】declareRoleファンクションで適切な追加の権限定義を設定する https://github.com/opensource-workshop/connect-cms/issues/658
        $role_ckeck_table = array();
        return $role_ckeck_table;
    }

    /**
     *  編集画面の最初のタブ
     *
     *  スーパークラスをオーバーライド
     */
    public function getFirstFrameEditAction()
    {
        return "editBuckets";
    }

    /* private 関数 */

    /**
     *  紐づくOPAC ID とフレームデータの取得
     */
    private function getOpacFrame($frame_id)
    {
        // Frame データ
        $frame = DB::table('frames')
                 ->select('frames.*', 'opacs.id as opacs_id', 'opacs.opac_name', 'opacs.view_count', 'lent_setting', 'lent_limit')
                 ->leftJoin('opacs', 'opacs.bucket_id', '=', 'frames.bucket_id')
                 ->where('frames.id', $frame_id)
                 ->first();
        return $frame;
    }

    /**
     *  書誌データ取得
     */
    private function getBook($request, $opacs_books)
    {
        if (empty($request->isbn)) {
            return;
        }

        // 国会図書館API
        $request_url = 'http://iss.ndl.go.jp/api/opensearch?isbn=' . $request->isbn;

        // $context = stream_context_create(array(
        //     'http' => array('ignore_errors' => true, 'timeout' => 10)
        // ));

        // NDL OpenSearch 呼び出しと結果のXML 取得
        $xml = null;
        try {
//              $xml_string = file_get_contents($request_url, false, $context);
//              $xml = simplexml_load_string($xml_string, 'SimpleXMLElement', LIBXML_NOERROR|LIBXML_ERR_NONE|LIBXML_ERR_FATAL);
//            $xml = simplexml_load_file($request_url);

            $ch = curl_init();
            curl_setopt($ch, CURLOPT_URL, $request_url);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            $xml_string = curl_exec($ch);
            $xml = simplexml_load_string($xml_string, 'SimpleXMLElement', LIBXML_NOERROR|LIBXML_ERR_NONE|LIBXML_ERR_FATAL);
            //var_dump($xml);
        } catch (Exception $e) {
            // Log::debug($e);
            return array($opacs_books, "書誌データ取得でエラーが発生しました。");
        }



        // http://iss.ndl.go.jp/api/opensearch?isbn=9784063655407
        // echo $xml->channel->item[1]->children('dc', true)->publisher;
        // echo $xml->channel->item->count();
        // var_dump($xml->channel->item[1]);
        // print_r($xml);

        // 結果が取得できた場合
        //var_dump($xml);
        $totalResults = $xml->channel->children('openSearch', true)->totalResults;
        if ($totalResults == 0) {
            return array($opacs_books, "書誌データが見つかりませんでした。");
        }
        if (!$xml) {
            return array($opacs_books, "取得した書誌データでエラーが発生しました。");
        } else {
            $target_item = null;
            $channel = get_object_vars($xml->channel);

            if (is_array($channel["item"])) {
                $target_item = end($channel["item"]);
            } else {
                $target_item = $channel["item"];
            }

            $opacs_books->title   = $target_item->title;
            $opacs_books->creator = $target_item->author;
            $opacs_books->publisher = $target_item->children('dc', true)->publisher;
        }

        return array($opacs_books, "");
    }

    /* 画面アクション関数 */

   /**
    * lent_flag        = 9:貸出終了(貸し出し可能)、1:貸し出し中、2:貸し出しリクエスト受付中
    * scheduled_return = 返却予定日(日付)
    * lent_at          = 貸し出し日時(日時)
    */

    /**
     *  書誌データ取得関数
     *
     * @return view
     */
    public function index($request, $page_id, $frame_id, $errors = null, $messages = null)
    {
        // セッション初期化などのLaravel 処理。
        $request->flash();

        // 処理の振り分け用にフレームの設定を取得
        $opacs_frames_setting = OpacsFrames::where('frames_id', $frame_id)->first();

        // フレーム設定がまだの場合
        if (empty($opacs_frames_setting)) {
            // 表示テンプレートを呼び出す。
            return $this->view('opacs_no_frame_setting');
        }

        // 処理の振り分け
        if ($opacs_frames_setting->view_form == 0) {
            return $this->indexMyOpac($request, $page_id, $frame_id, $errors, $messages);
        } else {
            return $this->indexSearch($request, $page_id, $frame_id);
        }
    }

    /**
     *  返却期限を取得
     */
    public static function getReturnMaxDate($opac, $users_roles, $opac_configs)
    {
        // 返却値
        $ret = 0;

        if ($opac->lent_setting == 2) {
            // 貸し出し許可日数を設定して貸し出しする。
            if (array_key_exists('lent_days_global', $opac_configs)) {
                $ret = $opac_configs['lent_days_global'];
            }
        } elseif ($opac->lent_setting == 3) {
            // 役割毎に貸し出し許可日数を設定して貸し出しする。
            // ユーザに設定されている役割をループし、Opac設定の役割毎貸し出し許可日数を取得。一番長い日数を採用する。
            foreach ($users_roles as $users_role) {
                if ($users_role->target == 'original_role') {
                    if (array_key_exists('lent_days_'.$users_role->role_name, $opac_configs)) {
                        if ($ret < $opac_configs['lent_days_'.$users_role->role_name]) {
                            $ret = $opac_configs['lent_days_'.$users_role->role_name];
                        }
                    }
                }
            }
        } else {
            // 貸し出ししない or 貸し出し許可日数を設定せずに貸し出しする。
        }

        return strtotime("+" . $ret ." day");
    }

    /**
     *  貸し出し冊数を取得
     */
    public static function getReturnMaxLentCount($opac, $users_roles, $opac_configs)
    {
        // 返却値
        $ret = 0;

        // 貸し出し冊数を設定して貸し出しする。
        if ($opac->lent_limit == 1) {
            if (array_key_exists('lent_kumit_global', $opac_configs)) {
                $ret = $opac_configs['lent_limit_global'];
            }
        } elseif ($opac->lent_limit == 2) {
            // 役割毎に貸し出し冊数を設定して貸し出しする。
            // ユーザに設定されている役割をループし、Opac設定の役割毎貸し出し冊数を取得。一番多い冊数を採用する。
            foreach ($users_roles as $users_role) {
                if ($users_role->target == 'original_role') {
                    if (array_key_exists('lent_limit_'.$users_role->role_name, $opac_configs)) {
                        if ($ret < $opac_configs['lent_limit_'.$users_role->role_name]) {
                            $ret = $opac_configs['lent_limit_'.$users_role->role_name];
                        }
                    }
                }
            }
        } else {
            // 貸し出ししない or 貸し出し冊数を設定せずに貸し出しする。
        }

        return $ret;
    }

    /**
     *  初期表示（MyOpac）
     *
     * @return view
     */
    public function indexMyOpac($request, $page_id, $frame_id, $errors = null, $messages = null)
    {
        // ブログ＆フレームデータ
        $opac_frame = $this->getOpacFrame($frame_id);
        if (empty($opac_frame)) {
            return;
        }

        // ログインチェック
        $user = Auth::user();
        if (empty($user)) {
            return $this->view('opacs_no_login');
        }

        // すでに借りている書籍を取得
        $lents = OpacsBooksLents::select('opacs_books_lents.*', 'opacs_books.barcode', 'title')
                                ->leftJoin('opacs_books', function ($join) use ($opac_frame) {
                                    $join->on('opacs_books.id', '=', 'opacs_books_lents.opacs_books_id')
                                         ->where('opacs_books.opacs_id', '=', $opac_frame->opacs_id);
                                })
                                ->where('student_no', $user->userid)
                                ->get();

        // 役割設定の情報取得
        $original_roles = Configs::where('category', 'original_role')->orderBy('additional1', 'asc')->get();

        // Opac の設定情報
        $opac_configs = OpacsConfigs::getConfigs($opac_frame->opacs_id, $original_roles);

        // 返却期限、貸出最大冊数を取得のため、ユーザのroleを取得
        $users_roles = UsersRoles::where('users_id', $user->id)
                                 ->where('target', 'original_role')
                                 ->get();

        // 返却期限
        $lent_max_ts = self::getReturnMaxDate($opac_frame, $users_roles, $opac_configs);
        $lent_max_date = date('Y年m月d日', $lent_max_ts);

        // 貸出最大冊数
        $lent_max_count = self::getReturnMaxLentCount($opac_frame, $users_roles, $opac_configs);

        // 書籍の貸出OKの判定
        // モデレータ以上の場合はOK
        if ($this->isCan('role_article')) {
            $lent_count_ok = true;
        } else {
            if ($lent_max_count > count($lents)) {
                $lent_count_ok = true;
            } else {
                $lent_count_ok = false;
            }
        }

        // 書籍の返却OKの判定
        if ($this->isCan('role_article')) {
            $lent_return_ok = true;
        } else {
            if (count($lents) > 0) {
                $lent_return_ok = true;
            } else {
                $lent_return_ok = false;
            }
        }


        // 表示テンプレートを呼び出す。
        return $this->view(
            'opacs_my', [
            'opac_frame'     => $opac_frame,
            'user'           => $user,
            'lents'          => $lents,
            'lent_max_date'  => $lent_max_date,
            'lent_max_count' => $lent_max_count,
            'lent_count_ok'  => $lent_count_ok,
            'lent_return_ok' => $lent_return_ok,
            'errors'         => $errors,
            'messages'       => $messages,
            ]
        );
    }

    /**
     *  初期表示（書籍検索）
     *
     * @return view
     */
    public function indexSearch($request, $page_id, $frame_id)
    {
        // ブログ＆フレームデータ
        $opac_frame = $this->getOpacFrame($frame_id);
        if (empty($opac_frame)) {
            return;
        }

        // Page データ
        $page = Page::where('id', $page_id)->first();

        // 検索キーワード
        $keyword = $request->session()->get('search_keyword');

        // データ取得（1ページの表示件数指定）
        if (empty($opac_frame->opacs_id)) {
            $opacs_books = null;
        } elseif (empty($keyword)) {
            $opacs_books = null;
/*
            $opacs_books = DB::table('opacs_books')
                          ->select('opacs_books.*', 'opacs_books_lents.lent_flag', 'opacs_books_lents.student_no', 'opacs_books_lents.return_scheduled', 'opacs_books_lents.lent_at')
                          ->leftJoin('opacs_books_lents', function ($join) {
                              $join->on('opacs_books_lents.opacs_books_id', '=', 'opacs_books.id')
                                  ->wherein('opacs_books_lents.lent_flag', [1, 2]);
                          })
                          ->where('opacs_id', $opac_frame->opacs_id)
                          ->orderBy('accept_date', 'desc')
                          ->paginate($opac_frame->view_count, ["*"], "frame_{$opac_frame->id}_page");
*/
        } else {
            $opacs_books = DB::table('opacs_books')
                          ->select('opacs_books.*', 'opacs_books_lents.lent_flag', 'opacs_books_lents.student_no', 'opacs_books_lents.return_scheduled', 'opacs_books_lents.lent_at')
                          ->leftJoin('opacs_books_lents', function ($join) {
                              $join->on('opacs_books_lents.opacs_books_id', '=', 'opacs_books.id')
                                  ->wherein('opacs_books_lents.lent_flag', [1, 2]);
                          })
                          ->where('opacs_id', $opac_frame->opacs_id)
                          ->where(function ($query) use ($keyword) {
                              $query->Where('isbn', 'like', '%' . $keyword . '%')
                                  ->orWhere('title', 'like', '%' . $keyword . '%')
                                  ->orWhere('ndc', 'like', '%' . $keyword . '%')
                                  ->orWhere('creator', 'like', '%' . $keyword . '%')
                                  ->orWhere('publisher', 'like', '%' . $keyword . '%')
                                  ->orWhere('barcode', 'like', '%' . $keyword . '%');
                          })
                          ->orderBy('accept_date', 'desc')
                          ->paginate($opac_frame->view_count, ["*"], "frame_{$opac_frame->id}_page");
        }

        // 表示テンプレートを呼び出す。
        return $this->view(
            'opacs', [
            'opac_frame'  => $opac_frame,
            'opacs_books' => $opacs_books,
            ]
        );
    }

    /**
     * データ選択表示関数
     */
    public function listBuckets($request, $page_id, $frame_id, $id = null)
    {
        // Frame データ
        $opac_frame = DB::table('frames')
                      ->select('frames.*', 'opacs.id as opacs_id', 'opacs.view_count')
                      ->leftJoin('opacs', 'opacs.bucket_id', '=', 'frames.bucket_id')
                      ->where('frames.id', $frame_id)->first();

        // データ取得（1ページの表示件数指定）
        $opacs = Opacs::orderBy('created_at', 'desc')
                       ->paginate(10, ["*"], "frame_{$frame_id}_page");

        // 表示テンプレートを呼び出す。
        return $this->view(
            'opacs_list_buckets', [
            'opac_frame' => $opac_frame,
            'opacs'      => $opacs,
            ]
        );
    }

    /**
     * OPAC新規作成画面
     */
    public function createBuckets($request, $page_id, $frame_id, $opacs_id = null, $create_flag = false, $message = null, $errors = null)
    {
        // 新規作成フラグを付けてOPAC設定変更画面を呼ぶ
        $create_flag = true;
        return $this->editBuckets($request, $page_id, $frame_id, $opacs_id, $create_flag, $message, $errors);
    }

    /**
     * OPAC設定変更画面の表示
     */
    public function editBuckets($request, $page_id, $frame_id, $opacs_id = null, $create_flag = false, $message = null, $errors = null)
    {
        // セッション初期化などのLaravel 処理。
        $request->flash();

        // OPAC＆フレームデータ
        $opac_frame = $this->getOpacFrame($frame_id);

        // OPACデータ
        $opac = new Opacs();

        if (!empty($opacs_id)) {
            // opacs_id が渡ってくればopacs_id が対象
            $opac = Opacs::where('id', $opacs_id)->first();
        } elseif (!empty($opac_frame->bucket_id) && $create_flag == false) {
            // Frame のbucket_id があれば、bucket_id からOPACデータ取得、なければ、新規作成か選択へ誘導
            $opac = Opacs::where('bucket_id', $opac_frame->bucket_id)->first();
        }

        // 役割設定の情報取得
        $original_roles = Configs::where('category', 'original_role')->orderBy('additional1', 'asc')->get();

        // Opac の設定情報
        $opac_configs = OpacsConfigs::getConfigs($opac->id, $original_roles);

        // 表示テンプレートを呼び出す。
        return $this->view(
            'opacs_edit_opac', [
            'opac_frame'     => $opac_frame,
            'opac'           => $opac,
            'original_roles' => $original_roles,
            'opac_configs'   => $opac_configs,
            'create_flag'    => $create_flag,
            'message'        => $message,
            'errors'         => $errors,
            ]
        )->withInput($request->all);
    }

    /**
     *  OPAC登録処理
     */
    public function saveBuckets($request, $page_id, $frame_id, $opacs_id = null)
    {
        // 役割設定の情報取得
        $original_roles = Configs::where('category', 'original_role')->orderBy('additional1', 'asc')->get();

        // 項目のエラーチェック条件設定
        $validator_columns = array(
            'opac_name'  => ['required'],
            'view_count' => ['required'],
        );
        $validator_attribute = array(
            'opac_name'  => 'OPAC名',
            'view_count' => '表示件数',
        );

        // 貸し出し許可日数を設定して貸し出しする。の場合は日数が必須
        if ($request->lent_setting == '2') {
            $validator_columns['opacs_configs.lent_days_global']   = ['required', 'numeric'];
            $validator_attribute['opacs_configs.lent_days_global'] = '日数';
        }

        // 役割毎に貸し出し許可日数を設定して貸し出しする。の場合は役割設定毎の日数が必須
        if ($request->lent_setting == '3') {
            foreach ($original_roles as $original_role) {
                $validator_columns['opacs_configs.lent_days_'.$original_role->name]   = ['required', 'numeric'];
                $validator_attribute['opacs_configs.lent_days_'.$original_role->name] = '日数';
            }
        }

        // 貸し出し冊数を設定して貸し出しする。の場合は冊数が必須
        if ($request->lent_limit == '1') {
            $validator_columns['opacs_configs.lent_limit_global']   = ['required', 'numeric'];
            $validator_attribute['opacs_configs.lent_limit_global'] = '冊数';
        }

        // 役割毎に冊数を設定して貸し出しする。の場合は役割設定毎の冊数が必須
        if ($request->lent_limit == '2') {
            foreach ($original_roles as $original_role) {
                $validator_columns['opacs_configs.lent_limit_'.$original_role->name]   = ['required', 'numeric'];
                $validator_attribute['opacs_configs.lent_limit_'.$original_role->name] = '冊数';
            }
        }

        // 項目のエラーチェック実施
        $validator = Validator::make($request->all(), $validator_columns);
        $validator->setAttributeNames($validator_attribute);

        // エラーがあった場合は入力画面に戻る。
        $message = null;
        if ($validator->fails()) {
            if (empty($opacs_id) && empty($request->opacs_id)) {
                $create_flag = true;
                return $this->createBuckets($request, $page_id, $frame_id, $opacs_id, $create_flag, $message, $validator->errors());
            } else {
                $create_flag = false;
                return $this->editBuckets($request, $page_id, $frame_id, $opacs_id, $create_flag, $message, $validator->errors());
            }
        }

        // 更新後のメッセージ
        $message = null;

        // 画面から渡ってくるopacs_id が空ならバケツとOPACを新規登録
        if (empty($request->opacs_id)) {
            // バケツの登録
            $bucket_id = DB::table('buckets')->insertGetId([
                  'bucket_name' => '無題',
                  'plugin_name' => 'opacs'
            ]);

            // OPACデータ新規オブジェクト
            $opacs = new Opacs();
            $opacs->bucket_id = $bucket_id;

            // Frame のBuckets を見て、Buckets が設定されていなければ、作成したものに紐づける。
            // Frame にBuckets が設定されていない ＞ 新規のフレーム＆OPAC作成
            // Frame にBuckets が設定されている ＞ 既存のフレーム＆OPAC更新
            // （表示OPAC選択から遷移してきて、内容だけ更新して、フレームに紐づけないケースもあるため）
            $frame = Frame::where('id', $frame_id)->first();
            if (empty($frame->bucket_id)) {
                // FrameのバケツIDの更新
                $frame = Frame::where('id', $frame_id)->update(['bucket_id' => $bucket_id]);
            }

            $message = 'OPAC設定を追加しました。';
        } else {
            // opacs_id があれば、OPACを更新
            // OPACデータ取得
            $opacs = Opacs::where('id', $request->opacs_id)->first();

            $message = 'OPAC設定を変更しました。';
        }

        // OPAC設定
        $opacs->opac_name                   = $request->opac_name;
        $opacs->view_count                  = $request->view_count;
        $opacs->moderator_mail_send_flag    = (empty($request->moderator_mail_send_flag)) ? 0 : $request->moderator_mail_send_flag;
        $opacs->moderator_mail_send_address = $request->moderator_mail_send_address;
        $opacs->lent_setting                = $request->lent_setting;
        $opacs->lent_limit                  = $request->lent_limit;

        // データ保存
        $opacs->save();

        // OpacConfigs テーブル保存
        // 貸出日数
        foreach ($request->opacs_configs as $config_name => $config_value) {
            OpacsConfigs::updateOrCreate(
                ['opacs_id' => $opacs->id, 'name' => $config_name],
                ['value' => intval($config_value)],
            );
        }

        // 新規作成フラグを付けてブログ設定変更画面を呼ぶ
        $create_flag = false;
        return $this->editBuckets($request, $page_id, $frame_id, $opacs_id, $create_flag, $message);
    }

    /**
     *  削除処理
     */
    public function destroyBuckets($request, $page_id, $frame_id, $opacs_id)
    {
        // opacs_id がある場合、データを削除
        if ($opacs_id) {
            // 書誌データを削除する。
            OpacsBooks::where('opacs_id', $opacs_id)->delete();

            // OPAC設定を削除する。
            Opacs::destroy($opacs_id);

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

        // 表示ブログ選択画面を呼ぶ
        return $this->listBuckets($request, $page_id, $frame_id, $id);
    }

    /**
     *  新規書誌データ画面
     */
    public function create($request, $page_id, $frame_id, $opacs_books_id = null, $errors = null)
    {
        // 権限チェック
        // 特別処理。role_article（記事修正）でチェック。
        if ($this->can('role_article')) {
            return $this->view_error(403);
        }

        // セッション初期化などのLaravel 処理。
        $request->flash();

        // OPAC＆フレームデータ
        $opac_frame = $this->getOpacFrame($frame_id);

        // 空のデータ(画面で初期値設定で使用するため)
        $opacs_books = new OpacsBooks();

        // 書誌データ取得の場合
        $search_error_message = '';
        if ($request->book_search == '1') {
            list($tmp_opacs_books, $search_error_message) = $this->getBook($request, $opacs_books);
            if (empty($tmp_opacs_books)) {
                $search_error_message = '書誌データが検索できませんでした。';
            } else {
                $opacs_books = $tmp_opacs_books;
            }
            //echo $opacs_books->title;
        }

        // 表示テンプレートを呼び出す。(blade でold を使用するため、withInput 使用)
        return $this->view(
            'opacs_input', [
            'opac_frame'  => $opac_frame,
            'opacs_books' => $opacs_books,
            'book_search' => $request->book_search,
            'errors'      => $errors,
            'search_error_message' => $search_error_message,
            ]
        )->withInput($request->all);
    }

    /**
     * 書誌データ編集画面
     */
    public function edit($request, $page_id, $frame_id, $opacs_books_id = null, $errors = null)
    {
        // 権限チェック
        // 特別処理。role_article（記事修正）でチェック。
        if ($this->can('role_article')) {
            return $this->view_error(403);
        }

        // セッション初期化などのLaravel 処理。
        $request->flash();

        // Frame データ
        $opac_frame = $this->getOpacFrame($frame_id);

        // 書誌データ取得
        $opacs_book = OpacsBooks::where('id', $opacs_books_id)->first();

        // 変更画面を呼び出す。(blade でold を使用するため、withInput 使用)
        return $this->view(
            'opacs_input', [
            'opac_frame'  => $opac_frame,
            'opacs_books' => $opacs_book,
            'errors'      => $errors,
            ]
        )->withInput($request->all);
    }

    /**
     * 書誌データ詳細画面
     */
    public function show($request, $page_id, $frame_id, $opacs_books_id, $message = null, $message_class = null, $errors = null)
    {
        // セッション初期化などのLaravel 処理。
        $request->flash();

        // Frame データ
        $opac_frame = $this->getOpacFrame($frame_id);

        // 書誌データ取得
        $opacs_book = DB::table('opacs_books')
                      ->select('opacs_books.*', 'opacs_books_lents.lent_flag', 'opacs_books_lents.student_no', 'opacs_books_lents.return_scheduled', 'opacs_books_lents.lent_at')
                      ->leftJoin('opacs_books_lents', function ($join) {
                          $join->on('opacs_books_lents.opacs_books_id', '=', 'opacs_books.id')
                              ->wherein('opacs_books_lents.lent_flag', [1, 2]);
                      })
                      ->where('opacs_books.id', $opacs_books_id)->first();

        // ログインチェック
        $user = Auth::user();

        $lent_max_date = "";
        $lent_limit_check = true;
        $lent_max_date = "";
        $lent_error_message = "";

        if (!empty($user)) {
            // 冊数による貸し出し制限
            list($lent_limit_check, $lent_error_message) = $this->lentCountCheck($opac_frame);

            // 役割設定の情報取得
            $original_roles = Configs::where('category', 'original_role')->orderBy('additional1', 'asc')->get();

            // Opac の設定情報
            $opac_configs = OpacsConfigs::getConfigs($opac_frame->opacs_id, $original_roles);

            // 返却期限、貸出最大冊数を取得のため、ユーザのroleを取得
            $users_roles = UsersRoles::where('users_id', $user->id)
                                     ->where('target', 'original_role')
                                     ->get();

            // 返却期限
            $lent_max_ts = self::getReturnMaxDate($opac_frame, $users_roles, $opac_configs);
            $lent_max_date = date('Y年m月d日', $lent_max_ts);
        }

        // 変更画面を呼び出す。(blade でold を使用するため、withInput 使用)
        return $this->view(
            'opacs_show', [
            'opac_frame'         => $opac_frame,
            'opacs_books'        => $opacs_book,
            'opacs_books_id'     => $opacs_books_id,
            'lent_limit_check'   => $lent_limit_check,
            'lent_max_date'      => $lent_max_date,
            'message'            => $message,
            'message_class'      => $message_class,
            'lent_error_message' => $lent_error_message,
            'errors'             => $errors,
            ]
        );
    }

    /**
     *  書誌データ登録処理
     */
    public function save($request, $page_id, $frame_id, $opacs_books_id = null)
    {
        // 権限チェック
        // 特別処理。role_article（記事修正）でチェック。
        if ($this->can('role_article')) {
            return $this->view_error(403);
        }

        // 項目のエラーチェック
        $validator = Validator::make($request->all(), [
            'title' => ['required'],
        ]);
        $validator->setAttributeNames([
            'title' => 'タイトル',
        ]);

        // エラーがあった場合は入力画面に戻る。
        if ($validator->fails()) {
            return ( $this->create($request, $page_id, $frame_id, $opacs_books_id, $validator->errors()) );
        }

        // 書誌データ取得の場合、入力画面に戻る
        if ($request->book_search == '1') {
            return ( $this->create($request, $page_id, $frame_id, $opacs_books_id, $validator->errors()) );
        }

        // id があれば更新、なければ登録
        if (empty($opacs_books_id)) {
            $opacs_book = new OpacsBooks();
        } else {
            $opacs_book = OpacsBooks::where('id', $opacs_books_id)->first();
        }

        // 書誌データ設定
        $opacs_book->opacs_id          = $request->opacs_id;
        $opacs_book->isbn              = $request->isbn;
        $opacs_book->title             = $request->title;
        $opacs_book->ndc               = $request->ndc;
        $opacs_book->creator           = $request->creator;
        $opacs_book->publisher         = $request->publisher;
        $opacs_book->barcode           = $request->barcode;
        $opacs_book->title_read        = $request->title_read;
        $opacs_book->subtitle          = $request->subtitle;
        $opacs_book->series            = $request->series;
        $opacs_book->publication_year  = $request->publication_year;
        $opacs_book->class             = $request->class;
        $opacs_book->size              = $request->size;
        $opacs_book->page_number       = $request->page_number;
        $opacs_book->marc              = $request->marc;
        $opacs_book->type              = $request->type;
        $opacs_book->shelf             = $request->shelf;
        $opacs_book->lend_flag         = $request->lend_flag;
        $opacs_book->accept_flag       = $request->accept_flag;
        $opacs_book->accept_date       = date('Y-m-d', strtotime($request->accept_date));
        $opacs_book->accept_price      = $request->accept_price;
        $opacs_book->storage_life      = date('Y-m-d', strtotime($request->storage_life));
        $opacs_book->remove_flag       = $request->remove_flag;
        $opacs_book->remove_date       = date('Y-m-d', strtotime($request->remove_date));
        $opacs_book->possession        = $request->possession;
        $opacs_book->library           = $request->library;
        $opacs_book->last_lending_date = date('Y-m-d', strtotime($request->last_lending_date));
        $opacs_book->total_lends       = $request->total_lends;

        // データ保存
        $opacs_book->save();

        // 登録後は表示用の初期処理を呼ぶ。
        return $this->index($request, $page_id, $frame_id);
    }

    /**
     *  削除処理
     */
    public function destroy($request, $page_id, $frame_id, $opacs_books_id)
    {
        // 権限チェック
        // 特別処理。role_article（記事修正）でチェック。
        if ($this->can('role_article')) {
            return $this->view_error(403);
        }

        // id がある場合、データを削除
        if ($opacs_books_id) {
            // データを削除する。
            OpacsBooks::destroy($opacs_books_id);
        }
        // 削除後は表示用の初期処理を呼ぶ。
        return $this->index($request, $page_id, $frame_id);
    }

    /**
     *  貸し出しチェック
     */
    private function lentCheck($opacs_books_id)
    {
        // 貸出中でないかのチェック
        $books_lents = OpacsBooksLents::where('opacs_books_id', $opacs_books_id)
                                      ->whereIn('lent_flag', [1, 2])
                                      ->get();
        if (count($books_lents) == 0) {
            return true;
        }
        return false;
    }

    /**
     * 貸出冊数チェック
     */
    public function lentCountCheck($opac_frame)
    {
        // すでに借りている冊数を取得
        $user = Auth::user();
        if (empty($user)) {
            return array(false, '');
        }

        // 冊数を制限しない。
        if ($opac_frame->lent_limit == 0) {
            return array(true, '');
        }

        $lent_count = OpacsBooksLents::where('student_no', $user->userid)->count();

        // 役割設定の情報取得
        $original_roles = Configs::where('category', 'original_role')->orderBy('additional1', 'asc')->get();

        // Opac の設定情報
        $opac_configs = OpacsConfigs::getConfigs($opac_frame->opacs_id, $original_roles);

        // ユーザーの役割設定
        $users_roles_obj = new UsersRoles();
        $users_roles = $users_roles_obj->getUsersRoles($user->id, 'original_role');

        // 貸出冊数
        $lent_limit_count = 0;

        // 冊数を制限する。
        if ($opac_frame->lent_limit == 1) {
            $lent_limit_count = $opac_configs['lent_limit_global'];
        }

        // 役割毎に冊数を設定して貸し出しする。
        if ($opac_frame->lent_limit == 2) {
            if (!array_key_exists('original_role', $users_roles)) {
                return array(false, '貸出権限が設定されていないため、貸し出しできません。');
            }

            // ユーザに設定されている役割をループし、Opac設定の貸し出し許可冊数を取得。一番多い冊数を採用する。
            foreach ($users_roles['original_role'] as $users_role => $users_role_value) {
                if (array_key_exists('lent_limit_'.$users_role, $opac_configs)) {
                    if ($lent_limit_count < $opac_configs['lent_limit_'.$users_role]) {
                        $lent_limit_count = $opac_configs['lent_limit_'.$users_role];
                    }
                }
            }
        }

        if ($lent_count < $lent_limit_count) {
            return array(true, '');
        }
        return array(false, '貸出上限数まで借りているので、貸し出しできません。');
    }

    /**
     *  メール送信
     */
    public static function sendMail($opacs, $subject, $content)
    {
        if (empty($opacs)) {
            return;
        }
        if ($opacs->moderator_mail_send_flag == 0 || empty($opacs->moderator_mail_send_address)) {
            return;
        }

        $moderator_mail_send_addresses = explode(',', $opacs->moderator_mail_send_address);

        // メール送信
        foreach ($moderator_mail_send_addresses as $mail_send_address) {
            Mail::to($mail_send_address)->send(new ConnectMail(['subject' => $subject, 'template' => 'mail.send'], ['content' => $content]));
        }
    }

    /**
     *  貸し出し登録
     */
    public function lent($request, $page_id, $frame_id, $opacs_books_id = null)
    {
        // 認証されているか確認
        if (!Auth::check()) {
            return $this->view_error(403);
        }

        // opacs_books_id がなければ、barcode を使用する。
        if (empty($opacs_books_id)) {
            // 項目のエラーチェック条件設定（バーコード）
            $validator_columns = array(
                'barcode'       => ['required'],
            );
            $validator_attribute = array(
                'barcode'       => 'バーコード',
            );

            // 項目のエラーチェック実施
            $validator = Validator::make($request->all(), $validator_columns);
            $validator->setAttributeNames($validator_attribute);

            // エラーがあった場合は詳細画面に戻る。
            $message = null;
            if ($validator->fails()) {
                return $this->index($request, $page_id, $frame_id, $validator->errors());
            }

            // バーコードから書籍情報を取得する。
            $tmp_opacs_books = OpacsBooks::where('barcode', $request->barcode)->first();
            if (empty($tmp_opacs_books)) {
                $messages = new MessageBag;
                $messages->add('barcode', '入力されたバーコードに対する書籍が見つかりません。');
                return $this->index($request, $page_id, $frame_id, $messages);
            }

            // バーコードから取得した書籍情報の書籍ID
            $opacs_books_id = $tmp_opacs_books->id;
        } else {
            // opacs_books_id から書籍情報を取得する。
            $tmp_opacs_books = OpacsBooks::where('id', $opacs_books_id)->first();

            if (empty($tmp_opacs_books)) {
                $messages = new MessageBag;
                $messages->add('barcode', '指定された書籍が見つかりません。');
                return $this->index($request, $page_id, $frame_id, $messages);
            }
        }

        // 貸出中でないかのチェック
        Log::debug($opacs_books_id);
        if (!$this->lentCheck($opacs_books_id)) {
            $messages = new MessageBag;
            $messages->add('barcode', 'この書籍は貸出中です。');
            return $this->index($request, $page_id, $frame_id, $messages);
        }

        // 禁帯出でないかのチェック
        if ($tmp_opacs_books->lend_flag == '9:禁帯出') {
            $messages = new MessageBag;
            $messages->add('barcode', 'この書籍は「禁帯出」のため、貸し出しはできません。');
            return $this->index($request, $page_id, $frame_id, $messages);
        }

        // Opac設定取得
        $opac_frame = $this->getOpacFrame($frame_id);

        // 役割設定の情報取得
        $original_roles = Configs::where('category', 'original_role')->orderBy('additional1', 'asc')->get();

        // Opac の設定情報
        $opac_configs = OpacsConfigs::getConfigs($opac_frame->opacs_id, $original_roles);

        // ユーザー情報
        $user = Auth::user();

        // ユーザーの役割設定
        $users_roles_obj = new UsersRoles();
        $users_roles = $users_roles_obj->getUsersRoles($user->id, 'original_role');

        // モデレータ以上の場合のエラーチェック
        if ($this->isCan('role_article')) {
            // 貸出期限
            if (empty($request->return_scheduled)) {
                $messages = new MessageBag;
                $messages->add('return_scheduled', '貸し出し期限を指定してください。');
                return $this->index($request, $page_id, $frame_id, $messages);
            }
            $return_scheduled_ts = strtotime($request->return_scheduled);
            if (!checkdate(date('n', $return_scheduled_ts), date('j', $return_scheduled_ts), date('Y', $return_scheduled_ts))) {
                $messages = new MessageBag;
                $messages->add('return_scheduled', '貸し出し期限が正しい日付になっていません。');
                return $this->index($request, $page_id, $frame_id, $messages);
            }
            // ログインID
            if (empty($request->student_no)) {
                $messages = new MessageBag;
                $messages->add('student_no', '借りる人のログインID（学籍番号）を指定してください。');
                return $this->index($request, $page_id, $frame_id, $messages);
            }
            $student = User::where('userid', $request->student_no)->first();
            if (empty($student)) {
                $messages = new MessageBag;
                $messages->add('student_no', '存在しないログインIDです。');
                return $this->index($request, $page_id, $frame_id, $messages);
            }
        }

        // 項目のエラーチェック条件設定（基本）
        //$validator_columns = array(
        //    'student_no'       => ['required'],
        //    'return_scheduled' => ['required'],
        //);
        //$validator_attribute = array(
        //    'student_no'       => '学籍番号',
        //    'return_scheduled' => '返却予定日',
        //);

        // 貸し出し許可日数を設定して貸し出しする。
        //if ($opac_frame->lent_setting == 2) {
        //    $validator_columns['return_scheduled'][] = 'before_or_equal:' . date('Y-m-d', strtotime("+" . $opac_configs['lent_days_global'] ." day"));
        //}

        // 役割毎に貸し出し許可日数を設定して貸し出しする。
        //if ($opac_frame->lent_setting == 3 && array_key_exists('original_role', $users_roles) && is_array($users_roles['original_role'])) {

        //    $lent_days = 0; // 貸出日
        //    // ユーザに設定されている役割をループし、Opac設定の役割毎貸し出し許可日数を取得。一番長い日数を採用する。
        //    foreach($users_roles['original_role'] as $users_role => $users_role_value) {
        //        if (array_key_exists('lent_days_'.$users_role, $opac_configs)) {
        //            if ($lent_days < $opac_configs['lent_days_'.$users_role] ) {
        //                $lent_days = $opac_configs['lent_days_'.$users_role];
        //            }
        //        }
        //    }
        //    $validator_columns['return_scheduled'][] = 'before_or_equal:' . date('Y-m-d', strtotime("+" . $lent_days ." day"));
        //}

        // すでに借りている冊数を取得
//        $lented = OpacsBooksLents
//$user


        // 冊数を制限する。
//        if ($opac_frame->lent_limit == 1) {
//            $validator_columns['return_scheduled'][] = 'before_or_equal:' . date('Y-m-d', strtotime("+" . $opac_configs['lent_days_global'] ." day"));
//        }

        // 項目のエラーチェック実施
        //$validator = Validator::make($request->all(), $validator_columns);
        //$validator->setAttributeNames($validator_attribute);

        // エラーがあった場合は詳細画面に戻る。
        //$message = null;
        //if ($validator->fails()) {
        //    return $this->show($request, $page_id, $frame_id, $opacs_books_id, $message, null, $validator->errors());
        //}

        // 役割設定の情報取得
        $original_roles = Configs::where('category', 'original_role')->orderBy('additional1', 'asc')->get();

        // Opac の設定情報
        $opac_configs = OpacsConfigs::getConfigs($opac_frame->opacs_id, $original_roles);

        // 返却期限、貸出最大冊数を取得のため、ユーザのroleを取得
        $users_roles = UsersRoles::where('users_id', $user->id)
                                 ->where('target', 'original_role')
                                 ->get();

        // 返却期限
        $lent_max_ts = self::getReturnMaxDate($opac_frame, $users_roles, $opac_configs);
        $lent_max_date = date('Y-m-d', $lent_max_ts);

        // 権限で異なる項目の編集
        $student_no = ($this->isCan('role_article')) ? $request->student_no : $user->userid;
        if ($this->isCan('role_article')) {
            $return_scheduled = $request->return_scheduled;
        } else {
            $return_scheduled = $lent_max_date;
        }

        // 書籍貸し出しデータ新規オブジェクト
        $opacs_books_lents                   = new OpacsBooksLents();
        $opacs_books_lents->opacs_books_id   = $opacs_books_id;
        $opacs_books_lents->lent_flag        = 1;
        $opacs_books_lents->student_no       = $student_no;
        $opacs_books_lents->return_scheduled = date('Y-m-d 00:00:00', strtotime($return_scheduled));

        // データ保存
        $opacs_books_lents->save();

        $message = '貸し出し登録しました。';

        // 書籍データ
        $opacs_books = OpacsBooks::where('id', $opacs_books_id)->first();

        // メール送信
        $subject = '図書を貸し出し登録しました。';
        $content = $student_no . " が貸し出し登録しました。\n";
        $content .= 'ISBN：' . $opacs_books->isbn . "\n";
        $content .= 'タイトル：' . $opacs_books->title . "\n";
        $content .= '返却期限日：' . $return_scheduled . "\n";

        $opacs = Opacs::where('id', $opacs_books->opacs_id)->first();
        self::sendMail($opacs, $subject, $content);

        // MyOpac画面へ遷移
        return $this->index($request, $page_id, $frame_id, null, ['貸し出し処理が完了しました。']);

        // 郵送貸し出しリクエスト処理後は詳細表示処理を呼ぶ。(更新成功時もエラー時も同じ)
        //return $this->show($request, $page_id, $frame_id, $opacs_books_id, $message, null, $validator->errors());
    }

    /**
     *  郵送貸し出しリクエスト
     */
    public function requestLent($request, $page_id, $frame_id, $opacs_books_id)
    {
        // 認証されているか確認
        if (!Auth::check()) {
            return $this->view_error(403);
        }

        // 貸出中でないかのチェック
        if (!$this->lentCheck($opacs_books_id)) {
            return $this->show($request, $page_id, $frame_id, $opacs_books_id, 'この書籍は貸出中です。', 'danger');
        }

        // 項目のエラーチェック
        $validator = Validator::make($request->all(), [
            'req_student_no'       => ['required'],
            'req_return_scheduled' => ['required'],
            'req_phone_no'         => ['required'],
            'req_email'            => ['required'],
        ]);
        $validator->setAttributeNames([
            'req_student_no'       => '学籍番号',
            'req_return_scheduled' => '返却予定日',
            'req_phone_no'         => '連絡先電話番号',
            'req_email'            => '連絡先メールアドレス',
        ]);

        // エラーがあった場合は詳細画面に戻る。
        $message = null;
        if ($validator->fails()) {
            return $this->show($request, $page_id, $frame_id, $opacs_books_id, $message, null, $validator->errors());
        }

        // 書籍貸し出しデータ新規オブジェクト
        $opacs_books_lents                   = new OpacsBooksLents();
        $opacs_books_lents->opacs_books_id   = $opacs_books_id;
        $opacs_books_lents->lent_flag        = 2;
        $opacs_books_lents->student_no       = $request->req_student_no;
        $opacs_books_lents->return_scheduled = date('Y-m-d 00:00:00', strtotime($request->req_return_scheduled));
        $opacs_books_lents->phone_no         = $request->req_phone_no;
        $opacs_books_lents->email            = $request->req_email;

        // データ保存
        $opacs_books_lents->save();

        $message = '郵送貸し出しリクエストを受け付けました。';

        // 書籍データ
        $opacs_books = OpacsBooks::where('id', $opacs_books_id)->first();

        // メール送信
        $subject = '郵送貸し出しリクエストを受け付けました。';
        $content = $request->req_student_no . " が郵送貸し出しリクエストしました。\n";
        $content .= 'ISBN：' . $opacs_books->isbn . "\n";
        $content .= 'タイトル：' . $opacs_books->title . "\n";
        $content .= '連絡先電話番号：' . $request->req_phone_no . "\n";
        $content .= '連絡先メールアドレス：' . $request->req_email . "\n";

        $opacs = Opacs::where('id', $opacs_books->opacs_id)->first();
        self::sendMail($opacs, $subject, $content);

        // 郵送貸し出しリクエスト処理後は詳細表示処理を呼ぶ。(更新成功時もエラー時も同じ)
        return $this->show($request, $page_id, $frame_id, $opacs_books_id, $message, null, $validator->errors());
    }

    /**
     *  貸し出し返却
     */
    public function returnLent($request, $page_id, $frame_id, $opacs_books_id = null)
    {
        // 認証されているか確認
        if (!Auth::check()) {
            return $this->view_error(403);
        }

        // opacs_books_id がなければ、barcode を使用する。
        if (empty($opacs_books_id)) {
            // 項目のエラーチェック条件設定（バーコード）
            $validator_columns = array(
                'return_barcode' => ['required'],
            );
            $validator_attribute = array(
                'return_barcode' => 'バーコード',
            );

            // 項目のエラーチェック実施
            $validator = Validator::make($request->all(), $validator_columns);
            $validator->setAttributeNames($validator_attribute);

            // エラーがあった場合は詳細画面に戻る。
            $message = null;
            if ($validator->fails()) {
                return $this->index($request, $page_id, $frame_id, $validator->errors());
            }

            // バーコードから書籍情報を取得する。
            $tmp_opacs_books = OpacsBooks::where('barcode', $request->return_barcode)->first();
            if (empty($tmp_opacs_books)) {
                $messages = new MessageBag;
                $messages->add('return_barcode', '入力されたバーコードに対する書籍が見つかりません。');
                return $this->index($request, $page_id, $frame_id, $messages);
            }

            // バーコードから取得した書籍情報の書籍ID
            $opacs_books_id = $tmp_opacs_books->id;
        }

        // 貸出中でないかのチェック
        if ($this->lentCheck($opacs_books_id)) {
            $messages = new MessageBag;
            $messages->add('barcode', 'この書籍は貸出中ではありません。');
            return $this->index($request, $page_id, $frame_id, $messages);
        }

        // モデレータ以上の場合のエラーチェック
        if ($this->isCan('role_article')) {
            // ログインID
            if (empty($request->return_student_no)) {
                $messages = new MessageBag;
                $messages->add('return_student_no', '借りる人のログインID（学籍番号）を指定してください。');
                return $this->index($request, $page_id, $frame_id, $messages);
            }
            $student = User::where('userid', $request->return_student_no)->first();
            if (empty($student)) {
                $messages = new MessageBag;
                $messages->add('return_student_no', '存在しないログインIDです。');
                return $this->index($request, $page_id, $frame_id, $messages);
            }
        }

        // 項目のエラーチェック
        //$validator = Validator::make($request->all(), [
        //    'return_student_no'  => ['required'],
        //    'return_date'        => ['required'],
        //]);
        //$validator->setAttributeNames([
        //    'return_student_no'  => '学籍番号',
        //    'return_date'        => '返却日',
        //]);

        // エラーがあった場合は詳細画面に戻る。
        //$message = null;
        //if ($validator->fails()) {
        //    return $this->show($request, $page_id, $frame_id, $opacs_books_id, $message, null, $validator->errors());
        //}

        // ユーザー情報
        $user = Auth::user();

        // 権限で異なる項目の編集
        $return_student_no = ($this->isCan('role_article')) ? $request->return_student_no : $user->userid;

        // 貸し出し中書籍
        $books_lents = OpacsBooksLents::where('opacs_books_id', $opacs_books_id)->whereIn('lent_flag', [1, 2])->first();

        // 学籍番号チェック
        // 返却時の学籍番号チェックはなくす。（今後のオプションにする可能性があるので、コメントで残しておく）
        // if ($books_lents->student_no != $return_student_no) {
        //     $messages = new MessageBag;
        //     $messages->add('return_barcode', '貸し出し時の学籍番号と一致しません。');
        //     return $this->index($request, $page_id, $frame_id, $messages);
        // }

        // 書籍貸し出しデータ
        $books_lents->lent_flag   = 9;
        $books_lents->student_no  = null;
        //$books_lents->return_date = date('Y-m-d 00:00:00', strtotime($request->return_date));
        $books_lents->return_date = date('Y-m-d 00:00:00');
        $books_lents->phone_no    = null;
        $books_lents->email       = null;

        // データ保存
        $books_lents->save();

        $message = '返却しました。';

        // 書籍データ
        $opacs_books = OpacsBooks::where('id', $opacs_books_id)->first();

        // メール送信
        $subject = '図書を返却しました。';
        $content = "";
        //$content = $request->return_student_no . " が返却しました。\n";
        $content .= 'ISBN：' . $opacs_books->isbn . "\n";
        $content .= 'タイトル：' . $opacs_books->title . "\n";
        $content .= '返却日：' . $books_lents->return_date . "\n";

        $opacs = Opacs::where('id', $opacs_books->opacs_id)->first();
        self::sendMail($opacs, $subject, $content);

        // MyOpac画面へ遷移
        //return redirect($this->page->permanent_link)->with('flash_message_'.$frame_id, '投稿が完了しました');
        return $this->index($request, $page_id, $frame_id, null, ['返却処理が完了しました。']);

        // 郵送貸し出しリクエスト処理後は詳細表示処理を呼ぶ。(更新成功時もエラー時も同じ)
        //return $this->show($request, $page_id, $frame_id, $opacs_books_id, $message, null, $validator->errors());
    }

    /**
     *  検索
     */
    public function search($request, $page_id, $frame_id)
    {
        // セッション初期化などのLaravel 処理。
        $request->flash();

        // キーワードをセッションに保存しておく。
        $request->session()->put('search_keyword', $request->keyword);

        // 検索はフォームでredirect指定しているので、ここは無効になるけれども、一応置いている。
        return $this->index($request, $page_id, $frame_id);
    }

    /**
     *  Opacフレーム設定表示画面
     */
    public function settingOpacFrame($request, $page_id, $frame_id)
    {
        // セッション初期化などのLaravel 処理。
        $request->flash();

        // Opac設定取得
        $opac_frame = $this->getOpacFrame($frame_id);

        // Opacフレーム設定
        $opac_frame_setting = OpacsFrames::where('frames_id', $frame_id)->first();
        if (empty($opac_frame_setting)) {
            $opac_frame_setting = new OpacsFrames();
        }

        // Opacフレーム設定画面を呼び出す。
        return $this->view(
            'opacs_setting_frame', [
            'opac_frame'         => $opac_frame,
            'opac_frame_setting' => $opac_frame_setting,
            ]
        );
    }

    /**
     *  Opacフレーム設定保存処理
     */
    public function saveOpacFrame($request, $page_id, $frame_id)
    {
        // Opac設定取得
        $opac_frame = $this->getOpacFrame($frame_id);

        // プラグインのフレームやOpacのID が設定されていない場合は空振りさせる。
        if (empty($opac_frame) || empty($opac_frame->opacs_id)) {
            return;
        }

        OpacsFrames::updateOrCreate(
            ['frames_id' => $frame_id],
            ['opacs_id' => $opac_frame->opacs_id, 'frames_id' => $frame_id, 'view_form' => $request->view_form ],
        );

        return;
    }

    /**
     *  貸し出し中一覧
     */
    public function rentlist($request, $page_id, $frame_id)
    {
        // 権限チェック
        if ($this->can('role_article')) {
            return $this->view_error(403);
        }

        // 貸し出し中一覧取得
        $books_lents = OpacsBooksLents::select('opacs_books_lents.*', 'users.name', 'opacs_books.title')
                                      ->leftJoin('opacs_books', 'opacs_books.id', '=', 'opacs_books_lents.opacs_books_id')
                                      ->leftJoin('users', 'users.userid', '=', 'opacs_books_lents.student_no')
                                      ->whereNull('opacs_books_lents.return_date')
                                      ->orderBy('opacs_books_lents.return_scheduled', 'asc')
                                      ->get();

        // Opacフレーム設定画面を呼び出す。
        return $this->view(
            'opacs_rentlist', [
            'books_lents' => $books_lents,
            ]
        );
    }
}
