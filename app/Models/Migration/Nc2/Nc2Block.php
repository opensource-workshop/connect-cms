<?php

namespace App\Models\Migration\Nc2;

use Illuminate\Database\Eloquent\Model;

class Nc2Block extends Model
{
    /**
     * 使用するDB Connection
     */
    protected $connection = 'nc2';

    /**
     * テーブル名の指定
     */
    protected $table = 'blocks';

    /**
     * NC2 theme_name -> Connect-CMS frame_design 変換用テーブル
     * 定義のないものは 'default' になる想定
     */
    protected $frame_designs = [

        // classic
        'classic_blue'       => 'primary',
        'classic_default'    => 'default',
        'classic_green'      => 'success',
        'classic_orange'     => 'warning',
        'classic_red'        => 'danger',

        // dot
        'dot_default'        => 'default',
        'dot_green'          => 'success',

        // dotline
        'dotline_darkkhaki'  => 'success',
        'dotline_default'    => 'default',
        'dotline_green'      => 'success',
        'dotline_orange'     => 'warning',
        'dotline_violet'     => 'danger',

        // dotround
        'dotround_blue'      => 'primary',
        'dotround_default'   => 'default',
        'dotround_green'     => 'success',
        'dotround_orange'    => 'warning',
        'dotround_red'       => 'danger',

        // noneframe
        'noneframe'          => 'none',

        // panelbar
        'panelbar_blue'      => 'primary',
        'panelbar_default'   => 'default',
        'panelbar_green'     => 'success',
        'panelbar_tan'       => 'default',
        'panelbar_violet'    => 'danger',

        // panelbasic
        'panelbasic_dark'    => 'secondary',
        'panelbasic_default' => 'default',

        // panelhole
        'panelhole_default'  => 'secondary',

        // panelround
        'panelround_default' => 'default',

        // sideline
        'sideline_blue'      => 'primary',
        'sideline_default'   => 'default',
        'sideline_green'     => 'success',
        'sideline_red'       => 'danger',

        // titleaccent
        'titleaccent'        => 'danger',

        // titlebox
        'titlebox_blue'      => 'primary',
        'titlebox_default'   => 'default',
        'titlebox_green'     => 'success',
        'titlebox_red'       => 'danger',

        // titleline
        'titleline_blue'     => 'primary',
        'titleline_default'  => 'default',
        'titleline_green'    => 'success',
        'titleline_red'      => 'danger',

        // titlepanel
        'titlepanel'         => 'default',

        // titleround
        'titleround'         => 'default',

        // underline
        'underline_blue'     => 'primary',
        'underline_default'  => 'default',
        'underline_green'    => 'success',
        'underline_red'      => 'danger',
    ];

    /**
     *  フレームテンプレートの変換
     */
    public function getFrameDesign($default = 'default')
    {
        // NC2 テンプレート変換配列にあれば、その値。
        // なければ default を返す。
        if (array_key_exists($this->theme_name, $this->frame_designs)) {
            return $this->frame_designs[$this->theme_name];
        }
        return $default;
    }

    /**
     *  モジュール名の取得
     */
    public function getModuleName()
    {
        $action_name = explode('_', $this->action_name);
        $module_name = $action_name[0];
        return $module_name;
    }
}
