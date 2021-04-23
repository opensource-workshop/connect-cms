<?php

namespace App\Enums;

use App\Enums\EnumsBase;

/**
 * 課題管理ユーザ参加方式
 */
final class LearningtaskUserJoinFlag extends EnumsBase
{
    // 定数メンバ
    const all = 2;
    const select = 3;

    // key/valueの連想配列
    const enum = [
        self::all => '全員',
        self::select => '選ぶ',
    ];
}
