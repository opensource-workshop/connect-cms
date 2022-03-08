<?php

namespace App\Http\Controllers\Core;

use Illuminate\Http\Request;
use Illuminate\Routing\Router;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\View;

use DB;
use File;

use App\Http\Controllers\Core\ConnectController;

use App\Models\Common\Frame;
use App\Models\Common\Page;
use App\Models\Core\Configs;
// use App\Models\Core\Plugins;

use App\Traits\ConnectCommonTrait;

/**
 * 画面の基本処理
 *
 * ルーティング処理から呼び出されるもの
 *
 * @author 永原　篤 <nagahara@opensource-workshop.jp>
 * @copyright OpenSource-WorkShop Co.,Ltd. All Rights Reserved
 * @category コア
 * @package Controller
 *
 *                                                                                      | 旧                                                                                                     | 現
 * @method __invoke() 基本のアクション（コアの画面処理や各プラグインの処理はここから呼び出す。管理画面トップもここにくる） | page,frame,setAppLocale(),page->isRequestPassword(),checkPageForbidden(403)  | page,frame,setAppLocale(),page->isRequestPassword(),checkPageForbidden(403),403ならaction無効化
 * @method changeLanguage() 言語切り替えアクション                                        | page,frame                                                                                             | 何もつけない
 * @method invokePost() 一般プラグインの表示系・更新系アクション                            | page,frame,setAppLocale(),page->isRequestPassword(),checkPageForbidden(403)                            | page,frame,setAppLocale(),page->isRequestPassword(),checkPageForbidden(403),403ならaction無効化
 * @method invokeGetJson() 一般プラグインのJSONレスポンスアクション                         | page,frame,setAppLocale()                                                                             | page,frame,setAppLocale(),page->isRequestPassword(),checkPageForbidden(403),403ならaction無効化  ※ page_id, frame_idを利用しているためチェック。
 * @method invokePostRedirect() 一般プラグインの更新系アクション（リダイレクトする場合）     | page,frame,setAppLocale(),page->isRequestPassword(),checkPageForbidden(403),403ならaction無効化         | page,frame,setAppLocale(),page->isRequestPassword(),checkPageForbidden(403),403ならaction無効化  ※ 同左
 * @method invokePostDownload() 一般プラグインのダウンロード系アクション                    | page,frame                                                                                             | page,frame,setAppLocale(),page->isRequestPassword(),checkPageForbidden(403),403ならaction無効化  ※ page_id, frame_idを利用しているためチェック。
 * ※ setAppLocale() - page に依存
 * ※ page->isRequestPassword() - session見てチェックしているため、一般プラグインのダウンロード系アクション（invokePostDownload()）でチェックしても、その処理にくるまでに観覧パスワード入力してるだろうから、問題ないだろう。
 *
 * @see routes\web.php このルーティングからDefaultController呼ばれる
 */
class DefaultController extends ConnectController
{
    use ConnectCommonTrait;

    /**
     * コンストラクタ
     */
    public function __construct()
    {
        // \Log::debug('[' . __METHOD__ . '] ' . __FILE__ . ' (line ' . __LINE__ . ')');

        // DefaultController::changeLanguage を除外（except）
        $this->middleware('connect.page')->except(['changeLanguage']);
        $this->middleware('connect.page.password')->except(['changeLanguage']);
        $this->middleware('connect.frame')->except(['changeLanguage']);
    }

    /**
     * 画面表示用にページやフレームなど呼び出し
     *
     * @param String $plugin_name
     * @return view
     */
    public function __invoke(Request $request)
    {
        // \Log::debug('[' . __METHOD__ . '] ' . __FILE__ . ' (line ' . __LINE__ . ')');

        // app\Http\Middleware\ConnectPage.php でセットした値
        $page = $request->attributes->get('page');
        $pages = $request->attributes->get('pages');
        $page_tree = $request->attributes->get('page_tree');

        // アプリのロケールを変更
        $this->setAppLocale($page);

        // move: app\Http\Middleware\ConnectPage.php に処理移動
        // // パスワード付きページのチェック（パスワードを要求するか確認）
        // if ($this->page && $this->page->isRequestPassword($request, $this->page_tree)) {
        //     // 認証されていなくてパスワードを要求する場合、パスワード要求画面を表示
        //     return redirect("/password/input/" . $this->page->id);
        // }
        //
        // // 現在のページが参照可能か判定して、NG なら403 ページを振り向ける。
        // $this->checkPageForbidden();

        // 特別なPath が指定された場合は処理を行い、return する。
        if ($this->isSpecialPath($request->path())) {
            return $this->callSpecialPath($request->path(), $request);
        }

        // フレーム一覧取得（メインエリアのみ）
        // $frames = $this->getFramesMain($this->page->id);
        $frames = $this->getFramesMain($page->id);

        // プラグインのインスタンス取得（メインエリアのみ）
        $plugin_instances = $this->createInstanceMain($frames, $page, $pages);

        // レイアウト取得
        $layouts_info = $this->getLayoutsInfo($page, $page_tree);

        // テーマ取得
        $themes = $this->getThemes($request, $page_tree);

        // プラグインのインスタンス生成（メインエリア以外の共通エリア）
        $plugin_instances = $this->createInstanceCommonArea($layouts_info, $plugin_instances, $page, $pages);

        // Page データ
        //$pages = Page::defaultOrder()->get();
        //Log::debug(json_decode($pages));
        // ConnectController へ移動

        // フレームで使用するテンプレート・リスト、プラグインのフレームメニュー
        $action_core_frame = $this->getActionCoreFrame($request);

        // プラグイン一覧の取得
        $plugins = $this->getPlugins();

        // delete: 管理画面・一般画面全てのviewで参照できる全configsは、$cc_configsとしてセットしたため、ここは廃止。$cc_configsのセット場所は app\Http\Middleware\ConnectInit::handle().
        // Config
        // $configs_array = $this->getConfigs('array');

        // view の場所を変更するテスト
        //$plugin_instances = ['contents' => new $class_name("User", "contents")];

        // メインページを呼び出し(coreのinvokeコントローラでは、スーパークラスのviewを使用)
        // 各フレーム内容の表示はメインページから行う。
        return $this->view('core.cms', [
            'action'            => $request->action,    // routes\web.php で Route::get or post( '{all}', 'Core\DefaultController')->where('all', '.*') で呼ばれるため、$request->action は基本 null の想定
            'frame_id'          => $request->frame_id,  // 同上. $request->frame_id は基本 null の想定
            // 'page'              => $this->page,
            'page'              => $page,
            'frames'            => $frames,
            // 'pages'             => $this->pages,
            'pages'             => $pages,
            'plugin_instances'  => $plugin_instances,
            'layouts_info'      => $layouts_info,
            'themes'            => $themes,
            'action_core_frame' => $action_core_frame,
            'plugins'           => $plugins,
            // 'configs_array'     => $configs_array,
        ]);
    }

    /**
     * アプリのロケールを変更
     * コンストラクタではセッションの保持ができなかったので、各ルートから呼び出し
     * （ConnectController から移動してきた）
     */
    private function setAppLocale($page)
    {
        // 多言語設定されていたら、ロケールに言語定数を設定
        // if ($this->isLanguageMultiOn() && $this->page) {
        if ($this->isLanguageMultiOn() && $page) {
            // $view_language = $this->getPageLanguage($this->page, $this->getLanguages());
            $view_language = $this->getPageLanguage($page, Configs::getLanguages());
            if (empty($view_language)) {
                $view_language = 'ja';
            }
            if (array_key_exists($view_language, Config::get('languages'))) {
                // URLから取得した言語定数が言語定義にあれば、アプリのロケールをURL値で上書き
                App::setLocale($view_language);
            } else {
                // なければデフォルト値でロケールを上書き
                App::setLocale(Config::get('app.fallback_locale'));
            }
        }
    }

    /**
     * 多言語設定がonか
     * （ConnectController から移動してきた）
     */
    private function isLanguageMultiOn()
    {
        // foreach ($this->getConfigs() as $config) {
        foreach (Configs::getSharedConfigs() as $config) {
            if ($config->name == 'language_multi_on') {
                if ($config->value == '1') {
                    return true;
                } else {
                    return false;
                }
            }
        }
        return false;
    }

    /**
     * ページのレイアウト情報
     */
    private function getLayoutsInfo($page, $page_tree)
    {
        // if (empty($this->page)) {
        if (empty($page)) {
            //return null;
            abort(404);
        }
        // if (empty($this->page->id)) {
        if (empty($page->id)) {
            //return null;
            abort(404);
        }

        // ページの系統取得
        // $page_tree = $this->getPageTree($this->page->id);
        $page_tree = $this->getPageTree($page, $page_tree);

        // ページのレイアウト取得
        $layout_array = explode('|', $this->getLayout($page, $page_tree));

        // ページのレイアウトがおかしな値の場合は、初期値として全カラムを設定しておく。
        if (count($layout_array) != 4) {
            $layout_array = array(1,1,1,1);
        }

        // 現ページの表示エリアの有無と幅の設定
        $layouts_info = array();
        $layouts_info[0]['exists'] = $layout_array[0];
        $layouts_info[0]['col'] = 'col-lg-12';

        $layouts_info[1]['exists'] = $layout_array[1];
        $layouts_info[1]['col'] = ($layout_array[1] == '1' ? 'col-lg-3' : '' );

        $layouts_info[2]['exists'] = '1';
        if (!$layout_array[1] && !$layout_array[2]) {
            $layouts_info[2]['col'] = 'col-lg-12';
        } elseif ($layout_array[1] && !$layout_array[2]) {
            $layouts_info[2]['col'] = 'col-lg-9';
        } elseif (!$layout_array[1] && $layout_array[2]) {
            $layouts_info[2]['col'] = 'col-lg-9';
        } elseif ($layout_array[1] && $layout_array[2]) {
            $layouts_info[2]['col'] = 'col-lg-6';
        }

        $layouts_info[3]['exists'] = $layout_array[2];
        $layouts_info[3]['col'] = ($layout_array[2] == '1' ? 'col-lg-3' : '' );

        $layouts_info[4]['exists'] = $layout_array[3];
        $layouts_info[4]['col'] = 'col-lg-12';

        // 共通エリアのフレーム取得

        // フレームを取得するページID
        $page_ins = array();
        foreach ($page_tree as $tmp_page) {
            $page_ins[] = $tmp_page->id;
        }

        // メインエリア以外のフレームの取得
        // --- クロージャでインスタンス変数の参照が構文エラーになったのでローカル変数で持つ。
        // $this_page_id = $this->page->id;
        $this_page_id = $page->id;

        $frames = Frame::where('area_id', '!=', 2)
                       ->select('frames.*', 'frames.id as frame_id', 'plugins.plugin_name_full')
                       ->join('pages', 'pages.id', '=', 'frames.page_id')
                       ->leftJoin('plugins', 'plugins.plugin_name', '=', 'frames.plugin_name')
                       ->whereIn('page_id', $page_ins)
                       // このページにのみ表示する。の処理用クロージャ。
                       ->where(function ($query) use ($this_page_id) {
                           $query->Where('page_only', 0)
                               ->orWhere(function ($query2) use ($this_page_id) {
                                   $query2->Where('page_only', 1)
                                          ->Where('page_id', $this_page_id);
                               })
                               ->orWhere('page_only', 2);
                               // 管理者ではフレームが見えないと設定できないので、以下の条件は付けない
                               //->orWhere(function($query3) use($this_page_id) {
                               //    $query3->Where('page_only', 2)
                               //           ->Where('page_id', '<>', $this_page_id);
                               //});
                       })
                       ->orderBy('area_id', 'asc')
                       ->orderBy('pages._lft', 'desc')
                       ->orderBy('display_sequence', 'asc')
                       ->get();

        // 共通エリアの継承処理
        foreach ($frames as $frame) {
            // すでに子の設定で共通エリアにフレームがある場合は、対象外。
            if (array_key_exists($frame['area_id'], $layouts_info) && array_key_exists('frames', $layouts_info[$frame['area_id']]) && !empty($layouts_info[$frame['area_id']]['frames'])) {
                // 同じページの複数フレームは使用する。
                if ($layouts_info[$frame['area_id']]['frames'][0]['page_id'] == $frame['page_id']) {
                    $layouts_info[$frame['area_id']]['frames'][] = $frame;
                }
            } else {
                // 子から遡って最初に出てきた共通エリアのフレーム
                $layouts_info[$frame['area_id']]['frames'][] = $frame;
            }
        }
        //print_r($layouts_info);

        return $layouts_info;
    }

    /**
     * ページの系統取得
     */
    private function getPageTree($page, $page_tree)
    {
        // 自分のページから親を遡って取得
        // $page_tree = $this->getAncestorsAndSelf($page_id);

        // トップページを取得
        // $top_page = Page::orderBy('_lft', 'asc')->first();
        $top_page = Page::getTopPage();

        // 多言語設定の場合、多言語のトップページをツリーのrootに入れる。
        if ($this->isLanguageMultiOn()) {
            // $global_top_page = $this->getTopPage($this->page, $this->getLanguages());
            $global_top_page = $this->getTopPage($page, Configs::getLanguages());
            $page_tree->push($global_top_page);
        }

        // 自分のページツリーの最後（root）にトップが入っていなければ、トップページをページツリーの最後に追加する
        if ($page_tree[count($page_tree)-1]->id != $top_page->id) {
            $page_tree->push($top_page);
        }
        return $page_tree;
    }

    /**
     * 現在の言語設定のトップページ
     * （ConnectCommonTrait から移動してきた）
     */
    private function getTopPage($page, $languages = null)
    {
        $page_language = $this->getPageLanguage($page, $languages);

        // 言語トップのページ確認
        return Page::where('permanent_link', '/'.$page_language)->first();
    }

    /**
     * ページのレイアウト取得
     */
    private function getLayout($page, $page_tree)
    {
        // レイアウトの初期値
        $layout_default = '1|1|0|1';

        // if (empty($this->page)) {
        if (empty($page)) {
            return $layout_default;
        }

        // レイアウト
        $layout = null;

        foreach ($page_tree as $tmp_page) {
            // レイアウト
            if (empty($layout) && $tmp_page->layout) {
                $layout = $tmp_page->layout;
            }
        }
        // 親も含めて空の場合は、初期値を返却
        if (empty($layout)) {
            $layout = $layout_default;
        }
        return $layout;
    }

    /**
     * テーマ取得
     * 配列で返却['css' => 'テーマ名', 'js' => 'テーマ名']
     * 値がなければキーのみで値は空
     * （ConnectController から移動してきた）
     */
    private function getThemes($request, $page_tree)
    {
        // 戻り値
        $return_array = array(
            'css' => '',
            'js' => '',
            'css_additional' => '',
            'js_additional' => ''
        );

        // セッションにテーマの選択がある場合（テーマ・チェンジャーで選択時の動き）
        if ($request && $request->session()->get('session_theme')) {
            return  $this->checkAsset($request->session()->get('session_theme'), $return_array);
        }

        // ページ固有の設定がある場合
        $theme = $this->getPagesColum('theme', $page_tree);
        if ($theme) {
            // CSS、JS をチェックして配列にして返却
            return  $this->checkAsset($theme, $return_array);
        }
        // テーマが設定されていない場合は一般設定の取得
        $configs = Configs::where('name', 'base_theme')->first();

        // CSS、JS をチェックして配列にして返却
        return  $this->checkAsset($configs->value, $return_array);
    }

    /**
     * ・指定された基本テーマにCSS、JS があるか確認
     * ・追加テーマにCSS、JS があれば設定
     */
    private function checkAsset($theme, $theme_setting_array)
    {
        // CSS 存在チェック
        if (File::exists(public_path().'/themes/'.$theme.'/themes.css')) {
            $theme_setting_array['css'] = $theme;
        }

        // JS 存在チェック
        if (File::exists(public_path().'/themes/'.$theme.'/themes.js')) {
            $theme_setting_array['js'] = $theme;
        }

        // 追加テーマが設定されていれば設定する
        $configs = Configs::where('name', 'additional_theme')->first();
        if ($configs) {
            // CSS 存在チェック
            if (File::exists(public_path().'/themes/'.$configs->value.'/themes.css')) {
                $theme_setting_array['additional_css'] = $configs->value;
            }

            // JS 存在チェック
            if (File::exists(public_path().'/themes/'.$configs->value.'/themes.js')) {
                $theme_setting_array['additional_js'] = $configs->value;
            }
        }

        return $theme_setting_array;
    }

    /**
     * ページのカラム取得
     * （ConnectController から移動してきた）
     */
    private function getPagesColum($col_name, $page_tree)
    {
        // 自分のページから親を遡って取得
        // $page_tree = $this->getAncestorsAndSelf($this->page->id);
        foreach ($page_tree as $page) {
            if (isset($page[$col_name])) {
                return $page[$col_name];
            }
        }
        return null;
    }

    /**
     * 多言語の切り替え機能
     */
    public function changeLanguage(Request $request, $language_or_1stdir, $link_or_after2nd = null)
    {
        // 言語設定にあるパターンの場合はその言語にリダイレクト。なければ、デフォルト言語とみなす。
        /*
            パターン
            日本語から英語へ(top)  ： href="/language/en"
            日本語から英語へ(blog) ： href="/language/en/blog"
            日本語から英語へ(blog2)： href="/language/en/blog/2"
            英語から日本語へ(top)  ： href="/language"
            英語から日本語へ(blog) ： href="/language/blog"
            英語から日本語へ(blog2)： href="/language/blog/2"
        */

        // 設定されている多言語のリスト取得
        $languages = Configs::where('category', 'language')->orderBy('additional1')->get();

        // 次に表示する言語(null はデフォルト)
        $next_language = null;

        // 次に表示するページの言語を判定
        foreach ($languages as $language) {
            if (trim($language->additional1, '/') == $language_or_1stdir) {
                $next_language = trim($language->additional1, '/');
                break;
            }
        }
        //echo $next_language;

       // permanent_link の編集
        if (empty($next_language) && $link_or_after2nd) {
            // 言語がデフォルト(next_language がnull)＆2nd以降がある場合は、language_or_1stdir はpermanent_link の一部なので、結合する。
            $permanent_link = $language_or_1stdir . '/' . $link_or_after2nd;
        } elseif (empty($next_language)) {
            // 言語がデフォルト(next_language がnull)＆2nd以降がない場合は、language_or_1stdir がディレクトリ。
            $permanent_link = $language_or_1stdir;
        } else {
            $permanent_link = $link_or_after2nd;
        }

       // 遷移するページ
        $next_page = null;

       // 指定されたページがあれば、リダイレクト。
        $next_path = '/';
        if ($next_language) {
            $next_path .= $next_language;
        }
        if ($permanent_link) {
            if (mb_substr($next_path, -1) != '/') {
                $next_path .= '/';
            }
            $next_path .= $permanent_link;
        }
        $next_page = Page::where('permanent_link', $next_path)->first();

        if (!empty($next_page)) {
            return redirect($next_page->permanent_link);
        }

       // 指定された言語のルートあれば、リダイレクト。
        $next_page = Page::where('permanent_link', '/'.$next_language)->first();
        if (!empty($next_page)) {
            return redirect($next_page->permanent_link);
        }

       // トップに戻る
        return redirect('/');
    }

    /**
     * フレームで使用するテンプレート・リスト、プラグインのフレームメニュー
     */
    private function getActionCoreFrame($request)
    {
        // フレームで使用するテンプレート・リスト、プラグインのフレームメニュー
        // とりあえずココで処理。後でcore 系のアクションを見直してリファクタリングすること。
        $action_core_frame = null;
        $target_frame_templates = array();

        if (!empty($request->action)) {
            // Frame データ
            $action_core_frame = Frame::where('id', $request->frame_id)->first();

            // Frame が存在しない場合はnullを返す。
            if (empty($action_core_frame)) {
                return null;
            }

            $finder = View::getFinder();
            $plugin_view_path = $finder->getPaths()[0].'/plugins/user/' . $action_core_frame->plugin_name;

            // テンプレート・ディレクトリがない場合はオプションプラグインのテンプレートディレクトリを探す
            if (!file_exists($plugin_view_path)) {
                $plugin_view_path = $finder->getPaths()[0].'/plugins_option/user/' . $action_core_frame->plugin_name;
            }

            // テンプレートソート時に順番が書いていない場合用の変数。通常の順番が1からと想定し、空のものは1000から開始
            $tmp_display_sequence = 1000;

            // テンプレートソート用配列
            $sort_array = array();

            // テンプレート・ディレクトリをループ
            $file_list = scandir($plugin_view_path);
            foreach ($file_list as $file) {
                if (in_array($file, array('.', '..'))) {
                    continue;
                }
                // テンプレートディレクトリを探す
                //if (is_dir(($finder->getPaths()[0].'/plugins/user/' . $action_core_frame->plugin_name . '/' . $file))) {
                $template_dir = $finder->getPaths()[0].'/plugins/user/' . $action_core_frame->plugin_name . '/' . $file;
                if (is_dir($template_dir)) {
                    if (File::exists($template_dir."/template.ini")) {
                        // テンプレート設定ファイルがある場合、テンプレート設定ファイルからテンプレート名を探す。設定がなければディレクトリ名をテンプレート名とする。
                        $template_inis = parse_ini_file($template_dir."/template.ini");
                        $template_name = $template_inis['template_name'];
                        if (empty($template_name)) {
                            $template_name = $file;
                        }
                    } else {
                        // テンプレート設定ファイルがない場合、テンプレートディレクトリ名をテンプレート名とする
                        $template_name = $file;
                        $template_inis = array();
                    }

                    // テンプレート配列
                    $target_frame_templates[$template_name] = $file;

                    // テンプレートソート用配列
                    if (array_key_exists('display_sequence', $template_inis)) {
                        $sort_array[] = $template_inis['display_sequence'];
                    } else {
                        $sort_array[] = $tmp_display_sequence;
                        $tmp_display_sequence++;
                    }
                }
            }
            // template.iniでtemplate_name(テンプレート名)が被ると array_multisort(): array sizes are inconsistentエラー出る
            // テンプレート名重複は設置ミスです。
            // Log::debug('[' . __METHOD__ . '] ' . __FILE__ . ' (line ' . __LINE__ . ')');
            // Log::debug(var_export($sort_array, true));
            // Log::debug(var_export($target_frame_templates, true));
            //
            // 編集画面でのテンプレート順番の変更
            array_multisort($sort_array, $target_frame_templates);

            // フレームにテンプレート情報を付与して返却
            $action_core_frame->setTemplates($target_frame_templates);
        }
        return $action_core_frame;
    }

    /**
     *  フレームIDが指定されている場合、plugin_name と合致しているか。
     *
     */
    private function checkFrame2Plugin($plugin_name, $frame_id = null, $frames = null)
    {
        if ($frame_id == null || $frames == null) {
            return true;
        }

        foreach ($frames as $frame) {
            if ($frame->frame_id == $frame_id) {
                if ($frame->plugin_name != $plugin_name) {
                    return false;
                }
            }
        }
        return true;
    }

    /**
     * 画面表示用にページやフレームなど呼び出し
     *
     * @param String $plugin_name
     * @return view
     */
    public function invokePost(Request $request, $plugin_name, $action = null, $page_id = null, $frame_id = null, $id = null)
    {
        // dd($request->frame_id, $frame_id, $router);
        // app\Http\Middleware\ConnectPage.php でセットした値
        $page = $request->attributes->get('page');
        $pages = $request->attributes->get('pages');
        $page_tree = $request->attributes->get('page_tree');

        // アプリのロケールを変更
        $this->setAppLocale($page);

        // move: app\Http\Middleware\ConnectPage.php に処理移動
        // // パスワード付きページのチェック（パスワードを要求するか確認）
        // if ($this->page && $this->page->isRequestPassword($request, $this->page_tree)) {
        //     // 認証されていなくてパスワードを要求する場合、パスワード要求画面を表示
        //     return redirect("/password/input/" . $this->page->id);
        // }
        //
        // // 現在のページが参照可能か判定して、NG なら403 ページを振り向ける。
        // $this->checkPageForbidden();

// 親クラスで取得しているはず
/*
        if (empty($page_id)) {

            // カレントページの取得
            $this->page = $this->current_page();

            // Page_id
            $pages_id = $this->current_page->id;
        }
        else {

            // page_id でPage テーブル検索
            $this->current_page = Page::where('id', '=', $page_id)->first();
            if (empty($this->current_page)) {
                return view('404_not_found');
            }

            // Page_id
            $pages_id = $page_id;
        }
*/

        // フレーム一覧取得
        // $frames = $this->getFramesMain($this->page->id);
        $frames = $this->getFramesMain($page->id);

        // フレームとプラグインの一致をチェック
        if (!$this->checkFrame2Plugin($plugin_name, $frame_id, $frames)) {
            return $this->viewError("403");
        }

        // インスタンス取得（メインエリアのみ）
        $plugin_instances = $this->createInstanceMain($frames, $page, $pages);

        // レイアウト取得
        $layouts_info = $this->getLayoutsInfo($page, $page_tree);

        // テーマ取得
        $themes = $this->getThemes($request, $page_tree);

        // プラグインのインスタンス生成（メインエリア以外の共通エリア）
        $plugin_instances = $this->createInstanceCommonArea($layouts_info, $plugin_instances, $page, $pages);

        // フレームとプラグインの一致をチェック
        foreach ($layouts_info as $area) {
            if (array_key_exists('frames', $area)) {
                if (!$this->checkFrame2Plugin($plugin_name, $frame_id, $area['frames'])) {
                    return $this->viewError("403");
                }
            }
        }

        // Page データ
        //$pages = Page::defaultOrder()->get();
        // ConnectController へ移動

        // view の場所を変更するテスト
        //$plugin_instances = ['contents' => new $class_name("User", "contents")];

        // フレームで使用するテンプレート・リスト、プラグインのフレームメニュー
        $action_core_frame = $this->getActionCoreFrame($request);

        // プラグイン一覧の取得
        $plugins = $this->getPlugins();

        // delete: 管理画面・一般画面全てのviewで参照できる全configsは、$cc_configsとしてセットしたため、ここは廃止。$cc_configsのセット場所は app\Http\Middleware\ConnectInit::handle().
        // Config
        // $configs_array = $this->getConfigs('array');

        // メインページを呼び出し
        // 各フレーム内容の表示はメインページから行う。
//Log::debug($action);
//Log::debug($frame_id);
        return $this->view('core.cms', [
            'action'            => $action,
            'frame_id'          => $frame_id,
            'id'                => $id,
            'page_id'           => $page_id,
            // 'page'              => $this->page,
            'page'              => $page,
            'frames'            => $frames,
            // 'pages'             => $this->pages,
            'pages'             => $pages,
            'plugin_instances'  => $plugin_instances,
            'layouts_info'      => $layouts_info,
            'themes'            => $themes,
            'action_core_frame' => $action_core_frame,
            'plugins'           => $plugins,
            // 'configs_array'     => $configs_array,
        ]);

        return;
    }

    /**
     * JSON-APIリクエスト（GET用）
     *     - フレームに紐づくプラグインをインスタンス化して該当プラグインのinvoke()を呼び出します。
     *     - Connect-CMSのview関連の処理を通さない為、returnにcollection渡してJSON返し等、Laravel的な処理が可能です。
     */
    public function invokeGetJson(Request $request, $plugin_name, $action = null, $page_id = null, $frame_id = null, $id = null)
    {
        // app\Http\Middleware\ConnectPage.php でセットした値
        $page = $request->attributes->get('page');
        $pages = $request->attributes->get('pages');

        // アプリのロケールを変更
        $this->setAppLocale($page);

        // プラグインのインスタンス生成
        $frame = Frame::find($frame_id);
        $class_name = $this->getClassName($frame->plugin_name);
        // $plugin_instance = new $class_name($this->page, $frame, $this->pages);
        $plugin_instance = new $class_name($page, $frame, $pages);

        return $plugin_instance->invoke($plugin_instance, $request, $action, $page_id, $frame_id, $id);
    }

    /**
     * データがない場合にフレームも非表示にする。
     */
    private function setHiddenFrame($frames, $plugin_instances)
    {
        // フレームをループし、対応するインスタンスの件数取得メソッドを呼んで、条件が合致すれば非表示フラグをon
        foreach ($frames as $key => $frame) {
            // データがない場合にフレームも非表示にする。
            if ($frame->none_hidden) {
                // 表示コンテンツの件数取得メソッドの有無確認と呼び出し
                if (method_exists($plugin_instances[$frame->frame_id], 'getContentsCount')) {
                    $count = $plugin_instances[$frame->frame_id]->getContentsCount($frame->frame_id);
                    if ($count == 0) {
                        // フレームオブジェクトの非表示フラグ
                        $frames[$key]->hidden_flag = true;

                        // 以下の方法だと、フレームとインスタンスを消してしまうことも可能。
                        // unset($plugin_instances[$frame->frame_id]);
                        // unset($frames[$key]);
                    }
                }
            }
        }
        return array($frames, $plugin_instances);
    }

    /**
     *  newするクラス名の取得
     */
    private function getClassName($plugin_name)
    {
        // 標準プラグインとして存在するか確認
        $class_name = "App\Plugins\User\\" . ucfirst($plugin_name) . "\\" . ucfirst($plugin_name) . "Plugin";
        if (class_exists($class_name)) {
            return $class_name;
        }
        // オプションプラグインとして存在するか確認
        $class_name = "App\PluginsOption\User\\" . ucfirst($plugin_name) . "\\" . ucfirst($plugin_name) . "Plugin";
        if (class_exists($class_name)) {
            return $class_name;
        }
        return false;
    }

    /**
     *  画面表示用にページやフレームなど呼び出し
     *
     * @param String $plugin_name
     * @return view
     */
    public function invokePostRedirect(Request $request, $plugin_name, $action = null, $page_id = null, $frame_id = null, $id = null)
    {
        // app\Http\Middleware\ConnectPage.php でセットした値
        $page = $request->attributes->get('page');
        $pages = $request->attributes->get('pages');
        $http_status_code = $request->attributes->get('http_status_code');

        // アプリのロケールを変更
        $this->setAppLocale($page);

        // move: app\Http\Middleware\ConnectPage.php に処理移動
        // // パスワード付きページのチェック（パスワードを要求するか確認）
        // if ($this->page && $this->page->isRequestPassword($request, $this->page_tree)) {
        //     // 認証されていなくてパスワードを要求する場合、パスワード要求画面を表示
        //     return redirect("/password/input/" . $this->page->id);
        // }
        //
        // 現在のページが参照可能か判定して、NG なら403 ページを振り向ける。
        // $http_status_code = $this->checkPageForbidden();

        // 403 なら、不正な実行を疑い、action を無効化する。
        if ($http_status_code == 403) {
            $action = null;
        }

        // プラグイン毎に動的にnew する。
        // Todo：プラグインを動的にインスタンス生成すること。

        // フレームのインスタンス生成、プラグインクラスに渡すこと
        $action_frame = null;
        if (!empty($frame_id)) {
            $action_frame = Frame::where('id', $frame_id)->first();
        }

        // 引数のアクションと同じメソッドを呼び出す。
        //$class_name = "App\Plugins\User\\" . ucfirst($plugin_name) . "\\" . ucfirst($plugin_name) . "Plugin";
        $class_name = $this->getClassName($plugin_name);
        // $contentsPlugin = new $class_name($this->page, $action_frame, $this->pages);
        $contentsPlugin = new $class_name($page, $action_frame, $pages);

        // invokeを通して呼び出すことで権限チェックを実施
        $plugin_ret = $contentsPlugin->invoke($contentsPlugin, $request, $action, $page_id, $frame_id, $id);

        // そのまま戻るが指定されている場合はreturn する。
        if ($request->return_mode == 'asis') {
            return $plugin_ret;
        }

        // 戻り値にredirect_path が含まれていたら、そこにredirectする。
        if (is_a($plugin_ret, 'Illuminate\Support\Collection')) {
            if ($plugin_ret->has('redirect_path')) {
                $request->redirect_path = $plugin_ret->get('redirect_path');
            }
        }

        // 2ページ目以降を表示している場合は、表示ページに遷移
        $page_no_link = "";
        // bugfix: 表示ページのパラメータ ?page= は、?frame_{$frame_id}_page= に修正
        //         同じページに複数のページ送りがある時に対応して、既にパラメータ名が変更されていてたため
        $frame_page = "frame_{$frame_id}_page";
        // セッションにあれば使用する。
        if ($request->session()->has('page_no.'.$frame_id)) {
            // $page_no_link = "page=" . $request->session()->get('page_no.'.$frame_id);
            $page_no_link = "{$frame_page}=" . $request->session()->get('page_no.'.$frame_id);
        }
        // リクエストにあれば優先で使用する。
        // if ($request->page) {
        //     $page_no_link = "page=" . $request->page;
        // }
        if ($request->$frame_page) {
            $page_no_link = "{$frame_page}=" . $request->$frame_page;
        }

        // return_frame_action があれば、編集中ページに遷移
        if ($request->return_frame_action) {
            $page = Page::where('id', $page_id)->first();
            $base_url = url('/');
            return redirect($base_url . $page->permanent_link . "?frame_action=" . $request->return_frame_action . "&frame_id=" . $frame_id . ($page_no_link ? "&" . $page_no_link : "") . "#" . $frame_id);
            // return redirect($base_url . $page->permanent_link . "?action=" . $return_frame_action . "&frame_id=" . $frame_id . ($page_no_link ? "&" . $page_no_link : "") . "#" . $frame_id);
        }

        // redirect_path があれば遷移
        if ($request->redirect_path) {
            $redirect_response = redirect($request->redirect_path);
            if ($request->flash_message) {
                // フラッシュメッセージの設定があれば、Laravelのフラッシュデータ保存に連携
                $redirect_response = $redirect_response->with('flash_message', $request->flash_message);
            }
            if ($request->validator) {
                // バリデーターの設定があれば（エラーチェックの結果、NGがあれば）、Laravelのバリデータ機能に連携
                $redirect_response = $redirect_response->withErrors($request->validator->errors());
                // フラッシュデータとして前画面の入力値を保存
                $request->session()->flash('_old_input', $request->except('validator'));
            }

            return $redirect_response;
        }

        // Page データがあれば、そのページに遷移
        if (!empty($page_id)) {
            $page = Page::where('id', $page_id)->first();
            return redirect($page->permanent_link . ($page_no_link ? "?" . $page_no_link : ""));
        }

        return redirect("/" . ($page_no_link ? "?" . $page_no_link : ""));
    }

    /**
     *  ダウンロード処理用にフレーム呼び出し
     *
     * @param String $plugin_name
     * @return view
     */
    public function invokePostDownload(Request $request, $plugin_name, $action = null, $page_id = null, $frame_id = null, $id = null)
    {
        // プラグイン毎に動的にnew する。
        // Todo：プラグインを動的にインスタンス生成すること。

        // フレームのインスタンス生成、プラグインクラスに渡すこと
        $action_frame = null;
        if (!empty($frame_id)) {
            $action_frame = Frame::where('id', $frame_id)->first();
        }

        // app\Http\Middleware\ConnectPage.php でセットした値
        $page = $request->attributes->get('page');
        $pages = $request->attributes->get('pages');

        // 引数のアクションと同じメソッドを呼び出す。
        //$class_name = "App\Plugins\User\\" . ucfirst($plugin_name) . "\\" . ucfirst($plugin_name) . "Plugin";
        $class_name = $this->getClassName($plugin_name);
        // $contentsPlugin = new $class_name($this->page, $action_frame, $this->pages);
        $contentsPlugin = new $class_name($page, $action_frame, $pages);

        // invokeを通して呼び出すことで権限チェックを実施
        return $contentsPlugin->invoke($contentsPlugin, $request, $action, $page_id, $frame_id, $id);
    }

    /**
     * メインエリアのインスタンス生成
     */
    private function createInstanceMain($frames, $page, $pages)
    {
        // プラグインのインスタンス生成（メインエリア）
        $plugin_instances = array();
        foreach ($frames as $frame) {
            //$class_name = "App\Plugins\User\\" . ucfirst($frame->plugin_name) . "\\" . ucfirst($frame->plugin_name) . "Plugin";
            $class_name = $this->getClassName($frame->plugin_name);
            // $plugin_instances[$frame->frame_id] = new $class_name($this->page, $frame, $this->pages);
            $plugin_instances[$frame->frame_id] = new $class_name($page, $frame, $pages);
        }

        // フレームの非表示条件を判定して非表示に合致するならFrame オブジェクトのhidden_flag をtrue に。
        // 引数はオブジェクトなので参照だけど、明示的にしたいので戻り値で受け取る。
        list($frames, $plugin_instances) = $this->setHiddenFrame($frames, $plugin_instances);

        return $plugin_instances;
    }

    /**
     * メインエリアのフレーム取得
     */
    private function getFramesMain($pages_id)
    {
        // フレーム一覧取得（メインエリアのみ）
        $frames = Frame::
            select(
                'frames.*', 'frames.id as frame_id',
                'pages.page_name', 'pages.id as page_id',
                'plugins.plugin_name_full'
            )
            ->join('pages', 'frames.page_id', '=', 'pages.id')
            ->leftJoin('plugins', 'plugins.plugin_name', '=', 'frames.plugin_name')
            ->where('pages.id', $pages_id)
            ->where('frames.area_id', 2)
            ->orderBy('frames.display_sequence')->get();

/*
        $frames = Frame::select('frames.*', 'pages.page_name', 'pages.id as page_id', 'frames.id as id', 'frames.id as frame_id', 'frames.area_id', 'frames.frame_title', 'frames.frame_design',
                            'frames.frame_col', 'frames.plugin_name', 'frames.template', 'frames.plug_name', 'frames.bucket_id', 'frames.browser_width', 'frames.disable_whatsnews',
                            'frames.default_hidden', 'frames.classname', 'frames.none_hidden',
                            'plugins.plugin_name_full')
                    ->join('pages', 'frames.page_id', '=', 'pages.id')
                    ->leftJoin('plugins',  'plugins.plugin_name', '=', 'frames.plugin_name')
                    ->where('pages.id', $pages_id)
                    ->where('frames.area_id', 2)
                    ->orderBy('frames.display_sequence')->get();
*/
/*
        $frames = DB::table('pages')
                    ->select('pages.page_name', 'pages.id as page_id', 'frames.id as id', 'frames.id as frame_id', 'frames.area_id', 'frames.frame_title', 'frames.frame_design',
                            'frames.frame_col', 'frames.plugin_name', 'frames.template', 'frames.plug_name', 'frames.bucket_id', 'frames.browser_width', 'frames.disable_whatsnews',
                            'frames.default_hidden', 'frames.classname',
                            'plugins.plugin_name_full')
                    ->join('frames', 'frames.page_id', '=', 'pages.id')
                    ->leftJoin('plugins',  'plugins.plugin_name', '=', 'frames.plugin_name')
                    ->where('pages.id', $pages_id)
                    ->where('frames.area_id', 2)
                    ->orderBy('frames.display_sequence')->get();
*/
        return $frames;
    }

    /**
     * 共通エリアのプラグインのインスタンス生成
     */
    private function createInstanceCommonArea($layouts_info, $plugin_instances, $page, $pages)
    {
        // 共通エリアのプラグインのインスタンス生成
        foreach ($layouts_info as $area) {
            if (array_key_exists('frames', $area)) {
                foreach ($area['frames'] as $frame) {
                    //$class_name = "App\Plugins\User\\" . ucfirst($frame->plugin_name) . "\\" . ucfirst($frame->plugin_name) . "Plugin";
                    $class_name = $this->getClassName($frame->plugin_name);
                    // $plugin_instances[$frame->frame_id] = new $class_name($this->page, $frame, $this->pages);
                    $plugin_instances[$frame->frame_id] = new $class_name($page, $frame, $pages);
                }

                // フレームの非表示条件を判定して非表示に合致するならFrame オブジェクトのhidden_flag をtrue に。
                // 引数はオブジェクトなので参照だけど、明示的にしたいので戻り値で受け取る。
                list($area['frames'], $plugin_instances) = $this->setHiddenFrame($area['frames'], $plugin_instances);
            }
        }

        return $plugin_instances;
    }
}
