<?php

namespace App\Enums;

use App\Enums\EnumsBase;

/**
 * データベース並べ替え項目
 */
final class DatabaseSortFlag extends EnumsBase
{
    // 定数メンバの'_'の前半. $sort_column_id
    // 注意：_ は区切り文字に使っているため、const内に _ を含めない事
    const created = 'created';
    const updated = 'updated';
    const posted  = 'posted';
    const display = 'display';
    const random  = 'random';

    // 定数メンバの'_'の後半. $sort_column_order
    const order_asc     = 'asc';
    const order_desc    = 'desc';
    const order_session = 'session';
    const order_every   = 'every';

    // 定数メンバ
    // const created_asc    = 'created_asc';
    // const created_desc   = 'created_desc';
    // const updated_asc    = 'updated_asc';
    // const updated_desc   = 'updated_desc';
    // const random_session = 'random_session';
    // const random_every   = 'random_every';
    // PHP 5.6.0 以降はconstで文字連結可能 https://www.php.net/manual/ja/language.oop5.constants.php
    const created_asc    = self::created . '_' . self::order_asc;
    const created_desc   = self::created . '_' . self::order_desc;
    const updated_asc    = self::updated . '_' . self::order_asc;
    const updated_desc   = self::updated . '_' . self::order_desc;
    const posted_asc     = self::posted . '_' . self::order_asc;
    const posted_desc    = self::posted . '_' . self::order_desc;
    const display_asc    = self::display . '_' . self::order_asc;
    const display_desc   = self::display . '_' . self::order_desc;
    const random_session = self::random . '_' . self::order_session;
    const random_every   = self::random . '_' . self::order_every;
    const column         = 'column';

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
        self::random_session => 'ランダム（セッション）',
        self::random_every   => 'ランダム（毎回）',
        self::column         => '各カラム設定',
    ];

    /**
     * 並べ替え項目の表示用 のkey/valueの連想配列を返す
     */
    public static function getDisplaySortFlags()
    {
        return self::getMembers();
    }

    /**
     * 並び順 のkey/valueの連想配列を返す
     */
    public static function getSortFlags()
    {
        $sort_flags = static::enum;
        // columnは 並べ替え項目の表示用 のため、取り除く
        unset($sort_flags[self::column]);
        return $sort_flags;
    }
}
