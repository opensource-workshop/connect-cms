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
final class SearchsFrameSelect extends EnumsBase
{
    // 定数メンバ
    /** 全て表示する */
    const all_frames = 0;
    /** 選択したものだけ表示する */
    const selected_only = 1;

    /** key/valueの連想配列 */
    const enum = [
        self::all_frames => '全て表示する',
        self::selected_only => '選択したものだけ表示する',
    ];
}
