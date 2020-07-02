<?php

namespace App\Plugins\User\Tabs;

use Illuminate\Support\Facades\Log;

use App\Models\Common\Frame;
use App\Models\Common\Page;
use App\Models\User\Tabs\Tabs;

use App\Plugins\User\UserPluginBase;

/**
 * タブ・プラグイン
 *
 * フレームをタブで切り替えるためのプラグイン
 *
 * @author 永原　篤 <nagahara@opensource-workshop.jp>
 * @copyright OpenSource-WorkShop Co.,Ltd. All Rights Reserved
 * @category タブ・プラグイン
 * @package Contoroller
 */
class TabsPlugin extends UserPluginBase
{

    /**
     *  編集画面の最初のタブ
     *
     *  スーパークラスをオーバーライド
     */
    public function getFirstFrameEditAction()
    {
        return "select";
    }

    /**
     *  関数定義（コアから呼び出す）
     */
    public function getPublicFunctions()
    {
        // 標準関数以外で画面などから呼ばれる関数の定義
        $functions = array();
        $functions['get']  = ['select'];
        $functions['post'] = ['saveSelect'];
        return $functions;
    }

    /**
     *  初期表示取得関数
     *
     * @return view
     */
    public function index($request, $page_id, $frame_id)
    {
        // 自分自身の設定
        $tabs = Tabs::where('frame_id', $frame_id)->first();

        $frame_ids = array();
        if (!empty($tabs)) {
            $frame_ids = explode(',', $tabs->frame_ids);
        }

        // タブでくくるために、同じページの自分以外のフレーム取得
        $frames = Frame::where('page_id', $page_id)
                       ->where('area_id', $this->frame->area_id)
                       ->where('id', '!=', $frame_id)
                       ->whereIn('id', $frame_ids)
                       ->orderBy('display_sequence', 'asc')
                       ->get();

        // 画面へ
        return $this->view('tabs', [
            'page_id'   => $page_id,
            'tabs'      => $tabs,
            'frames'    => $frames,
            'frames2'   => $frames,
        ]);
    }

    /**
     *  フレーム選択画面
     */
    public function select($request, $page_id, $frame_id)
    {
        // 権限チェック
        // ページ選択プラグインの特別処理。個別に権限チェックする。
        if ($this->can('role_arrangement')) {
            return $this->view_error(403);
        }

        // 自分自身の設定
        $tabs = Tabs::where('frame_id', $frame_id)->first();

        // タブでくくるために、同じページの自分以外のフレーム取得
        $frames = Frame::where('page_id', $page_id)
                       ->where('area_id', $this->frame->area_id)
                       ->where('id', '!=', $frame_id)
                       ->orderBy('display_sequence', 'asc')
                       ->get();

        // 画面へ
        return $this->view('tabs_select', [
            'page_id'   => $page_id,
            'frame_id'  => $frame_id,
            'page'      => $this->page,
            'tabs'      => $tabs,
            'frames'    => $frames,
        ]);
    }

    /**
     *  フレーム選択保存
     */
    public function saveSelect($request, $page_id, $frame_id)
    {
        // 権限チェック
        // ページ選択プラグインの特別処理。個別に権限チェックする。
        if ($this->can('role_arrangement')) {
            return $this->view_error(403);
        }

        // タブデータ作成 or 更新
        Tabs::updateOrCreate(
            ['frame_id' => $frame_id],
            [
                'frame_id' => $frame_id,
                'default_frame_id' => $request->default_frame_id,
                'frame_ids' => (empty($request->frame_select)) ? '' : implode(',', $request->frame_select)
            ]
        );

        // 画面へ
        return $this->select($request, $page_id, $frame_id);
    }
}
