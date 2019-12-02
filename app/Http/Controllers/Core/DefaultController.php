<?php

namespace App\Http\Controllers\Core;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\View;

use DB;

use App\Http\Controllers\Core\ConnectController;

use App\Models\Common\Frame;
use App\Models\Common\Page;
use App\Models\Core\Configs;
use App\Models\Core\Plugins;

use App\Traits\ConnectCommonTrait;

/**
 * 画面の基本処理
 *
 * ルーティング処理から呼び出されるもの
 *
 * @author 永原　篤 <nagahara@opensource-workshop.jp>
 * @copyright OpenSource-WorkShop Co.,Ltd. All Rights Reserved
 * @category コア
 * @package Contoroller
 */
class DefaultController extends ConnectController
{

    use ConnectCommonTrait;

    /**
     *  画面表示用にページやフレームなど呼び出し
     *
     * @param String $plugin_name
     * @return view
     */
    public function __invoke(Request $request)
    {
        // 特別なPath が指定された場合は処理を行い、return する。
        if ($this->isSpecialPath($request->path())) {
            return $this->callSpecialPath($request->path(), $request);
        }

        // フレーム一覧取得（メインエリアのみ）
        $frames = $this->getFramesMain($this->page->id);

        // プラグインのインスタンス取得（メインエリアのみ）
        $plugin_instances = $this->createInstanceMain($frames);

        // レイアウト取得
        $layouts_info = $this->getLayoutsInfo();

        // テーマ取得
        $themes = $this->getThemes();

        // プラグインのインスタンス生成（メインエリア以外の共通エリア）
        $plugin_instances = $this->createInstanceCommonArea($layouts_info, $plugin_instances);

        // Page データ
        $pages = Page::defaultOrder()->get();
        //Log::debug(json_decode($pages));

        // フレームで使用するテンプレート・リスト、プラグインのフレームメニュー
        $action_core_frame = $this->getActionCoreFrame($request);

        // プラグイン一覧の取得
        $plugins = $this->getPlugins();

        // Config
        $configs_array = $this->getConfigs('array');

        // view の場所を変更するテスト
        //$plugin_instances = ['contents' => new $class_name("User", "contents")];

        // メインページを呼び出し(coreのinvokeコントローラでは、スーパークラスのviewを使用)
        // 各フレーム内容の表示はメインページから行う。
        return $this->view('core.cms', [
            'action'            => $request->action,
            'frame_id'          => $request->frame_id,
            'page'              => $this->page,
            'frames'            => $frames,
            'pages'             => $pages,
            'plugin_instances'  => $plugin_instances,
            'layouts_info'      => $layouts_info,
            'themes'            => $themes,
            'action_core_frame' => $action_core_frame,
            'plugins'           => $plugins,
            'configs_array'     => $configs_array,
        ]);
    }

    /**
     *  多言語の切り替え機能
     *
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
        foreach($languages as $language) {
            if (trim($language->additional1, '/') == $language_or_1stdir) {
                $next_language = trim($language->additional1, '/');
                break;
            }
        }
        //echo $next_language;

       // permanent_link の編集
       // 言語がデフォルト(next_language がnull)＆2nd以降がある場合は、language_or_1stdir はpermanent_link の一部なので、結合する。
       if (empty($next_language) && $link_or_after2nd) {
           $permanent_link = $language_or_1stdir . '/' . $link_or_after2nd;
       }
       // 言語がデフォルト(next_language がnull)＆2nd以降がない場合は、language_or_1stdir がディレクトリ。
       else if (empty($next_language)) {
           $permanent_link = $language_or_1stdir;
       }
       else {
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
     *  フレームで使用するテンプレート・リスト、プラグインのフレームメニュー
     *
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

            $file_list = scandir($plugin_view_path);
            foreach ($file_list as $file) {
                if (in_array($file, array('.', '..', 'default'))) {
                    continue;
                }
                if (is_dir(($finder->getPaths()[0].'/plugins/user/' . $action_core_frame->plugin_name . '/' . $file))) {
                    $target_frame_templates[] = $file;
                }
            }
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
     *  画面表示用にページやフレームなど呼び出し
     *
     * @param String $plugin_name
     * @return view
     */
    public function invokePost(Request $request, $plugin_name, $action = null, $page_id = null, $frame_id = null, $id = null)
    {

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
        $frames = $this->getFramesMain($this->page->id);

        // フレームとプラグインの一致をチェック
        if (!$this->checkFrame2Plugin($plugin_name, $frame_id, $frames)) {
            return $this->view_error("403");
        }

        // インスタンス取得（メインエリアのみ）
        $plugin_instances = $this->createInstanceMain($frames);

        // レイアウト取得
        $layouts_info = $this->getLayoutsInfo();

        // テーマ取得
        $themes = $this->getThemes();

        // プラグインのインスタンス生成（メインエリア以外の共通エリア）
        $plugin_instances = $this->createInstanceCommonArea($layouts_info, $plugin_instances);

        // フレームとプラグインの一致をチェック
        foreach ($layouts_info as $area) {
            if (array_key_exists('frames', $area)) {
                if (!$this->checkFrame2Plugin($plugin_name, $frame_id, $area['frames'])) {
                    return $this->view_error("403");
                }
            }
        }

        // Page データ
        $pages = Page::defaultOrder()->get();

        // view の場所を変更するテスト
        //$plugin_instances = ['contents' => new $class_name("User", "contents")];

        // フレームで使用するテンプレート・リスト、プラグインのフレームメニュー
        $action_core_frame = $this->getActionCoreFrame($request);

        // プラグイン一覧の取得
        $plugins = $this->getPlugins();

        // Config
        $configs_array = $this->getConfigs('array');

        // メインページを呼び出し
        // 各フレーム内容の表示はメインページから行う。
//Log::debug($action);
//Log::debug($frame_id);
        return $this->view('core.cms', [
            'action'            => $action,
            'frame_id'          => $frame_id,
            'id'                => $id,
            'page'              => $this->page,
            'frames'            => $frames,
            'pages'             => $pages,
            'plugin_instances'  => $plugin_instances,
            'layouts_info'      => $layouts_info,
            'themes'            => $themes,
            'action_core_frame' => $action_core_frame,
            'plugins'           => $plugins,
            'configs_array'     => $configs_array,
        ]);

        return;
    }

    /**
     *  画面表示用にページやフレームなど呼び出し
     *
     * @param String $plugin_name
     * @return view
     */
    public function invokePostRedirect(Request $request, $plugin_name, $action = null, $page_id = null, $frame_id = null, $id = null)
    {
        // プラグイン毎に動的にnew する。
        // Todo：プラグインを動的にインスタンス生成すること。

        // フレームのインスタンス生成、プラグインクラスに渡すこと
        $action_frame = null;
        if (!empty($frame_id)) {
            $action_frame = Frame::where('id', $frame_id)->first();
        }

        // 引数のアクションと同じメソッドを呼び出す。
        $class_name = "App\Plugins\User\\" . ucfirst($plugin_name) . "\\" . ucfirst($plugin_name) . "Plugin";
        $contentsPlugin = new $class_name($this->page, $action_frame);

        // invokeを通して呼び出すことで権限チェックを実施
        $contentsPlugin->invoke($contentsPlugin, $request, $action, $page_id, $frame_id, $id);

        // 2ページ目以降を表示している場合は、表示ページに遷移
        $page_no_link = "";
        if ( $request->page ) {
            $page_no_link = "page=" . $request->page;
        }

        // return_frame_action があれば、編集中ページに遷移
        if ( $request->return_frame_action ) {
            $page = Page::where('id', $page_id)->first();
            $base_url = url('/');
            return redirect($base_url . $page->permanent_link . "?frame_action=" . $request->return_frame_action . "&frame_id=" . $frame_id . ($page_no_link ? "&" . $page_no_link : "") . "#" . $frame_id);
//            return redirect($base_url . $page->permanent_link . "?action=" . $return_frame_action . "&frame_id=" . $frame_id . ($page_no_link ? "&" . $page_no_link : "") . "#" . $frame_id);
        }

        // redirect_path があれば遷移
        if ( $request->redirect_path ) {
            return redirect($request->redirect_path);
        }

        // Page データがあれば、そのページに遷移
        if ( !empty($page_id) ) {
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

        // 引数のアクションと同じメソッドを呼び出す。
        $class_name = "App\Plugins\User\\" . ucfirst($plugin_name) . "\\" . ucfirst($plugin_name) . "Plugin";
        $contentsPlugin = new $class_name($this->page, $action_frame);

        // invokeを通して呼び出すことで権限チェックを実施
        return $contentsPlugin->invoke($contentsPlugin, $request, $action, $page_id, $frame_id, $id);
    }

    /**
     * メインエリアのインスタンス生成
     *
     */
    private function createInstanceMain($frames)
    {
        // プラグインのインスタンス生成（メインエリア）
        $plugin_instances = array();
        foreach ($frames as $frame) {
            $class_name = "App\Plugins\User\\" . ucfirst($frame->plugin_name) . "\\" . ucfirst($frame->plugin_name) . "Plugin";
//Log::debug($this->page);
//Log::debug(print_r($frame,true));
            $plugin_instances[$frame->frame_id] = new $class_name($this->page, $frame);
        }
        return $plugin_instances;
    }

    /**
     * メインエリアのフレーム取得
     *
     */
    private function getFramesMain($pages_id)
    {
        // フレーム一覧取得（メインエリアのみ）
        $frames = DB::table('pages')
                    ->select('pages.page_name', 'pages.id as page_id', 'frames.id as id', 'frames.id as frame_id', 'frames.area_id', 'frames.frame_title', 'frames.frame_design',
                            'frames.frame_col', 'frames.plugin_name', 'frames.template', 'frames.plug_name', 'frames.bucket_id', 'frames.browser_width', 'frames.disable_whatsnews',
                            'plugins.plugin_name_full')
                    ->join('frames', 'frames.page_id', '=', 'pages.id')
                    ->leftJoin('plugins',  'plugins.plugin_name', '=', 'frames.plugin_name')
                    ->where('pages.id', $pages_id)
                    ->where('frames.area_id', 2)
                    ->orderBy('frames.display_sequence')->get();

        return $frames;
    }

    /**
     * 共通エリアのプラグインのインスタンス生成
     *
     */
    private function createInstanceCommonArea($layouts_info, $plugin_instances)
    {
        // 共通エリアのプラグインのインスタンス生成
        foreach ($layouts_info as $area) {
            if (array_key_exists('frames', $area)) {
                foreach ($area['frames'] as $frame) {
                    $class_name = "App\Plugins\User\\" . ucfirst($frame->plugin_name) . "\\" . ucfirst($frame->plugin_name) . "Plugin";
                    $plugin_instances[$frame->frame_id] = new $class_name($this->page, $frame);
                }
            }
        }
        return $plugin_instances;
    }
}
