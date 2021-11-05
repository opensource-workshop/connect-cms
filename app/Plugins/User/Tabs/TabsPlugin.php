<?php

namespace App\Plugins\User\Tabs;

use Illuminate\Support\Facades\Log;

use App\Models\Common\Frame;
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
 * @package Controller
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
     * 追加の権限定義（コアから呼び出す）
     */
    public function declareRole()
    {
        // 標準権限以外で設定画面などから呼ばれる権限の定義
        // 標準権限は右記で定義 config/cc_role.php
        //
        // 権限チェックテーブル
        $role_check_table = [];
        $role_check_table["select"]        = ['role_arrangement'];
        $role_check_table["saveSelect"]    = ['role_arrangement'];

        return $role_check_table;
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
