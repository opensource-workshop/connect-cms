<?php

namespace App\Enums;

/**
 * 予約項目区分
 */
final class ReservationColumnType extends EnumsBase
{
    // 定数メンバ
    const text = 'text';
    // const textarea = 'textarea';
    const radio = 'radio';
    // const checkbox = 'checkbox';
    // const select = 'select';
    // const datetime = 'datetime';
    const created = 'created';
    const updated = 'updated';
    const created_name = 'created_name';
    const updated_name = 'updated_name';

    // key/valueの連想配列
    const enum = [
        self::text => '1行文字列型',
        // self::textarea => '複数行文字列型',
        self::radio => '単一選択型',
        // self::checkbox => '複数選択型',
        // self::select => 'リストボックス型',
        // self::datetime => '日付＆時間型',
        self::created => '登録日型（自動更新）',
        self::updated => '更新日型（自動更新）',
        self::created_name => '登録者型（自動更新）',
        self::updated_name => '更新者型（自動更新）',
    ];
}
