<?php

namespace App\Models\Common;

use App\Enums\ContentOpenType;
use Carbon\Carbon;
use Database\Factories\Common\FrameFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Frame extends Model
{
    use HasFactory;

    /**
     * キャストする必要のある属性
     */
    protected $casts = [
        'content_open_date_from' => 'datetime',
        'content_open_date_to' => 'datetime',
    ];

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
        'plug_name',
        'browser_width',
        'disable_whatsnews',
        'disable_searchs',
        'page_only',
        'default_hidden',
        'classname',
        'classname_body',
        'none_hidden',
        'bucket_id',
        'display_sequence',
        'content_open_type',
        'content_open_date_from',
        'content_open_date_to',
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

        // オプションのプラグイン側のフレームメニューが存在する場合は、読み込む
        if (file_exists(resource_path().'/views/plugins_option/user/' . $this->plugin_name . "/" . $this->plugin_name . "_frame_edit_tab.blade.php")) {
            $frame_view = view('plugins_option.user.' . $this->plugin_name . '.' . $this->plugin_name . '_frame_edit_tab', ['page' => $page, 'frame' => $frame, 'action' => $action]);
            echo $frame_view->render();
        }
    }

    /**
     *  狭い幅にするかどうかの判定
     */
    public function isExpandNarrow()
    {
        // 左もしくは右エリアなら狭い
        if ($this->area_id == 1 || $this->area_id == 3) {
            return true;
        }
        // フレーム幅が0(100％)でなく＆設定値以下（標準設定は6）の場合は狭い
        if ($this->frame_col != 0 && $this->frame_col <= config('connect.CC_SETTING_EXPAND_COL')) {
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

    /**
     * 非公開・限定公開フレームが非表示か
     * ※この関数を修正する場合、scopeVisible()も修正すべきか確認してください。
     */
    public function isInvisiblePrivateFrame()
    {
        // 非ログインまたはフレーム編集権限を持たない、且つ、非表示条件（非公開、又は、限定公開、又は、ログイン後非表示、又は、ログイン後表示（未ログインで非表示））にマッチした場合はフレームを非表示にする

        if (Auth::check() && Auth::user()->can('role_arrangement') && app('request')->input('mode') != 'preview') {
            // 表示
            return false;
        }

        if ($this->content_open_type == ContentOpenType::always_close) {
            // 非表示
            return true;

        } elseif ($this->content_open_type == ContentOpenType::limited_open &&
            !Carbon::now()->between($this->content_open_date_from, $this->content_open_date_to)) {
            // 非表示
            return true;

        } elseif ($this->content_open_type == ContentOpenType::login_close && Auth::check()) {
            // 非表示
            return true;

        } elseif ($this->content_open_type == ContentOpenType::login_open && !Auth::check()) {
            // 非表示
            return true;
        }

        // 表示
        return false;
    }

    /**
     * 利用者が見れるフレームか
     * ※この関数を修正する場合、isInvisiblePrivateFrame()も修正すべきか確認してください。
     * ※Frame::visible() 等で呼ばれる
     */
    public function scopeVisible($query)
    {
        // ログイン状態かつ、管理権限があればすべてフレームを見られる
        if (Auth::check() && Auth::user()->can('role_arrangement')) {
            return $query;
        }

        return $query->where('content_open_type', ContentOpenType::always_open)
            ->orWhere(function ($query) {
                $query->where('content_open_type', ContentOpenType::limited_open)
                    ->whereDate('content_open_date_from', '<=', Carbon::now())
                    ->whereDate('content_open_date_to', '>=', Carbon::now());
            })
            ->orWhere(function ($query) {
                $auth_check = Auth::check() ? 'true' : 'false';
                $query->where('content_open_type', ContentOpenType::login_close)
                    ->whereRaw("FALSE = $auth_check");
            });
    }

    /**
     * フレームに関連しているページの取得
     */
    public function page()
    {
        return $this->hasOne(Page::class, 'id', 'page_id');
    }

    protected static function newFactory()
    {
        return FrameFactory::new();
    }
}
