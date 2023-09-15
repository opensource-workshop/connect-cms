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
    const database_show_trend_words = 'database_show_trend_words';
    const database_trend_words = 'database_trend_words';
    const database_trend_words_caption = 'database_trend_words_caption';

    // key/valueの連想配列
    const enum = [
        self::database_use_select_multiple_flag => '絞り込み機能の表示（複数選択）',
        self::database_show_trend_words => '急上昇ワードの表示',
        self::database_trend_words => '急上昇ワード',
        self::database_trend_words_caption => '急上昇ワード表示項目名',
    ];
}
