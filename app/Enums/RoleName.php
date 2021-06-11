<?php

namespace App\Enums;

use App\Enums\EnumsBase;

/**
 * 役割名
 */
final class RoleName extends EnumsBase
{
    // 定数メンバ
    const student = 'student';
    const teacher = 'teacher';

    // key/valueの連想配列
    const enum = [
        self::student => '学生',
        self::teacher => '教員',
    ];
}
