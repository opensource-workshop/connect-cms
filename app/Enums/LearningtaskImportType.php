<?php

namespace App\Enums;

use App\Enums\EnumsBase;

/**
 * 課題管理インポートタイプ
 */
final class LearningtaskImportType extends EnumsBase
{
    // 定数メンバ
    const report = 'report';

    // key/valueの連想配列
    const enum = [
        self::report => 'レポート評価',
    ];
}
