<?php

namespace App\Models\Common;

use Illuminate\Database\Eloquent\Model;

use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\View;

class Frame extends Model
{

    /**
     * create()やupdate()で入力を受け付ける ホワイトリスト
     */
    protected $fillable = [
        'page_id',
        'area_id',
        'frame_title',
        'frame_design',
        'plugin_name',
        'frame_col',
        'template',
        'bucket_id',
        'display_sequence',
    ];

    /**
     *  テンプレート
     */
    public $templates = null;

    /**
     *  フレーム非表示の判定
     */
    public $hidden_flag = null;

    /**
     *  テンプレートの設定
     */
    public function setTemplates($templates)
    {
        $this->templates = $templates;
    }

    /**
     *  テンプレートの取得
     */
    public function getTemplates()
    {
        return $this->templates;
    }

    /**
     *  プラグイン側のフレームメニューの読み込み
     */
    public function includeFrameTab($page, $frame, $action)
    {
        // プラグイン側のフレームメニューが存在する場合は、読み込む
        if (file_exists(resource_path() . '/views/plugins/user/' . $this->plugin_name . '/' . $this->plugin_name . '_frame_edit_tab.blade.php')) {
            $frame_view = view('plugins.user.' . $this->plugin_name . '.' . $this->plugin_name . '_frame_edit_tab', ['page' => $page, 'frame' => $frame, 'action' => $action]);
            echo $frame_view->render();
        }
    }

    /**
     *  狭い幅にするかどうかの判定
     */
    public function isExpandNarrow()
    {
        // 左もしくは右エリアなら狭い
        if ($this->area_id == 1 || $this->area_id == 3){
            return true;
        }
        // フレーム幅が0(100％)でなく＆設定値以下（標準設定は6）の場合は狭い
        if ($this->frame_col != 0 && $this->frame_col <= config('connect.CC_SETTING_EXPAND_COL')){
            return true;
        }
        // フレーム幅を広いと判断
        return false;
    }

    /**
     *  設定系の画面のnavbar-expand 取得
     */
    public function getNavbarExpand()
    {
        if ($this->isExpandNarrow()) {
            return "";
        }
        return "navbar-expand-md";
    }

    /**
     *  設定系の画面のnavbar brand 取得
     */
    public function getNavbarBrand()
    {
        if ($this->isExpandNarrow()) {
            return "";
        }
        return "d-md-none";
    }

    /**
     *  設定系の画面の幅CSS 取得
     */
    public function getSettingLabelClass()
    {
        if ($this->isExpandNarrow()) {
            return "col-md-12 col-form-label";
        }
        return "col-md-3 col-form-label text-md-right";
    }

    /**
     *  設定系の画面の幅CSS 取得
     */
    public function getSettingInputClass($d_flex = false, $no_align_items_center = null)
    {
        if ($this->isExpandNarrow()) {
            return "col-md-12";
        }

        $ret = "col-md-9";
        if ($d_flex) {
            $ret .= " d-sm-flex";
        }
        if (!$no_align_items_center) {
            $ret .= " align-items-center";
        }

        return $ret;
    }

    /**
     *  設定系の画面のボタンキャプションCSS 取得
     */
    public function getSettingButtonCaptionClass($size = null)
    {
        if ($this->isExpandNarrow()) {
            return "d-none";
        }
        if ($size == null) {
            return "d-none d-sm-inline";
        }
        return "d-none d-".$size."-inline";
    }

    /**
     *  設定系の画面のTABLE CSS 取得
     */
    public function getSettingTableClass()
    {
        if ($this->isExpandNarrow()) {
            return "cc-force-responsive-table";
        }
        return "cc_responsive_table";
    }

    /**
     *  設定系の画面のキャプション CSS 取得
     */
    public function getSettingCaptionClass()
    {
        if ($this->isExpandNarrow()) {
            return "d-inline";
        }
        return "d-inline d-md-none";
    }

    /**
     *  設定系の画面の表示設定 CSS 取得
     */
    public function getNarrowDisplayNone()
    {
        if ($this->isExpandNarrow()) {
            return "d-none";
        }
        return "d-none d-md-block";
    }

    /**
     *  フレームが表示対象か判定
     */
    public function isVisible($page = null, $user = null)
    {
        // ページは渡ってくるはずだが一応チェック
        if (empty($page)) {
            return false;
        }

        // ゲスト（ログインしていない状態）
        // プラグイン管理者権限を持たない場合
        if (empty($user) || !$user->can('role_arrangement')) {

           // フレームがこのページのみ表示しないの場合、表示対象外とする。
           if ($this->page_id == $page->id && $this->page_only == 2) {
               return false;
           }
        }

        // 上記以外の条件（非表示対象ページではない or プラグイン管理者権限を持つ）
        return true;
    }
}
