<?php

namespace App\Models\Migration\Nc3;

use Illuminate\Database\Eloquent\Model;

class Nc3MenuFrameSetting extends Model
{
    // 丸み(nav-pills)表示
    const display_nav_pills = 'footer';
    // タブ(nav-tabs)表示
    const display_nav_tabs = 'header';
    // リスト表示
    const display_list = 'major';
    // 下層のみ表示
    const display_minor = 'minor';
    // パンくず
    const display_topic_path = 'topic_path';
    // 追加メニューデザイン
    const display_header_flat = 'header_flat';
    const display_header_ids = 'header_ids';
    const display_header_minor = 'header_minor';
    const display_header_minor_noroot = 'header_minor_noroot';
    const display_header_minor_noroot_room = 'header_minor_noroot_room';
    const display_minor_and_first = 'minor_and_first';

    /**
     * 使用するDB Connection
     */
    protected $connection = 'nc3';

    /**
     * テーブル名の指定
     */
    protected $table = 'menu_frame_settings';
}
