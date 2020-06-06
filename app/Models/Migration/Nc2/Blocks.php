<?php

namespace App\Models\Migration\Nc2;

use Illuminate\Database\Eloquent\Model;

class Blocks extends Model
{
    /**
     * 使用するDB Connection
     */
    protected $connection = 'nc2';

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
     * NC2 action_name -> Connect-CMS plugin_name 変換用テーブル
     * 開発中 or 開発予定のものは 'Development' にする。
     * 廃止のものは 'Abolition' にする。
     */
    protected $plugin_name = [
        'announcement'  => 'contents',     // お知らせ
        'assignment'    => 'Development',  // レポート
        'bbs'           => 'Development',  // 掲示板
        'cabinet'       => 'Development',  // キャビネット
        'calendar'      => 'Development',  // カレンダー
        'chat'          => 'Development',  // チャット
        'circular'      => 'Development',  // 回覧板
        'counter'       => 'Development',  // カウンター
        'iframe'        => 'Development',  // iFrame
        'imagine'       => 'Abolition',    // imagine
        'journal'       => 'blogs',        // ブログ
        'language'      => 'Development',  // 言語選択
        'linklist'      => 'Development',  // リンクリスト
        'login'         => 'Development',  // ログイン
        'menu'          => 'menus',        // メニュー
        'multidatabase' => 'databases',    // データベース
        'online'        => 'Development',  // オンライン状況
        'photoalbum'    => 'Development',  // フォトアルバム
        'pm'            => 'Abolition',    // プライベートメッセージ
        'questionnaire' => 'Development',  // アンケート
        'quiz'          => 'Development',  // 小テスト
        'registration'  => 'forms',        // フォーム
        'reservation'   => 'reservations', // 施設予約
        'rss'           => 'Development',  // RSS
        'search'        => 'searchs',      // 検索
        'todo'          => 'Development',  // ToDo
        'whatsnew'      => 'whatsnews',    // 新着情報
    ];

    /**
     *  フレームテンプレートの変換
     */
    public function getFrameDesign($nc2_block)
    {
        // NC2 テンプレート変換配列にあれば、その値。
        // なければ default を返す。
        if (array_key_exists($nc2_block->theme_name, $this->frame_designs)) {
            return $this->frame_designs[$nc2_block->theme_name];
        }
        return 'default';
    }

    /**
     *  プラグインの変換
     */
    public function getPluginName($nc2_block)
    {
        // NC2 テンプレート変換配列にあれば、その値。
        // 定義のないものは 'NotFound' にする。
        $action_name = explode('_', $nc2_block->action_name);
        $module_name = $action_name[0];

        if (array_key_exists($module_name, $this->plugin_name)) {
            return $this->plugin_name[$module_name];
        }
        return 'NotFound';
    }
}
