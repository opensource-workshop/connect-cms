<?php

namespace App\Enums;

use App\Enums\EnumsBase;

/**
 * ページ-CSVのインデックス
 *
 * @author 牟田口 満 <mutaguchi@opensource-workshop.jp>
 * @copyright OpenSource-WorkShop Co.,Ltd. All Rights Reserved
 * @category ページ管理
 * @package Enums
 */
final class PageCvsIndex extends EnumsBase
{
    // 定数メンバ
    const page_name = 0;
    const permanent_link = 1;
    const background_color = 2;
    const header_color = 3;
    const theme = 4;
    const layout = 5;
    const base_display_flag = 6;

    /** key/valueの連想配列 */
    const enum = [
        self::page_name => 'page_name',
        self::permanent_link => 'permanent_link',
        self::background_color => 'background_color',
        self::header_color => 'header_color',
        self::theme => 'theme',
        self::layout => 'layout',
        self::base_display_flag => 'base_display_flag',
    ];
}
