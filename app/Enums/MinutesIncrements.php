<?php

namespace App\Enums;

/**
 * 分刻み指定
 */
final class MinutesIncrements
{
    // 定数メンバ
    const every5 = 5;
    const every10 = 10;
    const every15 = 15;
    const every30 = 30;
    const every60 = 60;

    // key/valueの連想配列
    const enum = [
        self::every5=>'5分刻み',
        self::every10=>'10分刻み',
        self::every15=>'15分刻み',
        self::every30=>'30分刻み',
        self::every60=>'60分刻み',
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
