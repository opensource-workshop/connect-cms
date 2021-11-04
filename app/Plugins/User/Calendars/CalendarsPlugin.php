<?php

namespace App\Plugins\User\Calendars;

use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;

//use Carbon\Carbon;

use App\Enums\StatusType;

use App\Models\Common\Buckets;
use App\Models\Common\ConnectCarbon;
use App\Models\Common\Frame;
use App\Models\User\Calendars\Calendar;
use App\Models\User\Calendars\CalendarFrame;
use App\Models\User\Calendars\CalendarPost;

use App\Plugins\User\UserPluginBase;

use App\Rules\CustomValiWysiwygMax;

/**
 * カレンダー・プラグイン
 *
 * @author 永原　篤 <nagahara@opensource-workshop.jp>
 * @copyright OpenSource-WorkShop Co.,Ltd. All Rights Reserved
 * @category カレンダー・プラグイン
 * @package Controller
 */
class CalendarsPlugin extends UserPluginBase
{
    /* DB migrate
       php artisan make:migration create_calendars --create=calendars
       php artisan make:migration create_calendar_posts --create=calendar_posts
       php artisan make:migration create_calendar_frames --create=calendar_frames
    */

    /* オブジェクト変数 */

    /**
     * 変更時のPOSTデータ
     */
    public $post = null;

    /* コアから呼び出す関数 */

    /**
     * 関数定義（コアから呼び出す）
     */
    public function getPublicFunctions()
    {
        // 標準関数以外で画面などから呼ばれる関数の定義
        $functions = array();
        $functions['get']  = ['index'];
        $functions['post'] = ['edit'];
        return $functions;
    }

    /**
     *  権限定義
     */
    public function declareRole()
    {
        // 権限チェックテーブル (追加チェックなし)
        $role_check_table = [];
        return $role_check_table;
    }

    /**
     * 編集画面の最初のタブ（コアから呼び出す）
     *
     * スーパークラスをオーバーライド
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
        if (is_null($action)) {
            // プラグイン内からの呼び出しを想定。処理を通す。
        } elseif (in_array($action, ['edit', 'save', 'delete'])) {
            // コアから呼び出し。posts.update|posts.deleteの権限チェックを指定したアクションは、処理を通す。
        } else {
            // それ以外のアクションは null で返す。
            return null;
        }

        // 一度読んでいれば、そのPOSTを再利用する。
        if (!empty($this->post)) {
            return $this->post;
        }

        // POST を取得する。
        $this->post = CalendarPost::
            where(function ($query) {
                $query = $this->appendAuthWhereBase($query, 'calendar_posts');
            })
            ->firstOrNew(['id' => $id]);

        return $this->post;
    }

    /* private関数 */

    /**
     * プラグインのフレーム
     */
    private function getPluginFrame($frame_id)
    {
        return CalendarFrame::firstOrNew(['frame_id' => $frame_id]);
    }

    /**
     * プラグインのバケツ取得関数
     */
    private function getPluginBucket($bucket_id)
    {
        // プラグインのメインデータを取得する。
        return Calendar::firstOrNew(['bucket_id' => $bucket_id]);
    }

    /**
     *  POST一覧取得
     */
    private function getPosts($from_date, $to_date)
    {
        // データ取得
        $posts_query = CalendarPost::select('calendar_posts.*')
                                   ->join('calendars', function ($join) {
                                       $join->on('calendars.id', '=', 'calendar_posts.calendar_id')
                                          ->where('calendars.bucket_id', '=', $this->frame->bucket_id);
                                   })
                                   ->where('calendar_posts.start_date', '<=', $to_date)
                                   ->where('calendar_posts.end_date', '>=', $from_date);

        // 権限によって表示する記事を絞る
        $posts_query = $this->appendAuthWhereBase($posts_query, 'calendar_posts');

        // 取得
        return $posts_query->get();
    }

    /* スタティック関数 */

    /**
     *  新着情報用メソッド
     */
    public static function getWhatsnewArgs()
    {
        // 戻り値('sql_method'、'link_pattern'、'link_base')
        $return[] = DB::table('calendars_posts')
                      ->select(
                          'frames.page_id           as page_id',
                          'frames.id                as frame_id',
                          'calendars_posts.id           as post_id',
                          'calendars_posts.title        as post_title',
                          DB::raw("null             as important"),
                          'calendars_posts.created_at   as posted_at',
                          'calendars_posts.created_name as posted_name',
                          DB::raw("null             as classname"),
                          DB::raw("null             as category"),
                          DB::raw('"calendars"          as plugin_name')
                      )
                      ->join('calendars', 'calendars.id', '=', 'calendars_posts.calendars_id')
                      ->join('frames', 'frames.bucket_id', '=', 'calendars.bucket_id')
                      ->where('frames.disable_whatsnews', 0)
                      ->whereNull('calendars_posts.deleted_at');

        $return[] = 'show_page_frame_post';
        $return[] = '/plugin/calendars/show';

        return $return;
    }

    /**
     *  検索用メソッド
     */
    /*
    public static function getSearchArgs($search_keyword)
    {
        $return[] = DB::table('calendars_posts')
                      ->select(
                          'calendars_posts.id           as post_id',
                          'frames.id                as frame_id',
                          'frames.page_id           as page_id',
                          'pages.permanent_link     as permanent_link',
                          'calendars_posts.title        as post_title',
                          DB::raw("null             as important"),
                          'calendars_posts.created_at   as posted_at',
                          'calendars_posts.created_name as posted_name',
                          DB::raw("null             as classname"),
                          DB::raw("null             as category_id"),
                          DB::raw("null             as category"),
                          DB::raw('"calendars"          as plugin_name')
                      )
                      ->join('calendars', 'calendars.id',  '=', 'calendars_posts.calendars_id')
                      ->join('frames', 'frames.bucket_id', '=', 'calendars.bucket_id')
                      ->leftjoin('pages', 'pages.id',      '=', 'frames.page_id')
                      ->where(function ($plugin_query) use ($search_keyword) {
                          $plugin_query->where('calendars_posts.title', 'like', '?')
                                       ->orWhere('calendars_posts.body', 'like', '?');
                      })
                      ->whereNull('calendars_posts.deleted_at');

        $bind = array('%'.$search_keyword.'%', '%'.$search_keyword.'%');
        $return[] = $bind;
        $return[] = 'show_page_frame_post';
        $return[] = '/plugin/calendars/show';

        return $return;
    }
    */

    /**
     *  カレンダー取得
     */
    private function getCalendarDates($year, $month)
    {
        $dateStr = sprintf('%04d-%02d-01', $year, $month);
        $date = new ConnectCarbon($dateStr);

        $daysInMonth = $date->daysInMonth;  // 右下をずらす際の計算で使用。月の日数
        $add_day = $date->dayOfWeek;        // 右下をずらす際の計算で使用。月の最初の日付番号

        // カレンダーの前月分となる左上の隙間用のデータを入れるためずらす
        $date->subDay($date->dayOfWeek);

        // 同上。右下の隙間のための計算。
        $count = $daysInMonth + $date->dayOfWeek + $add_day;
        $count = ceil($count / 7) * 7;
        $dates = [];

        for ($i = 0; $i < $count; $i++, $date->addDay()) {
            // copyしないと全部同じオブジェクトを入れてしまうことになる
            $dates[$date->format('Y-m-d')] = $date->copy();
        }
        $dates = $this->addHoliday($year, $month, $dates);
        return $dates;
    }

    /* 画面アクション関数 */

    /**
     *  データ初期表示関数
     *  コアがページ表示の際に呼び出す関数
     */
    public function index($request, $page_id, $frame_id)
    {
        // 曜日表示のために日本語設定にする。
        setlocale(LC_ALL, 'ja_JP.UTF-8');

        // プラグインのフレームデータ
        $plugin_frame = $this->getPluginFrame($frame_id);

        // 1ページ複数カレンダー配置に対応するため、'month' . $frame_id 等 フレームIDを付けて対応する。
        // 例えば 1ページ複数カレンダー配置して １個目カレンダーで $request->month = 11 とすると、２個目も $request->month = 11  がindex()に飛んでくるため。
        $request_year_name = 'year' . $frame_id;
        $request_month_name = 'month' . $frame_id;
        $request_day_name = 'day' . $frame_id;
        $session_year_name = 'calendar_year' . $frame_id;
        $session_month_name = 'calendar_month' . $frame_id;
        $session_day_name = 'calendar_day' . $frame_id;

        // 年月のセッション処理
        if ($request->filled($request_year_name)) {
            // リクエストに年月が渡ってきたら、セッションに保持しておく。（詳細や更新後に元のページに戻るため）
            $request->session()->put($session_year_name, $request->$request_year_name);
        } elseif (!session()->has($session_year_name)) {
            // 画面の指定もセッションにも値がなければ当日をセット
            $request->session()->put($session_year_name, date("Y"));
        }
        if ($request->filled($request_month_name)) {
            // リクエストに年月が渡ってきたら、セッションに保持しておく。（詳細や更新後に元のページに戻るため）
            $request->session()->put($session_month_name, $request->$request_month_name);
        } elseif (!session()->has($session_month_name)) {
            // 画面の指定もセッションにも値がなければ当日をセット
            $request->session()->put($session_month_name, date("m"));
        }
        if ($request->filled($request_day_name)) {
            // リクエストに年月が渡ってきたら、セッションに保持しておく。（詳細や更新後に元のページに戻るため）
            $request->session()->put($session_day_name, $request->$request_day_name);
        } elseif (!session()->has($session_day_name)) {

            // 画面の指定もセッションにも値がなければ、テンプレートによって 01か当日 をセット
            if ($this->frame->template == 'day') {
                $request->session()->put($session_day_name, date("d"));
            } else {
                $request->session()->put($session_day_name, '01');
            }
        }

        // カレンダーデータ一覧の取得
        $dates = $this->getCalendarDates(session($session_year_name), session($session_month_name));

        // 該当年月のデータの取得
        $posts = $this->getPosts(array_key_first($dates), array_key_last($dates));

        // 表示テンプレートを呼び出す。
        return $this->view('index', [
            'dates'            => $dates,
            'posts'            => $posts,
            // 'current_ym_first' => strtotime(session('calendar_year') . "/" . session('calendar_month') . "/01"),
            'current_ym_first' => strtotime(session($session_year_name) . "/" . session($session_month_name) . "/" . session($session_day_name)),
            'current_month'    => session($session_month_name),
            'plugin_frame'     => $plugin_frame,
        ]);
    }

    /**
     *  詳細表示関数
     */
    public function show($request, $page_id, $frame_id, $post_id)
    {
        // プラグインのフレームデータ
        // $plugin_frame = $this->getPluginFrame($frame_id);

        // 記事取得
        $post = $this->getPost($post_id);

        // 詳細画面を呼び出す。
        return $this->view('show', [
            'post' => $post,
        ]);
    }

    /**
     * 記事編集画面
     */
    public function edit($request, $page_id, $frame_id, $post_id = null)
    {
        // 記事取得
        $post = $this->getPost($post_id);

        // id が空なら、新規オブジェクトとみなして、デフォルトの日付を設定して画面を表示する。
        if (empty($post->id) && $request->filled("date")) {
            $post->start_date = $request->date;
        }

        // 変更画面を呼び出す。
        return $this->view('edit', [
            'post' => $post,
        ]);
    }

    /**
     *  記事登録処理
     */
    public function save($request, $page_id, $frame_id, $post_id = null)
    {
        // 項目のエラーチェック
        $validator = Validator::make($request->all(), [
            'title'      => ['required', 'max:255'],
            'body'       => ['nullable', new CustomValiWysiwygMax()],
            'start_date' => ['required', 'date'],
        ]);
        $validator->setAttributeNames([
            'title'      => 'タイトル',
            'body'      => '本文',
            'start_date' => '開始日時',
        ]);
        // エラーがあった場合は入力画面に戻る。
        if ($validator->fails()) {
            return back()->withErrors($validator)->withInput();
        }

        // 開始日時 < 終了日時のチェック
        if (!empty($request->end_date) && $request->start_date > $request->end_date) {
            $validator = Validator::make($request->all(), []);
            $validator->errors()->add('end_date', '終了日が開始日の前は設定できません。');
            return back()->withErrors($validator)->withInput();
        }

        // 開始時間 < 終了時間のチェック
        if (empty($request->end_date) || $request->start_date == $request->end_date) {
            if (!empty($request->end_time) && $request->start_time > $request->end_time) {
                $validator = Validator::make($request->all(), []);
                $validator->errors()->add('end_date', '終了時間が開始日の前は設定できません。');
                return back()->withErrors($validator)->withInput();
            }
        }

        // POSTデータのモデル取得
        $post = CalendarPost::firstOrNew(['id' => $post_id]);

        // フレームから calendar_id 取得
        $calendar_frame = $this->getPluginFrame($frame_id);

        // 値のセット
        $post->calendar_id = $calendar_frame->calendar_id;
        $post->allday_flag = $request->get('allday_flag');
        $post->start_date  = $request->start_date;
        $post->start_time  = $request->start_time;
        $post->end_date    = $request->end_date;
        $post->end_time    = $request->end_time;
        $post->title       = $request->title;
        $post->body        = $this->clean($request->body);   // wysiwygのXSS対応のJavaScript等の制限

        // bugfix: 【カレンダー】承認機能ONで一般が書き込んだ内容を、管理者が編集すると、以後その予定が一般で編集できなくなるバグ修正. created_idは UserableNohistory で自動セットするよう修正
        // 投稿者をセット
        // if (Auth::check()) {
        //     $post->created_id = Auth::user()->id;
        // }

        // 承認の要否確認とステータス処理
        if ($request->status == StatusType::temporary) {
            $post->status = StatusType::temporary;  // 一時保存
        // } elseif ($this->buckets->needApprovalUser(Auth::user())) {
        } elseif ($this->isApproval()) {
            $post->status = StatusType::approval_pending;  // 承認待ち
        } else {
            $post->status = StatusType::active;  // 公開
        }

        // 保存
        $post->save();

        // 登録後はリダイレクトして編集画面を開く。(form のリダイレクト指定では post した id が渡せないため)
        return new Collection(['redirect_path' => url('/') . "/plugin/calendars/edit/" . $page_id . "/" . $frame_id . "/" . $post->id . "#frame-" . $frame_id]);
    }

    /**
     *  承認処理
     */
    public function approval($request, $page_id, $frame_id, $post_id)
    {
        // 記事取得
        $post = $this->getPost($post_id);

        // データがあることを確認
        if (empty($post->id)) {
            return;
        }

        // 更新されたら、行レコードの updated_at を更新したいので、update()
        $post->updated_at = now();
        $post->status = StatusType::active;  // 公開
        $post->update();

        // 登録後は画面側の指定により、リダイレクトして表示画面を開く。
        return;
    }

    /**
     *  削除処理
     */
    public function delete($request, $page_id, $frame_id, $post_id)
    {
        // id がある場合、データを削除
        if ($post_id) {
            // データを削除する。（論理削除で削除日、ID などを残すためにupdate）
            CalendarPost::where('id', $post_id)->update([
                'deleted_at'   => date('Y-m-d H:i:s'),
                'deleted_id'   => Auth::user()->id,
                'deleted_name' => Auth::user()->name,
            ]);
        }
        return;
    }

    /**
     * プラグインのバケツ選択表示関数
     */
    public function listBuckets($request, $page_id, $frame_id, $id = null)
    {
        // 表示テンプレートを呼び出す。
        return $this->view('list_buckets', [
            'plugin_buckets' => Calendar::orderBy('created_at', 'desc')->paginate(10),
        ]);
    }

    /**
     * バケツ新規作成画面
     */
    public function createBuckets($request, $page_id, $frame_id)
    {
        // 処理的には編集画面を呼ぶ
        return $this->editBuckets($request, $page_id, $frame_id);
    }

    // delete: 使ってない
    // /**
    //  * フレーム表示設定画面の表示
    //  */
    // public function editView($request, $page_id, $frame_id)
    // {
    //     // 表示テンプレートを呼び出す。
    //     return $this->view('frame', [
    //         // 表示中のバケツデータ
    //         'calendar'       => $this->getPluginBucket($this->getBucketId()),
    //         'calendar_frame' => $this->getPluginFrame($frame_id),
    //     ]);
    // }

    // delete: 使ってない
    // /**
    //  * フレーム表示設定の保存
    //  */
    // public function saveView($request, $page_id, $frame_id, $calendar_id)
    // {
    //     // 項目のエラーチェック
    //     $validator = Validator::make($request->all(), [
    //         'view_count'       => ['nullable', 'numeric'],
    //         'view_format'      => ['nullable', 'numeric'],
    //         'thread_sort_flag' => ['nullable', 'numeric'],
    //     ]);
    //     $validator->setAttributeNames([
    //         'view_count'       => '表示件数',
    //         'view_format'      => '表示形式',
    //         'thread_sort_flag' => '根記事の表示順',
    //     ]);

    //     // エラーがあった場合は入力画面に戻る。
    //     if ($validator->fails()) {
    //         return back()->withErrors($validator)->withInput();
    //     }

    //     // フレームごとの表示設定の更新
    //     $calendar_frame = CalendarFrame::updateOrCreate(
    //         ['calendar_id' => $calendar_id, 'frame_id' => $frame_id],
    //         ['view_count'       => $request->view_count,
    //          'view_format'      => $request->view_format,
    //          'thread_sort_flag' => $request->thread_sort_flag],
    //     );

    //     return;
    // }

    /**
     * バケツ設定変更画面の表示
     */
    public function editBuckets($request, $page_id, $frame_id)
    {
        // コアがbucket_id なしで呼び出してくるため、bucket_id は frame_id から探す。
        if ($this->action == 'createBuckets') {
            $bucket_id = null;
        } else {
            $bucket_id = $this->getBucketId();
        }

        // 表示テンプレートを呼び出す。
        return $this->view('bucket', [
            // 表示中のバケツデータ
            'calendar' => $this->getPluginBucket($bucket_id),
        ]);
    }

    /**
     *  バケツ登録処理
     */
    public function saveBuckets($request, $page_id, $frame_id, $bucket_id = null)
    {
        // 項目のエラーチェック
        $validator = Validator::make($request->all(), [
            'name' => ['required'],
        ]);
        $validator->setAttributeNames([
            'name' => 'カレンダー名',
        ]);

        // エラーがあった場合は入力画面に戻る。
        if ($validator->fails()) {
            return back()->withErrors($validator)->withInput();
        }

        // バケツの取得。なければ登録。
        $bucket = Buckets::updateOrCreate(
            ['id' => $bucket_id],
            ['bucket_name' => $request->name, 'plugin_name' => 'calendars'],
        );

        // フレームにバケツの紐づけ
        $frame = Frame::find($frame_id)->update(['bucket_id' => $bucket->id]);

        // プラグインバケツを取得(なければ新規オブジェクト)
        // プラグインバケツにデータを設定して保存
        $calendar = $this->getPluginBucket($bucket->id);
        $calendar->name = $request->name;
        $calendar->save();

        // プラグインフレームを作成 or 更新
        $calendar_frame = CalendarFrame::updateOrCreate(
            ['frame_id' => $frame_id],
            ['calendar_id' => $calendar->id, 'frame_id' => $frame_id],
        );

        // 登録後はリダイレクトして編集ページを開く。
        return new Collection(['redirect_path' => url('/') . "/plugin/calendars/editBuckets/" . $page_id . "/" . $frame_id . "/" . $bucket->id . "#frame-" . $frame_id]);
    }

    /**
     * 削除処理
     */
    public function destroyBuckets($request, $page_id, $frame_id, $calendar_id)
    {
        // プラグインバケツの取得
        $calendar = Calendar::find($calendar_id);
        if (empty($calendar)) {
            return;
        }

        // POSTデータ削除(一気にDelete なので、deleted_id は入らない)
        // change: deleted_id, deleted_nameを自動セットするため、複数件削除する時は collectionのpluck('id')でid配列を取得して destroy()で消す。
        // CalendarPost::where('calendar_id', $calendar->id)->delete();
        $calendar_post_ids = CalendarPost::where('calendar_id', $calendar->id)->pluck('id');
        CalendarPost::destroy($calendar_post_ids);

        // FrameのバケツIDの更新
        Frame::where('id', $frame_id)->update(['bucket_id' => null]);

        // プラグインフレームデータの削除(deleted_id を記録するために1回読んでから削除)
        $calendar_frame = CalendarFrame::where('frame_id', $frame_id)->first();
        $calendar_frame->delete();

        // バケツ削除
        Buckets::find($calendar->bucket_id)->delete();

        // プラグインデータ削除
        $calendar->delete();

        return;
    }

    /**
     * データ紐づけ変更関数
     */
    public function changeBuckets($request, $page_id, $frame_id)
    {
        // FrameのバケツIDの更新
        Frame::where('id', $frame_id)->update(['bucket_id' => $request->select_bucket]);

        // Calendars の特定
        $plugin_bucket = $this->getPluginBucket($request->select_bucket);

        // フレームごとの表示設定の更新
        $calendar_frame = $this->getPluginFrame($frame_id);
        $calendar_frame->calendar_id = $plugin_bucket->id;
        $calendar_frame->frame_id = $frame_id;
        $calendar_frame->save();

        return;
    }
}
