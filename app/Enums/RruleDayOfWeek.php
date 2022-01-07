<?php

namespace App\Enums;

use Illuminate\Support\Facades\App;

/**
 * rrule（繰り返し予定）の曜日
 */
final class RruleDayOfWeek extends EnumsBase
{
    // 定数メンバ
    const sun = 'SU';
    const mon = 'MO';
    const tue = 'TU';
    const wed = 'WE';
    const thu = 'TH';
    const fri = 'FR';
    const sat = 'SA';

    // key/valueの連想配列 日本語版
    const enum_ja = [
        self::sun => '日',
        self::mon => '月',
        self::tue => '火',
        self::wed => '水',
        self::thu => '木',
        self::fri => '金',
        self::sat => '土',
    ];

    // key/valueの連想配列 英語版
    const enum_en = [
        self::sun => 'Sun',
        self::mon => 'Mon',
        self::tue => 'Tue',
        self::wed => 'Wed',
        self::thu => 'Thu',
        self::fri => 'Fri',
        self::sat => 'Sat',
    ];

    /*
     * 対応した和名を返す
     */
    public static function getDescription($key): string
    {
        return App::getLocale() == ConnectLocale::en ? self::enum_en[$key] : self::enum_ja[$key];
    }

    /*
     * key/valueの連想配列を返す
     */
    public static function getMembers()
    {
        return App::getLocale() == ConnectLocale::en ? self::enum_en : self::enum_ja;
    }

    /*
     * 日本語のkey/valueの連想配列を返す
     */
    public static function getMembersJa()
    {
        return self::enum_ja;
    }

    /*
     * 曜日番号から、rruleの曜日キー get
     */
    public static function getDayOfWeekToKey($number)
    {
        $day_of_weeks_to_key = [
            0 => self::sun,
            1 => self::mon,
            2 => self::tue,
            3 => self::wed,
            4 => self::thu,
            5 => self::fri,
            6 => self::sat,
        ];
        return $day_of_weeks_to_key[$number];
    }

    /*
     * 毎月曜日指定のenum
     */
    private static function enumBydayMonthly(?string $locale = null) : array
    {
        if (is_null($locale)) {
            $locale = App::getLocale();
        }

        $rrule_byday_monthly = [];

        if ($locale == ConnectLocale::en) {
            for ($i = 1; $i <= 5; $i++) {

                if ($i == 5) {
                    $week_name = 'last';
                    $week_i = -1;
                } else {
                    $no_en = [
                        1 => '1st',
                        2 => '2nd',
                        3 => '3rd',
                        4 => '4th',
                    ];

                    $week_name = $no_en[$i];
                    $week_i = $i;
                }

                $rrule_byday_monthly[$week_i . self::sun] = "{$week_name} Sunday";
                $rrule_byday_monthly[$week_i . self::mon] = "{$week_name} Monday";
                $rrule_byday_monthly[$week_i . self::tue] = "{$week_name} Tuesday";
                $rrule_byday_monthly[$week_i . self::wed] = "{$week_name} Wednesday";
                $rrule_byday_monthly[$week_i . self::thu] = "{$week_name} Thursday";
                $rrule_byday_monthly[$week_i . self::fri] = "{$week_name} Friday";
                $rrule_byday_monthly[$week_i . self::sat] = "{$week_name} Saturday";
            }

        } else {
            for ($i = 1; $i <= 5; $i++) {

                if ($i == 5) {
                    $week_name = '最終週';
                    $week_i = -1;
                } else {
                    $week_name = "第{$i}";
                    $week_i = $i;
                }

                $rrule_byday_monthly[$week_i . self::sun] = "{$week_name}日曜日";
                $rrule_byday_monthly[$week_i . self::mon] = "{$week_name}月曜日";
                $rrule_byday_monthly[$week_i . self::tue] = "{$week_name}火曜日";
                $rrule_byday_monthly[$week_i . self::wed] = "{$week_name}水曜日";
                $rrule_byday_monthly[$week_i . self::thu] = "{$week_name}木曜日";
                $rrule_byday_monthly[$week_i . self::fri] = "{$week_name}金曜日";
                $rrule_byday_monthly[$week_i . self::sat] = "{$week_name}土曜日";
            }
        }

        return $rrule_byday_monthly;
    }

    /*
     * 毎月曜日指定のkey/valueの連想配列を返す
     */
    public static function getMembersBydayMonthly() : array
    {
        return self::enumBydayMonthly();
    }

    /*
     * 日本語の毎月曜日指定のkey/valueの連想配列を返す
     */
    public static function getMembersBydayMonthlyJp() : array
    {
        return self::enumBydayMonthly(ConnectLocale::ja);
    }

    /*
     * 毎月曜日指定の対応した和名を返す
     */
    public static function getDescriptionBydayMonthly($key): string
    {
        $rrule_byday_monthly = self::enumBydayMonthly();
        return $rrule_byday_monthly[$key];
    }
}
