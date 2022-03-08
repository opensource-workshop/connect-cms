<?php

namespace App\Plugins\User\Opacs;

use SimpleXMLElement;

use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\MessageBag;
use Illuminate\Support\Collection;

use App\User;
use App\Models\Common\Buckets;
use App\Models\Common\Frame;
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
 * @package Controller
 * @plugin_title Opac
 * @plugin_desc 図書館などの蔵書を管理できるプラグインです。
 */
class OpacsPlugin extends UserPluginBase
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
        $functions['get']  = ['settingOpacFrame', 'lentlist', 'searchClear', 'searchDetailClear', 'roleLent'];
        $functions['post'] = ['lent', 'requestLent', 'returnLent', 'search', 'saveOpacFrame', 'getBookInfo', 'destroyRequest'];
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
        $role_check_table["settingOpacFrame"]  = ['frames.edit'];
        $role_check_table["saveOpacFrame"]     = ['frames.create'];

        // 貸出・返却系はログインのみ必要でメソッド側でチェック済み。ここで設定しない。
        // $role_check_table["lent"]              = ['posts.create'];
        // $role_check_table["requestLent"]       = ['posts.create'];
        // $role_check_table["returnLent"]        = ['posts.update'];
        // $role_check_table["destroyRequest"]    = ['posts.delete'];

        $role_check_table["lentlist"]          = ['role_article'];
        $role_check_table["roleLent"]          = ['role_article'];

        // change: getBookInfoの呼び出しは create,edit のみ. create,edit の権限を設定する
        // $role_check_table["getBookInfo"]       = ['role_article'];
        $role_check_table["getBookInfo"]       = ['role_article_admin'];

        // bugfix: 標準権限チェックの posts系権限 が role_article では足らないため、実質操作できる role_article_admin を指定
        // $role_check_table["create"]            = ['role_article'];
        // $role_check_table["edit"]              = ['role_article'];
        // $role_check_table["save"]              = ['role_article'];
        // $role_check_table["destroy"]           = ['role_article'];
        $role_check_table["create"]            = ['role_article_admin'];
        $role_check_table["edit"]              = ['role_article_admin'];
        $role_check_table["save"]              = ['role_article_admin'];
        $role_check_table["destroy"]           = ['role_article_admin'];

        return $role_check_table;
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

    /**
     * POST取得関数（コアから呼び出す）
     * コアがPOSTチェックの際に呼び出す関数
     */
    public function getPost($id, $action = null)
    {
        // データ存在チェックのために getPost を利用

        if (is_null($action)) {
            // プラグイン内からの呼び出しを想定。処理を通す。
        } elseif (in_array($action, ['edit', 'save', 'destroy'])) {
            // コアから呼び出し。posts.update|posts.deleteの権限チェックを指定したアクションは、処理を通す。
        } else {
            // それ以外のアクションは null で返す。
            return null;
        }

        // 一度読んでいれば、そのPOSTを再利用する。
        if (!empty($this->post)) {
            return $this->post;
        }

        // POST を取得する。（statusカラムなしのため、appendAuthWhereBase 使わない）
        $this->post = OpacsBooks::firstOrNew(['id' => $id]);
        return $this->post;
    }

    /* private 関数 */

    /**
     *  紐づくOPAC ID とフレームデータの取得
     */
    private function getOpacFrame($frame_id)
    {
        // Frame データ
        $frame = Frame::select('frames.*', 'opacs.id as opacs_id', 'opacs.opac_name', 'opacs.view_count', 'lent_setting', 'lent_limit')
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
        $request_url = 'https://iss.ndl.go.jp/api/opensearch?isbn=' . $request->isbn;

        // NDL OpenSearch 呼び出しと結果のXML 取得
        $xml = null;
        try {
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

        // 結果が取得できた場合
        //var_dump($xml);

        // ISBN設定
        $opacs_books->isbn = $request->isbn;

        $totalResults = $xml->channel->children('openSearch', true)->totalResults;
        if ($totalResults == 0) {
            return array($opacs_books, "書誌データが見つかりませんでした。");
        }
        if (!$xml) {
            return array($opacs_books, "取得した書誌データでエラーが発生しました。");
        } else {
            $channel = get_object_vars($xml->channel);
            if (is_array($channel["item"])) {
                // itemが複数ある場合

                // タイトル：どのitemにも必ずあるのでチェックせず、１つ目からそのまま取得
                $opacs_books->title = $channel["item"][0]->title;

                // 著者：どのitemにも必ずあるのでチェックせず、１つ目からそのまま取得
                $opacs_books->creator = $channel["item"][0]->author;

                // 出版社：どのitemにも必ずあるのでチェックせず、１つ目からそのまま取得
                $opacs_books->publisher = $channel["item"][0]->children('dc', true)->publisher;

                // 請求番号：請求番号は分類コードをもとに作成する為、分類コードを入れる
                $opacs_books->ndc = $this->getBookDetailByList($channel["item"], 'dc:subject [@xsi:type="dcndl:NDC10" or @xsi:type="dcndl:NDC9" or @xsi:type="dcndl:NDC8" or @xsi:type="dcndl:NDC7" or @xsi:type="dcndl:NDC6"]');

                // タイトルヨミ：どのitemにも必ずあるのでチェックせず、１つ目からそのまま取得
                $opacs_books->title_read = $channel["item"][0]->children('dcndl', true)->titleTranscription;

                // シリーズ
                $opacs_books->series = $this->getBookDetailByList($channel["item"], 'dcndl:seriesTitle');

                // 出版年
                $opacs_books->publication_year = $this->getBookDetailByList($channel["item"], 'dcterms:issued [@xsi:type="dcterms:W3CDTF"]');

                // 分類：請求番号をそのまま入れる
                $opacs_books->class = $opacs_books->ndc;

                // 頁数/大きさ：頁数と大きさは一緒に登録されている事がある。情報入力した方の情報の入れ方如何でどうとでも入力できてしまう為、確実に間違いなく取得できる方法がない。
                // なので仮に、「dc:extent」が２つある時は、１つ目を頁数、２つ目を大きさと仮定して値を設定する
                // １しかない時はページ数の方にのみ設定する
                // ３つ以上ある時は最初の１つ目を頁数、２つ目を大きさとして設定して他を無視する
                foreach ($channel["item"] as $item) {
                    $get_element = $item->xpath('dc:extent');
                    $get_count = count($get_element);
                    if ($get_count == 1) {
                        $opacs_books->page_number = trim($get_element[0]->__toString());
                        break;
                    } elseif ($get_count > 1) {
                        $opacs_books->page_number = trim($get_element[0]->__toString());
                        $opacs_books->size = trim($get_element[1]->__toString());
                        break;
                    }
                }

                // MARC NO
                $opacs_books->marc = $this->getBookDetailByList($channel["item"], 'dc:identifier [@xsi:type="dcndl:NIIBibID"]');

                // 金額
                $get_value = $this->getBookDetailByList($channel["item"], 'dcndl:price');
                $opacs_books->accept_price = str_replace('円', '', $get_value);


            } else {
                // itemが１つだけの場合

                // タイトル
                $opacs_books->title = $channel["item"]->title;
                // 著者
                $opacs_books->creator = $channel["item"]->author;
                // 出版社
                $opacs_books->publisher = $channel["item"]->children('dc', true)->publisher;

                // 請求番号：請求番号は分類コードをもとに作成する為、分類コードを入れる
                $opacs_books->ndc = $this->getBookDetail($channel["item"], 'dc:subject [@xsi:type="dcndl:NDC10" or @xsi:type="dcndl:NDC9" or @xsi:type="dcndl:NDC8" or @xsi:type="dcndl:NDC7" or @xsi:type="dcndl:NDC6"]');

                // タイトルヨミ
                $opacs_books->title_read = $channel["item"]->children('dcndl', true)->titleTranscription;

                // シリーズ
                $opacs_books->series = $this->getBookDetail($channel["item"], 'dcndl:seriesTitle');

                // 出版年
                $opacs_books->publication_year = $this->getBookDetail($channel["item"], 'dcterms:issued [@xsi:type="dcterms:W3CDTF"]');

                // 分類：請求番号をそのまま入れる
                $opacs_books->class = $opacs_books->ndc;

                // 頁数/大きさ：頁数と大きさは一緒に登録されている事がある。情報入力した方の情報の入れ方如何でどうとでも入力できてしまう為、確実に間違いなく取得できる方法がない。
                // なので仮に、「dc:extent」が２つある時は、１つ目を頁数、２つ目を大きさと仮定して値を設定する
                // １しかない時はページ数の方にのみ設定する
                // ３つ以上ある時は最初の１つ目を頁数、２つ目を大きさとして設定して他を無視する
                $get_element = $channel["item"]->xpath('dc:extent');
                $get_count = count($get_element);
                if ($get_count == 1) {
                    $opacs_books->page_number = trim($get_element[0]->__toString());
                } elseif ($get_count > 1) {
                    $opacs_books->page_number = trim($get_element[0]->__toString());
                    $opacs_books->size = trim($get_element[1]->__toString());
                }

                // MARC NO
                $opacs_books->marc = $this->getBookDetail($channel["item"], 'dc:identifier [@xsi:type="dcndl:NIIBibID"]');

                // 金額
                $get_value = $this->getBookDetail($channel["item"], 'dcndl:price');
                $opacs_books->accept_price = str_replace('円', '', $get_value);

            }

        }

        return array($opacs_books, "");
    }

    /**
     *  書籍詳細情報取得関数
     */
    private function getBookDetail($channel_item, $xpath_string)
    {
        // itemの中から、xpathで指定された値を取得する。
        // 取得した値をstring値で返却。ない場合は空の文字列を返却
        $get_element = $channel_item->xpath($xpath_string);
        if (count($get_element) > 0) {
            return trim($get_element[0]->__toString());
        }

        return "";
    }

    /**
     *  書籍詳細情報取得関数
     */
    private function getBookDetailByList($channel_items, $xpath_string)
    {
        // channelの中にある複数のitemリストの中から、xpathで指定された値を取得する。
        // 取得した値をstring値で返却。ない場合は空の文字列を返却

        foreach ($channel_items as $item) {
            $get_value = $this->getBookDetail($item, $xpath_string);
            if ($get_value != "") {
                return $get_value;
            }
        }

        return "";
    }

    /* 画面アクション関数 */

    /**
     * lent_flag        = 9:貸出終了(貸し出し可能)、1:貸し出し中、2:貸し出しリクエスト受付中
     * scheduled_return = 返却予定日(日付)
     * lent_at          = 貸し出し日時(日時)
     */

    /**
     * 書誌データ取得関数
     *
     * @return view
     * @method_title 書籍一覧
     * @method_desc 書籍を検索し、一覧表示できます。
     * @method_detail
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
        // bugfix: フレームはあっても、既に削除したOpac（$opac_frame->bucket_id = null）は表示しない
        // if (empty($opac_frame)) {
        if (empty($opac_frame) || empty($opac_frame->bucket_id)) {
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
        // bugfix: フレームはあっても、既に削除したOpac（$opac_frame->bucket_id = null）は表示しない
        // if (empty($opac_frame)) {
        if (empty($opac_frame) || empty($opac_frame->bucket_id)) {
            return;
        }

        // Page データ
        // $page = Page::where('id', $page_id)->first();

        // 検索タイプ
        $opac_search_type = $request->session()->get('opac_search_type.'.$frame_id);

        // 検索キーワード
        $keyword = $request->session()->get('search_keyword.'.$frame_id);

        // 詳細検索条件
        $opac_search_condition = $request->session()->get('opac_search_condition.'.$frame_id);

        // 並び順
        $sort_type = $request->session()->get('opac_search_sort_type.'.$frame_id);
        if (empty($sort_type) === true) {
            $sort_type = 1;   // タイトル：昇順
        }
        $sort_query = null;
        switch ($sort_type) {
            case 1:  // タイトル：昇順
                $sort_query = array('title','asc');
                break;
            case 2:  // タイトル：降順
                $sort_query = array('title','desc');
                break;
            case 3:  // 著者：昇順
                $sort_query = array('creator','asc');
                break;
            case 4:  // 著者：降順
                $sort_query = array('creator','desc');
                break;
            case 5:  // 出版者：昇順
                $sort_query = array('publisher','asc');
                break;
            case 6:  // 出版者：降順
                $sort_query = array('publisher','desc');
                break;
            default: // その他の時はタイトル：昇順
                $sort_query = array('title','asc');
                break;
        }

        // 表示件数：セッションに保存されている表示件数を優先。もしない場合はフレームの表示件数を取得
        $opac_search_view_count = $request->session()->get('opac_search_view_count.'.$frame_id);
        if (empty($opac_search_view_count) === true) {
            $opac_search_view_count = $opac_frame->view_count;
        }

        // データ取得（1ページの表示件数指定）
        if (empty($opac_frame->opacs_id)) {
            $opacs_books = null;
        } elseif ($opac_search_type != 2 && empty($keyword) == true) {
            $opacs_books = null;
        } elseif ($opac_search_type != 2) {
            // キーワード検索
            $opacs_books = DB::table('opacs_books')
                          ->select('opacs_books.*', 'opacs_books_lents.lent_flag', 'opacs_books_lents.student_no', 'opacs_books_lents.return_scheduled', 'opacs_books_lents.lent_at')
                          ->leftJoin('opacs_books_lents', function ($join) {
                              $join->on('opacs_books_lents.opacs_books_id', '=', 'opacs_books.id')
                                  ->where('opacs_books_lents.lent_flag', [1]);
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
                          ->orderBy($sort_query[0], $sort_query[1])
                          ->paginate($opac_search_view_count, ["*"], "frame_{$opac_frame->id}_page");
            if (count($opacs_books) <= 0) {
                $opacs_books = null;
                session()->flash('search_opacs', '検索条件に該当する書籍情報がありませんでした。');
            }
        } elseif (empty($opac_search_condition) == true || count($opac_search_condition) <= 0) {
            $opacs_books = null;
        } else {
            // 詳細検索

            // 検索条件設定
            $where_querys = [];
            if (isset($opac_search_condition['title']) == true && empty($opac_search_condition['title']) == false) {
                $where_querys[] = array( 'title', 'like', '%' . $opac_search_condition['title'] . '%' );
            }
            if (isset($opac_search_condition['isbn']) == true && empty($opac_search_condition['isbn']) == false) {
                $where_querys[] = array( 'isbn', 'like', '%' . $opac_search_condition['isbn'] . '%' );
            }
            if (isset($opac_search_condition['creator']) == true && empty($opac_search_condition['creator']) == false) {
                $where_querys[] = array( 'creator', 'like', '%' . $opac_search_condition['creator'] . '%' );
            }
            if (isset($opac_search_condition['ndc']) == true && empty($opac_search_condition['ndc']) == false) {
                $where_querys[] = array( 'ndc', 'like', '%' . $opac_search_condition['ndc'] . '%' );
            }
            if (isset($opac_search_condition['publisher']) == true && empty($opac_search_condition['publisher']) == false) {
                $where_querys[] = array( 'publisher', 'like', '%' . $opac_search_condition['publisher'] . '%' );
            }
            if (isset($opac_search_condition['publication_year']) == true && empty($opac_search_condition['publication_year']) == false) {
                $where_querys[] = array( 'publication_year', 'like', '%' . $opac_search_condition['publication_year'] . '%' );
            }

            // 検索条件が何もない時は何もしない
            if (count($where_querys) <= 0) {
                $opacs_books = null;
            } else {
                $opacs_books = DB::table('opacs_books')
                              ->select('opacs_books.*', 'opacs_books_lents.lent_flag', 'opacs_books_lents.student_no', 'opacs_books_lents.return_scheduled', 'opacs_books_lents.lent_at')
                              ->leftJoin('opacs_books_lents', function ($join) {
                                  $join->on('opacs_books_lents.opacs_books_id', '=', 'opacs_books.id')
//                                      ->wherein('opacs_books_lents.lent_flag', [1, 2]);
                                      ->where('opacs_books_lents.lent_flag', [1]);
                              })
                              ->where('opacs_id', $opac_frame->opacs_id)
                              ->where(function ($query) use ($where_querys) {
                                foreach ($where_querys as $value) {
                                    $query->Where($value[0], $value[1], $value[2]);
                                }
                              })
                              ->orderBy($sort_query[0], $sort_query[1])
                              ->paginate($opac_search_view_count, ["*"], "frame_{$opac_frame->id}_page");

                if (count($opacs_books) <= 0) {
                    $opacs_books = null;
                    session()->flash('search_opacs', '検索条件に該当する書籍情報がありませんでした。');
                }
            }
        }

        // 表示テンプレートを呼び出す。
        return $this->view('opacs', [
            'opac_frame'             => $opac_frame,
            'opacs_books'            => $opacs_books,
            'sort_type'              => $sort_type,
            'opac_search_view_count' => $opac_search_view_count,
            'opac_search_type'       => $opac_search_type,
        ]);
    }

    /**
     * データ選択表示関数
     *
     * @method_title 選択
     * @method_desc このフレームに表示するOPACを選択します。
     * @method_detail
     */
    public function listBuckets($request, $page_id, $frame_id, $id = null)
    {
        // Frame データ
        $opac_frame = Frame::select('frames.*', 'opacs.id as opacs_id', 'opacs.view_count')
            ->leftJoin('opacs', 'opacs.bucket_id', '=', 'frames.bucket_id')
            ->where('frames.id', $frame_id)->first();

        // データ取得（1ページの表示件数指定）
        $opacs = Opacs::orderBy('created_at', 'desc')
            ->paginate(10, ["*"], "frame_{$frame_id}_page");

        // 表示テンプレートを呼び出す。
        return $this->view('opacs_list_buckets', [
            'opac_frame' => $opac_frame,
            'opacs'      => $opacs,
        ]);
    }

    /**
     * OPAC新規作成画面
     *
     * @method_title 作成
     * @method_desc OPACを新しく作成します。
     * @method_detail OPAC名や表示件数を入力してOPACを作成できます。
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
        $opacs->request_mail_send_flag      = $request->request_mail_send_flag ?? 0;
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

            // change: backets は $frame->bucket_id で消さない。選択したOpacsのbucket_idで消す
            $opacs = Opacs::find($opacs_id);
            // // バケツIDの取得のためにFrame を取得(Frame を更新する前に取得しておく)
            // $frame = Frame::where('id', $frame_id)->first();

            // FrameのバケツIDの更新
            // Frame::where('id', $frame_id)->update(['bucket_id' => null]);
            Frame::where('bucket_id', $opacs->bucket_id)->update(['bucket_id' => null]);

            // backetsの削除
            // Buckets::where('id', $frame->bucket_id)->delete();
            Buckets::destroy($opacs->bucket_id);

            // OPAC設定を削除する。
            Opacs::destroy($opacs_id);
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

        // メッセージ表示
        session()->flash('change_buckets_opacs', '設定しました。');

        // 表示ブログ選択画面を呼ぶ
        return $this->listBuckets($request, $page_id, $frame_id, $id);
    }

    /**
     * 新規書誌データ画面
     *
     * @method_title 書籍登録
     * @method_desc 書籍を登録します。
     * @method_detail ISBNで検索して登録することもできます。
     */
    public function create($request, $page_id, $frame_id, $opacs_books_id = null)
    {
        // セッション初期化などのLaravel 処理。
//        $request->flash();

        // OPAC＆フレームデータ
        $opac_frame = $this->getOpacFrame($frame_id);

        // 空のデータ(画面で初期値設定で使用するため)
        $opacs_books = new OpacsBooks();

        // 表示テンプレートを呼び出す。(blade でold を使用するため、withInput 使用)
        return $this->view(
            'opacs_input', [
            'opac_frame'  => $opac_frame,
            'opacs_books' => $opacs_books,
            'book_search' => $request->book_search,
            ]
        )->withInput($request->all);
    }

    /**
     * 書誌データ編集画面
     */
    public function edit($request, $page_id, $frame_id, $opacs_books_id = null)
    {
        // セッション初期化などのLaravel 処理。
//        $request->flash();

        // Frame データ
        $opac_frame = $this->getOpacFrame($frame_id);

        // 書誌データ取得
        $opacs_book = OpacsBooks::where('id', $opacs_books_id)->first();

        // 変更画面を呼び出す。(blade でold を使用するため、withInput 使用)
        return $this->view(
            'opacs_input', [
            'opac_frame'  => $opac_frame,
            'opacs_books' => $opacs_book,
            ]
        )->withInput($request->all);
    }

    /**
     * 書誌データ詳細画面
     *
     * @method_title 書籍詳細
     * @method_desc 書籍の詳細情報を表示できます。
     * @method_detail
     */
    public function show($request, $page_id, $frame_id, $opacs_books_id, $message = null, $message_class = null, $errors = null)
    {
        // セッション初期化などのLaravel 処理。
        $request->flash();

        // Frame データ
        $opac_frame = $this->getOpacFrame($frame_id);

        // 書籍情報取得
        $opacs_book = OpacsBooks::where('opacs_books.id', $opacs_books_id)->first();
        if (empty($opacs_book)) {
            return;
        }

        // 書籍貸出情報取得
        $opacs_book_lents = OpacsBooksLents::where('opacs_books_id', $opacs_books_id)
                                           ->where('lent_flag', [1])->first();

        // 書籍郵送リクエスト情報件数取得
        $opacs_book_lents_count = OpacsBooksLents::where('opacs_books_id', $opacs_books_id)
                                                 ->where('lent_flag', [2])->count();

        // ログインチェック
        $user = Auth::user();

        $lent_max_date = "";
        $lent_limit_check = true;
        $lent_max_date = "";
        $lent_error_message = "";
        $done_lent = 0;
        $done_requests = 0;

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

            // 借り済みかどうかチェック
            $done_lent = OpacsBooksLents::where('opacs_books_id', $opacs_books_id)
                                        ->where('student_no', $user->userid)
                                        ->where('lent_flag', [1])->count();

            // 貸出リクエスト済みかチェック
            $done_requests = $this->lentRequestCheck($opacs_books_id, $user->userid);
        }

        // 変更画面を呼び出す。
        return $this->view(
            'opacs_show', [
            'opac_frame'             => $opac_frame,
            'opacs_books'            => $opacs_book,
            'opacs_book_lents'       => $opacs_book_lents,
            'opacs_book_lents_count' => $opacs_book_lents_count,
            'opacs_books_id'         => $opacs_books_id,
            'lent_limit_check'       => $lent_limit_check,
            'lent_max_date'          => $lent_max_date,
            'done_lent'              => $done_lent,
            'done_requests'          => $done_requests,
            'message'                => $message,
            'message_class'          => $message_class,
            'lent_error_message'     => $lent_error_message,
            'errors'                 => $errors,
            ]
        );
    }

    /**
     * 書籍貸出画面表示（権限あり）
     */
    public function roleLent($request, $page_id, $frame_id, $opacs_books_id, $message = null, $message_class = null, $errors = null)
    {
        // セッション初期化などのLaravel 処理。
        $request->flash();

        // Frame データ
        $opac_frame = $this->getOpacFrame($frame_id);

        // 書誌データ取得
        $opacs_book = OpacsBooks::where('id', $opacs_books_id)->first();

        // 貸し出し済みかどうかチェックする
        $done_lent = $this->lentCheck($opacs_books_id);

        // 既に郵送リクエストされているようだったらその情報を取得する
        $opacs_books_lent = OpacsBooksLents::where('id', $request->req_lent_id)->first();

        // ユーザー名取得
        $user = User::where('userid', $opacs_books_lent->student_no)->first();

        // 画面を呼び出す。
        return $this->view(
            'opacs_role_lent', [
            'opac_frame'             => $opac_frame,
            'opacs_books'            => $opacs_book,
            'opacs_books_lents'      => $opacs_books_lent,
            'opacs_books_id'         => $opacs_books_id,
            'user_name'              => $user->name,
            'done_lent'              => $done_lent,
            'message'                => $message,
            'message_class'          => $message_class,
            'errors'                 => $errors,
            ]
        );
    }

    /**
     *  書誌データ登録処理
     */
    public function save($request, $page_id, $frame_id, $opacs_books_id = null)
    {
        // 項目のエラーチェック
        $validator = Validator::make($request->all(), [
            'title'        => ['required'],
            'isbn'         => ['alpha_num'],
            'barcode'      => ['required','alpha_num','size:9'],
            'type'         => ['nullable', \Illuminate\Validation\Rule::in([1, 3, 4])],
            'shelf'        => ['nullable', \Illuminate\Validation\Rule::in([1, 2, 3, 4])],
            'lend_flag'    => ['nullable', \Illuminate\Validation\Rule::in([0, 1, 2, 9])],
            'accept_flag'  => ['nullable', \Illuminate\Validation\Rule::in([0, 1])],
            'accept_price' => ['nullable', 'integer'],
            'remove_flag'  => ['nullable', \Illuminate\Validation\Rule::in([1, 3, 4, 5])],
            'possession'   => ['nullable', \Illuminate\Validation\Rule::in([1, 2, 3, 4, 5])],
            'library'      => ['nullable', \Illuminate\Validation\Rule::in([1, 2])],
        ]);
        $validator->setAttributeNames([
            'title'        => 'タイトル',
            'isbn'         => 'ISBN',
            'barcode'      => 'バーコード',
            'type'         => '資料区分',
            'shelf'        => '配架区分',
            'lend_flag'    => '貸出区分',
            'accept_flag'  => '受入区分',
            'accept_price' => '受入金額',
            'remove_flag'  => '除籍区分',
            'possession'   => '状態',
            'library'      => '所在館',
        ]);

        // エラーがあった場合は入力画面に戻る。
        if ($validator->fails()) {
            return back()->withErrors($validator)->withInput();
        }

        // バーコード重複チェック
        $tmp_opacs_books = OpacsBooks::where('barcode', $request->barcode)->first();
        if (empty($tmp_opacs_books) == false && $tmp_opacs_books->id != $opacs_books_id) {
            $messages = new MessageBag;
            $messages->add('barcode', '入力されたバーコードは既に存在します。');
            return back()->withErrors($messages)->withInput();
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
        $opacs_book->accept_date       = empty($request->accept_date) ? null : date('Y-m-d', strtotime($request->accept_date));
        $opacs_book->accept_price      = $request->accept_price;
        $opacs_book->storage_life      = empty($request->storage_life) ? null : date('Y-m-d', strtotime($request->storage_life));
        $opacs_book->remove_flag       = $request->remove_flag;
        $opacs_book->remove_date       = empty($request->remove_date) ? null : date('Y-m-d', strtotime($request->remove_date));
        $opacs_book->possession        = $request->possession;
        $opacs_book->library           = $request->library;
        $opacs_book->last_lending_date = empty($request->last_lending_date) ? null : date('Y-m-d', strtotime($request->last_lending_date));
        $opacs_book->total_lends       = $request->total_lends;

        // データ保存
        $opacs_book->save();

        // メッセージ表示
        session()->flash('save_opacs', '登録しました。');

        // 登録後はリダイレクトして編集画面を開く。(form のリダイレクト指定では post した id が渡せないため)
        return new Collection(['redirect_path' => url('/') . "/plugin/opacs/edit/" . $page_id . "/" . $frame_id . "/" . $opacs_book->id . "#frame-" . $frame_id]);
    }

    /**
     *  削除処理
     */
    public function destroy($request, $page_id, $frame_id, $opacs_books_id)
    {
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
     *  TRUE：未貸し出し / FALSE：貸し出し済み
     */
    private function lentCheck($opacs_books_id)
    {
        // 貸出中でないかのチェック
        $books_lents = OpacsBooksLents::where('opacs_books_id', $opacs_books_id)
                                      ->whereIn('lent_flag', [1])
                                      ->get();
        if (count($books_lents) == 0) {
            return true;
        }
        return false;
    }

    /**
     *  郵送リクエスト済みかどうかチェック
     *  TRUE：郵送リクエスト済み / FALSE：未リクエスト
     */
    private function lentRequestCheck($opacs_books_id, $student_no)
    {
        // 当該ユーザーが既に郵送リクエストを出してないかチェックする
        $books_lents = OpacsBooksLents::where('opacs_books_id', $opacs_books_id)
                                      ->whereIn('lent_flag', [2])
                                      ->where('student_no', $student_no)
                                      ->get();
        if (count($books_lents) == 0) {
            return false;
        }
        return true;
    }

    /**
     * 貸出冊数チェック
     */
    private function lentCountCheck($opac_frame)
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
    public static function sendMailOpac($opacs, $subject, $content)
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
     *  リクエストしたユーザーへのメール送信
     */
    public static function sendMailOpacUser($opacs, $subject, $content, $user_email = null)
    {
        if (empty($opacs)) {
            return;
        }

        if ($opacs->request_mail_send_flag != 1 || empty($user_email)) {
            return;
        }

        Mail::to($user_email)->send(new ConnectMail(['subject' => $subject, 'template' => 'mail.send'], ['content' => $content]));
    }

    /**
     *  貸し出し登録
     *  ※現在はモデレータのみがモデレータの一覧から貸し出す事しか想定していない。
     *    その為、戻る画面はモデレータ用一覧かモデレータ用貸出画面になっている
     */
    public function lent($request, $page_id, $frame_id, $opacs_books_id = null)
    {
        // 認証されているか確認
        if (!Auth::check()) {
            return $this->viewError(403);
        }

        // 項目のエラーチェック
        $validator = Validator::make($request->all(), [
            'req_student_no'   => ['required'],
            'barcode'          => ['required'],
            'return_scheduled' => ['required'],
        ]);
        $validator->setAttributeNames([
            'req_student_no'   => '学籍番号/教職員番号',
            'barcode'          => '確認用バーコード',
            'return_scheduled' => '返却予定日',
        ]);
        $message = null;
        if ($validator->fails()) {
            return $this->roleLent($request, $page_id, $frame_id, $opacs_books_id, $message, null, $validator->errors());
        }

        // lent_idがある場合、郵送リクエストからの貸出処理なのでデータ整合性チェックを行う
        $opacs_books_lent = null;
        if (empty($request->req_lent_id) == false) {
            // 郵送リクエスト情報取得
            $opacs_books_lent = OpacsBooksLents::where('id', $request->req_lent_id)
                                               ->where('opacs_books_id', $opacs_books_id)
                                               ->where('student_no', $request->req_student_no)
                                               ->where('lent_flag', [2])->first();
            if (empty($opacs_books_lent) == true) {
                $messages = new MessageBag;
                $messages->add('barcode', '該当する郵送リクエスト情報がありませんでした。');
                return $this->roleLent($request, $page_id, $frame_id, $opacs_books_id, null, null, $messages);
            }
        } else {
            $opacs_books_lent = OpacsBooksLents::where('opacs_books_id', $opacs_books_id)
                                               ->where('student_no', $request->req_student_no)
                                               ->where('lent_flag', [2])->first();
        }

        // 書籍情報取得
        $opacs_books = OpacsBooks::where('id', $opacs_books_id)->first();
        if ($opacs_books->barcode != $request->barcode) {
            $messages = new MessageBag;
            $messages->add('barcode', '確認用バーコードと一致しませんでした。');
            return $this->roleLent($request, $page_id, $frame_id, $opacs_books_id, null, null, $messages);
        }

        // 貸出中でないかのチェック
        if (!$this->lentCheck($opacs_books_id)) {
            $messages = new MessageBag;
            $messages->add('barcode', 'この書籍は貸出中です。');
            return $this->roleLent($request, $page_id, $frame_id, $opacs_books_id, null, null, $messages);
        }

        // 禁帯出でないかのチェック
        if ($opacs_books->lend_flag == '9:禁帯出') {
            $messages = new MessageBag;
            $messages->add('barcode', 'この書籍は「禁帯出」のため、貸し出しはできません。');
            return $this->roleLent($request, $page_id, $frame_id, $opacs_books_id, null, null, $messages);
        }

        // Opac設定取得
        $opac_frame = $this->getOpacFrame($frame_id);

        // 役割設定の情報取得
        $original_roles = Configs::where('category', 'original_role')->orderBy('additional1', 'asc')->get();

        // Opac の設定情報
        $opac_configs = OpacsConfigs::getConfigs($opac_frame->opacs_id, $original_roles);

        // 貸出期限
        $return_scheduled_ts = strtotime($request->return_scheduled);
        if (!checkdate(date('n', $return_scheduled_ts), date('j', $return_scheduled_ts), date('Y', $return_scheduled_ts))) {
            $messages = new MessageBag;
            $messages->add('return_scheduled', '貸し出し期限が正しい日付になっていません。');
            return $this->roleLent($request, $page_id, $frame_id, $opacs_books_id, null, null, $messages);
        }
        // ログインID
        $student = User::where('userid', $request->req_student_no)->first();
        if (empty($student)) {
            session()->flash('lent_error', '存在しないログインIDです。');
            return $this->roleLent($request, $page_id, $frame_id, $opacs_books_id);
        }

        // 役割設定の情報取得
        $original_roles = Configs::where('category', 'original_role')->orderBy('additional1', 'asc')->get();

        // Opac の設定情報
        $opac_configs = OpacsConfigs::getConfigs($opac_frame->opacs_id, $original_roles);

        // 返却期限、貸出最大冊数を取得のため、ユーザのroleを取得
        $users_roles = UsersRoles::where('users_id', $student->id)
                                 ->where('target', 'original_role')
                                 ->get();

        // 最大貸出数をオーバーしていないかどうかチェックする
        $mex_lent_count = $this->getReturnMaxLentCount($opac_frame, $users_roles, $opac_configs);
        $lent_infos = OpacsBooksLents::where('student_no', $request->req_student_no)->get();
        $lent_count = count($lent_infos);
        if ($lent_count > 0) {
            if (empty($opacs_books_lent) == false) {
                // lent_idがある場合は、当該ユーザーの貸出情報件数から-1する（一致する貸出情報があれば）
                foreach ($lent_infos as $lent_info) {
                    if ($lent_info->id == $opacs_books_lent->id) {
                        $lent_count = $lent_count -1;
                        break;
                    }
                }
            }
        }
        if ($mex_lent_count <= $lent_count) {
            session()->flash('lent_error', '対象ユーザーの最大貸出数をオーバーしています。');
            return $this->roleLent($request, $page_id, $frame_id, $opacs_books_id);
        }

        if (empty($opacs_books_lent)) {
            // 郵送リクエストがない場合は新規登録
            $opacs_books_lent = new OpacsBooksLents();
            $opacs_books_lent->opacs_books_id   = $opacs_books_id;
            $opacs_books_lent->lent_flag        = 1;
            $opacs_books_lent->student_no       = $request->req_student_no;
            $opacs_books_lent->return_scheduled = date('Y-m-d 00:00:00', strtotime($request->return_scheduled));
        } else {
            $opacs_books_lent->lent_flag        = 1;
            $opacs_books_lent->return_scheduled = date('Y-m-d 00:00:00', strtotime($request->return_scheduled));
        }
        // データ保存
        $opacs_books_lent->save();

        $message = '貸し出し登録しました。';

        // メール送信
        $subject = '図書を貸し出し登録しました。';
        $content = $request->req_student_no . " が貸し出し登録しました。\n";
        $content .= 'ISBN：' . $opacs_books->isbn . "\n";
        $content .= 'タイトル：' . $opacs_books->title . "\n";
        $content .= '返却期限日：' . $request->return_scheduled . "\n";

        $opacs = Opacs::where('id', $opacs_books->opacs_id)->first();

        // 郵送リクエストしたユーザーに送付するメール本文
        $user_subject = '図書館で貸出処理が行われました。';
        $user_content = "図書館で貸出処理が行われました。本が届くまでしばらくお待ちください。\n";
        $user_content .= 'ISBN：' . $opacs_books->isbn . "\n";
        $user_content .= 'タイトル：' . $opacs_books->title . "\n";
        $user_content .= '返却期限日：' . $request->return_scheduled . "\n";

        // メール送付
        self::sendMailOpac($opacs, $subject, $content);
        self::sendMailOpacUser($opacs, $user_subject, $user_content, $opacs_books_lent->email);

        // モデレータの一覧画面へ戻る
        return $this->lentlist($request, $page_id, $frame_id, '貸し出し処理が完了しました。');
    }

    /**
     *  郵送貸し出しリクエスト
     */
    public function requestLent($request, $page_id, $frame_id, $opacs_books_id)
    {
        // 認証されているか確認
        if (!Auth::check()) {
            return $this->viewError(403);
        }

        // 項目のエラーチェック
        $validator = Validator::make($request->all(), [
            'req_student_no'       => ['required'],
            'req_phone_no'         => ['required'],
            'req_email'            => ['required'],
            'req_postal_code'      => ['required'],
            'req_address'          => ['required'],
        ]);
        $validator->setAttributeNames([
            'req_student_no'       => '学籍番号/教職員番号',
            'req_phone_no'         => '連絡先電話番号',
            'req_email'            => '連絡先メールアドレス',
            'req_postal_code'      => '郵送先郵便番号',
            'req_address'          => '郵送先住所',
        ]);

        // エラーがあった場合は詳細画面に戻る。
        $message = null;
        if ($validator->fails()) {
            return $this->show($request, $page_id, $frame_id, $opacs_books_id, $message, null, $validator->errors());
        }

        // 書籍情報を取得し禁帯出書籍ではないかどうかチェックする
        $opac_book = OpacsBooks::where('id', $opacs_books_id)->first();
        if (empty($opac_book) == true) {
            return $this->show($request, $page_id, $frame_id, $opacs_books_id, '書籍情報がありません。', 'danger');
        }
        if ($opac_book->lend_flag == '9:禁帯出') {
            return $this->show($request, $page_id, $frame_id, $opacs_books_id, 'この書籍は「禁帯出」のため、貸し出しリクエストできません。', 'danger');
        }

        // 自分が既に郵送リクエスト済みかチェックする
        if ($this->lentRequestCheck($opacs_books_id, $request->req_student_no) == true) {
            return $this->show($request, $page_id, $frame_id, $opacs_books_id, 'この書籍は既に郵送リクエスト済みです。', 'danger');
        }

        // 書籍貸し出しデータ新規オブジェクト
        $opacs_books_lents                   = new OpacsBooksLents();
        $opacs_books_lents->opacs_books_id   = $opacs_books_id;
        $opacs_books_lents->lent_flag        = 2;
        $opacs_books_lents->student_no       = $request->req_student_no;
        $opacs_books_lents->phone_no         = $request->req_phone_no;
        $opacs_books_lents->email            = $request->req_email;
        $opacs_books_lents->postal_code      = $request->req_postal_code;
        $opacs_books_lents->address          = $request->req_address;
        $opacs_books_lents->mailing_name     = $request->req_mailing_name;

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
        self::sendMailOpac($opacs, $subject, $content);

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
            return $this->viewError(403);
        }

        // 項目のエラーチェック条件設定（バーコード）
        if (empty($request->return_barcode)) {
            $messages = new MessageBag;
            $messages->add('return_barcode', '返却用バーコードは必須です。');
            return $this->lentList($request, $page_id, $frame_id, null, $messages);
        }

        // 貸出情報取得
        $opac_books_lent = null;

        // バーコードから書籍情報を取得する
        $opacs_book = OpacsBooks::where('barcode', $request->return_barcode)->first();
        if (empty($opacs_book)) {
            $messages = new MessageBag;
            $messages->add('return_barcode', 'バーコードに該当する書籍がありません。');
            return $this->lentList($request, $page_id, $frame_id, null, $messages);
        }

        // opacs_books_idがない場合は、バーコードで取得したIDを設定する
        if (empty($opacs_books_id) == true) {
            $opacs_books_id = $opacs_book->id;
        }

        // 貸出中でないかのチェック
        if ($this->lentCheck($opacs_books_id)) {
            $messages = new MessageBag;
            $messages->add('return_barcode', 'この書籍は貸出中ではありません。');
            return $this->lentList($request, $page_id, $frame_id, null, $messages);
        }

        // 貸し出し中書籍
        $books_lents = OpacsBooksLents::where('opacs_books_id', $opacs_books_id)->where('lent_flag', [1])->first();

        // 返却メール送付用にメールアドレスだけ退避
        $add_email = $books_lents->email;

        // 書籍貸し出しデータ
        $books_lents->lent_flag   = 9;
        $books_lents->student_no  = null;
        $books_lents->return_date = date('Y-m-d 00:00:00');
        $books_lents->phone_no    = null;
        $books_lents->email       = null;
        $books_lents->postal_code  = null;
        $books_lents->address      = null;
        $books_lents->mailing_name = null;


        // データ保存
        $books_lents->save();

        $message = '返却しました。';

        // 書籍データ
        $opacs_books = OpacsBooks::where('id', $opacs_books_id)->first();

        // メール送信
        $subject = '図書を返却しました。';
        $content = "";
        $content .= 'ISBN：' . $opacs_books->isbn . "\n";
        $content .= 'タイトル：' . $opacs_books->title . "\n";
        $content .= '返却日：' . $books_lents->return_date . "\n";

        // メール送信
        $user_subject = '図書館で返却処理が終了しました。';
        $user_content = "図書館で返却処理が終了しました。\n";
        $user_content .= 'ISBN：' . $opacs_books->isbn . "\n";
        $user_content .= 'タイトル：' . $opacs_books->title . "\n";
        $user_content .= '返却日：' . $books_lents->return_date . "\n";

        $opacs = Opacs::where('id', $opacs_books->opacs_id)->first();

        self::sendMailOpac($opacs, $subject, $content);
        self::sendMailOpacUser($opacs, $user_subject, $user_content, $add_email);

        // MyOpac画面へ遷移
//        return $this->index($request, $page_id, $frame_id, null, ['返却処理が完了しました。']);
        return $this->lentList($request, $page_id, $frame_id, '返却処理が完了しました。');

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

        if ($request->opac_search_type != 2) {
            // キーワードをセッションに保存しておく。
            $request->session()->put('search_keyword.'.$frame_id, $request->keyword);
        } else {
            // 検索条件をセッションに保存しておく。
            $request->session()->put('opac_search_condition.'.$frame_id, $request->opac_search_condition);
        }

        $request->session()->put('opac_search_type.'      .$frame_id, $request->opac_search_type);
        $request->session()->put('opac_search_sort_type.' .$frame_id, $request->opac_search_sort_type);
        $request->session()->put('opac_search_view_count.'.$frame_id, $request->opac_search_view_count);

        // 検索はフォームでredirect指定しているので、ここは無効になるけれども、一応置いている。
        //return $this->index($request, $page_id, $frame_id);
    }

    /**
     *  検索条件クリア
     */
    public function searchClear($request, $page_id, $frame_id)
    {
        // セッション初期化などのLaravel 処理。
        $request->flash();

        // セッションに保存した条件を削除する
        $request->session()->forget('search_keyword.'.$frame_id);

        // 検索はフォームでredirect指定しているので、ここは無効になるけれども、一応置いている。
        return $this->index($request, $page_id, $frame_id);
    }

    /**
     *  検索詳細条件クリア
     */
    public function searchDetailClear($request, $page_id, $frame_id)
    {
        // セッション初期化などのLaravel 処理。
        $request->flash();

        // セッションに保存した条件を削除する
        $request->session()->forget('opac_search_condition.'.$frame_id);

        // 検索はフォームでredirect指定しているので、ここは無効になるけれども、一応置いている。
        return $this->index($request, $page_id, $frame_id);
    }

    /**
     * Opacフレーム設定表示画面
     *
     * @method_title 表示設定
     * @method_desc このフレームに表示する際のOpacをカスタマイズできます。
     * @method_detail 初期表示する機能を選択できます。
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

        // メッセージ表示
        session()->flash('change_buckets_opacs', '設定しました。');

        return;
    }

    /**
     *  貸し出し中一覧
     */
    public function lentlist($request, $page_id, $frame_id, $message = null, $errors = null)
    {
        // 権限チェック
        if ($this->can('role_article')) {
            return $this->viewError(403);
        }

        // 郵送リクエスト一覧取得
        $books_requests = OpacsBooksLents::select('opacs_books_lents.*', 'users.name', 'opacs_books.title', 'opacs_books.barcode', 'opacs_books.isbn')
                                      ->leftJoin('opacs_books', 'opacs_books.id', '=', 'opacs_books_lents.opacs_books_id')
                                      ->leftJoin('users', 'users.userid', '=', 'opacs_books_lents.student_no')
                                      ->where('lent_flag', [2])
                                      ->orderBy('opacs_books_lents.created_at', 'asc')
                                      ->get();


        // 貸し出し中一覧取得
        $books_lents = OpacsBooksLents::select('opacs_books_lents.*', 'users.name', 'opacs_books.title', 'opacs_books.barcode', 'opacs_books.isbn')
                                      ->leftJoin('opacs_books', 'opacs_books.id', '=', 'opacs_books_lents.opacs_books_id')
                                      ->leftJoin('users', 'users.userid', '=', 'opacs_books_lents.student_no')
                                      ->where('lent_flag', '<>', [2])
                                      ->whereNull('opacs_books_lents.return_date')
                                      ->orderBy('opacs_books_lents.return_scheduled', 'asc')
                                      ->get();

        // Opacフレーム設定画面を呼び出す。
        return $this->view(
            'opacs_lentlist', [
            'books_lents'    => $books_lents,
            'books_requests' => $books_requests,
            'message'        => $message,
            'errors'         => $errors,
            ]
        );
    }

    /**
     *  書籍データ検索
     */
    public function getBookInfo($request, $page_id, $frame_id, $opacs_books_id = null)
    {
        // OPAC＆フレームデータ
        $opac_frame = $this->getOpacFrame($frame_id);

        // 空のデータ(画面で初期値設定で使用するため)
        $opacs_books = new OpacsBooks();

       // 書籍データ取得
        $input_error_message = '';
        list($tmp_opacs_books, $search_error_message) = $this->getBook($request, $opacs_books);
        if (empty($tmp_opacs_books)) {
            $input_error_message = '書誌データが検索できませんでした。';
        } else {
            $opacs_books = $tmp_opacs_books;
        }

        // 表示テンプレートを呼び出す。(blade でold を使用するため、withInput 使用)
        return $this->view(
            'opacs_input', [
            'opac_frame'  => $opac_frame,
            'opacs_books' => $opacs_books,
            'book_search' => $request->book_search,
            'input_error_message' => $input_error_message,
            ]
        )->withInput($request->all);
    }

    /**
     *  貸出リクエスト削除
     */
    public function destroyRequest($request, $page_id, $frame_id, $opacs_books_id)
    {
        // 認証されているか確認
        if (!Auth::check()) {
            return $this->viewError(403);
        }

        // 権限と学籍番号のチェックして、対象ユーザーIDを取得する
        $user_id = "";
        if (isset($request->req_student_no) === true) {
            // 学籍番号指定ありなので権限ありであるかどうかチェックする
            if ($this->can('role_article')) {
                return $this->viewError(403);
            }
            $user_id = $request->req_student_no;
        } else {
            // 一般ユーザーからのリクエストの場合はこのまま処理
            $user = Auth::user();
            $user_id = $user->userid;
        }


        // 対象書籍を当該ユーザーが貸出リクエスト中かチェックする
        $user = Auth::user();
        $books_lents = OpacsBooksLents::where('opacs_books_id', $opacs_books_id)
                                      ->where('lent_flag', [2])
//                                      ->where('student_no', $user->userid)
                                      ->where('student_no', $user_id)
//                                      ->get();
                                      ->first();
//        if (count($books_lents) <= 0) {
        if (empty($books_lents) == true) {
                session()->flash('lent_errors', '対象書籍は貸出リクエストされていません。');
            return $this->index($request, $page_id, $frame_id);
        }

/*
        // 貸し出し中書籍の情報を上書きする為に取得する
        $books_lents = OpacsBooksLents::where('opacs_books_id', $opacs_books_id)
                                      ->where('lent_flag', [2])
//                                      ->where('student_no', $user->userid)
                                      ->where('student_no', $user_id)
                                      ->first();

        // 書籍貸出データ
        $books_lents->lent_flag   = 9;
        $books_lents->student_no  = null;
        $books_lents->return_date = date('Y-m-d 00:00:00');
        $books_lents->phone_no    = null;
        $books_lents->email       = null;

        // データ保存
        $books_lents->save();
*/
        // 対象データを削除する
        OpacsBooksLents::where('id', $books_lents->id)->delete();

        // 書籍データ
        $opacs_books = OpacsBooks::where('id', $opacs_books_id)->first();

        // メール送信
        $subject = '貸し出しリクエストを削除しました。';
        $content = "";
        $content .= 'ISBN：' . $opacs_books->isbn . "\n";
        $content .= 'タイトル：' . $opacs_books->title . "\n";
        $content .= '返却日：' . $books_lents->return_date . "\n";

        $opacs = Opacs::where('id', $opacs_books->opacs_id)->first();
        self::sendMailOpac($opacs, $subject, $content);

        // MyOpac画面へ遷移
        return $this->index($request, $page_id, $frame_id, null, ['貸し出しリクエストを削除しました。']);
    }
}
