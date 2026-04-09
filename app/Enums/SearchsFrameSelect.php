<?php

namespace App\Enums;

use App\Enums\EnumsBase;

/**
 * フレームの選択
 *
 * @author 牟田口 満 <akagane99@gmail.com>
 * @category サイト内検索
 * @package Enums
 */
class SearchsFrameSelect extends EnumsBase
{
    // 定数メンバ
    /** 全て表示する */
    const ALL_FRAMES = 0;
    /** 選択したものだけ表示する */
    const SELECTED_ONLY = 1;

    /** key/valueの連想配列 */
    const enum = [
        self::ALL_FRAMES => '全て表示する',
        self::SELECTED_ONLY => '選択したものだけ表示する',
    ];
}
