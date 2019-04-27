<?php

namespace App\Http\Controllers\Core;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\View;

use DB;

use App\Http\Controllers\Core\ConnectController;
use App\Frame;
use App\Page;

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

    /**
     *  画面表示用にページやフレームなど呼び出し
     *
     * @param String $plugin_name
     * @return view
     */
    public function __invoke(Request $request)
    {
        // フレーム一覧取得（メインエリアのみ）
        $frames = $this->getFramesMain($this->current_page->id);

        // プラグインのインスタンス取得（メインエリアのみ）
        $plugin_instances = $this->createInstanceMain($frames);

        // レイアウト取得
        $layouts_info = $this->getLayoutsInfo();

        // プラグインのインスタンス生成（メインエリア以外の共通エリア）
        $plugin_instances = $this->createInstanceCommonArea($layouts_info, $plugin_instances);

        // Page データ
        $pages = Page::defaultOrder()->get();


        // とりあえずココで処理。後でcore 系のアクションを見直してリファクタリングすること。
        $target_frame_templates = array();
        if (!empty($request->core_action)) {
 
            // Frame データ
            $frame = Frame::where('id', $request->frame_id)->first();
            $finder = View::getFinder();
            $plugin_view_path = $finder->getPaths()[0].'/plugins/user/' . $frame->plugin_name;

            $file_list = scandir($plugin_view_path);
            foreach ($file_list as $file) {
                if (in_array($file, array('.', '..', 'default'))) {
                    continue;
                }
                if (is_dir(($finder->getPaths()[0].'/plugins/user/' . $frame->plugin_name . '/' . $file))) {
                    $target_frame_templates[] = $file;
                }
            }
        }

        // view の場所を変更するテスト
        //$plugin_instances = ['contents' => new $class_name("User", "contents")];

        // メインページを呼び出し(coreのinvokeコントローラでは、スーパークラスのviewを使用)
        // 各フレーム内容の表示はメインページから行う。
        return $this->view('core.cms', [
            'current_page'     => $this->current_page,
            'frames'           => $frames,
            'pages'            => $pages,
            'plugin_instances' => $plugin_instances,
            'layouts_info'     => $layouts_info,
            'target_frame_templates' => $target_frame_templates,
        ]);
    }

    /**
     *  画面表示用にページやフレームなど呼び出し
     *
     * @param String $plugin_name
     * @return view
     */
    public function invokePost(Request $request, $plugin_name, $action = null, $page_id = null, $frame_id = null, $id = null)
    {
        if (empty($page_id)) {

            // カレントページの取得
            $this->current_page = $this->current_page();

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

        // フレーム一覧取得
        $frames = $this->getFramesMain($this->current_page->id);

        // インスタンス取得（メインエリアのみ）
        $plugin_instances = $this->createInstanceMain($frames);

        // レイアウト取得
        $layouts_info = $this->getLayoutsInfo();

        // プラグインのインスタンス生成（メインエリア以外の共通エリア）
        $plugin_instances = $this->createInstanceCommonArea($layouts_info, $plugin_instances);

        // Page データ
        $pages = Page::defaultOrder()->get();

        // view の場所を変更するテスト
        //$plugin_instances = ['contents' => new $class_name("User", "contents")];

        // メインページを呼び出し
        // 各フレーム内容の表示はメインページから行う。
        return $this->view('core.cms', [
            'action'           => $action,
            'frame_id'         => $frame_id,
            'id'               => $id,

            'current_page'     => $this->current_page,
            'frames'           => $frames,
            'pages'            => $pages,
            'plugin_instances' => $plugin_instances,
            'layouts_info'     => $this->getLayoutsInfo(),
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

        // 引数のアクションと同じメソッドを呼び出す。
        $class_name = "App\Plugins\User\\" . ucfirst($plugin_name) . "\\" . ucfirst($plugin_name) . "Plugin";
        $contentsPlugin = new $class_name;
        $contentsPlugin->$action($request, $page_id, $frame_id, $id);

        // 2ページ目以降を表示している場合は、表示ページに遷移
        $page_no_link = "";
        if ( $request->page ) {
            $page_no_link = "page=" . $request->page;
        }

        // return_mode があれば、編集中ページに遷移
        if ( $request->return_mode ) {
            $page = Page::where('id', $page_id)->first();
            $base_url = url('/');
            return redirect($base_url . $page->permanent_link . "?action=edit&frame_id=" . $frame_id . ($page_no_link ? "&" . $page_no_link : "") . "#" . $frame_id);
        }

        // Page データがあれば、そのページに遷移
        if ( !empty($page_id) ) {
            $page = Page::where('id', $page_id)->first();
            return redirect($page->permanent_link . ($page_no_link ? "?" . $page_no_link : ""));
        }

        return redirect("/" . ($page_no_link ? "?" . $page_no_link : ""));
    }

    /**
     * メインエリアのインスタンス生成
     *
     */
    public function createInstanceMain($frames)
    {
        // プラグインのインスタンス生成（メインエリア）
        $plugin_instances = array();
        foreach ($frames as $frame) {
            $class_name = "App\Plugins\User\\" . ucfirst($frame->plugin_name) . "\\" . ucfirst($frame->plugin_name) . "Plugin";
            $plugin_instances[$frame->frame_id] = new $class_name($frame);
        }
        return $plugin_instances;
    }

    /**
     * メインエリアのフレーム取得
     *
     */
    public function getFramesMain($pages_id)
    {
        // フレーム一覧取得（メインエリアのみ）
        $frames = DB::table('pages')
                    ->select('pages.page_name', 'pages.id as page_id', 'frames.id as frame_id', 'frames.area_id', 'frames.frame_title', 'frames.frame_design',
                            'frames.frame_col', 'frames.plugin_name', 'frames.template', 'frames.plug_name', 'frames.bucket_id')
                    ->join('frames', 'frames.page_id', '=', 'pages.id')
                    ->where('pages.id', $pages_id)
                    ->where('frames.area_id', 2)
                    ->orderBy('frames.display_sequence')->get();

        return $frames;
    }

    /**
     * 共通エリアのプラグインのインスタンス生成
     *
     */
    public function createInstanceCommonArea($layouts_info, $plugin_instances)
    {
        // 共通エリアのプラグインのインスタンス生成
        foreach ($layouts_info as $area) {
            if (array_key_exists('frames', $area)) {
                foreach ($area['frames'] as $frame) {
                    $class_name = "App\Plugins\User\\" . ucfirst($frame->plugin_name) . "\\" . ucfirst($frame->plugin_name) . "Plugin";
                    $plugin_instances[$frame->frame_id] = new $class_name($frame);
                }
            }
        }
        return $plugin_instances;
    }
}
