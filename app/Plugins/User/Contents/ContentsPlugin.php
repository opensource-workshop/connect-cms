<?php

namespace App\Plugins\User\Contents;

use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;

use App\Models\Common\Buckets;
use App\Models\Common\Frame;
use App\Models\User\Contents\Contents;

use App\Plugins\User\UserPluginBase;

use App\Enums\StatusType;
use App\Rules\CustomValiWysiwygMax;

/**
 * コンテンツプラグイン
 *
 * 固定エリアのデータ登録ができるプラグイン。
 *
 * @author 永原　篤 <nagahara@opensource-workshop.jp>
 * @copyright OpenSource-WorkShop Co.,Ltd. All Rights Reserved
 * @category コンテンツプラグイン
 * @package Controller
 * @plugin_title 固定記事
 * @plugin_desc サイト上に文字や画像を配置できるプラグインです。
 */
class ContentsPlugin extends UserPluginBase
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
        $role_check_table["show"]      = ['frames.delete'];
        $role_check_table["delete"]    = ['frames.delete'];
        return $role_check_table;
    }

    /**
     *  編集画面の最初のタブ
     *
     *  スーパークラスをオーバーライド
     */
/*
    public function getFirstFrameEditAction()
    {
        return "editBucketsRoles";
    }
*/

    /**
     * POST取得関数（コアから呼び出す）
     * コアがPOSTチェックの際に呼び出す関数
     */
    public function getPost($id, $action = null)
    {
        if (is_null($action)) {
            // プラグイン内からの呼び出しを想定。処理を通す。

        // } elseif (in_array($action, ['edit', 'update', 'temporarysave', 'delete'])) {
        } elseif (in_array($action, ['update', 'temporarysave', 'delete'])) {
            // コアから呼び出し。posts.update|posts.deleteの権限チェックを指定したアクションは、処理を通す。
            // editはURLにidを含めていないため、メソッド側で追加の権限チェックで対応
        } else {
            // それ以外のアクションは null で返す。
            return null;
        }

        // 一度読んでいれば、そのPOSTを再利用する。
        if (!empty($this->post)) {
            return $this->post;
        }

        $this->post = Contents::
            // 権限があるときは、アクティブ、一時保存、承認待ちを or で取得
            where(function ($query) {
                $query = $this->appendAuthWhere($query, 'contents');
            })
            ->firstOrNew(['id' => $id]);

        return $this->post;
    }

    /**
     *  データ取得
     */
    private function getFrameContents($frame_id)
    {
        // buckets_id
        $buckets_id = null;
        if (!empty($this->buckets)) {
            $buckets_id = $this->buckets->id;
        }

        // Bucketsに応じたデータを返す。
        $contents = Contents::
            select(
                'contents.*',
                'buckets.id as bucket_id',
                'buckets.bucket_name as bucket_name',
                'frames.page_id as page_id'
            )
            ->join('buckets', 'buckets.id', '=', 'contents.bucket_id')
            ->join('frames', function ($join) {
                $join->on('frames.bucket_id', '=', 'buckets.id');
            })
            ->where('buckets.id', $buckets_id)
            // ->where('contents.deleted_at', null)
            // 権限があるときは、アクティブ、一時保存、承認待ちを or で取得
            ->where(function ($query) {
                $query = $this->appendAuthWhere($query, 'contents');
            })
            ->orderBy('id', 'desc')
            ->first();

//        // 管理者権限の場合は、一時保存も対象
//        //if (!empty($user) && $this->isCan('admin_system')$user->role == config('cc_role.ROLE_SYSTEM_MANAGER')) {
//        if (!empty($user) && $this->isCan('admin_system')) {
//
//            // フレームID が渡されるので、そのフレームに応じたデータを返す。
//            // 表示するデータ、バケツ、フレームをJOIN して取得
//            $contents = DB::table('contents')
//                        ->select('contents.*', 'buckets.id as bucket_id', 'frames.page_id as page_id')
//                        ->join('buckets', 'buckets.id', '=', 'contents.bucket_id')
//                        ->join('frames', function ($join) {
//                            $join->on('frames.bucket_id', '=', 'buckets.id');
//                        })
//                        ->where('frames.id', $frame_id)
//                        ->where('contents.deleted_at', null)
//                        // 権限があるときは、アクティブ、一時保存、承認待ちを or で取得
//                        ->where(function($query){ $query->where('contents.status', 0)->orWhere('contents.status', 1)->orWhere('contents.status', 2); })
//                        ->orderBy('id', 'desc')
//                        ->first();
//        }
//        else {
//
//            // フレームID が渡されるので、そのフレームに応じたデータを返す。
//            // 表示するデータ、バケツ、フレームをJOIN して取得
//            $contents = DB::table('contents')
//                        ->select('contents.*', 'buckets.id as bucket_id', 'frames.page_id as page_id')
//                        ->join('buckets', 'buckets.id', '=', 'contents.bucket_id')
//                        ->join('frames', function ($join) {
//                            $join->on('frames.bucket_id', '=', 'buckets.id');
//                        })
//                        ->where('frames.id', $frame_id)
//                        ->where('contents.deleted_at', null)
//                        ->where('contents.status', 0)
//                        ->orderBy('id', 'desc')
//                        ->first();
//        }
        return $contents;
    }

    /**
     * 記事の取得権限に対する条件追加
     * 固定記事は独自処理があるため、共通処理（appendAuthWhereBase）を使わない。
     */
    private function appendAuthWhere($query, $table_name)
    {
        // コンテンツ管理者の場合、全記事の取得
        // bugfix: 固定記事のモデレータは 権限設定 で 投稿できる 権限を制御してるため、ここでは許可しない
        // if ($this->isCan('role_article') || $this->isCan('role_article_admin')) {
        if ($this->isCan('role_article_admin')) {
            // 全件取得のため、追加条件なしで戻る。
        } elseif ($this->isCan('role_approval')) {
            //
            // 承認者(role_approval)権限 = Active ＋ 承認待ちの取得
            //
            $query->WhereIn($table_name . '.status', [StatusType::active, StatusType::approval_pending]);

        } elseif ($this->buckets && $this->buckets->canPostUser(Auth::user())) {
            //
            // モデレータ or 編集者権限の場合、Active ＋ 自分の全ステータス記事の取得
            //
            // bugfix: 承認あり なら、自分の承認ありデータも見れる必要あり。
            // $query->Where('status', '=', 0)
            $query->where(function ($tmp_query) use ($table_name) {
                $tmp_query->WhereIn($table_name . '.status', [StatusType::active, StatusType::approval_pending])
                    ->orWhere($table_name . '.created_id', '=', Auth::user()->id);
            });

        } else {
            // その他（ゲスト）
            $query->where($table_name . '.status', StatusType::active);
        }

        return $query;
    }

    /**
     *  検索用メソッド
     */
    public static function getSearchArgs($search_keyword, $page_ids = null)
    {
        // Query Builder のバグ？
        // whereIn で指定した引数が展開されずに、引数の変数分だけ、setBindings の引数を要求される。
        // そのため、whereIn とsetBindings 用の変数に同じ $page_ids を設定している。
        $query = DB::table('contents')
                   ->select(
                       'contents.id                 as post_id',
                       'frames.id                   as frame_id',
                       'frames.page_id              as page_id',
                       'pages.permanent_link        as permanent_link',
                       'frames.frame_title          as post_title',
                       DB::raw('0 as important'),
                       'contents.created_at         as posted_at',
                       'contents.created_name       as posted_name',
                       DB::raw('null as classname'),
                       DB::raw('null as categories_id'),
                       DB::raw('null as category'),
                       DB::raw('"contents" as plugin_name')
                   )
                   ->join('frames', 'frames.bucket_id', '=', 'contents.bucket_id')
                   ->join('pages', 'pages.id', '=', 'frames.page_id')
                   ->whereIn('pages.id', $page_ids)
                   ->where('status', StatusType::active)
                   ->where(function ($plugin_query) use ($search_keyword) {
                       $plugin_query->where('contents.content_text', 'like', '%'.$search_keyword.'%')
                                    ->orWhere('frames.frame_title', 'like', '%'.$search_keyword.'%');
                   })
                   ->whereNull('contents.deleted_at');

        $return[] = $query;
        $return[] = 'show_page';
        $return[] = '/page';

/*
        $return[] = DB::table('contents')
                      ->select('contents.id                 as post_id',
                               'frames.id                   as frame_id',
                               'frames.page_id              as page_id',
                               'pages.permanent_link        as permanent_link',
                               'frames.frame_title          as post_title',
                               DB::raw('0 as important'),
                               'contents.created_at         as posted_at',
                               'contents.created_name       as posted_name',
                               DB::raw('null as classname'),
                               DB::raw('null as categories_id'),
                               DB::raw('null as category'),
                               DB::raw('"contents" as plugin_name')
                              )
                      ->join('frames', 'frames.bucket_id', '=', 'contents.bucket_id')
                      ->leftjoin('pages', 'pages.id', '=', 'frames.page_id')
                      ->where('status', '?')

                       ->where(function($plugin_query) use($search_keyword) {
                           $plugin_query->where('contents.content_text', 'like', '?')
                                        ->orWhere('frames.frame_title', 'like', '?');
                       })

                      ->whereNull('contents.deleted_at');


        $bind = array(0, '%'.$search_keyword.'%', '%'.$search_keyword.'%');
        $return[] = $bind;
        $return[] = 'show_page';
        $return[] = '/page';
*/
        return $return;
    }

    /* 画面アクション関数 */

    /**
     * データ初期表示関数
     * コアがページ表示の際に呼び出す関数
     *
     * @method_title 表示
     * @method_desc サイト上に記載した文字や画像を表示できる基本となるプラグインです。
     * @method_detail
     */
    public function index($request, $page_id, $frame_id)
    {
        // データ取得
        $contents = $this->getFrameContents($frame_id);

        // Connect-CMSタグ変換
        $contents = $this->replaceConnectTagAll($contents, $this->page, $this->configs);

        // ハンバーガーメニュー用ページ一覧
        $format = 'layer1';
        $level1_pages = $this->getPages($format);

        // スマホメニュー用タグ生成とコンテンツ変換
        $sp_menu = $this->getSmpMenu($level1_pages, $page_id, $this->page);
        if ($contents && $sp_menu) {
            $contents->content_text = str_replace('<cc value="cc:menu"></cc>', $sp_menu, $contents->content_text);
        }

        // CSRF用トークンの埋め込み指示がある場合
        if ($contents && mb_strpos($contents->content_text, '<cc value="cc:hidden_token"></cc>') !== false) {
            $contents->content_text = str_replace('<cc value="cc:hidden_token"></cc>', $this->getToken('hidden'), $contents->content_text);
        }

        // 表示テンプレートを呼び出す。
        return $this->view('contents', [
            'contents' => $contents,
        ]);
    }

    /**
     * 文字列変換
     * （ConnectCommonTraitから移動してきた）
     */
    private function replaceConnectTagAll($contents, $page, $configs)
    {
        // Connect-CMSタグを値に変換する。
        if (empty($contents)) {
            return $contents;
        }

        $patterns = array();
        $replacements = array();

        // 固定リンク(多言語切り替えで使用)
        $config_language_multi_on = null;
        foreach ($configs as $config) {
            if ($config->name == 'language_multi_on') {
                $config_language_multi_on = $config->value;
            }
        }

        // 言語設定の取得
        $languages = array();
        foreach ($configs as $config) {
            if ($config->category == 'language') {
                $languages[$config->additional1] = $config;
            }
        }
        $page_language = $this->getPageLanguage($page, $languages);

        // 確実に言語設定部分を取り除くために、permanent_link を / で分解して、1番目(/ の次)の内容を取得する。
        $permanent_link_array = explode('/', $page->permanent_link);

        // 多言語on＆現在のページがデフォルト以外の言語の場合、言語指定を取り除く
        if ($config_language_multi_on &&
            $page_language &&
            $permanent_link_array &&
            array_key_exists(1, $permanent_link_array) &&
            $permanent_link_array[1] == $page_language) {
            $patterns[0] = '/{{cc:permanent_link}}/';
            $replacements[0] = trim(mb_substr($page->permanent_link, mb_strlen('/'.$page_language)), '/');
        } else {
            $patterns[0] = '/{{cc:permanent_link}}/';
            $replacements[0] = trim($page->permanent_link, '/');
        }

        // 変換と値の返却
        $contents->content_text = preg_replace($patterns, $replacements, $contents->content_text);
        return $contents;
    }

    /**
     * 固定記事からスマホメニューを出すためのタグ生成
     * （ConnectController から移動してきた）
     */
    private function getSmpMenu($level1_pages, $page_id = null, $page = null)
    {
        $sp_menu  = '' . "\n";
        $sp_menu .= '<nav class="sp_menu">' . "\n";
        $sp_menu .= '<ul>' . "\n";
        foreach ($level1_pages as $level1_page) {
            // ページの表示条件の反映（IP制限など）
            if (!$level1_page['parent']->isView(Auth::user())) {
                continue;
            }

            // コンストラクタで全体作業用として取得したページを使用する想定
            // そのため、ページの基本の表示設定を反映する。
            if ($level1_page['parent']->base_display_flag == 0) {
                continue;
            }

            // ルーツのチェック
            // if ($level1_page['parent']->isAncestorOf($this->page)) {
            if ($level1_page['parent']->isAncestorOf($page)) {
                $active_class = ' class="active"';
            } else {
                $active_class = '';
            }
            //$sp_menu .= '<li class="' . $level1_page['parent']->getLinkUrl('/') . '_menu">' . "\n"; // ページにクラス名を保持する方式へ変更した。
            $sp_menu .= '<li class="' . $level1_page['parent']->getClass() . '">' . "\n";

            // クラス名取得
            $classes = explode(' ', $level1_page['parent']->getClass());

            // ページのクラスに "smp_a_link" がある場合は、a タグでリンクする。
            if (is_array($classes) && in_array('smp_a_link', $classes)) {
                $sp_menu .= '<a' . $active_class . ' href="' . $level1_page['parent']->getUrl() . '"' . $level1_page['parent']->getUrlTargetTag() . '>';
                $sp_menu .= $level1_page['parent']->page_name;
                $sp_menu .= '</a>' . "\n";
            } else {
                $sp_menu .= '<p' . $active_class . '>';
                $sp_menu .= $level1_page['parent']->page_name;
                $sp_menu .= '</p>' . "\n";
            }

            if (array_key_exists('child', $level1_page)) {
                $sp_menu .= '<ul' . $active_class . '>' . "\n";
                foreach ($level1_page['child'] as $child) {
                    // ページの表示条件の反映（IP制限など）
                    if (!$child->isView(Auth::user())) {
                        continue;
                    }

                    if ($child->base_display_flag == 0) {
                        continue;
                    } else {
                        $child_depth = intval($child->depth) - 1;
                        $child_margin_left = ($child_depth > 0) ? $child_depth * 20 : 0;
                        $sp_menu .= '<li><a href="' . $child->getUrl() . '"' . $child->getUrlTargetTag() . ' style="margin-left:' . $child_margin_left . 'px"' . '>';
                        if ($page_id == $child->id) {
                            $sp_menu .= '<u>' . $child->page_name . '</u>';
                        } else {
                            $sp_menu .= $child->page_name;
                        }
                        $sp_menu .= '</a></li>' . "\n";
                    }
                }
                $sp_menu .= '</ul>' . "\n";
            }
            $sp_menu .= '</li>' . "\n";
        }
        $sp_menu .= '</ul>' . "\n";
        $sp_menu .= '</nav>' . "\n";
        return $sp_menu;
    }

    /**
     * データ編集用表示関数
     * コアが編集画面表示の際に呼び出す関数
     *
     * @method_title 編集
     * @method_desc 表示する内容を編集できます。
     * @method_detail WYSIWYG の機能を使って、ワープロのように文章を編集できます。また、画像やPDF の挿入もできます。
     */
    public function edit($request, $page_id, $frame_id, $id = null)
    {
        // データ取得
        $contents = $this->getFrameContents($frame_id);

        // 引数idを使ってないため、追加で権限チェック
        $view_error = $this->can('posts.update', $contents, $this->frame->plugin_name, $this->buckets, $this->frame);
        if ($view_error) {
            return $view_error;
        }

        // データがない場合
        $contents = $contents ?? new Contents();

        return $this->view('contents_edit', [
            'contents' => $contents,
        ]);
    }

    /**
     *  データ詳細表示関数
     *  コアがデータ削除の確認用に呼び出す関数
     *
     * @method_title 削除
     * @method_desc 固定記事を削除できます。
     * @method_detail フレームも同時に削除することで、画面上の枠も消えます。
     */
    public function show($request, $page_id, $frame_id, $id = null)
    {
        // 権限チェック
        // 固定記事プラグインの特別処理。削除のための表示であり、フレーム画面のため、個別に権限チェックする。
        // if ($this->can('frames.delete')) {
        //     return $this->view_error(403);
        // }
        $view_error = $this->can('frames.delete');
        if ($view_error) {
            return $view_error;
        }

        // データ取得
        $contents = $this->getFrameContents($frame_id);

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
            'contents_show', [
            'contents' => $contents,
            ]
        );
    }

    /**
     * データ新規登録関数
     */
    public function store($request, $page_id = null, $frame_id = null, $id = null, $status = 0)
    {
        // 項目のエラーチェック
        $validator = $this->makeValidator($request);

        // エラーがあった場合は入力画面に戻る。
        if ($validator->fails()) {
            return back()->withErrors($validator)->withInput();
        }

        // バケツがまだ登録されていなかったら登録する。
        if (empty($this->buckets)) {
            $bucket = Buckets::create([
                'bucket_name' => $request->bucket_name ?? '無題',
                'plugin_name' => 'contents'
            ]);
            $bucket_id = $bucket->id;
        } else {
            $bucket_id = $this->buckets['id'];
        }

        // コンテンツデータの登録
        $contents = new Contents;
        $contents->created_id   = Auth::user()->id;
        $contents->bucket_id    = $bucket_id;
        $contents->content_text = $this->clean($request->contents);

        // 一時保存(status が 1 になる。)
        if ($status == 1) {
            $contents->status = 1;
        } elseif ($this->isApproval()) {
            // 承認フラグ(要承認の場合はstatus が 2 になる。)
            $contents->status = 2;
        } else {
            $contents->status = 0;
        }

        $contents->save();

        // FrameのバケツIDの更新
        Frame::where('id', $frame_id)
            ->update(['bucket_id' => $bucket_id]);

        // 登録後はリダイレクトして表示用の初期処理を呼ぶ。
        // return;
        return collect(['redirect_path' => url($this->page->permanent_link)]);
    }

    /**
     * データ更新（確定）関数
     */
    public function update($request, $page_id = null, $frame_id = null, $id = null)
    {
        // 項目のエラーチェック
        $validator = $this->makeValidator($request);

        // エラーがあった場合は入力画面に戻る。
        if ($validator->fails()) {
            return back()->withErrors($validator)->withInput();
        }

        // 新しいレコードの登録（旧レコードのコピー＆内容の入れ替え）
        $oldrow = Contents::find($id);

        // 新しいレコードの登録（旧レコードのコピー＆内容の入れ替え）
        $newrow = $oldrow->replicate();
        $newrow->content_text = $this->clean($request->contents);

        // 承認フラグ(要承認の場合はstatus が2 になる。)
        if ($this->isApproval()) {
            $newrow->status = 2;
        } else {
            $newrow->status = 0;
        }

        // 旧レコードのstatus 更新(Activeなもの(status:0)は、status:9 に更新。他はそのまま。)ただし、承認待ちレコード作成時は対象外
        if ($newrow->status != 2) {
            Contents::where('bucket_id', $oldrow->bucket_id)->where('status', 0)->update(['status' => 9]);
        }
        //Contents::where('id', $oldrow->id)->update(['status' => 9]);

        // 変更のデータ保存
        $newrow->save();

        // バケツのデータ名保存
        $buckets = Buckets::find($oldrow->bucket_id);
        $buckets->bucket_name = $request->bucket_name ?? '無題';
        $buckets->save();

        // 登録後はリダイレクトして表示用の初期処理を呼ぶ。
        // return;
        return collect(['redirect_path' => url($this->page->permanent_link)]);
    }

    /**
     * データ一時保存関数
     */
    public function temporarysave($request, $page_id = null, $frame_id = null, $id = null)
    {
        // 新規で一時保存しようとしたときは id、レコードがまだない。
        if (empty($id)) {
            $status = 1;
            return $this->store($request, $page_id, $frame_id, $id, $status);
        } else {
            // 項目のエラーチェック
            $validator = $this->makeValidator($request);

            // エラーがあった場合は入力画面に戻る。
            if ($validator->fails()) {
                return back()->withErrors($validator)->withInput();
            }

            // 旧データ取得
            $oldrow = Contents::find($id);

            // 旧レコードが表示でなければ、履歴に更新（表示を履歴に更新すると、画面に表示されなくなる）
// 過去のステータスも残す方式にする。
//            if ($oldrow->status != 0) {
//                Contents::where('id', $id)->update(['status' => 9]);
//            }

            // 新しいレコードの登録（旧レコードのコピー＆内容の入れ替え）
            $newrow = $oldrow->replicate();
            $newrow->content_text = $this->clean($request->contents);
            $newrow->status = 1; //（一時保存）
            $newrow->save();

            // バケツのデータ名保存
            $buckets = Buckets::find($oldrow->bucket_id);
            $buckets->bucket_name = $request->bucket_name ?? '無題';
            $buckets->save();

            // 登録後はリダイレクトして表示用の初期処理を呼ぶ。
            return collect(['redirect_path' => url($this->page->permanent_link)]);
        }
        // return;
    }

    /**
     * 入力チェック
     */
    private function makeValidator($request)
    {
        // 項目のエラーチェック
        $validator = Validator::make($request->all(), [
            'contents'    => ['nullable', new CustomValiWysiwygMax()],
            'bucket_name' => ['nullable', 'max:255'],
        ]);
        $validator->setAttributeNames([
            'contents'    => '内容',
            'bucket_name' => 'データ名',
        ]);

        return $validator;
    }

    /**
     * 承認
     */
    public function approval($request, $page_id = null, $frame_id = null, $id = null)
    {
        // 新しいレコードの登録（旧レコードのコピー＆内容の入れ替え）
        $oldrow = Contents::find($id);

        // 旧レコードのstatus 更新(Activeなもの(status:0)は、status:9 に更新。他はそのまま。)
        Contents::where('bucket_id', $oldrow->bucket_id)->where('status', 0)->update(['status' => 9]);

        // 新しいレコードの登録（旧レコードのコピー＆内容の入れ替え）
        $newrow = $oldrow->replicate();
        $newrow->status = 0;
        $newrow->save();

        return;
    }

    /**
     * データ削除関数
     */
    public function delete($request, $page_id = null, $frame_id = null, $id = null)
    {
        // id がある場合、コンテンツを削除
        if ($id) {
            // Contents データ
            $content = Contents::where('id', $id)->first();

            // フレームも同時に削除するがチェックされていたらフレームを削除する。
            if ($request->frame_delete_flag == "1") {
                Frame::destroy($frame_id);
            }

            // 論理削除のため、コンテンツデータを status:9 に変更する。バケツデータは削除しない。
// 過去のステータスも残す方式にする。
//            Contents::where('id', $id)->update(['status' => 9]);

            // 削除ユーザの更新
            Contents::where('bucket_id', $content->bucket_id)->update(['deleted_id' => Auth::user()->id, 'deleted_name' => Auth::user()->name]);

            // 同じbucket_id のものを削除
            Contents::where('bucket_id', $content->bucket_id)->delete();
        }
        return;
    }

    /**
     * データ選択表示関数
     *
     * @method_title 選択
     * @method_desc このフレームに表示する固定記事を選択できます。
     * @method_detail
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
        // * status は 0 のもののみ表示（データリスト表示はそれで良いと思う）
        // * 現在のものを最初に表示する。orderByRaw('buckets.id = ' . $this->buckets->id . ' desc') ※ desc 指定が必要だった。
        $buckets_query = Buckets::select('buckets.*', 'contents.id as contents_id', 'contents.content_text', 'contents.updated_at as contents_updated_at', 'frames.id as frames_id', 'frames.frame_title', 'pages.page_name')
                           ->join('contents', function ($join) {
                               $join->on('contents.bucket_id', '=', 'buckets.id');
                               $join->where('contents.status', '=', 0);
                               $join->whereNull('contents.deleted_at');
                           })
                           ->leftJoin('frames', 'buckets.id', '=', 'frames.bucket_id')
                           ->leftJoin('pages', 'pages.id', '=', 'frames.page_id')
                           ->where('buckets.plugin_name', 'contents');

        // buckets を作っていない状態で、設定の表示コンテンツ選択を開くこともあるので、バケツがあるかの判定
        if (!empty($this->buckets)) {
            // buckets がある場合は、該当buckets を一覧の最初に持ってくる。
            $buckets_query->orderByRaw('buckets.id = ' . $this->buckets->id . ' desc');
        }

        $buckets_list = $buckets_query->orderBy($request_order_by[0], $request_order_by[1])
            ->paginate(10, ["*"], "frame_{$frame_id}_page");

        return $this->view('contents_list_buckets', [
            'buckets_list'      => $buckets_list,
            'order_link'        => $order_link,
            'request_order_str' => implode('|', $request_order_by)
        ]);
    }

    /**
     * データ紐づけ変更関数
     */
    public function changeBuckets($request, $page_id = null, $frame_id = null, $id = null)
    {
        // FrameのバケツIDの更新
        Frame::where('id', $frame_id)
               ->update(['bucket_id' => $request->select_bucket]);
        return;
    }
}
