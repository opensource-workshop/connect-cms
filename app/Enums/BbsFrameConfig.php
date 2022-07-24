<?php

namespace App\Enums;

use App\Enums\EnumsBase;

/**
 * 掲示板のフレーム設定項目
 */
final class BbsFrameConfig extends EnumsBase
{
    // 定数メンバ
    const tree_indents = 'tree_indents';

    // key/valueの連想配列
    const enum = [
        self::tree_indents => 'ツリー形式の階層数',
    ];
}
