<?php

namespace App\Enums;

use App\Enums\EnumsBase;

/**
 * ブログのフレーム設定項目（投稿者名）
 */
final class BlogDisplayCreatedName extends EnumsBase
{
    // 定数メンバ
    const none = 'none';
    const display = 'display';

    // key/valueの連想配列
    const enum = [
        self::none => '表示しない',
        self::display => '表示する',
    ];
}
