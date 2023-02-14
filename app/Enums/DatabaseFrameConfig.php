<?php

namespace App\Enums;

use App\Enums\EnumsBase;

/**
 * データベースのフレーム設定項目
 */
final class DatabaseFrameConfig extends EnumsBase
{
    // 定数メンバ
    const database_use_select_multiple_flag = 'database_use_select_multiple_flag';

    // key/valueの連想配列
    const enum = [
        self::database_use_select_multiple_flag => '絞り込み機能の表示（複数選択）',
    ];
}
