<?php

namespace App\Http\Controllers\Core;

use Illuminate\Support\Facades\Log;
use Illuminate\Http\Request;

use DB;
use View;

use App\Http\Controllers\Core\ConnectController;

use App\Models\Common\Frame;
use App\Models\Common\Page;

use App\Traits\ConnectCommonTrait;

/**
 * Frame の基本処理
 *
 * ルーティング処理から呼び出されるもの
 *
 * @author 永原　篤 <nagahara@opensource-workshop.jp>
 * @copyright OpenSource-WorkShop Co.,Ltd. All Rights Reserved
 * @category コア
 * @package Contoroller
 */
class FrameController extends ConnectController
{

    use ConnectCommonTrait;

    /**
     *  コンストラクタ
     */
    function __construct($page_id, $frame_id)
    {
        // ルートパラメータを取得する
    }

    /**
     *  プラグインの追加
     *
     * @param String $plugin_name
     * @return view
     */
    public function addPlugin($request, $page_id = null, $frame_id = null)
    {
        // 権限チェック
        if ($this->can("frames.create")) {
            abort(403, '権限がありません。');
        }

        // Page データ
        $page = Page::where('id', $page_id)->first();

        // フレームデータの作成
        // bucket_id はnull。プラグイン側で更新してもらう。
        $frame = new Frame;
        $frame->page_id = $page_id;
        $frame->area_id = $request->area_id;
        $frame->frame_title = "[無題]";
        $frame->frame_design = "default";
        $frame->plugin_name = $request->add_plugin;
        $frame->frame_col = 0;
        $frame->template = "default";
        $frame->bucket_id = null;
        $frame->display_sequence = 0;
        $frame->save();

        // Frameの順番更新
        // 追加のプラグインが0、他は連番になっているはずとして、ページ内全て、+1 する。
        DB::table('frames')->where('page_id', '=', $page_id)->increment('display_sequence');

        return redirect($page->permanent_link);
    }

    /**
     *  フレームの削除
     *
     * @param String $plugin_name
     * @return view
     */
    public function destroy($request, $page_id, $frame_id)
    {
        // 権限チェック
        if ($this->can("frames.delete")) {
            abort(403, '権限がありません。');
        }

        // Page データ
        $page = Page::where('id', $page_id)->first();

        // 現在はフレームの削除のみ。
        // バケツとプラグイン・データはプラグイン側で対応する。
        // 関数を呼び出しても良いかも。
        Frame::destroy($frame_id);

        return redirect($page->permanent_link);
    }

    /**
     *  フレーム設定画面の更新
     *
     * @return view
     */
    public function update($request, $page_id, $frame_id)
    {
        // 権限チェック
        if ($this->can("frames.edit")) {
            abort(403, '権限がありません。');
        }

        // 権限チェック
        if ($this->can("frames.edit")) {
            abort(403, '権限がありません。');
        }

        // Page データ
        $page = Page::where('id', $page_id)->first();

        // Frame データの更新
        Frame::where('id', $frame_id)
            ->update(['frame_title'       => $request->frame_title,
                      'frame_design'      => $request->frame_design,
                      'frame_col'         => $request->frame_col,
                      'template'          => $request->template,
                      'browser_width'     => $request->browser_width,
                      'disable_whatsnews' => ($request->disable_whatsnews == '') ? 0 : $request->disable_whatsnews,
                      'page_only'         => ($request->page_only == '') ? 0 : $request->page_only,
                      'default_hidden'    => ($request->default_hidden == '') ? 0 : $request->default_hidden,
                      'classname'         => $request->classname,
                      'plug_name'         => $request->plug_name,
                      'none_hidden'       => ($request->none_hidden == '') ? 0 : $request->none_hidden,
        ]);

        return redirect($page->permanent_link."#".$frame_id);
    }

    /**
     *  フレームの下移動
     *
     * @return view
     */
    public function sequenceDown($request, $page_id, $frame_id, $area_id)
    {
        // 権限チェック
        if ($this->can("frames.edit")) {
            abort(403, '権限がありません。');
        }

        // Page データ
        $page = Page::where('id', $page_id)->first();

        // 一度、現在のページ内のフレーム順を取得し、ページ番号を再採番。その際、指定されたフレームと次のフレームのみロジックで入れ替え。
        // 対象ページのフレームレコードを全て更新するが、ページ内のフレーム数分なので、レスポンスにも問題ないと判断。
        $frames = DB::table('frames')
                ->select('frames.id', 'display_sequence')
                ->join('pages', 'pages.id', '=', 'frames.page_id')
                ->where('page_id', $page_id)
                ->where('area_id', $area_id)
                ->orderBy('display_sequence')
                ->get();

        // 指定されたフレームを判別した時、次のレコードを処理するためのコントロールブレーク・フラグ
        $change_flag = false;

        // 下移動の場合は、フレームを上から番号を設定していくので、最初は1
        $display_sequence = 1;

        // ページ内フレームをループ。上から順番に番号設定。対象番号の場合に次と入れ替え。
        foreach ($frames as $frame) {
            // 対象番号の次
            if ($change_flag) {
                $change_flag = false;

                Frame::where('id', $frame->id)
                  ->update(['display_sequence' => $display_sequence - 1]);
            }
            // 指定された番号
            elseif ($frame->id == $frame_id) {
                $change_flag = true;

                Frame::where('id', $frame->id)
                  ->update(['display_sequence' => $display_sequence + 1]);
            }
            // その他の項目。番号がおかしくなっている場合などがあっても、再設定するので、きれいになる。
            else {
                Frame::where('id', $frame->id)
                  ->update(['display_sequence' => $display_sequence]);
            }
            $display_sequence++;
        }
        return redirect($page->permanent_link);
    }

    /**
     *  フレームの上移動
     *
     * @return view
     */
    public function sequenceUp($request, $page_id, $frame_id)
    {
        // 権限チェック
        if ($this->can("frames.edit")) {
            abort(403, '権限がありません。');
        }

        // Page データ
        $page = Page::where('id', $page_id)->first();

        // 一度、現在のページ内のフレーム順を取得し、ページ番号を再採番。その際、指定されたフレームと次のフレームのみロジックで入れ替え。
        // 対象ページのフレームレコードを全て更新するが、ページ内のフレーム数分なので、レスポンスにも問題ないと判断。
        $frames = DB::table('frames')
                ->select('id', 'display_sequence')
                ->where('page_id', $page_id)
                ->orderBy('display_sequence', 'desc')
                ->get();

        // 指定されたフレームを判別した時、次のレコードを処理するためのコントロールブレーク・フラグ
        $change_flag = false;

        // 上移動の場合は、フレームを下から番号を設定していくので、MAX値を取得
        $display_sequence = DB::table('frames')->where('page_id', $page_id)->count();

        // ページ内フレームをループ。下から順番に番号設定。対象番号の場合に次と入れ替え。
        foreach ($frames as $frame) {
            // 対象番号の次
            if ($change_flag) {
                $change_flag = false;

                Frame::where('id', $frame->id)
                  ->update(['display_sequence' => $display_sequence + 1]);
            }
            // 指定された番号
            elseif ($frame->id == $frame_id) {
                $change_flag = true;

                Frame::where('id', $frame->id)
                  ->update(['display_sequence' => $display_sequence - 1]);
            }
            // その他の項目。番号がおかしくなっている場合などがあっても、再設定するので、きれいになる。
            else {
                Frame::where('id', $frame->id)
                  ->update(['display_sequence' => $display_sequence]);
            }
            $display_sequence--;
        }
        return redirect($page->permanent_link);
    }

    /**
     * 編集画面
     *
     */
    public function edit($request, $page_id, $frame_id)
    {
        // 権限チェック
        if ($this->can("frames.edit")) {
            abort(403, '権限がありません。');
        }

        // Page データ
        $page = Page::where('id', $page_id)->first();

        // Frame データ
        $frame = Frame::where('id', $frame_id)->first();

        return $this->view('core.frame', [
            'page_id'                => $page_id,
            'page'                   => $page,
            'frame_id'               => $frame_id,
            'frame'                  => $frame,
            'current_page'           => $this->current_page,
            'target_frame_templates' => $this->target_frame_templates,
        ]);
    }

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function frame_setting($request, $page_id, $frame_id)
    {
        // 権限チェック
        if ($this->can("role_arrangement")) {
            abort(403, '権限がありません。');
        }

        echo "frame_setting";
        exit;
    }
}
