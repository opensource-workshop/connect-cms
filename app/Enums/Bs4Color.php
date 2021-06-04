<?php

namespace App\Enums;

/**
 * カラー（BootStrap4版）
 */
final class Bs4Color
{
    // 定数メンバ
    const primary = 'primary';
    const secondary = 'secondary';
    const success = 'success';
    const danger = 'danger';
    const warning = 'warning';
    const info = 'info';
    const light = 'light';
    const dark = 'dark';
    const muted = 'muted';
    const white = 'white';

    // key/valueの連想配列
    const enum = [
        self::primary=>'青',
        self::secondary=>'灰',
        self::success=>'緑',
        self::danger=>'赤',
        self::warning=>'黄',
        self::info=>'水色',
        self::light=>'明色',
        self::dark=>'黒',
        self::muted=>'淡色',
        self::white=>'白',
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
    public static function getMembers()
    {
        return self::enum;
    }
}
