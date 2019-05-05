<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\View;

class Frame extends Model
{

    /**
     *  テンプレート
     */
    public $templates = null;

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
        if (file_exists(resource_path() . '/views/plugins/user/' . $this->plugin_name . '/frame_edit_tab.blade.php')) {
            $frame_view = view('plugins.user.' . $this->plugin_name . '.frame_edit_tab', ['page' => $page, 'frame' => $frame, 'action' => $action]);
            echo $frame_view->render();
        }
    }
}
