<?php

namespace App\Enums;

/**
 * カウンター表示形式
 */
final class CounterDesignType extends EnumsBase
{
    // 定数メンバ
    const numeric = 'numeric';
    const numeric_comma = 'numeric_comma';
    const badge_primary = 'badge_primary';
    const badge_secondary = 'badge_secondary';
    const badge_success = 'badge_success';
    const badge_info = 'badge_info';
    const badge_warning = 'badge_warning';
    const badge_danger = 'badge_danger';
    const badge_light = 'badge_light';
    const badge_dark = 'badge_dark';
    const circle = 'circle';
    const black_circle = 'black_circle';
    const white_number = 'white_number';

    // key/valueの連想配列
    const enum = [
        self::numeric => '数字（カンマなし）',
        self::numeric_comma => '数字（カンマあり）',
        self::badge_primary => 'badge_primary',
        self::badge_secondary => 'badge_secondary',
        self::badge_success => 'badge_success',
        self::badge_info => 'badge_info',
        self::badge_warning => 'badge_warning',
        self::badge_danger => 'badge_danger',
        self::badge_light => 'badge_light',
        self::badge_dark => 'badge_dark',
        self::circle => '丸数字',
        self::black_circle => '黒丸数字',
        self::white_number => '白抜き数字',
    ];
}
