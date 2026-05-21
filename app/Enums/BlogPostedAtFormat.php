<?php

namespace App\Enums;

use App\Enums\EnumsBase;
use Carbon\CarbonInterface;

/**
 * ブログの投稿日時表示形式
 */
final class BlogPostedAtFormat extends EnumsBase
{
    // 定数メンバ
    const japanese = 'japanese';
    const slash = 'slash';
    const hyphen = 'hyphen';
    const dot = 'dot';
    const japanese_era = 'japanese_era';

    // key/valueの連想配列
    const enum = [
        self::japanese => 'yyyy年m月d日 hh時mm分',
        self::slash => 'yyyy/mm/dd hh:mm',
        self::hyphen => 'yyyy-mm-dd hh:mm',
        self::dot => 'yyyy.mm.dd hh:mm',
        self::japanese_era => '和暦（元号年m月d日 hh時mm分）',
    ];

    const date_formats = [
        self::japanese => 'Y年n月j日',
        self::slash => 'Y/m/d',
        self::hyphen => 'Y-m-d',
        self::dot => 'Y.m.d',
        self::japanese_era => '',
    ];

    const time_formats = [
        self::japanese => 'H時i分',
        self::slash => 'H:i',
        self::hyphen => 'H:i',
        self::dot => 'H:i',
        self::japanese_era => 'H時i分',
    ];

    /**
     * key/valueの連想配列を返す。
     */
    public static function getMembers()
    {
        $members = parent::getMembers();
        if (!self::canUseJapaneseEra()) {
            unset($members[self::japanese_era]);
        }

        return $members;
    }

    /**
     * key配列を返す。
     */
    public static function getMemberKeys()
    {
        return array_keys(self::getMembers());
    }

    /**
     * 日付部分を表示形式に応じて返す。
     */
    public static function formatDate(CarbonInterface $date, ?string $key): string
    {
        if ($key === self::japanese_era) {
            if (self::canUseJapaneseEra()) {
                return self::formatJapaneseEraDate($date);
            }

            return $date->format(self::date_formats[self::japanese]);
        }

        return $date->format(self::getDateFormat($key));
    }

    /**
     * 和暦表示が利用できるかを返す。
     */
    private static function canUseJapaneseEra(): bool
    {
        return class_exists(\IntlDateFormatter::class);
    }

    /**
     * 日付部分の表示形式を返す。
     */
    public static function getDateFormat(?string $key): string
    {
        return self::date_formats[$key] ?? self::date_formats[self::japanese];
    }

    /**
     * 時刻部分の表示形式を返す。
     */
    public static function getTimeFormat(?string $key): string
    {
        return self::time_formats[$key] ?? self::time_formats[self::japanese];
    }

    /**
     * 日付を和暦で返す。
     */
    private static function formatJapaneseEraDate(CarbonInterface $date): string
    {
        $formatter = new \IntlDateFormatter(
            'ja_JP@calendar=japanese',
            \IntlDateFormatter::NONE,
            \IntlDateFormatter::NONE,
            $date->getTimezone()->getName(),
            \IntlDateFormatter::TRADITIONAL,
            'Gy年M月d日'
        );

        return $formatter->format($date->getTimestamp());
    }
}
