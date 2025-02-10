<?php

namespace App\Plugins\User\Rsses;

use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Collection;
use App\Models\Common\Buckets;
use App\Models\Common\Frame;
use App\Models\User\Rsses\Rsses;
use App\Models\User\Rsses\RssUrls;
use App\Rules\CustomValiUrlMax;
use App\Enums\ShowType;
use App\Plugins\User\UserPluginBase;
use App\Utilities\Curl\CurlUtils;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\Rule;

/**
 * RSS・プラグイン
 *
 * @author 堀口正行 <horiguchi@opensource-workshop.jp>
 * @copyright OpenSource-WorkShop Co.,Ltd. All Rights Reserved
 * @category RSS・プラグイン
 * @package Controller
 * @plugin_title RSS
 * @plugin_desc RSSを取得して表示することができます。
 */
class RssesPlugin extends UserPluginBase
{
    /* オブジェクト変数 */

    /**
     * POST チェックに使用する getPost() 関数を使うか
     */
    public $use_getpost = false;

    /* コアから呼び出す関数 */

    /**
     *  関数定義（コアから呼び出す）
     */
    public function getPublicFunctions()
    {
        // 標準関数以外で画面などから呼ばれる関数の定義
        $functions = array();
        $functions['get']  = [
            'index',
            'editUrl',
        ];
        $functions['post'] = [
            'index',
            'addUrl',
            'updateUrls',
            'deleteUrl',
            'updateUrlSequence',
        ];
        return $functions;
    }

    /**
     * 追加の権限定義（コアから呼び出す）
     */
    public function declareRole()
    {
        // 権限チェックテーブル
        $role_check_table = [];
        $role_check_table["addUrl"]            = ['buckets.addColumn'];
        $role_check_table["editUrl"]           = ['buckets.editColumn'];
        $role_check_table["updateUrls"]        = ['buckets.saveColumn'];
        $role_check_table["deleteUrl"]         = ['buckets.deleteColumn'];
        $role_check_table["updateUrlSequence"] = ['buckets.upColumnSequence', 'buckets.downColumnSequence'];
        return $role_check_table;
    }

    /* private関数 */

    /**
     *  フレームIDに紐づくプラグインデータ取得
     */
    private function getRsses($frame_id)
    {
        $rss = Rsses::query()
            ->select('rsses.*')
            ->join('frames', 'frames.bucket_id', '=', 'rsses.bucket_id')
            ->where('frames.id', '=', $frame_id)
            ->first();

        return $rss;
    }

    /**
     *  フレームに紐づくRSSID とフレームデータの取得
     */
    private function getRssFrame($frame_id)
    {
        // Frame データ
        $frame = Frame::query()
            ->select(
                'frames.*',
                'rsses.id as rsses_id'
            )
            ->leftJoin('rsses', 'rsses.bucket_id', '=', 'frames.bucket_id')
            ->where('frames.id', $frame_id)
            ->first();
        return $frame;
    }

    /* 画面アクション関数 */

    /**
     *  データ初期表示関数
     *  コアがページ表示の際に呼び出す関数
     *
     * @method_title RSS表示
     * @method_desc RSSを取得して表示します。
     * @method_detail 複数のRSSを設定でき、キャプション表示やリンクの設定ができます。
     */
    public function index($request, $page_id, $frame_id, $errors = null)
    {
        // Rsses、Frame データ
        $rss = $this->getRsses($frame_id);

        $setting_error_messages = null;
        $rss_urls = new Collection();
        if ($rss) {
            $rss_urls = RssUrls::query()
                ->where('rsses_id', $rss->id)
                ->where('display_flag', ShowType::show)
                ->orderBy('display_sequence')
                ->get();
        } else {
            // フレームに紐づくRSS親データがない場合
            $setting_error_messages[] = 'フレームの設定画面から、使用するRSSブロックを選択するか、作成してください。';
        }

        if ($rss_urls->count() == 0) {
            // フレームに紐づくRSS子データがない場合
            $setting_error_messages[] = 'フレームの設定画面から、使用するRSSを定義してください。';
        } else {
            foreach ($rss_urls as &$urls) {
                // 経過時間をチェック
                $execute_flag = $this->isExpireat($urls->xml_updated_at, $rss->cache_interval);
                if ($execute_flag) {// xmlデータを取得する
                    $xml_response = $this->getXml($urls->url);
                    if ($xml_response) {
                        // 取得したXMLデータを更新する
                        $this->updateXmlresponce($urls->id, $xml_response);
                    }
                } else {
                    // データから取得
                    $xml_response = $urls->xml;
                }
                // xmlレスポンスエラーの場合がある
                if ($xml_response) {
                    // Connect-CMS用にパース
                    $xmlitems = $this->xmlParse($xml_response);
                    // 出力数を調整
                    $xmlitems = array_splice($xmlitems, 0, $urls->item_count);
                    // 画面様に変数にセット
                    $urls->items = $xmlitems;
                }
            }

            // まとめて表示する場合
            $merge_urls = array();
            if ($rss->mergesort_flag) {
                foreach ($rss_urls as $urls) {
                    if (isset($urls->items)) {
                        foreach ($urls->items as $item) {
                            $merge_urls[$item["pubDateTime"]] = array_merge($item, ['rss_title'=>$urls->title,'caption'=>$urls->caption]);
                        }
                    }
                }
                // 降順に並べる
                krsort($merge_urls);
                // 出力数を調整
                $merge_urls = array_splice($merge_urls, 0, $rss->mergesort_count);
            }
        }


        if (empty($setting_error_messages)) {
            // 表示テンプレートを呼び出す。
            return $this->view('rsses', [
                'request' => $request,
                'frame_id' => $frame_id,
                'rss' => $rss,
                'rss_urls' => $rss_urls,
                'merge_urls' => $merge_urls,
                'errors' => $errors,
            ]);
        } else {
            // エラーあり
            return $this->view('rsses_error_messages', [
                'error_messages' => $setting_error_messages,
            ]);
        }
    }

    // 期限切れチェック
    private function isExpireat($last_fetch_at, $interval_min)
    {
        // 0 の場合は都度取得する
        if (!$interval_min) {
            return true;
        }
        // 新規取得時は取得する
        if (!$last_fetch_at) {
            return true;
        }

        // 一定時間が経過したかどうかのチェック
        $current_at = Carbon::now(); // 現在の時刻を取得
        $last_fetch_at = Carbon::now()->subMinutes($interval_min);
        if ($last_fetch_at > $current_at) {
            return true;
        }
        return false;
    }

    // XMLデータをDBに登録する
    private function updateXmlresponce($url_id, $xml_response)
    {
        // 項目の更新処理
        $rss_url = RssUrls::find($url_id);
        $rss_url->xml = $xml_response;
        $rss_url->xml_updated_at = now();
        $rss_url->save();
    }

    // XMLデータ取得
    private function getXml($url)
    {
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_HEADER, 0);
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);// リダイレクトを許可する
        curl_setopt($ch, CURLOPT_URL, $url);// 他の設定やURLを追加する（例：URLの設定）
        $response = curl_exec($ch);
        curl_close($ch);
        if (isset($response)) {
            return $response;
        }
        return false;
    }

    private function xmlParse($response)
    {
        // SimpleXMLを使用してXMLデータをオブジェクトに変換
        $xml = simplexml_load_string($response);

        // フィードのバージョンに応じてアイテムを取得
        if (isset($xml->channel->item)) {
            $xml_version = '2';
            $items = $xml->channel->item;
        } else {
            $xml_version = '1';
            $items = $xml->item;
        }

        $ret_items = [];
        foreach ($items as $item) {
            $title = htmlspecialchars($item->title, ENT_QUOTES, 'UTF-8');
            $link = htmlspecialchars($item->link, ENT_QUOTES, 'UTF-8');
            $description = htmlspecialchars($item->description, ENT_QUOTES, 'UTF-8');
            // ver.によって異なる可能性に対応
            $date_format = "Y/m/d H:i:s";
            if ($xml_version == "2") {
                $pubDateTime = date($date_format, strtotime($item->pubDate));
            } else {
                $pubDateTime = date($date_format, strtotime($item->children("http://purl.org/dc/elements/1.1/")->date));
            }
            $ret_items[] = [
                    'title' => $title,
                    'link' => $link,
                    'description' => $description,
                    'pubDateTime' => $pubDateTime,
                    'pubDate' => date("Y/m/d", strtotime($pubDateTime)),
            ];
        }
        return $ret_items;
    }




    /**
     * RSS新規作成画面
     *
     * @method_title 作成
     * @method_desc RSSを新しく作成します。
     * @method_detail RSS名やコントロールの表示など設定して、RSSを作成できます。
     */
    public function createBuckets($request, $page_id, $frame_id, $rsses_id = null, $is_create = false, $message = null, $errors = null)
    {
        // 新規作成フラグを付けてRSS設定変更画面を呼ぶ
        $is_create = true;
        return $this->editBuckets($request, $page_id, $frame_id, $rsses_id, $is_create, $message, $errors);
    }

    /**
     * RSS設定変更画面の表示
     */
    public function editBuckets($request, $page_id, $frame_id, $rsses_id = null, $is_create = false, $message = null, $errors = null)
    {
        // セッション初期化などのLaravel 処理。
        $request->flash();

        // RSS＆フレームデータ
        $rss_frame = $this->getRssFrame($frame_id);

        // RSSデータ
        $rss = new Rsses();

        if (!empty($rsses_id)) {
            // rsses_id が渡ってくればrsses_id が対象
            $rss = Rsses::where('id', $rsses_id)->first();
        } elseif (!empty($rss_frame->bucket_id) && $is_create == false) {
            // Frame のbucket_id があれば、bucket_id からRSSデータ取得、なければ、新規作成か選択へ誘導
            $rss = Rsses::where('bucket_id', $rss_frame->bucket_id)->first();
        }

        // 表示テンプレートを呼び出す。
        return $this->view(
            'rsses_edit_rss',
            [
                'rss_frame' => $rss_frame,
                'rss' => $rss,
                'is_create' => $is_create,
                'message' => $message,
                'errors' => $errors,
            ]
        )->withInput($request->all);
    }

    /**
     *  RSS登録処理
     */
    public function saveBuckets($request, $page_id, $frame_id, $rsses_id = null)
    {
        // エラーチェック
        $validator_values['rsses_name'] = ['required'];
        $validator_attributes['rsses_name'] = 'RSS名';
        $validator_values['cache_interval'] = ['required', 'numeric', 'integer', 'min:0', 'max:60'];
        $validator_attributes['cache_interval'] = '再取得時間(分)';
        $validator_values['mergesort_flag'] = ['required'];
        $validator_attributes['mergesort_flag'] = ['まとめて表示'];
        $validator_values['mergesort_count'] = ['numeric', 'integer', 'min:0', 'max:100'];
        $validator_attributes['mergesort_count'] = ['まとめ表示数'];

        // 項目のエラーチェック
        $validator = Validator::make($request->all(), $validator_values);
        $validator->setAttributeNames($validator_attributes);

        // エラーがあった場合は入力画面に戻る。
        $message = null;
        if ($validator->fails()) {
            $is_create = $rsses_id ? false : true;
            return $this->editBuckets($request, $page_id, $frame_id, $rsses_id, $is_create, $message, $validator->errors());
        }

        // 更新後のメッセージ
        $message = null;

        // 画面から渡ってくるrsses_id が空ならバケツとRSSを新規登録
        if (empty($rsses_id)) {
            /**
             * 新規登録用の処理
             */
            $bucket = new Buckets();
            $bucket->bucket_name = '無題';
            $bucket->plugin_name = 'rsses';
            $bucket->save();

            // RSSデータ新規オブジェクト
            $rsses = new Rsses();
            $rsses->bucket_id = $bucket->id;

            // Frame のBuckets を見て、Buckets が設定されていなければ、作成したものに紐づける。
            // Frame にBuckets が設定されていない ＞ 新規のフレーム＆RSS作成
            // Frame にBuckets が設定されている ＞ 既存のフレーム＆RSS更新
            // （表示RSS選択から遷移してきて、内容だけ更新して、フレームに紐づけないケースもあるため）
            $frame = Frame::where('id', $frame_id)->first();
            if (empty($frame->bucket_id)) {
                // FrameのバケツIDの更新
                $frame = Frame::where('id', $frame_id)->update(['bucket_id' => $bucket->id]);
            }

            $message = 'RSS設定を追加しました。<br />' .
                        '　 [ <a href="' . url('/') . '/plugin/rsses/listBuckets/' . $page_id . '/' . $frame_id . '/#frame-' . $frame_id . '">RSS選択</a> ]から作成したRSSを選択後、［ 項目設定 ］で使用する項目を設定してください。';
        } else {
            /**
             * 更新用の処理
             */

            // RSSデータ取得
            $rsses = Rsses::where('id', $rsses_id)->first();

            $message = 'RSS設定を変更しました。';
        }

        /**
         * 登録処理 ※新規、更新共通
         */
        $rsses->rsses_name = $request->rsses_name;
        $rsses->cache_interval = $request->cache_interval;
        $rsses->mergesort_flag = $request->mergesort_flag;
        $rsses->mergesort_count = $request->mergesort_count;
        $rsses->save();

        // 新規作成フラグを更新モードにセットして設定変更画面へ遷移
        $is_create = false;

        return $this->editBuckets($request, $page_id, $frame_id, $rsses->id, $is_create, $message);
    }

    /**
     * RSS削除処理
     */
    public function destroyBuckets($request, $page_id, $frame_id, $rsses_id)
    {
        if ($rsses_id) {

            // バケツに紐づく明細データを取得
            $rss_urls = RssUrls::where('rsses_id', $rsses_id)->get();

            /**
             * 明細データの削除
             */
            foreach ($rss_urls as $url) {
                RssUrls::where('id', $url->id)->delete();
            }

            $rsses = Rsses::find($rsses_id);

            // backetsの削除
            Buckets::where('id', $rsses->bucket_id)->delete();

            // バケツIDの取得のためにFrame を取得(Frame を更新する前に取得しておく)
            $frame = Frame::where('id', $frame_id)->first();
            // フレームのbucket_idと削除するRSSのbucket_idが同じなら、FrameのバケツIDの更新する
            if ($frame->bucket_id == $rsses->bucket_id) {
                // FrameのバケツIDの更新
                Frame::where('bucket_id', $frame->bucket_id)->update(['bucket_id' => null]);
            }

            // RSS設定を削除する。
            Rsses::destroy($rsses_id);
        }

        // リダイレクト設定はフォーム側で設定している為、return処理は省略
    }

    /**
     * データ選択表示関数
     *
     * @method_title 選択
     * @method_desc このフレームに表示するRSSを選択します。
     * @method_detail
     */
    public function listBuckets($request, $page_id, $frame_id, $id = null)
    {
        // ソート設定に初期設定値をセット
        $sort_inits = [
            "rsses_updated_at" => ["desc", "asc"],
            "page_name" => ["desc", "asc"],
            "frame_title" => ["asc", "desc"],
            "rsses_name" => ["desc", "asc"],
        ];

        // 要求するソート指示。初期値として更新日の降順を設定
        $request_order_by = ["rsses_updated_at", "desc"];

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
        // * status は 0 のもののみ表示（データリスト表示はそれで良いと思う）
        // * 現在のものを最初に表示する。orderByRaw('buckets.id = ' . $this->buckets->id . ' desc') ※ desc 指定が必要だった。
        $buckets_query = Buckets::
            select(
                'buckets.*',
                'rsses.id as rsses_id',
                'rsses.rsses_name',
                'rsses.updated_at as rsses_updated_at',
                'frames.id as frames_id',
                'frames.frame_title',
                'pages.page_name'
            )
            ->join('rsses', function ($join) {
                $join->on('rsses.bucket_id', '=', 'buckets.id');
                $join->whereNull('rsses.deleted_at');
            })
            ->leftJoin('frames', 'buckets.id', '=', 'frames.bucket_id')
            ->leftJoin('pages', 'pages.id', '=', 'frames.page_id')
            ->where('buckets.plugin_name', 'rsses');

        // buckets を作っていない状態で、設定の表示コンテンツ選択を開くこともあるので、バケツがあるかの判定
        if (!empty($this->buckets)) {
            // buckets がある場合は、該当buckets を一覧の最初に持ってくる。
            $buckets_query->orderByRaw('buckets.id = ' . $this->buckets->id . ' desc');
        }

        $buckets_list = $buckets_query
            ->orderBy($request_order_by[0], $request_order_by[1])
            ->paginate(10, ["*"], "frame_{$frame_id}_page");

        return $this->view(
            'rsses_list_buckets',
            [
                'buckets_list'      => $buckets_list,
                'order_link'        => $order_link,
                'request_order_str' => implode('|', $request_order_by)
            ]
        );
    }

    /**
     * データ紐づけ変更関数
     */
    public function changeBuckets($request, $page_id = null, $frame_id = null, $id = null)
    {
        // FrameのバケツIDの更新
        Frame::where('id', $frame_id)->update(['bucket_id' => $request->select_bucket]);

        return;
    }

    /**
     * 項目の追加
     */
    public function addUrl($request, $page_id, $frame_id, $id = null)
    {
        // エラーチェック
        $request->validate([
            'url'         => ['required', new CustomValiUrlMax()],
            'item_count'  => ['required', 'numeric', 'integer', 'min:0', 'max:60'],
            'title'       => 'max:255',
            'caption'     => 'max:255',
        ]);

        // 新規登録時の表示順を設定
        $max_display_sequence = RssUrls::query()->where('rsses_id', $request->rsses_id)->max('display_sequence');
        $max_display_sequence = $max_display_sequence ? $max_display_sequence + 1 : 1;

        // 項目の登録処理
        $rss_url = new RssUrls();
        $rss_url->rsses_id = $request->rsses_id;
        $rss_url->url = $request->url;
        $rss_url->title = $request->title;
        $rss_url->caption = $request->caption;
        $rss_url->item_count = $request->item_count;
        $rss_url->display_flag = ShowType::show;
        $rss_url->display_sequence = $max_display_sequence;
        $rss_url->save();

        // フラッシュメッセージ設定
        $request->merge([
            'flash_message' => 'RSS項目を登録しました。'
        ]);

        // リダイレクト設定はフォーム側で設定している為、return処理は省略
    }

    /**
     * 項目の更新
     */
    public function updateUrls($request, $page_id, $frame_id)
    {
        // エラーチェック
        $validator = Validator::make($request->all(), [
            'urls.*'    => [new CustomValiUrlMax()],
            'captions.*'     => ['max:255'],
            'item_counts.*'     => ['max:255'],
        ]);
        $validator->setAttributeNames([
            'urls.*'    => "URL",
            'captions.*'     => "キャプション",
            'item_counts.*'     => "表示データ数",
        ]);

        $errors = array();
        if ($validator->fails()) {
            $request->merge(['validator' => $validator]);
            return;
        }

        foreach (array_keys($request->link_urls) as $url_id) {
            // 項目の更新処理
            $rss_url = RssUrls::find($url_id);
            $rss_url->display_flag = isset($request->display_flags[$url_id]) ? ShowType::show : ShowType::not_show;
            $rss_url->url = $request->link_urls[$url_id];
            $rss_url->title = $request->titles[$url_id];
            $rss_url->caption = $request->captions[$url_id];
            $rss_url->item_count = $request->item_counts[$url_id];
            $rss_url->save();
        }

        // フラッシュメッセージ設定
        $request->merge([
            'flash_message' => 'RSS項目を更新しました。'
        ]);

        // リダイレクト設定はフォーム側で設定している為、return処理は省略
    }

    /**
     * 項目編集画面の表示
     *
     * @method_title RSSの登録
     * @method_desc RSSを登録します。
     * @method_detail URLを登録できます。<br />RSS表示画面のタイトルとキャプション、表示データ数を設定できます。
     */
    public function editUrl($request, $page_id, $frame_id, $id = null, $message = null, $errors = null)
    {
        // 権限チェック
        if ($this->can('role_article_admin')) {
            return $this->viewError(403);
        }

        // フレームに紐づくRSSを取得
        $rss = $this->getRsses($frame_id);

        // RSSのID。まだRSSがない場合は0
        $rsses_id = !empty($rss) ? $rss->id : 0;

        // 項目データ取得
        $urls = RssUrls::query()
            ->select(
                'rss_urls.*',
            )
            ->where('rss_urls.rsses_id', $rsses_id)
            ->orderby('rss_urls.display_sequence')
            ->get();

        return $this->view(
            'rsses_edit',
            [
                'rsses_id'   => $rsses_id,
                'urls'       => $urls,
                'message'    => $message,
            ]
        );
    }

    /**
     * 項目の削除
     */
    public function deleteUrl($request, $page_id, $frame_id)
    {
        $rss_url = RssUrls::find($request->url_id);

        // 項目の削除
        $rss_url->delete();
        // フラッシュメッセージ設定
        $request->merge([
            'flash_message' => 'RSS項目を削除しました。'
        ]);
        // リダイレクト設定はフォーム側で設定している為、return処理は省略
    }

    /**
     * 項目の表示順の更新
     */
    public function updateUrlSequence($request, $page_id, $frame_id)
    {
        // ボタンが押された行の項目データ
        $target_item = RssUrls::find($request->url_id);

        // ボタンが押された前（後）の項目データ
        $query = RssUrls::query()
            ->where('rsses_id', $request->rsses_id);
        $pair_item = $request->display_sequence_operation == 'up' ?
            $query->where('display_sequence', '<', $request->display_sequence)->orderby('display_sequence', 'desc')->limit(1)->first() :
            $query->where('display_sequence', '>', $request->display_sequence)->orderby('display_sequence', 'asc')->limit(1)->first();

        // それぞれの表示順を退避
        $target_item_display_sequence = $target_item->display_sequence;
        $pair_item_display_sequence = $pair_item->display_sequence;

        // 入れ替えて更新
        $target_item->display_sequence = $pair_item_display_sequence;
        $target_item->save();
        $pair_item->display_sequence = $target_item_display_sequence;
        $pair_item->save();

        // フラッシュメッセージ設定
        $request->merge([
            'flash_message' => '項目の表示順を更新しました。'
        ]);

        // リダイレクト設定はフォーム側で設定している為、return処理は省略
    }
}
