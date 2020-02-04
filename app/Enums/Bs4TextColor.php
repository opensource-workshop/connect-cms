<?php

namespace App\Enums;

/**
 * テキストカラー（BootStrap4版）
 */
final class Bs4TextColor
{
    // 定数メンバ
    const primary = 'text-primary';
    const secondary = 'text-secondary';
    const success = 'text-success';
    const danger = 'text-danger';
    const warning = 'text-warning';
    const info = 'text-info';
    const light = 'text-light';
    const dark = 'text-dark';
    const muted = 'text-muted';
    const white = 'text-white';

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
    public static function getMembers(){
        return self::enum;
    }
}
