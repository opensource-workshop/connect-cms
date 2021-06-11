<?php

namespace App\Enums;

use App\Enums\EnumsBase;

/**
 * キャビネットのファイル最大サイズ
 */
final class UploadMaxSize extends EnumsBase
{
    // 定数メンバ
    const two_mega_byte = '2048';
    const five_mega_byte = '5120';
    const ten_mega_byte = '10240';
    const twenty_mega_byte = '20480';
    const fifty_mega_byte = '51200';
    const hundred_mega_byte = '102400';
    const two_hundred_mega_byte = '204800';
    const one_giga_byte = '1024000';
    const infinity = 'infinity';

    // key/valueの連想配列
    const enum = [
        self::two_mega_byte => '2M',
        self::five_mega_byte => '5M',
        self::ten_mega_byte => '10M',
        self::twenty_mega_byte => '20M',
        self::fifty_mega_byte => '50M',
        self::hundred_mega_byte => '100M',
        self::two_hundred_mega_byte => '200M',
        self::one_giga_byte => '1G',
        self::infinity => '無制限',
    ];
}
