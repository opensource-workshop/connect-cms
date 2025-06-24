<?php

namespace App\Enums;

use App\Enums\EnumsBase;

/**
 * 課題管理エクスポートタイプ
 */
final class LearningtaskExportType extends EnumsBase
{
    // 定数メンバ
    const report = 'export_report';

    // key/valueの連想配列
    const enum = [
        self::report => 'レポート提出',
    ];
}
