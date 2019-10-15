<?php

namespace App\Enums;

use BenSampo\Enum\Enum;

/*
* 記事関連の権限
*/
final class PermitForArticle extends Enum
{
    // 定数メンバ
    const management = 1;
    const plugin_placement = 2;
    const add = 3;
    const approval = 4;
    const moderator = 5;

    // key/valueの連想配列
    const enum = [
        self::management=>'記事管理者',
        self::plugin_placement=>'プラグイン配置',
        self::add=>'記事追加',
        self::approval=>'記事承認',
        self::moderator=>'モデレータ',
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
