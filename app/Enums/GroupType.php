<?php

namespace App\Enums;

/**
 * グループ関係
 */
final class GroupType
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

    /*
    * 対応した和名を返す
    */
    public static function getDescription($key): string
    {
        return self::enum[$key];
    }

    /*
    * key/valueの連想配列を返す
    */
    public static function getMembers(){
        return self::enum;
    }
}
