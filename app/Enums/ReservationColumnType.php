<?php

namespace App\Enums;

/**
 * 予約項目区分
 */
final class ReservationColumnType
{
    // 定数メンバ
    const txt = 'text';
    // const txtarea = 'textarea';
    const radio = 'radio';
    // const checkbox = 'checkbox';
    // const select = 'select';
    // const datetime = 'datetime';

    // key/valueの連想配列
    const enum = [
        self::txt=>'1行文字列型',
        // self::txtarea=>'複数行文字列型',
        self::radio=>'単一選択型',
        // self::checkbox=>'複数選択型',
        // self::select=>'リストボックス型',
        // self::datetime=>'日付＆時間型',
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
