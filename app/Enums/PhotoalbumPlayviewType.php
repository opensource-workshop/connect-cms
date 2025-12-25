<?php

namespace App\Enums;

/**
 * 動画の再生形式区分
 */
final class PhotoalbumPlayviewType extends EnumsBase
{
    // 定数メンバ
    const play_in_list = 0;
    const play_in_detail = 1;

    // key/valueの連想配列
    const enum = [
        self::play_in_list => '一覧で再生する',
        self::play_in_detail => '詳細画面で再生する',
    ];
}
