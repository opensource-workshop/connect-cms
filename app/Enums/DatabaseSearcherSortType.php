<?php

namespace App\Enums;

use App\Enums\EnumsBase;

/**
 * データベース検索並べ替え項目
 */
final class DatabaseSearcherSortType extends EnumsBase
{
    // 注意：_ は区切り文字に使っているため、const内に _ を含めない事
    const created = 'created';
    const updated = 'updated';
    const posted  = 'posted';
    const display = 'display';

    const order_asc     = 'asc';
    const order_desc    = 'desc';

    // 定数メンバ
    const created_asc    = self::created . '_' . self::order_asc;
    const created_desc   = self::created . '_' . self::order_desc;
    const updated_asc    = self::updated . '_' . self::order_asc;
    const updated_desc   = self::updated . '_' . self::order_desc;
    const posted_asc     = self::posted . '_' . self::order_asc;
    const posted_desc    = self::posted . '_' . self::order_desc;
    const display_asc    = self::display . '_' . self::order_asc;
    const display_desc   = self::display . '_' . self::order_desc;

    // key/valueの連想配列
    const enum = [
        self::created_asc    => '登録日（古い順）',
        self::created_desc   => '登録日（新しい順）',
        self::updated_asc    => '更新日（古い順）',
        self::updated_desc   => '更新日（新しい順）',
        self::posted_asc     => '公開日（古い順）',
        self::posted_desc    => '公開日（新しい順）',
        self::display_asc    => '表示順（昇順）',
        self::display_desc   => '表示順（降順）',
    ];
}
