<?php

namespace App\Enums;

use BenSampo\Enum\Enum;

/*
* 管理権限
*/
final class PermitForManage extends Enum
{
    // 定数メンバ
    const system = 1;
    const page = 2;
    const site = 3;
    const user = 4;

    // key/valueの連想配列
    const enum = [
        self::system=>'システム管理者',
        self::page=>'ページ管理者',
        self::site=>'サイト管理者',
        self::user=>'ユーザ管理者',
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
