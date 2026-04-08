<?php

namespace App\Enums;

use App\Enums\EnumsBase;

/**
 * 通知の埋め込みタグ
 * 
 * @author 牟田口 満 <akagane99@gmail.com>
 * @category サイト内検索
 * @package Enums
 */
class SearchsPageSelect extends EnumsBase
{
    // 定数メンバ
    /** 全て表示する */
    const ALL_PAGES = 0;
    /** ページ管理のメニュー表示条件に従う */
    const MENU_VISIBLE_ONLY = 1;

    // key/valueの連想配列
    const enum = [
        self::ALL_PAGES => '全て表示する',
        self::MENU_VISIBLE_ONLY => 'ページ管理のメニュー表示条件に従う',
    ];
}
