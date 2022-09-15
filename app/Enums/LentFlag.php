<?php

namespace App\Enums;

/**
 * 貸出フラグ
 */
final class LentFlag extends EnumsBase
{
    // 定数メンバ
    const request = 2;
    const rented = 1;
    const finished = 9;

    // key/valueの連想配列
    const enum = [
        self::request => '貸出リクエスト',
        self::rented => '貸出中',
        self::finished => '貸出終了',
    ];
}
