<?php

namespace App\Enums;

use App\Enums\EnumsBase;

/**
 * ページの選択
 *
 * @author 牟田口 満 <akagane99@gmail.com>
 * @category サイト内検索
 * @package Enums
 */
final class SearchsPageSelect extends EnumsBase
{
    // 定数メンバ
    /** 全て表示する */
    const all_pages = 0;
    /** ページ管理のメニュー表示条件に従う */
    const menu_visible_only = 1;

    /** key/valueの連想配列 */
    const enum = [
        self::all_pages => '全て表示する',
        self::menu_visible_only => 'ページ管理のメニュー表示条件に従う',
    ];
}
