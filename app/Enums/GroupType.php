<?php

namespace App\Enums;

/**
 * グループ関係
 */
final class GroupType extends EnumsBase
{
    // 定数メンバ
    const general   = 'general';
    const moderator = 'moderator';
    const manager   = 'manager';

    // key/valueの連想配列
    const enum = [
        self::general   => '一般',
        self::moderator => 'モデレータ',
        self::manager   => '管理者',
    ];
}
