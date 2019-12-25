<?php

namespace App\Http\Controllers\Core;

use Illuminate\Http\Request;
use Illuminate\Routing\Router;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\View;

use App\Http\Controllers\Controller;

use App\Models\Core\Configs;
use App\Models\Common\Frame;
use App\Models\Common\Page;

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
     *  config 設定
     */
    public $configs = null;

    /**
     *  コンストラクタ
     */
    function __construct(Request $request, Router $router)
    {
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

//        // Page データ
//        $this->pages = Page::defaultOrder()->get();

        // ページID が渡ってきた場合
        if (!empty($this->page_id)) {
             $this->page = Page::where('id', $this->page_id)->first();
//Log::debug($this->page);
        }
        // ページID が渡されなかった場合、URL から取得
        else {
            $this->page = $this->getCurrentPage();
//Log::debug($this->page);
        }

        // 404 の場合、設定画面の404 ページを探す。
        if (!$this->isManagePage($request) && (empty($this->page->id) || empty($this->page))) {
            $configs = $this->getConfigs('array');
            if (!empty($configs['page_permanent_link_404'])) {
                $this->page = $this->getPage($configs['page_permanent_link_404']->value);
                if (empty($this->page)) {
                    abort(404, 'ページがありません。');
                }
                else {
                    $this->page_id = $this->page->id;
                }
            }
            else {
                abort(404, 'ページがありません。');
            }
        }

        // ページがある（管理画面ではページがない）＆IP制限がかかっていない場合は参照OK
        $check_ip_only = true;
        if ($this->page && get_class($this->page) == 'App\Models\Common\Page' && !$this->page->isView($check_ip_only)) {
            //abort(403, '参照できないページです。');

            $configs = $this->getConfigs('array');
            if (!empty($configs['page_permanent_link_403'])) {
                $this->page = $this->getPage($configs['page_permanent_link_403']->value);
                if (!empty($this->page)) {
                    $this->page_id = $this->page->id;
                }
                else {
                    abort(403, '参照できないページです。');
                }
            }
            else {
                abort(403, '参照できないページです。');
            }
        }

        // ページ一覧データはカレントページの取得後に取得。多言語対応をカレントページで判定しているため。
        if ($this->page && get_class($this->page) == 'App\Models\Common\Page') {
            // Page データ
            $this->pages = Page::defaultOrderWithDepth('flat', $this->page);
        }
        else {
            // Page データ
            $this->pages = Page::defaultOrder()->get();
        }


        // Frame データがあれば、画面のテンプレート情報をセット
        if (!empty($frame_id)) {
            $frame = Frame::where('id', $frame_id)->first();
            $finder = View::getFinder();
            $plugin_view_path = $finder->getPaths()[0].'/plugins/user/' . $frame->plugin_name;

            $file_list = scandir($plugin_view_path);
            foreach ($file_list as $file) {
                if (in_array($file, array('.', '..', 'default'))) {
                    continue;
                }
                if (is_dir(($finder->getPaths()[0].'/plugins/user/' . $frame->plugin_name . '/' . $file))) {
                    $this->target_frame_templates[] = $file;
                }
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
                               });
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
    private function getPageTree($page_id)
    {
        // 自分のページから親を遡って取得
        $page_tree = Page::reversed()->ancestorsAndSelf($page_id);

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
    protected function getThemes()
    {
        // 戻り値
        $return_array = array('css' => '', 'js' => '');

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
        $page_tree = Page::reversed()->ancestorsAndSelf($this->page->id);
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
            return view('404_not_found');
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

        return view($blade_path, $args);
    }
}
