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

    // key/valueの連想配列
    const enum = [
        self::text => '1行文字列型',
        // self::textarea => '複数行文字列型',
        self::radio => '単一選択型',
        // self::checkbox => '複数選択型',
        // self::select => 'リストボックス型',
        // self::datetime => '日付＆時間型',
    ];
}
