<?php

namespace App\Enums;

use App\Enums\EnumsBase;

/**
 * 掲示板の表示形式
 */
final class BbsViewFormat extends EnumsBase
{
    // 定数メンバ
    const flat = 'bbs-flat';
    const tree = 'bbs-tree';

    // key/valueの連想配列
    const enum = [
        self::flat => 'フラット形式',
        self::tree => 'ツリー形式',
    ];
}
