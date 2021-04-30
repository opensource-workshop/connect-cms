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
    const circle_primary = 'circle_primary';
    const circle_secondary = 'circle_secondary';
    const circle_success = 'circle_success';
    const circle_info = 'circle_info';
    const circle_warning = 'circle_warning';
    const circle_danger = 'circle_danger';
    const circle_light = 'circle_light';
    const circle_dark = 'circle_dark';
    const black_circle = 'black_circle';
    const black_circle_primary = 'black_circle_primary';
    const black_circle_secondary = 'black_circle_secondary';
    const black_circle_success = 'black_circle_success';
    const black_circle_info = 'black_circle_info';
    const black_circle_warning = 'black_circle_warning';
    const black_circle_danger = 'black_circle_danger';
    const black_circle_light = 'black_circle_light';
    const black_circle_dark = 'black_circle_dark';
    const white_number = 'white_number';
    const white_number_primary = 'white_number_primary';
    const white_number_secondary = 'white_number_secondary';
    const white_number_success = 'white_number_success';
    const white_number_info = 'white_number_info';
    const white_number_warning = 'white_number_warning';
    const white_number_danger = 'white_number_danger';
    const white_number_light = 'white_number_light';
    const white_number_dark = 'white_number_dark';

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
        self::circle_primary => '丸数字primary',
        self::circle_secondary => '丸数字secondary',
        self::circle_success => '丸数字success',
        self::circle_info => '丸数字info',
        self::circle_warning => '丸数字warning',
        self::circle_danger => '丸数字danger',
        self::circle_light => '丸数字light',
        self::circle_dark => '丸数字dark',
        self::black_circle => '黒丸数字',
        self::black_circle_primary => '黒丸数字primary',
        self::black_circle_secondary => '黒丸数字secondary',
        self::black_circle_success => '黒丸数字success',
        self::black_circle_info => '黒丸数字info',
        self::black_circle_warning => '黒丸数字warning',
        self::black_circle_danger => '黒丸数字danger',
        self::black_circle_light => '黒丸数字light',
        self::black_circle_dark => '黒丸数字dark',
        self::white_number => '白抜き数字',
        self::white_number_primary => '白抜き数字primary',
        self::white_number_secondary => '白抜き数字secondary',
        self::white_number_success => '白抜き数字success',
        self::white_number_info => '白抜き数字info',
        self::white_number_warning => '白抜き数字warning',
        self::white_number_danger => '白抜き数字danger',
        self::white_number_light => '白抜き数字light',
        self::white_number_dark => '白抜き数字dark',
    ];
}
