<?php

namespace App\Enums;

use App\Enums\EnumsBase;

/**
 * データベース並べ替え項目
 */
final class DatabaseSortFlag extends EnumsBase
{
    // 定数メンバ
    const created_asc    = 'created_asc';
    const created_desc   = 'created_desc';
    const updated_asc    = 'updated_asc';
    const updated_desc   = 'updated_desc';
    const random_session = 'random_session';
    const random_every   = 'random_every';
    const column         = 'column';

    // key/valueの連想配列
    const enum = [
        self::created_asc    => '登録日（古い順）',
        self::created_desc   => '登録日（新しい順）',
        self::updated_asc    => '更新日（古い順）',
        self::updated_desc   => '更新日（新しい順）',
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

    /**
     * 並び順 のkey配列を返す
     */
    public static function getSortFlagsKeys()
    {
        $sort_flags = self::getSortFlags();
        return array_keys($sort_flags);
    }
}
