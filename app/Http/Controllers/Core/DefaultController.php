<?php

namespace App\Http\Controllers\Core;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

use DB;
use View;

use App\Http\Controllers\Core\ConnectController;
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
    public function __invoke()
    {
        // ページデータ取得のため、URL から現在のURL パスを判定する。
        $current_url = url()->current();
        $base_url = url('/');
        $current_permanent_link = str_replace( $base_url, '', $current_url);

        // トップページの判定
        if (empty($current_permanent_link)) {
            $current_permanent_link = "/";
        }

        // URL パスでPage テーブル検索
        $current_page = Page::where('permanent_link', '=', $current_permanent_link)->first();
        if (empty($current_page)) {
            return view('404_not_found');
        }

        // Page_id
        $pages_id = $current_page->id;

        // フレーム一覧取得
        $frames = DB::table('pages')
                    ->select('pages.page_name', 'frames.id as frame_id', 'frames.frame_title', 'frames.frame_design',
                            'frames.frame_col', 'frames.plugin_name', 'frames.plug_name', 'frames.bucket_id')
                    ->join('frames', 'frames.page_id', '=', 'pages.id')
                    ->where('pages.id', $pages_id)->orderBy('frames.display_sequence')->get();

        // Page データ
        $pages = Page::defaultOrder()->get();

        // 該当ページのプラグイン一覧
        $plugin_names = DB::table('frames')
                    ->select('plugin_name', 'page_id')
                    ->groupBy('plugin_name', 'page_id')
                    ->having('page_id', '=', $pages_id)
                    ->get();

        // プラグインのインスタンス生成
        $plugin_instances = array();
        foreach ($plugin_names as $plugin_name) {
            $class_name = "App\Plugins\User\\" . ucfirst($plugin_name->plugin_name) . "\\" . ucfirst($plugin_name->plugin_name) . "Plugin";
            $plugin_instances[$plugin_name->plugin_name] = new $class_name;
        }

        // view の場所を変更するテスト
        //$plugin_instances = ['contents' => new $class_name("User", "contents")];

        // メインページを呼び出し
        // 各フレーム内容の表示はメインページから行う。
        return view('core.cms', [
            'current_page'     => $current_page,
            'frames'           => $frames,
            'pages'            => $pages,
            'plugin_instances' => $plugin_instances
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

            // ページデータ取得のため、URL から現在のURL パスを判定する。
            $current_url = url()->current();
            $base_url = url('/');
            $current_permanent_link = str_replace( $base_url, '', $current_url);

            // トップページの判定
            if (empty($current_permanent_link)) {
                $current_permanent_link = "/";
            }

            // URL パスでPage テーブル検索
            $current_page = Page::where('permanent_link', '=', $current_permanent_link)->first();
            if (empty($current_page)) {
                return view('404_not_found');
            }

            // Page_id
            $pages_id = $current_page->id;
        }
        else {

            // page_id でPage テーブル検索
            $current_page = Page::where('id', '=', $page_id)->first();
            if (empty($current_page)) {
                return view('404_not_found');
            }

            // Page_id
            $pages_id = $page_id;
        }

        // フレーム一覧取得
        $frames = DB::table('pages')
                    ->select('pages.page_name', 'frames.id as frame_id', 'frames.frame_title', 'frames.frame_design',
                            'frames.frame_col', 'frames.plugin_name', 'frames.plug_name', 'frames.bucket_id')
                    ->join('frames', 'frames.page_id', '=', 'pages.id')
                    ->where('pages.id', $pages_id)->orderBy('frames.display_sequence')->get();

        // Page データ
        $pages = Page::defaultOrder()->get();

        // 該当ページのプラグイン一覧
        $plugin_names = DB::table('frames')
                    ->select('plugin_name', 'page_id')
                    ->groupBy('plugin_name', 'page_id')
                    ->having('page_id', '=', $pages_id)
                    ->get();

        // プラグインのインスタンス生成
        $plugin_instances = array();
        foreach ($plugin_names as $plugin_name) {
            $class_name = "App\Plugins\User\\" . ucfirst($plugin_name->plugin_name) . "\\" . ucfirst($plugin_name->plugin_name) . "Plugin";
            $plugin_instances[$plugin_name->plugin_name] = new $class_name;
        }

        // view の場所を変更するテスト
        //$plugin_instances = ['contents' => new $class_name("User", "contents")];

        // メインページを呼び出し
        // 各フレーム内容の表示はメインページから行う。
        return view('core.cms', [
            'action'           => $action,
            'frame_id'         => $frame_id,
            'id'               => $id,

            'current_page'     => $current_page,
            'frames'           => $frames,
            'pages'            => $pages,
            'plugin_instances' => $plugin_instances
        ]);


        return;

        // リダイレクトしていたケース（リダイレクト用処理を別に定義するなど）

        // // プラグイン毎に動的にnew する。
        // // Todo：プラグインを動的にインスタンス生成すること。

        // // 引数のアクションと同じメソッドを呼び出す。
        // $class_name = "App\Plugins\User\\" . ucfirst($plugin_name) . "\\" . ucfirst($plugin_name) . "Plugin";
        // $contentsPlugin = new $class_name;
        // $contentsPlugin->$action($request, $page_id, $frame_id, $id);

        // // 2ページ目以降を表示している場合は、表示ページに遷移
        // $page_no_link = "";
        // if ( $request->page ) {
        //     $page_no_link = "page=" . $request->page;
        // }

        // // return_mode があれば、編集中ページに遷移
        // if ( $request->return_mode ) {
        //     $page = Page::where('id', $page_id)->first();
        //     $base_url = url('/');
        //     return redirect($base_url . $page->permanent_link . "?action=edit&frame_id=" . $frame_id . ($page_no_link ? "&" . $page_no_link : "") . "#" . $frame_id);
        // }

        // // Page データがあれば、そのページに遷移
        // if ( !empty($page_id) ) {
        //     $page = Page::where('id', $page_id)->first();
        //     return redirect($page->permanent_link . ($page_no_link ? "?" . $page_no_link : ""));
        // }

        // return redirect("/" . ($page_no_link ? "?" . $page_no_link : ""));
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
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        //
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        //
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        //
    }
}
