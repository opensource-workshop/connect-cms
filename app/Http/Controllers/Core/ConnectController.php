<?php

namespace App\Http\Controllers\Core;

use Illuminate\Http\Request;
use Illuminate\Routing\Router;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\View;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\App;

use App\Http\Controllers\Controller;

use App\Models\Core\Configs;
use App\Models\Common\Frame;
use App\Models\Common\Page;
use App\Models\Common\PageRole;

use File;
use Storage;

use App\Traits\ConnectCommonTrait;

/**
 * コア用の基底クラス
 *
 * @author 永原　篤 <nagahara@opensource-workshop.jp>
 * @copyright OpenSource-WorkShop Co.,Ltd. All Rights Reserved
 * @category ユーザープラグイン
 * @package Contoroller
 */
class ConnectController extends Controller
{

    use ConnectCommonTrait;

    /**
     *  ページID
     */
    public $page_id = null;

    /**
     *  フレームID
     */
    public $frame_id = null;

    /**
     *  テンプレート情報
     */
    public $target_frame_templates = array();

    /**
     *  カレントページ
     */
    public $page = null;

    /**
     *  ページ一覧
     */
    public $pages = null;

    /**
     *  ページ系統
     */
    public $page_tree = null;

    /**
     *  ページ＆ユーザの役割（Role）
     */
    public $page_roles = null;

    /**
     *  config 設定
     */
    public $configs = null;

    /**
     *  HTTP ステータスコード（null なら200）
     */
    public $http_status_code = null;

    /**
     *  router（コンストラクタで受け取ったものを後に使用するため、保持しておく）
     */
    public $router = null;

    /**
     *  コンストラクタ
     */
    function __construct(Request $request, Router $router)
    {
        // 別メソッドで使用するために保持しておく。
        $this->router = $router;

        // ルートパラメータを取得する
        $allRouteParams = $router->getCurrentRoute()->parameters();

        // ページID
        if ( !empty($allRouteParams) && array_key_exists('page_id', $allRouteParams)) {
            $this->page_id = $allRouteParams['page_id'];
        }

        // フレームID
        if ( !empty($allRouteParams) && array_key_exists('frame_id', $allRouteParams)) {
            $this->frame_id = $allRouteParams['frame_id'];
        }

        // ページID が渡ってきた場合
        if (!empty($this->page_id)) {
             $this->page = Page::where('id', $this->page_id)->first();
        }
        // ページID が渡されなかった場合、URL から取得
        else {
            $this->page = $this->getCurrentPage();
        }

        // ページがあるか判定し、なければ、404 ページを表示する。
        // 対象となる処理は、画面を持つルートの処理とする。
        // 404 ページは、管理画面でエラーページが設定されている場合はそのページを呼ぶ。
        // ただし、多言語対応がONの場合は、現在のページ用に作成された404 ページを呼ぶ。
        // 現在のページ用の404 ページがない場合は、デフォルト言語の404 ページを呼ぶ。
        // 404 ページの設定がない場合は、Connect-CMS デフォルトの404 ページを呼ぶ。
        // 404 ページを表示する場合は、HTTP ステータスコードも404 にしたいため、インスタンス変数でステータスコードを保持しておき、画面出力時に設定する。

        // 必要な結果はインスタンス変数にセットするので、戻り値は受け取らない。
        $this->checkPageNotFound($request, $router);

        // ページがある（管理画面ではページがない）＆IP制限がかかっていない場合は参照OK
        // コンストラクタではAuth による処理がうまくできなかったので、各コントローラのメソッドで必要なところから呼ぶ方式にした。
        //$this->checkPageForbidden($router);

        // ページ一覧データはカレントページの取得後に取得。多言語対応をカレントページで判定しているため。
        if ($this->page && get_class($this->page) == 'App\Models\Common\Page') {
            // Page データ
            $this->pages = Page::defaultOrderWithDepth('flat', $this->page);
        }
        else {
            // Page データ
            $this->pages = Page::defaultOrder()->get();
        }

        // 自分のページから親を遡って取得(getAncestorsAndSelf はシングルトンなのでここで取っておいてもレスポンスは問題ないと判断)
        if ($this->page && get_class($this->page) == 'App\Models\Common\Page') {
            $this->page_tree = $this->getAncestorsAndSelf($this->page->id);
        }
    }

    /**
     *  404 判定
     */
    protected function checkPageNotFound($request, $router)
    {
        // Log::debug($router->current()->getName());
        // Log::debug($router->current()->getActionMethod());

        // 特別なパス（管理画面の最初など）は404 扱いにしない。
        if (array_key_exists($request->path(), config('connect.CC_SPECIAL_PATH_MANAGE'))) {
            // 対象外の処理なので、戻る
            return;
        }

        // 特別なパス（特殊な一般画面など）は404 扱いにしない。
        if (array_key_exists($request->path(), config('connect.CC_SPECIAL_PATH'))) {
            // 対象外の処理なので、戻る
            return;
        }

        // 対象となる処理は、画面を持つルートの処理とする。
        $route_name = $router->current()->getName();
        if ($route_name == 'get_plugin'    ||
            $route_name == 'post_plugin'   ||
            $route_name == 'post_redirect' ||
            $route_name == 'get_redirect'  ||
            $route_name == 'get_all'       ||
            $route_name == 'post_all') {
            // 対象として次へ
        }
        else {
            // 対象外の処理なので、戻る
            return;
        }

        // ページがない場合
        if ($this->page === false || empty($this->page) || empty($this->page->id)) {
            // 404 対象として次へ
        }
        else {
            // ページありとして、戻る
            return;
        }

        // 表示中の他言語を取得（設定がない場合は空が返る）
        $view_language = $this->getPageLanguage4url($this->getLanguages());

        // 設定されている404 ページを探す。
        $configs = $this->getConfigs('array');
        if (array_key_exists('page_permanent_link_404', $configs)) {
            $this->page = $this->getPage($configs['page_permanent_link_404']->value, $view_language);
            if (empty($this->page)) {
                // 再度、デフォルト言語で調査
                $this->page = $this->getPage($configs['page_permanent_link_404']->value);
                if (empty($this->page)) {
                    // Connect-CMS のデフォルト404 ページ
                    abort(404, 'ページがありません。');
                }
                else {
                    $this->page_id = $this->page->id;
                    $this->http_status_code = 404;
                }
            }
            else {
                $this->page_id = $this->page->id;
                $this->http_status_code = 404;
            }
        }
        else {
            abort(404, 'ページがありません。');
        }
        return;
    }

    /**
     *  403 処理
     */
    protected function doForbidden()
    {
        // 表示中の他言語を取得（設定がない場合は空が返る）
        $view_language = $this->getPageLanguage4url($this->getLanguages());

        // 設定されている403 ページを探す。
        $configs = $this->getConfigs('array');
        if (array_key_exists('page_permanent_link_403', $configs)) {
            $this->page = $this->getPage($configs['page_permanent_link_403']->value, $view_language);
            if (empty($this->page)) {
                // 再度、デフォルト言語で調査
                $this->page = $this->getPage($configs['page_permanent_link_403']->value);
                if (empty($this->page)) {
                    // Connect-CMS のデフォルト403 ページ
                    abort(403, 'ページ参照権限がありません。');
                }
                else {
                    $this->page_id = $this->page->id;
                    $this->http_status_code = 403;
                }
            }
            else {
                $this->page_id = $this->page->id;
                $this->http_status_code = 403;
            }
        }
        else {
            abort(403, 'ページ参照権限がありません。');
        }
        return $this->http_status_code;
    }

    /**
     *  403 判定
     *  403 にする場合は、戻り先で処理の無効化を行う可能性もあるので、HTTPステータスコードを返す。
     */
    protected function checkPageForbidden()
    {
        // プラグイン管理者権限以上ならOK
        //$user = Auth::user();//ログインしたユーザーを取得
        //if (isset($user) && $user->can('role_arrangement')) {
        //    return;
        //}

        // 対象となる処理は、画面を持つルートの処理とする。
        $route_name = $this->router->current()->getName();
        if ($route_name == 'get_plugin'    ||
            $route_name == 'post_plugin'   ||
            $route_name == 'post_redirect' ||
            $route_name == 'get_redirect'  ||
            $route_name == 'get_all'       ||
            $route_name == 'post_all') {
            // 対象として次へ
        }
        else {
            // 対象外の処理なので、戻る
            return;
        }

        // 参照できない場合
        $user = Auth::user();  // 権限チェックをpage のisView で行うためにユーザを渡す。
        $check_no_display_flag = true; // ページ直接の参照可否チェックをしたいので、表示フラグは見ない。表示フラグは隠しページ用。

        if ($this->page && get_class($this->page) == 'App\Models\Common\Page') {

            // 自分のページから親を遡って取得
            $page_tree = $this->getAncestorsAndSelf($this->page->id);

            // 自分のページ＋先祖ページのpage_roles を取得
            $ids = null;
            $ids_collection = $this->page_tree->pluck('id');
            if ($ids_collection) {
                $ids = $ids_collection->all();
            }
            $page_roles = $this->getPageRoles($ids);

            // ページをループして表示可否をチェック
            // 継承関係を加味するために is_view 変数を使用。
            $is_view = true;
            foreach($page_tree as $page_obj) {
                $check_page_roles = null;
                if ($page_roles) {
                    $check_page_roles = $page_roles->where('page_id', $page_obj->id);
                }
                $is_view = $page_obj->isView($user, $check_no_display_flag, $is_view, $check_page_roles);
            }
            if (!$is_view) {
                // 403 対象
                return $this->doForbidden();
            }
        }
        // 参照可能
        return;
    }

    /**
     *  アプリのロケールを変更
     *  コンストラクタではセッションの保持ができなかったので、各ルートから呼び出し
     */
    protected function setAppLocale()
    {
        // 多言語設定されていたら、ロケールに言語定数を設定
        if ($this->isLanguageMultiOn() && $this->page) {
            $view_language = $this->getPageLanguage($this->page, $this->getLanguages());
            if (empty($view_language)) {
                $view_language = 'ja';
            }
            if (array_key_exists($view_language, Config::get('languages'))) {
                // URLから取得した言語定数が言語定義にあれば、アプリのロケールをURL値で上書き
                App::setLocale($view_language);
            }
            else {
                // なければデフォルト値でロケールを上書き
                App::setLocale(Config::get('app.fallback_locale'));
            }
        }
    }

    /**
     *  ページのレイアウト情報
     */
    protected function getLayoutsInfo()
    {
        if (empty($this->page)) {
            //return null;
            abort(404);
        }
        if (empty($this->page->id)) {
            //return null;
            abort(404);
        }

        // ページの系統取得
        $page_tree = $this->getPageTree($this->page->id);

        // ページのレイアウト取得
        $layout_array = explode('|',$this->getLayout($page_tree));

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
        }
        else if ($layout_array[1] && !$layout_array[2]) {
            $layouts_info[2]['col'] = 'col-lg-9';
        }
        else if (!$layout_array[1] && $layout_array[2]) {
            $layouts_info[2]['col'] = 'col-lg-9';
        }
        else if ($layout_array[1] && $layout_array[2]) {
            $layouts_info[2]['col'] = 'col-lg-6';
        }

        $layouts_info[3]['exists'] = $layout_array[2];
        $layouts_info[3]['col'] = ($layout_array[2] == '1' ? 'col-lg-3' : '' );

        $layouts_info[4]['exists'] = $layout_array[3];
        $layouts_info[4]['col'] = 'col-lg-12';

        // 共通エリアのフレーム取得

        // フレームを取得するページID
        $page_ins = array();
        foreach($page_tree as $page) {
            $page_ins[] = $page->id;
        }

        // メインエリア以外のフレームの取得
        // --- クロージャでインスタンス変数の参照が構文エラーになったのでローカル変数で持つ。
        $this_page_id = $this->page->id;

        $frames = Frame::where('area_id', '!=', 2)
                       ->select('frames.*', 'frames.id as frame_id', 'plugins.plugin_name_full')
                       ->leftJoin('plugins',  'plugins.plugin_name', '=', 'frames.plugin_name')
                       ->whereIn('page_id', $page_ins)
                       // このページにのみ表示する。の処理用クロージャ。
                       ->where(function($query) use($this_page_id) {
                           $query->Where('page_only', 0)
                               ->orWhere(function($query2) use($this_page_id) {
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
                       ->orderBy('page_id', 'desc')
                       ->orderBy('display_sequence', 'asc')
                       ->get();

        // 共通エリアの継承処理
        foreach($frames as $frame) {

            // すでに子の設定で共通エリアにフレームがある場合は、対象外。
            if (array_key_exists($frame['area_id'], $layouts_info) && array_key_exists('frames', $layouts_info[$frame['area_id']]) && !empty($layouts_info[$frame['area_id']]['frames']) ) {

                // 同じページの複数フレームは使用する。
                if ($layouts_info[$frame['area_id']]['frames'][0]['page_id'] == $frame['page_id']) {
                    $layouts_info[$frame['area_id']]['frames'][] = $frame;
                }
            }
            // 子から遡って最初に出てきた共通エリアのフレーム
            else {
                $layouts_info[$frame['area_id']]['frames'][] = $frame;
            }
        }
        //print_r($layouts_info);

        return $layouts_info;
    }

    /**
     *  Configのarray変換
     */
    protected function changeConfigsArray()
    {
        $return_array = array();

        foreach( $this->configs as $config) {
            $return_array[$config->name] = $config;
        }
        return $return_array;
    }

    /**
     *  Configの取得
     */
    protected function getConfigs($format = null)
    {
        if ($this->configs) {
            if ($format == 'array') {
                return $this->changeConfigsArray();
            }
            return $this->configs;
        }
        // Configの取得
        $this->configs = Configs::orderBy('name', 'asc')->orderBy('additional1', 'asc')->get();

        // format
        if ($format == 'array') {
            return $this->changeConfigsArray();
        }

        return $this->configs;
    }

    /**
     *  言語の取得
     */
    private function getLanguages()
    {
        $configs = $this->getConfigs();
        if (empty($configs)) {
            return null;
        }

        $languages = array();
        foreach($configs as $config) {
            if ($config->category == 'language') {
                $languages[$config->additional1] = $config;
            }
        }
        return $languages;
    }

    /**
     *  多言語設定がonか
     */
    private function isLanguageMultiOn()
    {
        foreach($this->getConfigs() as $config) {
            if ($config->name == 'language_multi_on') {
                if ($config->value == '1') {
                    return true;
                }
                else {
                    return false;
                }
            }
        }
        return false;
    }

    /**
     *  Config の配列形式での取得
     */
    private function getConfigsCategories($category)
    {
        $configs = $this->getConfigs();
        if (empty($configs)) {
            return null;
        }

        $config_array = array();
        foreach($configs as $config) {
            if ($config->category == $category) {
                $config_array[$config->name] = $config->value;
            }
        }
        return $config_array;
    }

    /**
     *  ページの系統取得
     */
    protected function getAncestorsAndSelf($page_id)
    {
        // シングルトン
        if ($this->page_tree) {
            return $this->page_tree;
        }

        // 自分のページから親を遡って取得
        $this->page_tree = Page::reversed()->ancestorsAndSelf($page_id);

        return $this->page_tree;
    }

    /**
     *  ページの系統取得
     */
    private function getPageTree($page_id)
    {
        // 自分のページから親を遡って取得
        $page_tree = $this->getAncestorsAndSelf($page_id);

        // トップページを取得
        $top_page = Page::orderBy('_lft', 'asc')->first();

        // 多言語設定の場合、多言語のトップページをツリーのrootに入れる。
        if ($this->isLanguageMultiOn()) {
            $global_top_page = $this->getTopPage($this->page, $this->getLanguages());
            $page_tree->push($global_top_page);
        }

        // 自分のページツリーの最後（root）にトップが入っていなければ、トップページをページツリーの最後に追加する
        if ($page_tree[count($page_tree)-1]->id != $top_page->id) {
            $page_tree->push($top_page);
        }
        return $page_tree;
    }

    /**
     *  ページのレイアウト取得
     */
    private function getLayout($page_tree)
    {
        // レイアウトの初期値
        $layout_defalt = '1|1|0|1';

        if (empty($this->page)) {
            return $layout_defalt;
        }

        // レイアウト
        $layout = null;

        foreach ( $page_tree as $page ) {

            // レイアウト
            if (empty($layout) && $page->layout) {
                $layout = $page->layout;
            }
        }
        // 親も含めて空の場合は、初期値を返却
        if (empty($layout)) {
            $layout = $layout_defalt;
        }
        return $layout;
    }

    /**
     *  指定されたテーマにCSS、JS があるか確認
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

        return $theme_setting_array;
    }

    /**
     *  テーマ取得
     *  配列で返却['css' => 'テーマ名', 'js' => 'テーマ名']
     *  値がなければキーのみで値は空
     */
    protected function getThemes($request = null)
    {
        // 戻り値
        $return_array = array('css' => '', 'js' => '');

        // セッションにテーマの選択がある場合（テーマ・チェンジャーで選択時の動き）
        if ($request && $request->session()->get('session_theme')) {
            return  $this->checkAsset($request->session()->get('session_theme'), $return_array);
        }

        // ページ固有の設定がある場合
        $theme = $this->getPagesColum('theme');
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
     *  ページのカラム取得
     */
    private function getPagesColum($col_name)
    {
        // 自分のページから親を遡って取得
        $page_tree = $this->getAncestorsAndSelf($this->page->id);
        foreach($page_tree as $page){
            if(isset($page[$col_name])) {
                return $page[$col_name];
            }
        }
        return null;
    }

    /**
     *  表示しているページのオブジェクトを取得
     */
    private function getCurrentPage()
    {
        // ページデータ取得のため、URL から現在のURL パスを判定する。
        $current_url = url()->current();
        $base_url = url('/');
        $current_permanent_link = str_replace( $base_url, '', $current_url);

        // URLデコード
        $current_permanent_link = urldecode($current_permanent_link);

        // トップページの判定
        if (empty($current_permanent_link)) {
            $current_permanent_link = "/";
        }

        // URL パスでPage テーブル検索
        $page = Page::where('permanent_link', '=', $current_permanent_link)->first();
        if (empty($page)) {
            return false;
        }
        return $page;
    }

    /**
     *  ページに関する情報取得
     */
    private function getPageList()
    {
        // ページ一覧の取得
        return Page::defaultOrderWithDepth('flat', $this->page);
    }

    /**
     *  表示しているページに関する情報取得
     */
    private function getPageConfig($page_id)
    {
    }

    /**
     *  画面表示
     *  ページ共通で必要な値をココで取得、viewに渡す。
     */
    protected function view($blade_path, $args)
    {
        // 一般設定の取得
        $configs = Configs::where('category', 'general')->orWhere('category', 'user_register')->get();
        $configs_array = array();
        foreach ($configs as $config) {
            $configs_array[$config['name']] = $config['value'];
        }
        $args["configs"] = $configs_array;

        // ハンバーガーメニューで使用するページの一覧
        $args["page_list"] = $this->getPageList();

        if ($this->http_status_code) {
            return response()->view($blade_path, $args, $this->http_status_code);
        }

        return view($blade_path, $args);
    }

    /**
     *  ログ出力
     */
//    public function putLog($e)
//    {
//        Log::error($e);
//    }
}
